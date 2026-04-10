@extends('admin.layouts.master')

@section('page-title')
    المواد الدراسية
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">المواد الدراسية</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('subject-create')
                        @if(!auth()->user()->usesTeacherAssignmentScope())
                            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> إضافة مادة جديدة
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
                            <form id="subjectsFilterForm" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1">البحث</label>
                                    <input type="text" name="query" id="searchQuery" class="form-control form-control-sm"
                                           placeholder="بحث باسم المادة أو الوصف"
                                           value="{{ request('query') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label mb-1">الصف</label>
                                    <select name="class_id" id="classFilter" class="form-select form-select-sm">
                                        <option value="">كل الصفوف</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} - {{ $class->stage?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1">الحالة</label>
                                    <select name="is_active" id="statusFilter" class="form-select form-select-sm">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشطة</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشطة</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <button type="button" id="searchBtn" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-search me-1"></i> بحث
                                    </button>
                                    <button type="button" id="clearFiltersBtn" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times me-1"></i> مسح الفلاتر
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Subjects List Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h5 class="mb-0 fw-bold">قائمة المواد الدراسية</h5>
                            @include('admin.partials.per-page-toolbar', ['paginator' => $subjects])
                        </div>

                        <div class="card-body">
                            <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                                <p class="text-muted mt-2">جاري التحميل...</p>
                            </div>
                            <div id="subjectsTableContainer">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 36px; min-width: 36px;"></th>
                                            <th style="width: 50px;">#</th>
                                            <th style="min-width: 140px;">الصورة</th>
                                            <th style="min-width: 180px;">اسم المادة</th>
                                            <th style="min-width: 180px;">الصف</th>
                                            <th style="min-width: 100px;">الحالة</th>
                                            <th style="min-width: 160px;">تاريخ الإنشاء</th>
                                            <th style="min-width: 200px;">العمليات</th>
                                        </tr>
                                        </thead>
                                        <tbody id="subjectsTableBody">
                                        @include('admin.pages.subjects.partials.table', ['subjects' => $subjects])
                                        </tbody>
                                    </table>
                                </div>

                                <div id="paginationContainer" class="mt-3">
                                    @include('admin.pages.subjects.partials.pagination', ['subjects' => $subjects])
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
    const subjectsTableContainer = document.getElementById('subjectsTableContainer');
    
    const csrfToken = '{{ csrf_token() }}';
    const filterUrl = '{{ route("admin.subjects.index") }}';
    const reorderUrl = '{{ route("admin.subjects.reorder") }}';

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

    let searchTimeout;
    let currentPage = 1;
    let sortableInstance = null;

    function initSortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        var tbody = document.getElementById('subjectsTableBody');
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
                if (filterForm.querySelector('[name="class_id"]').value) payload.class_id = filterForm.querySelector('[name="class_id"]').value;
                if (filterForm.querySelector('[name="is_active"]').value) payload.is_active = filterForm.querySelector('[name="is_active"]').value;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', reorderUrl);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        fetchSubjects(currentPage);
                    } else {
                        var msg = 'فشل حفظ الترتيب.';
                        try {
                            var res = JSON.parse(xhr.responseText);
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

    // دالة لجلب البيانات عبر Ajax
    function fetchSubjects(page = 1) {
        currentPage = page;
        
        // إظهار loading indicator
        loadingIndicator.style.display = 'block';
        subjectsTableContainer.style.opacity = '0.5';
        
        // جمع بيانات الفلاتر
        const formData = new FormData(filterForm);
        formData.append('page', page);
        
        // إضافة headers للـ Ajax request
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
                subjectsTableBody.innerHTML = data.html;
                
                // تحديث pagination
                paginationContainer.innerHTML = data.pagination || '';
                
                // إعادة ربط event listeners للـ pagination
                attachPaginationListeners();
                
                // إعادة تهيئة Sortable بعد تحديث الصفوف
                initSortable();
                
                // تحديث URL بدون إعادة تحميل الصفحة
                const newUrl = `${filterUrl}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                showError('حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error fetching subjects:', error);
            showError('حدث خطأ أثناء جلب البيانات');
        })
        .finally(() => {
            // إخفاء loading indicator
            loadingIndicator.style.display = 'none';
            subjectsTableContainer.style.opacity = '1';
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
                fetchSubjects(page);
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
            if (e.target.value !== 'custom') {
                fetchSubjects(1);
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
            fetchSubjects(1);
        });
    }

    // دالة لإظهار رسالة خطأ
    function showError(message) {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        subjectsTableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-danger fw-bold">
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
            fetchSubjects(1);
        }, 500);
    });

    // تحديث فوري عند تغيير الصف
    classFilter.addEventListener('change', function() {
        fetchSubjects(1);
    });

    // تحديث فوري عند تغيير الحالة
    statusFilter.addEventListener('change', function() {
        fetchSubjects(1);
    });

    // زر البحث
    searchBtn.addEventListener('click', function() {
        fetchSubjects(1);
    });

    // زر مسح الفلاتر
    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        classFilter.value = '';
        statusFilter.value = '';
        fetchSubjects(1);
    });

    // Enter في حقل البحث
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchSubjects(1);
        }
    });

    // تهيئة pagination listeners عند تحميل الصفحة
    syncCustomPerPageUi();
    attachPaginationListeners();
    // تهيئة السحب والإفلات لترتيب المواد
    initSortable();
});
</script>
@stop

