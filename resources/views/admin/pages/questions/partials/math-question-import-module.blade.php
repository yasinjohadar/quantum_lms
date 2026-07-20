@php
    $requireSubject = $requireSubject ?? empty($lockedSubject);
    $lockedSubjectId = !empty($lockedSubject) ? $lockedSubject->id : null;
    $lockedClassId = !empty($lockedSubject) ? $lockedSubject->class_id : null;
    $parseUrl = $parseUrl ?? route('admin.questions.math.parse');
    $importFormAction = $importFormAction ?? route('admin.questions.math.import');
    $importSubmitLabel = $importSubmitLabel ?? 'استيراد أسئلة الرياضيات';
@endphp

<div class="card custom-card mb-3 math-question-import-module pack-import-module" id="mathQuestionImportModule"
     data-parse-url="{{ $parseUrl }}"
     data-import-url="{{ $importFormAction }}"
     data-require-subject="{{ $requireSubject ? '1' : '0' }}"
     data-locked-subject-id="{{ $lockedSubjectId }}"
     data-locked-class-id="{{ $lockedClassId }}">
    <div class="card-header">
        <div class="card-title mb-0">
            <i class="bi bi-calculator me-2"></i>
            استيراد أسئلة رياضيات (LaTeX)
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            مخصّص لملفات اختبارات الرياضيات بالأعمدة: <code>#</code>, <code>Question</code>, <code>Hint</code>,
            <code>Option A-D</code>, <code>Correct Answer</code> (مثل <code>C. 185</code>), <code>Rationale</code>.
            تُحفظ الأسئلة دائماً كـ «اختيار واحد» مع تحويل رموز LaTeX/الرياضيات إلى صيغة تُعرض عبر KaTeX تلقائياً.
            @if($requireSubject)
                <span class="d-block mt-1"><strong>مطلوب:</strong> اختر المادة من «الربط بالمنهج» في الشريط الجانبي قبل الاستيراد.</span>
            @endif
        </p>

        <div class="pack-import-steps" aria-hidden="true">
            <span class="pack-import-step is-active" id="mathImportStepFile">1. الملف</span>
            <span class="pack-import-step" id="mathImportStepParse">2. التحليل والمعاينة</span>
            <span class="pack-import-step" id="mathImportStepDone">3. الاستيراد</span>
        </div>

        <label class="form-label fw-semibold">صيغة الملف</label>
        <div class="btn-group mb-3 format-toggle" role="group" aria-label="صيغة الملف">
            <input type="radio" class="btn-check" name="mathImportFormat" id="mathImportFormatCsv" value="csv" checked autocomplete="off">
            <label class="btn btn-outline-primary" for="mathImportFormatCsv"><i class="bi bi-filetype-csv me-1"></i> CSV (.csv)</label>
            <input type="radio" class="btn-check" name="mathImportFormat" id="mathImportFormatMd" value="md" autocomplete="off">
            <label class="btn btn-outline-primary" for="mathImportFormatMd"><i class="bi bi-markdown me-1"></i> Markdown (.md)</label>
        </div>

        <label class="form-label fw-semibold">ملف الاستيراد</label>
        <div class="pack-import-upload-area mb-3"
             id="mathImportUploadArea"
             role="button"
             tabindex="0"
             aria-label="اختر ملف أسئلة الرياضيات">
            <input type="file" id="mathImportFileInput" accept=".csv,text/csv" class="visually-hidden">
            <div id="mathImportUploadContent">
                <span class="pack-import-upload-area__format-badge" id="mathImportFormatBadge">CSV</span>
                <i class="bi bi-cloud-arrow-up display-6 text-primary mb-2 d-block"></i>
                <h6 class="mb-1">اسحب الملف هنا أو انقر للاختيار</h6>
                <p class="text-muted small mb-2" id="mathImportAcceptHint">الصيغة: .csv — الحد الأقصى 10 ميجابايت</p>
                <button type="button" class="btn btn-primary btn-sm" id="mathImportBrowseBtn">
                    <i class="bi bi-folder2-open me-1"></i> تصفح واختر ملف
                </button>
            </div>
            <div id="mathImportFileInfo" style="display: none;">
                <i class="bi bi-file-earmark-check display-6 text-success mb-2"></i>
                <h6 class="mb-1" id="mathImportFileName"></h6>
                <p class="text-muted small mb-2" id="mathImportFileSize"></p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="mathImportReplaceFile">
                        <i class="bi bi-arrow-repeat me-1"></i> استبدال
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="mathImportRemoveFile">
                        <i class="bi bi-x-circle me-1"></i> إزالة
                    </button>
                </div>
            </div>
        </div>

        <div id="mathImportParseError" class="alert alert-danger d-none" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 mb-3 parse-action-row">
            <button type="button" class="btn btn-outline-primary btn-parse-file" id="mathImportParseBtn" disabled>
                <i class="bi bi-search me-1"></i> تحليل الملف ومعاينة المعادلات
            </button>
            <span class="align-self-center text-muted small" id="mathImportParseHint">اختر ملفاً أولاً ثم اضغط تحليل</span>
        </div>

        <div id="mathImportPreviewSection" style="display: none;">
            <h6 class="fw-semibold mb-2">معاينة (<span id="mathImportPreviewCount">0</span> سؤال) — كما ستظهر بعد عرض LaTeX</h6>
            <div class="preview-table border rounded p-2 mb-3 math-import-preview-table" id="mathImportPreviewTable"></div>

            <form action="{{ $importFormAction }}" method="POST" enctype="multipart/form-data" id="mathImportForm">
                @csrf
                <input type="hidden" name="format" id="mathImportSubmitFormat" value="csv">
                <input type="hidden" name="class_id" id="mathImportClassId" value="{{ old('class_id', $prefillClassId ?? ($lockedClassId ?? '')) }}">
                <input type="hidden" name="subject_id" id="mathImportSubjectId" value="{{ old('subject_id', $prefillSubjectId ?? ($lockedSubjectId ?? '')) }}">
                <input type="hidden" name="unit_id" id="mathImportUnitId" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                <input type="file" name="file" id="mathImportSubmitFileInput" style="display: none;">

                <button type="submit" class="btn btn-success btn-lg" id="mathImportSubmitBtn">
                    <i class="bi bi-cloud-download me-2"></i>
                    <span id="mathImportSubmitBtnLabel">{{ $importSubmitLabel }}</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .math-import-preview-table .math-preview-card {
        border: 1px solid var(--default-border);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        background: var(--default-background);
    }
    .math-import-preview-table .math-preview-card:last-child {
        margin-bottom: 0;
    }
    .math-import-preview-table .math-preview-option {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
        margin-bottom: 0.25rem;
    }
    .math-import-preview-table .math-preview-option.is-correct {
        background: rgba(var(--success-rgb), 0.08);
        border: 1px solid rgba(var(--success-rgb), 0.35);
    }
    .math-import-preview-table .math-preview-option .option-letter {
        font-weight: 700;
        min-width: 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/admin/math-question-import.js') }}" defer></script>
@endpush
