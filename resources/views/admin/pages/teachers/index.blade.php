@extends('admin.layouts.master')

@section('page-title')
    تخصيص المعلمين
@stop

@push('styles')
    @include('admin.pages.teachers.partials.assignments-index-styles')
@endpush

@section('content')
    <div class="main-content app-content teachers-page">
        <div class="container-fluid">

            <div class="teachers-hero my-4">
                <div class="teachers-hero__icon">
                    <i class="bi bi-person-video3"></i>
                </div>
                <div class="teachers-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تخصيص المعلمين</li>
                        </ol>
                    </nav>
                    <h4 class="teachers-hero__title">تخصيص المعلمين للصفوف والمواد</h4>
                    <p class="teachers-hero__subtitle">إدارة تخصيصات المعلمين ومتابعة مؤشرات التقدم</p>
                </div>
                <div class="teachers-hero__actions">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'teacher']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus me-1"></i> معلم جديد
                        </a>
                    @endcan
                    <a href="{{ route('users.index', ['role' => 'teacher']) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-people me-1"></i> جميع المعلمين
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

            @if(isset($totalTeachers))
                <div class="teachers-stats">
                    <div class="teachers-stat-card teachers-stat-card--total">
                        <div>
                            <div class="teachers-stat-card__label">إجمالي المعلمين</div>
                            <div class="teachers-stat-card__value">{{ number_format($totalTeachers) }}</div>
                        </div>
                        <span class="teachers-stat-card__icon"><i class="bi bi-people-fill"></i></span>
                    </div>
                    <div class="teachers-stat-card teachers-stat-card--assigned">
                        <div>
                            <div class="teachers-stat-card__label">معلمون مخصصون</div>
                            <div class="teachers-stat-card__value">{{ number_format($assignedTeachers) }}</div>
                        </div>
                        <span class="teachers-stat-card__icon"><i class="bi bi-check2-circle"></i></span>
                    </div>
                    <div class="teachers-stat-card teachers-stat-card--unassigned">
                        <div>
                            <div class="teachers-stat-card__label">غير مخصصين</div>
                            <div class="teachers-stat-card__value">{{ number_format($unassignedTeachers) }}</div>
                        </div>
                        <span class="teachers-stat-card__icon"><i class="bi bi-exclamation-circle"></i></span>
                    </div>
                </div>
            @endif

            <div class="teachers-card">
                <div class="teachers-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="teachers-card__header-icon"><i class="bi bi-funnel"></i></span>
                        تصفية وبحث
                    </div>
                </div>
                <div class="teachers-card__body">
                    @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                        <div class="teachers-week-banner">
                            <i class="bi bi-calendar-week me-1"></i>
                            @if(isset($currentWeek) && $currentWeek)
                                <strong>الأسبوع المعتمد:</strong>
                                {{ $currentWeek->title ?? 'الأسبوع ' . $currentWeek->week_number }}
                                ({{ $currentWeek->start_date->format('Y-m-d') }} — {{ $currentWeek->end_date->format('Y-m-d') }})
                            @else
                                <strong>الأسبوع:</strong> أسبوع النظام (حسب التاريخ الحالي)
                            @endif
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.teachers.assignments.index') }}" id="teachersFiltersForm" class="teachers-filters">
                        <div class="row g-3 align-items-end">
                            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                                <div class="col-md-6 col-lg-2">
                                    <label class="form-label">الأسبوع</label>
                                    <select name="week_id" class="form-select">
                                        <option value="">الأسبوع الحالي</option>
                                        @foreach($activeWeeks as $w)
                                            <option value="{{ $w->id }}" {{ request('week_id') == $w->id ? 'selected' : '' }}>
                                                {{ $w->title ?? 'الأسبوع ' . $w->week_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0"
                                           placeholder="الاسم أو البريد" value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">التخصيص</label>
                                <select name="assignment" class="form-select">
                                    <option value="all" {{ request('assignment', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="assigned" {{ request('assignment') === 'assigned' ? 'selected' : '' }}>مخصصون</option>
                                    <option value="unassigned" {{ request('assignment') === 'unassigned' ? 'selected' : '' }}>غير مخصصين</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الدور</label>
                                <select name="role" id="teacherRoleFilter" class="form-select">
                                    <option value="">كل الأدوار</option>
                                    @foreach($filterRoles ?? [] as $role)
                                        <option value="{{ $role->name }}" {{ (string) request('role') === (string) $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">نسبة الصفحات</label>
                                <select name="pages_progress" class="form-select">
                                    <option value="all" {{ request('pages_progress', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="below_50" {{ request('pages_progress') === 'below_50' ? 'selected' : '' }}>أقل من 50%</option>
                                    <option value="below_100" {{ request('pages_progress') === 'below_100' ? 'selected' : '' }}>أقل من 100%</option>
                                    <option value="completed" {{ request('pages_progress') === 'completed' ? 'selected' : '' }}>منجز 100%</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">النسبة الأسبوعية</label>
                                <select name="weekly_progress" class="form-select">
                                    <option value="all" {{ request('weekly_progress', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="below_50" {{ request('weekly_progress') === 'below_50' ? 'selected' : '' }}>أقل من 50%</option>
                                    <option value="below_100" {{ request('weekly_progress') === 'below_100' ? 'selected' : '' }}>أقل من 100%</option>
                                    <option value="completed" {{ request('weekly_progress') === 'completed' ? 'selected' : '' }}>منجز 100%</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الترتيب</label>
                                <select name="sort" class="form-select">
                                    <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>الاسم (أ→ي)</option>
                                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>الاسم (ي→أ)</option>
                                    <option value="pages_asc" {{ request('sort') === 'pages_asc' ? 'selected' : '' }}>الصفحات ↑</option>
                                    <option value="pages_desc" {{ request('sort') === 'pages_desc' ? 'selected' : '' }}>الصفحات ↓</option>
                                    <option value="weekly_asc" {{ request('sort') === 'weekly_asc' ? 'selected' : '' }}>الأسبوع ↑</option>
                                    <option value="weekly_desc" {{ request('sort') === 'weekly_desc' ? 'selected' : '' }}>الأسبوع ↓</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                @if(request()->hasAny(['search', 'assignment', 'role', 'pages_progress', 'weekly_progress', 'sort', 'week_id']) && (request('search') || request('assignment', 'all') !== 'all' || request('role') || request('pages_progress', 'all') !== 'all' || request('weekly_progress', 'all') !== 'all' || request('sort', 'name_asc') !== 'name_asc' || request('week_id')))
                                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-x-lg me-1"></i> مسح
                                    </a>
                                @endif
                                @include('admin.partials.per-page-toolbar', ['paginator' => $teachers])
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="teachers-card">
                <div class="teachers-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="teachers-card__header-icon"><i class="bi bi-table"></i></span>
                        قائمة المعلمين
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @include('admin.pages.teachers.partials.column-visibility-toggle')
                        <span class="badge bg-primary-transparent text-primary">
                            صفحة {{ $teachers->currentPage() }} من {{ $teachers->lastPage() }}
                        </span>
                    </div>
                </div>
                <div class="teachers-card__body p-0">
                    @if($teachers->count() > 0)
                        <div class="teachers-table-wrap mx-3 mt-3 mb-0">
                            <div class="table-responsive">
                                <table class="table teachers-table align-middle mb-0" id="teachersAssignmentsTable">
                                    <thead>
                                    <tr>
                                        <th style="width: 48px;">#</th>
                                        <th data-tv-col="name">الاسم</th>
                                        <th data-tv-col="email">البريد</th>
                                        <th data-tv-col="roles">الأدوار</th>
                                        <th data-tv-col="classes">الصفوف</th>
                                        <th data-tv-col="subjects">المواد</th>
                                        <th data-tv-col="status">الحالة</th>
                                        <th data-tv-col="last_login">آخر دخول</th>
                                        <th data-tv-col="online">الاتصال</th>
                                        <th data-tv-col="quizzes" class="text-center" style="min-width: 90px;">الاختبارات</th>
                                        <th data-tv-col="progress">التقدم</th>
                                        <th style="min-width: 180px;">الإجراءات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="teachersTableBody">
                                    @include('admin.pages.teachers.partials.table-rows', ['teachers' => $teachers, 'teachersProgress' => $teachersProgress, 'lastLogins' => $lastLogins, 'onlineUserIds' => $onlineUserIds])
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="teachersPaginationContainer" class="px-3 pb-3 teachers-pagination">
                            @include('admin.pages.teachers.partials.pagination', ['teachers' => $teachers])
                        </div>
                    @else
                        <div class="teachers-empty">
                            <i class="bi bi-person-video3"></i>
                            <p class="mb-2 fw-semibold">لا يوجد معلمين مطابقين للفلاتر</p>
                            @can('user-create')
                                <a href="{{ route('users.create', ['role' => 'teacher']) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-person-plus me-1"></i> إضافة معلم جديد
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            <div id="teachersImpersonateModals">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $teachers])
            </div>

        </div>
    </div>
@stop

@section('js')
<script src="{{ asset('js/admin/teachers-table-columns.js') }}"></script>
<script>
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
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('teachersFiltersForm');
    const roleFilter = document.getElementById('teacherRoleFilter');
    const tableBody = document.getElementById('teachersTableBody');
    const paginationContainer = document.getElementById('teachersPaginationContainer');
    const impersonateModalsContainer = document.getElementById('teachersImpersonateModals');
    const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');

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

    if (!form || !roleFilter || !tableBody || !paginationContainer) return;

    function buildParams(extraPage) {
        const params = new URLSearchParams(new FormData(form));
        if (extraPage) params.set('page', String(extraPage));
        params.set('per_page', String(getCurrentPerPage()));
        return params;
    }

    function bindPaginationLinks() {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                fetchTeachers(url.searchParams.get('page') || '1');
            });
        });
    }

    function fetchTeachers(page) {
        const params = buildParams(page);
        fetch(`{{ route('admin.teachers.assignments.index') }}?${params.toString()}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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
            if (impersonateModalsContainer && typeof data.impersonate_modals === 'string') {
                impersonateModalsContainer.innerHTML = data.impersonate_modals;
            }
            bindPaginationLinks();
            syncCustomPerPageUi();
            if (window.TeachersTableColumns && typeof window.TeachersTableColumns.refresh === 'function') {
                window.TeachersTableColumns.refresh();
            }
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
        })
        .catch(function () {});
    }

    roleFilter.addEventListener('change', function () { fetchTeachers(1); });

    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function (e) {
            if (!e.target || e.target.id !== 'perPageSelect') return;
            syncCustomPerPageUi();
            if (e.target.value !== 'custom') fetchTeachers(1);
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
            fetchTeachers(1);
        });
    }

    syncCustomPerPageUi();
    bindPaginationLinks();
});
</script>
@stop
