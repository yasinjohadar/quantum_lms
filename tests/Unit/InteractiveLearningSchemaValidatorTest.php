<?php

use App\InteractiveLearning\Services\SchemaValidator;

test('schema validator accepts empty schema with one valid true_false question', function () {
    $validator = new SchemaValidator();
    $schema = $validator->emptySchema('اختبار');
    $schema['questions'][] = $validator->makeBlankQuestion('true_false');
    $schema['questions'][0]['stem'] = 'هل الأرض كروية؟';

    $result = $validator->validate($schema);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

test('schema validator accepts option media fields', function () {
    $validator = new SchemaValidator();
    $schema = $validator->emptySchema('اختبار أطفال');
    expect($schema['theme']['themeId'])->toBe('kids');

    $q = $validator->makeBlankQuestion('single_choice');
    $q['stem'] = 'اختر الحيوان';
    $q['payload']['options'][0]['icon'] = '🦁';
    $q['payload']['options'][0]['imageUrl'] = null;
    $q['payload']['options'][0]['audioUrl'] = '/sounds/ile/success-01.mp3';
    $schema['questions'][] = $q;

    $result = $validator->validate($schema);

    expect($result['valid'])->toBeTrue();
});

test('schema validator rejects non-string option icon', function () {
    $validator = new SchemaValidator();
    $schema = $validator->emptySchema('اختبار');
    $q = $validator->makeBlankQuestion('single_choice');
    $q['stem'] = 'سؤال';
    $q['payload']['options'][0]['icon'] = ['bad'];
    $schema['questions'][] = $q;

    $result = $validator->validate($schema);

    expect($result['valid'])->toBeFalse();
});

test('schema validator accepts dynamic question with scene and choice', function () {
    $validator = new SchemaValidator();
    $schema = $validator->emptySchema('عدّ التفاح', 'dynamic');
    $q = $validator->makeBlankDynamicQuestion('single_choice');
    $q['stem'] = 'كم عدد التفاحات؟';
    $q['stemBlocks'] = [
        ['type' => 'text', 'text' => 'كم عدد التفاحات الحمراء؟'],
        ['type' => 'scene', 'item' => 'apple', 'count' => 3, 'layout' => 'row'],
    ];
    $q['interaction']['payload']['options'] = [
        ['id' => 'a', 'label' => '2', 'icon' => '2️⃣'],
        ['id' => 'b', 'label' => '3', 'icon' => '3️⃣'],
        ['id' => 'c', 'label' => '5', 'icon' => '5️⃣'],
    ];
    $q['interaction']['payload']['correctId'] = 'b';
    $schema['questions'][] = $q;

    $result = $validator->validate($schema);

    expect($result['valid'])->toBeTrue()
        ->and($schema['mode'])->toBe('dynamic')
        ->and($schema['version'])->toBe(SchemaValidator::SCHEMA_VERSION_DYNAMIC);
});

test('schema validator rejects dynamic question without stemBlocks', function () {
    $validator = new SchemaValidator();
    $schema = $validator->emptySchema('ديناميك', 'dynamic');
    $q = $validator->makeBlankDynamicQuestion('single_choice');
    $q['stemBlocks'] = [];
    $schema['questions'][] = $q;

    $result = $validator->validate($schema);

    expect($result['valid'])->toBeFalse();
});
