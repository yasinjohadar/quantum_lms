(function () {
    'use strict';

    var renderOptions = {
        delimiters: [
            { left: '$$', right: '$$', display: true },
            { left: '\\[', right: '\\]', display: true },
            { left: '\\(', right: '\\)', display: false },
            { left: '$', right: '$', display: false },
        ],
        throwOnError: false,
        strict: false,
        trust: function (context) {
            return context.command === '\\ce' || context.command === '\\pu';
        },
        macros: {
            '\\RR': '\\mathbb{R}',
            '\\NN': '\\mathbb{N}',
            '\\ZZ': '\\mathbb{Z}',
            '\\QQ': '\\mathbb{Q}',
            '\\CC': '\\mathbb{C}',
        },
        ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
        ignoredClasses: ['question-inline-code', 'question-code-block', 'no-math'],
    };

    function katexReady() {
        return typeof katex !== 'undefined' && typeof katex.render === 'function';
    }

    function autoRenderReady() {
        return typeof renderMathInElement === 'function';
    }

    function extractLatexFromFragment(el) {
        var raw = (el.textContent || '').trim();
        if (!raw) {
            return null;
        }

        var display = false;
        var latex = raw;

        if (/^\\\[[\s\S]*\\]$/.test(raw)) {
            display = true;
            latex = raw.slice(2, -2).trim();
        } else if (/^\\\([\s\S]*\\\)$/.test(raw)) {
            latex = raw.slice(2, -2).trim();
        } else if (/^\$\$[\s\S]*\$\$$/.test(raw)) {
            display = true;
            latex = raw.slice(2, -2).trim();
        } else if (/^\$[\s\S]*\$$/.test(raw)) {
            latex = raw.slice(1, -1).trim();
        }

        return { latex: latex, display: display };
    }

    function renderFragmentsDirectly(root) {
        if (!katexReady()) {
            return false;
        }

        var scope = root && root.querySelectorAll ? root : document;
        var fragments = scope.querySelectorAll
            ? scope.querySelectorAll('.question-math-fragment')
            : [];

        if (root && root.classList && root.classList.contains('question-math-fragment')) {
            fragments = [root];
        }

        Array.prototype.forEach.call(fragments, function (el) {
            if (!el || el.dataset.katexDirect === '1') {
                return;
            }

            var parsed = extractLatexFromFragment(el);
            if (!parsed || !parsed.latex) {
                return;
            }

            try {
                katex.render(parsed.latex, el, {
                    throwOnError: false,
                    displayMode: parsed.display,
                    strict: false,
                    trust: renderOptions.trust,
                    macros: renderOptions.macros,
                });
                el.dataset.katexDirect = '1';
                el.dataset.mathRendered = '1';
            } catch (e) {
                // اترك النص كما هو
            }
        });

        return true;
    }

    function renderQuestionMath(root) {
        var ok = false;

        if (autoRenderReady()) {
            var targets;

            if (root) {
                targets = [root];
                if (root.querySelectorAll) {
                    root.querySelectorAll(
                        '.question-text-body, .question-page-heading, .math-live-preview-body, .question-math-fragment, .question-stem, .excel-math-preview-body'
                    ).forEach(function (el) {
                        targets.push(el);
                    });
                }
            } else {
                targets = Array.prototype.slice.call(
                    document.querySelectorAll(
                        '.question-text-body, .question-page-heading, .math-live-preview-body, .excel-math-preview-body'
                    )
                );
            }

            var seen = typeof WeakSet !== 'undefined' ? new WeakSet() : null;

            targets.forEach(function (el) {
                if (!el || (seen && seen.has(el))) {
                    return;
                }
                if (seen) {
                    seen.add(el);
                }

                if (el.dataset.mathRendered === '1') {
                    delete el.dataset.mathRendered;
                }

                renderMathInElement(el, renderOptions);
                el.dataset.mathRendered = '1';
            });

            ok = true;
        }

        // مسار مباشر عبر textContent — يتجنّب كسر < داخل HTML
        renderFragmentsDirectly(root || document);

        return ok || katexReady();
    }

    function boot(attempt) {
        attempt = attempt || 0;

        if (renderQuestionMath()) {
            return;
        }

        if (attempt < 50) {
            setTimeout(function () {
                boot(attempt + 1);
            }, 100);
        }
    }

    function scheduleBoot() {
        boot(0);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleBoot);
    } else {
        scheduleBoot();
    }

    window.addEventListener('load', function () {
        document.querySelectorAll('.question-text-body[data-math-rendered="1"], .math-live-preview-body[data-math-rendered="1"], .question-math-fragment[data-katex-direct="1"]').forEach(function (el) {
            delete el.dataset.mathRendered;
            delete el.dataset.katexDirect;
        });
        boot(0);
    });

    window.renderQuestionMath = renderQuestionMath;
    window.questionMathRenderOptions = renderOptions;
})();
