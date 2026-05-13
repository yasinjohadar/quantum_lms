<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\ClassEnrollment;
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
        
        // الحصول على المواد المسجل فيها الطالب
        $enrolledSubjectIds = $user->enrollments()
            ->pluck('subject_id')
            ->toArray();
        
        // الحصول على طلبات الانضمام المعلقة
        $pendingEnrollments = $user->enrollments()
            ->pending()
            ->pluck('subject_id')
            ->toArray();

        $stats = $this->studentEnrollmentStats($user);

        return view('student.pages.enrollments.class-show', array_merge(
            compact('class', 'enrolledSubjectIds', 'pendingEnrollments'),
            $stats
        ));
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
                    'message' => 'لقد قمت بشراء هذه المادة مسبقاً'
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
                        'message' => 'أنت مسجل في هذه المادة من خلال شراء الصف كاملاً'
                    ], 400);
                }
            }
            
            // إذا كان السعر > 0، توجيه إلى صفحة الشراء
            if (!$subject->is_free && $subject->price > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب شراء هذه المادة أولاً',
                    'redirect' => route('student.purchases.subject.show', $subjectId),
                    'requires_purchase' => true,
                ], 400);
            }

            // إذا كان السعر 0، إنشاء شراء مكتمل تلقائياً
            $purchase = $this->purchaseService->createPurchase($user, $subject, 'subject');
            
            return response()->json([
                'success' => true,
                'message' => 'تم التسجيل في المادة بنجاح'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المادة غير موجودة أو غير نشطة'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in requestEnrollment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الطلب: ' . $e->getMessage()
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
        
        // التحقق من وجود الصف
        $class = SchoolClass::with(['subjects' => function($query) {
            $query->where('is_active', true);
        }])->findOrFail($classId);
        
        if ($class->subjects->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد مواد دراسية في هذا الصف'
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
                    'message' => 'لقد قمت بشراء هذا الصف مسبقاً'
                ], 400);
            }

            // إذا كان السعر > 0، توجيه إلى صفحة الشراء
            if (!$class->is_free && $class->price > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب شراء هذا الصف أولاً',
                    'redirect' => route('student.purchases.class.show', $classId),
                    'requires_purchase' => true,
                ], 400);
            }

            // إذا كان السعر 0، إنشاء شراء مكتمل تلقائياً
            $purchase = $this->purchaseService->createPurchase($user, $class, 'class');
            
            return response()->json([
                'success' => true,
                'message' => 'تم التسجيل في الصف بنجاح'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in requestClassEnrollment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الطلب: ' . $e->getMessage()
            ], 500);
        }
    }
}
