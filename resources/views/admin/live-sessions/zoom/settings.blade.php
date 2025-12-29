@extends('admin.layouts.master')

@section('page-title')
    إعدادات Zoom
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات Zoom</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.live-sessions.index') }}">الجلسات الحية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إعدادات Zoom</li>
                    </ol>
                </nav>
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
            <div class="col-lg-12">
                <!-- إدارة حسابات Zoom -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i> حسابات Zoom
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                            <i class="fas fa-plus me-1"></i> إضافة حساب جديد
                        </button>
                    </div>
                    <div class="card-body">
                        @if($accounts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>الاسم</th>
                                            <th>النوع</th>
                                            <th>Client ID</th>
                                            <th>SDK Key</th>
                                            <th>الحالة</th>
                                            <th>افتراضي</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accounts as $account)
                                            <tr>
                                                <td>
                                                    <strong>{{ $account->name }}</strong>
                                                    @if($account->description)
                                                        <br><small class="text-muted">{{ $account->description }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($account->type === 'api')
                                                        <span class="badge bg-primary">API (Server-to-Server)</span>
                                                    @else
                                                        <span class="badge bg-info">OAuth App</span>
                                                    @endif
                                                </td>
                                                <td><code>{{ substr($account->client_id, 0, 20) }}...</code></td>
                                                <td>
                                                    @if($account->sdk_key)
                                                        <code>{{ substr($account->sdk_key, 0, 20) }}...</code>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($account->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($account->is_default)
                                                        <span class="badge bg-warning">افتراضي</span>
                                                    @else
                                                        <form action="{{ route('admin.zoom.accounts.set-default', $account) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">تعيين كافتراضي</button>
                                                        </form>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editAccountModal{{ $account->id }}">
                                                            <i class="fas fa-edit"></i> تعديل
                                                        </button>
                                                        <form action="{{ route('admin.zoom.accounts.delete', $account) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> حذف
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد حسابات Zoom. قم بإضافة حساب جديد للبدء.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- إعدادات نافذة الانضمام -->
                <form action="{{ route('admin.zoom.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock me-2"></i> إعدادات نافذة الانضمام
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">دقائق قبل بدء الجلسة <span class="text-danger">*</span></label>
                                    <input type="number" name="join_window_before_minutes" 
                                           class="form-control @error('join_window_before_minutes') is-invalid @enderror" 
                                           value="{{ old('join_window_before_minutes', $config['join_window_before_minutes']) }}" 
                                           min="0" max="60" required>
                                    <small class="text-muted">عدد الدقائق قبل بدء الجلسة التي يمكن للطلاب الانضمام فيها</small>
                                    @error('join_window_before_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">دقائق بعد انتهاء الجلسة <span class="text-danger">*</span></label>
                                    <input type="number" name="join_window_after_minutes" 
                                           class="form-control @error('join_window_after_minutes') is-invalid @enderror" 
                                           value="{{ old('join_window_after_minutes', $config['join_window_after_minutes']) }}" 
                                           min="0" max="120" required>
                                    <small class="text-muted">عدد الدقائق بعد انتهاء الجلسة التي يمكن للطلاب الانضمام فيها</small>
                                    @error('join_window_after_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إعدادات رمز الانضمام -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-ticket-alt me-2"></i> إعدادات رمز الانضمام
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">مدة صلاحية الرمز (بالدقائق) <span class="text-danger">*</span></label>
                                    <input type="number" name="token_ttl_minutes" 
                                           class="form-control @error('token_ttl_minutes') is-invalid @enderror" 
                                           value="{{ old('token_ttl_minutes', $config['token_ttl_minutes']) }}" 
                                           min="1" max="60" required>
                                    <small class="text-muted">مدة صلاحية رمز الانضمام بالدقائق</small>
                                    @error('token_ttl_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الحد الأقصى لاستخدامات الرمز <span class="text-danger">*</span></label>
                                    <input type="number" name="token_max_uses" 
                                           class="form-control @error('token_max_uses') is-invalid @enderror" 
                                           value="{{ old('token_max_uses', $config['token_max_uses']) }}" 
                                           min="1" max="10" required>
                                    <small class="text-muted">عدد المرات التي يمكن استخدام الرمز فيها</small>
                                    @error('token_max_uses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إعدادات Rate Limiting -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i> إعدادات Rate Limiting
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">حد الطلبات لكل مستخدم/دقيقة <span class="text-danger">*</span></label>
                                    <input type="number" name="rate_limit_per_user" 
                                           class="form-control @error('rate_limit_per_user') is-invalid @enderror" 
                                           value="{{ old('rate_limit_per_user', $config['rate_limit_per_user']) }}" 
                                           min="1" max="100" required>
                                    @error('rate_limit_per_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">حد الطلبات لكل جلسة/دقيقة <span class="text-danger">*</span></label>
                                    <input type="number" name="rate_limit_per_session" 
                                           class="form-control @error('rate_limit_per_session') is-invalid @enderror" 
                                           value="{{ old('rate_limit_per_session', $config['rate_limit_per_session']) }}" 
                                           min="1" max="500" required>
                                    @error('rate_limit_per_session')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">حد الطلبات لكل IP/دقيقة <span class="text-danger">*</span></label>
                                    <input type="number" name="rate_limit_per_ip" 
                                           class="form-control @error('rate_limit_per_ip') is-invalid @enderror" 
                                           value="{{ old('rate_limit_per_ip', $config['rate_limit_per_ip']) }}" 
                                           min="1" max="200" required>
                                    @error('rate_limit_per_ip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إعدادات الأمان -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shield-alt me-2"></i> إعدادات الأمان
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_device_binding" 
                                               id="enable_device_binding" value="1"
                                               {{ old('enable_device_binding', $config['enable_device_binding']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_device_binding">
                                            تفعيل ربط الجهاز (Device Binding)
                                        </label>
                                    </div>
                                    <small class="text-muted">ربط رمز الانضمام بجهاز معين لمنع المشاركة</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_ip_binding" 
                                               id="enable_ip_binding" value="1"
                                               {{ old('enable_ip_binding', $config['enable_ip_binding']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_ip_binding">
                                            تفعيل ربط IP (IP Binding)
                                        </label>
                                    </div>
                                    <small class="text-muted">ربط رمز الانضمام بعنوان IP لمنع المشاركة</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">طول بادئة IP <span class="text-danger">*</span></label>
                                    <input type="number" name="ip_prefix_length" 
                                           class="form-control @error('ip_prefix_length') is-invalid @enderror" 
                                           value="{{ old('ip_prefix_length', $config['ip_prefix_length']) }}" 
                                           min="1" max="4" required>
                                    <small class="text-muted">عدد الأوكتات من عنوان IP التي سيتم استخدامها للربط (مثال: 3 = أول 3 أوكتات)</small>
                                    @error('ip_prefix_length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ الإعدادات
                                </button>
                                <a href="{{ route('admin.live-sessions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-1"></i> رجوع
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: إضافة حساب جديد -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.zoom.accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalLabel">إضافة حساب Zoom جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    @include('admin.live-sessions.zoom.partials.account-form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals: تعديل الحسابات -->
@foreach($accounts as $account)
<div class="modal fade" id="editAccountModal{{ $account->id }}" tabindex="-1" aria-labelledby="editAccountModalLabel{{ $account->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.zoom.accounts.update', $account) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editAccountModalLabel{{ $account->id }}">تعديل حساب: {{ $account->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    @include('admin.live-sessions.zoom.partials.account-form', ['account' => $account])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
