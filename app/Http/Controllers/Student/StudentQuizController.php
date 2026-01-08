<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Question;
use App\Services\GamificationService;
use App\Services\AuditLogService;
use App\Services\AnalyticsService;
use App\Events\QuizStarted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class StudentQuizController extends Controller
{
    protected AuditLogService $auditLogService;
    protected AnalyticsService $analyticsService;

    public function __construct(AuditLogService $auditLogService, AnalyticsService $analyticsService)
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->auditLogService = $auditLogService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * بدء اختبار
     */
    public function startQuiz($quizId)
    {
        // #region agent log - Hypothesis A: Entry point
        $logDataA = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'StudentQuizController.php:35',
            'message' => 'startQuiz method called',
            'data' => [
                'quiz_id' => $quizId,
                'user_id' => Auth::id(),
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataA) . "\n", FILE_APPEND);
        // #endregion
        
        $user = Auth::user();
        $quiz = Quiz::with(['questions' => function($query) {
            $query->orderBy('quiz_questions.order');
        }])->where('is_active', true)
        ->where('is_published', true)
        ->findOrFail($quizId);

        // #region agent log - Hypothesis B: Quiz loaded
        $logDataB = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B',
            'location' => 'StudentQuizController.php:42',
            'message' => 'Quiz loaded',
            'data' => [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'is_active' => $quiz->is_active,
                'is_published' => $quiz->is_published,
                'questions_count' => $quiz->questions->count(),
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataB) . "\n", FILE_APPEND);
        // #endregion

        // التحقق من إمكانية بدء الاختبار
        $canAttempt = $quiz->canUserAttempt($user);
        
        // #region agent log - Hypothesis C: canUserAttempt check
        $logDataC = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'C',
            'location' => 'StudentQuizController.php:45',
            'message' => 'canUserAttempt result',
            'data' => [
                'can' => $canAttempt['can'] ?? false,
                'reason' => $canAttempt['reason'] ?? null,
                'canAttempt_full' => $canAttempt,
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataC) . "\n", FILE_APPEND);
        // #endregion
        
        if (!$canAttempt['can']) {
            // #region agent log - Hypothesis D: Redirect back due to canAttempt failure
            $logDataD = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'StudentQuizController.php:47',
                'message' => 'Redirecting back - canAttempt failed',
                'data' => [
                    'reason' => $canAttempt['reason'] ?? 'Unknown reason',
                ],
                'timestamp' => time() * 1000
            ];
            file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataD) . "\n", FILE_APPEND);
            // #endregion
            return redirect()->back()
                ->with('error', $canAttempt['reason']);
        }

        // التحقق من وجود محاولة جارية
        $inProgressAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->where('status', 'in_progress')
            ->first();

        // #region agent log - Hypothesis G: In-progress attempt check
        $logDataG = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'G',
            'location' => 'StudentQuizController.php:119',
            'message' => 'Checking for in-progress attempt',
            'data' => [
                'has_in_progress_attempt' => $inProgressAttempt !== null,
                'in_progress_attempt_id' => $inProgressAttempt ? $inProgressAttempt->id : null,
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataG) . "\n", FILE_APPEND);
        // #endregion

        if ($inProgressAttempt) {
            // #region agent log - Hypothesis H: Redirect to existing attempt
            $logDataH = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'H',
                'location' => 'StudentQuizController.php:130',
                'message' => 'Redirecting to existing in-progress attempt',
                'data' => [
                    'quiz_id' => $quizId,
                    'attempt_id' => $inProgressAttempt->id,
                ],
                'timestamp' => time() * 1000
            ];
            file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataH) . "\n", FILE_APPEND);
            // #endregion
            return redirect()->route('student.quizzes.show', [
                'quiz' => $quizId,
                'attempt' => $inProgressAttempt->id
            ]);
        }

        try {
            DB::beginTransaction();

            // الحصول على آخر رقم محاولة
            $lastAttempt = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quizId)
                ->orderBy('attempt_number', 'desc')
                ->first();

            $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

            // إنشاء محاولة جديدة
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quizId,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
                'status' => 'in_progress',
                'max_score' => $quiz->total_points,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // حفظ ترتيب الأسئلة (مع خلط إذا كان مطلوباً)
            $questionIds = $quiz->questions->pluck('id')->toArray();
            if ($quiz->shuffle_questions) {
                shuffle($questionIds);
            }
            $attempt->question_order = $questionIds;
            $attempt->save();

            // إرسال Event
            Event::dispatch(new QuizStarted($user, $quiz, [
                'attempt_id' => $attempt->id,
                'time_limit' => $quiz->time_limit,
            ]));

            // تسجيل حدث في Analytics
            $this->analyticsService->trackEvent('start_quiz', $user->id, [
                'quiz_id' => $quiz->id,
                'subject_id' => $quiz->subject_id,
                'attempt_id' => $attempt->id,
            ]);

            DB::commit();

            // #region agent log - Hypothesis E: Success redirect
            $logDataE = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'E',
                'location' => 'StudentQuizController.php:175',
                'message' => 'Redirecting to quiz show page',
                'data' => [
                    'quiz_id' => $quizId,
                    'attempt_id' => $attempt->id,
                    'redirect_url' => route('student.quizzes.show', ['quiz' => $quizId, 'attempt' => $attempt->id]),
                ],
                'timestamp' => time() * 1000
            ];
            file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataE) . "\n", FILE_APPEND);
            // #endregion

            return redirect()->route('student.quizzes.show', [
                'quiz' => $quizId,
                'attempt' => $attempt->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // #region agent log - Hypothesis F: Exception caught
            $logDataF = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'F',
                'location' => 'StudentQuizController.php:181',
                'message' => 'Exception caught in startQuiz',
                'data' => [
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'exception_trace' => substr($e->getTraceAsString(), 0, 500),
                ],
                'timestamp' => time() * 1000
            ];
            file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataF) . "\n", FILE_APPEND);
            // #endregion
            
            Log::error('Error starting quiz: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الاختبار
     */
    public function showQuiz($quizId, $attemptId)
    {
        // #region agent log - Hypothesis I: showQuiz entry
        $logDataI = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'I',
            'location' => 'StudentQuizController.php:259',
            'message' => 'showQuiz method called',
            'data' => [
                'quiz_id' => $quizId,
                'attempt_id' => $attemptId,
                'user_id' => Auth::id(),
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataI) . "\n", FILE_APPEND);
        // #endregion
        
        $user = Auth::user();
        $quiz = Quiz::with(['questions.options' => function($query) {
            $query->orderBy('order');
        }])->where('is_active', true)
        ->findOrFail($quizId);

        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->findOrFail($attemptId);
        
        // #region agent log - Hypothesis J: Quiz and attempt loaded
        $logDataJ = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'J',
            'location' => 'StudentQuizController.php:270',
            'message' => 'Quiz and attempt loaded',
            'data' => [
                'quiz_id' => $quiz->id,
                'attempt_id' => $attempt->id,
                'attempt_status' => $attempt->status,
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataJ) . "\n", FILE_APPEND);
        // #endregion

        if ($attempt->status !== 'in_progress') {
            return redirect()->back()
                ->with('info', 'هذه المحاولة مكتملة. يمكنك بدء محاولة جديدة.');
        }

        // الحصول على الأسئلة بالترتيب المحفوظ
        $questionIds = $attempt->question_order ?? $quiz->questions->pluck('id')->toArray();
        $questions = Question::whereIn('id', $questionIds)
            ->with(['options' => function($query) {
                $query->orderBy('order');
            }])
            ->get()
            ->sortBy(function($question) use ($questionIds) {
                return array_search($question->id, $questionIds);
            })
            ->values();

        // الحصول على الإجابات الحالية
        $answers = $attempt->answers()->with('question')->get()->keyBy('question_id');

        // تمرير ثوابت الأنواع
        $questionTypes = Question::TYPES;
        $questionTypeIcons = Question::TYPE_ICONS;
        $questionTypeColors = Question::TYPE_COLORS;
        $questionDifficulties = Question::DIFFICULTIES;

        return view('student.pages.quizzes.show', compact(
            'quiz',
            'attempt',
            'questions',
            'answers',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties'
        ));
    }

    /**
     * حفظ إجابة (AJAX)
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل محاولة مكتملة'
            ], 400);
        }

        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        try {
            $question = Question::findOrFail($request->question_id);
            $answerData = $this->prepareAnswerData($request, $question);

            $answer = QuizAnswer::updateOrCreate(
                [
                    'attempt_id' => $attemptId,
                    'question_id' => $request->question_id,
                ],
                array_merge($answerData, [
                    'answered_at' => now(),
                    'time_spent' => $attempt->started_at->diffInSeconds(now()),
                    'max_points' => $question->default_points ?? 0,
                ])
            );

            $attempt->updateActivity();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإجابة بنجاح',
                'answer' => [
                    'selected_options' => $answer->selected_options,
                    'answer_text' => $answer->answer_text,
                    'numeric_answer' => $answer->numeric_answer,
                    'matching_pairs' => $answer->matching_pairs,
                    'ordering' => $answer->ordering,
                    'fill_blanks_answers' => $answer->fill_blanks_answers,
                    'drag_drop_assignments' => $answer->drag_drop_assignments,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving quiz answer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإجابة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إرسال الاختبار
     */
    public function submitQuiz(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::with('quiz')->where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()->back()
                ->with('error', 'لا يمكن إرسال محاولة مكتملة');
        }

        try {
            DB::beginTransaction();

            // حفظ آخر إجابة إذا كانت موجودة
            if ($request->has('question_id')) {
                // #region agent log - Hypothesis E: Saving last answer
                $logDataE = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'E',
                    'location' => 'StudentQuizController.php:421',
                    'message' => 'Saving last answer from request',
                    'data' => [
                        'question_id' => $request->question_id,
                        'request_data' => $request->all(),
                    ],
                    'timestamp' => time() * 1000
                ];
                file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataE) . "\n", FILE_APPEND);
                // #endregion
                
                $question = Question::findOrFail($request->question_id);
                $answerData = $this->prepareAnswerData($request, $question);
                
                // #region agent log - Hypothesis F: Prepared answer data
                $logDataF = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'F',
                    'location' => 'StudentQuizController.php:430',
                    'message' => 'Prepared answer data',
                    'data' => [
                        'question_id' => $question->id,
                        'question_type' => $question->type,
                        'answer_data' => $answerData,
                    ],
                    'timestamp' => time() * 1000
                ];
                file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataF) . "\n", FILE_APPEND);
                // #endregion
                
                QuizAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attemptId,
                        'question_id' => $request->question_id,
                    ],
                    array_merge($answerData, [
                        'answered_at' => now(),
                        'max_points' => $question->default_points ?? 0,
                    ])
                );
            }

            // #region agent log - Hypothesis A: Before grading answers
            $logDataA = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'StudentQuizController.php:437',
                'message' => 'Before grading answers',
                'data' => [
                    'attempt_id' => $attemptId,
                    'answers_count' => $attempt->answers()->count(),
                    'ungraded_count' => $attempt->answers()->where('is_graded', false)->count(),
                ],
                'timestamp' => time() * 1000
            ];
            file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataA) . "\n", FILE_APPEND);
            // #endregion

            // تصحيح جميع الإجابات غير المصححة
            $answers = $attempt->answers()->with('question')->get();
            foreach ($answers as $answer) {
                if (!$answer->is_graded && !$answer->needs_manual_grading) {
                    // #region agent log - Hypothesis B: Grading answer
                    $logDataB = [
                        'sessionId' => 'debug-session',
                        'runId' => 'run1',
                        'hypothesisId' => 'B',
                        'location' => 'StudentQuizController.php:450',
                        'message' => 'Grading answer',
                        'data' => [
                            'answer_id' => $answer->id,
                            'question_id' => $answer->question_id,
                            'question_type' => $answer->question->type ?? null,
                            'has_answer' => $answer->answer !== null,
                            'selected_options' => $answer->selected_options,
                            'answer_text' => $answer->answer_text,
                            'numeric_answer' => $answer->numeric_answer,
                            'answer_raw' => $answer->toArray(),
                        ],
                        'timestamp' => time() * 1000
                    ];
                    file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataB) . "\n", FILE_APPEND);
                    // #endregion
                    
                    $answer->autoGrade();
                    
                    // #region agent log - Hypothesis C: After grading answer
                    $logDataC = [
                        'sessionId' => 'debug-session',
                        'runId' => 'run1',
                        'hypothesisId' => 'C',
                        'location' => 'StudentQuizController.php:465',
                        'message' => 'After grading answer',
                        'data' => [
                            'answer_id' => $answer->id,
                            'is_graded' => $answer->is_graded,
                            'is_correct' => $answer->is_correct,
                            'points_earned' => $answer->points_earned,
                            'max_points' => $answer->max_points,
                        ],
                        'timestamp' => time() * 1000
                    ];
                    file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataC) . "\n", FILE_APPEND);
                    // #endregion
                }
            }

            // إنهاء المحاولة
            $attempt->finish();
            
            // #region agent log - Hypothesis D: After finish
            $logDataD = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'StudentQuizController.php:480',
                'message' => 'After finish attempt',
                'data' => [
                    'attempt_id' => $attempt->id,
                    'score' => $attempt->score,
                    'max_score' => $attempt->max_score,
                    'percentage' => $attempt->percentage,
                    'questions_correct' => $attempt->questions_correct,
                    'questions_wrong' => $attempt->questions_wrong,
                ],
                'timestamp' => time() * 1000
            ];
            file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataD) . "\n", FILE_APPEND);
            // #endregion

            // ربط مع نظام التحفيز
            $gamificationService = app(GamificationService::class);
            $gamificationService->processQuizCompletion($attempt);

            // تسجيل حدث في Analytics
            $this->analyticsService->trackEvent('complete_quiz', $user->id, [
                'quiz_id' => $attempt->quiz_id,
                'subject_id' => $attempt->quiz->subject_id ?? null,
                'attempt_id' => $attempt->id,
                'score' => $attempt->score,
                'percentage' => $attempt->percentage,
                'passed' => $attempt->passed,
            ]);

            DB::commit();

            return redirect()->route('student.quizzes.result', [
                'quiz' => $attempt->quiz_id,
                'attempt' => $attemptId
            ])->with('success', 'تم إرسال الاختبار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting quiz: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * عرض نتيجة الاختبار
     */
    public function showResult($quizId, $attemptId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);
        
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->findOrFail($attemptId);

        $answers = $attempt->answers()->with('question.options')->get();

        return view('student.pages.quizzes.result', compact('quiz', 'attempt', 'answers'));
    }

    /**
     * API للحصول على الوقت المتبقي
     */
    public function getRemainingTime($attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::where('user_id', $user->id)
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
            $attempt->timeout();

            // تسجيل في AuditLog أن المحاولة انتهت لانتهاء الوقت
            $this->auditLogService->logQuizSecurity($user, 'quiz_timeout', [
                'quiz_id' => $attempt->quiz_id,
                'attempt_id' => $attempt->id,
            ]);

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
                // Support both option_id (legacy) and selected_options (new)
                $selectedOptions = $request->input('selected_options', []);
                if (empty($selectedOptions)) {
                    $optionId = $request->input('option_id');
                    $selectedOptions = $optionId ? [$optionId] : [];
                }
                if (!is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                // Filter out null/empty values
                $selectedOptions = array_filter($selectedOptions, function($v) {
                    return $v !== null && $v !== '';
                });
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'multiple_choice':
                $selectedOptions = $request->input('selected_options', []);
                if (!is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                // Filter out null/empty values
                $selectedOptions = array_filter($selectedOptions, function($v) {
                    return $v !== null && $v !== '';
                });
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'true_false':
                // Support both option_id (legacy) and selected_options (new)
                $selectedOptions = $request->input('selected_options', []);
                if (empty($selectedOptions)) {
                    $optionId = $request->input('option_id');
                    $selectedOptions = $optionId ? [$optionId] : [];
                }
                if (!is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                // Filter out null/empty values
                $selectedOptions = array_filter($selectedOptions, function($v) {
                    return $v !== null && $v !== '';
                });
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'short_answer':
            case 'essay':
                $data['answer_text'] = $request->input('answer_text');
                break;

            case 'matching':
                $data['matching_pairs'] = $request->input('matching_pairs', []);
                break;

            case 'ordering':
                $ordering = $request->input('ordering');
                if (is_string($ordering)) {
                    $ordering = explode(',', $ordering);
                    $ordering = array_filter(array_map('trim', $ordering));
                }
                $data['ordering'] = $ordering ?? [];
                break;

            case 'numerical':
                $data['numeric_answer'] = $request->input('numeric_answer');
                break;

            case 'fill_blanks':
                $fillBlanksAnswers = $request->input('fill_blanks_answers', []);
                // Ensure array format and preserve keys
                if (!is_array($fillBlanksAnswers)) {
                    $fillBlanksAnswers = [];
                }
                // Convert keys to integers for proper indexing
                $result = [];
                foreach ($fillBlanksAnswers as $key => $value) {
                    $result[(int)$key] = $value;
                }
                $data['fill_blanks_answers'] = $result;
                break;

            case 'drag_drop':
                $dragDropAssignments = $request->input('drag_drop_assignments');
                if (is_string($dragDropAssignments)) {
                    $dragDropAssignments = json_decode($dragDropAssignments, true);
                }
                $data['drag_drop_assignments'] = $dragDropAssignments ?? [];
                break;
        }

        // حفظ ترتيب الخيارات إذا كان موجوداً
        if ($request->has('options_order')) {
            $data['options_order'] = $request->input('options_order');
        }

        return $data;
    }
}
