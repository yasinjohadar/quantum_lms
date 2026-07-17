@php
    $importFormAction = $importFormAction ?? route('admin.questions.import');
    $cancelUrl = $cancelUrl ?? route('admin.questions.index');
    $importSubmitLabel = $importSubmitLabel ?? 'بدء الاستيراد';
    $curriculumSync = $curriculumSync ?? true;
    $wizardId = $wizardId ?? 'excelImportWizard';
@endphp

<div class="excel-import-wizard" id="{{ $wizardId }}"
     data-curriculum-sync="{{ $curriculumSync ? '1' : '0' }}"
     data-math-preview-url="{{ route('admin.questions.math-preview') }}">

    <div class="card custom-card mb-3">
        <div class="card-body py-3">
            <ul class="nav nav-tabs form-wizard-1 import-steps-bar d-flex mb-0" role="list">
                <li class="nav-item active excel-step-item" data-step="1" role="listitem">
                    <span class="nav-link active" aria-current="step">
                        <i class="bi bi-cloud-upload"></i>
                        <span class="ms-1 d-none d-sm-inline">رفع الملف</span>
                    </span>
                </li>
                <li class="nav-item excel-step-item" data-step="2" role="listitem">
                    <span class="nav-link">
                        <i class="bi bi-columns"></i>
                        <span class="ms-1 d-none d-sm-inline">تحديد الأعمدة</span>
                    </span>
                </li>
                <li class="nav-item excel-step-item" data-step="3" role="listitem">
                    <span class="nav-link">
                        <i class="bi bi-eye"></i>
                        <span class="ms-1 d-none d-sm-inline">معاينة البيانات</span>
                    </span>
                </li>
                <li class="nav-item excel-step-item" data-step="4" role="listitem">
                    <span class="nav-link">
                        <i class="bi bi-check-circle"></i>
                        <span class="ms-1 d-none d-sm-inline">الاستيراد</span>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <div class="card custom-card mb-3 excel-upload-step">
        <div class="card-header">
            <div class="card-title">رفع ملف Excel/CSV</div>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="bi bi-calculator me-2"></i>
                <strong>المعادلات:</strong> اكتبها بصيغة LaTeX داخل <code>$...$</code>
                مثل: <code dir="ltr">ليكن $f(x)=\sqrt{x^{2}+4x+5}$ على $\mathbb{R}$</code>.
                عند الاستيراد ستظهر معاينة KaTeX ويجب تأكيدها قبل الحفظ.
            </div>
            <div class="upload-area excel-upload-area">
                <input type="file" class="excel-file-input" accept=".xlsx,.xls,.csv" style="display: none;">
                <div class="excel-upload-content">
                    <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                    <h5 class="mb-2">اسحب الملف هنا أو اضغط للاختيار</h5>
                    <p class="text-muted mb-0">الصيغ المدعومة: Excel (.xlsx, .xls) أو CSV (.csv)</p>
                    <p class="text-muted small mb-0">الحد الأقصى: 10 ميجابايت</p>
                </div>
                <div class="excel-file-info" style="display: none;"></div>
            </div>
        </div>
    </div>

    <div class="card custom-card mb-3 excel-mapping-step" style="display: none;">
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
                    <div class="column-mapping excel-required-mappings"></div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold mb-3">الحقول الاختيارية</h6>
                    <div class="column-mapping excel-optional-mappings"></div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="button" class="btn btn-primary excel-next-preview-btn" disabled>
                    <i class="bi bi-arrow-left me-2"></i> التالي: معاينة البيانات
                </button>
                <button type="button" class="btn btn-secondary excel-back-upload-btn">
                    <i class="bi bi-arrow-right me-2"></i> رجوع
                </button>
            </div>
        </div>
    </div>

    <div class="card custom-card mb-3 excel-preview-step" style="display: none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">معاينة البيانات والمعادلات</div>
            <div>
                <span class="badge bg-primary excel-preview-count">0</span> صف
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-3">
                <i class="bi bi-info-circle me-2"></i>
                يتم عرض أول 10 صفوف فقط للمعاينة. المعادلات تُطبَّع تلقائياً إلى LaTeX وتُعرض كما ستظهر للطالب (KaTeX).
            </div>
            <div class="table-responsive preview-table mb-3">
                <table class="table table-bordered table-hover excel-preview-table">
                    <thead class="table-light sticky-top">
                        <tr class="excel-preview-header"></tr>
                    </thead>
                    <tbody class="excel-preview-body"></tbody>
                </table>
            </div>

            <div class="card border mb-3">
                <div class="card-header py-2">
                    <strong><i class="bi bi-calculator me-1"></i> معاينة المعادلات بعد التطبيع</strong>
                </div>
                <div class="card-body excel-math-preview-list">
                    <p class="text-muted small mb-0">اضغط «تحديث معاينة المعادلات» بعد تحديد الأعمدة.</p>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input excel-math-confirm-check" type="checkbox" id="excelMathConfirm_{{ $wizardId }}">
                <label class="form-check-label" for="excelMathConfirm_{{ $wizardId }}">
                    أكّدت أن المعادلات تظهر بشكل صحيح في المعاينة أعلاه (مطلوب قبل الاستيراد)
                </label>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="button" class="btn btn-outline-primary excel-refresh-math-preview-btn">
                    <i class="bi bi-arrow-clockwise me-1"></i> تحديث معاينة المعادلات
                </button>
                <button type="button" class="btn btn-primary excel-next-import-btn" disabled>
                    <i class="bi bi-arrow-left me-2"></i> التالي: الاستيراد
                </button>
                <button type="button" class="btn btn-secondary excel-back-mapping-btn">
                    <i class="bi bi-arrow-right me-2"></i> رجوع
                </button>
            </div>
        </div>
    </div>

    <div class="card custom-card mb-3 excel-import-step" style="display: none;">
        <div class="card-header">
            <div class="card-title">جاهز للاستيراد</div>
        </div>
        <div class="card-body">
            <div class="file-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white excel-final-file-name"></h6>
                        <p class="mb-0 text-white-50 small excel-final-file-size"></p>
                    </div>
                    <div class="text-end">
                        <div class="text-white fw-bold fs-18 excel-final-row-count">0</div>
                        <div class="text-white-50 small">صف</div>
                    </div>
                </div>
            </div>

            <form action="{{ $importFormAction }}" method="POST" enctype="multipart/form-data" class="excel-import-form">
                @csrf
                <input type="hidden" name="class_id" class="excel-import-class-id" value="{{ old('class_id', $prefillClassId ?? '') }}">
                <input type="hidden" name="subject_id" class="excel-import-subject-id" value="{{ old('subject_id', $prefillSubjectId ?? '') }}">
                <input type="hidden" name="unit_id" class="excel-import-unit-id" value="{{ old('unit_id', $prefillUnitId ?? '') }}">
                <input type="file" name="file" class="excel-hidden-file-input" style="display: none;">
                <input type="hidden" name="column_mapping" class="excel-column-mapping-input">

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-lg excel-import-submit-btn">
                        <i class="bi bi-upload me-2"></i> {{ $importSubmitLabel }}
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg excel-back-preview-btn">
                        <i class="bi bi-arrow-right me-2"></i> رجوع
                    </button>
                    <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x me-2"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
