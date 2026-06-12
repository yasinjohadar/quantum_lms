{{-- حقول بيانات الدور الأساسية — يتوقع $role اختيارياً (تعديل) --}}
<div class="row g-3 align-items-start">
    <div class="col-12 col-lg-4 role-form-field">
        <label class="form-label">اسم الدور</label>
        <input type="text"
               class="form-control @error('name') is-invalid @enderror"
               name="name"
               value="{{ old('name', $role->name ?? '') }}"
               placeholder="مثال: مشرف عام">
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-lg-4 role-form-field">
        <label class="form-label">نوع الواجهة</label>
        <select class="form-select @error('dashboard_type') is-invalid @enderror" name="dashboard_type" required>
            <option value="admin" @selected(old('dashboard_type', $role->dashboard_type ?? 'admin') === 'admin')>لوحة تحكم الأدمن</option>
            <option value="student" @selected(old('dashboard_type', $role->dashboard_type ?? 'admin') === 'student')>لوحة تحكم الطالب</option>
        </select>
        @error('dashboard_type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">نوع الواجهة التي يصل إليها حاملو هذا الدور</small>
    </div>
    @php
        $rolesTable = config('permission.table_names.roles', 'roles');
    @endphp
    @if(\Illuminate\Support\Facades\Schema::hasColumn($rolesTable, 'staff_profile'))
        <div class="col-12 col-lg-4 role-form-field">
            <label class="form-label">تصنيف المشرف / المعلم</label>
            <select class="form-select @error('staff_profile') is-invalid @enderror" name="staff_profile" required>
                <option value="none" @selected(old('staff_profile', $role->staff_profile ?? 'none') === 'none')>لا شيء (طالب، أدمن، دور عام)</option>
                <option value="supervisor" @selected(old('staff_profile', $role->staff_profile ?? 'none') === 'supervisor')>مشرف</option>
                <option value="teacher" @selected(old('staff_profile', $role->staff_profile ?? 'none') === 'teacher')>معلم</option>
            </select>
            @error('staff_profile')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted d-block mt-1">يحدد ظهور حاملي الدور في صفحات تخصيص المشرفين والمعلمين</small>
        </div>
    @endif
</div>
