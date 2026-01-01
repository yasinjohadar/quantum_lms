<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIStudentFeedback;
use App\Models\AIModel;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Services\AI\AIStudentFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIStudentFeedbackController extends Controller
{
    public function __construct(
        private AIStudentFeedbackService $feedbackService
    ) {}

    /**
     * توليد ملاحظات للطالب
     */
    public function generateFeedback(Request $request, User $student)
    {
        $validated = $request->validate([
            'quiz_attempt_id' => 'nullable|exists:quiz_attempts,id',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'feedback_type' => 'nullable|in:performance,general,improvement',
        ]);

        try {
            $attempt = $validated['quiz_attempt_id'] 
                ? QuizAttempt::with('quiz')->find($validated['quiz_attempt_id'])
                : null;

            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $feedback = $this->feedbackService->generateFeedback($student, $attempt, $model);

            return redirect()->route('admin.ai.student-feedback.show', $feedback)
                ->with('success', 'تم توليد الملاحظات بنجاح');

        } catch (\Exception $e) {
            Log::error('Error generating feedback: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء توليد الملاحظات: ' . $e->getMessage());
        }
    }

    /**
     * عرض ملاحظات الطالب
     */
    public function index(Request $request)
    {
        $query = AIStudentFeedback::with(['student', 'quizAttempt.quiz', 'aiModel']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('feedback_type')) {
            $query->where('feedback_type', $request->feedback_type);
        }

        $feedbacks = $query->latest()->paginate(20);

        return view('admin.pages.ai.student-feedback.index', compact('feedbacks'));
    }

    /**
     * عرض ملاحظة واحدة
     */
    public function show(AIStudentFeedback $studentFeedback)
    {
        $studentFeedback->load(['student', 'quizAttempt.quiz', 'aiModel']);

        return view('admin.pages.ai.student-feedback.show', compact('studentFeedback'));
    }
}



