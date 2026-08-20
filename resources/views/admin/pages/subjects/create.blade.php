@extends('admin.layouts.master')

@section('page-title')
    إضافة مادة دراسية
@stop

@push('styles')
    @include('admin.pages.subjects.partials.subject-form-styles')
@endpush

@section('content')
    <div class="main-content app-content subject-form-page">
        <div class="container-fluid">

            <div class="subject-form-hero my-4">
                <div class="subject-form-hero__icon">
                    <i class="bi bi-journal-plus"></i>
                </div>
                <div class="subject-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد الدراسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة مادة</li>
                        </ol>
                    </nav>
                    <h4 class="subject-form-hero__title">إضافة مادة دراسية جديدة</h4>
                    <p class="subject-form-hero__subtitle">أدخل بيانات المادة، التسعير، وخيارات الظهور للطلاب</p>
                </div>
                <div class="subject-form-hero__actions">
                    @if($selectedClassId ?? request('class_id'))
                        <a href="{{ route('admin.classes.show', $selectedClassId ?? request('class_id')) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i> رجوع للصف
                        </a>
                    @else
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                        </a>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.subjects.store') }}" enctype="multipart/form-data">
                @csrf
                @if($selectedClassId || request('class_id'))
                    <input type="hidden" name="return_to_class_id" value="{{ $selectedClassId ?? request('class_id') }}">
                @endif

                {{-- البيانات الأساسية --}}
                <div class="subject-form-card">
                    <div class="subject-form-card__header">
                        <span class="subject-form-card__header-icon"><i class="bi bi-info-circle"></i></span>
                        <div class="subject-form-card__header-text">
                            <div class="subject-form-card__title">البيانات الأساسية</div>
                            <p class="subject-form-card__desc">الاسم، الصف الدراسي، الرابط الدائم، وترتيب الظهور</p>
                        </div>
                    </div>
                    <div class="subject-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="subject-form-field">
                                    <label class="form-label">اسم المادة <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="مثال: الرياضيات"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="subject-form-hint">
                                        <i class="bi bi-lightbulb"></i>
                                        <span>الاسم الظاهر في قوائم المواد وبطاقة المادة للطلاب.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="subject-form-field">
                                    <label class="form-label">الصف الدراسي <span class="text-danger">*</span></label>
                                    @if($selectedClassId && $selectedClass)
                                        <input type="text"
                                               class="form-control"
                                               value="{{ $selectedClass->name }} — {{ $selectedClass->stage?->name }}"
                                               readonly>
                                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                                        <div class="subject-form-hint">
                                            <i class="bi bi-lock"></i>
                                            <span>الصف والمرحلة محددة مسبقاً ولا يمكن تغييرها.</span>
                                        </div>
                                    @else
                                        <select name="class_id"
                                                class="form-select @error('class_id') is-invalid @enderror"
                                                aria-label="الصف الدراسي" required>
                                            <option value="">اختر الصف</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}"
                                                    {{ old('class_id', request('class_id')) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }} — {{ $class->stage?->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="subject-form-hint">
                                            <i class="bi bi-mortarboard"></i>
                                            <span>تربط المادة بهذا الصف وتحدد ظهورها ضمن برنامجه.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="subject-form-field">
                                    <label class="form-label">الرابط الدائم <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="text" name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           placeholder="subject-slug"
                                           value="{{ old('slug') }}">
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="subject-form-hint">
                                        <i class="bi bi-link-45deg"></i>
                                        <span>جزء الرابط في URL؛ يُنشأ تلقائياً إن تُرك فارغاً.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="subject-form-field">
                                    <label class="form-label">ترتيب العرض</label>
                                    <input type="number" name="order"
                                           class="form-control @error('order') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('order', 0) }}">
                                    @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="subject-form-hint">
                                        <i class="bi bi-sort-numeric-down"></i>
                                        <span>الرقم الأصغر يظهر أولاً ضمن مواد نفس الصف.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- التسعير --}}
                <div class="subject-form-card">
                    <div class="subject-form-card__header">
                        <span class="subject-form-card__header-icon"><i class="bi bi-currency-exchange"></i></span>
                        <div class="subject-form-card__header-text">
                            <div class="subject-form-card__title">التسعير</div>
                            <p class="subject-form-card__desc">العملة الافتراضية وأسعار المادة بعدة عملات</p>
                        </div>
                    </div>
                    <div class="subject-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="subject-form-field">
                                    <label for="default_currency_id" class="form-label">العملة الافتراضية</label>
                                    @php
                                        // عملة النظام الافتراضية (الليرة السورية) مُنتقاة مسبقاً لمادة جديدة.
                                        $preselectedCurrencyId = old('default_currency_id', \App\Models\Currency::getDefault()?->id);
                                    @endphp
                                    <select name="default_currency_id" id="default_currency_id" class="form-select @error('default_currency_id') is-invalid @enderror">
                                        <option value="">اختر العملة الافتراضية</option>
                                        @foreach(\App\Models\Currency::active()->ordered()->get() as $currency)
                                            <option value="{{ $currency->id }}" {{ (string) $preselectedCurrencyId === (string) $currency->id ? 'selected' : '' }}>
                                                {{ $currency->code }} - {{ $currency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_currency_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="subject-form-hint">
                                        <i class="bi bi-currency-dollar"></i>
                                        <span>العملة المعتمدة عند عرض السعر أو إتمام الدفع.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive border rounded">
                                    <table class="table subject-form-currency-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>العملة</th>
                                                <th style="width: 200px;">السعر</th>
                                                <th style="width: 120px;">تفعيل السعر</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(\App\Models\Currency::active()->ordered()->get() as $currency)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $currency->code }}</strong>
                                                        <span class="text-muted small">({{ $currency->name }})</span>
                                                    </td>
                                                    <td>
                                                        @php $priceActive = (bool) old('prices.'.$currency->id.'.is_active', true); @endphp
                                                        {{-- readonly حتى يُشغّل مفتاح «تفعيل السعر»؛ يُزامنه السكربت في أسفل الصفحة. --}}
                                                        <input type="number"
                                                               class="form-control form-control-sm price-input"
                                                               name="prices[{{ $currency->id }}][price]"
                                                               value="{{ old('prices.' . $currency->id . '.price', 0) }}"
                                                               step="0.01"
                                                               min="0"
                                                               data-currency-id="{{ $currency->id }}"
                                                               @if(! $priceActive) readonly @endif>
                                                        <input type="hidden" name="prices[{{ $currency->id }}][currency_id]" value="{{ $currency->id }}">
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input price-active"
                                                                   type="checkbox"
                                                                   name="prices[{{ $currency->id }}][is_active]"
                                                                   value="1"
                                                                   data-currency-id="{{ $currency->id }}"
                                                                   {{ $priceActive ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="subject-form-hint mt-2">
                                    <i class="bi bi-table"></i>
                                    <span>شغّل <strong>تفعيل السعر</strong> أولاً ليصبح حقل السعر قابلاً للتعديل؛ إيقافه يوقف البيع بتلك العملة ويحفظ السعر كما هو.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الوصف والوسائط --}}
                <div class="subject-form-card">
                    <div class="subject-form-card__header">
                        <span class="subject-form-card__header-icon"><i class="bi bi-text-paragraph"></i></span>
                        <div class="subject-form-card__header-text">
                            <div class="subject-form-card__title">الوصف والوسائط</div>
                            <p class="subject-form-card__desc">نص تسويقي وصورة الغلاف للمادة</p>
                        </div>
                    </div>
                    <div class="subject-form-card__body">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="subject-form-field">
                                    <label class="form-label">وصف المادة <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="شرح مختصر لمحتوى المادة أو المتطلبات"
                                              rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="subject-form-hint">
                                        <i class="bi bi-card-text"></i>
                                        <span>يظهر في صفحة المادة للطلاب إن كان القالب يعرضه.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="subject-form-field">
                                    <label class="form-label">صورة المادة <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="file" name="image"
                                           class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="subject-form-hint">
                                        <i class="bi bi-file-image"></i>
                                        <span>صورة مصغّرة في القوائم والبطاقات؛ يُفضّل صورة مربّعة وواضحة.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الظهور والحالة --}}
                <div class="subject-form-card">
                    <div class="subject-form-card__header">
                        <span class="subject-form-card__header-icon"><i class="bi bi-eye"></i></span>
                        <div class="subject-form-card__header-text">
                            <div class="subject-form-card__title">الظهور والحالة</div>
                            <p class="subject-form-card__desc">تفعيل المادة وإظهارها في صفحة الصف</p>
                        </div>
                    </div>
                    <div class="subject-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="subject-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                               id="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">المادة نشطة</label>
                                    </div>
                                    <div class="subject-form-hint mb-0 mt-2">
                                        <i class="bi bi-toggle-on"></i>
                                        <span>عند الإيقاف لا تُعرض المادة للتسجيل أو التصفح العام.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="subject-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="display_in_class"
                                               id="display_in_class" value="1"
                                               {{ old('display_in_class', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="display_in_class">عرض في صفحة الصف</label>
                                    </div>
                                    <div class="subject-form-hint mb-0 mt-2">
                                        <i class="bi bi-grid"></i>
                                        <span>يضبط إدراج المادة في قائمة مواد الصف الدراسي.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- خيارات التسعير المتقدمة --}}
                <div class="subject-form-card">
                    <div class="subject-form-card__header">
                        <span class="subject-form-card__header-icon"><i class="bi bi-sliders"></i></span>
                        <div class="subject-form-card__header-text">
                            <div class="subject-form-card__title">خيارات التسعير المتقدمة</div>
                            <p class="subject-form-card__desc">علاقة المادة بتسعير الصف وطريقة الشراء والعرض</p>
                        </div>
                    </div>
                    <div class="subject-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="subject-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_free_override"
                                               id="is_free_override" value="1"
                                               {{ old('is_free_override', false) ? 'checked' : '' }}
                                               onchange="toggleSubjectPricingOptions()">
                                        <label class="form-check-label" for="is_free_override">مجانية دائماً</label>
                                    </div>
                                    <div class="subject-form-hint mb-0 mt-2">
                                        <i class="bi bi-gift"></i>
                                        <span>تجعل المادة مجانية حتى لو كان الصف مدفوعاً.</span>
                                    </div>
                                </div>
                            </div>

                            @include('admin.pages.subjects.partials.free_join_auto_approve', [
                                'freeJoinDefault' => true,
                                'isFreeOverrideDefault' => false,
                            ])

                            <div class="col-lg-4">
                                <div class="subject-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="can_purchase_separately"
                                               id="can_purchase_separately" value="1"
                                               {{ old('can_purchase_separately', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="can_purchase_separately">شراء منفصل</label>
                                    </div>
                                    <div class="subject-form-hint mb-0 mt-2">
                                        <i class="bi bi-basket"></i>
                                        <span>يسمح بشراء المادة وحدها دون شراء كامل مواد الصف.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="subject-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_price"
                                               id="show_price" value="1"
                                               {{ old('show_price', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_price">إظهار السعر</label>
                                    </div>
                                    <div class="subject-form-hint mb-0 mt-2">
                                        <i class="bi bi-tag"></i>
                                        <span>يتحكم في إظهار أو إخفاء رقم السعر في الواجهة.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-1" id="custom_price_label_wrapper_subject">
                            <div class="col-lg-4">
                                <div class="subject-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="use_custom_price_label"
                                               id="use_custom_price_label_subject" value="1"
                                               {{ old('use_custom_price_label') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_price_label_subject">عرض كلمة بدل السعر</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="subject-form-field">
                                    <label for="custom_price_label_input_subject" class="form-label">الكلمة المعروضة</label>
                                    <input type="text" class="form-control" name="custom_price_label"
                                           id="custom_price_label_input_subject" maxlength="100"
                                           value="{{ old('custom_price_label', 'مدفوع') }}"
                                           placeholder="مدفوع">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="subject-form-footer">
                    @if($selectedClassId ?? request('class_id'))
                        <a href="{{ route('admin.classes.show', $selectedClassId ?? request('class_id')) }}" class="btn btn-outline-secondary">إلغاء</a>
                    @else
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> حفظ المادة
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
function toggleSubjectCustomPriceLabel() {
    var isFreeOverride = document.getElementById('is_free_override').checked;
    var showPrice = document.getElementById('show_price');
    var wrapper = document.getElementById('custom_price_label_wrapper_subject');
    var useCustom = document.getElementById('use_custom_price_label_subject');
    var labelInput = document.getElementById('custom_price_label_input_subject');
    var canUse = !isFreeOverride && showPrice && showPrice.checked;

    if (wrapper) {
        wrapper.style.display = canUse ? '' : 'none';
    }
    if (useCustom) {
        useCustom.disabled = !canUse;
        if (!canUse) {
            useCustom.checked = false;
        }
    }
    if (labelInput) {
        labelInput.disabled = !canUse || !useCustom || !useCustom.checked;
    }
}

function toggleSubjectPricingOptions() {
    var isFreeOverride = document.getElementById('is_free_override').checked;
    var canPurchaseSeparately = document.getElementById('can_purchase_separately');
    var showPrice = document.getElementById('show_price');
    var freeJoinBlock = document.getElementById('subject_free_join_block');

    if (isFreeOverride) {
        canPurchaseSeparately.checked = false;
        canPurchaseSeparately.disabled = true;
        showPrice.checked = true;
        showPrice.disabled = true;
        if (freeJoinBlock) {
            freeJoinBlock.classList.remove('d-none');
        }
    } else {
        canPurchaseSeparately.disabled = false;
        showPrice.disabled = false;
        if (freeJoinBlock) {
            freeJoinBlock.classList.add('d-none');
        }
    }
    toggleSubjectCustomPriceLabel();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleSubjectPricingOptions();
    var showPrice = document.getElementById('show_price');
    if (showPrice) {
        showPrice.addEventListener('change', toggleSubjectCustomPriceLabel);
    }
    var useCustom = document.getElementById('use_custom_price_label_subject');
    if (useCustom) {
        useCustom.addEventListener('change', toggleSubjectCustomPriceLabel);
    }
});
</script>

@include('admin.pages.subjects.partials.price-activation-script')
@stop
