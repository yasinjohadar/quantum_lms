<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
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
            
            // التحقق من عدم وجود انضمام مسبق
            $existingEnrollment = Enrollment::where('user_id', $user->id)
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
                    // إذا كان الانضمام معلق أو مكتمل، يمكن إنشاء طلب جديد
                    $existingEnrollment->delete();
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
            DB::beginTransaction();
            
            $enrollments = [];
            $skipped = 0;
            $added = 0;
            
            foreach ($class->subjects as $subject) {
                // التحقق من عدم وجود انضمام مسبق
                $existingEnrollment = Enrollment::where('user_id', $user->id)
                    ->where('subject_id', $subject->id)
                    ->first();
                
                if ($existingEnrollment) {
                    if ($existingEnrollment->status === 'pending' || $existingEnrollment->status === 'active') {
                        $skipped++;
                        continue;
                    } elseif (in_array($existingEnrollment->status, ['suspended', 'completed'])) {
                        $existingEnrollment->delete();
                    }
                }
                
                $enrollments[] = [
                    'user_id' => $user->id,
                    'subject_id' => $subject->id,
                    'enrolled_by' => null,
                    'enrolled_at' => now(),
                    'status' => 'pending',
                    'notes' => 'طلب انضمام لصف كامل: ' . $class->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $added++;
            }
            
            if (empty($enrollments)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'أنت مسجل بالفعل في جميع مواد هذا الصف'
                ], 400);
            }
            
            Enrollment::insert($enrollments);
            
            DB::commit();
            
            $message = "تم إرسال طلب الانضمام لـ {$added} مادة";
            if ($skipped > 0) {
                $message .= " (تم تخطي {$skipped} مادة مسجل فيها مسبقاً)";
            }
            $message .= ". سيتم مراجعته من قبل الإدارة.";
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الطلب: ' . $e->getMessage()
            ], 500);
        }
    }
}

