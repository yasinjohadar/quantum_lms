@extends('admin.layouts.master')

@section('page-title')
    تخصيص المشرفين
@stop

@push('styles')
    @include('admin.pages.supervisors.partials.assignments-index-styles')
@endpush

@section('content')
    <div class="main-content app-content supervisors-page">
        <div class="container-fluid">

            <div class="supervisors-hero my-4">
                <div class="supervisors-hero__icon">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div class="supervisors-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تخصيص المشرفين</li>
                        </ol>
                    </nav>
                    <h4 class="supervisors-hero__title">تخصيص المشرفين للصفوف والمواد</h4>
                    <p class="supervisors-hero__subtitle">إدارة صلاحيات المشرفين وربطهم بالصفوف والمواد الدراسية</p>
                </div>
                <div class="supervisors-hero__actions">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus me-1"></i> مشرف جديد
                        </a>
                    @endcan
                    <a href="{{ route('users.index', ['role' => 'supervisor']) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-people me-1"></i> جميع المشرفين
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $validationError)
                            <li>{{ $validationError }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if(isset($totalSupervisors))
                <div class="supervisors-stats">
                    <div class="supervisors-stat-card supervisors-stat-card--total">
                        <div>
                            <div class="supervisors-stat-card__label">إجمالي المشرفين</div>
                            <div class="supervisors-stat-card__value">{{ number_format($totalSupervisors) }}</div>
                        </div>
                        <span class="supervisors-stat-card__icon"><i class="bi bi-people-fill"></i></span>
                    </div>
                    <div class="supervisors-stat-card supervisors-stat-card--assigned">
                        <div>
                            <div class="supervisors-stat-card__label">مشرفون مخصصون</div>
                            <div class="supervisors-stat-card__value">{{ number_format($assignedSupervisors) }}</div>
                        </div>
                        <span class="supervisors-stat-card__icon"><i class="bi bi-check2-circle"></i></span>
                    </div>
                    <div class="supervisors-stat-card supervisors-stat-card--unassigned">
                        <div>
                            <div class="supervisors-stat-card__label">غير مخصصين</div>
                            <div class="supervisors-stat-card__value">{{ number_format($unassignedSupervisors) }}</div>
                        </div>
                        <span class="supervisors-stat-card__icon"><i class="bi bi-exclamation-circle"></i></span>
                    </div>
                </div>
            @endif

            <div class="supervisors-card">
                <div class="supervisors-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="supervisors-card__header-icon"><i class="bi bi-funnel"></i></span>
                        تصفية وبحث
                    </div>
                </div>
                <div class="supervisors-card__body">
                    <form id="supervisorFiltersForm" class="supervisors-filters">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" id="supervisorSearch"
                                           class="form-control border-start-0"
                                           placeholder="الاسم أو البريد الإلكتروني"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الصف</label>
                                <select name="class_id" id="supervisorClassFilter" class="form-select">
                                    <option value="">جميع الصفوف</option>
                                    @foreach($filterClasses ?? [] as $class)
                                        <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}@if($class->stage) — {{ $class->stage->name }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">المادة</label>
                                <select name="subject_id" id="supervisorSubjectFilter" class="form-select"
                                        {{ request('class_id') ? '' : 'disabled' }}>
                                    @if(!request('class_id'))
                                        <option value="">اختر الصف أولاً</option>
                                    @else
                                        <option value="">جميع المواد</option>
                                        @foreach($filterSubjects ?? [] as $subject)
                                            <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الدور</label>
                                <select name="role" id="supervisorRoleFilter" class="form-select">
                                    <option value="">كل الأدوار</option>
                                    @foreach($filterRoles ?? [] as $role)
                                        <option value="{{ $role->name }}" {{ (string) request('role') === (string) $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2 align-items-end">
                                <button type="button" id="supervisorSearchBtn" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <button type="button" id="supervisorClearFiltersBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-lg me-1"></i> مسح
                                </button>
                                @include('admin.partials.per-page-toolbar', ['paginator' => $supervisors])
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="supervisors-card">
                <div class="supervisors-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="supervisors-card__header-icon"><i class="bi bi-table"></i></span>
                        قائمة المشرفين
                    </div>
                    <span class="badge bg-primary-transparent text-primary">
                        صفحة {{ $supervisors->currentPage() }} من {{ $supervisors->lastPage() }}
                    </span>
                </div>
                <div class="supervisors-card__body p-0 position-relative">
                    <div id="supervisorsLoading" class="supervisors-loading-overlay" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted small mt-2 mb-0">جاري التحميل...</p>
                        </div>
                    </div>

                    <div id="supervisorsTableWrapper">
                        @if($supervisors->count() > 0)
                            <div class="supervisors-table-wrap mx-3 mt-3 mb-0">
                                <div class="table-responsive">
                                    <table class="table supervisors-table align-middle mb-0">
                                        <thead>
                                        <tr>
                                            <th style="width: 48px;">#</th>
                                            <th>الاسم</th>
                                            <th>البريد</th>
                                            <th>الأدوار</th>
                                            <th>الصفوف</th>
                                            <th>المواد</th>
                                            <th>الاختبارات</th>
                                            <th>آخر دخول</th>
                                            <th>الاتصال</th>
                                            <th style="min-width: 160px;">الإجراءات</th>
                                        </tr>
                                        </thead>
                                        <tbody id="supervisorsTableBody">
                                        @include('admin.pages.supervisors.partials.table-rows', ['supervisors' => $supervisors, 'lastLogins' => $lastLogins, 'onlineUserIds' => $onlineUserIds])
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="paginationContainer" class="px-3 pb-3 pt-3">
                                @include('admin.pages.supervisors.partials.pagination', ['supervisors' => $supervisors])
                            </div>
                        @else
                            <div id="supervisorsEmptyState" class="supervisors-empty">
                                <i class="bi bi-person-workspace"></i>
                                <p class="mb-2 fw-semibold">لا يوجد مشرفين مطابقين للفلاتر</p>
                                @can('user-create')
                                    <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-person-plus me-1"></i> إضافة مشرف جديد
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div id="supervisorDeleteModals">
                @include('admin.pages.supervisors.partials.delete-modals', ['supervisors' => $supervisors])
            </div>

            <div id="supervisorImpersonateModals">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $supervisors])
            </div>

            <div id="supervisorResetPasswordModals">
                @include('admin.pages.supervisors.partials.reset-password-modals', ['supervisors' => $supervisors])
            </div>

        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fetchUrl = '{{ route("admin.supervisors.assignments.index") }}';
    const getSubjectsUrl = '{{ route("admin.supervisors.assignments.subjects-by-class") }}';

    const searchInput = document.getElementById('supervisorSearch');
    const classFilter = document.getElementById('supervisorClassFilter');
    const subjectFilter = document.getElementById('supervisorSubjectFilter');
    const roleFilter = document.getElementById('supervisorRoleFilter');
    const searchBtn = document.getElementById('supervisorSearchBtn');
    const clearBtn = document.getElementById('supervisorClearFiltersBtn');
    const loadingEl = document.getElementById('supervisorsLoading');
    const tableWrapper = document.getElementById('supervisorsTableWrapper');
    const modalsContainer = document.getElementById('supervisorDeleteModals');
    const impersonateModalsContainer = document.getElementById('supervisorImpersonateModals');
    const resetPasswordModalsContainer = document.getElementById('supervisorResetPasswordModals');
    const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');

    const tableHeadHtml = `
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th>الاسم</th>
                <th>البريد</th>
                <th>الأدوار</th>
                <th>الصفوف</th>
                <th>المواد</th>
                <th>آخر دخول</th>
                <th>الاتصال</th>
                <th style="min-width: 160px;">الإجراءات</th>
            </tr>
        </thead>`;

    function getPerPageSelect() {
        return document.getElementById('perPageSelect');
    }
    function getPerPageCustomWrap() {
        return document.getElementById('perPageCustomWrap');
    }
    function getCurrentPerPage() {
        const sel = getPerPageSelect();
        if (!sel) return 25;
        if (sel.value === 'custom') {
            const input = document.getElementById('perPageCustom');
            const n = input ? parseInt(input.value, 10) : NaN;
            return Number.isFinite(n) ? Math.min(100, Math.max(1, n)) : 25;
        }
        const n = parseInt(sel.value, 10);
        return Number.isFinite(n) ? Math.min(100, Math.max(1, n)) : 25;
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

    let supervisorsTableBody = document.getElementById('supervisorsTableBody');
    let paginationContainer = document.getElementById('paginationContainer');
    let searchTimeout;
    let currentPage = 1;

    function buildParams(page) {
        const params = new URLSearchParams();
        if (searchInput && searchInput.value.trim()) params.append('search', searchInput.value.trim());
        if (classFilter && classFilter.value) params.append('class_id', classFilter.value);
        if (subjectFilter && subjectFilter.value) params.append('subject_id', subjectFilter.value);
        if (roleFilter && roleFilter.value) params.append('role', roleFilter.value);
        params.append('page', page || 1);
        params.set('per_page', String(getCurrentPerPage()));
        return params;
    }

    function loadSubjectsByClass(classId, preserveSelected) {
        const selectedSubjectId = preserveSelected && subjectFilter ? subjectFilter.value : null;
        if (!classId) {
            if (subjectFilter) {
                subjectFilter.disabled = true;
                subjectFilter.innerHTML = '<option value="">اختر الصف أولاً</option>';
            }
            return Promise.resolve();
        }
        if (subjectFilter) {
            subjectFilter.disabled = true;
            subjectFilter.innerHTML = '<option value="">جاري التحميل...</option>';
        }
        return fetch(`${getSubjectsUrl}?class_id=${encodeURIComponent(classId)}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
        .then(data => {
            if (!subjectFilter) return;
            subjectFilter.disabled = false;
            subjectFilter.innerHTML = '<option value="">جميع المواد</option>';
            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(subject => {
                    const opt = document.createElement('option');
                    opt.value = subject.id;
                    opt.textContent = subject.name;
                    if (preserveSelected && selectedSubjectId && String(selectedSubjectId) === String(subject.id)) {
                        opt.selected = true;
                    }
                    subjectFilter.appendChild(opt);
                });
            }
        })
        .catch(() => {
            if (subjectFilter) {
                subjectFilter.disabled = false;
                subjectFilter.innerHTML = '<option value="">خطأ في التحميل</option>';
            }
        });
    }

    function ensureTableShell() {
        const emptyEl = document.getElementById('supervisorsEmptyState');
        if (emptyEl) emptyEl.remove();

        let tableWrap = tableWrapper.querySelector('.supervisors-table-wrap');
        if (!tableWrap) {
            tableWrapper.innerHTML = `
                <div class="supervisors-table-wrap mx-3 mt-3 mb-0">
                    <div class="table-responsive">
                        <table class="table supervisors-table align-middle mb-0">
                            ${tableHeadHtml}
                            <tbody id="supervisorsTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="paginationContainer" class="px-3 pb-3 pt-3"></div>`;
            supervisorsTableBody = document.getElementById('supervisorsTableBody');
            paginationContainer = document.getElementById('paginationContainer');
        } else {
            supervisorsTableBody = document.getElementById('supervisorsTableBody');
            if (!paginationContainer || !tableWrapper.contains(paginationContainer)) {
                const pag = document.createElement('div');
                pag.id = 'paginationContainer';
                pag.className = 'px-3 pb-3 pt-3';
                tableWrapper.appendChild(pag);
                paginationContainer = pag;
            }
        }
    }

    function showEmptyState() {
        tableWrapper.innerHTML = `
            <div id="supervisorsEmptyState" class="supervisors-empty">
                <i class="bi bi-person-workspace"></i>
                <p class="mb-2 fw-semibold">لا يوجد مشرفين مطابقين للفلاتر</p>
                @can('user-create')
                <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus me-1"></i> إضافة مشرف جديد
                </a>
                @endcan
            </div>`;
        supervisorsTableBody = null;
        paginationContainer = null;
    }

    function attachPaginationListeners() {
        if (!paginationContainer) return;
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                fetchSupervisors(parseInt(url.searchParams.get('page') || 1, 10));
            });
        });
        syncCustomPerPageUi();
    }

    function fetchSupervisors(page) {
        currentPage = page || 1;
        const params = buildParams(currentPage);
        loadingEl.style.display = 'flex';

        fetch(`${fetchUrl}?${params.toString()}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Error');
            if (modalsContainer && typeof data.modals === 'string') modalsContainer.innerHTML = data.modals;
            if (impersonateModalsContainer && typeof data.impersonate_modals === 'string') {
                impersonateModalsContainer.innerHTML = data.impersonate_modals;
            }
            if (resetPasswordModalsContainer && typeof data.reset_password_modals === 'string') {
                resetPasswordModalsContainer.innerHTML = data.reset_password_modals;
            }
            if (data.html && data.html.trim() !== '') {
                ensureTableShell();
                if (supervisorsTableBody) supervisorsTableBody.innerHTML = data.html;
                if (paginationContainer) paginationContainer.innerHTML = data.pagination || '';
                attachPaginationListeners();
            } else {
                showEmptyState();
                if (impersonateModalsContainer) impersonateModalsContainer.innerHTML = '';
                if (resetPasswordModalsContainer) resetPasswordModalsContainer.innerHTML = '';
            }
            window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
        })
        .catch(() => {
            ensureTableShell();
            if (supervisorsTableBody) {
                supervisorsTableBody.innerHTML = `
                    <tr><td colspan="9" class="text-center py-4">
                        <div class="alert alert-danger mb-0">حدث خطأ أثناء جلب البيانات</div>
                    </td></tr>`;
            }
        })
        .finally(() => { loadingEl.style.display = 'none'; });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchSupervisors(1), 500);
        });
    }
    if (classFilter) {
        classFilter.addEventListener('change', function() {
            loadSubjectsByClass(this.value, false).then(() => fetchSupervisors(1));
        });
    }
    if (subjectFilter) subjectFilter.addEventListener('change', () => fetchSupervisors(1));
    if (searchBtn) searchBtn.addEventListener('click', () => fetchSupervisors(1));
    if (roleFilter) roleFilter.addEventListener('change', () => fetchSupervisors(1));
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (classFilter) classFilter.value = '';
            if (roleFilter) roleFilter.value = '';
            loadSubjectsByClass('', false).then(() => fetchSupervisors(1));
        });
    }
    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function(e) {
            if (!e.target || e.target.id !== 'perPageSelect') return;
            syncCustomPerPageUi();
            if (e.target.value !== 'custom') fetchSupervisors(1);
        });
        perPageToolbarContainer.addEventListener('click', function(e) {
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
            fetchSupervisors(1);
        });
    }

    syncCustomPerPageUi();
    attachPaginationListeners();
});

function copyLink(userId) {
    const linkInput = document.getElementById('impersonateLink' + userId);
    if (!linkInput) return;
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);
    document.execCommand('copy');
    const button = event.target.closest('button');
    if (!button) return;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check me-1"></i> تم النسخ';
    button.classList.remove('btn-secondary');
    button.classList.add('btn-success');
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-secondary');
    }, 2000);
}

// دوال عامة لمودال "إعادة تعيين كلمة مرور المشرف" — مُعرَّفة مرة واحدة هنا (وليس داخل
// جزئي يتكرر لكل مشرف)، لتفادي إعادة كتابة أحد المشرفين لتعريف الآخر.
function generateSupervisorPassword(length = 10) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let value = '';
    for (let i = 0; i < length; i++) {
        value += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return value;
}

function generateSupervisorPasswordSuggestions(supervisorId) {
    const container = document.getElementById('resetSupervisorPasswordSuggestions' + supervisorId);
    if (!container) return;
    container.innerHTML = '';

    for (let i = 0; i < 3; i++) {
        const suggestion = generateSupervisorPassword();
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'btn btn-sm btn-outline-primary font-monospace';
        chip.textContent = suggestion;
        chip.title = 'اضغط لاستخدام هذه الكلمة';
        chip.addEventListener('click', function () {
            const passwordInput = document.getElementById('resetSupervisorPasswordField' + supervisorId);
            const confirmInput = document.getElementById('resetSupervisorPasswordConfirm' + supervisorId);
            if (passwordInput) passwordInput.value = suggestion;
            if (confirmInput) confirmInput.value = suggestion;
        });
        container.appendChild(chip);
    }
}

function copySupervisorPassword(supervisorId) {
    const passwordInput = document.getElementById('resetSupervisorPasswordField' + supervisorId);
    if (!passwordInput || !passwordInput.value) return;
    passwordInput.select();
    passwordInput.setSelectionRange(0, 99999);
    document.execCommand('copy');
    const button = event.target.closest('button');
    if (!button) return;
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check2"></i>';
    setTimeout(() => { button.innerHTML = originalHtml; }, 1500);
}

// تفويض عام على مستوى الصفحة كي يعمل مع كل مودالات إعادة تعيين كلمة المرور حتى بعد
// استبدال محتوى الجدول/المودالات عبر AJAX (تصفية/بحث/ترقيم صفحات).
document.addEventListener('shown.bs.modal', function (evt) {
    const modalEl = evt.target;
    if (!modalEl.id || !modalEl.id.startsWith('resetSupervisorPassword')) return;
    const supervisorId = modalEl.id.replace('resetSupervisorPassword', '');
    const container = document.getElementById('resetSupervisorPasswordSuggestions' + supervisorId);
    if (container && container.children.length === 0) {
        generateSupervisorPasswordSuggestions(supervisorId);
    }
});
</script>
@if ($errors->any() && old('supervisor_id'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var failedModalEl = document.getElementById('resetSupervisorPassword{{ old('supervisor_id') }}');
        if (failedModalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(failedModalEl).show();
        }
    });
    </script>
@endif
@stop
