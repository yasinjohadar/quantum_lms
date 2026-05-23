@extends('admin.layouts.master')

@section('page-title')
    تعديل صف دراسي
@stop

@push('styles')
<style>
    .class-form-block {
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
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
                <h5 class="page-title mb-0">تعديل الصف: {{ $class->name }}</h5>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.classes.update', $class->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="class-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">البيانات الأساسية</h6>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="text" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="اسم الصف"
                                               value="{{ old('name', $class->name) }}" required>
                                        <label>اسم الصف <span class="text-danger">*</span></label>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        الاسم الظاهر في قوائم المراحل والصفوف وبطاقة الصف للطلاب؛ حدّثه عند تغيير تسمية المسار الرسمية.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <select name="stage_id"
                                                class="form-select @error('stage_id') is-invalid @enderror"
                                                aria-label="المرحلة الدراسية" required>
                                            <option value="">اختر المرحلة</option>
                                            @foreach($stages as $stage)
                                                <option value="{{ $stage->id }}"
                                                    {{ old('stage_id', $class->stage_id) == $stage->id ? 'selected' : '' }}>
                                                    {{ $stage->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label>المرحلة الدراسية <span class="text-danger">*</span></label>
                                        @error('stage_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        تصنيف الصف ضمن مرحلة؛ يؤثر على التصفح والترتيب مع باقي صفوف نفس المرحلة.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="text" name="slug"
                                               class="form-control @error('slug') is-invalid @enderror"
                                               placeholder="الرابط الدائم"
                                               value="{{ old('slug', $class->slug) }}">
                                        <label>الرابط الدائم (اختياري)</label>
                                        @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        جزء عنوان URL الفريد لهذا الصف؛ تجنّب تغييره بعد نشر روابط خارجية إلا للضرورة (قد تحتاج إعادة توجيه يدوية).
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="number" name="order"
                                               class="form-control @error('order') is-invalid @enderror"
                                               placeholder="الترتيب"
                                               value="{{ old('order', $class->order) }}">
                                        <label>ترتيب العرض</label>
                                        @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        ترتيب ظهور الصف بين صفوف المرحلة؛ الأصغر غالباً يظهر أولاً في القوائم المرتبة بهذا العمود.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">التسعير الأساسي</h6>
                                    <p class="text-muted small mb-0">يضبط سعر «الصف كاملاً» وما يظهر للزائر، ويدمج مع جدول العملات أدناه.</p>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="number" name="price"
                                               class="form-control @error('price') is-invalid @enderror"
                                               placeholder="السعر"
                                               value="{{ old('price', $class->price ?? 0) }}"
                                               step="0.01"
                                               min="0"
                                               id="price_input_edit">
                                        <label>السعر (مرجعي) <span class="text-danger">*</span></label>
                                        @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        السعر المرجعي المخزّن للصف (يتماشى مع العملة الافتراضية والمنطق الخلفي). استخدم 0 مع «الصف مجاني» أو أدخل سعر الصف المدفوع؛ صفوف الأسعار متعددة العملات تفصّل الأسعار لكل عملة.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="is_free"
                                               id="is_free_edit" value="1"
                                               {{ old('is_free', $class->is_free ?? true) ? 'checked' : '' }}
                                               onchange="togglePriceFieldsEdit()">
                                        <label class="form-check-label" for="is_free_edit">الصف مجاني</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يجعل مسار «شراء/الانضمام للصف كاملاً» مجانياً: يصفّر السعر ويقيّد تعديله، ويضبط إظهار السعر في الواجهة وفق منطق النظام.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1" id="show_price_wrapper_edit">
                                        <input class="form-check-input" type="checkbox" name="show_price"
                                               id="show_price_edit" value="1"
                                               {{ old('show_price', $class->show_price ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_price_edit">إظهار السعر في الواجهة</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يتحكم في إظهار السعر أم استبداله بنص مثل «تواصل لمعرفة السعر» في الواجهة؛ يُجبَر سلوكاً عند تفعيل «الصف مجاني».
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3 mt-2" id="custom_price_label_wrapper_edit">
                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="use_custom_price_label"
                                               id="use_custom_price_label_edit" value="1"
                                               {{ old('use_custom_price_label', $class->use_custom_price_label ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_price_label_edit">عرض كلمة بدل السعر</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">عند التفعيل مع «إظهار السعر» يُعرض نص مخصص (مثل مدفوع) بدل المبلغ في الواجهة.</small>
                                </div>
                                <div class="col-lg-4">
                                    <label for="custom_price_label_input_edit" class="form-label">الكلمة المعروضة</label>
                                    <input type="text" class="form-control" name="custom_price_label"
                                           id="custom_price_label_input_edit" maxlength="100"
                                           value="{{ old('custom_price_label', $class->custom_price_label ?? 'مدفوع') }}"
                                           placeholder="مدفوع">
                                </div>
                            </div>

                            @php
                                $postPriceEdit = (float) old('price', $class->price ?? 0);
                                $oldIsFreeEdit = old('is_free');
                                $isFreeEffectiveEdit = $oldIsFreeEdit !== null ? (bool) $oldIsFreeEdit : (bool) ($class->is_free ?? false);
                                $joinRequiresPaymentEdit = ! $isFreeEffectiveEdit && $postPriceEdit > 0;
                                $fjOldEdit = old('free_join_auto_approve');
                                $freeJoinCheckedEdit = $fjOldEdit === null ? $class->effectiveFreeJoinAutoApprove() : filter_var($fjOldEdit, FILTER_VALIDATE_BOOLEAN);
                            @endphp
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="border rounded p-3 bg-light">
                                        <input type="hidden" name="free_join_auto_approve" id="free_join_auto_approve_value_edit"
                                               value="{{ $joinRequiresPaymentEdit ? '1' : ($freeJoinCheckedEdit ? '1' : '0') }}">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="free_join_auto_approve_cb_edit"
                                                   {{ $freeJoinCheckedEdit ? 'checked' : '' }}
                                                   {{ $joinRequiresPaymentEdit ? 'disabled' : '' }}
                                                   onchange="document.getElementById('free_join_auto_approve_value_edit').value = this.checked ? '1' : '0'">
                                            <label class="form-check-label" for="free_join_auto_approve_cb_edit">
                                                قبول الانضمام للمسار المجاني تلقائياً (بدون انتظار موافقة الإدارة)
                                            </label>
                                        </div>
                                        <small id="free_join_hint_paid_edit" class="form-text text-muted d-block mt-2 {{ $joinRequiresPaymentEdit ? '' : 'd-none' }}">
                                            للصف المدفوع (سعر &gt; 0 وبدون وضع «مجاني») يُخزَّن القبول التلقائي في الخلفية والخيار معطّل للتحرير لأن مسار المراجعة اليدوية المجاني لا ينطبق بنفس الطريقة.
                                        </small>
                                        <small id="free_join_hint_free_edit" class="form-text text-muted d-block mt-2 {{ $joinRequiresPaymentEdit ? 'd-none' : '' }}">
                                            عند الإيقاف تبقى طلبات الانضمام للمسار المجاني قيد المراجعة حتى موافقة الإدارة؛ عند التفعيل يقبل النظام الطلب مباشرة ضمن سياسات الطلبات.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">العملة الافتراضية والأسعار بعدة عملات</h6>
                                </div>

                                <div class="col-lg-4">
                                    <label for="default_currency_id" class="form-label mb-1">العملة الافتراضية</label>
                                    <select name="default_currency_id" id="default_currency_id" class="form-select">
                                        <option value="">اختر العملة الافتراضية</option>
                                        @foreach(\App\Models\Currency::active()->ordered()->get() as $currency)
                                            <option value="{{ $currency->id }}" {{ old('default_currency_id', $class->default_currency_id) == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->code }} - {{ $currency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_currency_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block mt-1">
                                        العملة الافتراضية لعرض السعر والدفع؛ ربطها مع أسعار الجدول يمنع تناقض العرض للمستخدم.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>العملة</th>
                                                    <th>السعر</th>
                                                    <th>الحالة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pricesTableBody">
                                                @php
                                                    $existingPrices = $class->prices()->with('currency')->get()->keyBy('currency_id');
                                                    $currencies = \App\Models\Currency::active()->ordered()->get();
                                                @endphp
                                                @foreach($currencies as $currency)
                                                    @php
                                                        $price = $existingPrices->get($currency->id);
                                                    @endphp
                                                    <tr data-currency-id="{{ $currency->id }}">
                                                        <td><strong>{{ $currency->code }}</strong> ({{ $currency->name }})</td>
                                                        <td>
                                                            <input type="number"
                                                                   class="form-control price-input"
                                                                   name="prices[{{ $currency->id }}][price]"
                                                                   value="{{ $price ? $price->price : 0 }}"
                                                                   step="0.01"
                                                                   min="0"
                                                                   data-currency-id="{{ $currency->id }}">
                                                            <input type="hidden" name="prices[{{ $currency->id }}][currency_id]" value="{{ $currency->id }}">
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input price-active"
                                                                       type="checkbox"
                                                                       name="prices[{{ $currency->id }}][is_active]"
                                                                       value="1"
                                                                       {{ $price && $price->is_active ? 'checked' : '' }}
                                                                       data-currency-id="{{ $currency->id }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($price)
                                                                <input type="hidden" name="prices[{{ $currency->id }}][id]" value="{{ $price->id }}">
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        حدّث السعر لكل عملة؛ عمود الحالة يفعّل أو يوقف بيع الصف بتلك العملة. عمود «الإجراءات» يحتفظ بمعرف السجل عند التحديث.
                                    </small>
                                </div>
                            </div>
                        </div>

                        @php
                            $editFeatureLabels = $class->features->pluck('label')->values()->all();
                            $editFeatureLabels = array_pad($editFeatureLabels, 10, '');
                        @endphp
                        <div class="class-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">خصائص الصف (اختياري — حتى 10)</h6>
                                    <p class="text-muted small mb-2">
                                        يُعاد بناء القائمة كاملة عند الحفظ: النصوص الفارغة تُتجاهل وبنفس الترتيب. استخدمها لنقاط مثل المدة أو عدد المواد.
                                    </p>
                                </div>
                                <div class="col-12">
                                    <div id="class-features-edit">
                                        @foreach(range(0, 9) as $i)
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">{{ $i + 1 }}</span>
                                                <input type="text" name="features[]" class="form-control @error('features.'.$i) is-invalid @enderror"
                                                       placeholder="نص الخاصية {{ $i + 1 }}"
                                                       value="{{ old('features.'.$i, $editFeatureLabels[$i] ?? '') }}">
                                                @error('features.'.$i)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="وصف الصف" style="height: 120px">{{ old('description', $class->description) }}</textarea>
                                        <label>وصف الصف (اختياري)</label>
                                        @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        وصف تسويقي أو تعليمي لصفحة الصف؛ لا يغيّر هيكل المواد المخزّن في لوحة التحكم.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="url" name="whatsapp_group_url"
                                               class="form-control @error('whatsapp_group_url') is-invalid @enderror"
                                               placeholder="https://chat.whatsapp.com/..."
                                               value="{{ old('whatsapp_group_url', $class->whatsapp_group_url ?? '') }}">
                                        <label>رابط مجموعة واتساب (اختياري)</label>
                                        @error('whatsapp_group_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        رابط دعوة مجموعة واتساب الرسمية؛ تأكد أنه لا يزال صالحاً بعد انتهاء صلاحية بعض روابط الدعوة.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">وسائط وخيارات العرض</h6>
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">صورة الصف (اختياري)</label>
                                    @if ($class->image)
                                        <div class="mb-2">
                                            <img src="{{ media_public_url($class->image) }}" alt="{{ $class->name }}"
                                                 class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                        </div>
                                    @endif
                                    <input type="file" name="image"
                                           class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block mt-1">
                                        رفع صورة جديدة يستبدل السابقة في التخزين حسب منطق الخادم؛ استخدم أبعاداً مناسبة للبطاقات.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                               id="is_active" value="1"
                                               {{ old('is_active', $class->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">الصف نشط</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        إيقاف التفعيل يخفي الصف عن التسجيل الجديد دون حذف البيانات أو اشتراكات قائمة.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="allow_subjects_purchase"
                                               id="allow_subjects_purchase" value="1"
                                               {{ old('allow_subjects_purchase', $class->allow_subjects_purchase ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_subjects_purchase">السماح بشراء المواد المتفرقة</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يظهر خيار «شراء مواد متفرقة» في واجهة صفحة الصف أمام الطالب؛ يعمل مع إعداد كل مادة «شراء منفصل». عطّله لإرغام شراء البرنامج كاملاً فقط.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary px-4 me-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> حفظ التعديلات
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
function toggleCustomPriceLabelEdit() {
    var isFree = document.getElementById('is_free_edit').checked;
    var showPriceInput = document.getElementById('show_price_edit');
    var wrapper = document.getElementById('custom_price_label_wrapper_edit');
    var useCustom = document.getElementById('use_custom_price_label_edit');
    var labelInput = document.getElementById('custom_price_label_input_edit');
    var canUse = !isFree && showPriceInput && showPriceInput.checked;

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

function togglePriceFieldsEdit() {
    var isFree = document.getElementById('is_free_edit').checked;
    var priceInput = document.getElementById('price_input_edit');
    var showPriceInput = document.getElementById('show_price_edit');

    priceInput.disabled = isFree;
    if (isFree) {
        priceInput.value = 0;
        showPriceInput.checked = true;
        showPriceInput.disabled = true;
    } else {
        showPriceInput.disabled = false;
    }
    toggleCustomPriceLabelEdit();
    syncFreeJoinAutoApproveEdit();
}

function syncFreeJoinAutoApproveEdit() {
    var isFree = document.getElementById('is_free_edit').checked;
    var priceInput = document.getElementById('price_input_edit');
    var price = priceInput ? (parseFloat(priceInput.value) || 0) : 0;
    var joinPaid = !isFree && price > 0;
    var hidden = document.getElementById('free_join_auto_approve_value_edit');
    var cb = document.getElementById('free_join_auto_approve_cb_edit');
    var hintPaid = document.getElementById('free_join_hint_paid_edit');
    var hintFree = document.getElementById('free_join_hint_free_edit');
    if (!hidden || !cb) {
        return;
    }
    if (joinPaid) {
        cb.disabled = true;
        hidden.value = '1';
        if (hintPaid) { hintPaid.classList.remove('d-none'); }
        if (hintFree) { hintFree.classList.add('d-none'); }
    } else {
        cb.disabled = false;
        hidden.value = cb.checked ? '1' : '0';
        if (hintPaid) { hintPaid.classList.add('d-none'); }
        if (hintFree) { hintFree.classList.remove('d-none'); }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    togglePriceFieldsEdit();
    var priceInputEdit = document.getElementById('price_input_edit');
    if (priceInputEdit) {
        priceInputEdit.addEventListener('input', syncFreeJoinAutoApproveEdit);
    }
    var showPriceEdit = document.getElementById('show_price_edit');
    if (showPriceEdit) {
        showPriceEdit.addEventListener('change', toggleCustomPriceLabelEdit);
    }
    var useCustomEdit = document.getElementById('use_custom_price_label_edit');
    if (useCustomEdit) {
        useCustomEdit.addEventListener('change', toggleCustomPriceLabelEdit);
    }
});
</script>
@stop
