<?php

use App\InteractiveLearning\Services\AiSessionGenerationService;
use App\InteractiveLearning\Services\ContentLogicChecker;
use App\InteractiveLearning\Services\ExperienceSourceExtractionService;
use App\InteractiveLearning\Services\SchemaValidator;
use App\Models\AIModel;
use App\Services\AI\AIModelService;

uses(Tests\TestCase::class);

$makeInMemoryAiModel = function (array $capabilities): AIModel {
    $model = new AIModel([
        'name' => 'Test Model',
        'provider' => 'openai',
        'model_key' => 'gpt-4o-mini',
        'max_tokens' => 4000,
        'temperature' => 0.5,
        'is_active' => true,
        'capabilities' => $capabilities,
    ]);
    $model->exists = true;

    return $model;
};

it('rejects an image-kind source when the resolved model is not tagged with vision capability', function () use ($makeInMemoryAiModel) {
    $modelService = Mockery::mock(AIModelService::class);
    $modelService->shouldReceive('getBestModelFor')
        ->with('question_generation')
        ->andReturn($makeInMemoryAiModel(['all']));

    $service = new AiSessionGenerationService($modelService, new SchemaValidator, new ContentLogicChecker);

    $source = [
        'kind' => ExperienceSourceExtractionService::KIND_IMAGES,
        'images' => [['mime' => 'image/jpeg', 'binary' => 'fake-bytes']],
    ];

    expect(fn () => $service->generateFromSource($source, ['single_choice'], 3))
        ->toThrow(RuntimeException::class, 'تحليل الصور (رؤية)');
});
