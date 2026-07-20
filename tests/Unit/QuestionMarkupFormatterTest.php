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
        ->toContain('katex-src')
        ->toContain('question-math-fragment')
        ->toContain('\int \frac{\ln x}{x} dx')
        ->not->toContain('question-math-content')
        ->not->toContain('&lt;');
});

test('wraps bare latex in mcq options for katex', function () {
    $input = 'ln|1+\cos^{2}x|+C\-';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('katex-src')
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

test('wraps absolute value inequalities as math', function () {
    $input = 'حدد جميع القيم التي تحقق المتباينة |x| ≤ 3.';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\\leq')
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
        ->toContain('katex-src')
        ->toContain('\lim_{x \to +\infty}')
        ->toContain('\frac{1}{x^n}')
        ->toContain('>n</span>')
        ->toContain('ما هي قيمة النهاية الشهيرة');
});

test('wraps single dollar inline math segments', function () {
    $input = 'ما هي قيمة النهاية $\lim_{x \to +\infty} \frac{1}{x^n}$ حيث $n$ عدد طبيعي؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('katex-src')
        ->toContain('\lim_{x \to +\infty} \frac{1}{x^n}')
        ->toContain('>n</span>');
});

test('wraps infinity unicode mcq options as math', function () {
    expect(QuestionMarkupFormatter::format('+∞'))
        ->toContain('katex-src')
        ->toContain('+\infty');

    expect(QuestionMarkupFormatter::format('-∞'))
        ->toContain('katex-src')
        ->toContain('-\infty');
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
            ->toContain('katex-src')
            ->toContain('+\infty')
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

test('normalize for storage converts notebooklm unicode math mixed with arabic', function () {
    $input = 'ليكن f(x) = √(x² + 4x + 5) لماذا يعتبر هذا التابع مستمراً على ℝ؟';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('$')
        ->toContain('\\sqrt{')
        ->toContain('x^{2}')
        ->toContain('\\mathbb{R}')
        ->not->toContain('√')
        ->not->toContain('ℝ')
        ->not->toContain('²');
});

test('normalize for storage converts notebooklm csv unicode subscripts', function () {
    $input = 'لتكن المتتالية الحسابية (uₙ)ₙ gₑ ₀ التي حدها الأول u₀ = 5';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('(u_n)_{n \\geq 0}')
        ->toContain('u_{0}')
        ->not->toContain('uₙ');
});

test('format renders notebooklm unicode math as katex fragments', function () {
    $input = 'ليكن f(x) = √(x² + 4x + 5) لماذا يعتبر هذا التابع مستمراً على ℝ؟';

    $result = QuestionMarkupFormatter::format($input);

    expect($result)
        ->toContain('question-math-fragment')
        ->toContain('\\sqrt')
        ->toContain('\\mathbb{R}');
});

test('normalize for storage keeps existing dollar latex intact', function () {
    $input = 'ليكن $f(x) = \\sqrt{x^{2} + 4x + 5}$ لماذا يعتبر هذا التابع مستمراً على $\\mathbb{R}$؟';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('$f(x) = \\sqrt{x^{2} + 4x + 5}$')
        ->toContain('$\\mathbb{R}$');
});

test('normalize for storage softens simple html from imports', function () {
    $input = '<p>احسب &amp;nbsp;`n &gt;= 1`</p>';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->not->toContain('<p>')
        ->toContain('$')
        ->toContain('\\geq');
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

test('repairs broken f(x) equals double-dollar frac delimiters', function () {
    $broken = 'أوجد نهاية التابع $f(x) = $$\frac{5x^{3} - 3x - 1}{8x^{4} - 12x^{2} + 5x}$ عندما x تسعى إلى +\infty.';

    $stored = QuestionMarkupFormatter::normalizeForStorage($broken);
    $formatted = QuestionMarkupFormatter::format($broken);

    expect($stored)
        ->toContain('$f(x) = \\frac{5x^{3} - 3x - 1}{8x^{4} - 12x^{2} + 5x}$')
        ->not->toContain('=$$')
        ->not->toContain('$$f(x)');

    expect($formatted)
        ->toContain('question-math-fragment')
        ->toContain('\\frac{5x^{3}')
        ->not->toContain('question-inline-code')
        ->not->toContain('$$');
});

test('normalizes bare f(x) equals frac in arabic stem as single math fragment', function () {
    $input = 'أوجد نهاية التابع f(x) = \frac{5x^3 - 3x - 1}{8x^4 - 12x^2 + 2} عندما x تسعى إلى +\infty.';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);
    $formatted = QuestionMarkupFormatter::format($input);

    expect($stored)
        ->toContain('$f(x) = \\frac{5x^{3} - 3x - 1}{8x^{4} - 12x^{2} + 2}$')
        ->not->toContain('=$$');

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('f(x) = \\frac{5x^{3}')
        ->toContain('+\\infty')
        ->not->toContain('question-inline-code');
});

test('formats induction sequence stem with braces and inequalities', function () {
    $input = 'لتكن المتتالية {u_{n+1}} = \sqrt{2 + u_n} مع {u_0} = 1. أثبت بالتدريج أن u_n < 2 لجميع n \geq 0.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('question-math-fragment')
        ->toContain('\\sqrt{2 + u_n}')
        ->toContain('\\lt')
        ->toContain('\\geq')
        ->not->toContain('u_n < 2')
        ->not->toContain('{u_{n+1}}');
});

test('html-safe math converts less-than inside fragments', function () {
    $formatted = QuestionMarkupFormatter::format('نبدأ من الفرض $u_p < 2$ ثم نضيف 2');

    expect($formatted)
        ->toContain('question-math-fragment')
        ->toContain('\\lt')
        ->not->toContain('u_p < 2');
});

test('formats geometric sequence stem with subscript notation', function () {
    $input = 'لتكن المتتالية الهندسية (u_n)_{n \\ge 0} أساسها q=2 وحدها الأول u_{0}=3. ما هي قيمة الحد u_{5}؟';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('(u_n)_{n \\geq 0}')
        ->toContain('u_{0}=3')
        ->toContain('u_{5}')
        ->toContain('q=2');
});

test('formats induction sum of squares stem as single katex source', function () {
    $input = 'أثبت بالتدريج أن S_n = 1^{2} + 2^{2} + ... + n^{2} = ( n(n+1)(2n+1))/(6) لكل n طبيعي. ما هو الطرف الأيمن لـ S_{p+1}؟';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('S_n = 1^{2}')
        ->toContain('\\cdots')
        ->toContain('\\frac')
        ->not->toContain('question-inline-code')
        ->not->toContain('1</span>^{2}');
});

test('looks like math expression detects subscript difference', function () {
    expect(QuestionMarkupFormatter::looksLikeMathExpression('u_{n+1} - u_n'))->toBeTrue();
    expect(QuestionMarkupFormatter::looksLikeMathExpression('= 9 / [(n+5)(n+4)]'))->toBeTrue();
});

test('formats complex recurrence relation stem as katex fragments', function () {
    $input = 'بفرض u_n-10u_(n+1) = 10u_n - 18. إذا علمنا أن u_(0) = 7 و u_n = 5*10^(n) + 2 لجميع n ≥ 0, فما هي قيمة u_10؟';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('u_n-10u_{n+1} = 10u_n - 18')
        ->toContain('u_{0} = 7')
        ->toContain('u_n = 5 \\cdot 10^{n} + 2')
        ->toContain('n \\geq 0')
        ->toContain('u_{10}');
});

test('does not stop math normalization when inline code is also present', function () {
    $input = 'استخدم الدالة `at(1)` لحساب $x^2 + 1$.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('<code class="question-inline-code">at(1)</code>')
        ->toContain('katex-src')
        ->toContain('x^{2} + 1')
        ->not->toContain('$x^2 + 1$');
});

test('converts parenthesised subscripts and superscripts to brace notation', function () {
    $input = 'u_(n+1) = u_n + 1 و 10^(n) عدد كبير.';

    $formatted = QuestionMarkupFormatter::format($input);
    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($formatted)
        ->toContain('u_{n+1}')
        ->toContain('10^{n}')
        ->not->toContain('u_(n+1)')
        ->not->toContain('10^(n)');

    expect($stored)
        ->toContain('u_{n+1}')
        ->toContain('10^{n}');
});

test('removes a stray unbalanced dollar sign instead of rendering it raw', function () {
    $input = 'السعر هو $5 فقط لهذا المنتج.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->not->toContain('$5')
        ->toContain('5 فقط');
});

test('does not corrupt other fractions in the expression when converting an unbalanced dollar', function () {
    $input = 'السؤال $ناقص. احسب $\frac{a}{b}$ رجاءً.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)->toContain('katex-src');
});

test('wraps nested frac and sqrt as a single katex fragment without truncation', function () {
    $input = 'f(x) = \frac{\sqrt{x+1}}{x^{2}+1} هي دالة معرّفة لكل x.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('f(x) = \\frac{\\sqrt{x+1}}{x^{2}+1}');
});

test('converts multiple slash fractions in one expression without greedy corruption', function () {
    $input = 'بسّط (a)/(b)+(c)/(d) إلى أبسط صورة.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('\\frac{a}{b}')
        ->toContain('\\frac{c}{d}')
        ->not->toContain('b)+(c)/(d');
});

test('converts nested-parentheses square root as one katex fragment', function () {
    $input = 'احسب √((x+1)/(x-1)) عند x=3.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('\\sqrt{(x+1)/(x-1)}')
        ->not->toContain('\\sqrt{}')
        ->not->toContain('} عند');
});

test('converts arabic-indic digits to ascii before math normalization', function () {
    $input = 'إذا كان u_٥ = ٣ فأوجد u_٦.';

    $formatted = QuestionMarkupFormatter::format($input);
    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($formatted)
        ->toContain('u_5 = 3')
        ->toContain('u_6')
        ->not->toContain('٥')
        ->not->toContain('٣');

    expect($stored)->toContain('u_5 = 3');
});

test('wraps bare trig identities without backslash as one math fragment', function () {
    $input = 'أثبت أن sin^{2}x + cos^{2}x = 1 لكل x.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('\\sin^{2}x + \\cos^{2}x = 1');
});

test('treats combinatorics notation as math instead of code', function () {
    $input = 'عدد التوافيق C(n,k) يساوي n!/(k!(n-k)!) واحسب أيضاً P(n,r).';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->not->toContain('<code class="question-inline-code">C(n,k)</code>')
        ->not->toContain('<code class="question-inline-code">P(n,r)</code>')
        ->toContain('katex-src')
        ->toContain('C(n,k)')
        ->toContain('P(n,r)');
});

test('does not misclassify parenthesised subscript recurrence as code without arabic context', function () {
    $input = 'u_n-10u_(n+1) = 10u_n - 18';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->not->toContain('question-inline-code')
        ->toContain('katex-src')
        ->toContain('u_n-10u_{n+1} = 10u_n - 18');
});

test('wraps sqrt directly wrapping a frac as a single katex fragment', function () {
    $input = 'f(x) = \sqrt{\frac{a}{b}}';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('f(x) = \\sqrt{\\frac{a}{b}}')
        ->not->toContain('$');
});

test('does not wrap a bare frac in dollars when nested inside an unclosed outer command', function () {
    $input = 'أثبت أن g(x) = \sqrt{\frac{x+1}{x-1}} معرّفة على مجالها.';

    $formatted = QuestionMarkupFormatter::format($input);

    expect($formatted)
        ->toContain('katex-src')
        ->toContain('g(x) = \\sqrt{\\frac{x+1}{x-1}}');
});

test('converts compound unicode subscript with trailing sign as one brace group', function () {
    $input = 'uₙ₊₁ = 10uₙ - 18 و uₙ₋₁ يساوي القيمة السابقة';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('u_{n+1}')
        ->toContain('u_{n-1}')
        ->not->toContain('u_n_{+1}')
        ->not->toContain('u_n_{-1}')
        ->not->toContain('ₙ');
});

test('converts unicode superscript letter x to caret notation', function () {
    $input = "أوجد مشتق التابع f(x) = eˣ ونهاية g(x) = e⁻³ˣ.";

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('e^{x}')
        ->toContain('e^{-3x}')
        ->not->toContain('ˣ');
});

test('converts stacked superscript-slash-subscript unicode fraction to frac notation', function () {
    $input = 'المجموع هو ¹⁰⁄₂(5 + 32) = 185.';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('\\frac{10}{2}')
        ->not->toContain('⁄');
});

test('does not split a sqrt call into fragments when its argument already has a braced exponent', function () {
    // انحدار حقيقي: \sqrt(x^{2}+x) (وسيط بأس مُهيّأ مسبقاً بأقواس {}) كان يُقسَّم
    // خطأً إلى ثلاث مقاطع منفصلة "\sqrt" و "x^{2}" و باقي التعبير، بسبب نمط
    // "أُس عارٍ" في wrapPseudoMathSegmentsInPlainText الذي لا يتحقق من وجوده
    // داخل قوس ( لم يُغلَق بعد.
    $withBraces = 'نهاية التابع f(x) = x - \sqrt(x^{2}+x) عندما x \to +\infty هي:';
    $withoutBraces = 'نهاية التابع f(x) = x - \sqrt(x^2+x) عندما x \to +\infty هي:';

    $storedWithBraces = QuestionMarkupFormatter::normalizeForStorage($withBraces);
    $storedWithoutBraces = QuestionMarkupFormatter::normalizeForStorage($withoutBraces);

    expect($storedWithBraces)
        ->toContain('$f(x) = x - \\sqrt(x^{2}+x)$')
        ->toContain('$x \\to +\\infty$')
        ->not->toContain('\\sqrt$')
        ->not->toContain('$\\sqrt');

    expect($storedWithBraces)->toBe($storedWithoutBraces);
});

test('strips existing math delimiters back to plain latex source', function () {
    $input = 'نهاية التابع $f(x)$ = x - \sqrt(x^{2}+x) $عندما x$ \to+\infty هي:';

    $stripped = QuestionMarkupFormatter::stripMathDelimiters($input);

    expect($stripped)
        ->toBe('نهاية التابع f(x) = x - \sqrt(x^{2}+x) عندما x \to+\infty هي:')
        ->not->toContain('$');
});

test('deep normalize repairs a legacy question stored with wrong math delimiters (screenshot regression)', function () {
    // محاكاة سؤال قديم مُخزَّن قبل إصلاح الأُس العاري داخل sqrt: التابع f(x) مُغلَّف
    // بشكل منعزل، وعبارة "عندما x" مُغلَّفة خطأً بالكامل (نص عربي داخل رياضيات)،
    // بينما "\to+\infty" وباقي التعبير غير مُغلَّفين أبداً — تماماً كما ظهر على
    // السيرفر (نص خام ظاهر للطالب).
    $legacyBroken = 'نهاية التابع $f(x)$ = x - \sqrt(x^{2}+x) $عندما x$ \to+\infty هي:';

    $repaired = QuestionMarkupFormatter::deepNormalizeForStorage($legacyBroken);

    expect($repaired)
        ->toContain('$f(x) = x - \\sqrt(x^{2}+x)$')
        ->toContain('$x \\to+\\infty$')
        ->not->toContain('عندما x$')
        ->not->toContain('\\to+\\infty هي')
        ->not->toContain('\\sqrt$');

    // \sqrt و \to يجب أن يظهرا فقط داخل مقاطع katex-src (مصدر خام يُرندَر لاحقاً
    // عبر KaTeX في المتصفح)، وليس كنص خام طليق خارجها.
    $html = format_question_markup($repaired);
    expect($html)
        ->toContain('katex-src')
        ->toMatch('/<span class="katex-src[^>]*>[^<]*\\\\sqrt/');

    // إعادة تشغيل الإصلاح على النتيجة المُصحَّحة يجب أن تكون بلا أي تغيير إضافي.
    expect(QuestionMarkupFormatter::deepNormalizeForStorage($repaired))->toBe($repaired);
});

test('deep normalize falls back to the safe shallow result instead of leaving stray backslashes', function () {
    // لاتيكس مخصّص نادر (\oint) قد لا يغطيه الكشف التلقائي بعد نزع $...$ وإعادة
    // البناء من الصفر؛ يجب ألا يفقد الإصلاح الشامل رياضيات كانت مُغلَّفة بصورة
    // صحيحة أصلاً عبر ترك شرطة عكسية "\" خاماً ظاهرة للمستخدم.
    $alreadyGood = 'أثبت أن $\oint_C \vec{F} \cdot d\vec{r} = 0$ لحقل متحفظ.';

    $result = QuestionMarkupFormatter::deepNormalizeForStorage($alreadyGood);

    expect($result)->toBe($alreadyGood);
});

test('deep normalize is a no-op safe superset of normalize for storage on already-correct text', function () {
    $good = 'نهاية التابع $f(x) = x - \sqrt{x^{2}+x}$ عندما $x \to +\infty$ هي:';

    expect(QuestionMarkupFormatter::deepNormalizeForStorage($good))->toBe($good);
});

test('merges a double ascii subscript typo into one braced subscript instead of a KaTeX double-subscript error', function () {
    // انحدار حقيقي من السيرفر: متتالية عودية كُتبت بمؤشرين سفليين متتاليين
    // u_n_(+1) بدل u_{n+1}، ما يُسبّب خطأ "Double subscript" في KaTeX ويُعرَض
    // كنص LaTeX خام أحمر للطالب (لقطة شاشة: سؤال المتتالية العودية 3/20).
    $withParenContinuation = 'لتكن المتتالية المعرفة بالعلاقة التكرارية u_n_(+1) = \sqrt{2 + u_n} مع u_0 = 1 ، ما هو نوع اطراد هذه المتتالية؟';
    $withBraceContinuation = 'لتكن المتتالية المعرفة بالعلاقة التكرارية u_n_{+1} = \sqrt{2 + u_n} مع u_0 = 1 ، ما هو نوع اطراد هذه المتتالية؟';
    $withBareContinuation = 'لتكن المتتالية المعرفة بالعلاقة التكرارية u_n_+1 = \sqrt{2 + u_n} مع u_0 = 1 ، ما هو نوع اطراد هذه المتتالية؟';

    foreach ([$withParenContinuation, $withBraceContinuation, $withBareContinuation] as $input) {
        $stored = QuestionMarkupFormatter::normalizeForStorage($input);

        expect($stored)
            ->toContain('u_{n+1}')
            ->not->toContain('u_n_');

        $html = format_question_markup($stored);
        expect($html)->toContain('katex-src');
    }

    // لا يجب أن يلمس متغيّرات منفصلة غير متلاصقة (a_1 ... a_2).
    $unrelatedVars = 'قارن بين a_1 و a_2 في هذا المتتالية.';
    expect(QuestionMarkupFormatter::normalizeForStorage($unrelatedVars))->toContain('a_1')->toContain('a_2');
});

test('recognizes a NotebookLM-style brace wrapping a paren subscript as math instead of leaking a stray brace', function () {
    // انحدار حقيقي: {u_(n+1)} (غلاف NotebookLM حول مؤشر سفلي بأقواس عادية) لم
    // يكن looksLikeMathExpression يتعرّف عليه، فيبقى القوس الخارجي { } كنص خام
    // معلَّق، وشبكة الأمان اللاحقة (wrapBareMathRunsOutsideMath) تلفّ نطاقاً
    // خاطئ الحدود يتضمن قوساً معقوفاً زائداً.
    $input = 'لتكن المتتالية المعرفة بالعلاقة التكرارية {u_(n+1)} = \sqrt{2 + u_n} مع {u_0 = 1} ، ما هو نوع اطراد هذه المتتالية؟';

    $stored = QuestionMarkupFormatter::normalizeForStorage($input);

    expect($stored)
        ->toContain('$u_{n+1}$')
        ->toContain('$\sqrt{2 + u_n}$')
        ->toContain('$u_0 = 1$')
        ->not->toContain('{$')
        ->not->toContain('}$}');
});
