(function () {
    'use strict';

    const REQUIRED_FIELDS = [
        { key: 'type', label: 'نوع السؤال', icon: 'bi-tag' },
        { key: 'title', label: 'عنوان السؤال', icon: 'bi-heading' },
        { key: 'difficulty', label: 'الصعوبة', icon: 'bi-bar-chart' },
        { key: 'points', label: 'الدرجة', icon: 'bi-star' },
    ];

    const OPTIONAL_FIELDS = [
        { key: 'content', label: 'محتوى السؤال', icon: 'bi-file-text' },
        { key: 'explanation', label: 'الشرح', icon: 'bi-lightbulb' },
        { key: 'category', label: 'التصنيف', icon: 'bi-folder' },
        { key: 'option1', label: 'الخيار الأول', icon: 'bi-list-ul' },
        { key: 'option1_correct', label: 'الخيار الأول صحيح', icon: 'bi-check-circle' },
        { key: 'option2', label: 'الخيار الثاني', icon: 'bi-list-ul' },
        { key: 'option2_correct', label: 'الخيار الثاني صحيح', icon: 'bi-check-circle' },
        { key: 'option3', label: 'الخيار الثالث', icon: 'bi-list-ul' },
        { key: 'option3_correct', label: 'الخيار الثالث صحيح', icon: 'bi-check-circle' },
        { key: 'option4', label: 'الخيار الرابع', icon: 'bi-list-ul' },
        { key: 'option4_correct', label: 'الخيار الرابع صحيح', icon: 'bi-check-circle' },
        { key: 'correct_answer', label: 'الإجابة الصحيحة (للأسئلة الرقمية)', icon: 'bi-123' },
        { key: 'units', label: 'الوحدات', icon: 'bi-book' },
    ];

    function formatFileSize(bytes) {
        if (bytes === 0) {
            return '0 Bytes';
        }
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    function initExcelQuestionsImport(root) {
        if (!root || root.dataset.excelBound === '1') {
            return;
        }
        root.dataset.excelBound = '1';

        const curriculumSync = root.dataset.curriculumSync === '1';

        let uploadedFile = null;
        let fileData = [];
        let fileColumns = [];
        let columnMapping = {};

        const uploadArea = root.querySelector('.excel-upload-area');
        const fileInput = root.querySelector('.excel-file-input');
        const uploadContent = root.querySelector('.excel-upload-content');
        const fileInfo = root.querySelector('.excel-file-info');

        if (!uploadArea || !fileInput) {
            return;
        }

        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                processFile(e.dataTransfer.files[0]);
            }
        });
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                processFile(e.target.files[0]);
            }
        });

        root.querySelector('.excel-next-preview-btn')?.addEventListener('click', () => {
            const allRequiredMapped = REQUIRED_FIELDS.every((field) => columnMapping[field.key]);
            if (allRequiredMapped) {
                showPreview(root, fileData, columnMapping);
                showStep(root, 3);
            } else {
                alert('الرجاء تحديد جميع الحقول المطلوبة');
            }
        });

        root.querySelector('.excel-next-import-btn')?.addEventListener('click', () => {
            prepareImport(root, uploadedFile, fileData, columnMapping, curriculumSync);
            showStep(root, 4);
        });

        root.querySelector('.excel-back-upload-btn')?.addEventListener('click', () => showStep(root, 1));
        root.querySelector('.excel-back-mapping-btn')?.addEventListener('click', () => showStep(root, 2));
        root.querySelector('.excel-back-preview-btn')?.addEventListener('click', () => showStep(root, 3));

        root.querySelector('.excel-import-form')?.addEventListener('submit', (e) => {
            if (!uploadedFile) {
                e.preventDefault();
                alert('الرجاء رفع ملف أولاً');
                return;
            }
            if (curriculumSync) {
                syncCurriculumToForm(root);
            }
        });

        root.addEventListener('change', (e) => {
            if (!e.target.classList.contains('field-mapping')) {
                return;
            }
            const field = e.target.dataset.field;
            const column = e.target.value;
            if (column) {
                columnMapping[field] = column;
            } else {
                delete columnMapping[field];
            }
            updateNextPreviewBtn(root, columnMapping);
            if (REQUIRED_FIELDS.every((f) => columnMapping[f.key])) {
                showPreview(root, fileData, columnMapping);
            }
        });

        function bindRemoveFile() {
            fileInfo.querySelector('.excel-remove-file')?.addEventListener('click', (e) => {
                e.stopPropagation();
                resetUpload();
            });
        }

        function resetUpload() {
            uploadedFile = null;
            fileData = [];
            fileColumns = [];
            columnMapping = {};
            uploadArea.classList.remove('has-file');
            uploadContent.style.display = 'block';
            fileInfo.style.display = 'none';
            fileInfo.innerHTML = '';
            fileInput.value = '';
            showStep(root, 1);
        }

        function renderFileInfo() {
            fileInfo.innerHTML = `
                <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
                <h5 class="mb-2">${uploadedFile.name}</h5>
                <p class="text-muted mb-0">${formatFileSize(uploadedFile.size)}</p>
                <p class="text-success small mt-2"><i class="bi bi-check-circle me-1"></i> تم قراءة ${fileData.length} صف</p>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2 excel-remove-file">
                    <i class="bi bi-x-circle me-1"></i> إزالة الملف
                </button>
            `;
            bindRemoveFile();
        }

        function processFile(file) {
            if (!file.name.match(/\.(xlsx|xls|csv)$/i)) {
                alert('الرجاء اختيار ملف Excel أو CSV');
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('حجم الملف كبير جداً. الحد الأقصى 10 ميجابايت');
                return;
            }

            uploadedFile = file;
            uploadArea.classList.add('has-file');
            uploadContent.style.display = 'none';
            fileInfo.style.display = 'block';
            fileInfo.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">جاري المعالجة...</span>
                    </div>
                    <p class="text-muted mb-0">جاري قراءة الملف...</p>
                </div>
            `;

            const reader = new FileReader();
            reader.onload = function (ev) {
                try {
                    if (file.name.endsWith('.csv')) {
                        parseCSV(ev.target.result);
                    } else {
                        parseExcel(file);
                    }
                } catch (error) {
                    alert('حدث خطأ أثناء قراءة الملف: ' + error.message);
                    resetUpload();
                }
            };
            reader.onerror = function () {
                alert('حدث خطأ أثناء قراءة الملف');
                resetUpload();
            };

            if (file.name.endsWith('.csv')) {
                reader.readAsText(file);
            } else {
                reader.readAsArrayBuffer(file);
            }
        }

        function parseCSV(text) {
            if (typeof Papa === 'undefined') {
                alert('مكتبة CSV غير محمّلة');
                resetUpload();
                return;
            }
            Papa.parse(text, {
                header: true,
                skipEmptyLines: true,
                complete: function (results) {
                    if (results.data.length === 0) {
                        alert('الملف فارغ أو لا يحتوي على بيانات');
                        resetUpload();
                        return;
                    }
                    fileData = results.data;
                    fileColumns = Object.keys(results.data[0]);
                    renderFileInfo();
                    setupColumnMapping(root, fileColumns, columnMapping);
                    showStep(root, 2);
                },
                error: function (error) {
                    alert('حدث خطأ أثناء قراءة الملف: ' + error.message);
                    resetUpload();
                },
            });
        }

        function parseExcel(file) {
            if (typeof XLSX === 'undefined') {
                alert('مكتبة Excel غير محمّلة');
                resetUpload();
                return;
            }
            const reader = new FileReader();
            reader.onload = function (ev) {
                try {
                    const data = new Uint8Array(ev.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet);
                    if (jsonData.length === 0) {
                        alert('الملف فارغ أو لا يحتوي على بيانات');
                        resetUpload();
                        return;
                    }
                    fileData = jsonData;
                    fileColumns = Object.keys(jsonData[0]);
                    renderFileInfo();
                    setupColumnMapping(root, fileColumns, columnMapping);
                    showStep(root, 2);
                } catch (error) {
                    alert('حدث خطأ أثناء قراءة الملف: ' + error.message);
                    resetUpload();
                }
            };
            reader.onerror = function () {
                alert('حدث خطأ أثناء قراءة الملف');
                resetUpload();
            };
            reader.readAsArrayBuffer(file);
        }
    }

    function setupColumnMapping(root, fileColumns, columnMapping) {
        const autoMapping = {};
        const columnLower = fileColumns.map((c) => c.toLowerCase().trim());

        REQUIRED_FIELDS.forEach((field) => {
            const fieldKey = field.key.toLowerCase();
            const fieldLabel = field.label.toLowerCase();
            const match = fileColumns.find((col, idx) => {
                const colLower = columnLower[idx];
                return (
                    colLower === fieldKey ||
                    colLower === fieldLabel ||
                    colLower.includes(fieldKey) ||
                    colLower.includes(fieldLabel)
                );
            });
            if (match) {
                autoMapping[field.key] = match;
            }
        });

        const requiredMappings = root.querySelector('.excel-required-mappings');
        const optionalMappings = root.querySelector('.excel-optional-mappings');
        if (!requiredMappings || !optionalMappings) {
            return;
        }

        requiredMappings.innerHTML = '';
        REQUIRED_FIELDS.forEach((field) => {
            requiredMappings.appendChild(createMappingItem(fileColumns, field, true, autoMapping[field.key]));
        });

        optionalMappings.innerHTML = '';
        OPTIONAL_FIELDS.forEach((field) => {
            optionalMappings.appendChild(createMappingItem(fileColumns, field, false, autoMapping[field.key]));
        });

        Object.keys(autoMapping).forEach((key) => {
            columnMapping[key] = autoMapping[key];
        });

        updateNextPreviewBtn(root, columnMapping);
    }

    function createMappingItem(fileColumns, field, required, autoSelected) {
        const div = document.createElement('div');
        div.className = 'mapping-item';
        div.innerHTML = `
            <i class="bi ${field.icon} text-primary"></i>
            <div class="flex-grow-1">
                <label class="form-label mb-1 small fw-semibold">
                    ${field.label}
                    ${required ? '<span class="required-field">*</span>' : ''}
                </label>
                <select class="form-select form-select-sm field-mapping" data-field="${field.key}" ${required ? 'required' : ''}>
                    <option value="">-- اختر العمود --</option>
                    ${fileColumns
                        .map(
                            (col) =>
                                `<option value="${col}" ${col === autoSelected ? 'selected' : ''}>${col}</option>`
                        )
                        .join('')}
                </select>
            </div>
        `;
        return div;
    }

    function updateNextPreviewBtn(root, columnMapping) {
        const nextBtn = root.querySelector('.excel-next-preview-btn');
        if (nextBtn) {
            nextBtn.disabled = !REQUIRED_FIELDS.every((field) => columnMapping[field.key]);
        }
    }

    function showPreview(root, fileData, columnMapping) {
        const previewHeader = root.querySelector('.excel-preview-header');
        const previewBody = root.querySelector('.excel-preview-body');
        if (!previewHeader || !previewBody) {
            return;
        }

        previewHeader.innerHTML = '';
        previewBody.innerHTML = '';

        const mappedFields = [...REQUIRED_FIELDS, ...OPTIONAL_FIELDS].filter((f) => columnMapping[f.key]);
        mappedFields.forEach((field) => {
            const th = document.createElement('th');
            th.textContent = field.label;
            previewHeader.appendChild(th);
        });

        fileData.slice(0, 10).forEach((row) => {
            const tr = document.createElement('tr');
            mappedFields.forEach((field) => {
                const td = document.createElement('td');
                const column = columnMapping[field.key];
                td.textContent = row[column] || '-';
                tr.appendChild(td);
            });
            previewBody.appendChild(tr);
        });

        const countEl = root.querySelector('.excel-preview-count');
        if (countEl) {
            countEl.textContent = Math.min(10, fileData.length);
        }
    }

    function showStep(root, stepNumber) {
        for (let i = 1; i <= 4; i++) {
            const stepItem = root.querySelector(`.excel-step-item[data-step="${i}"]`);
            if (!stepItem) {
                continue;
            }
            const link = stepItem.querySelector('.nav-link');
            stepItem.classList.remove('active', 'completed');
            link?.classList.remove('active');
            link?.removeAttribute('aria-current');
            if (i < stepNumber) {
                stepItem.classList.add('completed');
            } else if (i === stepNumber) {
                stepItem.classList.add('active');
                link?.classList.add('active');
                link?.setAttribute('aria-current', 'step');
            }
        }

        root.querySelector('.excel-upload-step').style.display = stepNumber === 1 ? 'block' : 'none';
        root.querySelector('.excel-mapping-step').style.display = stepNumber === 2 ? 'block' : 'none';
        root.querySelector('.excel-preview-step').style.display = stepNumber === 3 ? 'block' : 'none';
        root.querySelector('.excel-import-step').style.display = stepNumber === 4 ? 'block' : 'none';
    }

    function prepareImport(root, uploadedFile, fileData, columnMapping, curriculumSync) {
        root.querySelector('.excel-final-file-name').textContent = uploadedFile.name;
        root.querySelector('.excel-final-file-size').textContent = formatFileSize(uploadedFile.size);
        root.querySelector('.excel-final-row-count').textContent = fileData.length;
        root.querySelector('.excel-column-mapping-input').value = JSON.stringify(columnMapping);

        if (curriculumSync) {
            syncCurriculumToForm(root);
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(uploadedFile);
        root.querySelector('.excel-hidden-file-input').files = dataTransfer.files;
    }

    function syncCurriculumToForm(root) {
        const classSelect = document.getElementById('class_id');
        const subjectSelect = document.getElementById('subject_id');
        const unitSelect = document.getElementById('unit_id');
        const importClassId = root.querySelector('.excel-import-class-id');
        const importSubjectId = root.querySelector('.excel-import-subject-id');
        const importUnitId = root.querySelector('.excel-import-unit-id');

        if (classSelect && classSelect.tagName === 'SELECT' && importClassId) {
            importClassId.value = classSelect.value || '';
        } else {
            const lockedClass = document.getElementById('locked_class_id');
            if (lockedClass && importClassId) {
                importClassId.value = lockedClass.value || '';
            }
        }

        if (subjectSelect && subjectSelect.tagName === 'SELECT' && importSubjectId) {
            importSubjectId.value = subjectSelect.value || '';
        }

        if (unitSelect && importUnitId) {
            importUnitId.value = unitSelect.value || '';
        }
    }

    window.initExcelQuestionsImport = function (root) {
        if (typeof root === 'string') {
            root = document.querySelector(root);
        }
        if (root) {
            initExcelQuestionsImport(root);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.excel-import-wizard').forEach((root) => {
            initExcelQuestionsImport(root);
        });
    });
})();
