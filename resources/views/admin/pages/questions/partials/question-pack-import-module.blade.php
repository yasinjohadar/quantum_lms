@php
    $requireSubject = $requireSubject ?? empty($lockedSubject);
    $lockedSubjectId = !empty($lockedSubject) ? $lockedSubject->id : null;
    $lockedClassId = !empty($lockedSubject) ? $lockedSubject->class_id : null;
    $parseUrl = $parseUrl ?? route('admin.questions.question-pack.parse');
    $importFormAction = $importFormAction ?? route('admin.questions.question-pack.import');
    $importSubmitLabel = $importSubmitLabel ?? 'استيراد الأسئلة';
@endphp

<div class="card custom-card mb-3 question-pack-import-module pack-import-module" id="questionPackImportModule"
     data-parse-url="{{ $parseUrl }}"
     data-import-url="{{ $importFormAction }}"
     data-require-subject="{{ $requireSubject ? '1' : '0' }}"
     data-locked-subject-id="{{ $lockedSubjectId }}"
     data-locked-class-id="{{ $lockedClassId }}">
    <div class="card-header">
        <div class="card-title mb-0">
            <i class="bi bi-collection me-2"></i>
            استيراد حزمة أسئلة (MD / CSV)
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            ملفات مثل «اختبار-أحياء» أو «اختبار-الحواس» (خيارات A–D). اختر <strong>كيف تُحفظ</strong> الأسئلة في بنك الأسئلة قبل التحليل.
            @if($requireSubject)
                <span class="d-block mt-1"><strong>مطلوب:</strong> اختر المادة من «الربط بالمنهج» في الشريط الجانبي قبل الاستيراد.</span>
            @endif
        </p>

        <div class="pack-import-steps" aria-hidden="true">
            <span class="pack-import-step is-active" id="questionPackStepFormat">1. النوع والصيغة</span>
            <span class="pack-import-step" id="questionPackStepFile">2. الملف</span>
            <span class="pack-import-step" id="questionPackStepParse">3. التحليل</span>
        </div>

        <label class="form-label fw-semibold">نوع الحفظ في بنك الأسئلة</label>
        <div class="btn-group mb-3 d-flex flex-wrap" role="group" aria-label="نوع الحفظ">
            <input type="radio" class="btn-check" name="questionPackTargetType" id="questionPackTargetSingle" value="single_choice" checked autocomplete="off">
            <label class="btn btn-outline-primary" for="questionPackTargetSingle">اختيار من متعدد (A–D)</label>
            <input type="radio" class="btn-check" name="questionPackTargetType" id="questionPackTargetFill" value="fill_blanks" autocomplete="off">
            <label class="btn btn-outline-primary" for="questionPackTargetFill">املأ الفراغ</label>
        </div>

        <label class="form-label fw-semibold">صيغة الملف</label>
        <div class="btn-group mb-3 format-toggle" role="group" aria-label="صيغة الملف">
            <input type="radio" class="btn-check" name="questionPackFormat" id="questionPackFormatMd" value="md" checked autocomplete="off">
            <label class="btn btn-outline-primary" for="questionPackFormatMd"><i class="bi bi-markdown me-1"></i> Markdown (.md)</label>
            <input type="radio" class="btn-check" name="questionPackFormat" id="questionPackFormatCsv" value="csv" autocomplete="off">
            <label class="btn btn-outline-primary" for="questionPackFormatCsv"><i class="bi bi-filetype-csv me-1"></i> CSV (.csv)</label>
        </div>

        <label class="form-label fw-semibold">ملف الاستيراد</label>
        <div class="pack-import-upload-area mb-3"
             id="questionPackUploadArea"
             role="button"
             tabindex="0"
             aria-label="اختر ملف الحزمة">
            <input type="file" id="questionPackFileInput" accept=".md,.txt" class="visually-hidden">
            <div id="questionPackUploadContent">
                <span class="pack-import-upload-area__format-badge" id="questionPackFormatBadge">Markdown</span>
                <i class="bi bi-cloud-arrow-up display-6 text-primary mb-2 d-block"></i>
                <h6 class="mb-1">اسحب الملف هنا أو انقر للاختيار</h6>
                <p class="text-muted small mb-2" id="questionPackAcceptHint">الصيغة: .md — الحد الأقصى 10 ميجابايت</p>
                <button type="button" class="btn btn-primary btn-sm" id="questionPackBrowseBtn">
                    <i class="bi bi-folder2-open me-1"></i> تصفح واختر ملف
                </button>
            </div>
            <div id="questionPackFileInfo" style="display: none;">
                <i class="bi bi-file-earmark-check display-6 text-success mb-2"></i>
                <h6 class="mb-1" id="questionPackFileName"></h6>
                <p class="text-muted small mb-2" id="questionPackFileSize"></p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="questionPackReplaceFile">
                        <i class="bi bi-arrow-repeat me-1"></i> استبدال
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="questionPackRemoveFile">
                        <i class="bi bi-x-circle me-1"></i> إزالة
                    </button>
                </div>
            </div>
        </div>

        <div id="questionPackParseError" class="alert alert-danger d-none" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 mb-3 parse-action-row">
            <button type="button" class="btn btn-outline-primary btn-parse-file" id="questionPackParseBtn" disabled>
                <i class="bi bi-search me-1"></i> تحليل الملف
            </button>
            <span class="align-self-center text-muted small" id="questionPackParseHint">اختر ملفاً أولاً ثم اضغط تحليل</span>
        </div>

        <div id="questionPackPreviewSection" style="display: none;">
            <h6 class="fw-semibold mb-2">معاينة (<span id="questionPackPreviewCount">0</span> سؤال)</h6>
            <div class="preview-table border rounded p-2 mb-3" id="questionPackPreviewTable"></div>

            <form action="{{ $importFormAction }}" method="POST" enctype="multipart/form-data" id="questionPackImportForm">
                @csrf
                <input type="hidden" name="format" id="questionPackImportFormat" value="md">
                <input type="hidden" name="target_type" id="questionPackImportTargetType" value="single_choice">
                <input type="hidden" name="class_id" id="questionPackImportClassId" value="{{ old('class_id', $prefillClassId ?? ($lockedClassId ?? '')) }}">
                <input type="hidden" name="subject_id" id="questionPackImportSubjectId" value="{{ old('subject_id', $prefillSubjectId ?? ($lockedSubjectId ?? '')) }}">
                <input type="hidden" name="unit_id" id="questionPackImportUnitId" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                <input type="file" name="file" id="questionPackImportFileInput" style="display: none;">

                <button type="submit" class="btn btn-success btn-lg" id="questionPackImportBtn">
                    <i class="bi bi-cloud-download me-2"></i>
                    <span id="questionPackImportBtnLabel">{{ $importSubmitLabel }}</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/question-pack-import.js') }}" defer></script>
@endpush
