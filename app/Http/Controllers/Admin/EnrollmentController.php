<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStudentEnrollmentService;
use App\Services\StaffNotificationService;
use App\Models\Enrollment;
use App\Models\ClassEnrollment;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Stage;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    public function __construct(
        private AdminStudentEnrollmentService $adminStudentEnrollmentService,
        private StaffNotificationService $staffNotificationService
    ) {
        $this->middleware('auth');
        $this->middleware(['permission:enrollment-list'])->only('index');
        $this->middleware(['permission:enrollment-create'])->only(['create', 'store', 'assignClassToUser']);
        $this->middleware(['permission:enrollment-delete'])->only('destroy', 'destroyMultiple', 'enrollmentsByClass', 'destroyByClass', 'destroyBySubject', 'countByClass', 'countBySubject');
        $this->middleware(['permission:enrollment-pending-requests'])->only('pendingRequests');
        $this->middleware(['permission:enrollment-approve'])->only('approve');
        $this->middleware(['permission:enrollment-reject'])->only('reject');
        $this->middleware(['permission:enrollment-approve-multiple'])->only('approveMultiple');
        $this->middleware(['permission:enrollment-reject-multiple'])->only(['rejectMultiple', 'cleanStalePendingEnrollments']);
        $this->middleware(['permission:enrollment-search-students'])->only('searchStudents');
        $this->middleware(['permission:enrollment-get-subjects-by-class|enrollment-create'])->only('getSubjectsByClass');
        $this->middleware(['permission:enrollment-class-pending-requests'])->only('classPendingRequests');
        $this->middleware(['permission:enrollment-approve-class'])->only('approveClassEnrollment');
        $this->middleware(['permission:enrollment-reject-class'])->only('rejectClassEnrollment');
        $this->middleware(['permission:enrollment-approve-multiple-class'])->only(['approveMultipleClassEnrollments', 'approveAllPendingClassEnrollments']);
        $this->middleware(['permission:enrollment-reject-multiple-class'])->only(['rejectMultipleClassEnrollments', 'cleanStalePendingClassEnrollments']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $enrollmentsQuery = Enrollment::with(['user', 'subject.schoolClass.stage', 'enrolledBy']);

        // إذا كان المستخدم معلم وليس مشرف/مدير
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            
            $enrollmentsQuery->whereHas('subject', function($q) use ($classIds, $subjectIds) {
                // المواد من الصفوف المخصصة
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                // أو المواد المخصصة مباشرة
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $enrollmentsQuery->search($request->input('search'));
        }

        // فلترة حسب الطالب
        if ($request->filled('user_id')) {
            $enrollmentsQuery->forUser($request->input('user_id'));
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $enrollmentsQuery->whereHas('subject', function($query) use ($request) {
                $query->where('class_id', $request->input('class_id'));
            });
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
        
        // إذا كان هناك class_id محدد، فلتر المواد
        if ($request->filled('class_id')) {
            $subjects = $subjects->where('class_id', $request->input('class_id'));
        }
        
        $classes = SchoolClass::with('stage')->active()->ordered()->get();
        
        // جلب المستخدمين (الطلاب إذا كان role موجود، وإلا جميع المستخدمين)
        try {
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            $users = $hasStudentRole ? User::students()->get() : User::limit(100)->get();
        } catch (\Exception $e) {
            $users = User::limit(100)->get();
        }

        // إحصائيات طلبات الانضمام المعلقة
        $pendingCount = Enrollment::pending()->count();

        // إذا كان طلب Ajax، إرجاع JSON
        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.enrollments.partials.table', compact('enrollments'))->render();
            $pagination = view('admin.pages.enrollments.partials.pagination', compact('enrollments'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $enrollments->total(),
            ]);
        }

        return view('admin.pages.enrollments.index', compact('enrollments', 'subjects', 'users', 'pendingCount', 'classes'));
    }

    /**
     * عرض طلبات الانضمام المعلقة
     */
    public function pendingRequests(Request $request)
    {
        $enrollmentsQuery = Enrollment::with(['user', 'subject.schoolClass.stage', 'enrolledBy'])
            ->pending();

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

        $enrollments = $enrollmentsQuery->latest('enrolled_at')->paginate(20);
        
        $subjects = Subject::with('schoolClass')->active()->ordered()->get();
        
        // جلب المستخدمين (الطلاب إذا كان role موجود، وإلا جميع المستخدمين)
        try {
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            $users = $hasStudentRole ? User::students()->get() : User::limit(100)->get();
        } catch (\Exception $e) {
            $users = User::limit(100)->get();
        }

        // إحصائيات
        $pendingCount = Enrollment::pending()->count();
        $activeCount = Enrollment::active()->count();

        return view('admin.pages.enrollments.pending', compact('enrollments', 'subjects', 'users', 'pendingCount', 'activeCount'));
    }
    
    /**
     * قبول طلب انضمام
     */
    public function approve(Enrollment $enrollment, Request $request)
    {
        try {
            if ($enrollment->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'هذا الطلب ليس معلقاً');
            }

            $enrollment->update([
                'status' => 'active',
                'enrolled_by' => auth()->id(),
                'enrolled_at' => now(),
                'notes' => $request->input('notes', $enrollment->notes),
            ]);

            return redirect()->back()
                ->with('success', 'تم قبول طلب الانضمام بنجاح');

        } catch (\Exception $e) {
            Log::error('Error approving enrollment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول الطلب');
        }
    }
    
    /**
     * رفض طلب انضمام
     */
    public function reject(Enrollment $enrollment, Request $request)
    {
        try {
            if ($enrollment->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'هذا الطلب ليس معلقاً');
            }

            $enrollment->delete();

            return redirect()->back()
                ->with('success', 'تم رفض طلب الانضمام بنجاح');

        } catch (\Exception $e) {
            Log::error('Error rejecting enrollment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض الطلب');
        }
    }
    
    /**
     * قبول عدة طلبات دفعة واحدة
     */
    public function approveMultiple(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'required|exists:enrollments,id',
        ]);

        try {
            DB::beginTransaction();

            $enrollments = Enrollment::whereIn('id', $request->input('enrollment_ids'))
                ->pending()
                ->get();

            if ($enrollments->isEmpty()) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'لا توجد طلبات معلقة للقبول');
            }

            $count = 0;
            foreach ($enrollments as $enrollment) {
                $enrollment->update([
                    'status' => 'active',
                    'enrolled_by' => auth()->id(),
                    'enrolled_at' => now(),
                ]);
                $count++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "تم قبول {$count} طلب انضمام بنجاح");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving multiple enrollments: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول الطلبات');
        }
    }
    
    /**
     * رفض عدة طلبات دفعة واحدة
     */
    public function rejectMultiple(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'required|exists:enrollments,id',
        ]);

        try {
            DB::beginTransaction();

            $enrollments = Enrollment::whereIn('id', $request->input('enrollment_ids'))
                ->pending()
                ->get();

            if ($enrollments->isEmpty()) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'لا توجد طلبات معلقة للرفض');
            }

            $count = $enrollments->count();
            Enrollment::whereIn('id', $request->input('enrollment_ids'))
                ->pending()
                ->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', "تم رفض {$count} طلب انضمام بنجاح");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting multiple enrollments: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض الطلبات');
        }
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
            'redirect_to' => 'nullable|string|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $enrolledBy = auth()->id();
            $status = $request->input('status', 'active');
            $notes = $request->input('notes');
            $userIds = $request->input('user_ids');
            $subjectIds = $request->input('subject_ids');

            $counts = $this->adminStudentEnrollmentService->bulkAttachSubjects(
                $userIds,
                $subjectIds,
                $status,
                $notes,
                $enrolledBy
            );

            if ($counts['insert_count'] === 0 && $counts['reactivated'] === 0) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'جميع الانضمامات موجودة مسبقاً');
            }

            DB::commit();

            $messageParts = [];
            $insertCount = $counts['insert_count'];
            $reactivated = $counts['reactivated'];
            $skipped = $counts['skipped'];
            if ($insertCount > 0) {
                $messageParts[] = "تم إضافة {$insertCount} انضماماً جديداً";
            }
            if ($reactivated > 0) {
                $messageParts[] = "تمت إعادة تفعيل {$reactivated} انضماماً كان محذوفاً مسبقاً";
            }
            if ($skipped > 0) {
                $messageParts[] = "تم تخطي {$skipped} انضمام مكرر (موجود فعلاً)";
            }
            $message = implode('، ', $messageParts);

            $safeRedirect = $this->safeInternalRedirectPath($request->input('redirect_to'));
            if ($safeRedirect !== null) {
                return redirect($safeRedirect)->with('success', $message);
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

            $safeRedirect = $this->safeInternalRedirectPath($request->input('redirect_to'));
            if ($safeRedirect !== null) {
                return redirect($safeRedirect)->with('success', 'تم إلغاء الانضمام بنجاح');
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
     * فصل عدة انضمامات دفعة واحدة.
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'required|integer|exists:enrollments,id',
        ]);

        $ids = array_values(array_unique($request->input('enrollment_ids')));
        $user = auth()->user();

        $query = Enrollment::whereIn('id', $ids);
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $query->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }
        $enrollments = $query->get();
        $count = $enrollments->count();

        try {
            foreach ($enrollments as $enrollment) {
                $enrollment->delete();
            }
            $message = 'تم فصل ' . $count . ' انضمام بنجاح';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => $count,
                    'redirect' => route('admin.enrollments.index'),
                ]);
            }
            return redirect()
                ->route('admin.enrollments.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting enrollments: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء فصل الانضمامات'], 500);
            }
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء فصل الانضمامات');
        }
    }

    /**
     * إرجاع انضمامات صف معيّن (للمودال).
     */
    public function enrollmentsByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
        ]);

        $enrollmentsQuery = Enrollment::with(['user', 'subject.schoolClass.stage'])
            ->whereHas('subject', function ($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $enrollmentsQuery->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        $enrollments = $enrollmentsQuery->latest('enrolled_at')->get();
        $data = $enrollments->map(function ($e) {
            return [
                'id' => $e->id,
                'user_id' => $e->user_id,
                'user_name' => $e->user ? $e->user->name : '',
                'user_email' => $e->user ? $e->user->email : '',
                'subject_id' => $e->subject_id,
                'subject_name' => $e->subject ? $e->subject->name : '',
                'status' => $e->status,
            ];
        })->values();

        return response()->json($data);
    }

    /**
     * عدد الانضمامات التي ستُحذف عند فصل انضمامات صف محدد (للعرض في المودال).
     */
    public function countByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
        ]);

        $enrollmentsQuery = Enrollment::whereHas('subject', function ($q) use ($request) {
            $q->where('class_id', $request->input('class_id'));
        });

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $enrollmentsQuery->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        $count = $enrollmentsQuery->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * فصل جميع انضمامات صف محدد دفعة واحدة (بدون تحميل القائمة).
     */
    public function destroyByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
        ]);

        $enrollmentsQuery = Enrollment::whereHas('subject', function ($q) use ($request) {
            $q->where('class_id', $request->input('class_id'));
        });

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $enrollmentsQuery->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        $enrollments = $enrollmentsQuery->get();
        $count = $enrollments->count();

        try {
            foreach ($enrollments as $enrollment) {
                $enrollment->delete();
            }
            $message = 'تم فصل ' . $count . ' انضمام بنجاح';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => $count,
                    'redirect' => route('admin.enrollments.index'),
                ]);
            }
            return redirect()
                ->route('admin.enrollments.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error destroying enrollments by class: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء فصل الانضمامات'], 500);
            }
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء فصل الانضمامات');
        }
    }

    /**
     * عدد الانضمامات التي ستُحذف عند فصل انضمامات مادة معينة (للعرض في المودال).
     */
    public function countBySubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'nullable|integer|exists:classes,id',
        ]);

        $subjectId = $request->input('subject_id');
        $classId = $request->input('class_id');
        if ($classId) {
            $subject = Subject::find($subjectId);
            if (!$subject || (int) $subject->class_id !== (int) $classId) {
                return response()->json(['success' => false, 'message' => 'المادة المحددة لا تنتمي إلى الصف المحدد.'], 422);
            }
        }

        $enrollmentsQuery = Enrollment::where('subject_id', $subjectId);

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $enrollmentsQuery->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        $count = $enrollmentsQuery->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * فصل جميع انضمامات مادة معينة ضمن صف محدد (يجب تحديد الصف ثم المادة).
     */
    public function destroyBySubject(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $subject = Subject::find($request->input('subject_id'));
        if (!$subject || (int) $subject->class_id !== (int) $request->input('class_id')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'المادة المحددة لا تنتمي إلى الصف المحدد.',
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'المادة المحددة لا تنتمي إلى الصف المحدد.');
        }

        $enrollmentsQuery = Enrollment::where('subject_id', $request->input('subject_id'));

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $enrollmentsQuery->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        $enrollments = $enrollmentsQuery->get();
        $count = $enrollments->count();

        try {
            foreach ($enrollments as $enrollment) {
                $enrollment->delete();
            }
            $message = 'تم فصل ' . $count . ' انضمام بنجاح';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => $count,
                    'redirect' => route('admin.enrollments.index'),
                ]);
            }
            return redirect()
                ->route('admin.enrollments.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error destroying enrollments by subject: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء فصل الانضمامات'], 500);
            }
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء فصل الانضمامات');
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
        $classId = $request->input('class_id');
        
        $query = Subject::with('schoolClass.stage')
            ->active()
            ->ordered();

        if ($classId) {
            $request->validate([
                'class_id' => 'exists:classes,id',
            ]);
            $query->where('class_id', $classId);
        }

        $subjects = $query->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    /**
     * عرض طلبات الانضمام للصف المعلقة
     */
    public function classPendingRequests(Request $request)
    {
        $classEnrollmentsQuery = ClassEnrollment::with(['user', 'schoolClass.stage', 'enrolledBy'])
            ->pending();

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $classEnrollmentsQuery->search($request->input('search'));
        }

        // فلترة حسب الطالب
        if ($request->filled('user_id')) {
            $classEnrollmentsQuery->forUser($request->input('user_id'));
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $classEnrollmentsQuery->forClass($request->input('class_id'));
        }

        $classEnrollments = $classEnrollmentsQuery->latest('created_at')->paginate(20);
        
        $classes = SchoolClass::with('stage')->active()->ordered()->get();
        
        // جلب المستخدمين (الطلاب)
        try {
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            $users = $hasStudentRole ? User::students()->get() : User::limit(100)->get();
        } catch (\Exception $e) {
            $users = User::limit(100)->get();
        }

        // إحصائيات
        $pendingCount = ClassEnrollment::pending()->count();
        $approvedCount = ClassEnrollment::approved()->count();

        return view('admin.pages.enrollments.class-pending', compact('classEnrollments', 'classes', 'users', 'pendingCount', 'approvedCount'));
    }

    /**
     * ربط طالب بصف (موافقة إدارية مباشرة + مزامنة مواد الصف النشطة).
     */
    public function assignClassToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'class_id' => 'required|integer|exists:classes,id',
            'notes' => 'nullable|string|max:1000',
            'redirect_to' => 'nullable|string|max:2048',
        ], [
            'user_id.required' => 'معرّف المستخدم مطلوب.',
            'user_id.integer' => 'معرّف المستخدم غير صالح.',
            'user_id.exists' => 'المستخدم المحدد غير موجود.',
            'class_id.required' => 'يجب اختيار صف دراسي.',
            'class_id.integer' => 'معرّف الصف غير صالح.',
            'class_id.exists' => 'الصف المحدد غير موجود.',
            'notes.max' => 'الملاحظات طويلة جداً.',
            'redirect_to.max' => 'مسار إعادة التوجيه غير صالح.',
        ]);

        if ($validator->fails()) {
            return $this->redirectAfterClassAssign($request)
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::findOrFail($request->integer('user_id'));
        if (!$user->hasRole('student')) {
            return $this->redirectAfterClassAssign($request)
                ->with('error', 'يمكن ربط الطلاب فقط بالصفوف الدراسية. المستخدم المحدد ليس ضمن دور الطالب.');
        }

        try {
            DB::beginTransaction();

            $classId = $request->integer('class_id');
            $result = $this->adminStudentEnrollmentService->assignApprovedClassWithProvisioning(
                $user->id,
                $classId,
                $request->input('notes'),
                auth()->id()
            );

            DB::commit();

            $message = "تم ربط الطالب بالصف بنجاح. تم إنشاء {$result['created']} انضمام للمواد";
            if ($result['skipped'] > 0) {
                $message .= " (تم تخطي {$result['skipped']} مادة مسجل فيها مسبقاً)";
            }

            return $this->redirectAfterClassAssign($request)->with('success', $message);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Error assigning class to user (database)', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            return $this->redirectAfterClassAssign($request)
                ->with('error', $this->friendlyClassAssignDbError($e));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning class to user', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return $this->redirectAfterClassAssign($request)
                ->with('error', 'تعذر إتمام ربط الصف: '.$e->getMessage());
        }
    }

    /**
     * قبول طلب انضمام للصف
     */
    public function approveClassEnrollment(ClassEnrollment $classEnrollment, Request $request)
    {
        try {
            if ($classEnrollment->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'هذا الطلب ليس معلقاً');
            }

            DB::beginTransaction();

            // تحديث status إلى approved
            $classEnrollment->update([
                'status' => 'approved',
                'enrolled_by' => auth()->id(),
                'enrolled_at' => now(),
                'notes' => $request->input('notes', $classEnrollment->notes),
            ]);

            $class = SchoolClass::with(['subjects' => function ($query) {
                $query->where('is_active', true);
            }])->findOrFail($classEnrollment->class_id);

            $result = $this->adminStudentEnrollmentService->provisionSubjectEnrollmentsForApprovedClass(
                $classEnrollment->user_id,
                $class,
                'تم قبول طلب الانضمام للصف: '.$class->name,
                auth()->id()
            );

            DB::commit();

            $this->staffNotificationService->notifyClassEnrollmentDecision(
                $classEnrollment->fresh(),
                auth()->user(),
                true
            );

            $message = "تم قبول طلب الانضمام للصف بنجاح. تم إنشاء {$result['created']} انضمام للمواد";
            if ($result['skipped'] > 0) {
                $message .= " (تم تخطي {$result['skipped']} مادة مسجل فيها مسبقاً)";
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving class enrollment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول الطلب: ' . $e->getMessage());
        }
    }

    /**
     * رفض طلب انضمام للصف
     */
    public function rejectClassEnrollment(ClassEnrollment $classEnrollment, Request $request)
    {
        try {
            if ($classEnrollment->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'هذا الطلب ليس معلقاً');
            }

            $classEnrollment->update([
                'status' => 'rejected',
                'enrolled_by' => auth()->id(),
                'notes' => $request->input('notes', $classEnrollment->notes),
            ]);

            $this->staffNotificationService->notifyClassEnrollmentDecision(
                $classEnrollment->fresh(),
                auth()->user(),
                false
            );

            return redirect()->back()
                ->with('success', 'تم رفض طلب الانضمام للصف بنجاح');

        } catch (\Exception $e) {
            Log::error('Error rejecting class enrollment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض الطلب');
        }
    }

    /**
     * قبول عدة طلبات صف دفعة واحدة
     */
    public function approveMultipleClassEnrollments(Request $request)
    {
        $request->validate([
            'class_enrollment_ids' => 'required|array|min:1',
            'class_enrollment_ids.*' => 'required|exists:class_enrollments,id',
        ]);

        try {
            DB::beginTransaction();

            $classEnrollments = ClassEnrollment::whereIn('id', $request->input('class_enrollment_ids'))
                ->pending()
                ->get();

            if ($classEnrollments->isEmpty()) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'لا توجد طلبات معلقة للقبول');
            }

            $approvedCount = 0;
            $totalCreatedEnrollments = 0;

            foreach ($classEnrollments as $classEnrollment) {
                // تحديث status
                $classEnrollment->update([
                    'status' => 'approved',
                    'enrolled_by' => auth()->id(),
                    'enrolled_at' => now(),
                ]);

                $class = SchoolClass::with(['subjects' => function ($query) {
                    $query->where('is_active', true);
                }])->findOrFail($classEnrollment->class_id);

                $provisioned = $this->adminStudentEnrollmentService->provisionSubjectEnrollmentsForApprovedClass(
                    $classEnrollment->user_id,
                    $class,
                    'تم قبول طلب الانضمام للصف: '.$class->name,
                    auth()->id()
                );
                $totalCreatedEnrollments += $provisioned['created'];

                $approvedCount++;
            }

            DB::commit();

            $message = "تم قبول {$approvedCount} طلب انضمام للصف بنجاح. تم إنشاء {$totalCreatedEnrollments} انضمام للمواد";

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving multiple class enrollments: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول الطلبات');
        }
    }

    /**
     * قبول جميع طلبات الصف المعلّقة المطابقة لنفس فلاتر الصفحة (بحث، طالب، صف).
     */
    public function approveAllPendingClassEnrollments(Request $request): RedirectResponse
    {
        $query = ClassEnrollment::query()->pending();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('user_id')) {
            $query->forUser($request->input('user_id'));
        }
        if ($request->filled('class_id')) {
            $query->forClass($request->input('class_id'));
        }

        $ids = $query->pluck('id')->all();

        if ($ids === []) {
            return redirect()->back()
                ->with('error', 'لا توجد طلبات معلقة للقبول ضمن الفلاتر الحالية.');
        }

        $request->merge(['class_enrollment_ids' => $ids]);

        return $this->approveMultipleClassEnrollments($request);
    }

    /**
     * رفض عدة طلبات صف دفعة واحدة
     */
    public function rejectMultipleClassEnrollments(Request $request)
    {
        $request->validate([
            'class_enrollment_ids' => 'required|array|min:1',
            'class_enrollment_ids.*' => 'required|exists:class_enrollments,id',
        ]);

        try {
            DB::beginTransaction();

            $classEnrollments = ClassEnrollment::whereIn('id', $request->input('class_enrollment_ids'))
                ->pending()
                ->get();

            if ($classEnrollments->isEmpty()) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'لا توجد طلبات معلقة للرفض');
            }

            $count = 0;
            foreach ($classEnrollments as $classEnrollment) {
                $classEnrollment->update([
                    'status' => 'rejected',
                    'enrolled_by' => auth()->id(),
                ]);
                $count++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "تم رفض {$count} طلب انضمام للصف بنجاح");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting multiple class enrollments: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض الطلبات');
        }
    }

    /**
     * حذف نهائي لطلبات الصف المعلقة الأقدم من عدد الأيام المحدد، ضمن فلاتر الصفحة الحالية.
     */
    public function cleanStalePendingClassEnrollments(Request $request): RedirectResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:3650',
            'search' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
        ]);

        $days = (int) $request->input('days');
        $cutoff = now()->subDays($days);

        $query = ClassEnrollment::withTrashed()
            ->pending()
            ->where('created_at', '<', $cutoff);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('user_id')) {
            $query->forUser((int) $request->input('user_id'));
        }
        if ($request->filled('class_id')) {
            $query->forClass((int) $request->input('class_id'));
        }

        $rows = $query->get();
        $count = 0;
        foreach ($rows as $row) {
            $row->forceDelete();
            $count++;
        }

        return redirect()->back()->with(
            'success',
            $count > 0
                ? "تم حذف {$count} طلباً معلقاً أقدم من {$days} يوماً."
                : 'لم يُعثر على طلبات معلقة تطابق الشروط.'
        );
    }

    /**
     * حذف نهائي لطلبات المواد المعلقة الأقدم من عدد الأيام المحدد، ضمن فلاتر الصفحة الحالية.
     */
    public function cleanStalePendingEnrollments(Request $request): RedirectResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:3650',
            'search' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
        ]);

        $days = (int) $request->input('days');
        $cutoff = now()->subDays($days);

        $query = Enrollment::withTrashed()
            ->pending()
            ->where('created_at', '<', $cutoff);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('user_id')) {
            $query->forUser((int) $request->input('user_id'));
        }
        if ($request->filled('subject_id')) {
            $query->forSubject((int) $request->input('subject_id'));
        }

        $rows = $query->get();
        $count = 0;
        foreach ($rows as $row) {
            $row->forceDelete();
            $count++;
        }

        return redirect()->back()->with(
            'success',
            $count > 0
                ? "تم حذف {$count} طلباً معلقاً أقدم من {$days} يوماً."
                : 'لم يُعثر على طلبات معلقة تطابق الشروط.'
        );
    }

    protected function safeInternalRedirectPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return $path;
    }

    protected function redirectAfterClassAssign(Request $request): RedirectResponse
    {
        $path = $this->safeInternalRedirectPath($request->input('redirect_to'));
        if ($path !== null) {
            return redirect($path);
        }

        return redirect()->back();
    }

    protected function friendlyClassAssignDbError(QueryException $e): string
    {
        $msg = $e->getMessage();
        if (str_contains($msg, '1062') && str_contains($msg, 'class_enrollments')) {
            return 'تعذر حفظ ربط الصف: يوجد تعارض مع سجل سابق لنفس الطالب والصف (مثلاً سجل محذوف منطقياً لم يُعالَج). أعد تحميل الصفحة وحاول مرة أخرى، أو راجع جدول انضمامات الصفوف.';
        }
        if (str_contains($msg, '1062') && str_contains($msg, 'enrollments')) {
            return 'تعذر مزامنة مواد الصف: تعارض مع انضمام مادة موجود مسبقاً في قاعدة البيانات. راجع جدول انضمامات المواد لهذا الطالب.';
        }
        if (str_contains($msg, 'Integrity constraint violation')) {
            return 'تعذر إتمام العملية بسبب قيد في قاعدة البيانات. راجع البيانات أو سجلات النظام للتفاصيل.';
        }

        return 'تعذر إتمام ربط الصف أو مزامنة المواد. حاول لاحقاً أو راجع سجلات النظام.';
    }
}