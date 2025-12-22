<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\AssignmentGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssignmentSubmissionController extends Controller
{
    public function __construct(
        private AssignmentGradingService $gradingService
    ) {}

    /**
     * عرض قائمة إرسالات واجب معين
     */
    public function index(Assignment $assignment, Request $request)
    {
        $query = $assignment->submissions()
            ->with(['student', 'files', 'answers', 'grades']);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // فلترة حسب التأخير
        if ($request->filled('is_late')) {
            $query->where('is_late', $request->boolean('is_late'));
        }

        $submissions = $query->latest('submitted_at')->paginate(20);

        return view('admin.pages.assignments.submissions.index', compact('assignment', 'submissions'));
    }

    /**
     * عرض تفاصيل إرسال
     */
    public function show(Assignment $assignment, AssignmentSubmission $submission)
    {
        $submission->load(['student', 'files', 'answers.question', 'grades.grader']);

        return view('admin.pages.assignments.submissions.show', compact('assignment', 'submission'));
    }

    /**
     * تصحيح إرسال
     */
    public function grade(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'manual_score' => 'nullable|numeric|min:0',
            'criteria' => 'nullable|array',
            'comments' => 'nullable|string',
            'feedback' => 'nullable|string',
        ]);

        try {
            $this->gradingService->gradeSubmission($submission, $validated, Auth::user());

            return redirect()->back()
                ->with('success', 'تم تصحيح الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error grading assignment submission: ' . $e->getMessage(), [
                'submission_id' => $submission->id,
                'request' => $validated,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تصحيح الواجب: ' . $e->getMessage());
        }
    }

    /**
     * إرجاع الواجب للطالب
     */
    public function return(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'feedback' => 'nullable|string',
        ]);

        try {
            $submission->update([
                'status' => AssignmentSubmission::STATUS_RETURNED,
                'feedback' => $validated['feedback'] ?? null,
            ]);

            return redirect()->back()
                ->with('success', 'تم إرجاع الواجب للطالب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error returning assignment submission: ' . $e->getMessage(), [
                'submission_id' => $submission->id,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرجاع الواجب: ' . $e->getMessage());
        }
    }

    /**
     * تصدير نتائج الواجب
     */
    public function export(Assignment $assignment)
    {
        try {
            $results = $this->gradingService->exportResults($assignment);

            // TODO: إضافة تصدير Excel/PDF
            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting assignment results: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تصدير النتائج: ' . $e->getMessage(),
            ], 500);
        }
    }
}
