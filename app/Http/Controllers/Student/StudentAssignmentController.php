<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\AssignmentService;
use App\Services\AssignmentSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentAssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignmentService,
        private AssignmentSubmissionService $submissionService
    ) {}

    /**
     * عرض قائمة الواجبات
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        
        $filters = [
            'type' => $request->get('type', 'all'),
            'status' => $request->get('status', 'all'),
            'per_page' => $request->get('per_page', 20),
        ];

        $assignments = $this->assignmentService->getStudentAssignments($student, $filters);

        return view('student.pages.assignments.index', compact('assignments'));
    }

    /**
     * عرض تفاصيل واجب
     */
    public function show(Assignment $assignment)
    {
        $student = Auth::user();

        // التحقق من إمكانية الوصول
        if (!$this->assignmentService->canStudentSubmit($assignment, $student)) {
            return redirect()->route('student.assignments.index')
                ->with('error', 'لا يمكنك الوصول لهذا الواجب');
        }

        // جلب آخر إرسال (إن وجد)
        $lastSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        $assignment->load(['questions', 'assignable']);

        return view('student.pages.assignments.show', compact('assignment', 'lastSubmission'));
    }

    /**
     * إرسال واجب
     */
    public function submit(Request $request, Assignment $assignment)
    {
        $student = Auth::user();

        // التحقق من إمكانية الإرسال
        if (!$this->assignmentService->canStudentSubmit($assignment, $student)) {
            return redirect()->back()
                ->with('error', 'لا يمكنك إرسال هذا الواجب');
        }

        $validated = $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|max:' . ($assignment->max_file_size * 1024),
            'answers' => 'nullable|array',
            'answers.*' => 'required',
        ]);

        // التحقق من عدد الملفات
        if (isset($validated['files']) && count($validated['files']) > $assignment->max_files_per_submission) {
            return redirect()->back()
                ->with('error', "عدد الملفات يتجاوز الحد المسموح ({$assignment->max_files_per_submission} ملف)");
        }

        try {
            $submission = $this->submissionService->submitAssignment($assignment, $student, $validated);

            return redirect()->route('student.assignments.submission', $assignment)
                ->with('success', 'تم إرسال الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error submitting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الواجب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * إعادة إرسال واجب
     */
    public function resubmit(Request $request, Assignment $assignment)
    {
        $student = Auth::user();

        // التحقق من إمكانية إعادة الإرسال
        if (!$this->submissionService->canResubmit($assignment, $student)) {
            return redirect()->back()
                ->with('error', 'لا يمكنك إعادة إرسال هذا الواجب');
        }

        $validated = $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|max:' . ($assignment->max_file_size * 1024),
            'answers' => 'nullable|array',
            'answers.*' => 'required',
        ]);

        try {
            $submission = $this->submissionService->resubmitAssignment($assignment, $student, $validated);

            return redirect()->route('student.assignments.submission', $assignment)
                ->with('success', 'تم إعادة إرسال الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error resubmitting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إعادة إرسال الواجب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض إرسال سابق
     */
    public function viewSubmission(Assignment $assignment)
    {
        $student = Auth::user();

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->latest()
            ->firstOrFail();

        $submission->load(['files', 'answers.question', 'grades.grader']);

        return view('student.pages.assignments.submission', compact('assignment', 'submission'));
    }

    /**
     * تحميل ملف من إرسال
     */
    public function downloadFile(Assignment $assignment, AssignmentSubmission $submission, $fileId)
    {
        $student = Auth::user();

        // التحقق من أن الملف يخص الطالب
        if ($submission->student_id !== $student->id) {
            abort(403, 'غير مصرح لك بالوصول لهذا الملف');
        }

        $file = $submission->files()->findOrFail($fileId);

        if (!$file->fileExists()) {
            abort(404, 'الملف غير موجود');
        }

        return response()->download(
            storage_path('app/public/' . $file->file_path),
            $file->file_name
        );
    }
}
