(function () {
    'use strict';

    const moduleEl = document.getElementById('mathQuestionImportModule');
    if (!moduleEl) {
        return;
    }

    const parseUrl = moduleEl.dataset.parseUrl;
    const requireSubject = moduleEl.dataset.requireSubject === '1';
    const lockedSubjectId = moduleEl.dataset.lockedSubjectId || '';
    const lockedClassId = moduleEl.dataset.lockedClassId || '';

    const formatCsv = document.getElementById('mathImportFormatCsv');
    const formatMd = document.getElementById('mathImportFormatMd');
    const fileInput = document.getElementById('mathImportFileInput');
    const uploadArea = document.getElementById('mathImportUploadArea');
    const uploadContent = document.getElementById('mathImportUploadContent');
    const fileInfo = document.getElementById('mathImportFileInfo');
    const fileNameEl = document.getElementById('mathImportFileName');
    const fileSizeEl = document.getElementById('mathImportFileSize');
    const acceptHint = document.getElementById('mathImportAcceptHint');
    const formatBadge = document.getElementById('mathImportFormatBadge');
    const browseBtn = document.getElementById('mathImportBrowseBtn');
    const replaceFileBtn = document.getElementById('mathImportReplaceFile');
    const removeFileBtn = document.getElementById('mathImportRemoveFile');
    const parseHint = document.getElementById('mathImportParseHint');
    const stepFile = document.getElementById('mathImportStepFile');
    const stepParse = document.getElementById('mathImportStepParse');
    const stepDone = document.getElementById('mathImportStepDone');
    const parseBtn = document.getElementById('mathImportParseBtn');
    const parseError = document.getElementById('mathImportParseError');
    const previewSection = document.getElementById('mathImportPreviewSection');
    const previewTable = document.getElementById('mathImportPreviewTable');
    const previewCount = document.getElementById('mathImportPreviewCount');
    const importForm = document.getElementById('mathImportForm');
    const importFormat = document.getElementById('mathImportSubmitFormat');
    const importClassId = document.getElementById('mathImportClassId');
    const importSubjectId = document.getElementById('mathImportSubjectId');
    const importUnitId = document.getElementById('mathImportUnitId');
    const importFileInput = document.getElementById('mathImportSubmitFileInput');
    const importBtnLabel = document.getElementById('mathImportSubmitBtnLabel');

    let selectedFile = null;
    let moduleReady = false;
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#mathImportForm input[name="_token"]')?.value;

    function currentFormat() {
        return formatMd?.checked ? 'md' : 'csv';
    }

    function openFilePicker() {
        if (!fileInput) {
            return;
        }
        fileInput.value = '';
        fileInput.click();
    }

    function fileMatchesFormat(file, fmt) {
        const name = file.name.toLowerCase();
        if (fmt === 'csv') {
            return name.endsWith('.csv');
        }
        return name.endsWith('.md') || name.endsWith('.txt');
    }

    function syncStepIndicators() {
        const hasFile = !!selectedFile;
        const hasPreview = previewSection && previewSection.style.display !== 'none';

        stepFile?.classList.toggle('is-active', !hasFile);
        stepFile?.classList.toggle('is-done', hasFile || hasPreview);
        stepParse?.classList.toggle('is-active', hasFile && !hasPreview);
        stepParse?.classList.toggle('is-done', hasPreview);
        stepDone?.classList.toggle('is-active', hasPreview);
    }

    function syncParseHint() {
        if (!parseHint) {
            return;
        }
        if (!selectedFile) {
            parseHint.textContent = 'اختر ملفاً أولاً ثم اضغط تحليل';
            return;
        }
        parseHint.textContent = 'الملف جاهز — اضغط «تحليل الملف» لمعاينة المعادلات';
    }

    function updateAccept(openPicker) {
        const fmt = currentFormat();
        if (fileInput) {
            fileInput.accept = fmt === 'csv' ? '.csv,text/csv' : '.md,.txt,text/markdown';
        }
        if (acceptHint) {
            acceptHint.textContent = fmt === 'csv'
                ? 'الصيغة: .csv — الحد الأقصى 10 ميجابايت'
                : 'الصيغة: .md أو .txt — الحد الأقصى 10 ميجابايت';
        }
        if (formatBadge) {
            formatBadge.textContent = fmt === 'csv' ? 'CSV' : 'Markdown';
        }
        if (importFormat) {
            importFormat.value = fmt;
        }

        if (selectedFile && !fileMatchesFormat(selectedFile, fmt)) {
            clearFile(false);
        } else {
            clearPreview();
        }

        syncStepIndicators();
        syncParseHint();

        if (openPicker && moduleReady) {
            setTimeout(openFilePicker, 80);
        }
    }

    function formatBytes(bytes) {
        if (bytes < 1024) {
            return bytes + ' بايت';
        }
        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' ك.ب';
        }
        return (bytes / (1024 * 1024)).toFixed(1) + ' م.ب';
    }

    function showFile(file) {
        selectedFile = file;
        uploadContent.style.display = 'none';
        fileInfo.style.display = 'block';
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = formatBytes(file.size);
        uploadArea.classList.add('has-file');
        parseBtn.disabled = false;
        clearPreview();
        syncStepIndicators();
        syncParseHint();
    }

    function clearFile(resetInput) {
        if (resetInput !== false && fileInput) {
            fileInput.value = '';
        }
        selectedFile = null;
        uploadContent.style.display = 'block';
        fileInfo.style.display = 'none';
        uploadArea.classList.remove('has-file');
        parseBtn.disabled = true;
        clearPreview();
        syncStepIndicators();
        syncParseHint();
    }

    function clearPreview() {
        previewSection.style.display = 'none';
        previewTable.innerHTML = '';
        previewCount.textContent = '0';
        hideParseError();
        syncStepIndicators();
    }

    function hideParseError() {
        parseError.classList.add('d-none');
        parseError.textContent = '';
    }

    function showParseError(message) {
        parseError.textContent = message;
        parseError.classList.remove('d-none');
    }

    function syncCurriculumFromSidebar() {
        const mainClassId = document.getElementById('importClassId');
        const mainSubjectId = document.getElementById('importSubjectId');
        const mainUnitId = document.getElementById('importUnitId');

        if (lockedClassId && importClassId) {
            importClassId.value = lockedClassId;
        } else if (mainClassId && importClassId) {
            importClassId.value = mainClassId.value || '';
        }

        if (lockedSubjectId && importSubjectId) {
            importSubjectId.value = lockedSubjectId;
        } else if (mainSubjectId && importSubjectId) {
            importSubjectId.value = mainSubjectId.value || '';
        }

        if (mainUnitId && importUnitId) {
            importUnitId.value = mainUnitId.value || '';
        }
    }

    function resolveSubjectId() {
        if (lockedSubjectId) {
            return lockedSubjectId;
        }
        const hidden = document.getElementById('importSubjectId');
        if (hidden?.value) {
            return hidden.value;
        }
        const select = document.getElementById('subject_id');
        if (select?.tagName === 'SELECT' && select.value) {
            return select.value;
        }
        return importSubjectId?.value || '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function tryRenderMath(root, attempt) {
        attempt = attempt || 0;
        if (typeof window.renderQuestionMath === 'function' && window.renderQuestionMath(root)) {
            return;
        }
        if (attempt < 40) {
            setTimeout(function () {
                tryRenderMath(root, attempt + 1);
            }, 150);
        }
    }

    function renderPreview(questions) {
        const cards = questions.map(function (q) {
            const optionsHtml = (q.options || []).map(function (opt) {
                return '<div class="math-preview-option' + (opt.is_correct ? ' is-correct' : '') + '">' +
                    '<span class="option-letter">' + escapeHtml(opt.letter) + '.</span>' +
                    '<span class="question-text-body flex-fill">' + opt.html + '</span>' +
                    (opt.is_correct ? '<i class="bi bi-check-circle-fill text-success ms-1"></i>' : '') +
                    '</div>';
            }).join('');

            const hintHtml = q.hint_html
                ? '<div class="small text-muted mt-2"><i class="bi bi-lightbulb me-1"></i><strong>تلميح:</strong> <span class="question-text-body">' + q.hint_html + '</span></div>'
                : '';

            const explanationHtml = q.explanation_html
                ? '<div class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i><strong>التفسير:</strong> <span class="question-text-body">' + q.explanation_html + '</span></div>'
                : '';

            return '<div class="math-preview-card">' +
                '<div class="d-flex justify-content-between align-items-start mb-1">' +
                '<strong class="question-text-body">' + escapeHtml(String(q.number)) + '. ' + q.title_html + '</strong>' +
                '<span class="badge bg-primary-transparent text-nowrap ms-2">اختيار واحد</span>' +
                '</div>' +
                '<div class="mt-2">' + optionsHtml + '</div>' +
                hintHtml +
                explanationHtml +
                '</div>';
        }).join('');

        previewTable.innerHTML = cards;
        previewCount.textContent = String(questions.length);
        importBtnLabel.textContent = 'استيراد ' + questions.length + ' سؤال رياضيات';
        previewSection.style.display = 'block';
        syncStepIndicators();
        tryRenderMath(previewTable, 0);
    }

    formatCsv?.addEventListener('change', function () { updateAccept(true); });
    formatMd?.addEventListener('change', function () { updateAccept(true); });

    browseBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openFilePicker();
    });

    replaceFileBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openFilePicker();
    });

    uploadArea?.addEventListener('click', function (e) {
        if (e.target.closest('#mathImportRemoveFile')
            || e.target.closest('#mathImportBrowseBtn')
            || e.target.closest('#mathImportReplaceFile')) {
            return;
        }
        if (uploadArea.classList.contains('has-file')) {
            return;
        }
        openFilePicker();
    });

    uploadArea?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (!uploadArea.classList.contains('has-file')) {
                openFilePicker();
            }
        }
    });

    fileInput?.addEventListener('change', function () {
        if (fileInput.files?.length) {
            const file = fileInput.files[0];
            if (!fileMatchesFormat(file, currentFormat())) {
                showParseError(currentFormat() === 'csv'
                    ? 'الملف المختار ليس بصيغة CSV. اختر ملف .csv أو غيّر الصيغة إلى Markdown.'
                    : 'الملف المختار ليس Markdown. اختر ملف .md أو .txt أو غيّر الصيغة إلى CSV.');
                clearFile();
                return;
            }
            showFile(file);
        }
    });

    removeFileBtn?.addEventListener('click', function (e) {
        e.stopPropagation();
        clearFile();
    });

    uploadArea?.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea?.addEventListener('dragleave', function () {
        uploadArea.classList.remove('dragover');
    });

    uploadArea?.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer?.files?.[0];
        if (file) {
            if (!fileMatchesFormat(file, currentFormat())) {
                showParseError('صيغة الملف لا تطابق الصيغة المحددة.');
                return;
            }
            showFile(file);
        }
    });

    parseBtn?.addEventListener('click', async function () {
        if (!selectedFile) {
            return;
        }

        hideParseError();
        parseBtn.disabled = true;
        const originalHtml = parseBtn.innerHTML;
        parseBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري التحليل...';

        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('format', currentFormat());
        formData.append('_token', csrfToken);

        try {
            const response = await fetch(parseUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (!response.ok) {
                showParseError(data.message || 'تعذر تحليل الملف.');
                return;
            }

            if (!data.questions?.length) {
                showParseError('لم يُعثر على أسئلة في الملف.');
                return;
            }

            renderPreview(data.questions);
        } catch (err) {
            showParseError('خطأ في الاتصال بالخادم.');
        } finally {
            parseBtn.disabled = !selectedFile;
            parseBtn.innerHTML = originalHtml;
        }
    });

    importForm?.addEventListener('submit', function (e) {
        syncCurriculumFromSidebar();

        if (requireSubject && !resolveSubjectId()) {
            e.preventDefault();
            showParseError('اختر المادة من قسم «الربط بالمنهج» قبل الاستيراد.');
            return;
        }

        if (!selectedFile) {
            e.preventDefault();
            showParseError('اختر ملفاً أولاً.');
            return;
        }

        if (importFileInput) {
            const dt = new DataTransfer();
            dt.items.add(selectedFile);
            importFileInput.files = dt.files;
        }

        importFormat.value = currentFormat();
    });

    document.getElementById('class_id')?.addEventListener('change', syncCurriculumFromSidebar);
    document.getElementById('subject_id')?.addEventListener('change', syncCurriculumFromSidebar);
    document.getElementById('unit_id')?.addEventListener('change', syncCurriculumFromSidebar);

    if (typeof window.syncCurriculumToForm === 'function') {
        const originalSync = window.syncCurriculumToForm;
        window.syncCurriculumToForm = function () {
            originalSync();
            syncCurriculumFromSidebar();
        };
    }

    updateAccept(false);
    syncCurriculumFromSidebar();
    moduleReady = true;
})();
