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
    public function startAttempt($questionId = null)
    {
        $user = Auth::user();
        $lessonId = request()->input('lesson_id');

        // إذا تم تمرير lesson_id فقط (بدون question_id)، ابدأ بالسؤال الأول
        if (!$questionId && $lessonId) {
            return $this->startLessonQuestions($lessonId);
        }

        $question = Question::where('is_active', true)->findOrFail($questionId);

        // التحقق من أن الطالب مسجل في مادة السؤال (إذا كان مرتبط بدرس)
        if ($lessonId) {
            $lesson = Lesson::with('unit')->findOrFail($lessonId);
            $subject = $lesson->unit->section->subject;
            
            $isEnrolled = $subject->students()
                ->where('users.id', $user->id)
                ->where('enrollments.status', 'active')
                ->exists();

            if (!$isEnrolled) {
                abort(403, 'يجب أن تكون مسجلاً في المادة للإجابة على هذا السؤال');
            }

            // التحقق من التسلسل - يجب إكمال الأسئلة السابقة أولاً
            $allQuestions = Question::whereHas('units', function($query) use ($lesson) {
                    $query->where('units.id', $lesson->unit_id);
                })
                ->where('is_active', true)
                ->orderBy('created_at', 'asc')
                ->get();

            $currentQuestionIndex = $allQuestions->search(function($q) use ($questionId) {
                return $q->id == $questionId;
            });

            if ($currentQuestionIndex !== false && $currentQuestionIndex > 0) {
                // التحقق من إكمال جميع الأسئلة السابقة
                $previousQuestions = $allQuestions->take($currentQuestionIndex);
                $previousAttempts = QuestionAttempt::where('user_id', $user->id)
                    ->where('lesson_id', $lessonId)
                    ->whereIn('question_id', $previousQuestions->pluck('id'))
                    ->whereIn('status', ['completed', 'timed_out'])
                    ->get()
                    ->keyBy('question_id');

                $allPreviousCompleted = $previousQuestions->every(function($q) use ($previousAttempts) {
                    return $previousAttempts->has($q->id);
                });

                if (!$allPreviousCompleted) {
                    // العثور على أول سؤال غير مكتمل
                    $firstIncomplete = $previousQuestions->first(function($q) use ($previousAttempts) {
                        return !$previousAttempts->has($q->id);
                    });

                    if ($firstIncomplete) {
                        // بدء محاولة للسؤال الأول غير مكتمل
                        $defaultTimeLimit = 300; // 5 دقائق
                        $attempt = $this->questionAttemptService->createAttempt(
                            $user->id,
                            $firstIncomplete->id,
                            $lessonId,
                            $defaultTimeLimit
                        );

                        return redirect()->route('student.questions.show', [
                            'question' => $firstIncomplete->id,
                            'attempt' => $attempt->id
                        ]);
                    }
                }
            }
        }

        try {
            // تحديد وقت افتراضي للسؤال (5 دقائق = 300 ثانية)
            $defaultTimeLimit = 300; // 5 دقائق
            
            $attempt = $this->questionAttemptService->createAttempt(
                $user->id,
                $questionId,
                $lessonId,
                $defaultTimeLimit
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
     * بدء الأسئلة المتسلسلة للدرس
     */
    private function startLessonQuestions($lessonId)
    {
        $user = Auth::user();
        $lesson = Lesson::with('unit.section.subject')->findOrFail($lessonId);
        $subject = $lesson->unit->section->subject;
        
        // التحقق من أن الطالب مسجل في المادة
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled && !$lesson->is_free) {
            abort(403, 'يجب أن تكون مسجلاً في المادة للإجابة على الأسئلة');
        }

        // الحصول على جميع أسئلة الدرس مرتبة
        $questions = Question::whereHas('units', function($query) use ($lesson) {
                $query->where('units.id', $lesson->unit_id);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($questions->isEmpty()) {
            return redirect()->route('student.lessons.show', $lessonId)
                ->with('info', 'لا توجد أسئلة متاحة لهذا الدرس.');
        }

        // الحصول على محاولات الطالب
        $attempts = QuestionAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->whereIn('question_id', $questions->pluck('id'))
            ->whereIn('status', ['completed', 'timed_out'])
            ->get()
            ->keyBy('question_id');

        // البحث عن أول سؤال غير مكتمل
        $firstIncomplete = $questions->first(function($question) use ($attempts) {
            return !$attempts->has($question->id);
        });

        // إذا كانت جميع الأسئلة مكتملة، عرض التقرير
        if (!$firstIncomplete) {
            return redirect()->route('student.questions.report', $lessonId)
                ->with('info', 'لقد أكملت جميع الأسئلة. يمكنك مراجعة التقرير النهائي.');
        }

        // بدء محاولة للسؤال الأول غير مكتمل
        try {
            $defaultTimeLimit = 300; // 5 دقائق
            $attempt = $this->questionAttemptService->createAttempt(
                $user->id,
                $firstIncomplete->id,
                $lessonId,
                $defaultTimeLimit
            );

            return redirect()->route('student.questions.show', [
                'question' => $firstIncomplete->id,
                'attempt' => $attempt->id
            ]);
        } catch (\Exception $e) {
            return redirect()->route('student.lessons.show', $lessonId)
                ->with('error', 'حدث خطأ أثناء بدء الأسئلة: ' . $e->getMessage());
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
            ->with('question', 'lesson')
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
                // الحصول على جميع أسئلة الدرس مرتبة
                $lesson = $attempt->lesson;
                $allQuestions = Question::whereHas('units', function($query) use ($lesson) {
                        $query->where('units.id', $lesson->unit_id);
                    })
                    ->where('is_active', true)
                    ->orderBy('created_at', 'asc')
                    ->get();

                // الحصول على محاولات الطالب لجميع أسئلة هذا الدرس
                $allAttempts = QuestionAttempt::where('user_id', $user->id)
                    ->where('lesson_id', $lessonId)
                    ->whereIn('question_id', $allQuestions->pluck('id'))
                    ->whereIn('status', ['completed', 'timed_out'])
                    ->with('answer')
                    ->get()
                    ->keyBy('question_id');

                // التحقق من إكمال جميع الأسئلة
                $allCompleted = $allQuestions->every(function($question) use ($allAttempts) {
                    return $allAttempts->has($question->id);
                });

                if ($allCompleted) {
                    // جميع الأسئلة مكتملة - عرض التقرير النهائي
                    return redirect()->route('student.questions.report', $lessonId)
                        ->with('success', 'تم إكمال جميع الأسئلة!');
                } else {
                    // البحث عن السؤال التالي غير المكتمل
                    $nextQuestion = $allQuestions->first(function($question) use ($allAttempts) {
                        return !$allAttempts->has($question->id);
                    });

                    if ($nextQuestion) {
                        // بدء محاولة للسؤال التالي
                        $defaultTimeLimit = 300; // 5 دقائق
                        $nextAttempt = $this->questionAttemptService->createAttempt(
                            $user->id,
                            $nextQuestion->id,
                            $lessonId,
                            $defaultTimeLimit
                        );

                        return redirect()->route('student.questions.show', [
                            'question' => $nextQuestion->id,
                            'attempt' => $nextAttempt->id
                        ])->with('success', 'تم إرسال إجابتك بنجاح. ' . 
                            ($attempt->is_correct ? 'إجابتك صحيحة!' : 'إجابتك غير صحيحة.'));
                    }
                }

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

    /**
     * عرض التقرير النهائي لجميع أسئلة الدرس
     */
    public function showReport($lessonId)
    {
        $user = Auth::user();
        $lesson = Lesson::with('unit.section.subject')->findOrFail($lessonId);

        // التحقق من أن الطالب مسجل في مادة الدرس
        $subject = $lesson->unit->section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled && !$lesson->is_free) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا التقرير.');
        }

        // الحصول على جميع أسئلة الدرس مرتبة
        $questions = Question::whereHas('units', function($query) use ($lesson) {
                $query->where('units.id', $lesson->unit_id);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->with(['options' => function($query) {
                $query->orderBy('order');
            }])
            ->get();

        // الحصول على محاولات الطالب لجميع الأسئلة
        $attempts = QuestionAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->whereIn('question_id', $questions->pluck('id'))
            ->with('answer')
            ->get()
            ->keyBy('question_id');

        // التحقق من إكمال جميع الأسئلة
        $allCompleted = $questions->every(function($question) use ($attempts) {
            return $attempts->has($question->id) && 
                   in_array($attempts[$question->id]->status, ['completed', 'timed_out']);
        });

        if (!$allCompleted) {
            return redirect()->route('student.lessons.show', $lessonId)
                ->with('error', 'يجب إكمال جميع الأسئلة أولاً لعرض التقرير.');
        }

        // حساب الإحصائيات
        $totalQuestions = $questions->count();
        $correctAnswers = $attempts->filter(function($attempt) {
            return $attempt->is_correct;
        })->count();
        $totalPoints = $questions->sum('default_points');
        $earnedPoints = $attempts->sum(function($attempt) {
            return $attempt->answer ? $attempt->answer->points_earned : 0;
        });
        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

        // تمرير ثوابت الأنواع
        $questionTypes = Question::TYPES;
        $questionTypeIcons = Question::TYPE_ICONS;
        $questionTypeColors = Question::TYPE_COLORS;
        $questionDifficulties = Question::DIFFICULTIES;

        return view('student.pages.questions.report', compact(
            'lesson',
            'subject',
            'questions',
            'attempts',
            'totalQuestions',
            'correctAnswers',
            'totalPoints',
            'earnedPoints',
            'percentage',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties'
        ));
    }
}
