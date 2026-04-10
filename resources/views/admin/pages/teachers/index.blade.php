@extends('admin.layouts.master')

@section('page-title')
    تخصيص المعلمين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تخصيص المعلمين للصفوف والمواد</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'teacher']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> إضافة معلم جديد
                        </a>
                    @endcan
                    <a href="{{ route('users.index', ['role' => 'teacher']) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-users me-1"></i> عرض جميع المعلمين
                    </a>
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

            @if(isset($totalTeachers))
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <span class="small text-white-50 mb-0">إجمالي المعلمين</span>
                                <span class="fs-5 fw-bold mb-0">{{ $totalTeachers }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-info text-white overflow-hidden">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <span class="small text-white-50 mb-0">معلمون مخصصون</span>
                                <span class="fs-5 fw-bold mb-0">{{ $assignedTeachers }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-warning text-white overflow-hidden">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <span class="small text-white-50 mb-0">معلمون غير مخصصين</span>
                                <span class="fs-5 fw-bold mb-0">{{ $unassignedTeachers }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column justify-content-between gap-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <h5 class="mb-0 fw-bold">قائمة المعلمين</h5>
                                @include('admin.partials.per-page-toolbar', ['paginator' => $teachers])
                            </div>

                            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                                <div class="w-100 mb-2 small text-muted">
                                    @if(isset($currentWeek) && $currentWeek)
                                        <strong>الأسبوع المعتمد في الإحصائيات:</strong> {{ $currentWeek->title ?? 'الأسبوع ' . $currentWeek->week_number }} ({{ $currentWeek->start_date->format('Y-m-d') }} - {{ $currentWeek->end_date->format('Y-m-d') }})
                                    @else
                                        <strong>الأسبوع:</strong> أسبوع النظام (حسب التاريخ الحالي)
                                    @endif
                                </div>
                            @endif
                            <form method="GET" action="{{ route('admin.teachers.assignments.index') }}" id="teachersFiltersForm"
                                  class="d-flex flex-wrap gap-2 align-items-end">
                                @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                                    <div class="d-flex align-items-center gap-1">
                                        <label class="form-label mb-0 small text-muted">الأسبوع</label>
                                        <select name="week_id" class="form-select form-select-sm" style="min-width: 180px;">
                                            <option value="">الأسبوع الحالي</option>
                                            @foreach($activeWeeks as $w)
                                                <option value="{{ $w->id }}" {{ request('week_id') == $w->id ? 'selected' : '' }}>{{ $w->title ?? 'الأسبوع ' . $w->week_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">بحث</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="الاسم أو البريد"
                                           value="{{ request('search') }}" style="min-width: 180px;">
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">التخصيص</label>
                                    <select name="assignment" class="form-select form-select-sm" style="min-width: 120px;">
                                        <option value="all" {{ request('assignment', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="assigned" {{ request('assignment') === 'assigned' ? 'selected' : '' }}>مخصصون فقط</option>
                                        <option value="unassigned" {{ request('assignment') === 'unassigned' ? 'selected' : '' }}>غير مخصصين</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">الدور</label>
                                    <select name="role" id="teacherRoleFilter" class="form-select form-select-sm" style="min-width: 140px;">
                                        <option value="">كل أدوار المعلمين</option>
                                        @foreach($filterRoles ?? [] as $role)
                                            <option value="{{ $role->name }}" {{ (string) request('role') === (string) $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">نسبة الصفحات</label>
                                    <select name="pages_progress" class="form-select form-select-sm" style="min-width: 130px;">
                                        <option value="all" {{ request('pages_progress', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="below_50" {{ request('pages_progress') === 'below_50' ? 'selected' : '' }}>أقل من 50%</option>
                                        <option value="below_100" {{ request('pages_progress') === 'below_100' ? 'selected' : '' }}>أقل من 100%</option>
                                        <option value="completed" {{ request('pages_progress') === 'completed' ? 'selected' : '' }}>منجز 100%</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">النسبة الأسبوعية</label>
                                    <select name="weekly_progress" class="form-select form-select-sm" style="min-width: 130px;">
                                        <option value="all" {{ request('weekly_progress', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="below_50" {{ request('weekly_progress') === 'below_50' ? 'selected' : '' }}>أقل من 50%</option>
                                        <option value="below_100" {{ request('weekly_progress') === 'below_100' ? 'selected' : '' }}>أقل من 100%</option>
                                        <option value="completed" {{ request('weekly_progress') === 'completed' ? 'selected' : '' }}>منجز 100%</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">الترتيب</label>
                                    <select name="sort" class="form-select form-select-sm" style="min-width: 160px;">
                                        <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>الاسم (أ→ي)</option>
                                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>الاسم (ي→أ)</option>
                                        <option value="pages_asc" {{ request('sort') === 'pages_asc' ? 'selected' : '' }}>نسبة الصفحات (تصاعدي)</option>
                                        <option value="pages_desc" {{ request('sort') === 'pages_desc' ? 'selected' : '' }}>نسبة الصفحات (تنازلي)</option>
                                        <option value="weekly_asc" {{ request('sort') === 'weekly_asc' ? 'selected' : '' }}>النسبة الأسبوعية (تصاعدي)</option>
                                        <option value="weekly_desc" {{ request('sort') === 'weekly_desc' ? 'selected' : '' }}>النسبة الأسبوعية (تنازلي)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                                @if(request()->hasAny(['search', 'assignment', 'role', 'pages_progress', 'weekly_progress', 'sort', 'week_id']) && (request('search') || request('assignment', 'all') !== 'all' || request('role') || request('pages_progress', 'all') !== 'all' || request('weekly_progress', 'all') !== 'all' || request('sort', 'name_asc') !== 'name_asc' || request('week_id')))
                                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i> إلغاء الفلاتر
                                    </a>
                                @endif
                            </form>
                        </div>

                        <div class="card-body">
                            @if($teachers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الأدوار</th>
                                                <th>الصفوف المخصصة</th>
                                                <th>المواد المخصصة</th>
                                                <th>حالة الحساب</th>
                                                <th>آخر دخول</th>
                                                <th>متصل الآن</th>
                                                <th>مؤشرات التقدم</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="teachersTableBody">
                                            @include('admin.pages.teachers.partials.table-rows', ['teachers' => $teachers, 'teachersProgress' => $teachersProgress, 'lastLogins' => $lastLogins, 'onlineUserIds' => $onlineUserIds])
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center mt-3" id="teachersPaginationContainer">
                                    @include('admin.pages.teachers.partials.pagination', ['teachers' => $teachers])
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-3">لا يوجد معلمين</p>
                                    @can('user-create')
                                        <a href="{{ route('users.create', ['role' => 'teacher']) }}" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-1"></i> إضافة معلم جديد
                                        </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div id="teachersImpersonateModals">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $teachers])
            </div>

        </div>
    </div>
@stop

@section('js')
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
        if (!sel) {
            return 25;
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

    if (!form || !roleFilter || !tableBody || !paginationContainer) return;

    function buildParams(extraPage) {
        const params = new URLSearchParams(new FormData(form));
        if (extraPage) {
            params.set('page', String(extraPage));
        }
        params.set('per_page', String(getCurrentPerPage()));
        return params;
    }

    function bindPaginationLinks() {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || '1';
                fetchTeachers(page);
            });
        });
    }

    function fetchTeachers(page) {
        const params = buildParams(page);
        fetch(`{{ route('admin.teachers.assignments.index') }}?${params.toString()}`, {
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
            if (impersonateModalsContainer && typeof data.impersonate_modals === 'string') {
                impersonateModalsContainer.innerHTML = data.impersonate_modals;
            }
            bindPaginationLinks();
            syncCustomPerPageUi();
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);
        })
        .catch(function () {
            // تجاهل صامت — الصفحة ما زالت قابلة للاستخدام عبر submit التقليدي
        });
    }

    roleFilter.addEventListener('change', function () {
        fetchTeachers(1);
    });

    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function (e) {
            if (!e.target || e.target.id !== 'perPageSelect') {
                return;
            }
            syncCustomPerPageUi();
            if (e.target.value !== 'custom') {
                fetchTeachers(1);
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
            fetchTeachers(1);
        });
    }

    syncCustomPerPageUi();
    bindPaginationLinks();
});
</script>
@stop
