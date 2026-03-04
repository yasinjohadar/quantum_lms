<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\LessonCompletion;
use App\Models\Purchase;
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

        // مشتريات قيد المراجعة (لم يتم الموافقة عليها بعد)
        $pendingPurchases = Purchase::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('purchasable')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.pages.lessons.classes', compact('classes', 'pendingPurchases'));
    }
    
    /**
     * قائمة المواد - إعادة توجيه إلى صفوفي (الصفوف والمواد تظهر ضمن صفوفي)
     */
    public function subjects()
    {
        return redirect()->route('student.classes');
    }
    
    /**
     * عرض محتوى المادة (عرض المجلدات: كروت الأقسام الجذرية).
     */
    public function showSubject($subjectId)
    {
        $user = Auth::user();

        $subject = Subject::with(['schoolClass.stage'])
            ->whereHas('students', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('enrollments.status', 'active');
            })
            ->findOrFail($subjectId);

        $sections = $subject->sections()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('student.pages.lessons.subject-folders', compact('subject', 'sections'));
    }

    /**
     * إعادة توجيه عرض المجلدات إلى صفحة المادة (للتوافق مع الروابط القديمة).
     */
    public function showSubjectFolders($subjectId)
    {
        $user = Auth::user();
        $subject = Subject::with(['schoolClass.stage'])
            ->whereHas('students', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('enrollments.status', 'active');
            })
            ->findOrFail($subjectId);

        return redirect()->route('student.subjects.show', $subject);
    }

    /**
     * عرض مستوى القسم في عرض المجلدات (حسب نوع القسم: دروس = مشغّل فيديو + أقسام/وحدات، اختبارات = قائمة الاختبارات).
     */
    public function showSubjectFolderSection($subjectId, $sectionId)
    {
        $user = Auth::user();
        $subject = Subject::with(['schoolClass.stage'])
            ->whereHas('students', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('enrollments.status', 'active');
            })
            ->findOrFail($subjectId);

        $section = SubjectSection::where('subject_id', $subject->id)
            ->where('id', $sectionId)
            ->where('is_active', true)
            ->with([
                'children' => function ($q) {
                    $q->where('is_active', true)->orderBy('order');
                },
                'units' => function ($q) {
                    $q->where('is_active', true)->orderBy('order');
                },
            ])
            ->firstOrFail();

        $section->load(['units.quizzes', 'units.linkedQuizzes']);

        $children = $section->children;
        $units = $section->units;

        $sectionQuizzes = collect();
        $firstLessonWithVideo = null;

        // الاختبارات تظهر داخل صفحة الوحدة فقط، لا نجمع sectionQuizzes في صفحة القسم
        // الفيديو يظهر فقط داخل صفحة الوحدة، لا نحسب firstLessonWithVideo في صفحة القسم

        return view('student.pages.lessons.subject-folders-section', compact('subject', 'section', 'children', 'units', 'sectionQuizzes', 'firstLessonWithVideo'));
    }

    /**
     * عرض مستوى الوحدة في عرض المجلدات (اختبارات الوحدة + أوكورديون دروس + أسئلة الوحدة).
     * نفس ترتيب الأدمن: allUnitQuizzes ثم allLessons ثم questions.
     */
    public function showSubjectFolderUnit($subjectId, $sectionId, $unitId)
    {
        $user = Auth::user();
        $subject = Subject::with(['schoolClass.stage'])
            ->whereHas('students', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('enrollments.status', 'active');
            })
            ->findOrFail($subjectId);

        $section = SubjectSection::where('subject_id', $subject->id)
            ->where('id', $sectionId)
            ->where('is_active', true)
            ->firstOrFail();

        $unit = Unit::where('section_id', $section->id)
            ->where('id', $unitId)
            ->where('is_active', true)
            ->with([
                'lessons' => function ($query) {
                    $query->orderBy('order');
                },
                'linkedLessons' => function ($query) {
                    $query->orderBy('lessons.order');
                },
                'quizzes' => function ($query) {
                    $query->orderBy('order');
                },
                'linkedQuizzes' => function ($query) {
                    $query->orderBy('order');
                },
                'questions' => function ($query) {
                    $query->where('is_active', true)->orderBy('created_at', 'desc');
                },
            ])
            ->firstOrFail();

        $visibleLessons = $unit->allLessons()
            ->filter(fn ($lesson) => $lesson->is_active && $lesson->review_status === Lesson::REVIEW_STATUS_APPROVED)
            ->values();

        $visibleLessons->load([
            'quizzes' => function ($q) {
                $q->where('is_active', true)
                    ->where('is_published', true)
                    ->where('review_status', Quiz::REVIEW_STATUS_APPROVED)
                    ->orderBy('order');
            },
            'attachments' => function ($q) {
                $q->where('is_active', true)->orderBy('order');
            },
        ]);

        $unitQuizzes = $unit->allUnitQuizzes()
            ->filter(fn ($quiz) => $quiz->is_active && $quiz->is_published && $quiz->review_status === Quiz::REVIEW_STATUS_APPROVED)
            ->values();

        return view('student.pages.lessons.subject-folders-unit', compact('subject', 'section', 'unit', 'visibleLessons', 'unitQuizzes'));
    }

    /**
     * عرض الدرس مع الفيديو والمرفقات
     */
    public function showLesson($lessonId)
    {
        return view('student.pages.lessons.lesson-show', $this->getLessonShowData($lessonId));
    }

    /**
     * عرض الدرس بأسلوب المجلدات (فيديو أعلى ثم أوكورديون)
     */
    public function showLessonFolders($lessonId)
    {
        $data = $this->getLessonShowData($lessonId);
        return view('student.pages.lessons.lesson-show-folders', $data);
    }

    /**
     * تحضير بيانات عرض الدرس (مشترك بين showLesson و showLessonFolders)
     */
    protected function getLessonShowData($lessonId)
    {
        $user = Auth::user();

        $lesson = Lesson::with([
            'unit.section.subject',
            'attachments' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            }
        ])->findOrFail($lessonId);

        $subject = $lesson->unit->section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled && !$lesson->is_free) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا الدرس. يجب أن تكون مسجلاً في المادة.');
        }

        if ($lesson->review_status !== Lesson::REVIEW_STATUS_APPROVED || !$lesson->is_active) {
            abort(403, 'هذا الدرس قيد المراجعة أو غير مفعّل ولا يمكن عرضه حالياً.');
        }

        $sections = $subject->sections()
            ->with([
                'units.lessons' => function($query) {
                    $query->where('is_active', true)->orderBy('order');
                },
            ])
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $unitLessons = $lesson->unit->lessons()
            ->where('is_active', true)
            ->where('review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->orderBy('order')
            ->get();

        $currentIndex = $unitLessons->search(fn($item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $unitLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $unitLessons->count() - 1 ? $unitLessons[$currentIndex + 1] : null;

        $lessonQuizzes = Quiz::where('lesson_id', $lesson->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('review_status', \App\Models\Quiz::REVIEW_STATUS_APPROVED)
            ->with(['questions' => fn($q) => $q->orderBy('quiz_questions.order')])
            ->orderBy('order')
            ->get();

        $unitQuizzes = Quiz::where('unit_id', $lesson->unit_id)
            ->where('subject_id', $subject->id)
            ->whereNull('lesson_id')
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('review_status', \App\Models\Quiz::REVIEW_STATUS_APPROVED)
            ->with(['questions' => fn($q) => $q->orderBy('quiz_questions.order')])
            ->orderBy('order')
            ->get();

        $allQuizzes = $lessonQuizzes->merge($unitQuizzes);
        $quizQuestionIds = $allQuizzes->flatMap(fn($q) => $q->questions->pluck('id'))->unique()->values()->all();

        $questionsQuery = Question::whereHas('units', fn($q) => $q->where('units.id', $lesson->unit_id))
            ->where('is_active', true)
            ->with('units')
            ->orderBy('created_at', 'desc');
        if (!empty($quizQuestionIds)) {
            $questionsQuery->whereNotIn('id', $quizQuestionIds);
        }
        $questions = $questionsQuery->get();

        $questionAttempts = \App\Models\QuestionAttempt::where('user_id', $user->id)
            ->whereIn('question_id', $questions->pluck('id'))
            ->where('lesson_id', $lesson->id)
            ->with('answer')
            ->get()
            ->keyBy('question_id');

        $quizAttempts = \App\Models\QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $allQuizzes->pluck('id'))
            ->with('answers')
            ->get()
            ->keyBy('quiz_id');

        $lessonCompletion = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $videoTypes = \App\Models\Lesson::VIDEO_TYPES;
        $questionTypes = \App\Models\Question::TYPES;
        $questionTypeIcons = \App\Models\Question::TYPE_ICONS;
        $questionTypeColors = \App\Models\Question::TYPE_COLORS;
        $questionDifficulties = \App\Models\Question::DIFFICULTIES;

        $this->analyticsService->trackEvent('view_lesson', $user->id, [
            'lesson_id' => $lesson->id,
            'subject_id' => $subject->id,
            'unit_id' => $lesson->unit_id,
        ]);

        return compact(
            'lesson',
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
        );
    }

    /**
     * تحديث تقدم مشاهدة الدرس (وقت المشاهدة، الموضع، النسبة).
     * يُستدعى تلقائياً من الواجهة أثناء تشغيل الفيديو.
     */
    public function updateLessonProgress(Request $request, Lesson $lesson)
    {
        $request->validate([
            'time_spent_seconds' => 'nullable|integer|min:0',
            'last_position_seconds' => 'nullable|integer|min:0',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = Auth::user();
        $subject = $lesson->unit->section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled && !$lesson->is_free) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية للوصول إلى هذا الدرس',
            ], 403);
        }

        if ($lesson->review_status !== Lesson::REVIEW_STATUS_APPROVED || !$lesson->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الدرس غير متاح حالياً',
            ], 403);
        }

        try {
            $timeSpent = $request->has('time_spent_seconds') ? (int) $request->time_spent_seconds : null;
            $lastPosition = $request->has('last_position_seconds') ? (int) $request->last_position_seconds : null;
            $progressPercentage = $request->has('progress_percentage') ? round((float) $request->progress_percentage, 2) : null;

            $completion = LessonCompletion::firstOrNew([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]);

            if ($timeSpent !== null) {
                $completion->time_spent = max($completion->time_spent ?? 0, $timeSpent);
            }
            if ($lastPosition !== null) {
                $completion->last_position = max($completion->last_position ?? 0, $lastPosition);
            }
            if ($progressPercentage !== null) {
                $completion->progress_percentage = max($completion->progress_percentage ?? 0, $progressPercentage);
            }

            $now = now();
            $completion->marked_at = $completion->marked_at ?? $now;

            if (($completion->progress_percentage ?? 0) >= 90 && $completion->status !== 'completed') {
                $completion->status = 'completed';
                $completion->completed_at = $now;
                $completion->marked_at = $now;
            } elseif ($completion->status !== 'completed' && ($completion->time_spent ?? 0) > 0) {
                $completion->status = $completion->status ?? 'attended';
            }

            $completion->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التقدم',
                'completion' => $completion->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ التقدم: ' . $e->getMessage(),
            ], 500);
        }
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
            
            $now = now();
            $completion = LessonCompletion::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'status' => $request->status,
                    'marked_at' => $now,
                    'completed_at' => $now, // إضافة completed_at لتجنب خطأ NOT NULL
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
