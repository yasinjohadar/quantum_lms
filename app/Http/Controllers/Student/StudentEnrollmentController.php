<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\ClassEnrollment;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    /**
     * عرض جميع الصفوف والمواد المتاحة للانضمام
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // الحصول على جميع المراحل مع الصفوف فقط (بدون تحميل المواد)
        $stages = Stage::with(['classes' => function($query) {
            $query->where('is_active', true)->orderBy('order');
        }])
        ->whereHas('classes', function($query) {
            $query->where('is_active', true);
        })
        ->orderBy('order')
        ->get();
        
        return view('student.pages.enrollments.index', compact('stages'));
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
        
        return view('student.pages.enrollments.class-show', compact('class', 'enrolledSubjectIds', 'pendingEnrollments'));
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
            
            // التحقق من عدم وجود انضمام مسبق (بما في ذلك المحذوفة ب soft delete)
            $existingEnrollment = Enrollment::withTrashed()
                ->where('user_id', $user->id)
                ->where('subject_id', $subjectId)
                ->first();
            
            if ($existingEnrollment) {
                if ($existingEnrollment->status === 'pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'لديك طلب انضمام معلق لهذه المادة'
                    ], 400);
                } elseif ($existingEnrollment->status === 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'أنت مسجل بالفعل في هذه المادة'
                    ], 400);
                } elseif (in_array($existingEnrollment->status, ['suspended', 'completed'])) {
                    // إذا كان الانضمام معلق أو مكتمل، احذفه بشكل نهائي
                    $existingEnrollment->forceDelete();
                } else {
                    // أي حالة أخرى، احذفه بشكل نهائي
                    $existingEnrollment->forceDelete();
                }
            }
            
            // إنشاء طلب انضمام بحالة pending
            Enrollment::create([
                'user_id' => $user->id,
                'subject_id' => $subjectId,
                'enrolled_by' => null, // سيتم تعيينه من قبل الإدارة
                'enrolled_at' => now(),
                'status' => 'pending',
                'notes' => 'طلب انضمام من الطالب',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال طلب الانضمام بنجاح. سيتم مراجعته من قبل الإدارة.'
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
            // التحقق من عدم وجود طلب صف معلق أو مقبول للطالب في نفس الصف
            $existingClassEnrollment = ClassEnrollment::withTrashed()
                ->where('user_id', $user->id)
                ->where('class_id', $classId)
                ->first();
            
            if ($existingClassEnrollment) {
                if ($existingClassEnrollment->status === 'pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'لديك طلب انضمام معلق لهذا الصف'
                    ], 400);
                } elseif ($existingClassEnrollment->status === 'approved') {
                    return response()->json([
                        'success' => false,
                        'message' => 'أنت مسجل بالفعل في هذا الصف'
                    ], 400);
                } elseif ($existingClassEnrollment->status === 'rejected') {
                    // إذا كان مرفوض، احذفه بشكل نهائي للسماح بطلب جديد
                    $existingClassEnrollment->forceDelete();
                } else {
                    // أي حالة أخرى، احذفه بشكل نهائي
                    $existingClassEnrollment->forceDelete();
                }
            }
            
            // إنشاء طلب انضمام للصف
            ClassEnrollment::create([
                'user_id' => $user->id,
                'class_id' => $classId,
                'enrolled_by' => null,
                'enrolled_at' => null,
                'status' => 'pending',
                'notes' => 'طلب انضمام لصف كامل: ' . $class->name,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال طلب الانضمام للصف بنجاح. سيتم مراجعته من قبل الإدارة.'
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
