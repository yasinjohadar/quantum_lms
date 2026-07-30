<?php

namespace App\AiHtmlQuiz\Http\Controllers\Admin;

use App\AiHtmlQuiz\Models\AiHtmlQuiz;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleAssembler;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleNormalizer;
use App\AiHtmlQuiz\Services\AiHtmlQuizGenerationService;
use App\AiHtmlQuiz\Support\AiHtmlQuizQuestionTypes;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AiHtmlQuizController extends Controller
{
    public function __construct(
        protected AiHtmlQuizGenerationService $generationService,
        protected AiHtmlQuizBundleNormalizer $normalizer,
        protected AiHtmlQuizBundleAssembler $assembler
    ) {}

    public function index(Request $request): View
    {
        $query = AiHtmlQuiz::query()->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = trim($request->string('q')->toString())) {
            $query->where('title', 'like', "%{$search}%");
        }

        $stats = [
            'total' => AiHtmlQuiz::query()->count(),
            'published' => AiHtmlQuiz::query()->where('status', AiHtmlQuiz::STATUS_PUBLISHED)->count(),
            'draft' => AiHtmlQuiz::query()->where('status', AiHtmlQuiz::STATUS_DRAFT)->count(),
            'review' => AiHtmlQuiz::query()->where('status', AiHtmlQuiz::STATUS_REVIEW)->count(),
        ];

        return view('admin.pages.ai-html-quizzes.index', [
            'quizzes' => $query->paginate(20)->withQueryString(),
            'statuses' => AiHtmlQuiz::STATUSES,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $aiModels = AIModel::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderByDesc('priority')
            ->get(['id', 'name', 'provider', 'is_default']);

        return view('admin.pages.ai-html-quizzes.create', [
            'aiModels' => $aiModels,
            'questionTypes' => AiHtmlQuizQuestionTypes::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $typeKeys = implode(',', AiHtmlQuizQuestionTypes::keys());
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'topic' => ['required', 'string', 'max:500'],
            'objectives' => ['nullable', 'string', 'max:2000'],
            'question_count' => ['nullable', 'integer', 'min:3', 'max:8'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'question_types' => ['required', 'array', 'min:1'],
            'question_types.*' => ['string', 'in:'.$typeKeys],
            'interaction_hints' => ['nullable', 'string', 'max:2000'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
        ]);

        $questionTypes = AiHtmlQuizQuestionTypes::filterValid($data['question_types'] ?? []);

        $quiz = AiHtmlQuiz::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => AiHtmlQuiz::STATUS_DRAFT,
            'schema_version' => AiHtmlQuiz::SCHEMA_VERSION,
            'created_by' => $request->user()->id,
            'prompt_meta' => [
                'topic' => trim($data['topic']),
                'objectives' => $data['objectives'] ?? '',
                'question_count' => (int) ($data['question_count'] ?? 5),
                'difficulty' => $data['difficulty'] ?? 'medium',
                'question_types' => $questionTypes,
                'interaction_hints' => $data['interaction_hints'] ?? '',
                'ai_model_id' => $data['ai_model_id'] ?? null,
            ],
        ]);

        return redirect()
            ->route('admin.ai-html-quizzes.edit', $quiz)
            ->with('success', 'تم إنشاء الاختبار. يمكنك التوليد بالذكاء الاصطناعي ثم المراجعة والنشر.');
    }

    public function edit(AiHtmlQuiz $aiHtmlQuiz): View
    {
        $aiModels = AIModel::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderByDesc('priority')
            ->get(['id', 'name', 'provider', 'is_default']);

        return view('admin.pages.ai-html-quizzes.edit', [
            'quiz' => $aiHtmlQuiz,
            'aiModels' => $aiModels,
            'questionTypes' => AiHtmlQuizQuestionTypes::all(),
            'previewDocument' => $aiHtmlQuiz->hasBundle()
                ? $this->assembler->assembleDocument($aiHtmlQuiz)
                : null,
        ]);
    }

    public function update(Request $request, AiHtmlQuiz $aiHtmlQuiz): RedirectResponse
    {
        $typeKeys = implode(',', AiHtmlQuizQuestionTypes::keys());
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bundle_html' => ['nullable', 'string'],
            'bundle_css' => ['nullable', 'string'],
            'bundle_js' => ['nullable', 'string'],
            'topic' => ['nullable', 'string', 'max:500'],
            'objectives' => ['nullable', 'string', 'max:2000'],
            'question_count' => ['nullable', 'integer', 'min:3', 'max:8'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'question_types' => ['nullable', 'array'],
            'question_types.*' => ['string', 'in:'.$typeKeys],
            'interaction_hints' => ['nullable', 'string', 'max:2000'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
        ]);

        $meta = $aiHtmlQuiz->prompt_meta ?? [];
        if (array_key_exists('topic', $data) && trim((string) ($data['topic'] ?? '')) !== '') {
            $meta['topic'] = trim((string) $data['topic']);
        }
        $meta['objectives'] = $data['objectives'] ?? ($meta['objectives'] ?? '');
        $meta['question_count'] = (int) ($data['question_count'] ?? ($meta['question_count'] ?? 5));
        $meta['difficulty'] = $data['difficulty'] ?? ($meta['difficulty'] ?? 'medium');
        $meta['interaction_hints'] = $data['interaction_hints'] ?? ($meta['interaction_hints'] ?? '');
        if (array_key_exists('question_types', $data) && is_array($data['question_types'])) {
            $meta['question_types'] = AiHtmlQuizQuestionTypes::filterValid($data['question_types']);
        }
        if (array_key_exists('ai_model_id', $data)) {
            $meta['ai_model_id'] = $data['ai_model_id'];
        }

        $html = (string) ($data['bundle_html'] ?? $aiHtmlQuiz->bundle_html ?? '');
        $css = (string) ($data['bundle_css'] ?? $aiHtmlQuiz->bundle_css ?? '');
        $js = (string) ($data['bundle_js'] ?? $aiHtmlQuiz->bundle_js ?? '');

        if (trim($html.$css.$js) !== '') {
            if ($this->normalizer->hasDisallowedExternalScripts($html, $js)) {
                return back()->withInput()->withErrors(['bundle_js' => 'الحزمة تحتوي سكربتات خارجية غير مسموحة.']);
            }
            try {
                $normalized = $this->normalizer->normalize([
                    'title' => $data['title'],
                    'html' => $html,
                    'css' => $css,
                    'js' => $js,
                ]);
                $html = $normalized['html'];
                $css = $normalized['css'];
                $js = $normalized['js'];
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['bundle_html' => $e->getMessage()]);
            }
        }

        $aiHtmlQuiz->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'prompt_meta' => $meta,
            'bundle_html' => $html !== '' ? $html : $aiHtmlQuiz->bundle_html,
            'bundle_css' => $css !== '' ? $css : $aiHtmlQuiz->bundle_css,
            'bundle_js' => $js !== '' ? $js : $aiHtmlQuiz->bundle_js,
        ]);

        return back()->with('success', 'تم حفظ الاختبار.');
    }

    public function destroy(AiHtmlQuiz $aiHtmlQuiz): RedirectResponse
    {
        $aiHtmlQuiz->delete();

        return redirect()
            ->route('admin.ai-html-quizzes.index')
            ->with('success', 'تم حذف الاختبار.');
    }

    public function transition(Request $request, AiHtmlQuiz $aiHtmlQuiz): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', AiHtmlQuiz::STATUSES)],
        ]);

        if (! $aiHtmlQuiz->canTransitionTo($data['status'])) {
            return back()->withErrors(['status' => 'انتقال الحالة غير مسموح.']);
        }

        if ($data['status'] === AiHtmlQuiz::STATUS_PUBLISHED && ! $aiHtmlQuiz->hasBundle()) {
            return back()->withErrors(['status' => 'لا يمكن النشر بدون حزمة HTML/CSS/JS.']);
        }

        $aiHtmlQuiz->update(['status' => $data['status']]);

        return back()->with('success', 'تم تحديث الحالة.');
    }

    public function previewBundle(AiHtmlQuiz $aiHtmlQuiz): Response
    {
        if (! $aiHtmlQuiz->hasBundle()) {
            abort(404, 'لا توجد حزمة للمعاينة.');
        }

        return response($this->assembler->assembleDocument($aiHtmlQuiz), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function aiGenerate(Request $request, AiHtmlQuiz $aiHtmlQuiz): JsonResponse
    {
        $typeKeys = implode(',', AiHtmlQuizQuestionTypes::keys());
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:500'],
            'objectives' => ['nullable', 'string', 'max:2000'],
            'question_count' => ['nullable', 'integer', 'min:3', 'max:8'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'question_types' => ['nullable', 'array'],
            'question_types.*' => ['string', 'in:'.$typeKeys],
            'interaction_hints' => ['nullable', 'string', 'max:2000'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
        ]);

        $meta = $aiHtmlQuiz->prompt_meta ?? [];
        $topic = trim((string) ($data['topic'] ?? $meta['topic'] ?? ''));
        if ($topic === '') {
            return response()->json(['ok' => false, 'message' => 'الموضوع مطلوب بدقة.'], 422);
        }
        $objectives = (string) ($data['objectives'] ?? $meta['objectives'] ?? '');
        $count = (int) ($data['question_count'] ?? $meta['question_count'] ?? 5);
        $difficulty = (string) ($data['difficulty'] ?? $meta['difficulty'] ?? 'medium');
        $hints = (string) ($data['interaction_hints'] ?? $meta['interaction_hints'] ?? '');
        $questionTypes = array_key_exists('question_types', $data)
            ? AiHtmlQuizQuestionTypes::filterValid($data['question_types'] ?? [])
            : AiHtmlQuizQuestionTypes::filterValid($meta['question_types'] ?? []);
        $modelId = isset($data['ai_model_id'])
            ? (int) $data['ai_model_id']
            : (isset($meta['ai_model_id']) ? (int) $meta['ai_model_id'] : null);

        $aiHtmlQuiz->update([
            'prompt_meta' => array_merge($meta, [
                'topic' => $topic,
                'objectives' => $objectives,
                'question_count' => $count,
                'difficulty' => $difficulty,
                'interaction_hints' => $hints,
                'question_types' => $questionTypes,
                'ai_model_id' => $modelId,
            ]),
        ]);

        try {
            $bundle = $this->generationService->generate(
                $topic,
                $objectives,
                $count,
                $difficulty,
                $hints,
                $modelId,
                $questionTypes
            );
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'bundle' => [
                'title' => $bundle['title'],
                'html' => $bundle['html'],
                'css' => $bundle['css'],
                'js' => $bundle['js'],
                'summary' => $bundle['summary'],
                'answer_key' => $bundle['answer_key'],
            ],
            'model' => $bundle['model'],
            'summary' => $bundle['summary'],
        ]);
    }

    public function aiRefine(Request $request, AiHtmlQuiz $aiHtmlQuiz): JsonResponse
    {
        $data = $request->validate([
            'refine_prompt' => ['required', 'string', 'max:4000'],
            'title' => ['nullable', 'string', 'max:255'],
            'html' => ['nullable', 'string'],
            'css' => ['nullable', 'string'],
            'js' => ['nullable', 'string'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
        ]);

        $html = (string) ($data['html'] ?? $aiHtmlQuiz->bundle_html ?? '');
        $css = (string) ($data['css'] ?? $aiHtmlQuiz->bundle_css ?? '');
        $js = (string) ($data['js'] ?? $aiHtmlQuiz->bundle_js ?? '');
        $title = trim((string) ($data['title'] ?? $aiHtmlQuiz->title));
        $modelId = isset($data['ai_model_id']) ? (int) $data['ai_model_id'] : null;

        $meta = $aiHtmlQuiz->prompt_meta ?? [];
        $meta['last_refine_prompt'] = $data['refine_prompt'];
        if ($modelId) {
            $meta['ai_model_id'] = $modelId;
        }
        $aiHtmlQuiz->update(['prompt_meta' => $meta]);

        try {
            $bundle = $this->generationService->refine(
                $data['refine_prompt'],
                $html,
                $css,
                $js,
                $title,
                $modelId ?: (isset($meta['ai_model_id']) ? (int) $meta['ai_model_id'] : null)
            );
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'bundle' => [
                'title' => $bundle['title'],
                'html' => $bundle['html'],
                'css' => $bundle['css'],
                'js' => $bundle['js'],
                'summary' => $bundle['summary'],
                'answer_key' => $bundle['answer_key'],
            ],
            'model' => $bundle['model'],
            'summary' => $bundle['summary'],
        ]);
    }

    public function aiApply(Request $request, AiHtmlQuiz $aiHtmlQuiz): JsonResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'html' => ['required', 'string'],
            'css' => ['nullable', 'string'],
            'js' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
            'answer_key' => ['nullable', 'array'],
            'prompt_meta' => ['nullable', 'array'],
        ]);

        if ($this->normalizer->hasDisallowedExternalScripts($data['html'], (string) ($data['js'] ?? ''))) {
            return response()->json([
                'ok' => false,
                'message' => 'الحزمة تحتوي سكربتات خارجية غير مسموحة.',
            ], 422);
        }

        try {
            $normalized = $this->normalizer->normalize([
                'title' => $data['title'] ?? $aiHtmlQuiz->title,
                'html' => $data['html'],
                'css' => $data['css'] ?? '',
                'js' => $data['js'] ?? '',
                'summary' => $data['summary'] ?? '',
                'answer_key' => $data['answer_key'] ?? null,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $meta = array_merge($aiHtmlQuiz->prompt_meta ?? [], $data['prompt_meta'] ?? []);
        if (! empty($data['summary'])) {
            $meta['last_summary'] = $data['summary'];
        }

        $aiHtmlQuiz->update([
            'title' => $normalized['title'] ?: $aiHtmlQuiz->title,
            'bundle_html' => $normalized['html'],
            'bundle_css' => $normalized['css'],
            'bundle_js' => $normalized['js'],
            'answer_key_json' => $normalized['answer_key'],
            'prompt_meta' => $meta,
            'status' => $aiHtmlQuiz->status === AiHtmlQuiz::STATUS_DRAFT
                ? AiHtmlQuiz::STATUS_REVIEW
                : $aiHtmlQuiz->status,
            'schema_version' => AiHtmlQuiz::SCHEMA_VERSION,
        ]);

        return response()->json([
            'ok' => true,
            'quiz_id' => $aiHtmlQuiz->id,
            'status' => $aiHtmlQuiz->fresh()->status,
            'preview_url' => route('admin.ai-html-quizzes.preview', $aiHtmlQuiz),
        ]);
    }
}
