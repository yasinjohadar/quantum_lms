@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الجلسة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الجلسة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تفاصيل الجلسة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.user-sessions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                    <a href="{{ route('admin.user-sessions.activities', $session->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-list me-1"></i> عرض جميع الأنشطة
                    </a>
                    @if($session->is_active)
                        <button type="button"
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#endSessionModal">
                            <i class="fas fa-stop me-1"></i> إنهاء الجلسة
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">معلومات الجلسة</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات المستخدم</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">المستخدم:</th>
                                            <td>
                                                @if($session->user)
                                                    <div class="fw-semibold">{{ $session->user->name }}</div>
                                                    <small class="text-muted">{{ $session->user->email }}</small>
                                                @else
                                                    <span class="text-muted">غير مسجل</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>حالة الجلسة:</th>
                                            <td>{!! $session->status_badge !!}</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الجلسة</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">اسم الجلسة:</th>
                                            <td>{{ $session->session_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Session UUID:</th>
                                            <td><small class="text-muted">{{ $session->session_uuid ?? '-' }}</small></td>
                                        </tr>
                                        @if($session->session_description)
                                        <tr>
                                            <th>الوصف:</th>
                                            <td>{{ $session->session_description }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الشبكة</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">عنوان IP:</th>
                                            <td><code>{{ $session->ip_address ?? '-' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>نوع الاتصال:</th>
                                            <td>
                                                @if($session->connection_type)
                                                    <span class="badge bg-info-transparent text-info">
                                                        {{ ucfirst($session->connection_type) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>عرض النطاق:</th>
                                            <td>
                                                @if($session->bandwidth_mbps)
                                                    {{ number_format($session->bandwidth_mbps, 2) }} Mbps
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الجهاز</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">نوع الجهاز:</th>
                                            <td>
                                                @if($session->device_type)
                                                    <span class="badge bg-primary-transparent text-primary">
                                                        {{ ucfirst($session->device_type) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>المتصفح:</th>
                                            <td>{{ $session->browser ?? '-' }} {{ $session->browser_version ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>النظام:</th>
                                            <td>{{ $session->platform ?? '-' }} {{ $session->platform_version ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>دقة الشاشة:</th>
                                            <td>{{ $session->screen_resolution ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>User Agent:</th>
                                            <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($session->user_agent, 100) }}</small></td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الوقت</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">تاريخ البدء:</th>
                                            <td>
                                                <div class="fw-semibold">{{ $session->started_at->format('Y-m-d H:i:s') }}</div>
                                                <small class="text-muted">{{ $session->started_at->diffForHumans() }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الانتهاء:</th>
                                            <td>
                                                @if($session->ended_at)
                                                    <div class="fw-semibold">{{ $session->ended_at->format('Y-m-d H:i:s') }}</div>
                                                    <small class="text-muted">{{ $session->ended_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="badge bg-info-transparent text-info">الجلسة لا تزال نشطة</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>مدة الجلسة:</th>
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
                                        </tr>
                                    </table>
                                </div>

                                @if($session->notes)
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">ملاحظات</h6>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0">{{ $session->notes }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($session->meta)
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">معلومات إضافية (Meta)</h6>
                                    <div class="bg-light p-3 rounded">
                                        <pre class="mb-0">{{ json_encode($session->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات الأنشطة -->
            @if(isset($activityStats))
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">إحصائيات الأنشطة</h5>
                            <a href="{{ route('admin.user-sessions.activities', $session->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-list me-1"></i> عرض جميع الأنشطة
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-1">{{ number_format($activityStats['total']) }}</h4>
                                        <small class="text-muted">إجمالي الأنشطة</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-1 text-primary">{{ number_format($activityStats['page_views']) }}</h4>
                                        <small class="text-muted">عرض صفحات</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-1 text-info">{{ number_format($activityStats['actions']) }}</h4>
                                        <small class="text-muted">إجراءات</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-1 text-success">{{ number_format($activityStats['unique_pages']) }}</h4>
                                        <small class="text-muted">صفحات فريدة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- آخر الأنشطة -->
            @if(isset($activities) && $activities->count() > 0)
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">آخر الأنشطة</h5>
                            <a href="{{ route('admin.user-sessions.activities', $session->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i> عرض الكل
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                    <tr>
                                        <th>النوع</th>
                                        <th>الصفحة/الإجراء</th>
                                        <th>الوقت</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($activities->take(10) as $activity)
                                        <tr>
                                            <td>{!! $activity->activity_badge !!}</td>
                                            <td>
                                                @if($activity->page_url)
                                                    <small>{{ \Illuminate\Support\Str::limit($activity->page_url, 50) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $activity->occurred_at->format('Y-m-d H:i:s') }}</small>
                                                <br>
                                                <small class="text-muted">{{ $activity->occurred_at->diffForHumans() }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endif

            <!-- Modal for End Session -->
            @if($session->is_active)
            <div class="modal fade" id="endSessionModal" tabindex="-1" aria-labelledby="endSessionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('admin.user-sessions.end', $session->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="endSessionModalLabel">إنهاء الجلسة</h5>
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

        </div>
    </div>
@stop
