<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="distinguished-student-form">
    @csrf
    @if (isset($item) && $item)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-12">
            <h6 class="text-primary mb-3">البيانات الأساسية</h6>
        </div>

        <div class="col-md-6">
            <label class="form-label">الصف <span class="text-danger">*</span></label>
            <select name="class_id" id="form_class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                <option value="">اختر الصف</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ old('class_id', $item->class_id ?? '') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">الطالب <span class="text-danger">*</span></label>
            <select name="user_id" id="form_user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                <option value="">اختر الصف أولاً</option>
                @if (isset($item) && $item->user_id && $item->schoolClass && $item->class_id == old('class_id', $item->class_id))
                    <option value="{{ $item->user_id }}" selected>{{ $item->user->name ?? '—' }}</option>
                @endif
            </select>
            @error('user_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label">رأي الطالب / الوصف <span class="text-danger">*</span></label>
            <textarea name="quote" class="form-control @error('quote') is-invalid @enderror" rows="4"
                      placeholder="رأي الطالب أو وصف متميز نحصل عليه يدوياً" required>{{ old('quote', $item->quote ?? '') }}</textarea>
            @error('quote')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">صورة الطالب (اختياري)</label>
            @if (isset($item) && $item->photo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $item->photo) }}" alt="صورة الطالب"
                         class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                    <p class="small text-muted mb-1">ترك الحقل فارغاً = الإبقاء على الصورة الحالية</p>
                </div>
            @endif
            <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror"
                   accept="image/jpeg,image/png,image/jpg,image/webp">
            @error('photo')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <div class="form-floating">
                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                       placeholder="الترتيب" value="{{ old('order', $item->order ?? 0) }}" min="0">
                <label>ترتيب العرض</label>
                @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="form_is_active" value="1"
                       {{ old('is_active', isset($item) ? $item->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="form_is_active">نشط (يظهر في الصفحة الرئيسية)</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('admin.distinguished-students.index') }}" class="btn btn-secondary px-4 me-2">إلغاء</a>
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i> حفظ
        </button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var classSelect = document.getElementById('form_class_id');
    var userSelect = document.getElementById('form_user_id');
    var baseUrl = '{{ route("admin.distinguished-students.students-by-class") }}';
    var currentUserId = '{{ old("user_id", isset($item) && $item ? $item->user_id : "") }}';

    function loadStudents(classId) {
        userSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        userSelect.disabled = true;

        if (!classId) {
            userSelect.innerHTML = '<option value="">اختر الصف أولاً</option>';
            userSelect.disabled = false;
            return;
        }

        fetch(baseUrl + '?class_id=' + encodeURIComponent(classId))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                userSelect.innerHTML = '<option value="">اختر الطالب</option>';
                (data || []).forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    if (String(s.id) === String(currentUserId)) {
                        opt.selected = true;
                    }
                    userSelect.appendChild(opt);
                });
                userSelect.disabled = false;
            })
            .catch(function () {
                userSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
                userSelect.disabled = false;
            });
    }

    classSelect.addEventListener('change', function () {
        currentUserId = '';
        loadStudents(this.value);
    });

    if (classSelect.value) {
        loadStudents(classSelect.value);
    }
});
</script>
@endpush
