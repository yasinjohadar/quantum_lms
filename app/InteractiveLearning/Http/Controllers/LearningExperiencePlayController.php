<?php

namespace App\InteractiveLearning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Services\ArabicTtsService;
use App\InteractiveLearning\Services\AttemptService;
use App\InteractiveLearning\Services\SchemaValidator;
use App\InteractiveLearning\Support\FeedbackPhrases;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LearningExperiencePlayController extends Controller
{
    public function __construct(
        protected AttemptService $attemptService,
        protected SchemaValidator $schemaValidator,
        protected ArabicTtsService $ttsService
    ) {}

    public function show(LearningExperience $learningExperience): View
    {
        $user = auth()->user();
        $isPreview = $user && method_exists($user, 'hasRole') && (
            $user->hasRole(['admin', 'supervisor', 'teacher'])
            || ($learningExperience->created_by && (int) $learningExperience->created_by === (int) $user->id)
        );

        if (! $learningExperience->isPublished() && ! $isPreview) {
            abort(404);
        }

        $this->authorizeAccess($learningExperience, $user, $isPreview);

        $validation = $this->schemaValidator->validate($learningExperience->schema_json ?? []);
        if (! $validation['valid'] && ! $isPreview) {
            abort(422, 'Schema غير صالح.');
        }

        return view('learning-experiences.play', [
            'experience' => $learningExperience,
            'isPreview' => (bool) $isPreview && ! $learningExperience->isPublished(),
            'engineVersion' => SchemaValidator::ENGINE_VERSION,
            'schemaVersion' => $learningExperience->schema_version ?: SchemaValidator::SCHEMA_VERSION,
            'feedbackPhrases' => FeedbackPhrases::forPlayer(),
        ]);
    }

    public function storeAttempt(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        // score/total/percentage تُقبل للتوافق مع المشغّل لكنها لا تُصدَّق —
        // AttemptService يعيد التصحيح على الخادم ويحفظ نتيجته هو.
        $data = $request->validate([
            'score' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'startedAt' => ['nullable', 'date'],
            'finishedAt' => ['nullable', 'date'],
            'sessionVersion' => ['nullable', 'string'],
            'engineVersion' => ['nullable', 'string'],
            'answers' => ['nullable', 'array'],
        ]);

        $this->authorizeAccess($learningExperience, $request->user(), false);

        // لا نمرّر $request->all() — كان يُسرّب مفاتيح غير مُتحقَّقة إلى result_json
        $attempt = $this->attemptService->store($learningExperience, $request->user(), $data);

        return response()->json([
            'ok' => true,
            'attempt_id' => $attempt->id,
            'percentage' => $attempt->percentage,
            'score' => $attempt->score,
            'total' => $attempt->total,
            'passed' => $attempt->passed,
        ]);
    }

    /**
     * الطالب لا يفتح تجربة إلا إذا كان يستحق مادتها.
     *
     * قبل ذلك كان أي مستخدم مسجَّل يفتح أي تجربة منشورة بالمعرّف؛ وبعد ربط
     * المحاولات بالنقاط والشارات صارت هذه ثغرة لحصد النقاط.
     */
    protected function authorizeAccess(LearningExperience $experience, ?User $user, bool $isPreview): void
    {
        if ($isPreview || ! $user) {
            return;
        }

        // المعلّم/المشرف/الإدمن يعاينون أي تجربة
        if (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'supervisor', 'teacher'])) {
            return;
        }

        $subject = $experience->subject;
        if (! $subject) {
            // تجربة بلا مادة لا تُنسب لأي منهج — لا يجوز حلّها ولا تسجيل علامة لها
            abort(403, 'هذه التجربة غير مرتبطة بمادة، لذا لا يمكن حلّها.');
        }

        if (! $user->canAccessSubjectAsStudent($subject)) {
            abort(403, 'لا تملك صلاحية الوصول إلى مادة هذه التجربة.');
        }
    }

    public function tts(Request $request): BinaryFileResponse|Response|JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:200'],
            'lang' => ['nullable', 'string', 'in:ar,en'],
        ]);

        try {
            $path = $this->ttsService->pathFor($data['text'], $data['lang'] ?? 'ar');

            return response()->file($path, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
