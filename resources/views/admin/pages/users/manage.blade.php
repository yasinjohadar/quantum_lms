@extends('admin.layouts.master')

@section('page-title')
    كافة المستخدمين
@stop

@push('styles')
    @include('admin.pages.users.partials.manage-styles')
@endpush

@section('content')
    <div class="main-content app-content users-manage-page">
        <div class="container-fluid">

            <div class="users-manage-hero my-4">
                <div class="users-manage-hero__icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="users-manage-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">كافة المستخدمين</li>
                        </ol>
                    </nav>
                    <h4 class="users-manage-hero__title">كافة المستخدمين</h4>
                    <p class="users-manage-hero__subtitle">إدارة الحسابات، الأدوار، والصلاحيات من مكان واحد</p>
                </div>
                <div class="users-manage-stat-mini">
                    <span class="users-manage-stat-mini__value">{{ number_format($users->total()) }}</span>
                    <span class="users-manage-stat-mini__label">نتيجة مطابقة</span>
                </div>
                <div class="users-manage-hero__actions">
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-mortarboard me-1"></i> الطلاب
                    </a>
                    <a href="{{ route('admin.users.trashed.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-trash3 me-1"></i> المحذوفين سوفت
                    </a>
                    @can('user-create')
                        <a href="{{ route('users.create', ['return_context' => 'manage']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus me-1"></i> مستخدم جديد
                        </a>
                    @endcan
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="users-manage-card">
                <div class="users-manage-card__header">
                    <span class="users-manage-card__header-icon"><i class="bi bi-funnel"></i></span>
                    تصفية وبحث
                </div>
                <div class="users-manage-card__body">
                    <form id="usersManageFiltersForm" method="GET"
                          action="{{ route('admin.users.manage') }}"
                          class="users-manage-filters">
                        <input type="hidden" name="per_page" id="managePerPageHidden" value="{{ $users->perPage() }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text"
                                           name="query"
                                           class="form-control border-start-0"
                                           placeholder="الاسم، البريد، الهاتف"
                                           value="{{ request('query') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">نوع المستخدم</label>
                                <select name="user_type" id="userTypeFilter" class="form-select">
                                    <option value="">كل الأنواع</option>
                                    <option value="student" {{ request('user_type') === 'student' ? 'selected' : '' }}>طالب</option>
                                    <option value="teacher" {{ request('user_type') === 'teacher' ? 'selected' : '' }}>معلم</option>
                                    <option value="supervisor" {{ request('user_type') === 'supervisor' ? 'selected' : '' }}>مشرف</option>
                                    <option value="admin" {{ request('user_type') === 'admin' ? 'selected' : '' }}>مدير</option>
                                    <option value="other" {{ request('user_type') === 'other' ? 'selected' : '' }}>أخرى</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الدور</label>
                                <select name="role" id="roleFilter" class="form-select">
                                    <option value="">كل الأدوار</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">حالة الحساب</label>
                                <select name="is_active" id="manageIsActiveFilter" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active', '1') === '1' ? 'selected' : '' }}>مفعل</option>
                                    <option value="0" {{ request('is_active', '1') === '0' ? 'selected' : '' }}>معطل</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                @if(request()->hasAny(['query','user_type','role','is_active']) && (request('query') || request('user_type') || request('role') || request('is_active') !== null))
                                    <a href="{{ route('admin.users.manage') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-x-lg me-1"></i> مسح
                                    </a>
                                @endif
                                @include('admin.partials.per-page-toolbar', ['paginator' => $users])
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="users-manage-card">
                <div class="users-manage-card__header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="users-manage-card__header-icon"><i class="bi bi-table"></i></span>
                        قائمة المستخدمين
                    </div>
                    <span class="badge bg-primary-transparent text-primary">
                        صفحة {{ $users->currentPage() }} من {{ $users->lastPage() }}
                    </span>
                </div>
                <div class="users-manage-card__body p-0">
                    <div class="users-manage-table-wrap">
                        <div class="table-responsive">
                            <table class="table users-manage-table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 48px;">#</th>
                                    <th scope="col">اسم المستخدم</th>
                                    <th scope="col">النوع</th>
                                    <th scope="col">البريد</th>
                                    <th scope="col">الهاتف</th>
                                    <th scope="col">الأدوار</th>
                                    <th scope="col">الحالة</th>
                                    <th scope="col" style="min-width: 200px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody id="usersManageTableBody">
                                @include('admin.pages.users.partials.manage-tbody', ['users' => $users])
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="px-3 pb-3 users-manage-pagination" id="usersManagePaginationContainer">
                        @include('admin.pages.users.partials.manage-pagination', ['users' => $users])
                    </div>
                </div>
            </div>

            <div id="usersManageImpersonateModalsWrapper">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $users])
            </div>

        </div>
    </div>

    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title" id="archiveModalLabel">
                        <i class="bi bi-archive-fill me-2"></i> أرشفة المستخدم
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <h5 class="text-center mb-3">هل أنت متأكد من أرشفة هذا المستخدم؟</h5>
                    <p class="text-muted text-center mb-3"><strong id="archiveUserName"></strong></p>
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
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form id="archiveForm" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="reason" id="archiveReasonInput">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-archive me-1"></i> أرشفة
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
            if (!Number.isFinite(n)) return 25;
            return Math.min(100, Math.max(1, n));
        }
        const n = parseInt(sel.value, 10);
        if (!Number.isFinite(n)) return 25;
        return Math.min(100, Math.max(1, n));
    }
    function syncCustomPerPageUi() {
        const sel = getPerPageSelect();
        const wrap = getPerPageCustomWrap();
        if (!sel || !wrap) return;
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
        if (!params.has('is_active')) params.set('is_active', '');
        if (extraPage) params.set('page', String(extraPage));
        params.set('per_page', String(getCurrentPerPage()));
        return params;
    }

    function bindPaginationLinks() {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(function (a) {
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
            .catch(function () {});
    }

    userTypeSelect.addEventListener('change', function () { fetchUsers(1); });
    roleSelect.addEventListener('change', function () { fetchUsers(1); });
    isActiveSelect.addEventListener('change', function () { fetchUsers(1); });

    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function (e) {
            if (!e.target || e.target.id !== 'perPageSelect') return;
            syncCustomPerPageUi();
            syncManagePerPageHidden();
            if (e.target.value !== 'custom') fetchUsers(1);
        });
        perPageToolbarContainer.addEventListener('click', function (e) {
            const btn = e.target && e.target.closest ? e.target.closest('#applyCustomPerPage') : null;
            if (!btn) return;
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
            bootstrap.Modal.getOrCreateInstance(archiveModalEl).show();
        });
    }

    const archiveForm = document.getElementById('archiveForm');
    if (archiveForm) {
        archiveForm.addEventListener('submit', function () {
            const reasonEl = document.getElementById('archiveReason');
            const reasonInputEl = document.getElementById('archiveReasonInput');
            if (reasonEl && reasonInputEl) reasonInputEl.value = reasonEl.value || '';
        });
    }

    bindPaginationLinks();

    function bindCopyButtons() {
        tableBody.addEventListener('click', function (e) {
            const copyBtn = e.target.closest('.copy-btn');
            if (!copyBtn) return;
            e.preventDefault();
            e.stopPropagation();
            const textToCopy = copyBtn.getAttribute('data-copy-text');
            if (!textToCopy) return;
            navigator.clipboard.writeText(textToCopy).then(function () {
                const icon = copyBtn.querySelector('i');
                if (icon) {
                    const originalClass = icon.className;
                    icon.className = 'bi bi-check2 text-success';
                    setTimeout(function () { icon.className = originalClass; }, 1500);
                }
            }).catch(function () {});
        });
    }

    bindCopyButtons();
});
</script>
@stop
