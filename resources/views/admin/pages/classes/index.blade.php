@extends('admin.layouts.master')

@section('page-title')
    الصفوف الدراسية
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الصفوف الدراسية</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('class-create')
                        @if(!auth()->user()->usesTeacherAssignmentScope())
                            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> إضافة صف جديد
                            </a>
                        @endif
                    @endcan
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <!-- Filters Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <form id="classesFilterForm" class="d-flex flex-wrap align-items-end gap-2 w-100">
                                <input type="hidden" name="per_page" id="classesPerPageHidden" value="{{ $classes->perPage() }}">
                                <div class="d-flex flex-column">
                                    <label class="form-label mb-1">البحث</label>
                                    <input type="text" name="query" id="searchQuery" class="form-control form-control-sm"
                                           style="min-width: 200px;"
                                           placeholder="بحث باسم الصف أو الوصف"
                                           value="{{ request('query') }}">
                                </div>

                                <div class="d-flex flex-column">
                                    <label class="form-label mb-1">المرحلة</label>
                                    <select name="stage_id" id="stageFilter" class="form-select form-select-sm" style="min-width: 160px;">
                                        <option value="">كل المراحل</option>
                                        @foreach($stages as $stage)
                                            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex flex-column">
                                    <label class="form-label mb-1">الحالة</label>
                                    <select name="is_active" id="statusFilter" class="form-select form-select-sm" style="min-width: 140px;">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>

                                <button type="button" id="searchBtn" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                                <button type="button" id="clearFiltersBtn" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-times me-1"></i> مسح الفلاتر
                                </button>
                                @include('admin.partials.per-page-toolbar', ['paginator' => $classes])
                            </form>
                        </div>
                    </div>

                    <!-- Classes List Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة الصفوف الدراسية</h5>
                        </div>

                        <div class="card-body">
                            <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                                <p class="text-muted mt-2">جاري التحميل...</p>
                            </div>
                            <div id="classesTableContainer">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 36px; min-width: 36px;"></th>
                                            <th style="width: 50px;">#</th>
                                            <th style="min-width: 140px;">الصورة</th>
                                            <th style="min-width: 180px;">اسم الصف</th>
                                            <th style="min-width: 180px;">المرحلة</th>
                                            <th style="min-width: 100px;">الحالة</th>
                                            <th style="min-width: 200px;">العمليات</th>
                                        </tr>
                                        </thead>
                                        <tbody id="classesTableBody">
                                        @include('admin.pages.classes.partials.table', ['classes' => $classes])
                                        </tbody>
                                    </table>
                                </div>

                                <div id="paginationContainer" class="mt-3">
                                    @include('admin.pages.classes.partials.pagination', ['classes' => $classes])
                                </div>
                            </div>
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
    const filterForm = document.getElementById('classesFilterForm');
    const searchQuery = document.getElementById('searchQuery');
    const stageFilter = document.getElementById('stageFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const classesTableBody = document.getElementById('classesTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const classesTableContainer = document.getElementById('classesTableContainer');
    const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');
    const classesPerPageHidden = document.getElementById('classesPerPageHidden');

    const csrfToken = '{{ csrf_token() }}';
    const filterUrl = '{{ route("admin.classes.index") }}';
    const reorderUrl = '{{ route("admin.classes.reorder") }}';

    function getPerPageSelect() {
        return document.getElementById('perPageSelect');
    }
    function getPerPageCustomWrap() {
        return document.getElementById('perPageCustomWrap');
    }
    function getCurrentPerPage() {
        const sel = getPerPageSelect();
        if (!sel) {
            return classesPerPageHidden ? (parseInt(classesPerPageHidden.value, 10) || 25) : 25;
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
    function syncClassesPerPageHidden() {
        if (classesPerPageHidden) {
            classesPerPageHidden.value = String(getCurrentPerPage());
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
        var tbody = document.getElementById('classesTableBody');
        var rows = tbody.querySelectorAll('tr[data-id]');
        if (typeof Sortable === 'undefined' || rows.length === 0) return;
        sortableInstance = Sortable.create(tbody, {
            handle: '.sortable-handle',
            animation: 150,
            onEnd: function(evt) {
                var order = [];
                tbody.querySelectorAll('tr[data-id]').forEach(function(tr) {
                    var id = tr.getAttribute('data-id');
                    if (id) order.push(parseInt(id, 10));
                });
                var payload = {
                    order: order,
                    page: currentPage,
                    per_page: getCurrentPerPage(),
                    _token: csrfToken
                };
                if (filterForm.querySelector('[name="query"]').value) payload.query = filterForm.querySelector('[name="query"]').value;
                if (filterForm.querySelector('[name="stage_id"]').value) payload.stage_id = filterForm.querySelector('[name="stage_id"]').value;
                if (filterForm.querySelector('[name="is_active"]').value) payload.is_active = filterForm.querySelector('[name="is_active"]').value;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', reorderUrl);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        fetchClasses(currentPage);
                    } else {
                        var msg = 'فشل حفظ الترتيب.';
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res.message) msg = res.message;
                        } catch (e) {}
                        alert(msg);
                        fetchClasses(currentPage);
                    }
                };
                xhr.onerror = function() {
                    alert('فشل حفظ الترتيب. تحقق من الاتصال.');
                    fetchClasses(currentPage);
                };
                xhr.send(JSON.stringify(payload));
            }
        });
    }

    // دالة لجلب البيانات عبر Ajax
    function fetchClasses(page = 1) {
        currentPage = page;
        
        // إظهار loading indicator
        loadingIndicator.style.display = 'block';
        classesTableContainer.style.opacity = '0.5';
        
        // جمع بيانات الفلاتر
        syncClassesPerPageHidden();
        const formData = new FormData(filterForm);
        formData.append('page', page);

        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        params.set('per_page', String(getCurrentPerPage()));

        fetch(`${filterUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // تحديث الجدول
                classesTableBody.innerHTML = data.html;
                
                // تحديث pagination
                paginationContainer.innerHTML = data.pagination || '';
                
                attachPaginationListeners();

                initSortable();
                syncCustomPerPageUi();
                syncClassesPerPageHidden();
                
                // تحديث URL بدون إعادة تحميل الصفحة
                const newUrl = `${filterUrl}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                showError('حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error fetching classes:', error);
            showError('حدث خطأ أثناء جلب البيانات');
        })
        .finally(() => {
            // إخفاء loading indicator
            loadingIndicator.style.display = 'none';
            classesTableContainer.style.opacity = '1';
        });
    }

    // دالة لإعادة ربط event listeners للـ pagination
    function attachPaginationListeners() {
        const paginationLinks = paginationContainer.querySelectorAll('a[href*="page"]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                fetchClasses(page);
            });
        });
        syncCustomPerPageUi();
    }

    if (perPageToolbarContainer) {
        perPageToolbarContainer.addEventListener('change', function(e) {
            if (!e.target || e.target.id !== 'perPageSelect') {
                return;
            }
            syncCustomPerPageUi();
            syncClassesPerPageHidden();
            if (e.target.value !== 'custom') {
                fetchClasses(1);
            }
        });
        perPageToolbarContainer.addEventListener('click', function(e) {
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
            syncClassesPerPageHidden();
            fetchClasses(1);
        });
    }

    // دالة لإظهار رسالة خطأ
    function showError(message) {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        classesTableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-danger fw-bold">
                    ${message}
                </td>
            </tr>
        `;
        paginationContainer.innerHTML = '';
    }

    // Debounce للبحث النصي
    searchQuery.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchClasses(1);
        }, 500);
    });

    // تحديث فوري عند تغيير المرحلة
    stageFilter.addEventListener('change', function() {
        fetchClasses(1);
    });

    // تحديث فوري عند تغيير الحالة
    statusFilter.addEventListener('change', function() {
        fetchClasses(1);
    });

    // زر البحث
    searchBtn.addEventListener('click', function() {
        fetchClasses(1);
    });

    // زر مسح الفلاتر
    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        stageFilter.value = '';
        statusFilter.value = '';
        fetchClasses(1);
    });

    // Enter في حقل البحث
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchClasses(1);
        }
    });

    syncCustomPerPageUi();
    syncClassesPerPageHidden();
    attachPaginationListeners();
    // تهيئة السحب والإفلات لترتيب الصفوف
    initSortable();
});
</script>
@stop

