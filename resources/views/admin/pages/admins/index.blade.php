@extends('admin.layouts.master')

@section('page-title')
    كافة المدراء
@stop

@push('styles')
    @include('admin.pages.admins.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content admins-page">
        <div class="container-fluid">

            <div class="admins-hero my-4">
                <div class="admins-hero__icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="admins-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">كافة المدراء</li>
                        </ol>
                    </nav>
                    <h4 class="admins-hero__title">كافة المدراء</h4>
                    <p class="admins-hero__subtitle">إدارة حسابات المدراء، الصلاحيات، وسجلات الدخول</p>
                </div>
                <div class="admins-hero__stat">
                    <span class="admins-hero__stat-value">{{ number_format($admins->total()) }}</span>
                    <span class="admins-hero__stat-label">مدير مطابق</span>
                </div>
                <div class="admins-hero__actions">
                    <a href="{{ route('admin.users.manage') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-people me-1"></i> كل المستخدمين
                    </a>
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'admin']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus me-1"></i> مدير جديد
                        </a>
                    @endcan
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="admins-card">
                <div class="admins-card__header">
                    <span class="admins-card__header-icon"><i class="bi bi-funnel"></i></span>
                    تصفية وبحث
                </div>
                <div class="admins-card__body">
                    <form action="{{ route('admin.admins.index') }}" method="GET"
                          id="adminsFiltersForm"
                          class="admins-filters">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-5">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="query" class="form-control"
                                           placeholder="بحث بالاسم أو البريد أو الهاتف" value="{{ request('query') }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label" for="adminsIsActiveFilter">حالة الحساب</label>
                                <select name="is_active" id="adminsIsActiveFilter" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active', '1') === '1' ? 'selected' : '' }}>مفعل</option>
                                    <option value="0" {{ request('is_active', '1') === '0' ? 'selected' : '' }}>معطل</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-lg-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                            </div>
                            @if(request()->has('query') || request()->has('is_active'))
                                <div class="col-md-2 col-lg-2">
                                    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary w-100">
                                        مسح
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="admins-card">
                <div class="admins-card__header">
                    <span class="admins-card__header-icon"><i class="bi bi-table"></i></span>
                    قائمة المدراء
                </div>
                <div class="admins-card__body">
                    <div class="admins-table-wrap" id="adminsTableWrap">
                        <div class="admins-loading-overlay" id="adminsLoadingOverlay">
                            <div class="spinner-border text-danger spinner-border-sm" role="status"></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table admins-table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 48px;">#</th>
                                    <th scope="col">اسم المدير</th>
                                    <th scope="col">البريد الإلكتروني</th>
                                    <th scope="col">الهاتف</th>
                                    <th scope="col">حالة الحساب</th>
                                    <th scope="col" style="min-width: 140px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody id="adminsTableBody">
                                @include('admin.pages.admins.partials.table-rows', ['admins' => $admins])
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="admins-pagination" id="adminsPaginationContainer">
                        @include('admin.pages.admins.partials.pagination', ['admins' => $admins])
                    </div>
                </div>
            </div>

            <div id="adminsImpersonateModalsWrapper">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $admins])
            </div>

        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminsFiltersForm');
    const isActiveSelect = document.getElementById('adminsIsActiveFilter');
    const tableBody = document.getElementById('adminsTableBody');
    const paginationContainer = document.getElementById('adminsPaginationContainer');
    const impersonateModalsWrapper = document.getElementById('adminsImpersonateModalsWrapper');
    const loadingOverlay = document.getElementById('adminsLoadingOverlay');
    if (!form || !isActiveSelect || !tableBody || !paginationContainer) return;

    function buildParams(extraPage) {
        const params = new URLSearchParams(new FormData(form));
        if (!params.has('is_active')) {
            params.set('is_active', '');
        }
        if (extraPage) {
            params.set('page', String(extraPage));
        }
        return params;
    }

    function bindPaginationLinks() {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || '1';
                fetchAdmins(page);
            });
        });
    }

    function fetchAdmins(page) {
        const params = buildParams(page);
        if (loadingOverlay) loadingOverlay.classList.add('is-active');

        fetch(`{{ route('admin.admins.index') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (!data.success) throw new Error('Invalid response');
                tableBody.innerHTML = data.html || '';
                paginationContainer.innerHTML = data.pagination || '';
                if (impersonateModalsWrapper && typeof data.impersonate_modals === 'string') {
                    impersonateModalsWrapper.innerHTML = data.impersonate_modals;
                }
                bindPaginationLinks();
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.replaceState({}, '', newUrl);
            })
            .catch(function () {})
            .finally(function () {
                if (loadingOverlay) loadingOverlay.classList.remove('is-active');
            });
    }

    isActiveSelect.addEventListener('change', function () {
        fetchAdmins(1);
    });

    bindPaginationLinks();
});
</script>
@stop
