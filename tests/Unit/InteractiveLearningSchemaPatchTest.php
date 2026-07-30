<?php

use App\InteractiveLearning\Services\SchemaPatchApplicator;
use App\InteractiveLearning\Services\SchemaValidator;

test('schema patch applicator updates question stem', function () {
    $validator = new SchemaValidator();
    $schema = $validator->emptySchema('عنوان');
    $q = $validator->makeBlankQuestion('true_false');
    $q['stem'] = 'قديم';
    $schema['questions'][] = $q;

    $applicator = new SchemaPatchApplicator();
    $result = $applicator->apply($schema, [[
        'op' => 'update_question',
        'questionId' => $q['id'],
        'fields' => ['stem' => 'جديد', 'explanation' => 'شرح'],
    ]]);

    expect($result['applied'])->toBe(1)
        ->and($result['schema']['questions'][0]['stem'])->toBe('جديد')
        ->and($result['schema']['questions'][0]['explanation'])->toBe('شرح');
});
