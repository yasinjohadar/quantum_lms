<?php

namespace App\Services\AI;

use App\Exceptions\QuestionGenerationProcessException;
use App\Models\AIModel;
use App\Models\AIQuestionGeneration;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIQuestionGenerationService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService,
        private PdfTextExtractionService $pdfTextExtraction,
        private PdfPageImageService $pdfPageImage,
    ) {}

    /**
     * توليد أسئلة من درس
     */
    public function generateFromLesson(Lesson $lesson, array $options = []): AIQuestionGeneration
    {
        $content = $lesson->description ?? $lesson->title;

        // جمع محتوى إضافي من الدرس
        if ($lesson->attachments) {
            // يمكن إضافة محتوى من المرفقات
        }

        return $this->generateFromText($content, array_merge($options, [
            'lesson_id' => $lesson->id,
            'subject_id' => $lesson->unit?->section?->subject_id,
            'source_type' => 'lesson_content',
        ]));
    }

    /**
     * توليد أسئلة من نص
     */
    public function generateFromText(string $text, array $options = []): AIQuestionGeneration
    {
        $user = $options['user'] ?? auth()->user();
        $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');

        if (! $model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
        }

        // دعم question_types (array) أو question_type (string) للتوافق
        $questionType = $options['question_type'] ?? null;
        $questionTypes = $options['question_types'] ?? null;

        // إذا تم تمرير question_types، استخدمه، وإلا استخدم question_type
        if ($questionTypes && is_array($questionTypes) && count($questionTypes) > 0) {
            // استخدام question_types الجديد
            $generation = AIQuestionGeneration::create([
                'user_id' => $user->id,
                'subject_id' => $options['subject_id'] ?? null,
                'unit_id' => $options['unit_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'source_type' => $options['source_type'] ?? 'manual_text',
                'source_content' => $text,
                'question_type' => 'mixed', // للتوافق مع البيانات القديمة
                'question_types' => $questionTypes,
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => $model->id,
                'status' => 'pending',
            ]);
        } else {
            // استخدام question_type القديم
            $generation = AIQuestionGeneration::create([
                'user_id' => $user->id,
                'subject_id' => $options['subject_id'] ?? null,
                'unit_id' => $options['unit_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'source_type' => $options['source_type'] ?? 'manual_text',
                'source_content' => $text,
                'question_type' => $questionType ?? 'mixed',
                'question_types' => null,
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => $model->id,
                'status' => 'pending',
            ]);
        }

        // معالجة التوليد (يمكن أن تكون async)
        $this->processGeneration($generation);

        return $generation;
    }

    /**
     * توليد أسئلة من موضوع
     */
    public function generateFromTopic(string $topic, array $options = []): AIQuestionGeneration
    {
        return $this->generateFromText($topic, array_merge($options, [
            'source_type' => 'topic',
        ]));
    }

    /**
     * توليد أسئلة من ملف مرفوع (صورة أو PDF).
     *
     * @param  array<string, mixed>  $options
     */
    public function generateFromUploadedFile(UploadedFile $file, array $options = []): AIQuestionGeneration
    {
        $mime = strtolower((string) $file->getMimeType());
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return $this->generateFromUploadedImage($file, $options);
        }

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return $this->generateFromUploadedPdf($file, $options);
        }

        throw new \Exception('نوع الملف غير مدعوم. يُقبل صورة (JPEG, PNG, WebP, GIF) أو ملف PDF فقط.');
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function generateFromUploadedPdf(UploadedFile $file, array $options = []): AIQuestionGeneration
    {
        $user = $options['user'] ?? auth()->user();
        $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');

        if (! $model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
        }

        $path = $file->store('ai_question_sources', 'local');

        try {
            $questionTypes = $options['question_types'] ?? null;
            $instructions = isset($options['instructions']) ? (string) $options['instructions'] : '';

            $base = [
                'user_id' => $user->id,
                'subject_id' => $options['subject_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'unit_id' => $options['unit_id'] ?? null,
                'source_type' => 'pdf',
                'source_content' => $instructions,
                'source_image_path' => $path,
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => $model->id,
                'status' => 'pending',
            ];

            if ($questionTypes && is_array($questionTypes) && count($questionTypes) > 0) {
                $generation = AIQuestionGeneration::create(array_merge($base, [
                    'question_type' => 'mixed',
                    'question_types' => $questionTypes,
                ]));
            } else {
                $generation = AIQuestionGeneration::create(array_merge($base, [
                    'question_type' => $options['question_type'] ?? 'mixed',
                    'question_types' => null,
                ]));
            }
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        $this->processPdfGeneration($generation);

        return $generation->fresh();
    }

    /**
     * توليد أسئلة من صورة مرفوعة (تحليل بصري عبر موديل رؤية).
     *
     * @param  array<string, mixed>  $options
     */
    public function generateFromUploadedImage(UploadedFile $file, array $options = []): AIQuestionGeneration
    {
        $user = $options['user'] ?? auth()->user();
        $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');

        if (! $model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
        }

        if (! VisionQuestionGenerationSupport::providerSupportsVisionConversion($model->provider)) {
            throw new \Exception('مزود النموذج الحالي لا يدعم توليد الأسئلة من الصورة. استخدم OpenAI أو OpenRouter أو Anthropic أو Google أو Z.ai مع موديل يدعم الرؤية.');
        }

        $path = $file->store('ai_question_sources', 'local');

        try {
            $questionTypes = $options['question_types'] ?? null;
            $instructions = isset($options['instructions']) ? (string) $options['instructions'] : '';

            if ($questionTypes && is_array($questionTypes) && count($questionTypes) > 0) {
                $generation = AIQuestionGeneration::create([
                    'user_id' => $user->id,
                    'subject_id' => $options['subject_id'] ?? null,
                    'lesson_id' => $options['lesson_id'] ?? null,
                    'unit_id' => $options['unit_id'] ?? null,
                    'source_type' => 'image',
                    'source_content' => $instructions,
                    'source_image_path' => $path,
                    'question_type' => 'mixed',
                    'question_types' => $questionTypes,
                    'number_of_questions' => $options['number_of_questions'] ?? 5,
                    'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                    'ai_model_id' => $model->id,
                    'status' => 'pending',
                ]);
            } else {
                $generation = AIQuestionGeneration::create([
                    'user_id' => $user->id,
                    'subject_id' => $options['subject_id'] ?? null,
                    'lesson_id' => $options['lesson_id'] ?? null,
                    'unit_id' => $options['unit_id'] ?? null,
                    'source_type' => 'image',
                    'source_content' => $instructions,
                    'source_image_path' => $path,
                    'question_type' => $options['question_type'] ?? 'mixed',
                    'question_types' => null,
                    'number_of_questions' => $options['number_of_questions'] ?? 5,
                    'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                    'ai_model_id' => $model->id,
                    'status' => 'pending',
                ]);
            }
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        $this->processVisionGeneration($generation);

        return $generation->fresh();
    }

    /**
     * معالجة التوليد
     */
    public function processGeneration(AIQuestionGeneration $generation): array
    {
        if ($generation->source_type === 'image') {
            return $this->processVisionGeneration($generation);
        }

        if ($generation->source_type === 'pdf') {
            return $this->processPdfGeneration($generation);
        }

        return $this->processTextGeneration($generation, (string) $generation->source_content);
    }

    /**
     * معالجة PDF: استخراج نص أو تحليل بصري للصفحات الممسوحة.
     */
    public function processPdfGeneration(AIQuestionGeneration $generation): array
    {
        set_time_limit(180);

        $generation->update(['status' => 'processing']);

        try {
            $path = $generation->source_image_path;
            if (! $path || ! Storage::disk('local')->exists($path)) {
                throw new \Exception('ملف PDF غير موجود أو تم حذفه.');
            }

            $absolutePath = Storage::disk('local')->path($path);
            $extracted = $this->pdfTextExtraction->extractFromPath($absolutePath);

            if ($this->pdfTextExtraction->isTextSufficient($extracted['text'], $extracted['pageCount'])) {
                $truncated = $this->pdfTextExtraction->truncateForPrompt($extracted['text']);
                $instructions = trim((string) $generation->source_content);
                $combined = $instructions !== ''
                    ? $instructions."\n\n--- محتوى الملف ---\n\n".$truncated
                    : $truncated;

                return $this->processTextGeneration($generation, $combined);
            }

            if (! VisionQuestionGenerationSupport::providerSupportsVisionConversion($generation->model?->provider ?? '')) {
                throw new \Exception(
                    'ملف PDF يبدو ممسوحاً ضوئياً (نص غير كافٍ). استخدم موديلاً يدعم الرؤية (Vision)، '
                    .'أو ثبّت Imagick وGhostscript على الخادم، أو ارفع صور الصفحات بدلاً من PDF.'
                );
            }

            $maxPages = (int) config('ai.question_generation_pdf.max_pages_vision', 10);
            $images = $this->pdfPageImage->renderPages($absolutePath, $maxPages);

            $adminNotes = (string) $generation->source_content;
            if ($extracted['pageCount'] > count($images)) {
                $adminNotes .= "\n\n(تم تحليل أول ".count($images).' صفحات من '.$extracted['pageCount'].')';
            }
            $adminNotes .= "\n\nملف PDF ممسوح ضوئياً — حلّل محتوى الصفحات المرفقة وأنشئ الأسئلة منها.";

            return $this->processVisionGenerationFromImages($generation, $images, trim($adminNotes));
        } catch (\Throwable $e) {
            Log::error('Error processing PDF question generation: '.$e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $this->failGeneration($generation, $e);
        }
    }

    /**
     * توليد أسئلة من نص (يدوي، درس، أو PDF نصي).
     */
    public function processTextGeneration(AIQuestionGeneration $generation, string $content): array
    {
        set_time_limit(180);

        $generation->update(['status' => 'processing']);

        try {
            $model = $generation->model;
            if (! $model) {
                throw new \Exception('الموديل غير موجود');
            }

            $selectedTypes = $generation->getSelectedQuestionTypes();
            $questionTypeForPrompt = ! empty($selectedTypes) && count($selectedTypes) > 0
                ? (count($selectedTypes) === 1 ? $selectedTypes[0] : 'mixed')
                : $generation->question_type;

            $prompt = $this->promptService->getQuestionGenerationPrompt(
                $content,
                [
                    'question_type' => $questionTypeForPrompt,
                    'question_types' => ! empty($selectedTypes) ? $selectedTypes : null,
                    'number_of_questions' => $generation->number_of_questions,
                    'difficulty_level' => $generation->difficulty_level,
                ]
            );

            $requiredTokens = max(4000, $generation->number_of_questions * 800);
            $maxTokens = min($requiredTokens, $model->max_tokens ?: 16000);

            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

            if (! $response || empty($response)) {
                $lastError = $provider->getLastError() ?? 'فشل في توليد الأسئلة - لم يتم الحصول على رد من API';

                throw new \Exception($lastError);
            }

            return $this->finalizeGenerationFromAiResponse($generation, $model, $provider, $prompt, $response, null);
        } catch (\Throwable $e) {
            Log::error('Error processing question generation: '.$e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $this->failGeneration($generation, $e);
        }
    }

    /**
     * توليد من صورة عبر طلب رؤية (متعدد الوسائط).
     */
    public function processVisionGeneration(AIQuestionGeneration $generation): array
    {
        set_time_limit(180);

        $generation->update(['status' => 'processing']);

        try {
            $model = $generation->model;
            if (! $model) {
                throw new \Exception('الموديل غير موجود');
            }

            if (! VisionQuestionGenerationSupport::providerSupportsVisionConversion($model->provider)) {
                throw new \Exception('مزود النموذج لا يدعم توليد الأسئلة من الصورة. استخدم OpenAI أو OpenRouter أو Anthropic أو Google أو Z.ai مع موديل رؤية.');
            }

            $path = $generation->source_image_path;
            if (! $path || ! Storage::disk('local')->exists($path)) {
                throw new \Exception('ملف الصورة غير موجود أو تم حذفه.');
            }

            $binary = Storage::disk('local')->get($path);
            $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

            return $this->processVisionGenerationFromImages($generation, [
                ['mime' => $mime, 'binary' => $binary],
            ], $generation->source_content);
        } catch (\Throwable $e) {
            Log::error('Error processing vision question generation: '.$e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $this->failGeneration($generation, $e);
        }
    }

    /**
     * @param  array<int, array{mime: string, binary: string}>  $images
     */
    public function processVisionGenerationFromImages(
        AIQuestionGeneration $generation,
        array $images,
        ?string $adminNotes = null
    ): array {
        set_time_limit(180);

        $generation->update(['status' => 'processing']);

        try {
            $model = $generation->model;
            if (! $model) {
                throw new \Exception('الموديل غير موجود');
            }

            if (! VisionQuestionGenerationSupport::providerSupportsVisionConversion($model->provider)) {
                throw new \Exception('مزود النموذج لا يدعم توليد الأسئلة من الصورة. استخدم OpenAI أو OpenRouter أو Anthropic أو Google أو Z.ai مع موديل رؤية.');
            }

            if ($images === []) {
                throw new \Exception('لا توجد صور للتحليل.');
            }

            $selectedTypes = $generation->getSelectedQuestionTypes();
            $questionTypeForPrompt = ! empty($selectedTypes) && count($selectedTypes) > 0
                ? (count($selectedTypes) === 1 ? $selectedTypes[0] : 'mixed')
                : $generation->question_type;

            $textPrompt = $this->promptService->getQuestionGenerationVisionTextPrompt(
                $adminNotes ?? $generation->source_content,
                [
                    'question_type' => $questionTypeForPrompt,
                    'question_types' => ! empty($selectedTypes) ? $selectedTypes : null,
                    'number_of_questions' => $generation->number_of_questions,
                    'difficulty_level' => $generation->difficulty_level,
                ]
            );

            $messages = VisionQuestionGenerationSupport::buildOpenAiStyleMessagesWithImages($textPrompt, $images);

            $requiredTokens = max(8000, $generation->number_of_questions * 1000);
            $maxTokens = min($requiredTokens, $model->max_tokens ?: 16000);

            $provider = AIProviderFactory::create($model);
            $chatResult = $provider->chat($messages, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.25,
            ]);

            if (! ($chatResult['success'] ?? false)) {
                throw new \Exception($chatResult['error'] ?? 'فشل طلب تحليل الصورة');
            }

            $response = (string) ($chatResult['content'] ?? '');
            if ($response === '') {
                throw new \Exception($provider->getLastError() ?? 'لم يُرجع النموذج أي نص بعد تحليل الصورة.');
            }

            $promptStored = mb_substr($textPrompt, 0, 60000);
            $tokensOverride = (int) ($chatResult['tokens_used'] ?? 0);

            return $this->finalizeGenerationFromAiResponse(
                $generation,
                $model,
                $provider,
                $promptStored,
                $response,
                $tokensOverride > 0 ? $tokensOverride : null
            );
        } catch (\Throwable $e) {
            Log::error('Error processing vision question generation: '.$e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $this->failGeneration($generation, $e);
        }
    }

    /**
     * ترجمة رسائل أخطاء مزودي AI الشائعة إلى العربية.
     */
    public static function humanizeApiErrorMessage(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'high demand')
            || str_contains($lower, 'rate limit')
            || str_contains($lower, 'too many requests')
            || str_contains($lower, '429')) {
            return 'الموديل مشغول حالياً بسبب ضغط مرتفع على الخدمة. جرّب بعد قليل أو اختر موديلاً آخر.';
        }

        if (str_contains($lower, 'timeout') || str_contains($lower, 'timed out')) {
            return 'انتهت مهلة الاتصال بمزود الذكاء الاصطناعي. جرّب تقليل عدد الأسئلة أو إعادة المحاولة.';
        }

        if (str_contains($lower, 'insufficient') && str_contains($lower, 'quota')) {
            return 'رصيد أو حصة مزود الذكاء الاصطناعي غير كافٍ. تحقق من إعدادات الموديل أو استخدم موديلاً آخر.';
        }

        if (str_contains($lower, 'invalid api key') || str_contains($lower, 'incorrect api key')) {
            return 'مفتاح API غير صالح. تحقق من إعدادات الموديل في لوحة التحكم.';
        }

        return $message;
    }

    private function failGeneration(AIQuestionGeneration $generation, \Throwable $e): never
    {
        if ($e instanceof QuestionGenerationProcessException) {
            throw $e;
        }

        $message = self::humanizeApiErrorMessage($e->getMessage());

        $generation->update([
            'status' => 'failed',
            'error_message' => $message,
        ]);

        throw new QuestionGenerationProcessException($generation->fresh(), $message, $e);
    }

    private function finalizeGenerationFromAiResponse(
        AIQuestionGeneration $generation,
        AIModel $model,
        AIProviderService $provider,
        string $promptForStorage,
        string $response,
        ?int $tokensUsedOverride
    ): array {
        Log::info('Full AI response received', [
            'generation_id' => $generation->id,
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 1000),
            'response_full' => $response,
        ]);

        $questions = $this->parseGeneratedQuestions($response);
        $validatedQuestions = $this->validateGeneratedQuestions($questions);

        $requiredCount = $generation->number_of_questions;
        $actualCount = count($validatedQuestions);
        $warningMessage = null;

        if ($actualCount < $requiredCount) {
            $missingCount = $requiredCount - $actualCount;
            $warningMessage = "تم توليد {$actualCount} سؤال فقط من {$requiredCount} المطلوبة. ({$missingCount} سؤال مفقود)";

            Log::warning('Question generation incomplete', [
                'generation_id' => $generation->id,
                'required' => $requiredCount,
                'actual' => $actualCount,
                'missing' => $missingCount,
                'response_length' => strlen($response),
            ]);
        }

        $tokensUsed = ($tokensUsedOverride !== null && $tokensUsedOverride > 0)
            ? $tokensUsedOverride
            : $provider->estimateTokens($promptForStorage.$response);

        $generation->update([
            'status' => 'completed',
            'generated_questions' => $validatedQuestions,
            'prompt' => $promptForStorage,
            'ai_response_preview' => mb_substr($response, 0, 3000),
            'tokens_used' => $tokensUsed,
            'cost' => $model->getCost($tokensUsed),
            'error_message' => $warningMessage,
        ]);

        return $validatedQuestions;
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function saveGeneratedQuestions(AIQuestionGeneration $generation, ?array $selectedIndices = null): Collection
    {
        if ($generation->status !== 'completed') {
            throw new \Exception('التوليد لم يكتمل بعد');
        }

        $questions = $generation->generated_questions ?? [];
        $savedQuestions = collect();

        // إذا تم تحديد indices، احفظ فقط المحددة
        if ($selectedIndices !== null && ! empty($selectedIndices)) {
            $filteredQuestions = [];
            foreach ($questions as $index => $questionData) {
                if (in_array($index, $selectedIndices)) {
                    $filteredQuestions[] = $questionData;
                }
            }
            $questions = $filteredQuestions;
        }

        DB::beginTransaction();
        try {
            $unitId = $generation->unit_id ?? $generation->lesson?->unit_id;

            foreach ($questions as $questionData) {
                $questionType = $questionData['type'] ?? 'single_choice';

                $stem = QuestionMarkupFormatter::normalizeForStorage($questionData['question'] ?? '');

                // إنشاء السؤال
                $question = Question::create([
                    'type' => $questionType,
                    'title' => $stem,
                    'content' => $stem,
                    'explanation' => QuestionMarkupFormatter::normalizeForStorage($questionData['explanation'] ?? ''),
                    'difficulty' => $questionData['difficulty'] ?? 'medium',
                    'default_points' => $questionData['points'] ?? 10,
                    'is_active' => true,
                    'created_by' => $generation->user_id,
                    'subject_id' => $generation->subject_id,
                ]);

                if ($unitId) {
                    $question->units()->sync([$unitId]);
                }

                // معالجة الخيارات حسب نوع السؤال
                $this->saveQuestionOptions($question, $questionType, $questionData);

                $savedQuestions->push($question);
            }

            $generation->update(['questions_saved_at' => now()]);

            DB::commit();

            return $savedQuestions;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving generated questions: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * تطبيع قائمة الأسئلة بعد تحليل JSON (مفاتيح غلاف، بدائل لحقل السؤال).
     *
     * @param  array<mixed>  $decoded
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeParsedQuestionList(array $decoded): array
    {
        foreach (['questions', 'data', 'items', 'generated_questions', 'results'] as $wrapperKey) {
            if (isset($decoded[$wrapperKey]) && is_array($decoded[$wrapperKey])) {
                $decoded = $decoded[$wrapperKey];
                break;
            }
        }

        if (! array_is_list($decoded)) {
            $list = [];
            foreach ($decoded as $key => $value) {
                if (is_array($value) && (is_int($key) || (is_string($key) && ctype_digit($key)))) {
                    $list[] = $value;
                }
            }
            if ($list !== []) {
                $decoded = $list;
            }
        }

        $normalized = [];
        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $item = self::normalizeQuestionItemFields($item);
            $questionText = trim((string) ($item['question'] ?? ''));

            if ($questionText !== '') {
                $normalized[] = $item;
            }
        }

        return array_values($normalized);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function normalizeQuestionItemFields(array $item): array
    {
        if (isset($item['question']) && is_array($item['question'])) {
            $item['question'] = implode(' ', array_filter(array_map(
                static fn ($part) => is_scalar($part) ? trim((string) $part) : '',
                $item['question']
            )));
        }

        if (empty($item['question']) || ! is_string($item['question'])) {
            foreach (['title', 'text', 'stem', 'question_text', 'content'] as $altKey) {
                if (! empty($item[$altKey]) && is_string($item[$altKey])) {
                    $item['question'] = $item[$altKey];
                    break;
                }
            }
        }

        return $item;
    }

    /**
     * @param  array<mixed>  $decoded
     * @return array<int, array<string, mixed>>
     */
    private function normalizeDecodedQuestions(array $decoded): array
    {
        $normalized = self::normalizeParsedQuestionList($decoded);

        Log::info('Normalized parsed question list', ['count' => count($normalized)]);

        return $normalized;
    }

    /**
     * حفظ خيارات السؤال حسب نوعه
     */
    protected function saveQuestionOptions(Question $question, string $type, array $questionData): void
    {
        switch ($type) {
            case 'single_choice':
            case 'multiple_choice':
                $this->saveChoiceOptions($question, $type, $questionData);
                break;

            case 'true_false':
                $this->saveTrueFalseOptions($question, $questionData);
                break;

            case 'matching':
                $this->saveMatchingOptions($question, $questionData);
                break;

            case 'ordering':
                $this->saveOrderingOptions($question, $questionData);
                break;

            case 'numerical':
                $this->saveNumericalAnswer($question, $questionData);
                break;

            case 'fill_blanks':
                $this->saveFillBlanksAnswer($question, $questionData);
                break;

            case 'drag_drop':
                $this->saveDragDropOptions($question, $questionData);
                break;

            case 'essay':
            case 'short_answer':
                // لا تحتاج خيارات
                break;

            default:
                // Fallback: محاولة حفظ الخيارات بشكل عام
                if (isset($questionData['options']) && is_array($questionData['options']) && count($questionData['options']) >= 2) {
                    $this->saveChoiceOptions($question, 'single_choice', $questionData);
                }
                break;
        }
    }

    /**
     * حفظ خيارات اختيار واحد/متعدد
     */
    protected function saveChoiceOptions(Question $question, string $type, array $questionData): void
    {
        $options = $questionData['options'] ?? [];
        $correctAnswer = $questionData['correct_answer'] ?? '';

        // التحقق من وجود خيارات كافية
        if (count($options) < 2) {
            Log::warning('Insufficient options for choice question', [
                'question_id' => $question->id,
                'type' => $type,
                'options_count' => count($options),
            ]);

            return;
        }

        // لـ multiple_choice، correct_answer يجب أن يكون array
        if ($type === 'multiple_choice' && ! is_array($correctAnswer)) {
            $correctAnswer = [$correctAnswer];
        }

        foreach ($options as $index => $optionText) {
            if (empty(trim($optionText))) {
                continue; // تخطي الخيارات الفارغة
            }

            $isCorrect = false;
            if (is_array($correctAnswer)) {
                // للبحث في array (دعم multiple_choice)
                // محاولة 1: البحث في النصوص مباشرة
                $isCorrect = in_array(trim($optionText), array_map('trim', $correctAnswer), true);
                // محاولة 2: البحث في الـ indices (0, 1, 2, ...)
                if (! $isCorrect) {
                    $isCorrect = in_array($index, $correctAnswer, true);
                }
                // محاولة 3: البحث في array indexed
                if (! $isCorrect && isset($correctAnswer[$index])) {
                    $isCorrect = trim($optionText) === trim($correctAnswer[$index]);
                }
            } else {
                // للـ single_choice
                // محاولة 1: مطابقة النص
                $isCorrect = trim($optionText) === trim($correctAnswer);
                // محاولة 2: دعم index-based answer (0, 1, 2, ...)
                if (! $isCorrect && is_numeric($correctAnswer)) {
                    $isCorrect = ($index == (int) $correctAnswer);
                }
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => QuestionMarkupFormatter::normalizeForStorage(trim($optionText)),
                'is_correct' => $isCorrect,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * حفظ خيارات صح/خطأ
     */
    protected function saveTrueFalseOptions(Question $question, array $questionData): void
    {
        $correctAnswer = $questionData['correct_answer'] ?? '';
        $correctAnswerStr = is_array($correctAnswer) ? ($correctAnswer[0] ?? '') : $correctAnswer;
        $correctAnswerStr = strtolower(trim($correctAnswerStr));

        // إنشاء خيارين فقط
        $trueOption = QuestionOption::create([
            'question_id' => $question->id,
            'content' => 'صح',
            'is_correct' => in_array($correctAnswerStr, ['true', 'صح', '1', 'yes', 'نعم', 'صحيح'], true),
            'order' => 1,
        ]);

        $falseOption = QuestionOption::create([
            'question_id' => $question->id,
            'content' => 'خطأ',
            'is_correct' => in_array($correctAnswerStr, ['false', 'خطأ', '0', 'no', 'لا', 'خطأ'], true),
            'order' => 2,
        ]);
    }

    /**
     * حفظ خيارات المطابقة
     */
    protected function saveMatchingOptions(Question $question, array $questionData): void
    {
        $options = $questionData['options'] ?? [];
        $matchTargets = $questionData['match_targets'] ?? [];
        $matches = $questionData['matches'] ?? [];

        if (count($options) < 2) {
            Log::warning('Insufficient options for matching question', [
                'question_id' => $question->id,
                'options_count' => count($options),
            ]);

            return;
        }

        // محاولة استخراج matches من structure مختلف
        if (empty($matchTargets) && ! empty($matches) && is_array($matches)) {
            // Structure 1: [{'item': 'A', 'target': '1'}, ...]
            if (isset($matches[0]) && is_array($matches[0]) && isset($matches[0]['item'])) {
                foreach ($matches as $match) {
                    if (isset($match['item']) && isset($match['target'])) {
                        $itemIndex = array_search($match['item'], $options);
                        if ($itemIndex !== false) {
                            $matchTargets[$itemIndex] = $match['target'];
                        }
                    }
                }
            }
            // Structure 2: {'A': '1', 'B': '2', ...}
            elseif (isset($matches[0]) && ! is_array($matches[0])) {
                foreach ($matches as $key => $value) {
                    $itemIndex = array_search($key, $options);
                    if ($itemIndex !== false) {
                        $matchTargets[$itemIndex] = $value;
                    }
                }
            }
        }

        foreach ($options as $index => $optionText) {
            if (empty(trim($optionText))) {
                continue;
            }

            $matchTarget = $matchTargets[$index] ?? '';

            // إذا لم يكن match_target موجوداً، محاولة البحث في matches مرة أخرى
            if (empty($matchTarget) && ! empty($matches)) {
                foreach ($matches as $match) {
                    if (is_array($match)) {
                        if ((isset($match['item']) && trim($match['item']) === trim($optionText)) ||
                            (isset($match['left']) && trim($match['left']) === trim($optionText))) {
                            $matchTarget = $match['target'] ?? $match['right'] ?? '';
                            break;
                        }
                    }
                }
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => QuestionMarkupFormatter::normalizeForStorage(trim($optionText)),
                'match_target' => QuestionMarkupFormatter::normalizeForStorage(trim($matchTarget)),
                'is_correct' => true, // جميع خيارات المطابقة صحيحة إذا تمت المطابقة بشكل صحيح
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * حفظ خيارات الترتيب
     */
    protected function saveOrderingOptions(Question $question, array $questionData): void
    {
        $options = $questionData['options'] ?? [];

        if (count($options) < 2) {
            Log::warning('Insufficient options for ordering question', [
                'question_id' => $question->id,
                'options_count' => count($options),
            ]);

            return;
        }

        $correctOrder = $questionData['correct_order'] ?? [];

        foreach ($options as $index => $optionText) {
            if (empty(trim($optionText))) {
                continue;
            }

            // تحديد الترتيب الصحيح
            $order = $index + 1;
            if (is_array($correctOrder)) {
                if (isset($correctOrder[$index])) {
                    $order = (int) $correctOrder[$index];
                } elseif (isset($correctOrder[$optionText])) {
                    $order = (int) $correctOrder[$optionText];
                }
            } elseif (is_numeric($correctOrder) && $index === 0) {
                // إذا كان correct_order رقم واحد، استخدمه للخيار الأول
                $order = (int) $correctOrder;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => QuestionMarkupFormatter::normalizeForStorage(trim($optionText)),
                'correct_order' => $order,
                'is_correct' => true,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * حفظ الإجابة الرقمية
     */
    protected function saveNumericalAnswer(Question $question, array $questionData): void
    {
        $correctAnswer = $questionData['correct_answer'] ?? '';

        if (empty($correctAnswer) && ! is_numeric($correctAnswer)) {
            Log::warning('Missing correct answer for numerical question', [
                'question_id' => $question->id,
            ]);

            return;
        }

        // حفظ tolerance إذا كان موجوداً
        if (isset($questionData['tolerance'])) {
            $question->update(['tolerance' => (float) $questionData['tolerance']]);
        }

        // إنشاء خيار واحد يحتوي على الإجابة الصحيحة
        QuestionOption::create([
            'question_id' => $question->id,
            'content' => QuestionMarkupFormatter::normalizeForStorage((string) $correctAnswer),
            'is_correct' => true,
            'order' => 1,
        ]);
    }

    /**
     * حفظ إجابات ملء الفراغات
     */
    protected function saveFillBlanksAnswer(Question $question, array $questionData): void
    {
        $blankAnswers = $questionData['blank_answers'] ?? $questionData['correct_answers'] ?? [];

        if (empty($blankAnswers)) {
            Log::warning('Missing blank answers for fill_blanks question', [
                'question_id' => $question->id,
            ]);

            return;
        }

        // حفظ blank_answers كـ array في Question
        if (is_array($blankAnswers)) {
            $normalized = array_map(
                fn ($answer) => is_string($answer)
                    ? QuestionMarkupFormatter::normalizeForStorage($answer)
                    : $answer,
                $blankAnswers
            );
            $question->update(['blank_answers' => $normalized]);
        } elseif (is_string($blankAnswers)) {
            $normalized = array_map(
                fn ($answer) => QuestionMarkupFormatter::normalizeForStorage(trim($answer)),
                explode(',', $blankAnswers)
            );
            $question->update(['blank_answers' => $normalized]);
        }

        // حفظ case_sensitive إذا كان موجوداً
        if (isset($questionData['case_sensitive'])) {
            $question->update(['case_sensitive' => (bool) $questionData['case_sensitive']]);
        }
    }

    /**
     * حفظ خيارات السحب والإفلات
     */
    protected function saveDragDropOptions(Question $question, array $questionData): void
    {
        $options = $questionData['options'] ?? [];
        $correctAnswer = $questionData['correct_answer'] ?? '';

        if (count($options) < 2) {
            Log::warning('Insufficient options for drag_drop question', [
                'question_id' => $question->id,
                'options_count' => count($options),
            ]);

            return;
        }

        foreach ($options as $index => $optionText) {
            if (empty(trim($optionText))) {
                continue;
            }

            $isCorrect = false;
            if (is_array($correctAnswer)) {
                $isCorrect = in_array(trim($optionText), array_map('trim', $correctAnswer), true) ||
                            in_array($index, $correctAnswer, true);
            } else {
                $isCorrect = trim($optionText) === trim($correctAnswer);
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => QuestionMarkupFormatter::normalizeForStorage(trim($optionText)),
                'is_correct' => $isCorrect,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * التحقق من صحة الأسئلة المولدة
     */
    public function validateGeneratedQuestions(array $questions): array
    {
        $questions = self::normalizeParsedQuestionList($questions);
        $validated = [];

        foreach ($questions as $question) {
            $question = self::normalizeQuestionItemFields($question);

            if (! isset($question['question']) || trim((string) $question['question']) === '') {
                continue;
            }

            $type = $question['type'] ?? 'single_choice';
            $options = $question['options'] ?? [];

            // التحقق من صحة البيانات حسب نوع السؤال
            if (! $this->validateQuestionData($type, $question)) {
                Log::warning('Invalid question data, skipping', [
                    'type' => $type,
                    'question_preview' => substr($question['question'] ?? '', 0, 50),
                ]);

                continue;
            }

            $validated[] = [
                'type' => $type,
                'question' => $question['question'],
                'options' => $options,
                'correct_answer' => $question['correct_answer'] ?? '',
                'match_targets' => $question['match_targets'] ?? $question['matches'] ?? [],
                'correct_order' => $question['correct_order'] ?? [],
                'blank_answers' => $question['blank_answers'] ?? $question['correct_answers'] ?? [],
                'tolerance' => $question['tolerance'] ?? null,
                'case_sensitive' => $question['case_sensitive'] ?? false,
                'explanation' => $question['explanation'] ?? '',
                'difficulty' => $question['difficulty'] ?? 'medium',
                'points' => $question['points'] ?? 10,
            ];
        }

        return $validated;
    }

    /**
     * التحقق من صحة بيانات السؤال حسب نوعه
     */
    protected function validateQuestionData(string $type, array $questionData): bool
    {
        $options = $questionData['options'] ?? [];

        switch ($type) {
            case 'single_choice':
            case 'multiple_choice':
            case 'drag_drop':
                // يجب أن يكون هناك خياران على الأقل
                if (count($options) < 2) {
                    return false;
                }
                // يجب أن تكون هناك إجابة صحيحة
                if (empty($questionData['correct_answer'])) {
                    return false;
                }

                return true;

            case 'true_false':
                // لا يحتاج options، سيتم إنشاؤها تلقائياً
                return ! empty($questionData['correct_answer']);

            case 'matching':
                // يجب أن يكون هناك خياران على الأقل
                if (count($options) < 2) {
                    return false;
                }

                // match_targets اختياري - يمكن إنشاؤه لاحقاً
                return true;

            case 'ordering':
                // يجب أن يكون هناك خياران على الأقل
                if (count($options) < 2) {
                    return false;
                }

                return true;

            case 'numerical':
                // يجب أن تكون هناك إجابة رقمية
                $correctAnswer = $questionData['correct_answer'] ?? '';

                return ! empty($correctAnswer) && (is_numeric($correctAnswer) || is_numeric(str_replace(',', '.', $correctAnswer)));

            case 'fill_blanks':
                // يجب أن تكون هناك blank_answers
                $blankAnswers = $questionData['blank_answers'] ?? $questionData['correct_answers'] ?? [];

                return ! empty($blankAnswers);

            case 'essay':
            case 'short_answer':
                // لا يحتاج خيارات
                return true;

            default:
                return true; // السماح بالأنواع الأخرى
        }
    }

    /**
     * تحليل JSON للأسئلة المولدة
     */
    private function parseGeneratedQuestions(string $response): array
    {
        Log::info('Parsing AI response for questions', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 500),
        ]);

        // محاولة إصلاح encoding issues
        if (! mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
            Log::info('Fixed encoding issues in response');
        }

        // تنظيف الرد من markdown code blocks
        $cleanedResponse = $response;

        // إزالة ```json و ``` من البداية والنهاية
        $cleanedResponse = preg_replace('/^```(?:json)?\s*/i', '', trim($cleanedResponse));
        $cleanedResponse = preg_replace('/\s*```$/i', '', $cleanedResponse);

        // إزالة أي BOM أو characters غريبة
        $cleanedResponse = preg_replace('/^\xEF\xBB\xBF/', '', $cleanedResponse);
        $cleanedResponse = trim($cleanedResponse);

        // محاولة 1: تحليل JSON مباشرة
        $decoded = json_decode($cleanedResponse, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $normalized = $this->normalizeDecodedQuestions($decoded);
            if ($normalized !== []) {
                Log::info('JSON parsed successfully (direct)', ['count' => count($normalized)]);

                return $normalized;
            }
        }

        // محاولة 2: استخراج JSON array من النص
        if (preg_match('/\[\s*\{.*?\}\s*\]/s', $cleanedResponse, $matches)) {
            $jsonString = $matches[0];
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $normalized = $this->normalizeDecodedQuestions($decoded);
                if ($normalized !== []) {
                    Log::info('JSON parsed successfully (regex array)', ['count' => count($normalized)]);

                    return $normalized;
                }
            }
        }

        // محاولة 3: استخراج JSON object يحتوي questions
        if (preg_match('/\{\s*"questions"\s*:\s*\[.*?\]\s*\}/s', $cleanedResponse, $matches)) {
            $decoded = json_decode($matches[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $normalized = $this->normalizeDecodedQuestions($decoded);
                if ($normalized !== []) {
                    Log::info('JSON parsed successfully (questions wrapper)', ['count' => count($normalized)]);

                    return $normalized;
                }
            }
        }

        // محاولة 4: البحث عن [ و ] يدوياً
        $jsonStart = strpos($cleanedResponse, '[');
        $jsonEnd = strrpos($cleanedResponse, ']');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($cleanedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $normalized = $this->normalizeDecodedQuestions($decoded);
                if ($normalized !== []) {
                    Log::info('JSON parsed successfully (manual extraction)', ['count' => count($normalized)]);

                    return $normalized;
                }
            }
        }

        // محاولة 5: البحث عن JSON object واحد
        if (preg_match('/\{[^{}]*"question"[^{}]*\}/s', $cleanedResponse, $matches)) {
            $decoded = json_decode('['.$matches[0].']', true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $normalized = $this->normalizeDecodedQuestions($decoded);
                if ($normalized !== []) {
                    Log::info('JSON parsed successfully (single object)', ['count' => count($normalized)]);

                    return $normalized;
                }
            }
        }

        // محاولة 5: تحليل نص غير JSON (fallback)
        $questions = $this->parseTextBasedQuestions($cleanedResponse);
        if (! empty($questions)) {
            Log::info('Questions parsed from text format', ['count' => count($questions)]);

            return $questions;
        }

        Log::warning('Failed to parse questions from response', [
            'json_error' => json_last_error_msg(),
            'response' => substr($cleanedResponse, 0, 1000),
        ]);

        return [];
    }

    /**
     * محاولة تحليل الأسئلة من نص غير JSON
     */
    private function parseTextBasedQuestions(string $text): array
    {
        $questions = [];

        // البحث عن أنماط مثل "1. سؤال" أو "السؤال 1:"
        $patterns = [
            '/(?:سؤال|السؤال|Question)\s*(\d+)[:\.\)]\s*(.+?)(?=(?:سؤال|السؤال|Question)\s*\d+|$)/is',
            '/(\d+)[:\.\)]\s*(.+?)(?=\d+[:\.\)]|$)/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $questionText = trim($match[2] ?? $match[1] ?? '');
                    if (strlen($questionText) > 10) {
                        $questions[] = [
                            'type' => 'short_answer',
                            'question' => $questionText,
                            'options' => [],
                            'correct_answer' => '',
                            'explanation' => '',
                            'difficulty' => 'medium',
                        ];
                    }
                }

                if (! empty($questions)) {
                    break;
                }
            }
        }

        return $questions;
    }
}
