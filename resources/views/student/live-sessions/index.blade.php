@extends('student.layouts.master')

@section('page-title')
    الجلسات الحية
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">الجلسات الحية المتاحة</h4>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <form method="GET" action="{{ route('student.live-sessions.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                            <select name="subject_id" class="form-select form-select-sm" style="min-width: 200px;">
                                <option value="">كل المواد</option>
                                @foreach($enrolledSubjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-secondary btn-sm">فلترة</button>
                            <a href="{{ route('student.live-sessions.index') }}" class="btn btn-outline-danger btn-sm">مسح</a>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>عنوان الجلسة</th>
                                        <th>المادة/الدرس</th>
                                        <th>تاريخ ووقت الجلسة</th>
                                        <th>المدة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td>{{ $session->title }}</td>
                                            <td>
                                                @if($session->sessionable_type === \App\Models\Subject::class)
                                                    <span class="badge bg-info">{{ $session->sessionable->name ?? 'N/A' }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $session->sessionable->title ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $session->scheduled_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $session->duration_minutes }} دقيقة</td>
                                            <td>
                                                @if($session->status === 'scheduled')
                                                    <span class="badge bg-primary">مجدولة</span>
                                                @elseif($session->status === 'live')
                                                    <span class="badge bg-success">جارية</span>
                                                @else
                                                    <span class="badge bg-secondary">مكتملة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student.live-sessions.show', $session->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">لا توجد جلسات حية متاحة</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $sessions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection


