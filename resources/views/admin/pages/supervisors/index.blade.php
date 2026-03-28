@extends('admin.layouts.master')

@section('page-title')
    تخصيص المشرفين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تخصيص المشرفين للصفوف والمواد</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> إضافة مشرف جديد
                        </a>
                    @endcan
                    <a href="{{ route('users.index', ['role' => 'supervisor']) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-users me-1"></i> عرض جميع المشرفين
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

            @if(isset($totalSupervisors))
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <span class="small text-white-50 mb-0">إجمالي المشرفين</span>
                                <span class="fs-5 fw-bold mb-0">{{ $totalSupervisors }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-info text-white overflow-hidden">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <span class="small text-white-50 mb-0">مشرفون مخصصون</span>
                                <span class="fs-5 fw-bold mb-0">{{ $assignedSupervisors }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-warning text-white overflow-hidden">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <span class="small text-white-50 mb-0">مشرفون غير مخصصين</span>
                                <span class="fs-5 fw-bold mb-0">{{ $unassignedSupervisors }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="mb-3 fw-bold">قائمة المشرفين</h5>
                            <form id="supervisorFiltersForm" class="row g-2 align-items-end">
                                <div class="col-md-3 col-lg-3">
                                    <label class="form-label small mb-1">بحث</label>
                                    <input type="text" name="search" id="supervisorSearch" class="form-control form-control-sm"
                                           placeholder="الاسم أو البريد الإلكتروني"
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3 col-lg-3">
                                    <label class="form-label small mb-1">الصف</label>
                                    <select name="class_id" id="supervisorClassFilter" class="form-select form-select-sm">
                                        <option value="">جميع الصفوف</option>
                                        @foreach($filterClasses ?? [] as $class)
                                            <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                                @if($class->stage)
                                                    — {{ $class->stage->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-3">
                                    <label class="form-label small mb-1">المادة</label>
                                    <select name="subject_id" id="supervisorSubjectFilter" class="form-select form-select-sm"
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
                                <div class="col-md-3 col-lg-3 d-flex flex-wrap gap-2">
                                    <button type="button" id="supervisorSearchBtn" class="btn btn-primary btn-sm">
                                        <i class="bi bi-search me-1"></i> بحث
                                    </button>
                                    <button type="button" id="supervisorClearFiltersBtn" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i> إعادة تعيين
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-body position-relative">
                            <div id="supervisorsLoading" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                                <p class="text-muted small mt-2 mb-0">جاري التحميل...</p>
                            </div>

                            <div id="supervisorsTableWrapper">
                                @if($supervisors->count() > 0)
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
                                                    <th>آخر دخول</th>
                                                    <th>متصل الآن</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody id="supervisorsTableBody">
                                                @include('admin.pages.supervisors.partials.table-rows', ['supervisors' => $supervisors, 'lastLogins' => $lastLogins, 'onlineUserIds' => $onlineUserIds])
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="paginationContainer" class="d-flex justify-content-center mt-3">
                                        @include('admin.pages.supervisors.partials.pagination', ['supervisors' => $supervisors])
                                    </div>
                                @else
                                    <div id="supervisorsEmptyState" class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-3">لا يوجد مشرفين</p>
                                        @can('user-create')
                                            <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary">
                                                <i class="fas fa-user-plus me-1"></i> إضافة مشرف جديد
                                            </a>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="supervisorDeleteModals">
                @include('admin.pages.supervisors.partials.delete-modals', ['supervisors' => $supervisors])
            </div>

            <div id="supervisorImpersonateModals">
                @include('admin.pages.users.partials.impersonate-modals', ['users' => $supervisors])
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
    const searchBtn = document.getElementById('supervisorSearchBtn');
    const clearBtn = document.getElementById('supervisorClearFiltersBtn');
    const loadingEl = document.getElementById('supervisorsLoading');
    const tableWrapper = document.getElementById('supervisorsTableWrapper');
    const modalsContainer = document.getElementById('supervisorDeleteModals');
    const impersonateModalsContainer = document.getElementById('supervisorImpersonateModals');

    let supervisorsTableBody = document.getElementById('supervisorsTableBody');
    let paginationContainer = document.getElementById('paginationContainer');

    let searchTimeout;
    let currentPage = 1;

    function buildParams(page) {
        const params = new URLSearchParams();
        if (searchInput && searchInput.value.trim()) {
            params.append('search', searchInput.value.trim());
        }
        if (classFilter && classFilter.value) {
            params.append('class_id', classFilter.value);
        }
        if (subjectFilter && subjectFilter.value) {
            params.append('subject_id', subjectFilter.value);
        }
        params.append('page', page || 1);
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
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(r => {
            if (!r.ok) throw new Error('Network error');
            return r.json();
        })
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
        let emptyEl = document.getElementById('supervisorsEmptyState');
        if (emptyEl) {
            emptyEl.remove();
        }
        let tableResponsive = tableWrapper.querySelector('.table-responsive');
        if (!tableResponsive) {
            tableWrapper.innerHTML = '';
            const div = document.createElement('div');
            div.className = 'table-responsive';
            div.innerHTML = `
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الأدوار</th>
                            <th>الصفوف المخصصة</th>
                            <th>المواد المخصصة</th>
                            <th>آخر دخول</th>
                            <th>متصل الآن</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="supervisorsTableBody"></tbody>
                </table>`;
            tableWrapper.appendChild(div);
            supervisorsTableBody = document.getElementById('supervisorsTableBody');
            const pag = document.createElement('div');
            pag.id = 'paginationContainer';
            pag.className = 'd-flex justify-content-center mt-3';
            tableWrapper.appendChild(pag);
            paginationContainer = pag;
        } else {
            supervisorsTableBody = document.getElementById('supervisorsTableBody');
            if (!paginationContainer || !tableWrapper.contains(paginationContainer)) {
                const pag = document.createElement('div');
                pag.id = 'paginationContainer';
                pag.className = 'd-flex justify-content-center mt-3';
                tableWrapper.appendChild(pag);
                paginationContainer = pag;
            }
        }
    }

    function showEmptyState() {
        tableWrapper.innerHTML = `
            <div id="supervisorsEmptyState" class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-3">لا يوجد مشرفين</p>
                @can('user-create')
                <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i> إضافة مشرف جديد
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
                const page = url.searchParams.get('page') || 1;
                fetchSupervisors(parseInt(page, 10));
            });
        });
    }

    function fetchSupervisors(page) {
        currentPage = page || 1;
        const params = buildParams(currentPage);

        loadingEl.style.display = 'block';
        tableWrapper.style.opacity = '0.4';

        fetch(`${fetchUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Error');

            if (modalsContainer && typeof data.modals === 'string') {
                modalsContainer.innerHTML = data.modals;
            }
            if (impersonateModalsContainer && typeof data.impersonate_modals === 'string') {
                impersonateModalsContainer.innerHTML = data.impersonate_modals;
            }

            if (data.html && data.html.trim() !== '') {
                ensureTableShell();
                if (supervisorsTableBody) {
                    supervisorsTableBody.innerHTML = data.html;
                }
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination || '';
                }
                attachPaginationListeners();
            } else {
                showEmptyState();
                if (impersonateModalsContainer) {
                    impersonateModalsContainer.innerHTML = '';
                }
            }

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
        })
        .catch(() => {
            ensureTableShell();
            if (supervisorsTableBody) {
                supervisorsTableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="alert alert-danger mb-0">حدث خطأ أثناء جلب البيانات</div>
                        </td>
                    </tr>`;
            }
        })
        .finally(() => {
            loadingEl.style.display = 'none';
            tableWrapper.style.opacity = '1';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchSupervisors(1), 500);
        });
    }

    if (classFilter) {
        classFilter.addEventListener('change', function() {
            const classId = this.value;
            loadSubjectsByClass(classId, false).then(() => fetchSupervisors(1));
        });
    }

    if (subjectFilter) {
        subjectFilter.addEventListener('change', function() {
            fetchSupervisors(1);
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            fetchSupervisors(1);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (classFilter) classFilter.value = '';
            loadSubjectsByClass('', false).then(() => fetchSupervisors(1));
        });
    }

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
</script>
@stop
