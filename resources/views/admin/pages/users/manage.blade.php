@extends('admin.layouts.master')

@section('page-title')
    كافة المستخدمين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">كافة المستخدمين</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('user-create')
                        <a href="{{ route('users.create', ['return_context' => 'manage']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> إنشاء مستخدم جديد
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
                        <div class="card-header align-items-center d-flex flex-wrap gap-3">
                            <form id="usersManageFiltersForm" method="GET"
                                  action="{{ route('admin.users.manage') }}"
                                  class="d-flex flex-wrap align-items-end gap-2 w-100">
                                <input type="hidden" name="per_page" id="managePerPageHidden" value="{{ $users->perPage() }}">
                                <div class="d-flex flex-column">
                                    <label class="form-label small mb-1">بحث</label>
                                    <input type="text"
                                           name="query"
                                           class="form-control form-control-sm"
                                           style="min-width: 220px"
                                           placeholder="الاسم، البريد، الهاتف"
                                           value="{{ request('query') }}">
                                </div>

                                <div class="d-flex flex-column">
                                    <label class="form-label small mb-1">نوع المستخدم</label>
                                    <select name="user_type" id="userTypeFilter" class="form-select form-select-sm" style="min-width: 160px;">
                                        <option value="">كل الأنواع</option>
                                        <option value="student" {{ request('user_type') === 'student' ? 'selected' : '' }}>طالب</option>
                                        <option value="teacher" {{ request('user_type') === 'teacher' ? 'selected' : '' }}>معلم</option>
                                        <option value="supervisor" {{ request('user_type') === 'supervisor' ? 'selected' : '' }}>مشرف</option>
                                        <option value="admin" {{ request('user_type') === 'admin' ? 'selected' : '' }}>مدير</option>
                                        <option value="other" {{ request('user_type') === 'other' ? 'selected' : '' }}>أخرى</option>
                                    </select>
                                </div>

                                <div class="d-flex flex-column">
                                    <label class="form-label small mb-1">الدور</label>
                                    <select name="role" id="roleFilter" class="form-select form-select-sm" style="min-width: 160px;">
                                        <option value="">كل الأدوار</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex flex-column">
                                    <label class="form-label small mb-1">حالة الحساب</label>
                                    <select name="is_active" id="manageIsActiveFilter" class="form-select form-select-sm" style="min-width: 140px;">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_active', '1') === '1' ? 'selected' : '' }}>مفعل</option>
                                        <option value="0" {{ request('is_active', '1') === '0' ? 'selected' : '' }}>معطل</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                                @if(request()->hasAny(['query','user_type','role','is_active']) && (request('query') || request('user_type') || request('role') || request('is_active') !== null))
                                    <a href="{{ route('admin.users.manage') }}" class="btn btn-outline-secondary btn-sm">
                                        مسح الفلاتر
                                    </a>
                                @endif
                                @include('admin.partials.per-page-toolbar', ['paginator' => $users])
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 40px;">#</th>
                                        <th scope="col" style="min-width: 170px;">اسم المستخدم</th>
                                        <th scope="col" style="min-width: 110px;">نوع المستخدم</th>
                                        <th scope="col" style="min-width: 150px;">البريد</th>
                                        <th scope="col" style="min-width: 130px;">الهاتف</th>
                                        <th scope="col" style="min-width: 160px;">الأدوار</th>
                                        <th scope="col" style="min-width: 120px;">حالة الحساب</th>
                                        <th scope="col" style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="usersManageTableBody">
                                    @include('admin.pages.users.partials.manage-tbody', ['users' => $users])
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-center" id="usersManagePaginationContainer">
                                @include('admin.pages.users.partials.manage-pagination', ['users' => $users])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="usersManageImpersonateModalsWrapper">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $users])
            </div>

        </div>
    </div>

    <!-- Modal أرشفة المستخدم -->
    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="archiveModalLabel">
                        <i class="bi bi-archive-fill me-2"></i> أرشفة المستخدم
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-archive-fill text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-center mb-3">هل أنت متأكد من أرشفة هذا المستخدم؟</h5>
                    <p class="text-muted text-center mb-3">
                        <strong id="archiveUserName"></strong>
                    </p>
                    <div class="mb-3">
                        <label for="archiveReason" class="form-label">سبب الأرشفة (اختياري)</label>
                        <textarea class="form-control" id="archiveReason" name="reason" rows="3"
                                  placeholder="أدخل سبب الأرشفة (اختياري)"></textarea>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        سيتم نقل المستخدم إلى الأرشيف ويمكن استعادته لاحقاً.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <form id="archiveForm" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="reason" id="archiveReasonInput">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-archive me-1"></i> نعم، أرشفة المستخدم
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('usersManageFiltersForm');
    const userTypeSelect = document.getElementById('userTypeFilter');
    const roleSelect = document.getElementById('roleFilter');
    const isActiveSelect = document.getElementById('manageIsActiveFilter');
    const tableBody = document.getElementById('usersManageTableBody');
    const paginationContainer = document.getElementById('usersManagePaginationContainer');
    const impersonateModalsWrapper = document.getElementById('usersManageImpersonateModalsWrapper');
    const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');
    const managePerPageHidden = document.getElementById('managePerPageHidden');
    if (!form || !userTypeSelect || !roleSelect || !isActiveSelect || !tableBody || !paginationContainer) return;

    function getPerPageSelect() {
        return document.getElementById('perPageSelect');
    }
    function getPerPageCustomWrap() {
        return document.getElementById('perPageCustomWrap');
    }
    function getCurrentPerPage() {
        const sel = getPerPageSelect();
        if (!sel) {
            return managePerPageHidden ? parseInt(managePerPageHidden.value, 10) || 25 : 25;
        }
        if (sel.value === 'custom') {
            const input = document.getElementById('perPageCustom');
            const n = input ? parseInt(input.value, 10) : NaN;
            if (!Number.isFinite(n)) {
                return 25;
            }
            return Math.min(100, Math.max(1, n));
        }
        const n = parseInt(sel.value, 10);
        if (!Number.isFinite(n)) {
            return 25;
        }
        return Math.min(100, Math.max(1, n));
    }
    function syncCustomPerPageUi() {
        const sel = getPerPageSelect();
        const wrap = getPerPageCustomWrap();
        if (!sel || !wrap) {
            return;
        }
        if (sel.value === 'custom') {
            wrap.classList.remove('d-none');
            wrap.classList.add('d-flex');
        } else {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }
    function syncManagePerPageHidden() {
        if (managePerPageHidden) {
            managePerPageHidden.value = String(getCurrentPerPage());
        }
    }

    function buildParams(extraPage) {
        syncManagePerPageHidden();
        const params = new URLSearchParams(new FormData(form));
        if (!params.has('is_active')) {
            params.set('is_active', '');
        }
        if (extraPage) {
            params.set('page', String(extraPage));
        }
        params.set('per_page', String(getCurrentPerPage()));
        return params;
    }

    function bindPaginationLinks() {
        paginationContainer.querySelectorAll('a[href*=\"page=\"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || '1';
                fetchUsers(page);
            });
        });
    }

    function fetchUsers(page) {
        const params = buildParams(page);
        fetch(`{{ route('admin.users.manage') }}?${params.toString()}`, {
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
                syncCustomPerPageUi();
                syncManagePerPageHidden();
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.replaceState({}, '', newUrl);
            })
            .catch(function () {
                // في حال الفشل يمكن استخدام التحديث العادي
            });
    }

    userTypeSelect.addEventListener('change', function () {
        fetchUsers(1);
    });

    roleSelect.addEventListener('change', function () {
        fetchUsers(1);
    });

    isActiveSelect.addEventListener('change', function () {
        fetchUsers(1);
    });

    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function (e) {
            if (!e.target || e.target.id !== 'perPageSelect') {
                return;
            }
            syncCustomPerPageUi();
            syncManagePerPageHidden();
            if (e.target.value !== 'custom') {
                fetchUsers(1);
            }
        });
        perPageToolbarContainer.addEventListener('click', function (e) {
            const btn = e.target && e.target.closest ? e.target.closest('#applyCustomPerPage') : null;
            if (!btn) {
                return;
            }
            e.preventDefault();
            const sel = getPerPageSelect();
            const input = document.getElementById('perPageCustom');
            if (sel && sel.value === 'custom' && input) {
                const raw = parseInt(input.value, 10);
                if (!Number.isFinite(raw) || raw < 1 || raw > 100) {
                    alert('أدخل عدداً بين 1 و 100');
                    return;
                }
            }
            syncManagePerPageHidden();
            fetchUsers(1);
        });
    }

    syncCustomPerPageUi();
    syncManagePerPageHidden();

    // Archive modal handlers (works with AJAX-updated rows)
    if (tableBody) {
        tableBody.addEventListener('click', function (e) {
            const archiveBtn = e.target.closest('.archive-user-btn');
            if (!archiveBtn) return;

            e.preventDefault();
            e.stopPropagation();

            const userId = archiveBtn.getAttribute('data-user-id');
            const userName = archiveBtn.getAttribute('data-user-name');
            if (!userId || !userName) return;

            const archiveUserNameEl = document.getElementById('archiveUserName');
            const archiveFormEl = document.getElementById('archiveForm');
            const archiveReasonEl = document.getElementById('archiveReason');
            const archiveReasonInputEl = document.getElementById('archiveReasonInput');
            const archiveModalEl = document.getElementById('archiveModal');
            if (!archiveUserNameEl || !archiveFormEl || !archiveModalEl) return;

            archiveUserNameEl.textContent = userName;
            archiveFormEl.action = '{{ route("admin.users.archive", ":id") }}'.replace(':id', userId);
            if (archiveReasonEl) archiveReasonEl.value = '';
            if (archiveReasonInputEl) archiveReasonInputEl.value = '';

            const archiveModal = bootstrap.Modal.getOrCreateInstance(archiveModalEl);
            archiveModal.show();
        });
    }

    const archiveForm = document.getElementById('archiveForm');
    if (archiveForm) {
        archiveForm.addEventListener('submit', function () {
            const reasonEl = document.getElementById('archiveReason');
            const reasonInputEl = document.getElementById('archiveReasonInput');
            if (reasonEl && reasonInputEl) {
                reasonInputEl.value = reasonEl.value || '';
            }
        });
    }

    bindPaginationLinks();
});
</script>
@stop

