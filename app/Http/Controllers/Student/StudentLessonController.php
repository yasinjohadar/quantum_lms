<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\LessonCompletion;
use App\Services\GamificationService;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentLessonController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->analyticsService = $analyticsService;
    }

    /**
     * عرض الصفوف المنضم إليها الطالب مع المواد داخل كل صف
     */
    public function classes()
    {
        $user = Auth::user();
        
        // الحصول على المواد المسجل فيها الطالب
        $subjects = $user->subjects()
            ->with(['schoolClass.stage', 'enrollments' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();
        
        // تجميع المواد حسب الصف
        $classes = collect();
        
        foreach ($subjects as $subject) {
            if ($subject->schoolClass) {
                $classId = $subject->schoolClass->id;
                
                if (!$classes->has($classId)) {
                    $classes->put($classId, [
                        'class' => $subject->schoolClass,
                        'subjects' => collect()
                    ]);
                }
                
                $classes[$classId]['subjects']->push($subject);
            }
        }
        
        // ترتيب الصفوف حسب order
        $classes = $classes->sortBy(function($item) {
            return $item['class']->order ?? 999;
        });
        
        return view('student.pages.lessons.classes', compact('classes'));
    }
    
    /**
     * عرض قائمة المواد المسجل فيها الطالب
     */
    public function subjects()
    {
        $user = Auth::user();
        
        // الحصول على المواد المسجل فيها الطالب
        $subjects = $user->subjects()
            ->with(['schoolClass.stage', 'enrollments' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();
        
        return view('student.pages.lessons.subjects', compact('subjects'));
    }
    
    /**
     * عرض محتوى المادة (أقسام، وحدات، دروس)
     */
    public function showSubject($subjectId)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في هذه المادة
        $subject = Subject::with(['schoolClass.stage'])
            ->whereHas('students', function($query) use ($user) {
                $query->where('users.id', $user->id)
                      ->where('enrollments.status', 'active');
            })
            ->findOrFail($subjectId);
        
        // تحميل الأقسام مع الوحدات والدروس والاختبارات
        $sections = $subject->sections()
            ->with([
                'units.lessons' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('order');
                },
                // اختبارات عامة للوحدة
                'units.unitQuizzes' => function($query) {
                    $query->where('is_published', true)
                          ->withCount('questions')
                          ->with(['attempts' => function($q) {
                              $q->where('user_id', Auth::id());
                          }])
                          ->orderBy('order');
                },
                // تحميل اختبارات الدرس المنشورة لكل درس
                'units.lessons.quizzes' => function($query) {
                    $query->where('is_published', true)
                          ->withCount('questions')
                          ->with(['attempts' => function($q) {
                              $q->where('user_id', Auth::id());
                          }])
                          ->orderBy('order');
                },
                'units.questions' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('created_at', 'desc');
                }
            ])
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        
        // #region agent log - Hypothesis A: Questions not loaded in eager loading
        $allUnits = $sections->flatMap(function($s) { return $s->units; });
        $unitsWithQuestions = $allUnits->map(function($u) { 
            return [
                'unit_id' => $u->id, 
                'unit_title' => $u->title,
                'questions_count' => $u->questions ? $u->questions->count() : 0,
                'questions_ids' => $u->questions ? $u->questions->pluck('id')->toArray() : []
            ]; 
        })->toArray();
        $logDataA = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'StudentLessonController.php:107',
            'message' => 'Sections loaded - checking if questions are loaded',
            'data' => [
                'subject_id' => $subjectId,
                'sections_count' => $sections->count(),
                'total_units' => $allUnits->count(),
                'units_with_questions' => $unitsWithQuestions,
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataA) . "\n", FILE_APPEND);
        // #endregion
        
        // #region agent log - Hypothesis B: Check if questions are active
        $allQuestions = $allUnits->flatMap(function($u) { return $u->questions ?? collect(); });
        $activeQuestions = $allQuestions->where('is_active', true);
        $logDataB = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B',
            'location' => 'StudentLessonController.php:107',
            'message' => 'Checking question active status',
            'data' => [
                'total_questions' => $allQuestions->count(),
                'active_questions' => $activeQuestions->count(),
                'inactive_questions' => $allQuestions->where('is_active', false)->count(),
                'question_ids_and_status' => $allQuestions->map(function($q) { return ['id' => $q->id, 'is_active' => $q->is_active]; })->toArray(),
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataB) . "\n", FILE_APPEND);
        // #endregion
        
        // #region agent log - Hypothesis C: Check question-unit relationship
        $questionUnitRelations = [];
        foreach($allUnits as $unit) {
            $unitQuestions = \App\Models\Question::whereHas('units', function($q) use ($unit) {
                $q->where('units.id', $unit->id);
            })->where('is_active', true)->get();
            $questionUnitRelations[] = [
                'unit_id' => $unit->id,
                'questions_via_relationship' => $unitQuestions->count(),
                'questions_via_eager_loading' => $unit->questions ? $unit->questions->count() : 0,
            ];
        }
        $logDataC = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'C',
            'location' => 'StudentLessonController.php:107',
            'message' => 'Checking question-unit relationship',
            'data' => [
                'question_unit_relations' => $questionUnitRelations,
            ],
            'timestamp' => time() * 1000
        ];
        file_put_contents('d:\\Web Programming\\Projects\\Quantum LMS1\\.cursor\\debug.log', json_encode($logDataC) . "\n", FILE_APPEND);
        // #endregion
        
        // إحصائيات المادة
        $stats = [
            'total_sections' => $sections->count(),
            'total_units' => $sections->sum(function($section) {
                return $section->units->count();
            }),
            'total_lessons' => $sections->sum(function($section) {
                return $section->units->sum(function($unit) {
                    return $unit->lessons->count();
                });
            }),
        ];
        
        return view('student.pages.lessons.subject-show', compact('subject', 'sections', 'stats'));
    }
    
    /**
     * عرض الدرس مع الفيديو والمرفقات
     */
    public function showLesson($lessonId)
    {
        $user = Auth::user();
        
        // تحميل الدرس مع العلاقات
        $lesson = Lesson::with([
            'unit.section.subject',
            'attachments' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('order');
            }
        ])->findOrFail($lessonId);
        
        // التحقق من أن الطالب مسجل في مادة الدرس
        $subject = $lesson->unit->section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();
        
        if (!$isEnrolled && !$lesson->is_free) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا الدرس. يجب أن تكون مسجلاً في المادة.');
        }
        
        // تحميل جميع الأقسام والوحدات والدروس والاختبارات (للعرض في الأكورديون)
        $sections = $subject->sections()
            ->with([
                'units.lessons' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('order');
                },
                // اختبارات عامة للوحدة
                'units.unitQuizzes' => function($query) {
                    $query->where('is_published', true)
                          ->withCount('questions')
                          ->with(['attempts' => function($q) {
                              $q->where('user_id', Auth::id());
                          }])
                          ->orderBy('order');
                },
                // تحميل اختبارات الدرس المنشورة لكل درس
                'units.lessons.quizzes' => function($query) {
                    $query->where('is_published', true)
                          ->withCount('questions')
                          ->with(['attempts' => function($q) {
                              $q->where('user_id', Auth::id());
                          }])
                          ->orderBy('order');
                }
            ])
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        
        // الحصول على الدروس الأخرى في نفس الوحدة
        $unitLessons = $lesson->unit->lessons()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        
        // العثور على الدرس التالي والسابق
        $currentIndex = $unitLessons->search(function($item) use ($lesson) {
            return $item->id === $lesson->id;
        });
        
        $previousLesson = $currentIndex > 0 ? $unitLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $unitLessons->count() - 1 ? $unitLessons[$currentIndex + 1] : null;
        
        // اختبارات الدرس الحالي فقط
        $lessonQuizzes = Quiz::where('lesson_id', $lesson->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->with(['questions' => function($query) {
                $query->orderBy('quiz_questions.order');
            }])
            ->orderBy('order')
            ->get();

        // اختبارات الوحدة العامة (غير مرتبطة بدرس محدد)
        $unitQuizzes = Quiz::where('unit_id', $lesson->unit_id)
            ->where('subject_id', $subject->id)
            ->whereNull('lesson_id') // اختبارات الوحدة فقط
            ->where('is_active', true)
            ->where('is_published', true)
            ->with(['questions' => function($query) {
                $query->orderBy('quiz_questions.order');
            }])
            ->orderBy('order')
            ->get();
        
        // دمج جميع الاختبارات للحصول على IDs الأسئلة
        $allQuizzes = $lessonQuizzes->merge($unitQuizzes);
        $quizQuestionIds = [];
        foreach ($allQuizzes as $quiz) {
            foreach ($quiz->questions as $question) {
                $quizQuestionIds[] = $question->id;
            }
        }
        $quizQuestionIds = array_unique($quizQuestionIds);
        
        // الحصول على الأسئلة المرتبطة بنفس الوحدة (غير مرتبطة باختبارات)
        $questionsQuery = Question::whereHas('units', function($query) use ($lesson) {
                $query->where('units.id', $lesson->unit_id);
            })
            ->where('is_active', true)
            ->with('units')
            ->orderBy('created_at', 'desc');
        
        if (!empty($quizQuestionIds)) {
            $questionsQuery->whereNotIn('id', $quizQuestionIds);
        }
        
        $questions = $questionsQuery->get();
        
        // الحصول على محاولات الطالب للأسئلة
        $user = Auth::user();
        $questionAttempts = \App\Models\QuestionAttempt::where('user_id', $user->id)
            ->whereIn('question_id', $questions->pluck('id'))
            ->where('lesson_id', $lesson->id)
            ->with('answer')
            ->get()
            ->keyBy('question_id');
        
        // الحصول على محاولات الطالب للاختبارات (كلا النوعين)
        $quizAttempts = \App\Models\QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $allQuizzes->pluck('id'))
            ->with('answers')
            ->get()
            ->keyBy('quiz_id');
        
        // تمرير أنواع الفيديو للـ view
        $videoTypes = \App\Models\Lesson::VIDEO_TYPES;
        
        // تمرير ثوابت الأسئلة للـ view
        $questionTypes = \App\Models\Question::TYPES;
        $questionTypeIcons = \App\Models\Question::TYPE_ICONS;
        $questionTypeColors = \App\Models\Question::TYPE_COLORS;
        $questionDifficulties = \App\Models\Question::DIFFICULTIES;
        
        // الحصول على حالة الدرس للطالب
        $lessonCompletion = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        // تسجيل حدث في Analytics
        $this->analyticsService->trackEvent('view_lesson', $user->id, [
            'lesson_id' => $lesson->id,
            'subject_id' => $subject->id,
            'unit_id' => $lesson->unit_id,
        ]);
        
        return view('student.pages.lessons.lesson-show', compact(
            'lesson',
            'unitLessons',
            'previousLesson',
            'nextLesson',
            'subject',
            'sections',
            'videoTypes',
            'lessonQuizzes',
            'unitQuizzes',
            'questions',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties',
            'questionAttempts',
            'quizAttempts',
            'lessonCompletion'
        ));
    }
    
    /**
     * حفظ/تحديث حالة الدرس (حضور أو إكمال)
     */
    public function markLessonStatus(Request $request, $lessonId)
    {
        $request->validate([
            'status' => 'required|in:attended,completed',
        ]);
        
        $user = Auth::user();
        $lesson = Lesson::findOrFail($lessonId);
        
        // التحقق من أن الطالب مسجل في مادة الدرس
        $subject = $lesson->unit->section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();
        
        if (!$isEnrolled && !$lesson->is_free) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية للوصول إلى هذا الدرس'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $completion = LessonCompletion::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'status' => $request->status,
                    'marked_at' => now(),
                ]
            );
            
            // ربط مع نظام التحفيز
            $gamificationService = app(GamificationService::class);
            if ($request->status === 'attended') {
                $gamificationService->processLessonAttendance($completion);
                // تسجيل حدث في Analytics
                $this->analyticsService->trackEvent('attend_lesson', $user->id, [
                    'lesson_id' => $lesson->id,
                    'subject_id' => $subject->id,
                    'unit_id' => $lesson->unit_id,
                ]);
            } elseif ($request->status === 'completed') {
                $gamificationService->processLessonCompletion($completion);
                // تسجيل حدث في Analytics
                $this->analyticsService->trackEvent('complete_lesson', $user->id, [
                    'lesson_id' => $lesson->id,
                    'subject_id' => $subject->id,
                    'unit_id' => $lesson->unit_id,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $request->status === 'attended' ? 'تم تحديد الحضور بنجاح' : 'تم تحديد الإكمال بنجاح',
                'completion' => $completion,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الحالة: ' . $e->getMessage()
            ], 500);
        }
    }
}
