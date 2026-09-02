<?php

namespace App\Services;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Models\LearningExperienceAttempt;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\QuestionAttempt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class StudentProgressService
{
    /**
     * الوحدات المعروضة في القسم بما فيها الفروع تحت كل جذر (شجرة القسم المنزل لكل وحدة جذر).
     *
     * @return Collection<int, Unit>
     */
    protected function unitsForSectionProgressTree(SubjectSection $section, ?Collection $sectionsPool = null): Collection
    {
        $collected = collect();
        foreach ($section->rootUnitsForDisplay(onlyActive: true) as $root) {
            if (!$root->relationLoaded('section')) {
                $homeSection = null;
                if ((int) $root->section_id === (int) $section->id) {
                    $homeSection = $section;
                } elseif ($sectionsPool) {
                    $homeSection = $sectionsPool->firstWhere('id', $root->section_id);
                }

                if ($homeSection) {
                    $root->setRelation('section', $homeSection);
                } else {
                    $root->load([
                        'section.units' => fn ($q) => $q->where('is_active', true),
                        'section.units.lessons',
                        'section.units.quizzes',
                        'section.units.questions',
                    ]);
                }
            }

            // إن كانت الوحدات محمّلة مسبقًا (من $section أو من مجمّع الأقسام) فلن يُنفَّذ أي استعلام هنا.
            $root->section->loadMissing([
                'units' => fn ($q) => $q->where('is_active', true),
                'units.lessons',
                'units.quizzes',
                'units.questions',
            ]);

            $homeUnits = $root->section?->units?->where('is_active', true);
            if ($homeUnits === null || $homeUnits->isEmpty()) {
                $collected->push($root);
                continue;
            }

            $queue = collect([$root]);
            $seen = [];
            while ($queue->isNotEmpty()) {
                $u = $queue->shift();
                if (isset($seen[$u->id])) {
                    continue;
                }
                $seen[$u->id] = true;
                $collected->push($u);
                foreach ($homeUnits->where('parent_id', $u->id)->sortBy('order') as $child) {
                    $queue->push($child);
                }
            }
        }

        return $collected->unique('id')->values();
    }

    /**
     * حساب نسبة إكمال درس معين
     */
    public function calculateLessonProgress($userId, $lessonId): array
    {
        $lesson = Lesson::with('unit.section.subject')->findOrFail($lessonId);
        
        // التحقق من أن الطالب مسجل في المادة
        $subject = $lesson->unit->section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $userId)
            ->where('enrollments.status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            return [
                'completed' => false,
                'status' => null,
                'percentage' => 0,
            ];
        }

        return $this->batchLessonProgress($userId, collect([$lesson]))[$lesson->id];
    }

    /**
     * حساب تقدم مجموعة من الدروس دفعة واحدة (استعلام LessonCompletion واحد بدل استعلام لكل درس).
     *
     * @param  Collection<int, Lesson>  $lessons
     * @return array<int, array{completed: bool, attended: bool, status: ?string, percentage: int, marked_at: mixed}>
     */
    protected function batchLessonProgress(int $userId, Collection $lessons): array
    {
        $lessonIds = $lessons->pluck('id')->unique()->values();

        $completions = $lessonIds->isEmpty()
            ? collect()
            : LessonCompletion::where('user_id', $userId)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->keyBy('lesson_id');

        $result = [];
        foreach ($lessons as $lesson) {
            $completion = $completions->get($lesson->id);
            $isCompleted = $completion && $completion->status === 'completed';
            $isAttended = $completion && $completion->status === 'attended';

            $result[$lesson->id] = [
                'completed' => $isCompleted,
                'attended' => $isAttended,
                'status' => $completion ? $completion->status : null,
                'percentage' => $isCompleted ? 100 : ($isAttended ? 50 : 0),
                'marked_at' => $completion ? $completion->marked_at : null,
            ];
        }

        return $result;
    }

    /**
     * حساب نسبة إكمال قسم معين
     */
    public function calculateSectionProgress($userId, $sectionId): array
    {
        $section = SubjectSection::with([
            'units.lessons',
            'units.quizzes',
            'units.questions',
            'mirroredUnits.lessons',
            'mirroredUnits.quizzes',
            'mirroredUnits.questions',
        ])->findOrFail($sectionId);
        
        // التحقق من أن الطالب مسجل في المادة
        $subject = $section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $userId)
            ->where('enrollments.status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            return [
                'lessons_percentage' => 0,
                'quizzes_percentage' => 0,
                'questions_percentage' => 0,
                'overall_percentage' => 0,
                'lessons_completed' => 0,
                'lessons_total' => 0,
                'quizzes_completed' => 0,
                'quizzes_total' => 0,
                'questions_completed' => 0,
                'questions_total' => 0,
            ];
        }
        
        // جمع جميع الدروس والاختبارات والأسئلة في القسم
        $allLessons = collect();
        $allQuizzes = collect();
        $allQuestions = collect();
        
        $treeUnits = $this->unitsForSectionProgressTree($section);
        foreach ($treeUnits as $unit) {
            $allLessons = $allLessons->merge($unit->lessons->where('is_active', true));
            $allQuizzes = $allQuizzes->merge($unit->quizzes->where('is_active', true)->where('is_published', true));
            $allQuestions = $allQuestions->merge($unit->questions->where('is_active', true));
        }
        $allLessons = $allLessons->unique('id')->values();
        $allQuizzes = $allQuizzes->unique('id')->values();
        $allQuestions = $allQuestions->unique('id')->values();
        $interactiveExperiences = $this->publishedLearningExperiencesForUnits($treeUnits, $section->subject_id);
        
        // حساب الدروس المكتملة
        $lessonsTotal = $allLessons->count();
        $lessonProgressMap = $this->batchLessonProgress($userId, $allLessons);
        $lessonsCompleted = collect($lessonProgressMap)->where('completed', true)->count();
        $lessonsPercentage = $lessonsTotal > 0 ? ($lessonsCompleted / $lessonsTotal) * 100 : 0;

        // حساب الاختبارات المكتملة (عادية + تفاعلية)
        $regularQuizzesCompleted = $allQuizzes->isEmpty()
            ? 0
            : QuizAttempt::where('user_id', $userId)
                ->whereIn('quiz_id', $allQuizzes->pluck('id'))
                ->whereIn('status', ['completed', 'timed_out'])
                ->select('quiz_id')
                ->distinct()
                ->pluck('quiz_id')
                ->count();
        $interactiveCompleted = $this->countCompletedLearningExperiences((int) $userId, $interactiveExperiences);
        $quizzesTotal = $allQuizzes->count() + $interactiveExperiences->count();
        $quizzesCompleted = $regularQuizzesCompleted + $interactiveCompleted;
        $quizzesPercentage = $quizzesTotal > 0 ? ($quizzesCompleted / $quizzesTotal) * 100 : 0;
        
        // حساب الأسئلة المكتملة
        $questionsTotal = $allQuestions->count();
        $questionsCompletedIds = QuestionAttempt::where('user_id', $userId)
            ->whereIn('question_id', $allQuestions->pluck('id'))
            ->whereIn('status', ['completed', 'timed_out'])
            ->select('question_id')
            ->distinct()
            ->pluck('question_id')
            ->count();
        $questionsCompleted = $questionsCompletedIds;
        $questionsPercentage = $questionsTotal > 0 ? ($questionsCompleted / $questionsTotal) * 100 : 0;
        
        // النسبة الإجمالية (متوسط النسب الموجودة فقط)
        $percentages = [];
        if ($lessonsTotal > 0) {
            $percentages[] = $lessonsPercentage;
        }
        if ($quizzesTotal > 0) {
            $percentages[] = $quizzesPercentage;
        }
        if ($questionsTotal > 0) {
            $percentages[] = $questionsPercentage;
        }
        
        $overallPercentage = !empty($percentages) ? array_sum($percentages) / count($percentages) : 0;
        
        return [
            'lessons_percentage' => round($lessonsPercentage, 2),
            'quizzes_percentage' => round($quizzesPercentage, 2),
            'questions_percentage' => round($questionsPercentage, 2),
            'overall_percentage' => round($overallPercentage, 2),
            'lessons_completed' => $lessonsCompleted,
            'lessons_total' => $lessonsTotal,
            'quizzes_completed' => $quizzesCompleted,
            'quizzes_total' => $quizzesTotal,
            'questions_completed' => $questionsCompleted,
            'questions_total' => $questionsTotal,
        ];
    }
    
    /**
     * حساب نسبة إكمال كورس كامل
     */
    public function calculateSubjectProgress($userId, $subjectId): array
    {
        $subject = Subject::with([
            'sections.units.lessons',
            'sections.units.quizzes',
            'sections.units.questions',
            'sections.mirroredUnits.lessons',
            'sections.mirroredUnits.quizzes',
            'sections.mirroredUnits.questions',
        ])
            ->findOrFail($subjectId);

        // التحقق من أن الطالب مسجل في المادة
        $isEnrolled = $subject->students()
            ->where('users.id', $userId)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled) {
            return [
                'lessons_percentage' => 0,
                'quizzes_percentage' => 0,
                'questions_percentage' => 0,
                'overall_percentage' => 0,
                'lessons_completed' => 0,
                'lessons_total' => 0,
                'quizzes_completed' => 0,
                'quizzes_total' => 0,
                'questions_completed' => 0,
                'questions_total' => 0,
            ];
        }

        return $this->calculateSubjectProgressForLoadedSubject($subject, $userId);
    }

    /**
     * حساب نسبة إكمال كورس كامل لمادة محمّلة ومُتحقَّق من تسجيل الطالب فيها مسبقًا.
     * يُستخدم لتفادي إعادة جلب المادة والتحقق من التسجيل عند الحساب لعدة مواد ضمن حلقة واحدة.
     */
    protected function calculateSubjectProgressForLoadedSubject(Subject $subject, $userId): array
    {
        // جمع جميع الدروس والاختبارات والأسئلة في الكورس
        $allLessons = collect();
        $allQuizzes = collect();
        $allQuestions = collect();

        $allInteractive = collect();
        foreach ($subject->sections as $section) {
            $treeUnits = $this->unitsForSectionProgressTree($section, $subject->sections);
            foreach ($treeUnits as $unit) {
                $allLessons = $allLessons->merge($unit->lessons->where('is_active', true));
                $allQuizzes = $allQuizzes->merge($unit->quizzes->where('is_active', true)->where('is_published', true));
                $allQuestions = $allQuestions->merge($unit->questions->where('is_active', true));
            }
            $allInteractive = $allInteractive->merge(
                $this->publishedLearningExperiencesForUnits($treeUnits, $subject->id)
            );
        }
        $allLessons = $allLessons->unique('id')->values();
        $allQuizzes = $allQuizzes->unique('id')->values();
        $allQuestions = $allQuestions->unique('id')->values();
        $allInteractive = $allInteractive->unique('id')->values();

        // حساب الدروس المكتملة
        $lessonsTotal = $allLessons->count();
        $lessonProgressMap = $this->batchLessonProgress($userId, $allLessons);
        $lessonsCompleted = collect($lessonProgressMap)->where('completed', true)->count();
        $lessonsPercentage = $lessonsTotal > 0 ? ($lessonsCompleted / $lessonsTotal) * 100 : 0;
        
        // حساب الاختبارات المكتملة (عادية + تفاعلية)
        $regularQuizzesCompleted = $allQuizzes->isEmpty()
            ? 0
            : QuizAttempt::where('user_id', $userId)
                ->whereIn('quiz_id', $allQuizzes->pluck('id'))
                ->whereIn('status', ['completed', 'timed_out'])
                ->select('quiz_id')
                ->distinct()
                ->pluck('quiz_id')
                ->count();
        $interactiveCompleted = $this->countCompletedLearningExperiences((int) $userId, $allInteractive);
        $quizzesTotal = $allQuizzes->count() + $allInteractive->count();
        $quizzesCompleted = $regularQuizzesCompleted + $interactiveCompleted;
        $quizzesPercentage = $quizzesTotal > 0 ? ($quizzesCompleted / $quizzesTotal) * 100 : 0;
        
        // حساب الأسئلة المكتملة
        $questionsTotal = $allQuestions->count();
        $questionsCompletedIds = QuestionAttempt::where('user_id', $userId)
            ->whereIn('question_id', $allQuestions->pluck('id'))
            ->whereIn('status', ['completed', 'timed_out'])
            ->select('question_id')
            ->distinct()
            ->pluck('question_id')
            ->count();
        $questionsCompleted = $questionsCompletedIds;
        $questionsPercentage = $questionsTotal > 0 ? ($questionsCompleted / $questionsTotal) * 100 : 0;
        
        // النسبة الإجمالية (متوسط النسب الموجودة فقط)
        $percentages = [];
        if ($lessonsTotal > 0) {
            $percentages[] = $lessonsPercentage;
        }
        if ($quizzesTotal > 0) {
            $percentages[] = $quizzesPercentage;
        }
        if ($questionsTotal > 0) {
            $percentages[] = $questionsPercentage;
        }
        
        $overallPercentage = !empty($percentages) ? array_sum($percentages) / count($percentages) : 0;
        
        return [
            'lessons_percentage' => round($lessonsPercentage, 2),
            'quizzes_percentage' => round($quizzesPercentage, 2),
            'questions_percentage' => round($questionsPercentage, 2),
            'overall_percentage' => round($overallPercentage, 2),
            'lessons_completed' => $lessonsCompleted,
            'lessons_total' => $lessonsTotal,
            'quizzes_completed' => $quizzesCompleted,
            'quizzes_total' => $quizzesTotal,
            'questions_completed' => $questionsCompleted,
            'questions_total' => $questionsTotal,
        ];
    }
    
    /**
     * إحصائيات الحضور لمادة معينة: عدد الدروس المحضورة، وقت المشاهدة، ونسب الحضور.
     *
     * @return array{
     *   total_lessons: int,
     *   attended_lessons: int,
     *   time_spent_sum: int,
     *   subject_duration_seconds: int,
     *   lessons_attendance_percentage: float,
     *   watch_time_percentage: float
     * }
     */
    public function getSubjectAttendanceStats($userId, $subjectId): array
    {
        $subject = Subject::findOrFail($subjectId);
        $sectionIds = $subject->allSections()->pluck('id')->toArray();
        if (empty($sectionIds)) {
            return [
                'total_lessons' => 0,
                'attended_lessons' => 0,
                'time_spent_sum' => 0,
                'subject_duration_seconds' => 0,
                'lessons_attendance_percentage' => 0.0,
                'watch_time_percentage' => 0.0,
            ];
        }
        $unitIds = \App\Models\Unit::whereIn('section_id', $sectionIds)->pluck('id')->toArray();
        if (empty($unitIds)) {
            return [
                'total_lessons' => 0,
                'attended_lessons' => 0,
                'time_spent_sum' => 0,
                'subject_duration_seconds' => 0,
                'lessons_attendance_percentage' => 0.0,
                'watch_time_percentage' => 0.0,
            ];
        }
        $lessonIdsFromUnits = Lesson::whereIn('unit_id', $unitIds)->pluck('id');
        $lessonIdsFromLinked = DB::table('lesson_units')->whereIn('unit_id', $unitIds)->pluck('lesson_id');
        $allLessonIds = $lessonIdsFromUnits->merge($lessonIdsFromLinked)->unique()->values()->all();
        $totalLessons = count($allLessonIds);
        $subjectDurationSeconds = $subject->getTotalDurationSeconds();

        $attendedCount = LessonCompletion::where('user_id', $userId)
            ->whereIn('lesson_id', $allLessonIds)
            ->where(function ($q) {
                $q->where('time_spent', '>', 0)
                    ->orWhereIn('status', ['attended', 'completed']);
            })
            ->pluck('lesson_id')
            ->unique()
            ->count();

        $timeSpentSum = (int) LessonCompletion::where('user_id', $userId)
            ->whereIn('lesson_id', $allLessonIds)
            ->sum('time_spent');

        $lessonsAttendancePercentage = $totalLessons > 0
            ? round(($attendedCount / $totalLessons) * 100, 2)
            : 0.0;
        $watchTimePercentage = $subjectDurationSeconds > 0
            ? round(($timeSpentSum / $subjectDurationSeconds) * 100, 2)
            : 0.0;

        return [
            'total_lessons' => $totalLessons,
            'attended_lessons' => $attendedCount,
            'time_spent_sum' => $timeSpentSum,
            'subject_duration_seconds' => $subjectDurationSeconds,
            'lessons_attendance_percentage' => $lessonsAttendancePercentage,
            'watch_time_percentage' => $watchTimePercentage,
        ];
    }

    /**
     * قائمة كل دروس المادة مع تفاصيل LessonCompletion للطالب (للأدمن).
     * كل درس يظهر مرة واحدة مع completion إن وُجد، وإلا completion = null.
     */
    public function getStudentSubjectLessonCompletions($userId, $subjectId): array
    {
        $subject = Subject::findOrFail($subjectId);
        $sectionIds = $subject->allSections()->pluck('id')->toArray();
        if (empty($sectionIds)) {
            return [];
        }
        $unitIds = \App\Models\Unit::whereIn('section_id', $sectionIds)->pluck('id')->toArray();
        if (empty($unitIds)) {
            return [];
        }
        $lessonIdsFromUnits = Lesson::whereIn('unit_id', $unitIds)->pluck('id');
        $lessonIdsFromLinked = DB::table('lesson_units')->whereIn('unit_id', $unitIds)->pluck('lesson_id');
        $allLessonIds = $lessonIdsFromUnits->merge($lessonIdsFromLinked)->unique()->values()->all();
        if (empty($allLessonIds)) {
            return [];
        }

        $lessons = Lesson::whereIn('id', $allLessonIds)->orderBy('title')->get()->keyBy('id');
        $completions = LessonCompletion::where('user_id', $userId)
            ->whereIn('lesson_id', $allLessonIds)
            ->with('lesson')
            ->get()
            ->keyBy('lesson_id');

        $rows = [];
        foreach ($allLessonIds as $lid) {
            $lesson = $lessons->get($lid);
            $completion = $completions->get($lid);
            $rows[] = [
                'lesson_id' => $lid,
                'lesson_title' => $lesson ? $lesson->title : '',
                'lesson_duration' => $lesson ? ($lesson->duration ?? null) : null,
                'completion' => $completion ? [
                    'status' => $completion->status,
                    'progress_percentage' => $completion->progress_percentage,
                    'time_spent' => $completion->time_spent,
                    'last_position' => $completion->last_position,
                    'marked_at' => $completion->marked_at,
                    'updated_at' => $completion->updated_at,
                ] : null,
            ];
        }
        usort($rows, fn ($a, $b) => strcmp($a['lesson_title'], $b['lesson_title']));
        return $rows;
    }

    /**
     * إحصائيات شاملة لكورس معين
     */
    public function getStudentSubjectStats($userId, $subjectId): array
    {
        $subject = Subject::with([
            'sections.units.lessons',
            'sections.units.quizzes',
            'sections.units.questions',
            'sections.mirroredUnits.lessons',
            'sections.mirroredUnits.quizzes',
            'sections.mirroredUnits.questions',
        ])
            ->findOrFail($subjectId);
        
        $progress = $this->calculateSubjectProgress($userId, $subjectId);
        
        // إحصائيات الأقسام
        $sectionsStats = [];
        foreach ($subject->sections->where('is_active', true) as $section) {
            $sectionProgress = $this->calculateSectionProgress($userId, $section->id);
            $sectionsStats[] = [
                'section' => $section,
                'progress' => $sectionProgress,
            ];
        }
        
        $attendance = $this->getSubjectAttendanceStats($userId, $subjectId);

        return [
            'subject' => $subject,
            'progress' => $progress,
            'sections' => $sectionsStats,
            'attendance' => $attendance,
        ];
    }

    /**
     * جميع الكورسات مع نسب التقدم
     */
    public function getAllStudentProgress($userId): array
    {
        return Cache::remember("student_progress_{$userId}", 60, function () use ($userId) {
            $user = \App\Models\User::findOrFail($userId);

            $subjects = $user->subjects()
                ->with([
                    'schoolClass.stage',
                    'sections.units.lessons',
                    'sections.units.quizzes',
                    'sections.units.questions',
                    'sections.mirroredUnits.lessons',
                    'sections.mirroredUnits.quizzes',
                    'sections.mirroredUnits.questions',
                ])
                ->wherePivot('status', 'active')
                ->get()
                ->sort(function (Subject $a, Subject $b) {
                    $stageOrderA = (int) ($a->schoolClass?->stage?->order ?? 999999);
                    $stageOrderB = (int) ($b->schoolClass?->stage?->order ?? 999999);
                    if ($stageOrderA !== $stageOrderB) {
                        return $stageOrderA <=> $stageOrderB;
                    }

                    $stageIdA = (int) ($a->schoolClass?->stage?->id ?? 0);
                    $stageIdB = (int) ($b->schoolClass?->stage?->id ?? 0);
                    if ($stageIdA !== $stageIdB) {
                        return $stageIdA <=> $stageIdB;
                    }

                    $classOrderA = (int) ($a->schoolClass?->order ?? 999999);
                    $classOrderB = (int) ($b->schoolClass?->order ?? 999999);
                    if ($classOrderA !== $classOrderB) {
                        return $classOrderA <=> $classOrderB;
                    }

                    $classIdA = (int) ($a->schoolClass?->id ?? 0);
                    $classIdB = (int) ($b->schoolClass?->id ?? 0);
                    if ($classIdA !== $classIdB) {
                        return $classIdA <=> $classIdB;
                    }

                    return strcmp((string) ($a->name ?? ''), (string) ($b->name ?? ''));
                })
                ->values();

            $progressList = [];
            foreach ($subjects as $subject) {
                $progress = $this->calculateSubjectProgressForLoadedSubject($subject, $userId);
                // نتخلص من شجرة الأقسام/الوحدات الثقيلة قبل التخزين/الكاش، فهي لم تعد مطلوبة
                // بعد حساب التقدم، والعرض يحتاج فقط إلى schoolClass.stage.
                $subject->unsetRelation('sections');
                $progressList[] = [
                    'subject' => $subject,
                    'progress' => $progress,
                ];
            }

            return $progressList;
        });
    }
    
    /**
     * تفاصيل القسم مع قائمة الدروس والاختبارات والأسئلة
     */
    public function getSectionDetails($userId, $sectionId): array
    {
        $section = SubjectSection::with([
            'units.lessons' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'units.quizzes' => function ($query) {
                $query->where('is_active', true)
                      ->where('is_published', true)
                      ->orderBy('order');
            },
            'units.questions' => function ($query) {
                $query->where('is_active', true);
            },
            'mirroredUnits.lessons' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'mirroredUnits.quizzes' => function ($query) {
                $query->where('is_active', true)
                      ->where('is_published', true)
                      ->orderBy('order');
            },
            'mirroredUnits.questions' => function ($query) {
                $query->where('is_active', true);
            },
        ])->findOrFail($sectionId);

        $progress = $this->calculateSectionProgress($userId, $sectionId);

        $treeUnits = $this->unitsForSectionProgressTree($section);
        $unitIdsInSection = $treeUnits->pluck('id')->unique()->values()->all();

        // تفاصيل الدروس
        $lessonsDetails = [];
        $seenLessonIds = [];
        $lessonUnitPairs = [];
        foreach ($treeUnits as $unit) {
            foreach ($unit->lessons->where('is_active', true) as $lesson) {
                if (isset($seenLessonIds[$lesson->id])) {
                    continue;
                }
                $seenLessonIds[$lesson->id] = true;
                $lessonUnitPairs[] = ['lesson' => $lesson, 'unit' => $unit];
            }
        }
        $lessonProgressMap = $this->batchLessonProgress(
            $userId,
            collect($lessonUnitPairs)->pluck('lesson')
        );
        foreach ($lessonUnitPairs as $pair) {
            $lessonsDetails[] = [
                'lesson' => $pair['lesson'],
                'unit' => $pair['unit'],
                'progress' => $lessonProgressMap[$pair['lesson']->id],
            ];
        }

        // تفاصيل الاختبارات
        $quizzesDetails = [];
        $quizAttempts = QuizAttempt::where('user_id', $userId)
            ->whereHas('quiz', function ($query) use ($unitIdsInSection) {
                $query->whereIn('unit_id', $unitIdsInSection);
            })
            ->get()
            ->keyBy('quiz_id');

        foreach ($treeUnits as $unit) {
            foreach ($unit->quizzes->where('is_active', true)->where('is_published', true) as $quiz) {
                $attempt = $quizAttempts->get($quiz->id);
                $quizzesDetails[] = [
                    'quiz' => $quiz,
                    'unit' => $unit,
                    'attempt' => $attempt,
                    'completed' => $attempt && in_array($attempt->status, ['completed', 'timed_out']),
                    'kind' => 'quiz',
                ];
            }
        }

        $interactiveExperiences = $this->publishedLearningExperiencesForUnits($treeUnits, $section->subject_id);
        $ileBestAttempts = $this->bestLearningExperienceAttemptsByExperience((int) $userId, $interactiveExperiences);
        foreach ($interactiveExperiences as $experience) {
            $best = $ileBestAttempts->get($experience->id);
            $unit = $treeUnits->firstWhere('id', $experience->unit_id);
            $quizzesDetails[] = [
                'quiz' => null,
                'experience' => $experience,
                'unit' => $unit,
                'attempt' => $best,
                'completed' => $best !== null,
                'kind' => 'interactive',
                'best_percentage' => $best?->percentage,
            ];
        }

        // تفاصيل الأسئلة
        $questionsDetails = [];
        $questionAttempts = QuestionAttempt::where('user_id', $userId)
            ->whereHas('question', function ($query) use ($unitIdsInSection) {
                $query->whereHas('units', function ($q) use ($unitIdsInSection) {
                    $q->whereIn('units.id', $unitIdsInSection);
                });
            })
            ->get()
            ->keyBy('question_id');

        foreach ($treeUnits as $unit) {
            foreach ($unit->questions->where('is_active', true) as $question) {
                $attempt = $questionAttempts->get($question->id);
                $questionsDetails[] = [
                    'question' => $question,
                    'unit' => $unit,
                    'attempt' => $attempt,
                    'completed' => $attempt && in_array($attempt->status, ['completed', 'timed_out']),
                ];
            }
        }
        
        return [
            'section' => $section,
            'progress' => $progress,
            'lessons' => $lessonsDetails,
            'quizzes' => $quizzesDetails,
            'questions' => $questionsDetails,
        ];
    }

    /**
     * الاختبارات التفاعلية المنشورة المرتبطة بوحدات القسم/المادة.
     *
     * @param  Collection<int, Unit>  $units
     * @return Collection<int, LearningExperience>
     */
    protected function publishedLearningExperiencesForUnits(Collection $units, ?int $subjectId = null): Collection
    {
        $unitIds = $units->pluck('id')->filter()->unique()->values();
        $lessonIds = $units
            ->flatMap(fn (Unit $unit) => ($unit->lessons ?? collect())->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        if ($unitIds->isEmpty() && $lessonIds->isEmpty() && ! $subjectId) {
            return collect();
        }

        return LearningExperience::query()
            ->where('status', LearningExperience::STATUS_PUBLISHED)
            ->where(function ($query) use ($unitIds, $lessonIds, $subjectId) {
                $started = false;
                if ($unitIds->isNotEmpty()) {
                    $query->whereIn('unit_id', $unitIds);
                    $started = true;
                }
                if ($lessonIds->isNotEmpty()) {
                    $started ? $query->orWhereIn('lesson_id', $lessonIds) : $query->whereIn('lesson_id', $lessonIds);
                    $started = true;
                }
                if ($subjectId) {
                    $clause = function ($q) use ($subjectId) {
                        $q->where('subject_id', $subjectId)
                            ->whereNull('unit_id')
                            ->whereNull('lesson_id');
                    };
                    $started ? $query->orWhere($clause) : $query->where($clause);
                }
            })
            ->orderBy('title')
            ->get();
    }

    /**
     * @param  Collection<int, LearningExperience>  $experiences
     */
    protected function countCompletedLearningExperiences(int $userId, Collection $experiences): int
    {
        if ($experiences->isEmpty()) {
            return 0;
        }

        return (int) LearningExperienceAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('learning_experience_id', $experiences->pluck('id'))
            ->distinct()
            ->count('learning_experience_id');
    }

    /**
     * أفضل محاولة لكل اختبار تفاعلي (أعلى نسبة).
     *
     * @param  Collection<int, LearningExperience>  $experiences
     * @return Collection<int, LearningExperienceAttempt>
     */
    protected function bestLearningExperienceAttemptsByExperience(int $userId, Collection $experiences): Collection
    {
        if ($experiences->isEmpty()) {
            return collect();
        }

        return LearningExperienceAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('learning_experience_id', $experiences->pluck('id'))
            ->orderByDesc('percentage')
            ->orderByDesc('finished_at')
            ->get()
            ->unique('learning_experience_id')
            ->keyBy('learning_experience_id');
    }
}

