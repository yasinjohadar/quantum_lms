<form method="POST" action="{{ $action }}">
    @csrf
    @if(isset($socialLink) && $socialLink->id) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">الاسم <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                   name="name" value="{{ old('name', isset($socialLink) ? $socialLink->name : '') }}" placeholder="مثال: فيسبوك" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="url">الرابط <span class="text-danger">*</span></label>
            <input type="url" class="form-control @error('url') is-invalid @enderror" id="url"
                   name="url" value="{{ old('url', isset($socialLink) ? $socialLink->url : '') }}" placeholder="https://" required>
            @error('url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="icon_class">الأيقونة (Font Awesome) <span class="text-danger">*</span></label>
            <select class="form-select @error('icon_class') is-invalid @enderror" id="icon_class" name="icon_class" required>
                <option value="">— اختر أو اكتب أدناه —</option>
                @foreach($suggestedIcons as $class => $label)
                    <option value="{{ $class }}" {{ old('icon_class', isset($socialLink) ? $socialLink->icon_class : '') === $class ? 'selected' : '' }}>
                        {{ $label }} ({{ $class }})
                    </option>
                @endforeach
            </select>
            <small class="text-muted">يمكنك أيضاً كتابة أي كلاس Font Awesome مثل: fa-brands fa-facebook-f</small>
            @error('icon_class')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="icon_class_custom">أو اكتب الكلاس يدوياً</label>
            <input type="text" class="form-control" id="icon_class_custom" placeholder="fa-brands fa-tiktok"
                   value="{{ old('icon_class', isset($socialLink) ? $socialLink->icon_class : '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label" for="sort_order">ترتيب العرض</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order"
                   name="sort_order" value="{{ old('sort_order', isset($socialLink) ? $socialLink->sort_order : 0) }}" min="0">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                       {{ old('is_active', isset($socialLink) ? $socialLink->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">نشط (يظهر في الموقع)</label>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> حفظ
        </button>
        <a href="{{ route('admin.social-links.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('icon_class');
    var custom = document.getElementById('icon_class_custom');
    if (select && custom) {
        custom.addEventListener('input', function() {
            var v = this.value.trim();
            if (v) {
                var opt = Array.from(select.options).find(function(o) { return o.value === v; });
                if (!opt) {
                    select.value = '';
                    var newOpt = document.createElement('option');
                    newOpt.value = v;
                    newOpt.textContent = v;
                    select.appendChild(newOpt);
                    select.value = v;
                } else {
                    select.value = v;
                }
            }
        });
        select.addEventListener('change', function() {
            if (this.value) custom.value = this.value;
        });
    }
});
</script>
@endpush
