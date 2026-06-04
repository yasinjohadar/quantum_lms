(function () {
    'use strict';

    const moduleEl = document.getElementById('questionPackImportModule');
    if (!moduleEl) {
        return;
    }

    const parseUrl = moduleEl.dataset.parseUrl;
    const requireSubject = moduleEl.dataset.requireSubject === '1';
    const lockedSubjectId = moduleEl.dataset.lockedSubjectId || '';
    const lockedClassId = moduleEl.dataset.lockedClassId || '';

    const targetSingle = document.getElementById('questionPackTargetSingle');
    const targetFill = document.getElementById('questionPackTargetFill');
    const formatMd = document.getElementById('questionPackFormatMd');
    const formatCsv = document.getElementById('questionPackFormatCsv');
    const fileInput = document.getElementById('questionPackFileInput');
    const uploadArea = document.getElementById('questionPackUploadArea');
    const uploadContent = document.getElementById('questionPackUploadContent');
    const fileInfo = document.getElementById('questionPackFileInfo');
    const fileNameEl = document.getElementById('questionPackFileName');
    const fileSizeEl = document.getElementById('questionPackFileSize');
    const acceptHint = document.getElementById('questionPackAcceptHint');
    const removeFileBtn = document.getElementById('questionPackRemoveFile');
    const parseBtn = document.getElementById('questionPackParseBtn');
    const parseError = document.getElementById('questionPackParseError');
    const previewSection = document.getElementById('questionPackPreviewSection');
    const previewTable = document.getElementById('questionPackPreviewTable');
    const previewCount = document.getElementById('questionPackPreviewCount');
    const importForm = document.getElementById('questionPackImportForm');
    const importFormat = document.getElementById('questionPackImportFormat');
    const importTargetType = document.getElementById('questionPackImportTargetType');
    const importClassId = document.getElementById('questionPackImportClassId');
    const importSubjectId = document.getElementById('questionPackImportSubjectId');
    const importUnitId = document.getElementById('questionPackImportUnitId');
    const importFileInput = document.getElementById('questionPackImportFileInput');
    const importBtnLabel = document.getElementById('questionPackImportBtnLabel');

    let selectedFile = null;
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#questionPackImportForm input[name="_token"]')?.value;

    function currentFormat() {
        return formatCsv?.checked ? 'csv' : 'md';
    }

    function currentTargetType() {
        return targetFill?.checked ? 'fill_blanks' : 'single_choice';
    }

    function isFillBlanksMode() {
        return currentTargetType() === 'fill_blanks';
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
        if (importTargetType) {
            importTargetType.value = currentTargetType();
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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function renderPreview(questions, targetType) {
        const fillMode = targetType === 'fill_blanks';
        const headers = fillMode
            ? ['#', 'العنوان', 'تلميح', 'إجابة الفراغ', 'النوع', 'الإجابة الصحيحة']
            : ['#', 'العنوان', 'تلميح', 'الخيارات', 'النوع', 'الإجابة'];

        const rows = questions.map(function (q) {
            const warn = (q.warnings || []).length
                ? '<br><small class="text-warning">' + escapeHtml(q.warnings.join(' ')) + '</small>'
                : '';
            const typeBadge = '<span class="badge bg-secondary-transparent">' + escapeHtml(q.type_label || '') + '</span>';
            const optionsCell = fillMode
                ? escapeHtml(q.blank_answer || '')
                : escapeHtml(q.options_summary || '');

            return '<tr>' +
                '<td class="text-nowrap">' + escapeHtml(String(q.number)) + '</td>' +
                '<td class="small">' + escapeHtml(q.title) + warn + '</td>' +
                '<td class="small text-muted">' + escapeHtml(q.hint || '') + '</td>' +
                '<td class="small">' + optionsCell + '</td>' +
                '<td>' + typeBadge + '</td>' +
                '<td class="small text-success">' + escapeHtml(q.correct_answer || '') + '</td>' +
                '</tr>';
        }).join('');

        previewTable.innerHTML =
            '<table class="table table-sm table-striped mb-0">' +
            '<thead><tr>' + headers.map(function (h) {
                return '<th>' + h + '</th>';
            }).join('') + '</tr></thead><tbody>' + rows + '</tbody></table>';

        previewCount.textContent = String(questions.length);
        importBtnLabel.textContent = 'استيراد ' + questions.length + ' سؤال';
        previewSection.style.display = 'block';
    }

    [targetSingle, targetFill, formatMd, formatCsv].forEach(function (el) {
        el?.addEventListener('change', updateAccept);
    });

    uploadArea?.addEventListener('click', function (e) {
        if (e.target.closest('#questionPackRemoveFile')) {
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
        formData.append('target_type', currentTargetType());
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

            renderPreview(data.questions, data.target_type || currentTargetType());
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
        importTargetType.value = currentTargetType();
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
