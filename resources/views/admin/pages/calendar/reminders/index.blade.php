@extends('admin.layouts.master')

@section('page-title')
    إدارة التذكيرات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إدارة التذكيرات</h5>
            </div>
            <div>
                <a href="{{ route('admin.calendar.reminders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة تذكير جديد
                </a>
                <a href="{{ route('admin.calendar.events.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للتقويم
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>نوع الحدث</th>
                                        <th>معرف الحدث</th>
                                        <th>المستخدم</th>
                                        <th>نوع التذكير</th>
                                        <th>التفاصيل</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reminders as $reminder)
                                        <tr>
                                            <td>{{ $reminder->id }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ \App\Models\EventReminder::EVENT_TYPES[$reminder->event_type] ?? $reminder->event_type }}
                                                </span>
                                            </td>
                                            <td>{{ $reminder->event_id }}</td>
                                            <td>{{ $reminder->user ? $reminder->user->name : 'جميع المستخدمين' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $reminder->reminder_type === 'single' ? 'واحد' : 'متعدد' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($reminder->reminder_type === 'single')
                                                    {{ $reminder->custom_minutes }} دقيقة قبل الحدث
                                                @else
                                                    {{ implode(', ', $reminder->reminder_times ?? []) }} ساعة قبل الحدث
                                                @endif
                                            </td>
                                            <td>
                                                @if($reminder->is_sent)
                                                    <span class="badge bg-success">تم الإرسال</span>
                                                    <br>
                                                    <small class="text-muted">{{ $reminder->sent_at?->format('Y-m-d H:i') }}</small>
                                                @else
                                                    <span class="badge bg-warning">معلق</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('admin.calendar.reminders.edit', $reminder->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.calendar.reminders.destroy', $reminder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا التذكير؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">لا توجد تذكيرات.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $reminders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

