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

    function renderQuestionMath(root) {
        if (typeof renderMathInElement !== 'function') {
            return false;
        }

        var targets = root
            ? [root]
            : Array.prototype.slice.call(
                document.querySelectorAll('.question-text-body, .question-page-heading, .math-live-preview-body')
            );

        targets.forEach(function (el) {
            if (!el) {
                return;
            }

            if (el.dataset.mathRendered === '1') {
                delete el.dataset.mathRendered;
            }

            renderMathInElement(el, renderOptions);
            el.dataset.mathRendered = '1';
        });

        return true;
    }

    function boot(attempt) {
        attempt = attempt || 0;

        if (renderQuestionMath()) {
            return;
        }

        if (attempt < 40) {
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
        document.querySelectorAll('.question-text-body[data-math-rendered="1"], .math-live-preview-body[data-math-rendered="1"]').forEach(function (el) {
            delete el.dataset.mathRendered;
        });
        boot(0);
    });

    window.renderQuestionMath = renderQuestionMath;
    window.questionMathRenderOptions = renderOptions;
})();
