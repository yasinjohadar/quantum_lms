@extends('admin.layouts.master')

@section('page-title')
    استيراد الأسئلة
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.css">
<style>
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8fafc;
        cursor: pointer;
    }
    .upload-area:hover, .upload-area.dragover {
        border-color: #4f46e5;
        background: #eef2ff;
    }
    .upload-area.has-file {
        border-color: #10b981;
        background: #ecfdf5;
    }
    .preview-table {
        max-height: 400px;
        overflow-y: auto;
    }
    .column-mapping {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        background: #f9fafb;
    }
    .mapping-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: white;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
    }
    .mapping-item:hover {
        border-color: #4f46e5;
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.1);
    }
    .file-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        padding: 16px;
    }
    .steps-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }
    
    @media (max-width: 768px) {
        .steps-container {
            grid-template-columns: 1fr;
        }
    }
    .step-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 28px 24px;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
        position: relative;
        overflow: hidden;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .step-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: all 0.3s ease;
    }
    .step-card:hover:not(.active):not(.completed) {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .step-card.active {
        border-color: #4f46e5;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.15);
        transform: translateY(-4px);
    }
    .step-card.active::before {
        background: linear-gradient(180deg, #4f46e5 0%, #6366f1 100%);
        width: 5px;
    }
    .step-card.completed {
        border-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.1);
    }
    .step-card.completed::before {
        background: linear-gradient(180deg, #10b981 0%, #22c55e 100%);
        width: 5px;
    }
    .step-icon-wrapper {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        background: #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .step-icon-wrapper::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 22px;
        background: transparent;
        transition: all 0.3s ease;
    }
    .step-card.active .step-icon-wrapper {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
        transform: scale(1.05);
    }
    .step-card.active .step-icon-wrapper::after {
        background: rgba(79, 70, 229, 0.1);
    }
    .step-card.completed .step-icon-wrapper {
        background: linear-gradient(135deg, #10b981 0%, #22c55e 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
    }
    .step-card.completed .step-icon-wrapper::after {
        background: rgba(16, 185, 129, 0.1);
    }
    .step-icon {
        font-size: 32px;
        color: #64748b;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }
    .step-card.active .step-icon {
        color: white;
        transform: scale(1.1);
    }
    .step-card.completed .step-icon {
        color: white;
    }
    .step-number {
        position: absolute;
        top: 16px;
        left: 16px;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #e5e7eb;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .step-card.active .step-number {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: white;
        box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
        transform: scale(1.1);
    }
    .step-card.completed .step-number {
        background: linear-gradient(135deg, #10b981 0%, #22c55e 100%);
        color: white;
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
    }
    .step-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    .step-card.active .step-title {
        color: #4f46e5;
        font-size: 19px;
    }
    .step-card.completed .step-title {
        color: #10b981;
    }
    .step-description {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 16px;
        min-height: 40px;
    }
    .step-card.active .step-description {
        color: #4b5563;
        font-weight: 500;
    }
    .step-status {
        margin-top: auto;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .step-status.pending {
        background: #f1f5f9;
        color: #64748b;
    }
    .step-status.active {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4f46e5;
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
    }
    .step-status.completed {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #10b981;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }
    .required-field {
        color: #ef4444;
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">استيراد الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">استيراد الأسئلة</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    @if (session('import_summary'))
                        <div class="mt-2 small">
                            <strong>ملخص الاستيراد:</strong><br>
                            ✅ نجح: {{ session('import_summary')['success'] }}<br>
                            ❌ فشل: {{ session('import_summary')['errors'] }}<br>
                            📊 الإجمالي: {{ session('import_summary')['total'] }}
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

            <!-- Steps Cards -->
            <div class="steps-container">
                <div class="step-card active" id="step1">
                    <div class="step-number">1</div>
                    <div class="step-icon-wrapper">
                        <i class="bi bi-cloud-upload step-icon"></i>
                    </div>
                    <h6 class="step-title">رفع الملف</h6>
                    <p class="step-description mb-0">قم برفع ملف Excel أو CSV</p>
                    <span class="step-status active">نشط</span>
                </div>
                
                <div class="step-card" id="step2">
                    <div class="step-number">2</div>
                    <div class="step-icon-wrapper">
                        <i class="bi bi-columns step-icon"></i>
                    </div>
                    <h6 class="step-title">تحديد الأعمدة</h6>
                    <p class="step-description mb-0">حدد الأعمدة المناسبة</p>
                    <span class="step-status pending">قادم</span>
                </div>
                
                <div class="step-card" id="step3">
                    <div class="step-number">3</div>
                    <div class="step-icon-wrapper">
                        <i class="bi bi-eye step-icon"></i>
                    </div>
                    <h6 class="step-title">معاينة البيانات</h6>
                    <p class="step-description mb-0">راجع البيانات قبل الاستيراد</p>
                    <span class="step-status pending">قادم</span>
                </div>
                
                <div class="step-card" id="step4">
                    <div class="step-number">4</div>
                    <div class="step-icon-wrapper">
                        <i class="bi bi-check-circle step-icon"></i>
                    </div>
                    <h6 class="step-title">الاستيراد</h6>
                    <p class="step-description mb-0">ابدأ عملية الاستيراد</p>
                    <span class="step-status pending">قادم</span>
                </div>
            </div>

            <!-- Step 1: Upload File -->
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
                            <p class="text-muted small">الحد الأقصى: 10 ميجابايت</p>
                        </div>
                        <div id="fileInfo" style="display: none;">
                            <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
                            <h5 class="mb-2" id="fileName"></h5>
                            <p class="text-muted mb-0" id="fileSize"></p>
                            <button class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                                <i class="bi bi-x-circle me-1"></i> إزالة الملف
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Column Mapping -->
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
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-primary" id="nextToPreviewBtn" disabled>
                            <i class="bi bi-arrow-left me-2"></i> التالي: معاينة البيانات
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="showStep(1)">
                            <i class="bi bi-arrow-right me-2"></i> رجوع
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Preview -->
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
                        يتم عرض أول 10 صفوف فقط للمعاينة. سيتم استيراد جميع الصفوف عند الضغط على "بدء الاستيراد".
                    </div>
                    
                    <div class="table-responsive preview-table">
                        <table class="table table-bordered table-hover" id="previewTable">
                            <thead class="table-light sticky-top">
                                <tr id="previewHeader"></tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-primary" id="nextToImportBtn">
                            <i class="bi bi-arrow-left me-2"></i> التالي: الاستيراد
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="showStep(2)">
                            <i class="bi bi-arrow-right me-2"></i> رجوع
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Import -->
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
                        <input type="file" name="file" id="hiddenFileInput" style="display: none;">
                        <input type="hidden" name="column_mapping" id="columnMappingInput">
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="importBtn">
                                <i class="bi bi-upload me-2"></i> بدء الاستيراد
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" id="backBtn">
                                <i class="bi bi-arrow-right me-2"></i> رجوع
                            </button>
                            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x me-2"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">تعليمات الاستيراد</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">الحقول المطلوبة:</h6>
                            <ul class="small mb-0">
                                <li><code>type</code> - نوع السؤال (single_choice, multiple_choice, etc.)</li>
                                <li><code>title</code> - عنوان السؤال</li>
                                <li><code>difficulty</code> - الصعوبة (easy, medium, hard)</li>
                                <li><code>points</code> - الدرجة</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">أنواع الأسئلة المدعومة:</h6>
                            <ul class="small mb-0">
                                <li>single_choice (اختيار واحد)</li>
                                <li>multiple_choice (اختيار متعدد)</li>
                                <li>true_false (صح/خطأ)</li>
                                <li>short_answer (إجابة قصيرة)</li>
                                <li>essay (مقالي)</li>
                                <li>numerical (رقمي)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.questions.export.template') }}" class="btn btn-outline-primary">
                            <i class="bi bi-download me-1"></i> تحميل ملف Template
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
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
    
    // Field definitions
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

    // Upload area handlers
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadContent = document.getElementById('uploadContent');
    const fileInfo = document.getElementById('fileInfo');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', handleDrop);
    fileInput.addEventListener('change', handleFileSelect);

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
        
        // Show loading
        const loadingHtml = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">جاري المعالجة...</span>
                </div>
                <p class="text-muted mb-0">جاري قراءة الملف...</p>
            </div>
        `;
        fileInfo.innerHTML = loadingHtml;

        // Read file
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
        fileInput.value = '';
        showStep(1);
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
                
                // Update file info
                fileInfo.innerHTML = `
                    <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
                    <h5 class="mb-2" id="fileName">${uploadedFile.name}</h5>
                    <p class="text-muted mb-0" id="fileSize">${formatFileSize(uploadedFile.size)}</p>
                    <p class="text-success small mt-2"><i class="bi bi-check-circle me-1"></i> تم قراءة ${fileData.length} صف</p>
                    <button class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                        <i class="bi bi-x-circle me-1"></i> إزالة الملف
                    </button>
                `;
                
                // Re-attach remove button
                document.getElementById('removeFile')?.addEventListener('click', function() {
                    resetUpload();
                });
                
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
                
                // Update file info
                fileInfo.innerHTML = `
                    <i class="bi bi-file-earmark-check display-4 text-success mb-3"></i>
                    <h5 class="mb-2" id="fileName">${uploadedFile.name}</h5>
                    <p class="text-muted mb-0" id="fileSize">${formatFileSize(uploadedFile.size)}</p>
                    <p class="text-success small mt-2"><i class="bi bi-check-circle me-1"></i> تم قراءة ${fileData.length} صف</p>
                    <button class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                        <i class="bi bi-x-circle me-1"></i> إزالة الملف
                    </button>
                `;
                
                // Re-attach remove button
                document.getElementById('removeFile')?.addEventListener('click', function() {
                    resetUpload();
                });
                
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
        // Auto-detect common column names
        const autoMapping = {};
        const columnLower = fileColumns.map(c => c.toLowerCase().trim());
        
        requiredFields.forEach(field => {
            const fieldKey = field.key.toLowerCase();
            const fieldLabel = field.label.toLowerCase();
            
            // Try to find matching column
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

        // Setup required fields
        const requiredMappings = document.getElementById('requiredMappings');
        requiredMappings.innerHTML = '';
        requiredFields.forEach(field => {
            const mappingItem = createMappingItem(field, true, autoMapping[field.key]);
            requiredMappings.appendChild(mappingItem);
        });

        // Setup optional fields
        const optionalMappings = document.getElementById('optionalMappings');
        optionalMappings.innerHTML = '';
        optionalFields.forEach(field => {
            const mappingItem = createMappingItem(field, false, autoMapping[field.key]);
            optionalMappings.appendChild(mappingItem);
        });
        
        // Update column mapping from auto-detection
        Object.keys(autoMapping).forEach(key => {
            columnMapping[key] = autoMapping[key];
        });
        
        // Check if all required fields are auto-mapped
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

    // Handle mapping changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('field-mapping')) {
            const field = e.target.dataset.field;
            const column = e.target.value;
            if (column) {
                columnMapping[field] = column;
            } else {
                delete columnMapping[field];
            }
            
            // Check if all required fields are mapped
            const allRequiredMapped = requiredFields.every(field => columnMapping[field.key]);
            const nextBtn = document.getElementById('nextToPreviewBtn');
            if (nextBtn) {
                nextBtn.disabled = !allRequiredMapped;
            }
            
            // Update preview if all required fields are mapped
            if (allRequiredMapped) {
                updatePreview();
            }
        }
    });
    
    // Next to preview button
    document.getElementById('nextToPreviewBtn')?.addEventListener('click', function() {
        const allRequiredMapped = requiredFields.every(field => columnMapping[field.key]);
        if (allRequiredMapped) {
            updatePreview();
            showStep(3);
        } else {
            alert('الرجاء تحديد جميع الحقول المطلوبة');
        }
    });
    
    // Next to import button
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
        
        // Clear previous
        previewHeader.innerHTML = '';
        previewBody.innerHTML = '';
        
        // Create header
        const mappedFields = [...requiredFields, ...optionalFields].filter(f => columnMapping[f.key]);
        mappedFields.forEach(field => {
            const th = document.createElement('th');
            th.textContent = field.label;
            previewHeader.appendChild(th);
        });
        
        // Show first 10 rows
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
        // Update step cards
        for (let i = 1; i <= 4; i++) {
            const stepCard = document.getElementById(`step${i}`);
            const statusBadge = stepCard.querySelector('.step-status');
            
            if (i < stepNumber) {
                stepCard.classList.remove('active');
                stepCard.classList.add('completed');
                statusBadge.textContent = 'مكتمل';
                statusBadge.className = 'step-status completed';
            } else if (i === stepNumber) {
                stepCard.classList.add('active');
                stepCard.classList.remove('completed');
                statusBadge.textContent = 'نشط';
                statusBadge.className = 'step-status active';
            } else {
                stepCard.classList.remove('active', 'completed');
                statusBadge.textContent = 'قادم';
                statusBadge.className = 'step-status pending';
            }
        }
        
        // Show/hide steps
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
        
        // Store mapping in hidden input
        document.getElementById('columnMappingInput').value = JSON.stringify(columnMapping);
        
        // Store file reference
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(uploadedFile);
        document.getElementById('hiddenFileInput').files = dataTransfer.files;
    }


    // Back button
    document.getElementById('backBtn')?.addEventListener('click', function() {
        showStep(3);
    });

    // Form submit
    document.getElementById('importForm')?.addEventListener('submit', function(e) {
        if (!uploadedFile) {
            e.preventDefault();
            alert('الرجاء رفع ملف أولاً');
            return;
        }
        
        // Create FormData with file
        const formData = new FormData(this);
        formData.append('file', uploadedFile);
        
        // Submit will be handled normally
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
@stop