@extends('admin.layouts.master')

@section('page-title')
    إضافة صف دراسي
@stop

@push('styles')
    @include('admin.pages.classes.partials.class-form-styles')
@endpush

@section('content')
    <div class="main-content app-content class-form-page">
        <div class="container-fluid">

            <div class="class-form-hero my-4">
                <div class="class-form-hero__icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="class-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">الصفوف الدراسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة صف</li>
                        </ol>
                    </nav>
                    <h4 class="class-form-hero__title">إضافة صف دراسي جديد</h4>
                    <p class="class-form-hero__subtitle">أدخل بيانات الصف، التسعير، والخيارات الظاهرة للطلاب في الواجهة</p>
                </div>
                <div class="class-form-hero__actions">
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                    </a>
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
                    <i class="bi bi-exclamation-triangle me-2"></i><strong>خطأ:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.classes.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- البيانات الأساسية --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-info-circle"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">البيانات الأساسية</div>
                            <p class="class-form-card__desc">الاسم، المرحلة، الرابط الدائم، وترتيب الظهور في القوائم</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="class-form-field">
                                    <label class="form-label">اسم الصف <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="مثال: الصف الثالث متوسط"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-lightbulb"></i>
                                        <span>الاسم الظاهر في قوائم المراحل وصفحة عرض الصف للطلاب.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="class-form-field">
                                    <label class="form-label">المرحلة الدراسية <span class="text-danger">*</span></label>
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
                                    @error('stage_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-lightbulb"></i>
                                        <span>تصنيف الصف ضمن مرحلة (ابتدائي، متوسط، ثانوي…) للتنقل وتجميع الصفوف.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="class-form-field">
                                    <label class="form-label">الرابط الدائم <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="text" name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           placeholder="class-slug"
                                           value="{{ old('slug') }}">
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-link-45deg"></i>
                                        <span>جزء الرابط في URL (أحرف لاتينية أو أرقام). يُنشأ تلقائياً إن تُرك فارغاً.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="class-form-field">
                                    <label class="form-label">ترتيب العرض</label>
                                    <input type="number" name="order"
                                           class="form-control @error('order') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('order', 0) }}">
                                    @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-sort-numeric-down"></i>
                                        <span>ترتيب ظهور الصف ضمن صفوف نفس المرحلة؛ الرقم الأصغر يظهر أولاً.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- التسعير الأساسي --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-currency-exchange"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">التسعير الأساسي</div>
                            <p class="class-form-card__desc">تحديد ما إذا كان شراء الصف مجانياً أو مدفوعاً وما يظهر للزائر</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="class-form-field">
                                    <label class="form-label" for="price_input_create">السعر (مرجعي) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price_input_create"
                                           class="form-control @error('price') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('price', 0) }}"
                                           step="0.01"
                                           min="0">
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-cash-stack"></i>
                                        <span>السعر المرجعي بعملة النظام؛ اضبطه على 0 لمسار مجاني.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_free"
                                               id="is_free_create" value="1"
                                               {{ old('is_free', false) ? 'checked' : '' }}
                                               onchange="togglePriceFieldsCreate()">
                                        <label class="form-check-label" for="is_free_create">الصف مجاني</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-gift"></i>
                                        <span>يُصفَر السعر ويُعطّل تعديله عند التفعيل.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box" id="show_price_wrapper_create">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_price"
                                               id="show_price_create" value="1"
                                               {{ old('show_price', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_price_create">إظهار السعر في الواجهة</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-eye"></i>
                                        <span>يتحكم في ظهور رقم السعر في الواجهة الأمامية.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-1" id="custom_price_label_wrapper_create">
                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="use_custom_price_label"
                                               id="use_custom_price_label_create" value="1"
                                               {{ old('use_custom_price_label') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_price_label_create">عرض كلمة بدل السعر</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="class-form-field">
                                    <label class="form-label" for="custom_price_label_input_create">الكلمة المعروضة</label>
                                    <input type="text" class="form-control" name="custom_price_label"
                                           id="custom_price_label_input_create" maxlength="100"
                                           value="{{ old('custom_price_label', 'مدفوع') }}"
                                           placeholder="مدفوع">
                                </div>
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
                        <div class="class-form-callout mt-4">
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
                            <div class="class-form-hint mb-0 mt-2" id="free_join_hint_paid_create" @unless($joinRequiresPaymentCreate) style="display:none" @endunless>
                                <i class="bi bi-info-circle"></i>
                                <span>عندما يتطلّب الدخول دفعاً، يُثبت النظام القبول التلقائي ويصبح الخيار للقراءة فقط.</span>
                            </div>
                            <div class="class-form-hint mb-0 mt-2" id="free_join_hint_free_create" @if($joinRequiresPaymentCreate) style="display:none" @endif>
                                <i class="bi bi-info-circle"></i>
                                <span>عند التعطيل يُنشأ طلب انضمام بحالة «قيد المراجعة» حتى الموافقة من لوحة التحكم.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- العملات --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-globe2"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">العملة الافتراضية والأسعار بعدة عملات</div>
                            <p class="class-form-card__desc">تحديد العملة المعتمدة وأسعار الصف لكل عملة نشطة</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="class-form-field">
                                    <label for="default_currency_id" class="form-label">العملة الافتراضية</label>
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
                                    <div class="class-form-hint">
                                        <i class="bi bi-currency-dollar"></i>
                                        <span>العملة المعتمدة عند عرض السعر الافتراضي أو إتمام الدفع.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive border rounded">
                                    <table class="table class-form-currency-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>العملة</th>
                                                <th style="width: 200px;">السعر</th>
                                                <th style="width: 100px;">الحالة</th>
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
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               name="prices[{{ $currency->id }}][price]"
                                                               value="{{ old('prices.' . $currency->id . '.price', 0) }}"
                                                               step="0.01"
                                                               min="0">
                                                        <input type="hidden" name="prices[{{ $currency->id }}][currency_id]" value="{{ $currency->id }}">
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch mb-0">
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
                                <div class="class-form-hint mt-2">
                                    <i class="bi bi-table"></i>
                                    <span><strong>السعر:</strong> قيمة اشتراك الصف بهذه العملة. <strong>الحالة:</strong> تفعيل أو إيقاف بيع الصف بهذه العملة.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin.pages.classes.partials.subscription-ends-field')

                {{-- خصائص الصف --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-stars"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">خصائص الصف <span class="text-muted fw-normal">(اختياري — حتى 10)</span></div>
                            <p class="class-form-card__desc">نقاط سريعة تُعرض في بطاقة الصف (مدة البرنامج، عدد المواد، لغة الشرح…)</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div id="class-features-create">
                            @foreach(range(0, 9) as $i)
                                <div class="class-form-feature-row">
                                    <span class="class-form-feature-num">{{ $i + 1 }}</span>
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

                {{-- الوصف والتواصل --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-text-paragraph"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">الوصف والتواصل</div>
                            <p class="class-form-card__desc">نص تسويقي ورابط مجموعة واتساب للطلاب</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="class-form-field">
                                    <label class="form-label">وصف الصف <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="نص تسويقي أو تعليمي يظهر في صفحة الصف"
                                              rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-card-text"></i>
                                        <span>يظهر في صفحة الصف إذا كان القالب يعرضه؛ لا يغيّر هيكل المواد داخل النظام.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="class-form-field">
                                    <label class="form-label">رابط مجموعة واتساب <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="url" name="whatsapp_group_url"
                                           class="form-control @error('whatsapp_group_url') is-invalid @enderror"
                                           placeholder="https://chat.whatsapp.com/..."
                                           value="{{ old('whatsapp_group_url') }}">
                                    @error('whatsapp_group_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>رابط دعوة المجموعة الرسمية؛ يُعرض للطالب بعد الشراء حسب التصميم.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- وسائط وخيارات --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-image"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">وسائط وخيارات العرض</div>
                            <p class="class-form-card__desc">صورة الغلاف، حالة النشر، وخيار شراء المواد المتفرقة</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="class-form-field">
                                    <label class="form-label">صورة الصف <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="file" name="image"
                                           class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-file-image"></i>
                                        <span>صورة الغلاف في بطاقات الصف؛ يُفضّل صورة أفقية أو مربعة ووضوح جيد.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                               id="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">الصف نشط</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-toggle-on"></i>
                                        <span>عند الإيقاف يُخفى الصف عن القوائم العامة دون حذف البيانات.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="allow_subjects_purchase"
                                               id="allow_subjects_purchase" value="1"
                                               {{ old('allow_subjects_purchase', false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_subjects_purchase">السماح بشراء المواد المتفرقة</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-basket"></i>
                                        <span>يظهر خيار شراء مواد محددة بجانب شراء الصف كاملاً.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="class-form-footer">
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                        إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> حفظ الصف
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
function toggleCustomPriceLabelCreate() {
    var isFree = document.getElementById('is_free_create').checked;
    var showPriceInput = document.getElementById('show_price_create');
    var wrapper = document.getElementById('custom_price_label_wrapper_create');
    var useCustom = document.getElementById('use_custom_price_label_create');
    var labelInput = document.getElementById('custom_price_label_input_create');
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
    toggleCustomPriceLabelCreate();
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
        if (hintPaid) { hintPaid.style.display = ''; }
        if (hintFree) { hintFree.style.display = 'none'; }
    } else {
        cb.disabled = false;
        hidden.value = cb.checked ? '1' : '0';
        if (hintPaid) { hintPaid.style.display = 'none'; }
        if (hintFree) { hintFree.style.display = ''; }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    togglePriceFieldsCreate();
    var priceInputCreate = document.getElementById('price_input_create');
    if (priceInputCreate) {
        priceInputCreate.addEventListener('input', syncFreeJoinAutoApproveCreate);
    }
    var showPriceCreate = document.getElementById('show_price_create');
    if (showPriceCreate) {
        showPriceCreate.addEventListener('change', toggleCustomPriceLabelCreate);
    }
    var useCustomCreate = document.getElementById('use_custom_price_label_create');
    if (useCustomCreate) {
        useCustomCreate.addEventListener('change', toggleCustomPriceLabelCreate);
    }
});
</script>
@stop
