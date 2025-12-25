@extends('admin.layouts.master')

@section('page-title')
    جلسات المستخدم - {{ $user->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">جلسات المستخدم - {{ $user->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                            <li class="breadcrumb-item active" aria-current="page">جلسات المستخدم</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.user-sessions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                </div>
            </div>

            <!-- معلومات المستخدم -->
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                                         class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $user->name }}</h5>
                                    <p class="text-muted mb-0">{{ $user->email }}</p>
                                    @if($user->phone)
                                        <small class="text-muted">{{ $user->phone }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                    <p class="text-muted mb-1">إجمالي مدة الجلسات</p>
                                    <h4 class="mb-0 text-info">
                                        @if($stats['total_duration'])
                                            @php
                                                $hours = floor($stats['total_duration'] / 3600);
                                                $minutes = floor(($stats['total_duration'] % 3600) / 60);
                                            @endphp
                                            @if($hours > 0)
                                                {{ $hours }}س {{ $minutes }}د
                                            @else
                                                {{ $minutes }}د
                                            @endif
                                        @else
                                            0
                                        @endif
                                    </h4>
                                </div>
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="bi bi-clock-history fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قائمة الجلسات -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">قائمة الجلسات</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 150px;">اسم الجلسة</th>
                                        <th style="min-width: 120px;">عنوان IP</th>
                                        <th style="min-width: 150px;">الجهاز/المتصفح</th>
                                        <th style="min-width: 120px;">دقة الشاشة</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 150px;">تاريخ البدء</th>
                                        <th style="min-width: 150px;">تاريخ الانتهاء</th>
                                        <th style="min-width: 120px;">مدة الجلسة</th>
                                        <th style="min-width: 150px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td>{{ $loop->iteration + ($sessions->currentPage() - 1) * $sessions->perPage() }}</td>
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
                                                    <div class="text-muted">{{ $session->platform ?? '-' }}</div>
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
                                                <a href="{{ route('admin.user-sessions.show', $session->id) }}"
                                                   class="btn btn-sm btn-info text-white"
                                                   title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد جلسات لهذا المستخدم</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($sessions->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $sessions->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
