<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ReviewQueueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:review-queue-list'])->only('index');
        $this->middleware(['permission:review-queue-lessons'])->only('lessons');
        $this->middleware(['permission:review-queue-quizzes'])->only('quizzes');
    }

    private function lessonReviewRelations(): array
    {
        return [
            'unit.section.subject.schoolClass.stage',
            'unit.section.subject.assignedTeachers',
            'section.subject.schoolClass.stage',
            'section.subject.assignedTeachers',
            'reviewer',
            'reviewComments',
        ];
    }

    private function applyLessonSubjectScope($query, callable $subjectConstraint): void
    {
        $query->where(function ($q) use ($subjectConstraint) {
            $q->whereHas('unit.section.subject', $subjectConstraint)
                ->orWhereHas('section.subject', $subjectConstraint);
        });
    }

    private function buildReviewStats($user, bool $isSupervisor): array
    {
        $lessonsQuery = Lesson::query();
        $quizzesQuery = Quiz::query();

        if ($isSupervisor) {
            $lessonsQuery->forSupervisor($user->id);
            $quizzesQuery->forSupervisor($user->id);
        }

        return [
            'lessons' => [
                'pending' => (clone $lessonsQuery)->pendingReview()->count(),
                'approved' => (clone $lessonsQuery)->approved()->count(),
                'rejected' => (clone $lessonsQuery)->rejected()->count(),
            ],
            'quizzes' => [
                'pending' => (clone $quizzesQuery)->pendingReview()->count(),
                'approved' => (clone $quizzesQuery)->approved()->count(),
                'rejected' => (clone $quizzesQuery)->rejected()->count(),
            ],
        ];
    }

    /**
     * عرض جميع العناصر قيد المراجعة
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user->usesSupervisorAssignmentScope();

        // إحصائيات
        $lessonsQuery = Lesson::with($this->lessonReviewRelations());
        $quizzesQuery = Quiz::with(['subject.schoolClass.stage', 'creator', 'reviewer', 'reviewComments']);

        // فلترة للمشرف
        if ($isSupervisor) {
            $lessonsQuery->forSupervisor($user->id);
            $quizzesQuery->forSupervisor($user->id);
        }

        $stats = [
            'lessons' => [
                'pending' => (clone $lessonsQuery)->pendingReview()->count(),
                'approved' => (clone $lessonsQuery)->approved()->count(),
                'rejected' => (clone $lessonsQuery)->rejected()->count(),
            ],
            'quizzes' => [
                'pending' => (clone $quizzesQuery)->pendingReview()->count(),
                'approved' => (clone $quizzesQuery)->approved()->count(),
                'rejected' => (clone $quizzesQuery)->rejected()->count(),
            ],
        ];

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $lessonsQuery->where('title', 'like', "%{$search}%");
            $quizzesQuery->where('title', 'like', "%{$search}%");
        }

        // فلترة حسب حالة المراجعة
        if ($request->filled('review_status')) {
            $status = $request->input('review_status');
            $lessonsQuery->where('review_status', $status);
            $quizzesQuery->where('review_status', $status);
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $this->applyLessonSubjectScope($lessonsQuery, function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
            $quizzesQuery->whereHas('subject', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $this->applyLessonSubjectScope($lessonsQuery, function ($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
            $quizzesQuery->where('subject_id', $subjectId);
        }

        // جلب البيانات
        $lessons = $lessonsQuery->pendingReview()->orderBy('submitted_for_review_at', 'desc')->paginate(10, ['*'], 'lessons_page');
        $quizzes = $quizzesQuery->pendingReview()->orderBy('submitted_for_review_at', 'desc')->paginate(10, ['*'], 'quizzes_page');

        // البيانات للفلترة
        if ($isSupervisor) {
            $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
            $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');

            $classes = SchoolClass::with('stage')
                ->active()
                ->ordered()
                ->when($classIds->isNotEmpty(), function ($q) use ($classIds) {
                    $q->whereIn('id', $classIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get();

            $subjects = Subject::with('schoolClass.stage')
                ->active()
                ->ordered()
                ->when($subjectIds->isNotEmpty(), function ($q) use ($subjectIds) {
                    $q->whereIn('id', $subjectIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get();
        } else {
            $classes = SchoolClass::with('stage')->active()->ordered()->get();
            $subjects = Subject::with('schoolClass.stage')->active()->ordered()->get();
        }

        return view('admin.pages.review-queue.index', compact(
            'lessons',
            'quizzes',
            'stats',
            'classes',
            'subjects'
        ));
    }

    /**
     * عرض الدروس قيد المراجعة فقط
     */
    public function lessons(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user->usesSupervisorAssignmentScope();

        $query = Lesson::with($this->lessonReviewRelations());

        // فلترة للمشرف
        if ($isSupervisor) {
            $query->forSupervisor($user->id);
        }

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // فلترة حسب حالة المراجعة
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        } else {
            $query->pendingReview();
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $this->applyLessonSubjectScope($query, function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $this->applyLessonSubjectScope($query, function ($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
        }

        $lessons = $query->orderBy('submitted_for_review_at', 'desc')->paginate(20);
        $stats = $this->buildReviewStats($user, $isSupervisor);

        // البيانات للفلترة
        if ($isSupervisor) {
            $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
            $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');

            $classes = SchoolClass::with('stage')
                ->active()
                ->ordered()
                ->when($classIds->isNotEmpty(), function ($q) use ($classIds) {
                    $q->whereIn('id', $classIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get();

            $subjects = Subject::with('schoolClass.stage')
                ->active()
                ->ordered()
                ->when($subjectIds->isNotEmpty(), function ($q) use ($subjectIds) {
                    $q->whereIn('id', $subjectIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get();
        } else {
            $classes = SchoolClass::with('stage')->active()->ordered()->get();
            $subjects = Subject::with('schoolClass.stage')->active()->ordered()->get();
        }

        return view('admin.pages.review-queue.lessons', compact('lessons', 'classes', 'subjects', 'stats'));
    }

    /**
     * عرض الاختبارات قيد المراجعة فقط
     */
    public function quizzes(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user->usesSupervisorAssignmentScope();

        $query = Quiz::with(['subject.schoolClass.stage', 'reviewer', 'reviewComments']);

        // فلترة للمشرف
        if ($isSupervisor) {
            $query->forSupervisor($user->id);
        }

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // فلترة حسب حالة المراجعة
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        } else {
            $query->pendingReview();
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $query->whereHas('subject', function($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        $quizzes = $query->orderBy('submitted_for_review_at', 'desc')->paginate(20);
        $stats = $this->buildReviewStats($user, $isSupervisor);

        // البيانات للفلترة
        if ($isSupervisor) {
            $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
            $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');

            $classes = SchoolClass::with('stage')
                ->active()
                ->ordered()
                ->when($classIds->isNotEmpty(), function ($q) use ($classIds) {
                    $q->whereIn('id', $classIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get();

            $subjects = Subject::with('schoolClass.stage')
                ->active()
                ->ordered()
                ->when($subjectIds->isNotEmpty(), function ($q) use ($subjectIds) {
                    $q->whereIn('id', $subjectIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get();
        } else {
            $classes = SchoolClass::with('stage')->active()->ordered()->get();
            $subjects = Subject::with('schoolClass.stage')->active()->ordered()->get();
        }

        return view('admin.pages.review-queue.quizzes', compact('quizzes', 'classes', 'subjects', 'stats'));
    }
}
