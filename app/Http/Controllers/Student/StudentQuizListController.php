<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Models\LearningExperienceAttempt;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class StudentQuizListController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.user.active']);
    }

    /**
     * عرض الاختبارات المتاحة للطالب
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // جلب المواد المسجلة
        $subjects = $user->subjects()->wherePivot('status', 'active')->get();
        $subjectIds = $subjects->pluck('id')->toArray();

        // جلب الاختبارات المتاحة
        $query = Quiz::with(['subject', 'unit'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->whereIn('subject_id', $subjectIds)
            ->available();

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // فلترة حسب الحالة (متاح، منتهي، قادم)
        if ($request->filled('status')) {
            $now = now();
            switch ($request->status) {
                case 'available':
                    $query->where(function ($q) use ($now) {
                        $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
                    })->where(function ($q) use ($now) {
                        $q->whereNull('available_to')->orWhere('available_to', '>=', $now);
                    });
                    break;
                case 'upcoming':
                    $query->where('available_from', '>', $now);
                    break;
                case 'expired':
                    $query->where('available_to', '<', $now);
                    break;
            }
        }

        // عند فلترة قادم/منتهي لا نعرض التفاعلية (ليس لها جدول زمني)
        $includeInteractive = ! $request->filled('status') || $request->status === 'available';

        $quizItems = collect();
        $regularQuizzes = $query->orderBy('available_from', 'desc')
            ->orderBy('title')
            ->get();

        foreach ($regularQuizzes as $quiz) {
            $attempts = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->get();

            $quiz->user_attempts = $attempts;
            $quiz->can_attempt = $quiz->canUserAttempt($user)['can'];
            $quiz->last_attempt = $attempts->sortByDesc('started_at')->first();
            $quizItems->push([
                'kind' => 'quiz',
                'sort' => $quiz->title,
                'model' => $quiz,
            ]);
        }

        if ($includeInteractive && ! empty($subjectIds)) {
            $ileQuery = LearningExperience::query()
                ->with(['subject', 'unit'])
                ->where('status', LearningExperience::STATUS_PUBLISHED)
                ->whereIn('subject_id', $subjectIds);

            if ($request->filled('subject_id')) {
                $ileQuery->where('subject_id', $request->subject_id);
            }

            $experiences = $ileQuery->orderBy('title')->get();
            $attemptGroups = LearningExperienceAttempt::query()
                ->where('user_id', $user->id)
                ->whereIn('learning_experience_id', $experiences->pluck('id'))
                ->orderByDesc('finished_at')
                ->get()
                ->groupBy('learning_experience_id');

            foreach ($experiences as $experience) {
                $attempts = $attemptGroups->get($experience->id, collect());
                $experience->user_attempts = $attempts;
                $experience->user_attempts_count = $attempts->count();
                $experience->last_attempt = $attempts->first();
                $quizItems->push([
                    'kind' => 'interactive',
                    'sort' => $experience->title,
                    'model' => $experience,
                ]);
            }
        }

        $sorted = $quizItems->sortBy('sort', SORT_NATURAL | SORT_FLAG_CASE)->values();
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 15;
        $quizzes = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('student.pages.quizzes.index', compact('quizzes', 'subjects'));
    }

    /**
     * عرض نتائج الاختبارات للطالب (عادية + تفاعلية)
     */
    public function results(Request $request)
    {
        $user = Auth::user();
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 15;

        $quizQuery = QuizAttempt::with(['quiz.subject', 'quiz.unit'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'graded', 'timeout']);

        if ($request->filled('subject_id')) {
            $quizQuery->whereHas('quiz', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('status')) {
            $quizQuery->where('status', $request->status);
        }

        if ($request->filled('passed')) {
            $quizQuery->where('passed', $request->passed === '1');
        }

        $includeInteractive = ! $request->filled('status') || in_array($request->status, ['completed', 'graded'], true);

        $rows = $quizQuery->get()->map(function (QuizAttempt $attempt) {
            return [
                'kind' => 'quiz',
                'sort_at' => $attempt->finished_at ?? $attempt->started_at,
                'attempt' => $attempt,
            ];
        });

        if ($includeInteractive) {
            $ileQuery = LearningExperienceAttempt::with(['experience.subject', 'experience.unit'])
                ->where('user_id', $user->id);

            if ($request->filled('subject_id')) {
                $ileQuery->whereHas('experience', function ($q) use ($request) {
                    $q->where('subject_id', $request->subject_id);
                });
            }

            if ($request->filled('passed')) {
                $wantPassed = $request->passed === '1';
                $ileQuery->where(function ($q) use ($wantPassed) {
                    if ($wantPassed) {
                        $q->where('percentage', '>=', 50);
                    } else {
                        $q->where('percentage', '<', 50);
                    }
                });
            }

            $ileRows = $ileQuery->get()->map(function (LearningExperienceAttempt $attempt) {
                return [
                    'kind' => 'interactive',
                    'sort_at' => $attempt->finished_at ?? $attempt->started_at ?? $attempt->created_at,
                    'attempt' => $attempt,
                ];
            });

            $rows = $rows->concat($ileRows);
        }

        $sorted = $rows->sortByDesc(function (array $row) {
            $at = $row['sort_at'];

            return $at ? $at->getTimestamp() : 0;
        })->values();

        $paginator = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $subjects = $user->subjects()->wherePivot('status', 'active')->get();

        return view('student.pages.quizzes.results', [
            'attempts' => $paginator,
            'subjects' => $subjects,
        ]);
    }
}
