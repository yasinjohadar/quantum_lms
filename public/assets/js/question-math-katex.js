(function () {
    'use strict';

    var renderOptions = {
        delimiters: [
            { left: '$$', right: '$$', display: true },
            { left: '\\[', right: '\\]', display: true },
            { left: '\\(', right: '\\)', display: false },
        ],
        throwOnError: false,
        strict: false,
        ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
        ignoredClasses: ['question-inline-code', 'question-code-block', 'no-math'],
    };

    function renderQuestionMath(root) {
        if (typeof renderMathInElement !== 'function') {
            return false;
        }

        var targets = root
            ? [root]
            : Array.prototype.slice.call(document.querySelectorAll('.question-text-body'));

        targets.forEach(function (el) {
            if (!el || el.dataset.mathRendered === '1') {
                return;
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            boot(0);
        });
    } else {
        boot(0);
    }

    window.renderQuestionMath = renderQuestionMath;
})();
