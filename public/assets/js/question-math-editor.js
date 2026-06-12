(function () {
    'use strict';

    var modalEl = null;
    var latexInput = null;
    var previewEl = null;
    var insertBtn = null;
    var previewTimer = null;
    var activeTarget = null;
    var previewUrl = null;

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function renderLocalPreview(latex) {
        if (!previewEl) {
            return;
        }
        var trimmed = (latex || '').trim();
        if (!trimmed) {
            previewEl.innerHTML = '<span class="text-muted small">اكتب LaTeX لمعاينة المعادلة</span>';
            return;
        }
        previewEl.textContent = '$' + trimmed + '$';
        if (typeof window.renderQuestionMath === 'function') {
            delete previewEl.dataset.mathRendered;
            window.renderQuestionMath(previewEl);
        }
    }

    function renderServerPreview(text) {
        if (!previewUrl || !previewEl) {
            renderLocalPreview(text);
            return;
        }
        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ text: '$' + (text || '').trim() + '$' }),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.html) {
                    previewEl.innerHTML = data.html;
                    if (typeof window.renderQuestionMath === 'function') {
                        delete previewEl.dataset.mathRendered;
                        window.renderQuestionMath(previewEl);
                    }
                }
            })
            .catch(function () {
                renderLocalPreview(text);
            });
    }

    function schedulePreview() {
        if (!latexInput) {
            return;
        }
        clearTimeout(previewTimer);
        previewTimer = setTimeout(function () {
            renderServerPreview(latexInput.value);
        }, 200);
    }

    function getDisplayMode() {
        var display = document.getElementById('math-editor-display');
        return display && display.checked ? 'display' : 'inline';
    }

    function buildDelimiter(latex) {
        var trimmed = (latex || '').trim();
        if (!trimmed) {
            return '';
        }
        if (getDisplayMode() === 'display') {
            return '$$' + trimmed + '$$';
        }
        return '$' + trimmed + '$';
    }

    function insertIntoTextarea(el, text) {
        if (!el) {
            return;
        }
        var start = el.selectionStart || 0;
        var end = el.selectionEnd || 0;
        var value = el.value || '';
        el.value = value.slice(0, start) + text + value.slice(end);
        el.focus();
        var pos = start + text.length;
        el.setSelectionRange(pos, pos);
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function insertIntoTinyMce(editorId, text) {
        if (typeof tinymce === 'undefined') {
            return false;
        }
        var editor = tinymce.get(editorId);
        if (!editor) {
            return false;
        }
        editor.focus();
        editor.insertContent(text);
        return true;
    }

    function openMathEditor(target) {
        activeTarget = target || null;
        if (latexInput) {
            latexInput.value = '';
        }
        schedulePreview();
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function handleInsert() {
        var snippet = buildDelimiter(latexInput ? latexInput.value : '');
        if (!snippet) {
            return;
        }

        if (activeTarget) {
            if (activeTarget.type === 'tinymce' && activeTarget.editorId) {
                insertIntoTinyMce(activeTarget.editorId, snippet);
            } else if (activeTarget.element) {
                insertIntoTextarea(activeTarget.element, snippet);
            }
        }

        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }

        document.dispatchEvent(new CustomEvent('question-math-inserted', { detail: { snippet: snippet } }));
    }

    function initLivePreview() {
        var previewBox = document.getElementById('question-live-preview');
        var titleEditor = document.getElementById('question-title-editor');
        var explanationEditor = document.getElementById('question-explanation-editor');
        if (!previewBox || !previewUrl) {
            return;
        }

        var timer = null;

        function collectRawText() {
            var parts = [];
            if (typeof tinymce !== 'undefined') {
                var titleTm = tinymce.get('question-title-editor');
                var explTm = tinymce.get('question-explanation-editor');
                if (titleTm) {
                    parts.push(titleTm.getContent({ format: 'text' }));
                } else if (titleEditor) {
                    parts.push(titleEditor.value);
                }
                if (explTm) {
                    parts.push(explTm.getContent({ format: 'text' }));
                } else if (explanationEditor) {
                    parts.push(explanationEditor.value);
                }
            } else {
                if (titleEditor) {
                    parts.push(titleEditor.value);
                }
                if (explanationEditor) {
                    parts.push(explanationEditor.value);
                }
            }

            document.querySelectorAll('#optionsContainer textarea[name*="[content]"]').forEach(function (el) {
                parts.push(el.value);
            });

            return parts.filter(Boolean).join('\n\n');
        }

        function refreshLivePreview() {
            var text = collectRawText();
            if (!text.trim()) {
                previewBox.innerHTML = '<span class="text-muted">ستظهر المعاينة هنا أثناء الكتابة...</span>';
                return;
            }
            fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ text: text }),
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    previewBox.innerHTML = data.html || '';
                    if (typeof window.renderQuestionMath === 'function') {
                        delete previewBox.dataset.mathRendered;
                        window.renderQuestionMath(previewBox);
                    }
                })
                .catch(function () {
                    previewBox.textContent = text;
                });
        }

        function scheduleLivePreview() {
            clearTimeout(timer);
            timer = setTimeout(refreshLivePreview, 300);
        }

        document.addEventListener('question-math-inserted', scheduleLivePreview);
        if (titleEditor) {
            titleEditor.addEventListener('input', scheduleLivePreview);
        }
        if (explanationEditor) {
            explanationEditor.addEventListener('input', scheduleLivePreview);
        }
        document.addEventListener('input', function (e) {
            if (e.target && e.target.matches('#optionsContainer textarea, #optionsContainer input[type="text"]')) {
                scheduleLivePreview();
            }
        });

        if (typeof tinymce !== 'undefined') {
            var attachTiny = function () {
                ['question-title-editor', 'question-explanation-editor'].forEach(function (id) {
                    var ed = tinymce.get(id);
                    if (ed && !ed._mathPreviewBound) {
                        ed.on('keyup change undo redo SetContent', scheduleLivePreview);
                        ed._mathPreviewBound = true;
                    }
                });
            };
            setTimeout(attachTiny, 800);
            setTimeout(attachTiny, 2000);
        }

        scheduleLivePreview();
    }

    function bindSymbolButtons() {
        document.querySelectorAll('.math-symbol-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var symbol = btn.getAttribute('data-symbol') || '';
                if (!latexInput) {
                    return;
                }
                var start = latexInput.selectionStart || latexInput.value.length;
                var end = latexInput.selectionEnd || latexInput.value.length;
                latexInput.value = latexInput.value.slice(0, start) + symbol + latexInput.value.slice(end);
                latexInput.focus();
                schedulePreview();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        modalEl = document.getElementById('questionMathEditorModal');
        latexInput = document.getElementById('math-editor-latex');
        previewEl = document.getElementById('math-editor-preview');
        insertBtn = document.getElementById('math-editor-insert-btn');
        previewUrl = document.body.getAttribute('data-math-preview-url') || null;

        if (latexInput) {
            latexInput.addEventListener('input', schedulePreview);
        }
        if (insertBtn) {
            insertBtn.addEventListener('click', handleInsert);
        }

        bindSymbolButtons();
        initLivePreview();
    });

    window.openQuestionMathEditor = openMathEditor;
})();
