@extends('admin.layouts.master')

@section('page-title')
    جلسات المستخدمين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">جلسات المستخدمين</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">جلسات المستخدمين</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">إجمالي الجلسات</p>
                                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent">
                                    <i class="bi bi-list-check fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">نشطة</p>
                                    <h4 class="mb-0 text-success">{{ number_format($stats['active']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-success-transparent">
                                    <i class="bi bi-circle-fill fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">مكتملة</p>
                                    <h4 class="mb-0 text-primary">{{ number_format($stats['completed']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent">
                                    <i class="bi bi-check-circle fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">اليوم</p>
                                    <h4 class="mb-0 text-info">{{ number_format($stats['today']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="bi bi-calendar-day fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <!-- قسم الفلاتر -->
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-funnel me-2"></i> البحث والفلترة
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.user-sessions.index') }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">بحث</label>
                                        <input type="text" name="search" class="form-control form-control-sm"
                                               placeholder="بحث بالاسم، البريد، أو UUID"
                                               value="{{ request('search') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">المستخدم</label>
                                        <select name="user_id" class="form-select form-select-sm">
                                            <option value="">كل المستخدمين</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">عنوان IP</label>
                                        <input type="text" name="ip_address" class="form-control form-control-sm"
                                               placeholder="عنوان IP"
                                               value="{{ request('ip_address') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">الحالة</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">كل الحالات</option>
                                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشطة</option>
                                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتملة</option>
                                            <option value="disconnected" {{ request('status') === 'disconnected' ? 'selected' : '' }}>منفصلة</option>
                                            <option value="timeout" {{ request('status') === 'timeout' ? 'selected' : '' }}>منتهية</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">الجهاز</label>
                                        <select name="device_type" class="form-select form-select-sm">
                                            <option value="">كل الأجهزة</option>
                                            <option value="desktop" {{ request('device_type') === 'desktop' ? 'selected' : '' }}>سطح المكتب</option>
                                            <option value="mobile" {{ request('device_type') === 'mobile' ? 'selected' : '' }}>جوال</option>
                                            <option value="tablet" {{ request('device_type') === 'tablet' ? 'selected' : '' }}>تابلت</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1">
                                        <label class="form-label mb-1">من تاريخ</label>
                                        <input type="date" name="date_from" class="form-control form-control-sm"
                                               value="{{ request('date_from') }}">
                                    </div>

                                    <div class="col-md-1">
                                        <label class="form-label mb-1">إلى تاريخ</label>
                                        <input type="date" name="date_to" class="form-control form-control-sm"
                                               value="{{ request('date_to') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1 d-block">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                                <i class="bi bi-search me-1"></i> بحث
                                            </button>
                                            <a href="{{ route('admin.user-sessions.index') }}" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-circle me-1"></i> مسح
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- جدول الجلسات -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة الجلسات</h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 150px;">المستخدم</th>
                                        <th style="min-width: 150px;">اسم الجلسة</th>
                                        <th style="min-width: 120px;">عنوان IP</th>
                                        <th style="min-width: 150px;">الجهاز/المتصفح</th>
                                        <th style="min-width: 120px;">دقة الشاشة</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 150px;">تاريخ البدء</th>
                                        <th style="min-width: 150px;">تاريخ الانتهاء</th>
                                        <th style="min-width: 120px;">مدة الجلسة</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td>{{ $loop->iteration + ($sessions->currentPage() - 1) * $sessions->perPage() }}</td>
                                            <td>
                                                @if($session->user)
                                                    <div class="fw-semibold">{{ $session->user->name }}</div>
                                                    <small class="text-muted">{{ $session->user->email }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $session->session_name ?? '-' }}</div>
                                                @if($session->session_uuid)
                                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($session->session_uuid, 20) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <code>{{ $session->ip_address ?? '-' }}</code>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><strong>{{ $session->device_type ?? '-' }}</strong></div>
                                                    <div class="text-muted">{{ $session->browser ?? '-' }} {{ $session->browser_version ?? '' }}</div>
                                                    <div class="text-muted">{{ $session->platform ?? '-' }} {{ $session->platform_version ?? '' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $session->screen_resolution ?? '-' }}
                                            </td>
                                            <td>
                                                {!! $session->status_badge !!}
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $session->started_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $session->started_at->format('H:i:s') }}</small>
                                                <div class="text-muted small">{{ $session->started_at->diffForHumans() }}</div>
                                            </td>
                                            <td>
                                                @if($session->ended_at)
                                                    <div class="fw-semibold">{{ $session->ended_at->format('Y-m-d') }}</div>
                                                    <small class="text-muted">{{ $session->ended_at->format('H:i:s') }}</small>
                                                    <div class="text-muted small">{{ $session->ended_at->diffForHumans() }}</div>
                                                @else
                                                    <span class="badge bg-info-transparent text-info">لا تزال نشطة</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($session->duration)
                                                    <div class="fw-semibold">{{ $session->duration }}</div>
                                                    @php
                                                        $hours = floor($session->duration_seconds / 3600);
                                                        $minutes = floor(($session->duration_seconds % 3600) / 60);
                                                    @endphp
                                                    <small class="text-muted">
                                                        @if($hours > 0)
                                                            {{ $hours }} ساعة {{ $minutes }} دقيقة
                                                        @else
                                                            {{ $minutes }} دقيقة
                                                        @endif
                                                    </small>
                                                @elseif($session->is_active)
                                                    <span class="text-muted">قيد التشغيل...</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                                    <a href="{{ route('admin.user-sessions.show', $session->id) }}"
                                                       class="btn btn-sm btn-info text-white"
                                                       title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                    @if($session->is_active)
                                                        <button type="button"
                                                                class="btn btn-sm btn-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#endSessionModal{{ $session->id }}"
                                                                title="إنهاء الجلسة">
                                                            <i class="fas fa-stop"></i> إنهاء
                                                        </button>
                                                    @endif
                                                    @if($session->user)
                                                        <a href="{{ route('admin.user-sessions.user', $session->user_id) }}"
                                                           class="btn btn-sm btn-primary text-white"
                                                           title="جلسات المستخدم">
                                                            <i class="fas fa-user"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteSessionModal{{ $session->id }}"
                                                            title="حذف الجلسة">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                <!-- Modal for End Session -->
                                                @if($session->is_active)
                                                <div class="modal fade" id="endSessionModal{{ $session->id }}" tabindex="-1" aria-labelledby="endSessionModalLabel{{ $session->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="{{ route('admin.user-sessions.end', $session->id) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="endSessionModalLabel{{ $session->id }}">إنهاء الجلسة</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">حالة الانتهاء</label>
                                                                        <select name="status" class="form-select" required>
                                                                            <option value="completed">مكتملة</option>
                                                                            <option value="disconnected">منفصلة</option>
                                                                            <option value="timeout">منتهية</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">ملاحظات (اختياري)</label>
                                                                        <textarea name="notes" class="form-control" rows="3"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                                    <button type="submit" class="btn btn-warning">إنهاء الجلسة</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <!-- Modal for Delete Confirmation -->
                                                <div class="modal fade" id="deleteSessionModal{{ $session->id }}" tabindex="-1" aria-labelledby="deleteSessionModalLabel{{ $session->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-4">
                                                                <i class="bi bi-trash-fill text-danger display-1 mb-3"></i>
                                                                <h4 class="mb-3">تأكيد حذف الجلسة</h4>
                                                                <p class="mb-3">هل أنت متأكد من حذف هذه الجلسة؟</p>
                                                                <div class="alert alert-warning mb-4">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    <small>هذه العملية لا يمكن التراجع عنها.</small>
                                                                </div>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                        <i class="bi bi-x-circle me-1"></i> إلغاء
                                                                    </button>
                                                                    <form action="{{ route('admin.user-sessions.destroy', $session->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">
                                                                            <i class="bi bi-trash me-1"></i> حذف
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد جلسات</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($sessions->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $sessions->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- حذف الجلسات القديمة -->
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trash me-2"></i> حذف الجلسات القديمة
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.user-sessions.clear-old') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف جميع الجلسات الأقدم من التاريخ المحدد؟')">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">حذف الجلسات الأقدم من (بالأيام)</label>
                                        <input type="number" name="days" class="form-control" min="1" max="365" value="30" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">حالة الجلسات (اختياري)</label>
                                        <select name="status" class="form-select">
                                            <option value="">كل الحالات</option>
                                            <option value="completed">مكتملة</option>
                                            <option value="disconnected">منفصلة</option>
                                            <option value="timeout">منتهية</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash me-1"></i> حذف الجلسات القديمة
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
@stop
