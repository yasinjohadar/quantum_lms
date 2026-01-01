@extends('admin.layouts.master')

@section('page-title')
    الجلسات الحية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الجلسات الحية</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.live-sessions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إنشاء جلسة جديدة
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="mb-0 fw-bold">قائمة الجلسات الحية</h5>

                        <form method="GET" action="{{ route('admin.live-sessions.index') }}"
                              class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="بحث بالعنوان"
                                   value="{{ request('search') }}" style="min-width: 220px;">

                            <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                                <option value="">كل الحالات</option>
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                                <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>جارية</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                            </select>

                            <select name="subject_id" class="form-select form-select-sm" style="min-width: 200px;">
                                <option value="">كل المواد</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="date" name="date_from" class="form-control form-control-sm" 
                                   value="{{ request('date_from') }}" placeholder="من تاريخ" style="min-width: 150px;">

                            <input type="date" name="date_to" class="form-control form-control-sm" 
                                   value="{{ request('date_to') }}" placeholder="إلى تاريخ" style="min-width: 150px;">

                            <button type="submit" class="btn btn-secondary btn-sm">
                                بحث
                            </button>
                            <a href="{{ route('admin.live-sessions.index') }}" class="btn btn-outline-danger btn-sm">
                                مسح الفلاتر
                            </a>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="min-width: 200px;">العنوان</th>
                                    <th style="min-width: 150px;">المادة/الدرس</th>
                                    <th style="min-width: 150px;">تاريخ ووقت الجلسة</th>
                                    <th style="min-width: 100px;">المدة</th>
                                    <th style="min-width: 100px;">الحالة</th>
                                    <th style="min-width: 100px;">Zoom Meeting</th>
                                    <th style="min-width: 200px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td>{{ $session->id }}</td>
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
                                            @elseif($session->status === 'completed')
                                                <span class="badge bg-secondary">مكتملة</span>
                                            @else
                                                <span class="badge bg-danger">ملغاة</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->zoomMeeting)
                                                <span class="badge bg-success">موجود</span>
                                            @else
                                                <span class="badge bg-warning">غير موجود</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.live-sessions.show', $session) }}" 
                                                   class="btn btn-info btn-sm" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.live-sessions.edit', $session) }}" 
                                                   class="btn btn-primary btn-sm" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.live-sessions.destroy', $session) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الجلسة؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">لا توجد جلسات حية</td>
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
@endsection




