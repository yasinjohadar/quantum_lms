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
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#nerveTestImportForm input[name="_token"]')?.value;

    function currentFormat() {
        return formatCsv?.checked ? 'csv' : 'md';
    }

    function updateAccept() {
        const fmt = currentFormat();
        if (fileInput) {
            fileInput.accept = fmt === 'csv' ? '.csv' : '.md,.txt';
        }
        if (acceptHint) {
            acceptHint.textContent = fmt === 'csv'
                ? 'الصيغة: .csv — الحد الأقصى 10 ميجابايت'
                : 'الصيغة: .md — الحد الأقصى 10 ميجابايت';
        }
        if (importFormat) {
            importFormat.value = fmt;
        }
        clearPreview();
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
    }

    function clearFile() {
        selectedFile = null;
        fileInput.value = '';
        uploadContent.style.display = 'block';
        fileInfo.style.display = 'none';
        uploadArea.classList.remove('has-file');
        parseBtn.disabled = true;
        clearPreview();
    }

    function clearPreview() {
        previewSection.style.display = 'none';
        previewTable.innerHTML = '';
        previewCount.textContent = '0';
        hideParseError();
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
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatMd?.addEventListener('change', updateAccept);
    formatCsv?.addEventListener('change', updateAccept);

    uploadArea?.addEventListener('click', function (e) {
        if (e.target.closest('#nerveTestRemoveFile')) {
            return;
        }
        fileInput?.click();
    });

    fileInput?.addEventListener('change', function () {
        if (fileInput.files?.length) {
            showFile(fileInput.files[0]);
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

    updateAccept();
    syncCurriculumFromSidebar();
})();
