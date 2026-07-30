<?php

namespace App\AiHtmlQuiz\Http\Controllers;

use App\AiHtmlQuiz\Models\AiHtmlQuiz;
use App\AiHtmlQuiz\Services\AiHtmlQuizAttemptService;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleAssembler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AiHtmlQuizPlayController extends Controller
{
    public function __construct(
        protected AiHtmlQuizAttemptService $attemptService,
        protected AiHtmlQuizBundleAssembler $assembler
    ) {}

    public function show(AiHtmlQuiz $aiHtmlQuiz): View
    {
        $user = auth()->user();
        $isPreview = $user && method_exists($user, 'hasRole') && (
            $user->hasRole(['admin', 'supervisor', 'teacher'])
            || ($aiHtmlQuiz->created_by && (int) $aiHtmlQuiz->created_by === (int) $user->id)
        );

        if (! $aiHtmlQuiz->isPublished() && ! $isPreview) {
            abort(404);
        }

        if (! $aiHtmlQuiz->hasBundle()) {
            abort(404, 'لا توجد حزمة للتشغيل.');
        }

        return view('ai-html-quizzes.play', [
            'quiz' => $aiHtmlQuiz,
            'isPreview' => (bool) $isPreview && ! $aiHtmlQuiz->isPublished(),
            'bundleUrl' => route('ai-html-quizzes.bundle', $aiHtmlQuiz),
            'attemptUrl' => route('ai-html-quizzes.attempts.store', $aiHtmlQuiz),
        ]);
    }

    public function bundle(AiHtmlQuiz $aiHtmlQuiz): Response
    {
        $user = auth()->user();
        $isPreview = $user && method_exists($user, 'hasRole') && (
            $user->hasRole(['admin', 'supervisor', 'teacher'])
            || ($aiHtmlQuiz->created_by && (int) $aiHtmlQuiz->created_by === (int) $user->id)
        );

        if (! $aiHtmlQuiz->isPublished() && ! $isPreview) {
            abort(404);
        }

        if (! $aiHtmlQuiz->hasBundle()) {
            abort(404);
        }

        return response($this->assembler->assembleDocument($aiHtmlQuiz), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function storeAttempt(Request $request, AiHtmlQuiz $aiHtmlQuiz): JsonResponse
    {
        if (! $aiHtmlQuiz->isPublished()) {
            $user = $request->user();
            $isPreview = $user && method_exists($user, 'hasRole') && (
                $user->hasRole(['admin', 'supervisor', 'teacher'])
                || ($aiHtmlQuiz->created_by && (int) $aiHtmlQuiz->created_by === (int) $user->id)
            );
            if (! $isPreview) {
                abort(404);
            }
        }

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:0'],
            'percentage' => ['nullable', 'numeric'],
            'durationSeconds' => ['nullable', 'integer', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'answers' => ['nullable', 'array'],
            'startedAt' => ['nullable', 'date'],
            'finishedAt' => ['nullable', 'date'],
        ]);

        $attempt = $this->attemptService->store($aiHtmlQuiz, $request->user(), $data);

        return response()->json([
            'ok' => true,
            'attempt_id' => $attempt->id,
        ]);
    }
}
