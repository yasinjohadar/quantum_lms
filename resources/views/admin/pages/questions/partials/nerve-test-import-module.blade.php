@php
    $requireSubject = $requireSubject ?? empty($lockedSubject);
    $lockedSubjectId = !empty($lockedSubject) ? $lockedSubject->id : null;
    $lockedClassId = !empty($lockedSubject) ? $lockedSubject->class_id : null;
    $parseUrl = $parseUrl ?? route('admin.questions.nerve-test.parse');
    $importFormAction = $importFormAction ?? route('admin.questions.nerve-test.import');
    $importSubmitLabel = $importSubmitLabel ?? 'استيراد الأسئلة';
@endphp

<div class="card custom-card mb-3 nerve-test-import-module pack-import-module" id="nerveTestImportModule"
     data-parse-url="{{ $parseUrl }}"
     data-import-url="{{ $importFormAction }}"
     data-require-subject="{{ $requireSubject ? '1' : '0' }}"
     data-locked-subject-id="{{ $lockedSubjectId }}"
     data-locked-class-id="{{ $lockedClassId }}">
    <div class="card-header">
        <div class="card-title mb-0">
            <i class="bi bi-lightning-charge me-2"></i>
            استيراد حزمة اختبار الأعصاب (MD / CSV)
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            ملف واحد بصيغة Markdown أو CSV كما في نموذج «اختبار الأعصاب». الأسئلة من نوع صح/خطأ وتُستورد كلها بعد المعاينة.
            @if($requireSubject)
                <span class="d-block mt-1"><strong>مطلوب:</strong> اختر المادة من «الربط بالمنهج» في الشريط الجانبي قبل الاستيراد.</span>
            @endif
        </p>

        <div class="pack-import-steps" aria-hidden="true">
            <span class="pack-import-step is-active" id="nerveTestStepFormat">1. الصيغة</span>
            <span class="pack-import-step" id="nerveTestStepFile">2. الملف</span>
            <span class="pack-import-step" id="nerveTestStepParse">3. التحليل</span>
        </div>

        <label class="form-label fw-semibold">صيغة الملف</label>
        <div class="btn-group mb-3 format-toggle" role="group" aria-label="صيغة الملف">
            <input type="radio" class="btn-check" name="nerveTestFormat" id="nerveTestFormatMd" value="md" checked autocomplete="off">
            <label class="btn btn-outline-primary" for="nerveTestFormatMd"><i class="bi bi-markdown me-1"></i> Markdown (.md)</label>
            <input type="radio" class="btn-check" name="nerveTestFormat" id="nerveTestFormatCsv" value="csv" autocomplete="off">
            <label class="btn btn-outline-primary" for="nerveTestFormatCsv"><i class="bi bi-filetype-csv me-1"></i> CSV (.csv)</label>
        </div>

        <label class="form-label fw-semibold">ملف الاستيراد</label>
        <div class="pack-import-upload-area mb-3"
             id="nerveTestUploadArea"
             role="button"
             tabindex="0"
             aria-label="اختر ملف اختبار الأعصاب">
            <input type="file" id="nerveTestFileInput" accept=".md,.txt" class="visually-hidden">
            <div id="nerveTestUploadContent">
                <span class="pack-import-upload-area__format-badge" id="nerveTestFormatBadge">Markdown</span>
                <i class="bi bi-cloud-arrow-up display-6 text-primary mb-2 d-block"></i>
                <h6 class="mb-1">اسحب الملف هنا أو انقر للاختيار</h6>
                <p class="text-muted small mb-2" id="nerveTestAcceptHint">الصيغة: .md — الحد الأقصى 10 ميجابايت</p>
                <button type="button" class="btn btn-primary btn-sm" id="nerveTestBrowseBtn">
                    <i class="bi bi-folder2-open me-1"></i> تصفح واختر ملف
                </button>
            </div>
            <div id="nerveTestFileInfo" style="display: none;">
                <i class="bi bi-file-earmark-check display-6 text-success mb-2"></i>
                <h6 class="mb-1" id="nerveTestFileName"></h6>
                <p class="text-muted small mb-2" id="nerveTestFileSize"></p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="nerveTestReplaceFile">
                        <i class="bi bi-arrow-repeat me-1"></i> استبدال
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="nerveTestRemoveFile">
                        <i class="bi bi-x-circle me-1"></i> إزالة
                    </button>
                </div>
            </div>
        </div>

        <div id="nerveTestParseError" class="alert alert-danger d-none" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 mb-3 parse-action-row">
            <button type="button" class="btn btn-outline-primary btn-parse-file" id="nerveTestParseBtn" disabled>
                <i class="bi bi-search me-1"></i> تحليل الملف
            </button>
            <span class="align-self-center text-muted small" id="nerveTestParseHint">اختر ملفاً أولاً ثم اضغط تحليل</span>
        </div>

        <div id="nerveTestPreviewSection" style="display: none;">
            <h6 class="fw-semibold mb-2">معاينة (<span id="nerveTestPreviewCount">0</span> سؤال)</h6>
            <div class="preview-table border rounded p-2 mb-3" id="nerveTestPreviewTable"></div>

            <form action="{{ $importFormAction }}" method="POST" enctype="multipart/form-data" id="nerveTestImportForm">
                @csrf
                <input type="hidden" name="format" id="nerveTestImportFormat" value="md">
                <input type="hidden" name="class_id" id="nerveTestImportClassId" value="{{ old('class_id', $prefillClassId ?? ($lockedClassId ?? '')) }}">
                <input type="hidden" name="subject_id" id="nerveTestImportSubjectId" value="{{ old('subject_id', $prefillSubjectId ?? ($lockedSubjectId ?? '')) }}">
                <input type="hidden" name="unit_id" id="nerveTestImportUnitId" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                <input type="file" name="file" id="nerveTestImportFileInput" style="display: none;">

                <button type="submit" class="btn btn-success btn-lg" id="nerveTestImportBtn">
                    <i class="bi bi-cloud-download me-2"></i>
                    <span id="nerveTestImportBtnLabel">{{ $importSubmitLabel }}</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/nerve-test-import.js') }}" defer></script>
@endpush
