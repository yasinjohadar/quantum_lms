@extends('admin.layouts.master')

@section('page-title')
    إضافة تذكير جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة تذكير جديد</h5>
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
                        <form action="{{ route('admin.calendar.reminders.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="event_type" class="form-label">نوع الحدث <span class="text-danger">*</span></label>
                                <select class="form-select" id="event_type" name="event_type" required>
                                    <option value="">اختر نوع الحدث</option>
                                    @foreach($eventTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('event_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="event_id" class="form-label">معرف الحدث <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="event_id" name="event_id" value="{{ old('event_id') }}" required>
                                <small class="text-muted">أدخل ID الحدث (من جدول calendar_events أو quizzes أو assignments)</small>
                            </div>

                            <div class="mb-3">
                                <label for="user_id" class="form-label">المستخدم (اختياري)</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">جميع المستخدمين</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">اتركه فارغاً لإرسال التذكير لجميع المستخدمين المعنيين</small>
                            </div>

                            <div class="mb-3">
                                <label for="reminder_type" class="form-label">نوع التذكير <span class="text-danger">*</span></label>
                                <select class="form-select" id="reminder_type" name="reminder_type" required>
                                    <option value="single" {{ old('reminder_type') == 'single' ? 'selected' : '' }}>تذكير واحد</option>
                                    <option value="multiple" {{ old('reminder_type') == 'multiple' ? 'selected' : '' }}>تذكيرات متعددة</option>
                                </select>
                            </div>

                            <div id="single_reminder" class="mb-3" style="{{ old('reminder_type', 'single') == 'single' ? '' : 'display: none;' }}">
                                <label for="custom_minutes" class="form-label">عدد الدقائق قبل الحدث <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="custom_minutes" name="custom_minutes" value="{{ old('custom_minutes') }}" min="1" {{ old('reminder_type', 'single') == 'single' ? 'required' : '' }}>
                                <small class="text-muted">مثال: 60 = ساعة واحدة قبل الحدث</small>
                            </div>

                            <div id="multiple_reminders" class="mb-3" style="{{ old('reminder_type') == 'multiple' ? '' : 'display: none;' }}">
                                <label class="form-label">أوقات التذكير (بالساعات) <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="reminder_times[]" value="1" id="reminder_1" {{ in_array(1, old('reminder_times', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_1">ساعة واحدة</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="reminder_times[]" value="24" id="reminder_24" {{ in_array(24, old('reminder_times', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_24">24 ساعة (يوم)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="reminder_times[]" value="168" id="reminder_168" {{ in_array(168, old('reminder_times', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_168">168 ساعة (أسبوع)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ
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
            const customMinutesInput = document.getElementById('custom_minutes');
            customMinutesInput.setAttribute('required', 'required');
            customMinutesInput.removeAttribute('disabled');
        } else if (reminderType.value === 'multiple') {
            singleReminder.style.display = 'none';
            multipleReminders.style.display = 'block';
            const customMinutesInput = document.getElementById('custom_minutes');
            customMinutesInput.removeAttribute('required');
            customMinutesInput.value = '';
        } else {
            singleReminder.style.display = 'none';
            multipleReminders.style.display = 'none';
            document.getElementById('custom_minutes').removeAttribute('required');
        }
    }

    reminderType.addEventListener('change', toggleReminderFields);
    toggleReminderFields(); // Initialize on page load
});
</script>
@endpush
@stop

