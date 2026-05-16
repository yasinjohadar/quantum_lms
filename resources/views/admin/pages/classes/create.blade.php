@extends('admin.layouts.master')

@section('page-title')
    إضافة صف دراسي
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
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">إضافة صف دراسي جديد</h5>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><strong>خطأ:</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>يرجى تصحيح الأخطاء التالية:</strong>
                            <ul class="mb-0 mt-2 list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li><i class="bi bi-dot text-danger me-1"></i>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.classes.store') }}" enctype="multipart/form-data">
                        @csrf

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
                                               value="{{ old('name') }}" required>
                                        <label>اسم الصف <span class="text-danger">*</span></label>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        الاسم الظاهر في قوائم المراحل والصفوف وفي صفحة عرض الصف للطلاب؛ اختر اسماً يعبّر عن المستوى أو البرنامج (مثل «الصف الثالث متوسط»).
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
                                                        {{ old('stage_id', request('stage_id')) == $stage->id ? 'selected' : '' }}>
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
                                        تصنيف الصف ضمن مرحلة (ابتدائي، متوسط، ثانوي، إلخ)؛ يُستخدم للتنقل في الموقع وتجميع الصفوف في القوائم.
                                    </small>
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
                                        جزء الرابط في URL (أحرف لاتينية أو أرقام، بدون مسافات). يجب أن يكون فريداً بين الصفوف؛ إن تُرك فارغاً قد يُنشأ آلياً حسب إعدادات النظام.
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
                                        ترتيب ظهور هذا الصف ضمن صفوف نفس المرحلة؛ الرقم الأصغر عادةً يظهر أولاً في القوائم المرتبة حسب هذا الحقل.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3 align-items-start">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">التسعير الأساسي</h6>
                                    <p class="text-muted small mb-0">الحقل والمفاتيح التالية تحدد ما إذا كان شراء «الصف كاملاً» مجانياً أو مدفوعاً، وما يظهر للزائر.</p>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-floating">
                                        <input type="number" name="price" id="price_input_create"
                                               class="form-control @error('price') is-invalid @enderror"
                                               placeholder="السعر"
                                               value="{{ old('price', 0) }}"
                                               step="0.01"
                                               min="0">
                                        <label>السعر (مرجعي) <span class="text-danger">*</span></label>
                                        @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        السعر المرجعي للصف بعملة النظام/السجل الأساسي؛ يُستخدم مع خيار «الصف مجاني» ومع منطق قبول الطلبات. اضبطه على 0 لمسار مجاني، أو أدخل سعراً عندما يكون الصف مدفوعاً. يكمّل جدول «أسعار متعددة العملات» أدناه للعوامل والعملات الأخرى.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="is_free"
                                               id="is_free_create" value="1"
                                               {{ old('is_free', false) ? 'checked' : '' }}
                                               onchange="togglePriceFieldsCreate()">
                                        <label class="form-check-label" for="is_free_create">الصف مجاني</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        عند التفعيل يُعامل الصف كمسار مجاني: يُصفَر حقل السعر ويُعطّل تعديله، ويُفترض إظهار السعر في الواجهة بحيث لا يُخفى رسم «مجاني». يستخدم مع قواعد انضمام المسارات المجانية أدناه.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1" id="show_price_wrapper_create">
                                        <input class="form-check-input" type="checkbox" name="show_price"
                                               id="show_price_create" value="1"
                                               {{ old('show_price', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_price_create">إظهار السعر في الواجهة</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        يتحكم في ظهور رقم السعر في الواجهة الأمامية (مثل صفحة الصف). عند الإيقاف قد يعرض القالب نصاً بديلاً مثل «تواصل لمعرفة السعر» بدل الرقم؛ يُعطّى تلقائياً عند تفعيل «الصف مجاني».
                                    </small>
                                </div>
                            </div>

                            @php
                                $postPriceCreate = (float) old('price', 0);
                                $oldIsFreeCreate = old('is_free');
                                $isFreeEffectiveCreate = $oldIsFreeCreate !== null ? (bool) $oldIsFreeCreate : false;
                                $joinRequiresPaymentCreate = ! $isFreeEffectiveCreate && $postPriceCreate > 0;
                                $fjOldCreate = old('free_join_auto_approve');
                                $freeJoinCheckedCreate = $fjOldCreate === null ? true : filter_var($fjOldCreate, FILTER_VALIDATE_BOOLEAN);
                            @endphp
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="border rounded p-3 bg-light">
                                        <input type="hidden" name="free_join_auto_approve" id="free_join_auto_approve_value_create"
                                               value="{{ $joinRequiresPaymentCreate ? '1' : ($freeJoinCheckedCreate ? '1' : '0') }}">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="free_join_auto_approve_cb_create"
                                                   {{ $freeJoinCheckedCreate ? 'checked' : '' }}
                                                   {{ $joinRequiresPaymentCreate ? 'disabled' : '' }}
                                                   onchange="document.getElementById('free_join_auto_approve_value_create').value = this.checked ? '1' : '0'">
                                            <label class="form-check-label" for="free_join_auto_approve_cb_create">
                                                قبول الانضمام للمسار المجاني تلقائياً (بدون انتظار موافقة الإدارة)
                                            </label>
                                        </div>
                                        <small id="free_join_hint_paid_create" class="form-text text-muted d-block mt-2 {{ $joinRequiresPaymentCreate ? '' : 'd-none' }}">
                                            عندما يكون الدخول للصف يتطلّب دفعاً (سعر &gt; 0 وغير «مجاني»)، يُثبت النظام القبول التلقائي للانضمام في الخلفية؛ مربع الخيار يصبح للقراءة فقط لأن مسار «المراجعة اليدوية» لا ينطبق على الصف المدفوع بالمعنى نفسه.
                                        </small>
                                        <small id="free_join_hint_free_create" class="form-text text-muted d-block mt-2 {{ $joinRequiresPaymentCreate ? 'd-none' : '' }}">
                                            عند التعطيل، يُنشأ طلب انضمام للصف (أو المسار المجاني) بحالة «قيد المراجعة» حتى تتم الموافقة من لوحة التحكم. عند التفعيل يُقبل الطالب مباشرة ضمن سياسات الطلبات المجانية.
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
                                    <select name="default_currency_id" id="default_currency_id" class="form-select @error('default_currency_id') is-invalid @enderror">
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
                                        العملة المعتمدة عند عرض السعر الافتراضي أو إتمام الدفع إن لم يختر المستخدم عملة أخرى؛ يجب أن تتسق مع أعمدة جدول الأسعار التالي.
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
                                        <strong>السعر:</strong> قيمة اشتراك الصف بهذه العملة. <strong>الحالة:</strong> تفعيل أو إلغاء تفعيل هذا السطر (مثلاً إيقاف بيع الصف بالليرة دون حذف القيمة). الصفوف ذات السعر 0 تُفهم عادةً كمجانية في تلك العملة مع مراعاة «الصف مجاني» أعلاه.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">خصائص الصف (اختياري — حتى 10)</h6>
                                </div>
                                <div class="col-12">
                                    <p class="text-muted small mb-2">
                                        نقاط سريعة تُعرض في بطاقة الصف (مثل مدة البرنامج، عدد المواد، لغة الشرح…). اترَك الحقول الفارغة؛ يُخزّن فقط النصوص غير الفارغة وبترتيب الظهور.
                                    </p>
                                    <div id="class-features-create">
                                        @foreach(range(0, 9) as $i)
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">{{ $i + 1 }}</span>
                                                <input type="text" name="features[]" class="form-control @error('features.'.$i) is-invalid @enderror"
                                                       placeholder="نص الخاصية {{ $i + 1 }}"
                                                       value="{{ old('features.'.$i) }}">
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
                                                  placeholder="وصف الصف" style="height: 120px">{{ old('description') }}</textarea>
                                        <label>وصف الصف (اختياري)</label>
                                        @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        نص تسويقي أو تعليمي يظهر في صفحة الصف إذا كان القالب يعرضه؛ لا يغيّر هيكل المواد أو الدروس داخل النظام.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="url" name="whatsapp_group_url"
                                               class="form-control @error('whatsapp_group_url') is-invalid @enderror"
                                               placeholder="https://chat.whatsapp.com/..."
                                               value="{{ old('whatsapp_group_url') }}">
                                        <label>رابط مجموعة واتساب (اختياري)</label>
                                        @error('whatsapp_group_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        رابط دعوة المجموعة الرسمية من واتساب (يُنصح بلصق الرابط كاملاً من تطبيق واتساب). يُعرض للطالب بعد الشراء أو في صفحة الصف حسب التصميم.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="class-form-block">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="text-primary mb-1">وسائط وخيارات العرض</h6>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">صورة الصف (اختياري)</label>
                                    <input type="file" name="image"
                                           class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block mt-1">
                                        صورة الغلاف أو الأيقونة المعروضة في بطاقات الصف؛ يُفضّل صورة أفقية أو مربعة ووضوح جيد وملف ضمن حد الرفع (عادة 2 ميغابايت).
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                               id="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">الصف نشط</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        عند الإيقاف يُخفى الصف عن القوائم العامة والتسجيل حسب منطق الموقع، دون حذف المواد أو الاشتراكات السابقة.
                                    </small>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-check form-switch mt-2 pt-1">
                                        <input class="form-check-input" type="checkbox" name="allow_subjects_purchase"
                                               id="allow_subjects_purchase" value="1"
                                               {{ old('allow_subjects_purchase', false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_subjects_purchase">السماح بشراء المواد المتفرقة</label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-1">
                                        إذا فُعّل، تظهر في صفحة الصف خيار «شراء مواد متفرقة» بجانب شراء الصف كاملاً؛ يسمح للطالب باختيار مواد محددة فقط (مع احترام إعداد «شراء منفصل» لكل مادة). عطّله لإجبار شراء البرنامج كاملاً فقط عندما ينطبق المنطق التجاري لديك.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary px-4 me-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> حفظ الصف
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
function togglePriceFieldsCreate() {
    var isFree = document.getElementById('is_free_create').checked;
    var priceInput = document.getElementById('price_input_create');
    var showPriceInput = document.getElementById('show_price_create');

    priceInput.disabled = isFree;
    if (isFree) {
        priceInput.value = 0;
        showPriceInput.checked = true;
        showPriceInput.disabled = true;
    } else {
        showPriceInput.disabled = false;
    }
    syncFreeJoinAutoApproveCreate();
}

function syncFreeJoinAutoApproveCreate() {
    var isFree = document.getElementById('is_free_create').checked;
    var priceInput = document.getElementById('price_input_create');
    var price = priceInput ? (parseFloat(priceInput.value) || 0) : 0;
    var joinPaid = !isFree && price > 0;
    var hidden = document.getElementById('free_join_auto_approve_value_create');
    var cb = document.getElementById('free_join_auto_approve_cb_create');
    var hintPaid = document.getElementById('free_join_hint_paid_create');
    var hintFree = document.getElementById('free_join_hint_free_create');
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
    togglePriceFieldsCreate();
    var priceInputCreate = document.getElementById('price_input_create');
    if (priceInputCreate) {
        priceInputCreate.addEventListener('input', syncFreeJoinAutoApproveCreate);
    }
});
</script>
@stop
