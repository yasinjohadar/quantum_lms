<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\QuestionAttempt;
use Illuminate\Support\Facades\DB;

class StudentProgressService
{
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
        
        $completion = LessonCompletion::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
        
        $isCompleted = $completion && $completion->status === 'completed';
        $isAttended = $completion && $completion->status === 'attended';
        
        return [
            'completed' => $isCompleted,
            'attended' => $isAttended,
            'status' => $completion ? $completion->status : null,
            'percentage' => $isCompleted ? 100 : ($isAttended ? 50 : 0),
            'marked_at' => $completion ? $completion->marked_at : null,
        ];
    }
    
    /**
     * حساب نسبة إكمال قسم معين
     */
    public function calculateSectionProgress($userId, $sectionId): array
    {
        $section = SubjectSection::with(['units.lessons', 'units.quizzes', 'units.questions'])
            ->findOrFail($sectionId);
        
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
        
        foreach ($section->units as $unit) {
            $allLessons = $allLessons->merge($unit->lessons->where('is_active', true));
            $allQuizzes = $allQuizzes->merge($unit->quizzes->where('is_active', true)->where('is_published', true));
            $allQuestions = $allQuestions->merge($unit->questions->where('is_active', true));
        }
        
        // حساب الدروس المكتملة
        $lessonsTotal = $allLessons->count();
        $lessonsCompleted = 0;
        foreach ($allLessons as $lesson) {
            $progress = $this->calculateLessonProgress($userId, $lesson->id);
            if ($progress['completed']) {
                $lessonsCompleted++;
            }
        }
        $lessonsPercentage = $lessonsTotal > 0 ? ($lessonsCompleted / $lessonsTotal) * 100 : 0;
        
        // حساب الاختبارات المكتملة
        $quizzesTotal = $allQuizzes->count();
        $quizzesCompleted = QuizAttempt::where('user_id', $userId)
            ->whereIn('quiz_id', $allQuizzes->pluck('id'))
            ->whereIn('status', ['completed', 'timed_out'])
            ->select('quiz_id')
            ->distinct()
            ->pluck('quiz_id')
            ->count();
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
        $subject = Subject::with(['sections.units.lessons', 'sections.units.quizzes', 'sections.units.questions'])
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
        
        // جمع جميع الدروس والاختبارات والأسئلة في الكورس
        $allLessons = collect();
        $allQuizzes = collect();
        $allQuestions = collect();
        
        foreach ($subject->sections as $section) {
            foreach ($section->units as $unit) {
                $allLessons = $allLessons->merge($unit->lessons->where('is_active', true));
                $allQuizzes = $allQuizzes->merge($unit->quizzes->where('is_active', true)->where('is_published', true));
                $allQuestions = $allQuestions->merge($unit->questions->where('is_active', true));
            }
        }
        
        // حساب الدروس المكتملة
        $lessonsTotal = $allLessons->count();
        $lessonsCompleted = 0;
        foreach ($allLessons as $lesson) {
            $progress = $this->calculateLessonProgress($userId, $lesson->id);
            if ($progress['completed']) {
                $lessonsCompleted++;
            }
        }
        $lessonsPercentage = $lessonsTotal > 0 ? ($lessonsCompleted / $lessonsTotal) * 100 : 0;
        
        // حساب الاختبارات المكتملة
        $quizzesTotal = $allQuizzes->count();
        $quizzesCompleted = QuizAttempt::where('user_id', $userId)
            ->whereIn('quiz_id', $allQuizzes->pluck('id'))
            ->whereIn('status', ['completed', 'timed_out'])
            ->select('quiz_id')
            ->distinct()
            ->pluck('quiz_id')
            ->count();
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
     * إحصائيات شاملة لكورس معين
     */
    public function getStudentSubjectStats($userId, $subjectId): array
    {
        $subject = Subject::with(['sections.units.lessons', 'sections.units.quizzes', 'sections.units.questions'])
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
        
        return [
            'subject' => $subject,
            'progress' => $progress,
            'sections' => $sectionsStats,
        ];
    }
    
    /**
     * جميع الكورسات مع نسب التقدم
     */
    public function getAllStudentProgress($userId): array
    {
        $user = \App\Models\User::findOrFail($userId);
        
        $subjects = $user->subjects()
            ->with(['schoolClass.stage'])
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();
        
        $progressList = [];
        foreach ($subjects as $subject) {
            $progress = $this->calculateSubjectProgress($userId, $subject->id);
            $progressList[] = [
                'subject' => $subject,
                'progress' => $progress,
            ];
        }
        
        return $progressList;
    }
    
    /**
     * تفاصيل القسم مع قائمة الدروس والاختبارات والأسئلة
     */
    public function getSectionDetails($userId, $sectionId): array
    {
        $section = SubjectSection::with([
            'units.lessons' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'units.quizzes' => function($query) {
                $query->where('is_active', true)
                      ->where('is_published', true)
                      ->orderBy('order');
            },
            'units.questions' => function($query) {
                $query->where('is_active', true);
            }
        ])->findOrFail($sectionId);
        
        $progress = $this->calculateSectionProgress($userId, $sectionId);
        
        // تفاصيل الدروس
        $lessonsDetails = [];
        foreach ($section->units as $unit) {
            foreach ($unit->lessons->where('is_active', true) as $lesson) {
                $lessonProgress = $this->calculateLessonProgress($userId, $lesson->id);
                $lessonsDetails[] = [
                    'lesson' => $lesson,
                    'unit' => $unit,
                    'progress' => $lessonProgress,
                ];
            }
        }
        
        // تفاصيل الاختبارات
        $quizzesDetails = [];
        $quizAttempts = QuizAttempt::where('user_id', $userId)
            ->whereHas('quiz', function($query) use ($section) {
                $query->whereHas('unit', function($q) use ($section) {
                    $q->where('section_id', $section->id);
                });
            })
            ->get()
            ->keyBy('quiz_id');
        
        foreach ($section->units as $unit) {
            foreach ($unit->quizzes->where('is_active', true)->where('is_published', true) as $quiz) {
                $attempt = $quizAttempts->get($quiz->id);
                $quizzesDetails[] = [
                    'quiz' => $quiz,
                    'unit' => $unit,
                    'attempt' => $attempt,
                    'completed' => $attempt && in_array($attempt->status, ['completed', 'timed_out']),
                ];
            }
        }
        
        // تفاصيل الأسئلة
        $questionsDetails = [];
        $questionAttempts = QuestionAttempt::where('user_id', $userId)
            ->whereHas('question', function($query) use ($section) {
                $query->whereHas('units', function($q) use ($section) {
                    $q->where('units.section_id', $section->id);
                });
            })
            ->get()
            ->keyBy('question_id');
        
        foreach ($section->units as $unit) {
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
}

