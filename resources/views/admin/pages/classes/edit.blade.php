@extends('admin.layouts.master')

@section('page-title')
    تعديل صف دراسي
@stop

@section('css')
@stop

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

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">التسعير</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" name="price"
                                           class="form-control @error('price') is-invalid @enderror"
                                           placeholder="السعر"
                                           value="{{ old('price', $class->price ?? 0) }}"
                                           step="0.01"
                                           min="0"
                                           id="price_input_edit">
                                    <label>السعر (ر.س) <span class="text-danger">*</span></label>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">اتركه 0 إذا كان الصف مجانياً</small>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_free"
                                           id="is_free_edit" value="1"
                                           {{ old('is_free', $class->is_free ?? true) ? 'checked' : '' }}
                                           onchange="togglePriceFieldsEdit()">
                                    <label class="form-check-label" for="is_free_edit">الصف مجاني</label>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-3" id="show_price_wrapper_edit">
                                    <input class="form-check-input" type="checkbox" name="show_price"
                                           id="show_price_edit" value="1"
                                           {{ old('show_price', $class->show_price ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_price_edit">
                                        إظهار السعر في الواجهة
                                        <i class="fas fa-info-circle text-muted ms-1" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="عند تعطيل هذا الخيار لن يظهر سعر الكورس للمستخدمين في الواجهة الأمامية."></i>
                                    </label>
                                </div>
                            </div>

                            @php
                                $editFeatureLabels = $class->features->pluck('label')->values()->all();
                                $editFeatureLabels = array_pad($editFeatureLabels, 10, '');
                            @endphp
                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">خصائص الصف (اختياري - حتى 10)</h6>
                                <p class="text-muted small mb-2">أضف نصوصاً للخصائص التي تريد إظهارها مع الصف. اترك الحقول الفارغة دون استخدام.</p>
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

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="وصف الصف" style="height: 100px">{{ old('description', $class->description) }}</textarea>
                                    <label>وصف الصف (اختياري)</label>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="url" name="whatsapp_group_url"
                                           class="form-control @error('whatsapp_group_url') is-invalid @enderror"
                                           placeholder="https://chat.whatsapp.com/..."
                                           value="{{ old('whatsapp_group_url', $class->whatsapp_group_url ?? '') }}">
                                    <label>رابط مجموعة واتساب (اختياري)</label>
                                    @error('whatsapp_group_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">مثال: https://chat.whatsapp.com/xxxxxxxxxxxxx</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة الصف (اختياري)</label>
                                @if ($class->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$class->image) }}" alt="{{ $class->name }}"
                                             class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="file" name="image"
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/*">
                                @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة Open Graph (اختياري)</label>
                                @if ($class->og_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$class->og_image) }}" alt="{{ $class->name }}"
                                             class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="file" name="og_image"
                                       class="form-control @error('og_image') is-invalid @enderror"
                                       accept="image/*">
                                @error('og_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1"
                                        {{ old('is_active', $class->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">الصف نشط</label>
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="allow_subjects_purchase"
                                           id="allow_subjects_purchase" value="1"
                                           {{ old('allow_subjects_purchase', $class->allow_subjects_purchase ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_subjects_purchase">السماح بشراء المواد المتفرقة</label>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">التسعير</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="default_currency_id" class="form-label">العملة الافتراضية</label>
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
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">الأسعار بعدة عملات</h6>
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
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">إعدادات الـ SEO (اختيارية)</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="meta_title"
                                           class="form-control @error('meta_title') is-invalid @enderror"
                                           placeholder="عنوان الميتا"
                                           value="{{ old('meta_title', $class->meta_title) }}">
                                    <label>Meta Title</label>
                                    @error('meta_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="meta_keywords"
                                           class="form-control @error('meta_keywords') is-invalid @enderror"
                                           placeholder="الكلمات المفتاحية"
                                           value="{{ old('meta_keywords', $class->meta_keywords) }}">
                                    <label>Meta Keywords</label>
                                    @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                                              placeholder="وصف الميتا" style="height: 90px">{{ old('meta_description', $class->meta_description) }}</textarea>
                                    <label>Meta Description</label>
                                    @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
function togglePriceFieldsEdit() {
    var isFree = document.getElementById('is_free_edit').checked;
    var priceInput = document.getElementById('price_input_edit');
    var showPriceWrapper = document.getElementById('show_price_wrapper_edit');
    var showPriceInput = document.getElementById('show_price_edit');
    
    priceInput.disabled = isFree;
    if (isFree) {
        priceInput.value = 0;
        showPriceInput.checked = true;
        showPriceInput.disabled = true;
    } else {
        showPriceInput.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    togglePriceFieldsEdit();
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@stop

