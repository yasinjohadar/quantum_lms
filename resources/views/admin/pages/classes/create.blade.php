@extends('admin.layouts.master')

@section('page-title')
    إضافة صف دراسي
@stop

@section('css')
@stop

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
                    {{-- إظهار كافة أخطاء التحقق والرسائل العامة في أعلى النموذج --}}
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

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">التسعير</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" name="price" id="price_input_create"
                                           class="form-control @error('price') is-invalid @enderror"
                                           placeholder="السعر"
                                           value="{{ old('price', 0) }}"
                                           step="0.01"
                                           min="0">
                                    <label>السعر (العملة الافتراضية) <span class="text-danger">*</span></label>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">اتركه 0 إذا كان الصف مجانياً</small>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_free"
                                           id="is_free_create" value="1"
                                           {{ old('is_free', false) ? 'checked' : '' }}
                                           onchange="var el=document.getElementById('price_input_create'); el.disabled=this.checked; if(this.checked) el.value=0;">
                                    <label class="form-check-label" for="is_free_create">الصف مجاني</label>
                                </div>
                            </div>

                            <div class="col-md-6">
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
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">خصائص الصف (اختياري - حتى 10)</h6>
                                <p class="text-muted small mb-2">أضف نصوصاً للخصائص التي تريد إظهارها مع الصف (مثل: مدة الدورة، عدد الدروس، إلخ). اترك الحقول الفارغة دون استخدام.</p>
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

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="وصف الصف" style="height: 100px">{{ old('description') }}</textarea>
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
                                           value="{{ old('whatsapp_group_url') }}">
                                    <label>رابط مجموعة واتساب (اختياري)</label>
                                    @error('whatsapp_group_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">مثال: https://chat.whatsapp.com/xxxxxxxxxxxxx</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة الصف (اختياري)</label>
                                <input type="file" name="image"
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/*">
                                @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة Open Graph (اختياري)</label>
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
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">الصف نشط</label>
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="allow_subjects_purchase"
                                           id="allow_subjects_purchase" value="1"
                                           {{ old('allow_subjects_purchase', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_subjects_purchase">السماح بشراء المواد المتفرقة</label>
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
                                           value="{{ old('meta_title') }}">
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
                                           value="{{ old('meta_keywords') }}">
                                    <label>Meta Keywords</label>
                                    @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                                              placeholder="وصف الميتا" style="height: 90px">{{ old('meta_description') }}</textarea>
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
                                <i class="fas fa-save me-1"></i> حفظ الصف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop


