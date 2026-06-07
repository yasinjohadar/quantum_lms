<?php

use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\Exports\QuestionWordDocumentBuilder;
use App\Support\QuestionExportContentRenderer;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Support\Collection;

test('question export content renderer avoids duplicate title and content', function () {
    $title = "باستخدام البرهان بالتدريج لإثبات صحة العلاقة\r\n&nbsp;`sum_{k=1 to n} k*k! = (n+1)! - 1` لكل `n &gt;= 1`.";
    $content = 'باستخدام البرهان بالتدريج لإثبات صحة العلاقة `sum_{k=1 to n} k*k! = (n+1)! - 1` لكل `n >= 1`.';

    $question = new Question([
        'type' => 'single_choice',
        'title' => $title,
        'content' => $content,
        'difficulty' => 'medium',
        'default_points' => 1,
    ]);

    $stem = QuestionExportContentRenderer::questionStemText($question);

    expect($stem)->not->toContain("\n\n");
    expect(mb_substr_count($stem, 'باستخدام البرهان بالتدريج'))->toBe(1);
});

test('word document builder creates docx for single choice math question', function () {
    $question = new Question([
        'type' => 'single_choice',
        'title' => 'ما هي قيمة النهاية الشهيرة lim_x \to \infty \frac{1}{x^n} حيث n عدد طبيعي غير معدوم؟',
        'content' => null,
        'difficulty' => 'hard',
        'default_points' => 1,
        'explanation' => 'عندما يكبر المقام بشكل غير محدود تقارب القيمة من الصفر.',
    ]);

    $options = collect([
        new QuestionOption(['content' => '1', 'is_correct' => true, 'order' => 1]),
        new QuestionOption(['content' => '+∞', 'is_correct' => false, 'order' => 2]),
        new QuestionOption(['content' => '-∞', 'is_correct' => false, 'order' => 3]),
    ]);
    $question->setRelation('options', $options);

    $builder = new QuestionWordDocumentBuilder;
    $path = $builder->saveToTempFile(collect([$question]), [
        'title' => 'بنك أسئلة — رياضيات',
        'subtitle' => 'اختبار وحدة',
    ], 'list_order');

    expect(is_file($path))->toBeTrue();
    expect(filesize($path))->toBeGreaterThan(1000);

    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();
    $documentXml = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($path);

    expect($documentXml)->toBeString();
    expect($documentXml)->toContain('ما هي قيمة النهاية');
    expect($documentXml)->toContain('lim');
    expect($documentXml)->toContain('الإجابة الصحيحة');
    expect($documentXml)->toContain('عندما يكبر المقام');
});

test('word document builder groups questions by type', function () {
    $single = new Question([
        'type' => 'single_choice',
        'title' => 'سؤال اختيار واحد',
        'difficulty' => 'easy',
        'default_points' => 1,
    ]);
    $single->setRelation('options', collect([
        new QuestionOption(['content' => 'أ', 'is_correct' => true, 'order' => 1]),
    ]));

    $essay = new Question([
        'type' => 'essay',
        'title' => 'سؤال مقالي',
        'difficulty' => 'medium',
        'default_points' => 2,
        'explanation' => 'إجابة نموذجية للمقال.',
    ]);
    $essay->setRelation('options', collect());

    $builder = new QuestionWordDocumentBuilder;
    $path = $builder->saveToTempFile(collect([$essay, $single]), [
        'title' => 'بنك الأسئلة',
    ], 'by_type');

    $zip = new ZipArchive;
    $zip->open($path);
    $documentXml = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($path);

    $singlePos = mb_strpos($documentXml, 'اختيار واحد');
    $essayPos = mb_strpos($documentXml, 'مقالي');

    expect($singlePos)->not->toBeFalse();
    expect($essayPos)->not->toBeFalse();
    expect($singlePos)->toBeLessThan($essayPos);
});

test('question stem uses title only when content matches title', function () {
    $title = 'سؤال بسيط';
    $question = new Question([
        'type' => 'short_answer',
        'title' => $title,
        'content' => $title,
        'difficulty' => 'easy',
        'default_points' => 1,
    ]);

    expect(QuestionExportContentRenderer::questionStemText($question))->toBe('سؤال بسيط');
});
