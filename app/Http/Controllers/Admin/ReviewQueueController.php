<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\StaffNotificationService;
use App\Services\StudentContentNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewQueueController extends Controller
{
    public function __construct(
        private StaffNotificationService $staffNotificationService,
        private StudentContentNotificationService $studentContentNotificationService,
    ) {
        $this->middleware(['permission:review-queue-list'])->only('index');
        $this->middleware(['permission:review-queue-lessons'])->only('lessons');
        $this->middleware(['permission:review-queue-quizzes'])->only('quizzes');
        $this->middleware(['permission:lesson-approve-review'])->only('bulkApproveLessons');
        $this->middleware(['permission:quiz-approve-review'])->only('bulkApproveQuizzes');
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

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user->usesSupervisorAssignmentScope();

        $lessonsQuery = Lesson::with($this->lessonReviewRelations());
        $quizzesQuery = Quiz::with(['subject.schoolClass.stage', 'creator', 'reviewer', 'reviewComments']);

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

        if ($request->filled('search')) {
            $search = $request->input('search');
            $lessonsQuery->where('title', 'like', "%{$search}%");
            $quizzesQuery->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('review_status')) {
            $status = $request->input('review_status');
            $lessonsQuery->where('review_status', $status);
            $quizzesQuery->where('review_status', $status);
        }

        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $this->applyLessonSubjectScope($lessonsQuery, function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
            $quizzesQuery->whereHas('subject', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $this->applyLessonSubjectScope($lessonsQuery, function ($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
            $quizzesQuery->where('subject_id', $subjectId);
        }

        $lessons = $lessonsQuery->pendingReview()->orderBy('submitted_for_review_at', 'desc')->paginate(10, ['*'], 'lessons_page');
        $quizzes = $quizzesQuery->pendingReview()->orderBy('submitted_for_review_at', 'desc')->paginate(10, ['*'], 'quizzes_page');

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

    public function lessons(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user->usesSupervisorAssignmentScope();

        $query = Lesson::with($this->lessonReviewRelations());

        if ($isSupervisor) {
            $query->forSupervisor($user->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        } else {
            $query->pendingReview();
        }

        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $this->applyLessonSubjectScope($query, function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $this->applyLessonSubjectScope($query, function ($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
        }

        $lessons = $query->orderBy('submitted_for_review_at', 'desc')->paginate(20);
        $stats = $this->buildReviewStats($user, $isSupervisor);

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

    public function quizzes(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user->usesSupervisorAssignmentScope();

        $query = Quiz::with(['subject.schoolClass.stage', 'reviewer', 'reviewComments']);

        if ($isSupervisor) {
            $query->forSupervisor($user->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        } else {
            $query->pendingReview();
        }

        if ($request->filled('class_id')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        $quizzes = $query->orderBy('submitted_for_review_at', 'desc')->paginate(20);
        $stats = $this->buildReviewStats($user, $isSupervisor);

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

    public function bulkApproveLessons(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->canReviewContent()) {
            abort(403, 'غير مصرح لك بالموافقة على الدروس');
        }

        $validated = $request->validate([
            'approve_all' => ['nullable', 'boolean'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:lessons,id'],
        ]);

        $approveAll = (bool) ($validated['approve_all'] ?? false);
        $ids = array_values(array_unique(array_map('intval', $validated['ids'] ?? [])));

        if (! $approveAll && $ids === []) {
            return redirect()->back()->with('error', 'يرجى تحديد درس واحد على الأقل للموافقة.');
        }

        $query = Lesson::query()->pendingReview();
        if ($user->usesSupervisorAssignmentScope()) {
            $query->forSupervisor($user->id);
        }
        if (! $approveAll) {
            $query->whereIn('id', $ids);
        }

        $lessons = $query->get();
        if ($lessons->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد دروس قابلة للموافقة ضمن التحديد.');
        }

        $approved = 0;

        foreach ($lessons as $lesson) {
            try {
                DB::transaction(function () use ($lesson, $user, &$approved) {
                    $before = clone $lesson;

                    $lesson->update([
                        'review_status' => Lesson::REVIEW_STATUS_APPROVED,
                        'is_active' => true,
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                    ]);

                    $approved++;

                    try {
                        $this->staffNotificationService->notifyLessonReviewOutcome($lesson->fresh(), $user, true);
                    } catch (\Throwable $e) {
                        Log::error('Lesson bulk approve notification failed: '.$e->getMessage(), [
                            'lesson_id' => $lesson->id,
                        ]);
                    }

                    $this->studentContentNotificationService->notifyIfLessonBecameVisible(
                        $before,
                        $lesson->fresh(),
                        $user
                    );
                });
            } catch (\Throwable $e) {
                Log::error('Lesson bulk approve failed: '.$e->getMessage(), [
                    'lesson_id' => $lesson->id,
                ]);
            }
        }

        if ($approved === 0) {
            return redirect()->back()->with('error', 'تعذّرت الموافقة على الدروس المحددة.');
        }

        $message = $approveAll
            ? "تم قبول جميع الدروس قيد المراجعة ({$approved})."
            : "تم قبول {$approved} درس/دروساً محددة.";

        return redirect()->back()->with('success', $message);
    }

    public function bulkApproveQuizzes(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->canReviewContent()) {
            abort(403, 'غير مصرح لك بالموافقة على الاختبارات');
        }

        $validated = $request->validate([
            'approve_all' => ['nullable', 'boolean'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:quizzes,id'],
        ]);

        $approveAll = (bool) ($validated['approve_all'] ?? false);
        $ids = array_values(array_unique(array_map('intval', $validated['ids'] ?? [])));

        if (! $approveAll && $ids === []) {
            return redirect()->back()->with('error', 'يرجى تحديد اختبار واحد على الأقل للموافقة.');
        }

        $query = Quiz::query()->pendingReview()->withCount('questions');
        if ($user->usesSupervisorAssignmentScope()) {
            $query->forSupervisor($user->id);
        }
        if (! $approveAll) {
            $query->whereIn('id', $ids);
        }

        $quizzes = $query->get();
        if ($quizzes->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد اختبارات قابلة للموافقة ضمن التحديد.');
        }

        $approved = 0;
        $skippedNoQuestions = 0;

        foreach ($quizzes as $quiz) {
            if ((int) $quiz->questions_count === 0) {
                $skippedNoQuestions++;

                continue;
            }

            try {
                DB::transaction(function () use ($quiz, $user, &$approved) {
                    $before = clone $quiz;

                    $quiz->update([
                        'review_status' => Quiz::REVIEW_STATUS_APPROVED,
                        'is_published' => true,
                        'is_active' => true,
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                    ]);

                    $approved++;

                    try {
                        $this->staffNotificationService->notifyQuizReviewOutcome($quiz->fresh(), $user, true);
                    } catch (\Throwable $e) {
                        Log::error('Quiz bulk approve notification failed: '.$e->getMessage(), [
                            'quiz_id' => $quiz->id,
                        ]);
                    }

                    $this->studentContentNotificationService->notifyIfQuizBecameVisible(
                        $before,
                        $quiz->fresh(),
                        $user
                    );
                });
            } catch (\Throwable $e) {
                Log::error('Quiz bulk approve failed: '.$e->getMessage(), [
                    'quiz_id' => $quiz->id,
                ]);
            }
        }

        if ($approved === 0) {
            $error = $skippedNoQuestions > 0
                ? 'تعذّرت الموافقة: الاختبارات المحددة بدون أسئلة.'
                : 'تعذّرت الموافقة على الاختبارات المحددة.';

            return redirect()->back()->with('error', $error);
        }

        $message = $approveAll
            ? "تم قبول جميع الاختبارات القابلة للموافقة ({$approved})."
            : "تم قبول {$approved} اختبار/اختبارات محددة.";

        if ($skippedNoQuestions > 0) {
            $message .= " وتم تخطي {$skippedNoQuestions} بدون أسئلة.";
        }

        return redirect()->back()->with('success', $message);
    }
}
