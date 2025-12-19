<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentLessonController extends Controller
{
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
        
        // تحميل الأقسام مع الوحدات والدروس
        $sections = $subject->sections()
            ->with(['units.lessons' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('order');
            }])
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        
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
        
        // الحصول على الاختبارات المرتبطة بنفس الوحدة
        $quizzes = Quiz::where('unit_id', $lesson->unit_id)
            ->where('subject_id', $subject->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->with(['questions' => function($query) {
                $query->orderBy('quiz_questions.order');
            }])
            ->orderBy('order')
            ->get();
        
        // الحصول على IDs الأسئلة المرتبطة باختبارات
        $quizQuestionIds = [];
        foreach ($quizzes as $quiz) {
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
        
        // الحصول على محاولات الطالب للاختبارات
        $quizAttempts = \App\Models\QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
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
        
        return view('student.pages.lessons.lesson-show', compact(
            'lesson',
            'unitLessons',
            'previousLesson',
            'nextLesson',
            'subject',
            'videoTypes',
            'quizzes',
            'questions',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties',
            'questionAttempts',
            'quizAttempts'
        ));
    }
}

