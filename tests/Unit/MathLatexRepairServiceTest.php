<?php

use App\Services\AI\AIModelService;
use App\Services\AI\AIPromptService;
use App\Services\AI\MathLatexRepairService;

function make_math_latex_repair_service(): MathLatexRepairService
{
    return new MathLatexRepairService(new AIModelService, new AIPromptService);
}

test('getMathLatexRepairPrompt embeds every field and its current text as JSON in the prompt', function () {
    $promptService = new AIPromptService;
    $fields = [
        'title' => 'ما ناتج frac2√(4x + 1)؟',
        'option_5_content' => '2',
    ];

    $prompt = $promptService->getMathLatexRepairPrompt($fields);

    expect($prompt)
        ->toContain('title')
        ->toContain('option_5_content')
        ->toContain('frac2√(4x + 1)')
        ->toContain('\\frac{البسط}{المقام}')
        ->toContain('JSON');
});

test('applyAiResponse maps corrected json fields back onto the original field keys and re-normalizes them', function () {
    $service = make_math_latex_repair_service();

    $originalFields = [
        'title' => 'ما ناتج frac2√(4x + 1)؟',
        'content' => 'بدون رياضيات هنا.',
        'option_5_content' => '2',
    ];

    // رد ذكاء اصطناعي نموذجي: JSON صافٍ يعيد نفس المفاتيح بعد إصلاح صيغة LaTeX،
    // ويترك الحقل الذي لا يحتاج إصلاحاً كما هو.
    $aiResponse = json_encode([
        'title' => 'ما ناتج $\frac{2}{\sqrt{4x + 1}}$؟',
        'content' => 'بدون رياضيات هنا.',
        'option_5_content' => '2',
    ], JSON_UNESCAPED_UNICODE);

    $result = $service->applyAiResponse($originalFields, $aiResponse);

    expect($result['title'])
        ->toContain('\frac{2}{\sqrt{4x + 1}}')
        ->not->toContain('frac2');
    expect($result['content'])->toBe('بدون رياضيات هنا.');
    expect($result['option_5_content'])->toBe('2');
});

test('applyAiResponse extracts the json object even when the AI wraps it with extra prose or markdown fences', function () {
    $service = make_math_latex_repair_service();

    $originalFields = ['title' => 'frac12√(4x + 1)'];

    $aiResponse = "بالتأكيد، هذا هو الإصلاح:\n```json\n".json_encode([
        'title' => '$\frac{1}{2\sqrt{4x + 1}}$',
    ], JSON_UNESCAPED_UNICODE)."\n```\nأتمنى أن يفيدك ذلك.";

    $result = $service->applyAiResponse($originalFields, $aiResponse);

    expect($result['title'])->toContain('\frac{1}{2\sqrt{4x + 1}}');
});

test('applyAiResponse falls back to the original value for a field missing from the AI response', function () {
    $service = make_math_latex_repair_service();

    $originalFields = [
        'title' => 'frac2√(4x + 1)',
        'content' => 'نص إضافي غير موجود في رد الذكاء الاصطناعي',
    ];

    // رد ناقص: نسي "content" تماماً.
    $aiResponse = json_encode(['title' => '$\frac{2}{\sqrt{4x + 1}}$']);

    $result = $service->applyAiResponse($originalFields, $aiResponse);

    expect($result['content'])->toBe('نص إضافي غير موجود في رد الذكاء الاصطناعي');
});

test('applyAiResponse falls back to the original value when the AI returns an empty string for a field', function () {
    $service = make_math_latex_repair_service();

    $originalFields = ['title' => 'نص عادي بدون رياضيات'];
    $aiResponse = json_encode(['title' => '   ']);

    $result = $service->applyAiResponse($originalFields, $aiResponse);

    expect($result['title'])->toBe('نص عادي بدون رياضيات');
});

test('applyAiResponse throws when the AI response does not contain any parsable json object', function () {
    $service = make_math_latex_repair_service();

    expect(fn () => $service->applyAiResponse(['title' => 'frac2√(4x + 1)'], 'عذراً، لا أستطيع المساعدة في ذلك.'))
        ->toThrow(RuntimeException::class);
});

test('applyAiResponse throws when the AI response contains malformed json', function () {
    $service = make_math_latex_repair_service();

    expect(fn () => $service->applyAiResponse(['title' => 'frac2√(4x + 1)'], '{"title": "غير مكتمل'))
        ->toThrow(RuntimeException::class);
});
