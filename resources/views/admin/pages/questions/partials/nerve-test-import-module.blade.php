@php
    $requireSubject = $requireSubject ?? empty($lockedSubject);
    $lockedSubjectId = !empty($lockedSubject) ? $lockedSubject->id : null;
    $lockedClassId = !empty($lockedSubject) ? $lockedSubject->class_id : null;
@endphp

<div class="card custom-card mb-3 nerve-test-import-module" id="nerveTestImportModule"
     data-parse-url="{{ route('admin.questions.nerve-test.parse') }}"
     data-import-url="{{ route('admin.questions.nerve-test.import') }}"
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

        <div class="btn-group mb-3" role="group" aria-label="صيغة الملف">
            <input type="radio" class="btn-check" name="nerveTestFormat" id="nerveTestFormatMd" value="md" checked autocomplete="off">
            <label class="btn btn-outline-primary" for="nerveTestFormatMd">Markdown (.md)</label>
            <input type="radio" class="btn-check" name="nerveTestFormat" id="nerveTestFormatCsv" value="csv" autocomplete="off">
            <label class="btn btn-outline-primary" for="nerveTestFormatCsv">CSV (.csv)</label>
        </div>

        <div class="upload-area nerve-test-upload-area mb-3" id="nerveTestUploadArea">
            <input type="file" id="nerveTestFileInput" accept=".md,.txt" style="display: none;">
            <div id="nerveTestUploadContent">
                <i class="bi bi-file-earmark-text display-6 text-muted mb-2"></i>
                <h6 class="mb-1">اختر ملف اختبار الأعصاب</h6>
                <p class="text-muted small mb-0" id="nerveTestAcceptHint">الصيغة: .md — الحد الأقصى 10 ميجابايت</p>
            </div>
            <div id="nerveTestFileInfo" style="display: none;">
                <i class="bi bi-file-earmark-check display-6 text-success mb-2"></i>
                <h6 class="mb-1" id="nerveTestFileName"></h6>
                <p class="text-muted small mb-2" id="nerveTestFileSize"></p>
                <button type="button" class="btn btn-sm btn-outline-danger" id="nerveTestRemoveFile">
                    <i class="bi bi-x-circle me-1"></i> إزالة
                </button>
            </div>
        </div>

        <div id="nerveTestParseError" class="alert alert-danger d-none" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-outline-primary" id="nerveTestParseBtn" disabled>
                <i class="bi bi-search me-1"></i> تحليل الملف
            </button>
        </div>

        <div id="nerveTestPreviewSection" style="display: none;">
            <h6 class="fw-semibold mb-2">معاينة (<span id="nerveTestPreviewCount">0</span> سؤال)</h6>
            <div class="preview-table border rounded p-2 mb-3" id="nerveTestPreviewTable"></div>

            <form action="{{ route('admin.questions.nerve-test.import') }}" method="POST" enctype="multipart/form-data" id="nerveTestImportForm">
                @csrf
                <input type="hidden" name="format" id="nerveTestImportFormat" value="md">
                <input type="hidden" name="class_id" id="nerveTestImportClassId" value="{{ old('class_id', $prefillClassId ?? ($lockedClassId ?? '')) }}">
                <input type="hidden" name="subject_id" id="nerveTestImportSubjectId" value="{{ old('subject_id', $prefillSubjectId ?? ($lockedSubjectId ?? '')) }}">
                <input type="hidden" name="unit_id" id="nerveTestImportUnitId" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                <input type="file" name="file" id="nerveTestImportFileInput" style="display: none;">

                <button type="submit" class="btn btn-success btn-lg" id="nerveTestImportBtn">
                    <i class="bi bi-cloud-download me-2"></i>
                    <span id="nerveTestImportBtnLabel">استيراد الأسئلة</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/nerve-test-import.js') }}" defer></script>
@endpush
