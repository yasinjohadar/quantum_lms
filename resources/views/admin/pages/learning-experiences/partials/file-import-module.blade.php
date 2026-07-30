@php
    $parseUrl = route('admin.learning-experiences.import.parse', $experience);
    $applyUrl = route('admin.learning-experiences.import.apply', $experience);
@endphp

<div class="ile-panel pack-import-module ile-file-import-module mb-3" id="ileFileImportModule"
     data-parse-url="{{ $parseUrl }}"
     data-apply-url="{{ $applyUrl }}"
     x-data
     @ile-import-apply.window="typeof applyImportedQuestions === 'function' && applyImportedQuestions($event.detail)">
    <div class="ile-panel__head">
        <h6><i class="bi bi-file-earmark-arrow-up"></i>استيراد من ملف (CSV / MD / JSON)</h6>
        <div class="d-flex flex-wrap gap-2">
            <a class="ile-btn ile-btn--line" style="padding:.35rem .7rem;font-size:.75rem"
               href="{{ route('admin.learning-experiences.import.template', ['format' => 'csv']) }}">
                <i class="bi bi-download"></i> قالب CSV
            </a>
            <a class="ile-btn ile-btn--line" style="padding:.35rem .7rem;font-size:.75rem"
               href="{{ route('admin.learning-experiences.import.template', ['format' => 'md']) }}">
                قالب MD
            </a>
            <a class="ile-btn ile-btn--line" style="padding:.35rem .7rem;font-size:.75rem"
               href="{{ route('admin.learning-experiences.import.template', ['format' => 'json']) }}">
                قالب JSON
            </a>
        </div>
    </div>
    <div class="ile-panel__body">
        <p class="ile-hint mb-3">
            نفس أسلوب استيراد المواد: ارفع الملف ← تحليل ومعاينة (مع عرض معادلات الرياضيات) ← ثم تأكيد الاستيراد.
            يدعم أنواع التجربة بما فيها اختيار واحد/متعدد، صح/خطأ، رقمي، إجابة قصيرة، وترتيب.
            الأنواع المعقّدة (سحب، مطابقة، …) عبر <strong>JSON</strong> ببنية <code>payload</code> الكاملة.
            ملفات الرياضيات بصيغة Question / Option A-D / Correct Answer مدعومة أيضاً.
        </p>

        <div class="pack-import-steps" aria-hidden="true">
            <span class="pack-import-step is-active" id="ileImportStepFile">1. الملف</span>
            <span class="pack-import-step" id="ileImportStepParse">2. التحليل والمعاينة</span>
            <span class="pack-import-step" id="ileImportStepDone">3. الاستيراد</span>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">صيغة الملف</label>
                <div class="btn-group w-100 format-toggle" role="group">
                    <input type="radio" class="btn-check" name="ileImportFormat" id="ileImportFormatCsv" value="csv" checked>
                    <label class="btn btn-outline-primary" for="ileImportFormatCsv"><i class="bi bi-filetype-csv me-1"></i>CSV</label>
                    <input type="radio" class="btn-check" name="ileImportFormat" id="ileImportFormatMd" value="md">
                    <label class="btn btn-outline-primary" for="ileImportFormatMd"><i class="bi bi-markdown me-1"></i>MD</label>
                    <input type="radio" class="btn-check" name="ileImportFormat" id="ileImportFormatJson" value="json">
                    <label class="btn btn-outline-primary" for="ileImportFormatJson"><i class="bi bi-filetype-json me-1"></i>JSON</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">وضع الدمج</label>
                <select class="form-select" id="ileImportMergeMode">
                    <option value="append">إضافة للأسئلة الحالية</option>
                    <option value="replace">استبدال الأسئلة الحالية</option>
                </select>
            </div>
        </div>

        <label class="form-label">ملف الاستيراد</label>
        <div class="pack-import-upload-area mb-3" id="ileImportUploadArea" role="button" tabindex="0">
            <input type="file" id="ileImportFileInput" accept=".csv,text/csv" class="visually-hidden">
            <div id="ileImportUploadContent">
                <span class="pack-import-upload-area__format-badge" id="ileImportFormatBadge">CSV</span>
                <i class="bi bi-cloud-arrow-up display-6 text-primary mb-2 d-block"></i>
                <h6 class="mb-1">اسحب الملف هنا أو انقر للاختيار</h6>
                <p class="text-muted small mb-2" id="ileImportAcceptHint">الصيغة: .csv — الحد الأقصى 10 ميجابايت</p>
                <button type="button" class="btn btn-primary btn-sm" id="ileImportBrowseBtn">
                    <i class="bi bi-folder2-open me-1"></i> تصفح واختر ملف
                </button>
            </div>
            <div id="ileImportFileInfo" style="display:none;">
                <i class="bi bi-file-earmark-check display-6 text-success mb-2"></i>
                <h6 class="mb-1" id="ileImportFileName"></h6>
                <p class="text-muted small mb-2" id="ileImportFileSize"></p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="ileImportReplaceFile">استبدال</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="ileImportRemoveFile">إزالة</button>
                </div>
            </div>
        </div>

        <div id="ileImportParseError" class="alert alert-danger d-none" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 mb-3 parse-action-row">
            <button type="button" class="btn btn-outline-primary btn-parse-file" id="ileImportParseBtn" disabled>
                <i class="bi bi-search me-1"></i> تحليل الملف والمعاينة
            </button>
            <span class="align-self-center text-muted small" id="ileImportParseHint">اختر ملفاً أولاً ثم اضغط تحليل</span>
        </div>

        <div id="ileImportPreviewSection" style="display:none;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h6 class="fw-semibold mb-0">معاينة (<span id="ileImportPreviewCount">0</span> سؤال)</h6>
                <span class="badge bg-warning-transparent text-warning" id="ileImportWarnBadge" style="display:none;"></span>
            </div>
            <div class="preview-table border rounded p-2 mb-3 math-import-preview-table" id="ileImportPreviewTable"></div>
            <button type="button" class="ile-btn ile-btn--primary" id="ileImportApplyBtn">
                <i class="bi bi-cloud-download"></i>
                <span id="ileImportApplyLabel">استيراد الأسئلة إلى التجربة</span>
            </button>
        </div>
    </div>
</div>

@include('admin.pages.questions.partials.pack-import-upload-styles')
@include('partials.question-math-scripts')

@push('styles')
<style>
    .ile-file-import-module .math-preview-card {
        border: 1px solid var(--ile-line, #e5e7eb);
        border-radius: 12px;
        padding: .85rem 1rem;
        margin-bottom: .75rem;
        background: #fff;
    }
    .ile-file-import-module .math-preview-card.has-math-warning {
        border-color: rgba(234, 179, 8, .55);
        background: rgba(234, 179, 8, .05);
    }
    .ile-file-import-module .math-preview-option {
        display: flex; gap: .5rem; align-items: flex-start;
        padding: .35rem .5rem; border-radius: 8px; margin-bottom: .25rem;
    }
    .ile-file-import-module .math-preview-option.is-correct {
        background: rgba(13, 143, 122, .08);
        border: 1px solid rgba(13, 143, 122, .3);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/admin/ile-experience-import.js') }}?v=20260730a" defer></script>
@endpush
