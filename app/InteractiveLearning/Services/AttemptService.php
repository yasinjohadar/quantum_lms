<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Models\LearningExperienceAttempt;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\GamificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AttemptService
{
    public function __construct(
        protected ExperienceGradingService $grading,
    ) {}

    /**
     * حفظ محاولة تجربة تفاعلية.
     *
     * العلامة تُحسب **على الخادم** من إجابات الطالب مقابل المخطط، ولا تُصدَّق
     * العلامة القادمة من المتصفح إطلاقاً (تُحفظ في result_json.client_reported
     * للتشخيص ورصد التلاعب فقط) — تماماً كما تُصحَّح الاختبارات العادية.
     *
     * @param  array<string, mixed>  $payload
     */
    public function store(LearningExperience $experience, User $user, array $payload): LearningExperienceAttempt
    {
        if (! $experience->isPublished()) {
            throw ValidationException::withMessages([
                'experience' => 'التجربة غير منشورة.',
            ]);
        }

        $answers = $payload['answers'] ?? [];
        if (! is_array($answers)) {
            $answers = [];
        }

        $graded = $this->grading->grade($experience, $answers);

        $attempt = LearningExperienceAttempt::create([
            'learning_experience_id' => $experience->id,
            'user_id' => $user->id,
            'score' => $graded['score'],
            'total' => $graded['total'],
            'percentage' => $graded['percentage'],
            'passed' => $graded['passed'],
            'duration' => max(0, (int) ($payload['duration'] ?? 0)),
            'started_at' => isset($payload['startedAt']) ? Carbon::parse($payload['startedAt']) : null,
            'finished_at' => isset($payload['finishedAt']) ? Carbon::parse($payload['finishedAt']) : now(),
            'answers_json' => $answers,
            'result_json' => [
                'score' => $graded['score'],
                'total' => $graded['total'],
                'percentage' => $graded['percentage'],
                'passed' => $graded['passed'],
                'per_question' => $graded['perQuestion'],
                'duration' => max(0, (int) ($payload['duration'] ?? 0)),
                'session_version' => (string) ($payload['sessionVersion'] ?? ''),
                'engine_version' => (string) ($payload['engineVersion'] ?? ''),
                // ما أعلنه المتصفح — للمقارنة ورصد التلاعب، لا يُعتمد عليه
                'client_reported' => [
                    'score' => isset($payload['score']) ? (float) $payload['score'] : null,
                    'total' => isset($payload['total']) ? (float) $payload['total'] : null,
                    'percentage' => isset($payload['percentage']) ? (float) $payload['percentage'] : null,
                ],
                'saved_at' => now()->toIso8601String(),
            ],
        ]);

        $this->awardCompletion($attempt);

        return $attempt;
    }

    /**
     * تحفيز + تحليلات بعد حفظ المحاولة. أي فشل هنا لا يُفشل حفظ العلامة.
     */
    protected function awardCompletion(LearningExperienceAttempt $attempt): void
    {
        try {
            // النقاط تُمنح مرة واحدة لكل تجربة لكل طالب.
            // المحاولات غير محدودة هنا (بخلاف max_attempts في الاختبارات العادية)،
            // فلو مُنحت النقاط لكل محاولة صار بالإمكان حصدها بإعادة الحل بلا نهاية.
            if ($this->isFirstAttempt($attempt)) {
                app(GamificationService::class)->processInteractiveExperienceCompletion(
                    $attempt->fresh(['user', 'experience'])
                );
            }
        } catch (\Throwable $e) {
            Log::warning('ILE gamification failed: '.$e->getMessage(), ['attempt_id' => $attempt->id]);
        }

        try {
            $experience = $attempt->experience;
            // نستخدم نفس اسم الحدث المستخدم للاختبارات العادية حتى تحتسبها
            // AnalyticsService::getStudentAnalytics()['quizzes_completed'].
            app(AnalyticsService::class)->trackEvent('complete_quiz', $attempt->user_id, [
                'subject_id' => $experience?->subject_id,
                'learning_experience_id' => $attempt->learning_experience_id,
                'attempt_id' => $attempt->id,
                'score' => $attempt->score,
                'percentage' => $attempt->percentage,
                'passed' => $attempt->passed,
                'kind' => 'interactive',
            ]);
        } catch (\Throwable $e) {
            Log::warning('ILE analytics failed: '.$e->getMessage(), ['attempt_id' => $attempt->id]);
        }
    }

    /**
     * هل هذه أول محاولة لهذا الطالب في هذه التجربة؟
     */
    protected function isFirstAttempt(LearningExperienceAttempt $attempt): bool
    {
        return ! LearningExperienceAttempt::query()
            ->where('learning_experience_id', $attempt->learning_experience_id)
            ->where('user_id', $attempt->user_id)
            ->where('id', '<', $attempt->id)
            ->exists();
    }
}
