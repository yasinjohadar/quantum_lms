@extends('admin.layouts.master')

@section('page-title')
    تعديل صف دراسي
@stop

@push('styles')
    @include('admin.pages.classes.partials.class-form-styles')
    <style>
        .class-form-image-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--cf-border, #e9ecef);
        }
    </style>
@endpush

@section('content')
    <div class="main-content app-content class-form-page">
        <div class="container-fluid">

            <div class="class-form-hero my-4">
                <div class="class-form-hero__icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div class="class-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">الصفوف الدراسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل صف</li>
                        </ol>
                    </nav>
                    <h4 class="class-form-hero__title">تعديل الصف: {{ $class->name }}</h4>
                    <p class="class-form-hero__subtitle">حدّث بيانات الصف، التسعير، والخيارات الظاهرة للطلاب</p>
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

            <form method="POST" action="{{ route('admin.classes.update', $class->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                                           value="{{ old('name', $class->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-lightbulb"></i>
                                        <span>الاسم الظاهر في قوائم المراحل وبطاقة الصف للطلاب.</span>
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
                                                {{ old('stage_id', $class->stage_id) == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('stage_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-lightbulb"></i>
                                        <span>يؤثر على التصفح والترتيب مع باقي صفوف نفس المرحلة.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="class-form-field">
                                    <label class="form-label">الرابط الدائم <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="text" name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           placeholder="class-slug"
                                           value="{{ old('slug', $class->slug) }}">
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-link-45deg"></i>
                                        <span>تجنّب تغييره بعد نشر روابط خارجية إلا للضرورة.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="class-form-field">
                                    <label class="form-label">ترتيب العرض</label>
                                    <input type="number" name="order"
                                           class="form-control @error('order') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('order', $class->order) }}">
                                    @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-sort-numeric-down"></i>
                                        <span>الرقم الأصغر يظهر أولاً في القوائم المرتبة بهذا الحقل.</span>
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
                            <p class="class-form-card__desc">يضبط سعر «الصف كاملاً» وما يظهر للزائر، ويدمج مع جدول العملات</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="class-form-field">
                                    <label class="form-label" for="price_input_edit">السعر (مرجعي) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price_input_edit"
                                           class="form-control @error('price') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('price', $class->price ?? 0) }}"
                                           step="0.01"
                                           min="0">
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-cash-stack"></i>
                                        <span>السعر المرجعي المخزّن للصف؛ استخدم 0 مع «الصف مجاني».</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_free"
                                               id="is_free_edit" value="1"
                                               {{ old('is_free', $class->is_free ?? true) ? 'checked' : '' }}
                                               onchange="togglePriceFieldsEdit()">
                                        <label class="form-check-label" for="is_free_edit">الصف مجاني</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-gift"></i>
                                        <span>يجعل مسار شراء/الانضمام للصف كاملاً مجانياً.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box" id="show_price_wrapper_edit">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_price"
                                               id="show_price_edit" value="1"
                                               {{ old('show_price', $class->show_price ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_price_edit">إظهار السعر في الواجهة</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-eye"></i>
                                        <span>يتحكم في إظهار السعر أم استبداله بنص في الواجهة.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-1" id="custom_price_label_wrapper_edit">
                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="use_custom_price_label"
                                               id="use_custom_price_label_edit" value="1"
                                               {{ old('use_custom_price_label', $class->use_custom_price_label ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_price_label_edit">عرض كلمة بدل السعر</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="class-form-field">
                                    <label class="form-label" for="custom_price_label_input_edit">الكلمة المعروضة</label>
                                    <input type="text" class="form-control" name="custom_price_label"
                                           id="custom_price_label_input_edit" maxlength="100"
                                           value="{{ old('custom_price_label', $class->custom_price_label ?? 'مدفوع') }}"
                                           placeholder="مدفوع">
                                </div>
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
                        <div class="class-form-callout mt-4">
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
                            <div class="class-form-hint mb-0 mt-2" id="free_join_hint_paid_edit" @unless($joinRequiresPaymentEdit) style="display:none" @endunless>
                                <i class="bi bi-info-circle"></i>
                                <span>للصف المدفوع يُخزَّن القبول التلقائي والخيار معطّل للتحرير.</span>
                            </div>
                            <div class="class-form-hint mb-0 mt-2" id="free_join_hint_free_edit" @if($joinRequiresPaymentEdit) style="display:none" @endif>
                                <i class="bi bi-info-circle"></i>
                                <span>عند الإيقاف تبقى طلبات الانضمام قيد المراجعة حتى موافقة الإدارة.</span>
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
                            <p class="class-form-card__desc">تحديث العملة المعتمدة وأسعار الصف لكل عملة نشطة</p>
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
                                            <option value="{{ $currency->id }}" {{ old('default_currency_id', $class->default_currency_id) == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->code }} - {{ $currency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_currency_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-currency-dollar"></i>
                                        <span>العملة الافتراضية لعرض السعر والدفع.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                @php
                                    $existingPrices = $class->prices()->with('currency')->get()->keyBy('currency_id');
                                    $currencies = \App\Models\Currency::active()->ordered()->get();
                                @endphp
                                <div class="table-responsive border rounded">
                                    <table class="table class-form-currency-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>العملة</th>
                                                <th style="width: 200px;">السعر</th>
                                                <th style="width: 100px;">الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pricesTableBody">
                                            @foreach($currencies as $currency)
                                                @php $price = $existingPrices->get($currency->id); @endphp
                                                <tr data-currency-id="{{ $currency->id }}">
                                                    <td>
                                                        <strong>{{ $currency->code }}</strong>
                                                        <span class="text-muted small">({{ $currency->name }})</span>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm price-input"
                                                               name="prices[{{ $currency->id }}][price]"
                                                               value="{{ $price ? $price->price : 0 }}"
                                                               step="0.01"
                                                               min="0"
                                                               data-currency-id="{{ $currency->id }}">
                                                        <input type="hidden" name="prices[{{ $currency->id }}][currency_id]" value="{{ $currency->id }}">
                                                        @if($price)
                                                            <input type="hidden" name="prices[{{ $currency->id }}][id]" value="{{ $price->id }}">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input price-active"
                                                                   type="checkbox"
                                                                   name="prices[{{ $currency->id }}][is_active]"
                                                                   value="1"
                                                                   {{ $price && $price->is_active ? 'checked' : '' }}
                                                                   data-currency-id="{{ $currency->id }}">
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="class-form-hint mt-2">
                                    <i class="bi bi-table"></i>
                                    <span>حدّث السعر لكل عملة؛ عمود الحالة يفعّل أو يوقف بيع الصف بتلك العملة.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $editFeatureLabels = $class->features->pluck('label')->values()->all();
                    $editFeatureLabels = array_pad($editFeatureLabels, 10, '');
                @endphp

                {{-- خصائص الصف --}}
                <div class="class-form-card">
                    <div class="class-form-card__header">
                        <span class="class-form-card__header-icon"><i class="bi bi-stars"></i></span>
                        <div class="class-form-card__header-text">
                            <div class="class-form-card__title">خصائص الصف <span class="text-muted fw-normal">(اختياري — حتى 10)</span></div>
                            <p class="class-form-card__desc">يُعاد بناء القائمة كاملة عند الحفظ؛ النصوص الفارغة تُتجاهل</p>
                        </div>
                    </div>
                    <div class="class-form-card__body">
                        <div id="class-features-edit">
                            @foreach(range(0, 9) as $i)
                                <div class="class-form-feature-row">
                                    <span class="class-form-feature-num">{{ $i + 1 }}</span>
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
                                              placeholder="وصف تسويقي أو تعليمي لصفحة الصف"
                                              rows="4">{{ old('description', $class->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-card-text"></i>
                                        <span>لا يغيّر هيكل المواد المخزّن في لوحة التحكم.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="class-form-field">
                                    <label class="form-label">رابط مجموعة واتساب <span class="text-muted fw-normal">(اختياري)</span></label>
                                    <input type="url" name="whatsapp_group_url"
                                           class="form-control @error('whatsapp_group_url') is-invalid @enderror"
                                           placeholder="https://chat.whatsapp.com/..."
                                           value="{{ old('whatsapp_group_url', $class->whatsapp_group_url ?? '') }}">
                                    @error('whatsapp_group_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>تأكد أن الرابط لا يزال صالحاً بعد انتهاء صلاحية بعض روابط الدعوة.</span>
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
                                    @if ($class->image)
                                        <div class="mb-2">
                                            <img src="{{ media_public_url($class->image) }}" alt="{{ $class->name }}"
                                                 class="class-form-image-preview">
                                        </div>
                                    @endif
                                    <input type="file" name="image"
                                           class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="class-form-hint">
                                        <i class="bi bi-file-image"></i>
                                        <span>رفع صورة جديدة يستبدل السابقة في التخزين.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                               id="is_active" value="1"
                                               {{ old('is_active', $class->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">الصف نشط</label>
                                    </div>
                                    <div class="class-form-hint mb-0 mt-2">
                                        <i class="bi bi-toggle-on"></i>
                                        <span>إيقاف التفعيل يخفي الصف عن التسجيل الجديد دون حذف البيانات.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="class-form-switch-box">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="allow_subjects_purchase"
                                               id="allow_subjects_purchase" value="1"
                                               {{ old('allow_subjects_purchase', $class->allow_subjects_purchase ?? false) ? 'checked' : '' }}>
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
                        <i class="bi bi-check-lg me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
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
