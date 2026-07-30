<?php

use App\InteractiveLearning\Services\ContentLogicChecker;
use App\InteractiveLearning\Services\ExperienceQuestionImportService;
use App\InteractiveLearning\Services\SchemaValidator;
use App\Services\QuestionPackImport\CsvQuestionPackParser;
use App\Services\QuestionPackImport\MarkdownQuestionPackParser;

function makeExperienceImportService(): ExperienceQuestionImportService
{
    return new ExperienceQuestionImportService(
        new SchemaValidator,
        new ContentLogicChecker,
        new CsvQuestionPackParser,
        new MarkdownQuestionPackParser,
    );
}

test('experience import parses typed csv with single choice and numerical', function () {
    $csv = <<<'CSV'
type,stem,difficulty,points,hint,explanation,option_a,option_b,option_c,option_d,correct,tolerance,unit
single_choice,"ما ناتج 2+2؟",easy,1,"اجمع","2+2=4",3,4,5,6,B,,
numerical,"احسب 10/2",medium,1,"","","",,,,5,0,
true_false,"3+1=4",easy,1,"","","",,,,true,,
CSV;

    $result = makeExperienceImportService()->parseContent($csv, 'csv', 'classic');

    expect($result['questions'])->toHaveCount(3);
    expect($result['questions'][0]['type'])->toBe('single_choice');
    expect($result['questions'][0]['payload']['correctId'])->toBe('b');
    expect($result['questions'][1]['type'])->toBe('numerical');
    expect((string) $result['questions'][1]['payload']['correct'])->toBe('5');
    expect($result['questions'][2]['type'])->toBe('true_false');
    expect($result['questions'][2]['payload']['correct'])->toBeTrue();
    expect($result['previews'])->toHaveCount(3);
});

test('experience import parses math pack csv into single_choice', function () {
    $csv = <<<'CSV'
#,Question,Hint,Option A,Option B,Option C,Option D,Correct Answer,Rationale
1,"ما ناتج $2+2$؟","اجمع",3,4,5,6,"B. 4","2+2=4"
CSV;

    $result = makeExperienceImportService()->parseContent($csv, 'csv', 'classic');

    expect($result['questions'])->toHaveCount(1);
    expect($result['questions'][0]['type'])->toBe('single_choice');
    expect($result['questions'][0]['payload']['correctId'])->toBe('b');
});

test('experience import parses json array into schema questions', function () {
    $json = json_encode([
        'questions' => [
            [
                'type' => 'short_answer',
                'stem' => 'ما عاصمة سوريا؟',
                'payload' => [
                    'correct' => 'دمشق',
                    'acceptedAnswers' => ['دمشق', 'الشام'],
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);

    $result = makeExperienceImportService()->parseContent($json, 'json', 'classic');

    expect($result['questions'])->toHaveCount(1);
    expect($result['questions'][0]['type'])->toBe('short_answer');
    expect($result['questions'][0]['payload']['correct'])->toBe('دمشق');
});

test('experience import converts to dynamic mode with stemBlocks', function () {
    $csv = <<<'CSV'
type,stem,difficulty,points,option_a,option_b,correct
single_choice,"سؤال ديناميك",easy,1,أ,ب,A
CSV;

    $result = makeExperienceImportService()->parseContent($csv, 'csv', 'dynamic');

    expect($result['questions'][0])->toHaveKeys(['stemBlocks', 'interaction']);
    expect($result['questions'][0]['interaction']['type'])->toBe('single_choice');

    $validator = new SchemaValidator;
    $schema = $validator->emptySchema('اختبار', 'dynamic');
    $schema['questions'] = $result['questions'];
    $check = $validator->validate($schema);
    expect($check['valid'])->toBeTrue($check['errors'] ? implode(' | ', $check['errors']) : '');
});
