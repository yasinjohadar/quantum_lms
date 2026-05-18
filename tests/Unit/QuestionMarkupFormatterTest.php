<?php

use App\Support\QuestionMarkupFormatter;

test('converts inline backticks to styled code without showing backticks', function () {
    $input = 'ما هي القيمة التي تُرجعها الدالة `at(1)` عند تطبيقها على المصفوفة `[4, 5, 6, 7]`؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->not->toContain('`at(1)`')
        ->not->toContain('`[4, 5, 6, 7]`')
        ->toContain('<code class="question-inline-code">at(1)</code>')
        ->toContain('<code class="question-inline-code">[4, 5, 6, 7]</code>');
});

test('converts fenced code blocks', function () {
    $input = "نص قبل\n```\n[4, 5, 6]\n```\nنص بعد";

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('<pre class="question-code-block">')
        ->toContain('[4, 5, 6]')
        ->not->toContain('```');
});

test('wraps latex segments for katex rendering', function () {
    $input = 'ما هو ناتج التكامل: \(\int \frac{\ln x}{x} dx\) ؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\(\int \frac{\ln x}{x} dx\)')
        ->not->toContain('question-math-content')
        ->not->toContain('&lt;');
});

test('plain heading strips latex for page header', function () {
    $input = 'احسب التكامل: \(\int x\cos(x^{2})\,dx\).';

    $heading = QuestionMarkupFormatter::plainHeading($input);

    expect($heading)
        ->toBe('احسب التكامل')
        ->not->toContain('\(')
        ->not->toContain('\int');
});

test('auto wraps bare function calls like mcq answer options', function () {
    expect(QuestionMarkupFormatter::format('push()'))
        ->toBe('<code class="question-inline-code">push()</code>');

    expect(QuestionMarkupFormatter::format('unshift()'))
        ->toContain('question-inline-code">unshift()</code>');
});

test('auto wraps code inside arabic explanation text', function () {
    $input = 'الدالة unshift() تضيف عنصرًا إلى بداية المصفوفة [3, 4, 5, 6, 7]';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-inline-code">unshift()</code>')
        ->toContain('question-inline-code">[3, 4, 5, 6, 7]</code>');
});

test('preserves html while formatting inline code', function () {
    $input = '<p>الدالة <code>قديم</code> و `at(1)`</p>';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('<p>')
        ->toContain('question-inline-code">at(1)</code>');
});
