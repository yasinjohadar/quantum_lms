<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if (isset($heroSlide))
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-12">
            <h6 class="text-primary mb-3">البيانات الأساسية</h6>
        </div>

        <div class="col-md-12">
            <div class="form-floating">
                <input type="text" name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       placeholder="العنوان الرئيسي"
                       value="{{ old('title', $heroSlide->title ?? '') }}" required>
                <label>العنوان الرئيسي <span class="text-danger">*</span></label>
                @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-floating">
                <input type="text" name="subtitle"
                       class="form-control @error('subtitle') is-invalid @enderror"
                       placeholder="العنوان الفرعي أو الشارة"
                       value="{{ old('subtitle', isset($heroSlide) ? $heroSlide->subtitle : '') }}">
                <label>العنوان الفرعي / الشارة (اختياري)</label>
                @error('subtitle')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-floating">
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          placeholder="الوصف" style="height: 100px">{{ old('description', isset($heroSlide) ? $heroSlide->description : '') }}</textarea>
                <label>الوصف (اختياري)</label>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-12">
            <label class="form-label">موضع النص والصورة</label>
            <select name="text_position" class="form-select @error('text_position') is-invalid @enderror">
                <option value="right" {{ old('text_position', isset($heroSlide) ? $heroSlide->text_position : 'right') === 'right' ? 'selected' : '' }}>النص يمين — الصورة يسار</option>
                <option value="left" {{ old('text_position', isset($heroSlide) ? $heroSlide->text_position : 'right') === 'left' ? 'selected' : '' }}>النص يسار — الصورة يمين</option>
                <option value="center" {{ old('text_position', isset($heroSlide) ? $heroSlide->text_position : 'right') === 'center' ? 'selected' : '' }}>النص والأزرار بالوسط</option>
            </select>
            @error('text_position')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" name="button_text"
                       class="form-control @error('button_text') is-invalid @enderror"
                       placeholder="نص الزر"
                       value="{{ old('button_text', $heroSlide->button_text ?? '') }}">
                <label>نص الزر (اختياري)</label>
                @error('button_text')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-floating">
                <input type="url" name="button_url"
                       class="form-control @error('button_url') is-invalid @enderror"
                       placeholder="رابط الزر"
                       value="{{ old('button_url', isset($heroSlide) ? $heroSlide->button_url : '') }}">
                <label>رابط الزر (اختياري)</label>
                @error('button_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12 mt-2">
            <h6 class="text-primary mb-2">الزر الثاني</h6>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" name="button2_text"
                       class="form-control @error('button2_text') is-invalid @enderror"
                       placeholder="نص الزر الثاني"
                       value="{{ old('button2_text', isset($heroSlide) ? $heroSlide->button2_text : '') }}">
                <label>نص الزر الثاني (اختياري)</label>
                @error('button2_text')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" name="button2_url"
                       class="form-control @error('button2_url') is-invalid @enderror"
                       placeholder="رابط الزر الثاني (مثال: #classes-section أو https://...)"
                       value="{{ old('button2_url', isset($heroSlide) ? $heroSlide->button2_url : '') }}">
                <label>رابط الزر الثاني (اختياري)</label>
                @error('button2_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">صورة الخلفية (اختياري)</label>
            @if (isset($heroSlide) && $heroSlide->background_image)
                <div class="mb-2">
                    <img src="{{ media_public_url($heroSlide->background_image) }}" alt="خلفية"
                         class="rounded" style="width: 120px; height: 70px; object-fit: cover;">
                    <p class="small text-muted mb-1">ترك الحقل فارغاً = الإبقاء على الصورة الحالية</p>
                </div>
            @endif
            <input type="file" name="background_image"
                   class="form-control @error('background_image') is-invalid @enderror"
                   accept="image/jpeg,image/png,image/jpg,image/webp">
            @error('background_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">صورة المحتوى (اختياري)</label>
            @if (isset($heroSlide) && $heroSlide->content_image)
                <div class="mb-2">
                    <img src="{{ media_public_url($heroSlide->content_image) }}" alt="محتوى"
                         class="rounded" style="width: 120px; height: 70px; object-fit: cover;">
                    <p class="small text-muted mb-1">ترك الحقل فارغاً = الإبقاء على الصورة الحالية</p>
                </div>
            @endif
            <input type="file" name="content_image"
                   class="form-control @error('content_image') is-invalid @enderror"
                   accept="image/jpeg,image/png,image/jpg,image/webp">
            @error('content_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <div class="form-floating">
                <input type="number" name="order"
                       class="form-control @error('order') is-invalid @enderror"
                       placeholder="الترتيب"
                       value="{{ old('order', isset($heroSlide) ? $heroSlide->order : 0) }}" min="0">
                <label>ترتيب العرض</label>
                @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" name="is_active"
                       id="is_active" value="1"
                       {{ old('is_active', isset($heroSlide) ? $heroSlide->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">الشريحة نشطة</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-secondary px-4 me-2">إلغاء</a>
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i> حفظ
        </button>
    </div>
</form>
