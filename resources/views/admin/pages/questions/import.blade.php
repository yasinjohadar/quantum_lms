@extends('admin.layouts.master')

@section('page-title')
    استيراد الأسئلة
@stop

@push('styles')
<style>
    .questions-import .upload-area {
        border: 2px dashed var(--default-border);
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: var(--default-background);
        color: var(--default-text-color);
        cursor: pointer;
    }
    .questions-import .upload-area h5 {
        color: var(--default-text-color);
    }
    .questions-import .upload-area:hover,
    .questions-import .upload-area.dragover {
        border-color: rgb(var(--primary-rgb));
        background: var(--primary005);
    }
    .questions-import .upload-area.has-file {
        border-color: rgb(var(--success-rgb));
        background: rgba(var(--success-rgb), 0.08);
    }
    .questions-import .preview-table {
        max-height: 400px;
        overflow-y: auto;
    }
    .questions-import .column-mapping {
        border: 1px solid var(--default-border);
        border-radius: 8px;
        padding: 16px;
        background: var(--default-background);
    }
    .questions-import .mapping-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--custom-white);
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid var(--default-border);
    }
    .questions-import .mapping-item:hover {
        border-color: rgb(var(--primary-rgb));
        box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.12);
    }
    .questions-import .file-info {
        background: linear-gradient(135deg, rgb(var(--primary-rgb)) 0%, rgba(var(--primary-rgb), 0.75) 100%);
        color: #fff;
        border-radius: 8px;
        padding: 16px;
    }
    .questions-import .required-field {
        color: rgb(var(--danger-rgb));
    }
    .questions-import .import-steps-bar {
        width: 100%;
        border-bottom: 0;
    }
    .questions-import .import-steps-bar .nav-item {
        flex: 1 1 0;
        min-width: 0;
        margin-inline-end: 0.5rem;
    }
    .questions-import .import-steps-bar .nav-item:last-child {
        margin-inline-end: 0;
    }
    .questions-import .import-steps-bar .nav-link {
        justify-content: center;
        width: 100%;
        pointer-events: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    .questions-import .import-steps-bar .nav-item.completed .nav-link {
        color: rgb(var(--success-rgb));
        border-color: rgb(var(--success-rgb)) !important;
    }
    .questions-import .import-steps-bar .nav-item.completed .nav-link i {
        border-color: rgb(var(--success-rgb));
        color: rgb(var(--success-rgb));
    }
    .questions-import-sidebar {
        top: 5rem;
        z-index: 1;
    }
    @media (max-width: 991.98px) {
        .questions-import .import-steps-bar {
            overflow-x: auto;
            flex-wrap: nowrap;
        }
        .questions-import .import-steps-bar .nav-item {
            flex: 0 0 auto;
            min-width: 130px;
        }
    }
</style>
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid questions-import">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">استيراد الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            @if(!empty($lockedSubject))
                                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.questions.index', $lockedSubject->id) }}">{{ $lockedSubject->name }}</a></li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">بنك الأسئلة</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">استيراد الأسئلة</li>
                        </ol>
                    </nav>
                    @if(!empty($lockedSubject))
                        <p class="text-muted small mb-0 mt-1">الأسئلة المستوردة ستُربط تلقائياً بمادة: <strong>{{ $lockedSubject->name }}</strong></p>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    @if (session('import_summary'))
                        <div class="mt-2 small">
                            <strong>ملخص الاستيراد:</strong><br>
                            نجح: {{ session('import_summary')['success'] }}<br>
                            فشل: {{ session('import_summary')['errors'] }}<br>
                            الإجمالي: {{ session('import_summary')['total'] }}
                        </div>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-xl-8 col-lg-7">

                    <div class="card custom-card mb-3">
                        <div class="card-body py-3">
                            <ul class="nav nav-tabs form-wizard-1 import-steps-bar d-flex mb-0" role="list">
                                <li class="nav-item active" id="step1" role="listitem">
                                    <span class="nav-link active" aria-current="step">
                                        <i class="bi bi-cloud-upload"></i>
                                        <span class="ms-1 d-none d-sm-inline">رفع الملف</span>
                                    </span>
                                </li>
                                <li class="nav-item" id="step2" role="listitem">
                                    <span class="nav-link">
                                        <i class="bi bi-columns"></i>
                                        <span class="ms-1 d-none d-sm-inline">تحديد الأعمدة</span>
                                    </span>
                                </li>
                                <li class="nav-item" id="step3" role="listitem">
                                    <span class="nav-link">
                                        <i class="bi bi-eye"></i>
                                        <span class="ms-1 d-none d-sm-inline">معاينة البيانات</span>
                                    </span>
                                </li>
                                <li class="nav-item" id="step4" role="listitem">
                                    <span class="nav-link">
                                        <i class="bi bi-check-circle"></i>
                                        <span class="ms-1 d-none d-sm-inline">الاستيراد</span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card custom-card mb-3" id="uploadStep">
                        <div class="card-header">
                            <div class="card-title">رفع ملف Excel/CSV</div>
                        </div>
                        <div class="card-body">
                            <div class="upload-area" id="uploadArea">
                                <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display: none;">
                                <div id="uploadContent">
                                    <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                                    <h5 class="mb-2">اسحب الملف هنا أو اضغط للاختيار</h5>
                                    <p class="text-muted mb-0">الصيغ المدعومة: Excel (.xlsx, .xls) أو CSV (.csv)</p>
                                    <p class="text-muted small mb-0">الحد الأقصى: 10 ميجابايت</p>
                                </div>
                                <div id="fileInfo" style="display: none;">
                                    <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
                                    <h5 class="mb-2" id="fileName"></h5>
                                    <p class="text-muted mb-0" id="fileSize"></p>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                                        <i class="bi bi-x-circle me-1"></i> إزالة الملف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-3" id="mappingStep" style="display: none;">
                        <div class="card-header">
                            <div class="card-title">تحديد الأعمدة</div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                قم بتحديد أي عمود في ملفك يطابق كل حقل في النظام. الحقول المميزة بعلامة <span class="required-field">*</span> إلزامية.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3">الحقول المطلوبة <span class="required-field">*</span></h6>
                                    <div class="column-mapping" id="requiredMappings"></div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3">الحقول الاختيارية</h6>
                                    <div class="column-mapping" id="optionalMappings"></div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="button" class="btn btn-primary" id="nextToPreviewBtn" disabled>
                                    <i class="bi bi-arrow-left me-2"></i> التالي: معاينة البيانات
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="showStep(1)">
                                    <i class="bi bi-arrow-right me-2"></i> رجوع
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-3" id="previewStep" style="display: none;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">معاينة البيانات</div>
                            <div>
                                <span class="badge bg-primary" id="previewCount">0</span> صف
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                يتم عرض أول 10 صفوف فقط للمعاينة. سيتم استيراد جميع الصفوف عند الضغط على «بدء الاستيراد».
                            </div>

                            <div class="table-responsive preview-table">
                                <table class="table table-bordered table-hover" id="previewTable">
                                    <thead class="table-light sticky-top">
                                        <tr id="previewHeader"></tr>
                                    </thead>
                                    <tbody id="previewBody"></tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="button" class="btn btn-primary" id="nextToImportBtn">
                                    <i class="bi bi-arrow-left me-2"></i> التالي: الاستيراد
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="showStep(2)">
                                    <i class="bi bi-arrow-right me-2"></i> رجوع
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-3" id="importStep" style="display: none;">
                        <div class="card-header">
                            <div class="card-title">جاهز للاستيراد</div>
                        </div>
                        <div class="card-body">
                            <div class="file-info mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white" id="finalFileName"></h6>
                                        <p class="mb-0 text-white-50 small" id="finalFileSize"></p>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-white fw-bold fs-18" id="finalRowCount">0</div>
                                        <div class="text-white-50 small">صف</div>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <input type="hidden" name="class_id" id="importClassId" value="{{ old('class_id', $prefillClassId ?? '') }}">
                                <input type="hidden" name="subject_id" id="importSubjectId" value="{{ old('subject_id', $prefillSubjectId ?? '') }}">
                                <input type="hidden" name="unit_id" id="importUnitId" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                                <input type="file" name="file" id="hiddenFileInput" style="display: none;">
                                <input type="hidden" name="column_mapping" id="columnMappingInput">

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg" id="importBtn">
                                        <i class="bi bi-upload me-2"></i> بدء الاستيراد
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-lg" id="backBtn">
                                        <i class="bi bi-arrow-right me-2"></i> رجوع
                                    </button>
                                    <a href="{{ !empty($lockedSubject) ? route('admin.subjects.questions.index', $lockedSubject->id) : route('admin.questions.index') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x me-2"></i> إلغاء
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="questions-import-sidebar sticky-lg-top">
                        <div class="card custom-card mb-3">
                            <div class="card-header">
                                <div class="card-title">الربط بالمنهج</div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    حدّد الصف والمادة والوحدة لربط جميع الأسئلة المستوردة. الوحدة اختيارية — إن تركتها فارغة يبقى السؤال عاماً ما لم يُحدَّد عمود <code>units</code> في الملف.
                                </p>
                                @if(!empty($lockedSubject))
                                    <input type="hidden" id="locked_class_id" value="{{ $lockedSubject->class_id }}">
                                @endif
                                @include('admin.pages.ai.question-generations.partials.optional-curriculum-fields', [
                                    'fieldPrefix' => '',
                                    'schoolClasses' => $schoolClasses,
                                    'lockedSubject' => $lockedSubject ?? null,
                                    'prefillClassId' => $prefillClassId ?? null,
                                    'prefillSubjectId' => $prefillSubjectId ?? null,
                                    'prefillUnitId' => $prefillUnitId ?? null,
                                ])
                            </div>
                        </div>

                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">تعليمات الاستيراد</div>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">الحقول المطلوبة:</h6>
                                <ul class="small mb-4">
                                    <li><code>type</code> — نوع السؤال (single_choice, multiple_choice, …)</li>
                                    <li><code>title</code> — عنوان السؤال</li>
                                    <li><code>difficulty</code> — الصعوبة (easy, medium, hard)</li>
                                    <li><code>points</code> — الدرجة</li>
                                </ul>

                                <h6 class="fw-semibold mb-3">أنواع الأسئلة المدعومة:</h6>
                                <ul class="small mb-4">
                                    <li>single_choice (اختيار واحد)</li>
                                    <li>multiple_choice (اختيار متعدد)</li>
                                    <li>true_false (صح/خطأ)</li>
                                    <li>short_answer (إجابة قصيرة)</li>
                                    <li>essay (مقالي)</li>
                                    <li>numerical (رقمي)</li>
                                </ul>

                                <div class="d-grid">
                                    <a href="{{ route('admin.questions.export.template') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> تحميل ملف Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let uploadedFile = null;
    let fileData = [];
    let fileColumns = [];
    let columnMapping = {};

    const requiredFields = [
        { key: 'type', label: 'نوع السؤال', icon: 'bi-tag' },
        { key: 'title', label: 'عنوان السؤال', icon: 'bi-heading' },
        { key: 'difficulty', label: 'الصعوبة', icon: 'bi-bar-chart' },
        { key: 'points', label: 'الدرجة', icon: 'bi-star' },
    ];

    const optionalFields = [
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

    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadContent = document.getElementById('uploadContent');
    const fileInfo = document.getElementById('fileInfo');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', handleDrop);
    fileInput.addEventListener('change', handleFileSelect);

    document.getElementById('removeFile')?.addEventListener('click', function(e) {
        e.stopPropagation();
        resetUpload();
    });

    function handleDragOver(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            processFile(files[0]);
        }
    }

    function handleFileSelect(e) {
        if (e.target.files.length > 0) {
            processFile(e.target.files[0]);
        }
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
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);

        fileInfo.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">جاري المعالجة...</span>
                </div>
                <p class="text-muted mb-0">جاري قراءة الملف...</p>
            </div>
        `;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                if (file.name.endsWith('.csv')) {
                    parseCSV(e.target.result);
                } else {
                    parseExcel(file);
                }
            } catch (error) {
                alert('حدث خطأ أثناء قراءة الملف: ' + error.message);
                resetUpload();
            }
        };

        reader.onerror = function() {
            alert('حدث خطأ أثناء قراءة الملف');
            resetUpload();
        };

        if (file.name.endsWith('.csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsArrayBuffer(file);
        }
    }

    function resetUpload() {
        uploadedFile = null;
        fileData = [];
        fileColumns = [];
        columnMapping = {};
        uploadArea.classList.remove('has-file');
        uploadContent.style.display = 'block';
        fileInfo.style.display = 'none';
        fileInfo.innerHTML = `
            <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
            <h5 class="mb-2" id="fileName"></h5>
            <p class="text-muted mb-0" id="fileSize"></p>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                <i class="bi bi-x-circle me-1"></i> إزالة الملف
            </button>
        `;
        document.getElementById('removeFile')?.addEventListener('click', function(e) {
            e.stopPropagation();
            resetUpload();
        });
        fileInput.value = '';
        showStep(1);
    }

    function renderFileInfo() {
        fileInfo.innerHTML = `
            <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
            <h5 class="mb-2" id="fileName">${uploadedFile.name}</h5>
            <p class="text-muted mb-0" id="fileSize">${formatFileSize(uploadedFile.size)}</p>
            <p class="text-success small mt-2"><i class="bi bi-check-circle me-1"></i> تم قراءة ${fileData.length} صف</p>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                <i class="bi bi-x-circle me-1"></i> إزالة الملف
            </button>
        `;
        document.getElementById('removeFile')?.addEventListener('click', function(e) {
            e.stopPropagation();
            resetUpload();
        });
    }

    function parseCSV(text) {
        Papa.parse(text, {
            header: true,
            skipEmptyLines: true,
            complete: function(results) {
                if (results.data.length === 0) {
                    alert('الملف فارغ أو لا يحتوي على بيانات');
                    resetUpload();
                    return;
                }
                fileData = results.data;
                fileColumns = Object.keys(results.data[0]);
                renderFileInfo();
                setupColumnMapping();
                showStep(2);
            },
            error: function(error) {
                alert('حدث خطأ أثناء قراءة الملف: ' + error.message);
                resetUpload();
            }
        });
    }

    function parseExcel(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
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
                setupColumnMapping();
                showStep(2);
            } catch (error) {
                alert('حدث خطأ أثناء قراءة الملف: ' + error.message);
                resetUpload();
            }
        };
        reader.onerror = function() {
            alert('حدث خطأ أثناء قراءة الملف');
            resetUpload();
        };
        reader.readAsArrayBuffer(file);
    }

    function setupColumnMapping() {
        const autoMapping = {};
        const columnLower = fileColumns.map(c => c.toLowerCase().trim());

        requiredFields.forEach(field => {
            const fieldKey = field.key.toLowerCase();
            const fieldLabel = field.label.toLowerCase();

            const match = fileColumns.find((col, idx) => {
                const colLower = columnLower[idx];
                return colLower === fieldKey ||
                       colLower === fieldLabel ||
                       colLower.includes(fieldKey) ||
                       colLower.includes(fieldLabel);
            });

            if (match) {
                autoMapping[field.key] = match;
            }
        });

        const requiredMappings = document.getElementById('requiredMappings');
        requiredMappings.innerHTML = '';
        requiredFields.forEach(field => {
            requiredMappings.appendChild(createMappingItem(field, true, autoMapping[field.key]));
        });

        const optionalMappings = document.getElementById('optionalMappings');
        optionalMappings.innerHTML = '';
        optionalFields.forEach(field => {
            optionalMappings.appendChild(createMappingItem(field, false, autoMapping[field.key]));
        });

        Object.keys(autoMapping).forEach(key => {
            columnMapping[key] = autoMapping[key];
        });

        const allRequiredMapped = requiredFields.every(field => columnMapping[field.key]);
        const nextBtn = document.getElementById('nextToPreviewBtn');
        if (nextBtn) {
            nextBtn.disabled = !allRequiredMapped;
        }
    }

    function createMappingItem(field, required, autoSelected = null) {
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
                    ${fileColumns.map(col =>
                        `<option value="${col}" ${col === autoSelected ? 'selected' : ''}>${col}</option>`
                    ).join('')}
                </select>
            </div>
        `;
        return div;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('field-mapping')) {
            const field = e.target.dataset.field;
            const column = e.target.value;
            if (column) {
                columnMapping[field] = column;
            } else {
                delete columnMapping[field];
            }

            const allRequiredMapped = requiredFields.every(field => columnMapping[field.key]);
            const nextBtn = document.getElementById('nextToPreviewBtn');
            if (nextBtn) {
                nextBtn.disabled = !allRequiredMapped;
            }

            if (allRequiredMapped) {
                updatePreview();
            }
        }
    });

    document.getElementById('nextToPreviewBtn')?.addEventListener('click', function() {
        const allRequiredMapped = requiredFields.every(field => columnMapping[field.key]);
        if (allRequiredMapped) {
            updatePreview();
            showStep(3);
        } else {
            alert('الرجاء تحديد جميع الحقول المطلوبة');
        }
    });

    document.getElementById('nextToImportBtn')?.addEventListener('click', function() {
        prepareImport();
        showStep(4);
    });

    function updatePreview() {
        showPreview();
    }

    function showPreview() {
        const previewHeader = document.getElementById('previewHeader');
        const previewBody = document.getElementById('previewBody');

        previewHeader.innerHTML = '';
        previewBody.innerHTML = '';

        const mappedFields = [...requiredFields, ...optionalFields].filter(f => columnMapping[f.key]);
        mappedFields.forEach(field => {
            const th = document.createElement('th');
            th.textContent = field.label;
            previewHeader.appendChild(th);
        });

        const previewRows = fileData.slice(0, 10);
        previewRows.forEach(row => {
            const tr = document.createElement('tr');
            mappedFields.forEach(field => {
                const td = document.createElement('td');
                const column = columnMapping[field.key];
                td.textContent = row[column] || '-';
                tr.appendChild(td);
            });
            previewBody.appendChild(tr);
        });

        document.getElementById('previewCount').textContent = previewRows.length;
    }

    window.showStep = function(stepNumber) {
        for (let i = 1; i <= 4; i++) {
            const stepItem = document.getElementById(`step${i}`);
            const link = stepItem.querySelector('.nav-link');

            stepItem.classList.remove('active', 'completed');
            link.classList.remove('active');
            link.removeAttribute('aria-current');

            if (i < stepNumber) {
                stepItem.classList.add('completed');
            } else if (i === stepNumber) {
                stepItem.classList.add('active');
                link.classList.add('active');
                link.setAttribute('aria-current', 'step');
            }
        }

        document.getElementById('uploadStep').style.display = stepNumber === 1 ? 'block' : 'none';
        document.getElementById('mappingStep').style.display = stepNumber === 2 ? 'block' : 'none';
        document.getElementById('previewStep').style.display = stepNumber === 3 ? 'block' : 'none';
        document.getElementById('importStep').style.display = stepNumber === 4 ? 'block' : 'none';

        if (stepNumber === 4) {
            prepareImport();
        }
    };

    function prepareImport() {
        document.getElementById('finalFileName').textContent = uploadedFile.name;
        document.getElementById('finalFileSize').textContent = formatFileSize(uploadedFile.size);
        document.getElementById('finalRowCount').textContent = fileData.length;

        document.getElementById('columnMappingInput').value = JSON.stringify(columnMapping);

        syncCurriculumToForm();

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(uploadedFile);
        document.getElementById('hiddenFileInput').files = dataTransfer.files;
    }

    function syncCurriculumToForm() {
        const classSelect = document.getElementById('class_id');
        const subjectSelect = document.getElementById('subject_id');
        const unitSelect = document.getElementById('unit_id');
        const importClassId = document.getElementById('importClassId');
        const importSubjectId = document.getElementById('importSubjectId');
        const importUnitId = document.getElementById('importUnitId');

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

    document.getElementById('backBtn')?.addEventListener('click', function() {
        showStep(3);
    });

    document.getElementById('importForm')?.addEventListener('submit', function(e) {
        if (!uploadedFile) {
            e.preventDefault();
            alert('الرجاء رفع ملف أولاً');
            return;
        }
        syncCurriculumToForm();
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
});
</script>
@include('admin.pages.ai.question-generations.partials.optional-curriculum-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initOptionalCurriculumCascade === 'function') {
        window.initOptionalCurriculumCascade({
            classSelectId: 'class_id',
            subjectSelectId: 'subject_id',
            unitSelectId: 'unit_id',
            ajaxSubjectsBase: @json(url('admin/questions/ajax/classes')),
            ajaxUnitsBase: @json(url('admin/questions/ajax/subjects')),
            prefillSubjectId: @json($prefillSubjectId ?? ''),
            prefillUnitId: @json($prefillUnitId ?? ''),
            lockedSubjectId: @json(!empty($lockedSubject) ? $lockedSubject->id : null),
        });
    }
});
</script>
@stop
