@extends('admin.layouts.master')

@section('page-title')
    كافة المدراء
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">كافة المدراء</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'admin']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> إنشاء مدير جديد
                        </a>
                    @endcan
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 20px; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>نجح!</strong> {!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 20px; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>خطأ!</strong> {!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 20px; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>خطأ في البيانات!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex gap-3">
                            <div class="flex-shrink-0 ms-auto w-100">
                                <form action="{{ route('admin.admins.index') }}" method="GET"
                                      id="adminsFiltersForm"
                                      class="d-flex flex-wrap align-items-end gap-2">
                                    <div class="d-flex flex-column">
                                        <label class="form-label small mb-1">بحث</label>
                                        <input style="min-width: 220px" type="text" name="query" class="form-control form-control-sm"
                                               placeholder="بحث بالاسم أو البريد أو الهاتف" value="{{ request('query') }}">
                                    </div>

                                    <div class="d-flex flex-column">
                                        <label class="form-label small mb-1">حالة الحساب</label>
                                        <select name="is_active" id="adminsIsActiveFilter" class="form-select form-select-sm">
                                            <option value="">كل الحالات</option>
                                            <option value="1" {{ request('is_active', '1') === '1' ? 'selected' : '' }}>مفعل</option>
                                            <option value="0" {{ request('is_active', '1') === '0' ? 'selected' : '' }}>معطل</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-search me-1"></i> بحث
                                    </button>
                                    @if(request()->has('query') || request()->has('is_active'))
                                        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary btn-sm">
                                            مسح الفلاتر
                                        </a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 40px;">#</th>
                                        <th scope="col" style="min-width: 170px;">اسم المدير</th>
                                        <th scope="col" style="min-width: 150px;">البريد الإلكتروني</th>
                                        <th scope="col" style="min-width: 120px;">الهاتف</th>
                                        <th scope="col" style="min-width: 120px;">حالة الحساب</th>
                                        <th scope="col" style="min-width: 180px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="adminsTableBody">
                                    @include('admin.pages.admins.partials.table-rows', ['admins' => $admins])
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-center" id="adminsPaginationContainer">
                                @include('admin.pages.admins.partials.pagination', ['admins' => $admins])
                            </div>
                        </div>
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
            .catch(function () {
                // في حال الفشل يمكن للصفحة أن تعمل بتحديث عادي
            });
    }

    isActiveSelect.addEventListener('change', function () {
        fetchAdmins(1);
    });

    bindPaginationLinks();
});
</script>
@stop

