<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Lesson;
use App\Services\QuestionAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentQuestionController extends Controller
{
    protected $questionAttemptService;

    public function __construct(QuestionAttemptService $questionAttemptService)
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->questionAttemptService = $questionAttemptService;
    }

    /**
     * بدء محاولة إجابة على سؤال
     */
    public function startAttempt($questionId)
    {
        $user = Auth::user();
        $question = Question::where('is_active', true)->findOrFail($questionId);

        // التحقق من أن الطالب مسجل في مادة السؤال (إذا كان مرتبط بدرس)
        $lessonId = request()->input('lesson_id');
        if ($lessonId) {
            $lesson = Lesson::findOrFail($lessonId);
            $subject = $lesson->unit->section->subject;
            
            $isEnrolled = $subject->students()
                ->where('users.id', $user->id)
                ->where('enrollments.status', 'active')
                ->exists();

            if (!$isEnrolled) {
                abort(403, 'يجب أن تكون مسجلاً في المادة للإجابة على هذا السؤال');
            }
        }

        try {
            $attempt = $this->questionAttemptService->createAttempt(
                $user->id,
                $questionId,
                $lessonId,
                null // time_limit يمكن إضافته لاحقاً
            );

            return redirect()->route('student.questions.show', [
                'question' => $questionId,
                'attempt' => $attempt->id
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء بدء المحاولة: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الإجابة على سؤال
     */
    public function showQuestion($questionId, $attemptId)
    {
        $user = Auth::user();
        $question = Question::with(['options' => function($query) {
            $query->orderBy('order');
        }])->where('is_active', true)->findOrFail($questionId);

        $attempt = QuestionAttempt::where('user_id', $user->id)
            ->where('question_id', $questionId)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()->back()
                ->with('info', 'هذه المحاولة مكتملة. يمكنك بدء محاولة جديدة.');
        }

        $answer = $attempt->answer()->first();
        $lesson = $attempt->lesson;

        // تمرير ثوابت الأنواع
        $questionTypes = Question::TYPES;
        $questionTypeIcons = Question::TYPE_ICONS;
        $questionTypeColors = Question::TYPE_COLORS;
        $questionDifficulties = Question::DIFFICULTIES;

        return view('student.pages.questions.show', compact(
            'question',
            'attempt',
            'answer',
            'lesson',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties'
        ));
    }

    /**
     * حفظ الإجابة (AJAX)
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuestionAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل محاولة مكتملة'
            ], 400);
        }

        try {
            $answerData = $this->prepareAnswerData($request, $attempt->question);
            $answer = $this->questionAttemptService->saveAnswer($attemptId, $answerData);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإجابة بنجاح',
                'answer' => $answer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإجابة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إرسال الإجابة النهائية
     */
    public function submitAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuestionAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()->back()
                ->with('error', 'لا يمكن إرسال محاولة مكتملة');
        }

        try {
            // حفظ الإجابة النهائية إذا كانت موجودة
            if ($request->has('answer')) {
                $answerData = $this->prepareAnswerData($request, $attempt->question);
                $this->questionAttemptService->saveAnswer($attemptId, $answerData);
            }

            // إرسال الإجابة
            $attempt = $this->questionAttemptService->submitAnswer($attemptId);

            $lessonId = $attempt->lesson_id;
            if ($lessonId) {
                return redirect()->route('student.lessons.show', $lessonId)
                    ->with('success', 'تم إرسال إجابتك بنجاح. ' . 
                        ($attempt->is_correct ? 'إجابتك صحيحة!' : 'إجابتك غير صحيحة.'));
            }

            return redirect()->back()
                ->with('success', 'تم إرسال إجابتك بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الإجابة: ' . $e->getMessage());
        }
    }

    /**
     * API للحصول على الوقت المتبقي
     */
    public function getRemainingTime($attemptId)
    {
        $user = Auth::user();
        $attempt = QuestionAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'المحاولة غير جارية'
            ], 400);
        }

        $remaining = $attempt->remaining_time;

        // التحقق من انتهاء الوقت
        if ($remaining !== null && $remaining <= 0) {
            $this->questionAttemptService->submitAnswer($attemptId);
            return response()->json([
                'success' => false,
                'timeout' => true,
                'message' => 'انتهى الوقت'
            ]);
        }

        return response()->json([
            'success' => true,
            'remaining' => $remaining,
            'formatted' => $attempt->formatted_remaining_time
        ]);
    }

    /**
     * تحضير بيانات الإجابة حسب نوع السؤال
     */
    private function prepareAnswerData(Request $request, Question $question): array
    {
        $data = [];

        switch ($question->type) {
            case 'single_choice':
                $data['selected_options'] = [$request->input('option_id')];
                break;

            case 'multiple_choice':
                $data['selected_options'] = $request->input('option_ids', []);
                break;

            case 'true_false':
                $data['selected_options'] = [$request->input('option_id')];
                break;

            case 'short_answer':
            case 'essay':
                $data['answer_text'] = $request->input('answer_text');
                break;

            case 'matching':
                $data['matching_pairs'] = $request->input('matching_pairs', []);
                break;

            case 'ordering':
                $data['ordering'] = $request->input('ordering', []);
                break;

            case 'numerical':
                $data['numeric_answer'] = $request->input('numeric_answer');
                break;

            case 'fill_blanks':
                $data['fill_blanks_answers'] = $request->input('fill_blanks_answers', []);
                break;

            case 'drag_drop':
                $data['answer'] = $request->input('answer');
                break;
        }

        // حفظ ترتيب الخيارات إذا كان موجوداً
        if ($request->has('options_order')) {
            $data['options_order'] = $request->input('options_order');
        }

        return $data;
    }
}
