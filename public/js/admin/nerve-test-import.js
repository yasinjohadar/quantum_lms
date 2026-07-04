(function () {
    'use strict';

    const moduleEl = document.getElementById('nerveTestImportModule');
    if (!moduleEl) {
        return;
    }

    const parseUrl = moduleEl.dataset.parseUrl;
    const importUrl = moduleEl.dataset.importUrl;
    const requireSubject = moduleEl.dataset.requireSubject === '1';
    const lockedSubjectId = moduleEl.dataset.lockedSubjectId || '';
    const lockedClassId = moduleEl.dataset.lockedClassId || '';

    const formatMd = document.getElementById('nerveTestFormatMd');
    const formatCsv = document.getElementById('nerveTestFormatCsv');
    const fileInput = document.getElementById('nerveTestFileInput');
    const uploadArea = document.getElementById('nerveTestUploadArea');
    const uploadContent = document.getElementById('nerveTestUploadContent');
    const fileInfo = document.getElementById('nerveTestFileInfo');
    const fileNameEl = document.getElementById('nerveTestFileName');
    const fileSizeEl = document.getElementById('nerveTestFileSize');
    const acceptHint = document.getElementById('nerveTestAcceptHint');
    const formatBadge = document.getElementById('nerveTestFormatBadge');
    const browseBtn = document.getElementById('nerveTestBrowseBtn');
    const replaceFileBtn = document.getElementById('nerveTestReplaceFile');
    const parseHint = document.getElementById('nerveTestParseHint');
    const stepFormat = document.getElementById('nerveTestStepFormat');
    const stepFile = document.getElementById('nerveTestStepFile');
    const stepParse = document.getElementById('nerveTestStepParse');
    const removeFileBtn = document.getElementById('nerveTestRemoveFile');
    const parseBtn = document.getElementById('nerveTestParseBtn');
    const parseError = document.getElementById('nerveTestParseError');
    const previewSection = document.getElementById('nerveTestPreviewSection');
    const previewTable = document.getElementById('nerveTestPreviewTable');
    const previewCount = document.getElementById('nerveTestPreviewCount');
    const importForm = document.getElementById('nerveTestImportForm');
    const importFormat = document.getElementById('nerveTestImportFormat');
    const importClassId = document.getElementById('nerveTestImportClassId');
    const importSubjectId = document.getElementById('nerveTestImportSubjectId');
    const importUnitId = document.getElementById('nerveTestImportUnitId');
    const importFileInput = document.getElementById('nerveTestImportFileInput');
    const importBtnLabel = document.getElementById('nerveTestImportBtnLabel');

    let selectedFile = null;
    let moduleReady = false;
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#nerveTestImportForm input[name="_token"]')?.value;

    function currentFormat() {
        return formatCsv?.checked ? 'csv' : 'md';
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

        stepFormat?.classList.toggle('is-active', !hasFile);
        stepFormat?.classList.toggle('is-done', hasFile || hasPreview);
        stepFile?.classList.toggle('is-active', !hasFile);
        stepFile?.classList.toggle('is-done', hasFile);
        stepParse?.classList.toggle('is-active', hasFile && !hasPreview);
        stepParse?.classList.toggle('is-done', hasPreview);
    }

    function syncParseHint() {
        if (!parseHint) {
            return;
        }
        if (!selectedFile) {
            parseHint.textContent = 'اختر ملفاً أولاً ثم اضغط تحليل';
            return;
        }
        parseHint.textContent = 'الملف جاهز — اضغط «تحليل الملف» للمعاينة';
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

    function renderPreview(questions) {
        const rows = questions.map(function (q) {
            return '<tr>' +
                '<td class="text-nowrap">' + escapeHtml(String(q.number)) + '</td>' +
                '<td>' + escapeHtml(q.title) + '</td>' +
                '<td class="small text-muted">' + escapeHtml(q.hint || '') + '</td>' +
                '<td class="small">' + escapeHtml(q.option_a) + ' / ' + escapeHtml(q.option_b) + '</td>' +
                '<td class="small text-success">' + escapeHtml(q.correct_answer) + '</td>' +
                '</tr>';
        }).join('');

        previewTable.innerHTML =
            '<table class="table table-sm table-striped mb-0">' +
            '<thead><tr>' +
            '<th>#</th><th>العنوان</th><th>تلميح</th><th>الخيارات</th><th>الإجابة</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table>';

        previewCount.textContent = String(questions.length);
        importBtnLabel.textContent = 'استيراد ' + questions.length + ' سؤال';
        previewSection.style.display = 'block';
        syncStepIndicators();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatMd?.addEventListener('change', function () { updateAccept(true); });
    formatCsv?.addEventListener('change', function () { updateAccept(true); });

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
        if (e.target.closest('#nerveTestRemoveFile')
            || e.target.closest('#nerveTestBrowseBtn')
            || e.target.closest('#nerveTestReplaceFile')) {
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
