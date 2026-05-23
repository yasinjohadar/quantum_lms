@extends('admin.layouts.master')

@section('page-title')
    إضافة مادة دراسية
@stop

@push('styles')
<style>
    .subject-form-block {
        border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.1));
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">إضافة مادة دراسية جديدة</h5>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subjects.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if($selectedClassId || request('class_id'))
                            <input type="hidden" name="return_to_class_id" value="{{ $selectedClassId ?? request('class_id') }}">
                        @endif

                        <div class="subject-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">البيانات الأساسية</h6>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="text" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="اسم المادة"
                                               value="{{ old('name') }}" required>
                                        <label>اسم المادة <span class="text-danger">*</span></label>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        الاسم الذي يراه الطلاب والزوار في قوائم المواد وبطاقة المادة؛ يُفضّل أن يكون واضحاً ومختصراً ومطابقاً لما تقدّمه المادة فعلياً.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    @if($selectedClassId && $selectedClass)
                                        <div class="form-floating">
                                            <input type="text"
                                                   class="form-control bg-light"
                                                   value="{{ $selectedClass->name }} - {{ $selectedClass->stage?->name }}"
                                                   readonly
                                                   style="cursor: not-allowed;">
                                            <label>الصف الدراسي <span class="text-danger">*</span></label>
                                        </div>
                                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle me-1"></i>الصف والمرحلة محددة مسبقاً ولا يمكن تغييرها.
                                        </small>
                                    @else
                                        <div class="form-floating">
                                            <select name="class_id"
                                                    class="form-select @error('class_id') is-invalid @enderror"
                                                    aria-label="الصف الدراسي" required>
                                                <option value="">اختر الصف</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}"
                                                        {{ old('class_id', request('class_id')) == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }} - {{ $class->stage?->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label>الصف الدراسي <span class="text-danger">*</span></label>
                                            @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted d-block mt-1">
                                            تربط المادة بهذا الصف الدراسي؛ يحدد ذلك ظهورها ضمن برنامج الصف، وارتباطها بتسعير الصف أو مزامنة الانضمامات عند قبول طلبات الصف.
                                        </small>
                                    @endif
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="text" name="slug"
                                               class="form-control @error('slug') is-invalid @enderror"
                                               placeholder="الرابط الدائم"
                                               value="{{ old('slug') }}">
                                        <label>الرابط الدائم (اختياري)</label>
                                        @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        جزء من عنوان URL للمادة (باللاتينية أو الأرقام عادةً، بدون مسافات). إن تركت الحقل فارغاً قد يُولَّد تلقائياً من الاسم؛ يجب أن يبقى فريداً بين المواد.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="number" name="order"
                                               class="form-control @error('order') is-invalid @enderror"
                                               placeholder="الترتيب"
                                               value="{{ old('order', 0) }}">
                                        <label>ترتيب العرض</label>
                                        @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        رقم صحيح: الأصغر يظهر أولاً ضمن قائمة مواد نفس الصف. استخدم 0 للاحتفاظ بالترتيب الافتراضي النسبي.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="subject-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">التسعير</h6>
                                </div>

                                <div class="col-lg-4">
                                    <label for="default_currency_id" class="form-label mb-1">العملة الافتراضية</label>
                                    <select name="default_currency_id" id="default_currency_id" class="form-select">
                                        <option value="">اختر العملة الافتراضية</option>
                                        @foreach(\App\Models\Currency::active()->ordered()->get() as $currency)
                                            <option value="{{ $currency->id }}" {{ old('default_currency_id') == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->code }} - {{ $currency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_currency_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block mt-1">
                                        العملة المرجعية عند عرض السعر أو عند إتمام الدفع إن لم يختر المستخدم عملة أخرى؛ تتناسق مع جدول الأسعار أدناه.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <h6 class="text-secondary small mb-2">الأسعار بعدة عملات</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>العملة</th>
                                                    <th>السعر</th>
                                                    <th>الحالة</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(\App\Models\Currency::active()->ordered()->get() as $currency)
                                                    <tr>
                                                        <td><strong>{{ $currency->code }}</strong> ({{ $currency->name }})</td>
                                                        <td>
                                                            <input type="number"
                                                                   class="form-control"
                                                                   name="prices[{{ $currency->id }}][price]"
                                                                   value="{{ old('prices.' . $currency->id . '.price', 0) }}"
                                                                   step="0.01"
                                                                   min="0">
                                                            <input type="hidden" name="prices[{{ $currency->id }}][currency_id]" value="{{ $currency->id }}">
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input"
                                                                       type="checkbox"
                                                                       name="prices[{{ $currency->id }}][is_active]"
                                                                       value="1"
                                                                       {{ old('prices.' . $currency->id . '.is_active', true) ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        <strong>السعر:</strong> المبلغ بهذه العملة. <strong>الحالة:</strong> تفعيل أو إيقاف استخدام هذا السطر للعملة. السعر 0 قد يعني مجاناً ضمن هذه العملة مع مراعاة خيار «المجاني دائماً» ونوع تسعير الصف.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="subject-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="وصف المادة" style="height: 120px">{{ old('description') }}</textarea>
                                        <label>وصف المادة (اختياري)</label>
                                        @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        نص يظهر في صفحة المادة للطلاب إن كان القالب يعرضه؛ استخدمه لشرح المحتوى أو المتطلبات (لا يؤثر على محتوى الدروس داخل النظام).
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="subject-form-block">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label">صورة المادة (اختياري)</label>
                                    <input type="file" name="image"
                                           class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block mt-1">
                                        صورة مصغّرة للمادة في القوائم والبطاقات؛ يُفضّل صورة مربّعة وواضحة وحجم ملف معقول (حد الرفع عادة 2 ميغابايت).
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="subject-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">الظهور والحالة</h6>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                               id="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">المادة نشطة</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        عند الإيقاف لا تُعرض المادة للتسجيل أو التصفح العام حسب منطق الموقع، دون حذف المحتوى لاحقاً إن أردت إعادة التفعيل.
                                    </small>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="display_in_class"
                                               id="display_in_class" value="1"
                                               {{ old('display_in_class', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="display_in_class">عرض في صفحة الصف</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يضبط إدراج المادة في واجهة صفحة الصف الدراسي. عطّله إن أردت أن تبقى المادة مخفية من قائمة مواد الصف ومتاحة عبر مسارات أخرى فقط.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="subject-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">خيارات التسعير المتقدمة</h6>
                                    <p class="text-muted small mb-0">تتحكم بعلاقة المادة بتسعير الصف وطريقة الشراء والعرض في الواجهة.</p>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_free_override"
                                               id="is_free_override" value="1"
                                               {{ old('is_free_override', false) ? 'checked' : '' }}
                                               onchange="toggleSubjectPricingOptions()">
                                        <label class="form-check-label" for="is_free_override">مجانية دائماً</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يفرض أن المادة مجانية لجميع المستخدمين حتى لو كان الصف مدفوعاً؛ عند التفعيل قد يعطّل «شراء منفصل» ويُثبت «إظهار السعر» آلياً وفق السكربت لتجنّب تعارض المنطق.
                                    </small>
                                </div>

                                @include('admin.pages.subjects.partials.free_join_auto_approve', [
                                    'freeJoinDefault' => true,
                                    'isFreeOverrideDefault' => false,
                                ])

                                <div class="col-lg-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="can_purchase_separately"
                                               id="can_purchase_separately" value="1"
                                               {{ old('can_purchase_separately', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="can_purchase_separately">شراء منفصل</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يسمح بشراء هذه المادة وحدها دون شراء كامل مواد الصف؛ عطّله إن أردت أن تكون المادة ضمن باقة الصف فقط.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_price"
                                               id="show_price" value="1"
                                               {{ old('show_price', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_price">إظهار السعر</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يتحكم في إظهار أو إخفاء رقم السعر في الواجهة؛ قد يُخفى السعر مع بقاء إمكانية الدفع وفق تصميم الموقع.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3 mt-2" id="custom_price_label_wrapper_subject">
                                <div class="col-lg-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="use_custom_price_label"
                                               id="use_custom_price_label_subject" value="1"
                                               {{ old('use_custom_price_label') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_price_label_subject">عرض كلمة بدل السعر</label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label for="custom_price_label_input_subject" class="form-label">الكلمة المعروضة</label>
                                    <input type="text" class="form-control" name="custom_price_label"
                                           id="custom_price_label_input_subject" maxlength="100"
                                           value="{{ old('custom_price_label', 'مدفوع') }}"
                                           placeholder="مدفوع">
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary px-4 me-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> حفظ المادة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
@stop
