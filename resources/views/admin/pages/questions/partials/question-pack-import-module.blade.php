@php
    $requireSubject = $requireSubject ?? empty($lockedSubject);
    $lockedSubjectId = !empty($lockedSubject) ? $lockedSubject->id : null;
    $lockedClassId = !empty($lockedSubject) ? $lockedSubject->class_id : null;
@endphp

<div class="card custom-card mb-3 question-pack-import-module" id="questionPackImportModule"
     data-parse-url="{{ route('admin.questions.question-pack.parse') }}"
     data-import-url="{{ route('admin.questions.question-pack.import') }}"
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

        <label class="form-label fw-semibold">نوع الحفظ في بنك الأسئلة</label>
        <div class="btn-group mb-3 d-flex flex-wrap" role="group" aria-label="نوع الحفظ">
            <input type="radio" class="btn-check" name="questionPackTargetType" id="questionPackTargetSingle" value="single_choice" checked autocomplete="off">
            <label class="btn btn-outline-primary" for="questionPackTargetSingle">اختيار من متعدد (A–D)</label>
            <input type="radio" class="btn-check" name="questionPackTargetType" id="questionPackTargetFill" value="fill_blanks" autocomplete="off">
            <label class="btn btn-outline-primary" for="questionPackTargetFill">املأ الفراغ</label>
        </div>

        <label class="form-label fw-semibold">صيغة الملف</label>
        <div class="btn-group mb-3" role="group" aria-label="صيغة الملف">
            <input type="radio" class="btn-check" name="questionPackFormat" id="questionPackFormatMd" value="md" checked autocomplete="off">
            <label class="btn btn-outline-secondary" for="questionPackFormatMd">Markdown (.md)</label>
            <input type="radio" class="btn-check" name="questionPackFormat" id="questionPackFormatCsv" value="csv" autocomplete="off">
            <label class="btn btn-outline-secondary" for="questionPackFormatCsv">CSV (.csv)</label>
        </div>

        <div class="upload-area nerve-test-upload-area mb-3" id="questionPackUploadArea">
            <input type="file" id="questionPackFileInput" accept=".md,.txt" style="display: none;">
            <div id="questionPackUploadContent">
                <i class="bi bi-file-earmark-spreadsheet display-6 text-muted mb-2"></i>
                <h6 class="mb-1">اختر ملف الحزمة</h6>
                <p class="text-muted small mb-0" id="questionPackAcceptHint">الصيغة: .md — الحد الأقصى 10 ميجابايت</p>
            </div>
            <div id="questionPackFileInfo" style="display: none;">
                <i class="bi bi-file-earmark-check display-6 text-success mb-2"></i>
                <h6 class="mb-1" id="questionPackFileName"></h6>
                <p class="text-muted small mb-2" id="questionPackFileSize"></p>
                <button type="button" class="btn btn-sm btn-outline-danger" id="questionPackRemoveFile">
                    <i class="bi bi-x-circle me-1"></i> إزالة
                </button>
            </div>
        </div>

        <div id="questionPackParseError" class="alert alert-danger d-none" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-outline-primary" id="questionPackParseBtn" disabled>
                <i class="bi bi-search me-1"></i> تحليل الملف
            </button>
        </div>

        <div id="questionPackPreviewSection" style="display: none;">
            <h6 class="fw-semibold mb-2">معاينة (<span id="questionPackPreviewCount">0</span> سؤال)</h6>
            <div class="preview-table border rounded p-2 mb-3" id="questionPackPreviewTable"></div>

            <form action="{{ route('admin.questions.question-pack.import') }}" method="POST" enctype="multipart/form-data" id="questionPackImportForm">
                @csrf
                <input type="hidden" name="format" id="questionPackImportFormat" value="md">
                <input type="hidden" name="target_type" id="questionPackImportTargetType" value="single_choice">
                <input type="hidden" name="class_id" id="questionPackImportClassId" value="{{ old('class_id', $prefillClassId ?? ($lockedClassId ?? '')) }}">
                <input type="hidden" name="subject_id" id="questionPackImportSubjectId" value="{{ old('subject_id', $prefillSubjectId ?? ($lockedSubjectId ?? '')) }}">
                <input type="hidden" name="unit_id" id="questionPackImportUnitId" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                <input type="file" name="file" id="questionPackImportFileInput" style="display: none;">

                <button type="submit" class="btn btn-success btn-lg" id="questionPackImportBtn">
                    <i class="bi bi-cloud-download me-2"></i>
                    <span id="questionPackImportBtnLabel">استيراد الأسئلة</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/question-pack-import.js') }}" defer></script>
@endpush
