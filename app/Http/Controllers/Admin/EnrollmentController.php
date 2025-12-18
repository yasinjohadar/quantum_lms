<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $enrollmentsQuery = Enrollment::with(['user', 'subject.schoolClass.stage', 'enrolledBy']);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $enrollmentsQuery->search($request->input('search'));
        }

        // فلترة حسب الطالب
        if ($request->filled('user_id')) {
            $enrollmentsQuery->forUser($request->input('user_id'));
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $enrollmentsQuery->forSubject($request->input('subject_id'));
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $enrollmentsQuery->where('status', $request->input('status'));
        }

        $enrollments = $enrollmentsQuery->latest('enrolled_at')->paginate(20);
        
        $subjects = Subject::with('schoolClass')->active()->ordered()->get();
        
        // جلب المستخدمين (الطلاب إذا كان role موجود، وإلا جميع المستخدمين)
        try {
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            $users = $hasStudentRole ? User::students()->get() : User::limit(100)->get();
        } catch (\Exception $e) {
            $users = User::limit(100)->get();
        }

        return view('admin.pages.enrollments.index', compact('enrollments', 'subjects', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $stages = Stage::ordered()->get();
        $classes = SchoolClass::with('stage')->active()->ordered()->get();
        $subjects = Subject::with(['schoolClass.stage'])->active()->ordered()->get();

        // إذا كان هناك subject_id محدد، فلتر المواد
        $selectedSubjectId = null;
        if ($request->filled('subject_id')) {
            $selectedSubjectId = $request->input('subject_id');
            $selectedSubject = Subject::find($selectedSubjectId);
            if ($selectedSubject && $selectedSubject->class_id) {
                $subjects = $subjects->where('class_id', $selectedSubject->class_id);
            }
        }

        // إذا كان هناك class_id محدد، فلتر المواد
        $selectedClassId = null;
        if ($request->filled('class_id')) {
            $selectedClassId = $request->input('class_id');
            $subjects = $subjects->where('class_id', $selectedClassId);
        }

        return view('admin.pages.enrollments.create', compact('stages', 'classes', 'subjects', 'selectedSubjectId', 'selectedClassId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|exists:subjects,id',
            'status' => 'nullable|in:active,suspended,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $enrolledBy = auth()->id();
            $status = $request->input('status', 'active');
            $notes = $request->input('notes');
            $userIds = $request->input('user_ids');
            $subjectIds = $request->input('subject_ids');

            $enrollments = [];
            $skipped = 0;

            foreach ($userIds as $userId) {
                foreach ($subjectIds as $subjectId) {
                    // التحقق من عدم وجود انضمام مكرر
                    $existing = Enrollment::where('user_id', $userId)
                        ->where('subject_id', $subjectId)
                        ->first();

                    if ($existing) {
                        $skipped++;
                        continue;
                    }

                    $enrollments[] = [
                        'user_id' => $userId,
                        'subject_id' => $subjectId,
                        'enrolled_by' => $enrolledBy,
                        'enrolled_at' => now(),
                        'status' => $status,
                        'notes' => $notes,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (empty($enrollments)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'جميع الانضمامات موجودة مسبقاً');
            }

            Enrollment::insert($enrollments);

            DB::commit();

            $successCount = count($enrollments);
            $message = "تم إضافة {$successCount} انضمام بنجاح";
            
            if ($skipped > 0) {
                $message .= "، وتم تخطي {$skipped} انضمام مكرر";
            }

            return redirect()
                ->route('admin.enrollments.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating enrollments: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة الانضمامات: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment, Request $request)
    {
        try {
            $enrollment->delete();

            // إذا كان هناك redirect_to محدد، استخدمه
            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'تم إلغاء الانضمام بنجاح');
            }

            return redirect()
                ->route('admin.enrollments.index')
                ->with('success', 'تم إلغاء الانضمام بنجاح');

        } catch (\Exception $e) {
            Log::error('Error deleting enrollment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إلغاء الانضمام');
        }
    }

    /**
     * AJAX endpoint للبحث عن الطلاب
     */
    public function searchStudents(Request $request)
    {
        try {
            $query = User::query();

            // فلترة الطلاب فقط (إذا كان role 'student' موجود)
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            if ($hasStudentRole) {
                try {
                    $query->students();
                } catch (\Exception $e) {
                    Log::warning('Error in students scope: ' . $e->getMessage());
                }
            }

            // البحث
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('id', $search);
                });
            }

            // فلترة حسب المرحلة (من خلال المواد المنضمة) - فقط إذا كان هناك enrollments
            if ($request->filled('stage_id')) {
                try {
                    $query->whereHas('enrollments.subject.schoolClass', function ($q) use ($request) {
                        $q->where('classes.stage_id', $request->input('stage_id'));
                    });
                } catch (\Exception $e) {
                    Log::warning('Error filtering by stage: ' . $e->getMessage());
                }
            }

            // فلترة حسب الصف (من خلال المواد المنضمة) - فقط إذا كان هناك enrollments
            if ($request->filled('class_id')) {
                try {
                    $query->whereHas('enrollments.subject', function ($q) use ($request) {
                        $q->where('subjects.class_id', $request->input('class_id'));
                    });
                } catch (\Exception $e) {
                    Log::warning('Error filtering by class: ' . $e->getMessage());
                }
            }

            // فلترة حسب حالة المستخدم
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // فلترة حسب المواد المنضمة مسبقاً
            if ($request->filled('exclude_subject_id')) {
                $query->whereDoesntHave('enrollments', function ($q) use ($request) {
                    $q->where('subject_id', $request->input('exclude_subject_id'));
                });
            }

            // فلترة حسب المواد المنضمة
            if ($request->filled('has_subject_id')) {
                $query->whereHas('enrollments', function ($q) use ($request) {
                    $q->where('subject_id', $request->input('has_subject_id'));
                });
            }

            $students = $query->select('id', 'name', 'email', 'phone', 'is_active', 'avatar')
                ->orderBy('name')
                ->limit(100)
                ->get();
            
            // إعادة تسمية avatar إلى photo للتوافق مع الكود
            $students->each(function ($student) {
                $student->photo = $student->avatar ?? null;
            });

            // إضافة معلومات إضافية
            $students->each(function ($student) {
                $student->enrolled_subjects_count = \App\Models\Enrollment::where('user_id', $student->id)->count();
                $student->active_enrollments_count = \App\Models\Enrollment::where('user_id', $student->id)->where('status', 'active')->count();
            });

            return response()->json([
                'success' => true,
                'data' => $students,
                'count' => $students->count(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in searchStudents: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث: ' . $e->getMessage(),
                'data' => [],
                'count' => 0,
            ], 500);
        }
    }

    /**
     * AJAX endpoint للحصول على المواد حسب الصف
     */
    public function getSubjectsByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $subjects = Subject::with('schoolClass.stage')
            ->where('class_id', $request->input('class_id'))
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }
}
