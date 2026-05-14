<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Stage;
use App\Models\Purchase;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }
    /**
     * عرض جميع الصفوف والمواد المتاحة للانضمام
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $stats = $this->studentEnrollmentStats($user);

        // الحصول على جميع المراحل مع الصفوف فقط (بدون تحميل المواد)
        $stages = Stage::with(['classes' => function($query) {
            $query->where('is_active', true)->orderBy('order');
        }])
        ->whereHas('classes', function($query) {
            $query->where('is_active', true);
        })
        ->orderBy('order')
        ->get();

        $this->filterStagesForJoinableClasses($user, $stages);

        $stages = $stages->filter(fn (Stage $stage) => $stage->classes->isNotEmpty())->values();

        return view('student.pages.enrollments.index', array_merge(compact('stages'), $stats));
    }
    
    /**
     * عرض مواد صف معين
     */
    public function showClass($classId)
    {
        $user = Auth::user();
        
        // الحصول على الصف مع المواد
        $class = SchoolClass::with(['subjects' => function($query) {
            $query->where('is_active', true)->orderBy('order');
        }, 'stage'])
        ->where('is_active', true)
        ->findOrFail($classId);
        
        $activeEnrolledSubjectIds = $user->enrollments()
            ->where('status', 'active')
            ->pluck('subject_id');

        $pendingEnrollments = $user->enrollments()
            ->pending()
            ->pluck('subject_id')
            ->toArray();

        $hasFullClassAccess = $user->hasFullAccessToSchoolClass($class);

        $subjectsToShow = $hasFullClassAccess
            ? collect()
            : $class->subjects
                ->reject(fn (Subject $subject) => $activeEnrolledSubjectIds->contains($subject->id))
                ->values();

        $stats = $this->studentEnrollmentStats($user);

        return view('student.pages.enrollments.class-show', array_merge(
            compact('class', 'subjectsToShow', 'pendingEnrollments', 'hasFullClassAccess'),
            $stats
        ));
    }

    /**
     * إخفاء الصفوف التي لدى الطالب فيها وصول كامل لجميع المواد النشطة، وضبط عدد المواد المتاحة للانضمام على كل بطاقة.
     *
     * @param  \Illuminate\Support\Collection<int, Stage>  $stages
     */
    private function filterStagesForJoinableClasses(User $user, $stages): void
    {
        $approvedClassIdSet = array_flip($user->classEnrollments()->approved()->pluck('class_id')->all());
        $completedClassPurchaseIdSet = array_flip(Purchase::query()
            ->where('user_id', $user->id)
            ->where('purchasable_type', SchoolClass::class)
            ->where('status', 'completed')
            ->pluck('purchasable_id')
            ->all());
        $userActiveSubjectIdSet = array_flip($user->enrollments()->where('status', 'active')->pluck('subject_id')->all());

        $classIds = $stages->flatMap->classes->pluck('id')->unique()->values();
        if ($classIds->isEmpty()) {
            return;
        }

        $activeSubjectsByClass = Subject::query()
            ->whereIn('class_id', $classIds)
            ->where('is_active', true)
            ->get(['id', 'class_id'])
            ->groupBy('class_id');

        foreach ($stages as $stage) {
            $filtered = $stage->classes->filter(function (SchoolClass $class) use (
                $approvedClassIdSet,
                $completedClassPurchaseIdSet,
                $userActiveSubjectIdSet,
                $activeSubjectsByClass
            ) {
                if (isset($approvedClassIdSet[$class->id])) {
                    return false;
                }

                if (isset($completedClassPurchaseIdSet[$class->id])) {
                    return false;
                }

                $subjectIds = $activeSubjectsByClass->get($class->id, collect())->pluck('id');
                if ($subjectIds->isEmpty()) {
                    return true;
                }

                foreach ($subjectIds as $sid) {
                    if (! isset($userActiveSubjectIdSet[$sid])) {
                        return true;
                    }
                }

                return false;
            })->values();

            $stage->setRelation('classes', $filtered);

            foreach ($filtered as $class) {
                $subjectIds = $activeSubjectsByClass->get($class->id, collect())->pluck('id');
                $joinable = $subjectIds->filter(fn ($id) => ! isset($userActiveSubjectIdSet[$id]))->count();
                $class->setAttribute('joinable_subjects_count', $joinable);
            }
        }
    }

    /**
     * إحصائيات انضمام الطالب: عدد الصفوف التي لديه فيها وصول (مادة نشطة أو انضمام صف معتمد) وعدد المواد النشطة.
     *
     * @return array{assigned_classes_total: int, assigned_subjects_total: int}
     */
    private function studentEnrollmentStats(User $user): array
    {
        $assignedSubjectsTotal = $user->enrollments()->where('status', 'active')->count();

        $classIdsFromSubjects = $user->subjects()
            ->wherePivot('status', 'active')
            ->pluck('class_id')
            ->filter(fn ($id) => $id !== null && $id !== '');

        $classIdsFromClassEnrollment = $user->classEnrollments()->approved()->pluck('class_id');

        $assignedClassesTotal = $classIdsFromSubjects
            ->merge($classIdsFromClassEnrollment)
            ->unique()
            ->count();

        return [
            'assigned_classes_total' => (int) $assignedClassesTotal,
            'assigned_subjects_total' => (int) $assignedSubjectsTotal,
        ];
    }
    
    /**
     * طلب الانضمام إلى مادة
     */
    public function requestEnrollment(Request $request, $subjectId)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'currency_id' => 'nullable|exists:currencies,id',
            ]);
            $currencyId = $request->filled('currency_id') ? (int) $request->input('currency_id') : null;

            // التحقق من وجود المادة
            $subject = Subject::where('is_active', true)->findOrFail($subjectId);

            // التحقق من وجود شراء مسبق
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', Subject::class)
                ->where('purchasable_id', $subjectId)
                ->where('status', 'completed')
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد قمت بشراء هذه المادة مسبقاً',
                ], 400);
            }

            // التحقق من شراء الصف كاملاً
            $class = $subject->schoolClass;
            if ($class) {
                $classPurchase = Purchase::where('user_id', $user->id)
                    ->where('purchasable_type', SchoolClass::class)
                    ->where('purchasable_id', $class->id)
                    ->where('status', 'completed')
                    ->first();

                if ($classPurchase) {
                    return response()->json([
                        'success' => false,
                        'message' => 'أنت مسجل في هذه المادة من خلال شراء الصف كاملاً',
                    ], 400);
                }
            }

            if (! $subject->is_free && $subject->price > 0) {
                $purchase = $this->resolveOrCreatePendingPurchase($user, $subject, 'subject', $currencyId);

                if ($purchase->status === 'completed') {
                    return response()->json([
                        'success' => true,
                        'message' => 'تم التسجيل في المادة بنجاح',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'requires_payment' => true,
                    'purchase_id' => $purchase->id,
                    'message' => 'أكمل الدفع لإتمام التسجيل في المادة.',
                ]);
            }

            // إذا كان السعر 0، إنشاء شراء مكتمل تلقائياً
            $this->purchaseService->createPurchase($user, $subject, 'subject');

            return response()->json([
                'success' => true,
                'message' => 'تم التسجيل في المادة بنجاح',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المادة غير موجودة أو غير نشطة',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in requestEnrollment: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الطلب: '.$e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * إلغاء طلب الانضمام
     */
    public function cancelRequest($subjectId)
    {
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('subject_id', $subjectId)
            ->pending()
            ->firstOrFail();
        
        try {
            $enrollment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء طلب الانضمام بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء الطلب'
            ], 500);
        }
    }
    
    /**
     * طلب الانضمام لصف كامل (جميع المواد في الصف)
     */
    public function requestClassEnrollment(Request $request, $classId)
    {
        $user = Auth::user();

        $request->validate([
            'currency_id' => 'nullable|exists:currencies,id',
        ]);
        $currencyId = $request->filled('currency_id') ? (int) $request->input('currency_id') : null;

        // التحقق من وجود الصف
        $class = SchoolClass::with(['subjects' => function ($query) {
            $query->where('is_active', true);
        }])->findOrFail($classId);

        if ($class->subjects->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد مواد دراسية في هذا الصف',
            ], 400);
        }

        try {
            // التحقق من وجود شراء مسبق
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', SchoolClass::class)
                ->where('purchasable_id', $classId)
                ->where('status', 'completed')
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد قمت بشراء هذا الصف مسبقاً',
                ], 400);
            }

            if (! $class->is_free && $class->price > 0) {
                $purchase = $this->resolveOrCreatePendingPurchase($user, $class, 'class', $currencyId);

                if ($purchase->status === 'completed') {
                    return response()->json([
                        'success' => true,
                        'message' => 'تم التسجيل في الصف بنجاح',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'requires_payment' => true,
                    'purchase_id' => $purchase->id,
                    'message' => 'أكمل الدفع لإتمام التسجيل في الصف.',
                ]);
            }

            // إذا كان السعر 0، إنشاء شراء مكتمل تلقائياً
            $this->purchaseService->createPurchase($user, $class, 'class');

            return response()->json([
                'success' => true,
                'message' => 'تم التسجيل في الصف بنجاح',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in requestClassEnrollment: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الطلب: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * إنشاء شراء معلّق أو إعادة استخدام شراء معلّق لنفس العنصر (صف/مادة) لإكمال الدفع من واجهة الانضمام.
     */
    private function resolveOrCreatePendingPurchase(User $user, object $purchasable, string $purchaseType, ?int $currencyId): Purchase
    {
        $pending = Purchase::query()
            ->where('user_id', $user->id)
            ->where('purchasable_type', $purchasable::class)
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return $pending;
        }

        return $this->purchaseService->createPurchase($user, $purchasable, $purchaseType, $currencyId);
    }
}
