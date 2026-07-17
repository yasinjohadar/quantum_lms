/**
 * رسم معادلات الأسئلة عبر KaTeX — مسار واحد فقط:
 * 1) .katex-src → katex.render(textContent)  [المصدر الأساسي الآمن]
 * 2) احتياطي: auto-render لأي $...$ متبقٍ في .question-text-body
 */
(function () {
    'use strict';

    var katexOptions = {
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
            '\\ge': '\\geq',
            '\\le': '\\leq',
        },
    };

    var autoRenderOptions = {
        delimiters: [
            { left: '$$', right: '$$', display: true },
            { left: '\\[', right: '\\]', display: true },
            { left: '\\(', right: '\\)', display: false },
            { left: '$', right: '$', display: false },
        ],
        throwOnError: false,
        strict: false,
        trust: katexOptions.trust,
        macros: katexOptions.macros,
        ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
        ignoredClasses: ['question-inline-code', 'question-code-block', 'no-math', 'katex-src'],
    };

    function katexReady() {
        return typeof window.katex !== 'undefined' && typeof window.katex.render === 'function';
    }

    function queryAll(root, selector) {
        var scope = root && root.querySelectorAll ? root : document;
        var list = [];

        if (root && root.matches && root.matches(selector)) {
            list.push(root);
        }

        if (scope.querySelectorAll) {
            scope.querySelectorAll(selector).forEach(function (el) {
                list.push(el);
            });
        }

        return list;
    }

    function renderKatexSrc(root) {
        if (!katexReady()) {
            return false;
        }

        queryAll(root, '.katex-src').forEach(function (el) {
            if (!el || el.getAttribute('data-katex-done') === '1') {
                return;
            }

            var latex = (el.textContent || '').trim();
            if (!latex) {
                return;
            }

            var displayMode = el.getAttribute('data-display') === '1';

            try {
                window.katex.render(latex, el, Object.assign({}, katexOptions, {
                    displayMode: displayMode,
                }));
                el.setAttribute('data-katex-done', '1');
            } catch (err) {
                // اترك النص كما هو إن فشل الرسم
            }
        });

        return true;
    }

    function renderLeftoverDelimiters(root) {
        if (typeof window.renderMathInElement !== 'function') {
            return;
        }

        var targets = [];

        if (root) {
            targets.push(root);
            queryAll(root, '.question-text-body, .question-page-heading, .math-live-preview-body, .excel-math-preview-body, .question-stem').forEach(function (el) {
                targets.push(el);
            });
        } else {
            targets = queryAll(document, '.question-text-body, .question-page-heading, .math-live-preview-body, .excel-math-preview-body');
        }

        var seen = typeof WeakSet !== 'undefined' ? new WeakSet() : null;

        targets.forEach(function (el) {
            if (!el || (seen && seen.has(el))) {
                return;
            }
            if (seen) {
                seen.add(el);
            }
            try {
                window.renderMathInElement(el, autoRenderOptions);
            } catch (err) {
                // تجاهل
            }
        });
    }

    function renderQuestionMath(root) {
        var ok = renderKatexSrc(root);
        renderLeftoverDelimiters(root);
        return ok;
    }

    function boot(attempt) {
        attempt = attempt || 0;

        if (renderQuestionMath()) {
            return;
        }

        if (attempt < 60) {
            setTimeout(function () {
                boot(attempt + 1);
            }, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            boot(0);
        });
    } else {
        boot(0);
    }

    window.addEventListener('load', function () {
        document.querySelectorAll('.katex-src[data-katex-done="1"]').forEach(function (el) {
            // لا نعيد الرسم إن نجح مسبقاً
        });
        boot(0);
    });

    window.renderQuestionMath = renderQuestionMath;
    window.questionMathRenderOptions = autoRenderOptions;
})();
