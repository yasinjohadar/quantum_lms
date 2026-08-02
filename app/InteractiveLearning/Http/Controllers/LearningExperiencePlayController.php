<?php

namespace App\InteractiveLearning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Services\ArabicTtsService;
use App\InteractiveLearning\Services\AttemptService;
use App\InteractiveLearning\Services\SchemaValidator;
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

        $validation = $this->schemaValidator->validate($learningExperience->schema_json ?? []);
        if (! $validation['valid'] && ! $isPreview) {
            abort(422, 'Schema غير صالح.');
        }

        return view('learning-experiences.play', [
            'experience' => $learningExperience,
            'isPreview' => (bool) $isPreview && ! $learningExperience->isPublished(),
            'engineVersion' => SchemaValidator::ENGINE_VERSION,
            'schemaVersion' => $learningExperience->schema_version ?: SchemaValidator::SCHEMA_VERSION,
        ]);
    }

    public function storeAttempt(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'startedAt' => ['nullable', 'date'],
            'finishedAt' => ['nullable', 'date'],
            'sessionVersion' => ['nullable', 'string'],
            'engineVersion' => ['nullable', 'string'],
            'answers' => ['nullable', 'array'],
        ]);

        $attempt = $this->attemptService->store(
            $learningExperience,
            $request->user(),
            array_merge($request->all(), $data)
        );

        return response()->json([
            'ok' => true,
            'attempt_id' => $attempt->id,
            'percentage' => $attempt->percentage,
            'score' => $attempt->score,
            'total' => $attempt->total,
        ]);
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
