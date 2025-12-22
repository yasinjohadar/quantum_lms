@extends('admin.layouts.master')

@section('page-title')
    تعديل تذكير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل تذكير</h5>
            </div>
            <div>
                <a href="{{ route('admin.calendar.reminders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.calendar.reminders.update', $reminder->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">نوع الحدث</label>
                                <input type="text" class="form-control" value="{{ \App\Models\EventReminder::EVENT_TYPES[$reminder->event_type] ?? $reminder->event_type }}" disabled>
                                <small class="text-muted">لا يمكن تغيير نوع الحدث</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">معرف الحدث</label>
                                <input type="text" class="form-control" value="{{ $reminder->event_id }}" disabled>
                                <small class="text-muted">لا يمكن تغيير معرف الحدث</small>
                            </div>

                            <div class="mb-3">
                                <label for="user_id" class="form-label">المستخدم (اختياري)</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">جميع المستخدمين</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $reminder->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="reminder_type" class="form-label">نوع التذكير <span class="text-danger">*</span></label>
                                <select class="form-select" id="reminder_type" name="reminder_type" required>
                                    <option value="single" {{ old('reminder_type', $reminder->reminder_type) == 'single' ? 'selected' : '' }}>تذكير واحد</option>
                                    <option value="multiple" {{ old('reminder_type', $reminder->reminder_type) == 'multiple' ? 'selected' : '' }}>تذكيرات متعددة</option>
                                </select>
                            </div>

                            <div id="single_reminder" class="mb-3" style="display: none;">
                                <label for="custom_minutes" class="form-label">عدد الدقائق قبل الحدث <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="custom_minutes" name="custom_minutes" value="{{ old('custom_minutes', $reminder->custom_minutes) }}" min="1">
                            </div>

                            <div id="multiple_reminders" class="mb-3" style="display: none;">
                                <label class="form-label">أوقات التذكير (بالساعات) <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="reminder_times[]" value="1" id="reminder_1" {{ in_array(1, old('reminder_times', $reminder->reminder_times ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_1">ساعة واحدة</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="reminder_times[]" value="24" id="reminder_24" {{ in_array(24, old('reminder_times', $reminder->reminder_times ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_24">24 ساعة (يوم)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="reminder_times[]" value="168" id="reminder_168" {{ in_array(168, old('reminder_times', $reminder->reminder_times ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_168">168 ساعة (أسبوع)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> تحديث
                                </button>
                                <a href="{{ route('admin.calendar.reminders.index') }}" class="btn btn-secondary">
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reminderType = document.getElementById('reminder_type');
    const singleReminder = document.getElementById('single_reminder');
    const multipleReminders = document.getElementById('multiple_reminders');

    function toggleReminderFields() {
        if (reminderType.value === 'single') {
            singleReminder.style.display = 'block';
            multipleReminders.style.display = 'none';
            document.getElementById('custom_minutes').required = true;
        } else if (reminderType.value === 'multiple') {
            singleReminder.style.display = 'none';
            multipleReminders.style.display = 'block';
            document.getElementById('custom_minutes').required = false;
        }
    }

    reminderType.addEventListener('change', toggleReminderFields);
    toggleReminderFields(); // Initialize
});
</script>
@endpush
@stop

