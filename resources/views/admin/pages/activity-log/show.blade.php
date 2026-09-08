@extends('admin.layouts.master')

@section('page-title')
    تفاصيل النشاط
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل النشاط</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.activity-log.index') }}">سجل النشاطات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $log->id }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        @php
            $eventKey = \Illuminate\Support\Str::afterLast($log->event_type, '.');
            $allKeys = array_unique(array_merge(array_keys($log->old_values ?? []), array_keys($log->new_values ?? [])));
        @endphp

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card custom-card">
                    <div class="card-header"><h6 class="mb-0">معلومات الحدث</h6></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">المستخدم</td><td>{{ $log->user->name ?? 'غير معروف' }}</td></tr>
                            <tr><td class="text-muted">الدور</td><td>{{ $log->causer_role ?? '-' }}</td></tr>
                            <tr><td class="text-muted">نوع العنصر</td><td>{{ $subjectLabels[$log->subject_type] ?? $log->subject_type }}</td></tr>
                            <tr><td class="text-muted">العنصر</td><td>{{ $log->subject_label }}</td></tr>
                            <tr><td class="text-muted">رقم العنصر</td><td><code>{{ $log->subject_id }}</code></td></tr>
                            <tr><td class="text-muted">الإجراء</td><td>{{ $eventLabels[$eventKey] ?? $eventKey }}</td></tr>
                            <tr><td class="text-muted">الوقت</td><td>{{ $log->occurred_at->format('Y-m-d H:i:s') }}</td></tr>
                            <tr><td class="text-muted">IP</td><td><code>{{ $log->ip_address ?? '-' }}</code></td></tr>
                            <tr><td class="text-muted">الرابط</td><td class="text-break"><small>{{ $log->url ?? '-' }}</small></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                <div class="card custom-card">
                    <div class="card-header"><h6 class="mb-0">التغييرات</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>الحقل</th><th>القيمة قبل</th><th>القيمة بعد</th></tr></thead>
                                <tbody>
                                    @forelse($allKeys as $key)
                                        @php
                                            $oldVal = $log->old_values[$key] ?? null;
                                            $newVal = $log->new_values[$key] ?? null;
                                            $format = fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
                                        @endphp
                                        <tr>
                                            <td><code>{{ $key }}</code></td>
                                            <td class="text-break">{{ $format($oldVal) ?? '-' }}</td>
                                            <td class="text-break">{{ $format($newVal) ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">لا توجد تفاصيل تغييرات مسجلة</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
