(function () {
    'use strict';

    const moduleEl = document.getElementById('ileFileImportModule');
    if (!moduleEl) return;

    const parseUrl = moduleEl.dataset.parseUrl;
    const applyUrl = moduleEl.dataset.applyUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    const fileInput = document.getElementById('ileImportFileInput');
    const uploadArea = document.getElementById('ileImportUploadArea');
    const uploadContent = document.getElementById('ileImportUploadContent');
    const fileInfo = document.getElementById('ileImportFileInfo');
    const fileNameEl = document.getElementById('ileImportFileName');
    const fileSizeEl = document.getElementById('ileImportFileSize');
    const acceptHint = document.getElementById('ileImportAcceptHint');
    const formatBadge = document.getElementById('ileImportFormatBadge');
    const browseBtn = document.getElementById('ileImportBrowseBtn');
    const replaceBtn = document.getElementById('ileImportReplaceFile');
    const removeBtn = document.getElementById('ileImportRemoveFile');
    const parseBtn = document.getElementById('ileImportParseBtn');
    const parseHint = document.getElementById('ileImportParseHint');
    const parseError = document.getElementById('ileImportParseError');
    const previewSection = document.getElementById('ileImportPreviewSection');
    const previewTable = document.getElementById('ileImportPreviewTable');
    const previewCount = document.getElementById('ileImportPreviewCount');
    const warnBadge = document.getElementById('ileImportWarnBadge');
    const applyBtn = document.getElementById('ileImportApplyBtn');
    const mergeMode = document.getElementById('ileImportMergeMode');
    const stepFile = document.getElementById('ileImportStepFile');
    const stepParse = document.getElementById('ileImportStepParse');
    const stepDone = document.getElementById('ileImportStepDone');

    let selectedFile = null;
    let parsedQuestions = [];

    function currentFormat() {
        const checked = document.querySelector('input[name="ileImportFormat"]:checked');
        return checked ? checked.value : 'csv';
    }

    function syncSteps() {
        const hasFile = !!selectedFile;
        const hasPreview = previewSection && previewSection.style.display !== 'none';
        stepFile?.classList.toggle('is-active', !hasFile);
        stepFile?.classList.toggle('is-done', hasFile || hasPreview);
        stepParse?.classList.toggle('is-active', hasFile && !hasPreview);
        stepParse?.classList.toggle('is-done', hasPreview);
        stepDone?.classList.toggle('is-active', hasPreview);
    }

    function updateAccept() {
        const fmt = currentFormat();
        const map = {
            csv: { accept: '.csv,text/csv', hint: 'الصيغة: .csv — الحد الأقصى 10 ميجابايت', badge: 'CSV' },
            md: { accept: '.md,.txt,text/markdown', hint: 'الصيغة: .md أو .txt — الحد الأقصى 10 ميجابايت', badge: 'MD' },
            json: { accept: '.json,application/json', hint: 'الصيغة: .json — الحد الأقصى 10 ميجابايت', badge: 'JSON' },
        };
        const conf = map[fmt] || map.csv;
        if (fileInput) fileInput.accept = conf.accept;
        if (acceptHint) acceptHint.textContent = conf.hint;
        if (formatBadge) formatBadge.textContent = conf.badge;
        clearFile(false);
    }

    function clearFile(resetFormatRadios) {
        selectedFile = null;
        parsedQuestions = [];
        if (fileInput) fileInput.value = '';
        uploadArea?.classList.remove('has-file');
        if (uploadContent) uploadContent.style.display = '';
        if (fileInfo) fileInfo.style.display = 'none';
        if (previewSection) previewSection.style.display = 'none';
        if (previewTable) previewTable.innerHTML = '';
        if (parseBtn) parseBtn.disabled = true;
        hideError();
        if (parseHint) parseHint.textContent = 'اختر ملفاً أولاً ثم اضغط تحليل';
        syncSteps();
    }

    function setFile(file) {
        if (!file) return;
        const fmt = currentFormat();
        const name = file.name.toLowerCase();
        const ok = (fmt === 'csv' && name.endsWith('.csv'))
            || (fmt === 'md' && (name.endsWith('.md') || name.endsWith('.txt')))
            || (fmt === 'json' && name.endsWith('.json'));
        if (!ok) {
            showError('امتداد الملف لا يطابق الصيغة المختارة.');
            return;
        }
        selectedFile = file;
        parsedQuestions = [];
        uploadArea?.classList.add('has-file');
        if (uploadContent) uploadContent.style.display = 'none';
        if (fileInfo) fileInfo.style.display = '';
        if (fileNameEl) fileNameEl.textContent = file.name;
        if (fileSizeEl) fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
        if (parseBtn) parseBtn.disabled = false;
        if (previewSection) previewSection.style.display = 'none';
        if (parseHint) parseHint.textContent = 'الملف جاهز — اضغط «تحليل الملف» للمعاينة';
        hideError();
        syncSteps();
    }

    function showError(msg) {
        if (!parseError) return;
        parseError.textContent = msg;
        parseError.classList.remove('d-none');
    }

    function hideError() {
        parseError?.classList.add('d-none');
    }

    function tryRenderMath(root, attempt) {
        attempt = attempt || 0;
        if (typeof window.renderQuestionMath === 'function' && window.renderQuestionMath(root)) {
            return;
        }
        if (attempt < 40) {
            setTimeout(function () { tryRenderMath(root, attempt + 1); }, 150);
        }
    }

    function renderPreviews(previews) {
        if (!previewTable) return;
        previewTable.innerHTML = '';
        previews.forEach((p) => {
            const card = document.createElement('div');
            card.className = 'math-preview-card' + (p.has_warning ? ' has-math-warning' : '');
            const opts = (p.options || []).map((o) => {
                return `<div class="math-preview-option${o.is_correct ? ' is-correct' : ''}">
                    <span class="option-letter">${o.letter || o.id || ''}</span>
                    <div class="question-text-body flex-fill">${o.html || ''}</div>
                </div>`;
            }).join('');
            card.innerHTML = `
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <span class="badge" style="background:${p.type_color || '#64748b'}">${p.type_name || p.type}</span>
                        <strong class="ms-1">#${p.number}</strong>
                        <span class="text-muted small ms-1">${p.difficulty || ''} · ${p.points || 1} نقطة</span>
                    </div>
                    ${p.has_warning ? '<span class="badge bg-warning text-dark">مراجعة معادلات</span>' : ''}
                </div>
                <div class="mb-2 question-text-body">${p.stem_html || ''}</div>
                ${p.meta_line ? `<div class="small text-muted mb-2">${p.meta_line}</div>` : ''}
                ${opts}
                ${p.hint_html ? `<div class="small mt-2"><strong>تلميح:</strong> <span class="question-text-body">${p.hint_html}</span></div>` : ''}
                ${p.explanation_html ? `<div class="small mt-1"><strong>شرح:</strong> <span class="question-text-body">${p.explanation_html}</span></div>` : ''}
            `;
            previewTable.appendChild(card);
        });
        tryRenderMath(previewTable, 0);
    }

    async function parseFile() {
        if (!selectedFile) return;
        hideError();
        parseBtn.disabled = true;
        parseBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري التحليل…';
        try {
            const fd = new FormData();
            fd.append('file', selectedFile);
            fd.append('format', currentFormat());
            const res = await fetch(parseUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: fd,
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'فشل التحليل');
            }
            parsedQuestions = data.questions || [];
            if (previewCount) previewCount.textContent = String(data.count || parsedQuestions.length);
            if (warnBadge) {
                if (data.suspicious_count > 0) {
                    warnBadge.style.display = '';
                    warnBadge.textContent = data.suspicious_count + ' سؤال قد يحتاج مراجعة معادلات';
                } else {
                    warnBadge.style.display = 'none';
                }
            }
            renderPreviews(data.previews || []);
            if (previewSection) previewSection.style.display = '';
            syncSteps();
        } catch (e) {
            showError(e.message || String(e));
            parsedQuestions = [];
            if (previewSection) previewSection.style.display = 'none';
        } finally {
            parseBtn.disabled = !selectedFile;
            parseBtn.innerHTML = '<i class="bi bi-search me-1"></i> تحليل الملف والمعاينة';
        }
    }

    async function applyImport() {
        if (!parsedQuestions.length) return;
        applyBtn.disabled = true;
        const label = document.getElementById('ileImportApplyLabel');
        if (label) label.textContent = 'جاري الاستيراد…';
        try {
            // Prefer Alpine handler on the editor (keeps local schema in sync)
            const detail = {
                questions: parsedQuestions,
                mode: mergeMode?.value || 'append',
                applyUrl,
                csrf,
            };
            const alpineRoot = document.querySelector('.ile-edit-page');
            if (alpineRoot && alpineRoot._x_dataStack && alpineRoot._x_dataStack[0]?.applyImportedQuestions) {
                await alpineRoot._x_dataStack[0].applyImportedQuestions(detail);
            } else {
                window.dispatchEvent(new CustomEvent('ile-import-apply', { detail }));
                // Fallback direct API
                const res = await fetch(applyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        questions: parsedQuestions,
                        mode: mergeMode?.value || 'append',
                        persist: true,
                    }),
                });
                const data = await res.json();
                if (!data.ok) throw new Error((data.errors || []).join(' ') || data.message || 'فشل الاستيراد');
                alert('تم استيراد ' + (data.count || parsedQuestions.length) + ' سؤال. حدّث الصفحة إن لم تظهر مباشرة.');
                location.reload();
            }
            clearFile();
        } catch (e) {
            showError(e.message || String(e));
        } finally {
            applyBtn.disabled = false;
            if (label) label.textContent = 'استيراد الأسئلة إلى التجربة';
        }
    }

    document.querySelectorAll('input[name="ileImportFormat"]').forEach((el) => {
        el.addEventListener('change', updateAccept);
    });
    browseBtn?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); fileInput?.click(); });
    replaceBtn?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); fileInput?.click(); });
    removeBtn?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); clearFile(); });
    uploadArea?.addEventListener('click', () => {
        if (!selectedFile) fileInput?.click();
    });
    uploadArea?.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea?.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea?.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const f = e.dataTransfer?.files?.[0];
        if (f) setFile(f);
    });
    fileInput?.addEventListener('change', () => {
        const f = fileInput.files?.[0];
        if (f) setFile(f);
    });
    parseBtn?.addEventListener('click', parseFile);
    applyBtn?.addEventListener('click', applyImport);

    updateAccept();
    syncSteps();
})();
