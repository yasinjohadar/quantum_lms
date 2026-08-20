@extends('admin.layouts.master')

@section('page-title')
    المواد الدراسية
@stop

@push('styles')
    @include('admin.pages.subjects.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content subjects-page">
        <div class="container-fluid">

            <div class="subjects-hero my-4">
                <div class="subjects-hero__icon">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="subjects-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">المواد الدراسية</li>
                        </ol>
                    </nav>
                    <h4 class="subjects-hero__title">المواد الدراسية</h4>
                    <p class="subjects-hero__subtitle">إدارة المواد، الأقسام، والمحتوى التعليمي</p>
                </div>
                <div class="subjects-stat-mini">
                    <span class="subjects-stat-mini__value">{{ number_format($subjects->total()) }}</span>
                    <span class="subjects-stat-mini__label">مادة مطابقة</span>
                </div>
                <div class="subjects-hero__actions">
                    @can('subject-create')
                        @if(!auth()->user()->usesTeacherAssignmentScope())
                            <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> مادة جديدة
                            </a>
                        @endif
                    @endcan
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

            <div class="subjects-card">
                <div class="subjects-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="subjects-card__header-icon"><i class="bi bi-funnel"></i></span>
                        تصفية وبحث
                    </div>
                </div>
                <div class="subjects-card__body">
                    <form id="subjectsFilterForm" class="subjects-filters">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="query" id="searchQuery" class="form-control border-start-0"
                                           placeholder="اسم المادة أو الوصف" value="{{ request('query') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">الصف</label>
                                <select name="class_id" id="classFilter" class="form-select">
                                    <option value="">كل الصفوف</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }} — {{ $class->stage?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" id="statusFilter" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2 align-items-end">
                                <button type="button" id="searchBtn" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-lg me-1"></i> مسح
                                </button>
                                @include('admin.partials.per-page-toolbar', ['paginator' => $subjects])
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="subjects-card">
                <div class="subjects-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="subjects-card__header-icon"><i class="bi bi-table"></i></span>
                        قائمة المواد
                        <span class="badge bg-light text-muted fw-normal ms-1" style="font-size: 0.7rem;">
                            <i class="bi bi-grip-vertical me-1"></i> اسحب لإعادة الترتيب
                        </span>
                    </div>
                    <span class="badge bg-primary-transparent text-primary">
                        صفحة {{ $subjects->currentPage() }} من {{ $subjects->lastPage() }}
                    </span>
                </div>
                <div class="subjects-card__body p-0 position-relative">
                    <div id="loadingIndicator" class="subjects-loading-overlay" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted small mt-2 mb-0">جاري التحميل...</p>
                        </div>
                    </div>

                    <div id="subjectsTableContainer">
                        <div class="subjects-table-wrap mx-3 mt-3 mb-0">
                            <div class="table-responsive">
                                <table class="table subjects-table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th style="width: 32px;"></th>
                                        <th style="width: 48px;">#</th>
                                        <th>المادة</th>
                                        <th>الصف</th>
                                        <th>الحالة</th>
                                        <th style="width: 130px;">مجانية دائماً</th>
                                        <th style="min-width: 180px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="subjectsTableBody">
                                    @include('admin.pages.subjects.partials.table', ['subjects' => $subjects])
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="paginationContainer" class="px-3 pb-3 subjects-pagination">
                            @include('admin.pages.subjects.partials.pagination', ['subjects' => $subjects])
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('subjectsFilterForm');
    const searchQuery = document.getElementById('searchQuery');
    const classFilter = document.getElementById('classFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const subjectsTableBody = document.getElementById('subjectsTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');

    const csrfToken = '{{ csrf_token() }}';
    const filterUrl = '{{ route("admin.subjects.index") }}';
    const reorderUrl = '{{ route("admin.subjects.reorder") }}';

    // تفويض الحدث على tbody الثابت حتى يستمر عمله بعد إعادة رسم الصفوف (فلترة/ترقيم/ترتيب)
    subjectsTableBody.addEventListener('change', function(e) {
        const toggle = e.target.closest('.sb-free-override-toggle');
        if (!toggle) return;

        const url = toggle.dataset.url;
        const label = toggle.closest('.form-check')?.querySelector('.form-check-label');
        const previousChecked = !toggle.checked;
        toggle.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) throw new Error(data.message || 'فشل التحديث');
                toggle.checked = data.is_free_override;
                if (label) label.textContent = data.is_free_override ? 'مجانية' : 'مدفوعة';
            })
            .catch(function(err) {
                toggle.checked = previousChecked;
                alert(err.message || 'فشل تحديث حالة المادة');
            })
            .finally(function() {
                toggle.disabled = false;
            });
    });

    function getPerPageSelect() { return document.getElementById('perPageSelect'); }
    function getPerPageCustomWrap() { return document.getElementById('perPageCustomWrap'); }
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

    let searchTimeout;
    let currentPage = 1;
    let sortableInstance = null;

    function initSortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        const tbody = document.getElementById('subjectsTableBody');
        const rows = tbody.querySelectorAll('tr[data-id]');
        if (typeof Sortable === 'undefined' || rows.length === 0) return;
        sortableInstance = Sortable.create(tbody, {
            handle: '.sortable-handle',
            animation: 150,
            onEnd: function() {
                const order = [];
                tbody.querySelectorAll('tr[data-id]').forEach(function(tr) {
                    const id = tr.getAttribute('data-id');
                    if (id) order.push(parseInt(id, 10));
                });
                const payload = {
                    order: order,
                    page: currentPage,
                    per_page: getCurrentPerPage(),
                    _token: csrfToken
                };
                if (filterForm.querySelector('[name="query"]').value) payload.query = filterForm.querySelector('[name="query"]').value;
                if (filterForm.querySelector('[name="class_id"]').value) payload.class_id = filterForm.querySelector('[name="class_id"]').value;
                if (filterForm.querySelector('[name="is_active"]').value) payload.is_active = filterForm.querySelector('[name="is_active"]').value;
                const xhr = new XMLHttpRequest();
                xhr.open('POST', reorderUrl);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        fetchSubjects(currentPage);
                    } else {
                        let msg = 'فشل حفظ الترتيب.';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.message) msg = res.message;
                        } catch (e) {}
                        alert(msg);
                        fetchSubjects(currentPage);
                    }
                };
                xhr.onerror = function() {
                    alert('فشل حفظ الترتيب. تحقق من الاتصال.');
                    fetchSubjects(currentPage);
                };
                xhr.send(JSON.stringify(payload));
            }
        });
    }

    function fetchSubjects(page = 1) {
        currentPage = page;
        loadingIndicator.style.display = 'flex';

        const formData = new FormData(filterForm);
        formData.append('page', page);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        params.set('per_page', String(getCurrentPerPage()));

        fetch(`${filterUrl}?${params.toString()}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                subjectsTableBody.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination || '';
                attachPaginationListeners();
                initSortable();
                window.history.pushState({}, '', `${filterUrl}?${params.toString()}`);
            } else {
                showError('حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(() => showError('حدث خطأ أثناء جلب البيانات'))
        .finally(() => { loadingIndicator.style.display = 'none'; });
    }

    function attachPaginationListeners() {
        paginationContainer.querySelectorAll('a[href*="page"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                fetchSubjects(url.searchParams.get('page') || 1);
            });
        });
        syncCustomPerPageUi();
    }

    function showError(message) {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        subjectsTableBody.innerHTML = `
            <tr><td colspan="7" class="text-center py-4">
                <div class="alert alert-danger mb-0">${message}</div>
            </td></tr>`;
        paginationContainer.innerHTML = '';
    }

    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function(e) {
            if (!e.target || e.target.id !== 'perPageSelect') return;
            syncCustomPerPageUi();
            if (e.target.value !== 'custom') fetchSubjects(1);
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
            fetchSubjects(1);
        });
    }

    searchQuery.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchSubjects(1), 500);
    });
    classFilter.addEventListener('change', () => fetchSubjects(1));
    statusFilter.addEventListener('change', () => fetchSubjects(1));
    searchBtn.addEventListener('click', () => fetchSubjects(1));
    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        classFilter.value = '';
        statusFilter.value = '';
        fetchSubjects(1);
    });
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchSubjects(1);
        }
    });

    syncCustomPerPageUi();
    attachPaginationListeners();
    initSortable();
});
</script>
@stop
