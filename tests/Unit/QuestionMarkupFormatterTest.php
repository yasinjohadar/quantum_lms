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

test('wraps bare latex in mcq options for katex', function () {
    $input = 'ln|1+\cos^{2}x|+C\-';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\(')
        ->toContain('\cos^{2}x')
        ->not->toContain('question-inline-code');
});

test('bare latex option arctan', function () {
    $input = 'arctan(\cos x)+C';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('arctan(\cos x)+C');
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

test('decodes html entities in arabic question text', function () {
    $input = 'ما هو الشكل الصحيح للعبارة التي تبدأ بـ &quot;2.. ) حُدّثي&quot; في النص؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('2.. ) حُدّثي')
        ->not->toContain('&amp;quot;')
        ->not->toContain('&amp;#');
});

test('decodes double-encoded html entities', function () {
    $input = '&amp;quot;نص عربي&amp;quot;';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('نص عربي')
        ->not->toContain('&amp;quot;')
        ->not->toContain('&amp;#');
});

test('plain heading decodes entities before stripping', function () {
    $input = '&quot;عنوان قصير&quot; للسؤال';

    $heading = QuestionMarkupFormatter::plainHeading($input, 50);

    expect($heading)
        ->toContain('"عنوان قصير"')
        ->not->toContain('&quot;');
});

test('wraps absolute value inequalities in inline code', function () {
    $input = 'حدد جميع القيم التي تحقق المتباينة |x| ≤ 3.';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('<code class="question-inline-code">|x| ≤ 3</code>')
        ->toContain('حدد جميع القيم');
});

test('wraps numeric intervals and mcq numeric options', function () {
    $explanation = 'القيم ضمن الفترة [-3, 3].';

    expect(QuestionMarkupFormatter::format($explanation))
        ->toContain('<code class="question-inline-code">[-3, 3]</code>');

    expect(QuestionMarkupFormatter::format('-4'))
        ->toBe('<code class="question-inline-code">-4</code>');

    expect(QuestionMarkupFormatter::format('2'))
        ->toBe('<code class="question-inline-code">2</code>');
});

test('normalizes famous limit pseudo latex in arabic question stem', function () {
    $input = 'ما هي قيمة النهاية الشهيرة (1)/(x^n) lim_x \to +\infty حيث n عدد طبيعي غير معدوم؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\lim_{x \to +\infty}')
        ->toContain('\frac{1}{x^n}')
        ->toContain('\(n\)')
        ->toContain('ما هي قيمة النهاية الشهيرة');
});

test('wraps single dollar inline math segments', function () {
    $input = 'ما هي قيمة النهاية $\lim_{x \to +\infty} \frac{1}{x^n}$ حيث $n$ عدد طبيعي؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\(\lim_{x \to +\infty} \frac{1}{x^n}\)')
        ->toContain('\(n\)');
});

test('wraps infinity unicode mcq options as math', function () {
    expect(QuestionMarkupFormatter::format('+∞'))
        ->toContain('question-math-fragment')
        ->toContain('\(+\infty\)');

    expect(QuestionMarkupFormatter::format('-∞'))
        ->toContain('question-math-fragment')
        ->toContain('\(-\infty\)');
});

test('normalizes lim frac storage format in arabic stem', function () {
    $input = 'ما هي قيمة النهاية الشهيرة lim_x \to \infty \frac{1}{x^n} حيث n عدد طبيعي غير معدوم؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\lim_{x \to \infty}')
        ->toContain('\frac{1}{x^n}')
        ->toContain('ما هي قيمة النهاية الشهيرة');
});

test('normalizes split infinity option variants to single fragment', function () {
    foreach (['+$\infty$', '$+∞$', '+\infty'] as $option) {
        $result = QuestionMarkupFormatter::format($option);

        expect($result)
            ->toContain('question-math-fragment')
            ->toContain('\(+\infty\)')
            ->not->toContain('$+\infty$$');
    }
});

test('looks like bare latex returns false for arabic mixed text', function () {
    $input = 'ما هي قيمة النهاية lim_x \to \infty \frac{1}{x^n}';

    expect(QuestionMarkupFormatter::format($input))
        ->toContain('ما هي قيمة النهاية');
});

test('same plain text treats title and content duplicates as equal', function () {
    $title = "باستخدام البرهان بالتدريج لإثبات صحة العلاقة\r\n&nbsp;`sum_{k=1 to n} k*k! = (n+1)! - 1` لكل `n &gt;= 1`.";
    $content = 'باستخدام البرهان بالتدريج لإثبات صحة العلاقة `sum_{k=1 to n} k*k! = (n+1)! - 1` لكل `n >= 1`.';

    expect(QuestionMarkupFormatter::samePlainText($title, $content))->toBeTrue();
});

test('plain heading strips normalized limit latex', function () {
    $input = 'ما هي قيمة النهاية الشهيرة (1)/(x^n) lim_x \to +\infty حيث n عدد طبيعي غير معدوم؟';

    $heading = QuestionMarkupFormatter::plainHeading($input);

    expect($heading)
        ->toContain('ما هي قيمة النهاية الشهيرة')
        ->not->toContain('\lim')
        ->not->toContain('\frac');
});

test('converts math backticks in induction proof to math fragments', function () {
    $input = 'باستخدام البرهان بالتدريج لإثبات صحة العلاقة `sum_{k=1 to n} k*k! = (n+1)! - 1` لكل `n >= 1`.';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\sum_{k=1}^{n}')
        ->toContain('\cdot')
        ->toContain('\geq')
        ->not->toContain('question-inline-code');
});

test('formats integral mcq stem with latex delimiters', function () {
    $input = 'ما هو ناتج التكامل \(\int \frac{\ln(x)}{x} dx\)؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\int')
        ->toContain('\frac');
});

test('normalize for storage converts pseudo math backticks', function () {
    $stored = QuestionMarkupFormatter::normalizeForStorage('`sum_{k=1 to n} k*k!`');

    expect($stored)
        ->toContain('$')
        ->toContain('\sum_{k=1}^{n}');
});

test('bare factorial mcq option renders as math', function () {
    $input = '(m+1)! - 1 + (m+1)(m+1)!';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->not->toContain('question-inline-code');
});

test('formats multiline sequence explanation with subscripts and equation steps', function () {
    $input = <<<'TEXT'
لدراسة رتابة المتتالية، نقوم بحساب الفرق `u_{n+1} - u_n`:
`u_{n+1} = (2(n+1)-1)/((n+1)+4) = \frac{2n+1}{n+5}`
`= [(2n+1)(n+4) - (2n-1)(n+5)] / [(n+5)(n+4)]`
نفك الأقواس في البسط:
TEXT;

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-explanation-text-line')
        ->toContain('question-explanation-math-line')
        ->toContain('question-math-fragment')
        ->toContain('u_{n+1}')
        ->toContain('\frac')
        ->not->toContain('question-inline-code');
});

test('looks like math expression detects subscript difference', function () {
    expect(QuestionMarkupFormatter::looksLikeMathExpression('u_{n+1} - u_n'))->toBeTrue();
    expect(QuestionMarkupFormatter::looksLikeMathExpression('= 9 / [(n+5)(n+4)]'))->toBeTrue();
});
