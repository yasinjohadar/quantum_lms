<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AIStudentFeedback;
use Illuminate\Http\Request;

class AIStudentFeedbackController extends Controller
{
    /**
     * عرض ملاحظات الطالب
     */
    public function index(Request $request)
    {
        $feedbacks = AIStudentFeedback::with(['quizAttempt.quiz', 'aiModel'])
            ->where('student_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('student.pages.ai-feedback.index', compact('feedbacks'));
    }

    /**
     * عرض ملاحظة واحدة
     */
    public function show(AIStudentFeedback $aiFeedback)
    {
        // التحقق من أن الملاحظة للطالب الحالي
        if ($aiFeedback->student_id !== auth()->id()) {
            abort(403);
        }

        $aiFeedback->load(['quizAttempt.quiz', 'aiModel']);

        return view('student.pages.ai-feedback.show', compact('aiFeedback'));
    }
}


