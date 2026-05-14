@extends('admin.layouts.master')

@section('page-title')
    الانضمامات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الانضمامات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الانضمامات</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @if($pendingCount > 0)
                        <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-warning btn-sm position-relative">
                            <i class="bi bi-clock me-1"></i> طلبات الانضمام المعلقة
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $pendingCount }}
                            </span>
                        </a>
                    @endif
                    @can('payment-list')
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-credit-card me-1"></i> المدفوعات
                    </a>
                    @endcan
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#unenrollByClassModal">
                        <i class="bi bi-people me-1"></i> فصل انضمامات صف
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#unenrollBySubjectModal">
                        <i class="bi bi-journal-text me-1"></i> فصل انضمامات مادة
                    </button>
                    <a href="{{ route('admin.enrollments.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> إضافة انضمامات جديدة
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div id="enrollmentsBulkSuccessAlert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                <i class="bi bi-check-circle me-2"></i><span id="enrollmentsBulkSuccessText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <!-- قسم الفلاتر -->
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-funnel me-2"></i> البحث والفلترة
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="enrollmentsFilterForm">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">بحث</label>
                                        <input type="text" name="search" id="searchQuery" class="form-control form-control-sm"
                                               placeholder="بحث بالاسم، البريد، أو المادة"
                                               value="{{ request('search') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">الطالب</label>
                                        <select name="user_id" id="userFilter" class="form-select form-select-sm">
                                            <option value="">كل الطلاب</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">الصف</label>
                                        <select name="class_id" id="classFilter" class="form-select form-select-sm">
                                            <option value="">كل الصفوف</option>
                                            @foreach($classes ?? [] as $class)
                                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">المادة</label>
                                        <select name="subject_id" id="subjectFilter" class="form-select form-select-sm">
                                            <option value="">كل المواد</option>
                                            @if(request('class_id'))
                                                @foreach($subjects ?? [] as $subject)
                                                    @if($subject->class_id == request('class_id'))
                                                        <option value="{{ $subject->id }}" 
                                                                data-class-id="{{ $subject->class_id }}"
                                                                {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach($subjects ?? [] as $subject)
                                                    <option value="{{ $subject->id }}" 
                                                            data-class-id="{{ $subject->class_id }}"
                                                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1">الحالة</label>
                                        <select name="status" id="statusFilter" class="form-select form-select-sm">
                                            <option value="">كل الحالات</option>
                                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>معلق</option>
                                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>معلق</option>
                                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label mb-1 d-block">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" id="searchBtn" class="btn btn-primary btn-sm flex-fill">
                                                <i class="bi bi-search me-1"></i> بحث
                                            </button>
                                            <button type="button" id="clearFiltersBtn" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-circle me-1"></i> مسح
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- جدول الانضمامات -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة الانضمامات</h5>
                        </div>

                        <div class="card-body">
                            <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                                <p class="text-muted mt-2">جاري التحميل...</p>
                            </div>
                            <form id="bulkUnenrollForm" method="POST" action="{{ route('admin.enrollments.destroy-multiple') }}">
                                @csrf
                                <div id="bulkActionsBar" class="alert alert-secondary d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3" style="display: none;">
                                    <span class="fw-semibold"><span id="bulkSelectedCount">0</span> انضمام محدد</span>
                                    <div class="d-flex gap-2">
                                        <button type="button" id="clearSelectionBtn" class="btn btn-sm btn-outline-secondary">إلغاء التحديد</button>
                                        <button type="submit" id="bulkUnenrollSubmitBtn" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash me-1"></i> فصل الانضمامات المحددة
                                        </button>
                                    </div>
                                </div>
                            <div id="enrollmentsTableContainer">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 44px;" class="text-center">
                                            <input type="checkbox" id="selectAllEnrollments" class="form-check-input" aria-label="تحديد الكل" title="تحديد الكل">
                                        </th>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 180px;">الطالب</th>
                                        <th style="min-width: 200px;">المادة</th>
                                        <th style="min-width: 120px;">الصف</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 150px;">تاريخ الانضمام</th>
                                        <th style="min-width: 150px;">أضيف بواسطة</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="enrollmentsTableBody">
                                    @include('admin.pages.enrollments.partials.table', ['enrollments' => $enrollments])
                                    </tbody>
                                </table>
                            </div>

                            <div id="paginationContainer" class="mt-3">
                                @include('admin.pages.enrollments.partials.pagination', ['enrollments' => $enrollments])
                            </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- مودال تأكيد فصل الانضمامات المحددة --}}
    <div class="modal fade" id="confirmBulkUnenrollModal" tabindex="-1" aria-labelledby="confirmBulkUnenrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="confirmBulkUnenrollModalLabel">تأكيد فصل الانضمامات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 80px;"></i>
                    </div>
                    <h6 class="mb-3">هل أنت متأكد من فصل <span id="confirmBulkUnenrollCount">0</span> انضمام محدد؟</h6>
                    <p class="text-muted mb-0">لا يمكن التراجع عن هذه العملية.</p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmBulkUnenrollBtn">
                        <i class="bi bi-trash me-1"></i> تأكيد
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال فصل جميع انضمامات صف محدد --}}
    <div class="modal fade" id="unenrollByClassModal" tabindex="-1" aria-labelledby="unenrollByClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="unenrollByClassModalLabel">فصل جميع انضمامات صف محدد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الصف</label>
                        <select id="unenrollByClassSelect" class="form-select">
                            <option value="">-- اختر الصف --</option>
                            @foreach($classes ?? [] as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p id="unenrollByClassCountText" class="mb-3" style="display: none;">عدد الانضمامات التي سيتم فصلها: <strong id="unenrollByClassCount">0</strong></p>
                    <p class="text-muted small mb-3">سيتم فصل جميع انضمامات الطلاب المرتبطين بمواد هذا الصف. لا يمكن التراجع عن هذه العملية.</p>
                    <button type="button" id="unenrollByClassSubmitBtn" class="btn btn-danger w-100">
                        <i class="bi bi-trash me-1"></i> فصل جميع انضمامات الصف
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال فصل انضمامات مادة (حسب الصف ثم المادة) --}}
    <div class="modal fade" id="unenrollBySubjectModal" tabindex="-1" aria-labelledby="unenrollBySubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="unenrollBySubjectModalLabel">فصل انضمامات مادة (حسب الصف والمادة)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الصف</label>
                        <select id="unenrollBySubjectClassSelect" class="form-select">
                            <option value="">-- اختر الصف --</option>
                            @foreach($classes ?? [] as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المادة</label>
                        <select id="unenrollBySubjectSelect" class="form-select" disabled>
                            <option value="">-- اختر الصف أولاً --</option>
                        </select>
                    </div>
                    <p id="unenrollBySubjectCountText" class="mb-3" style="display: none;">عدد الانضمامات التي سيتم فصلها: <strong id="unenrollBySubjectCount">0</strong></p>
                    <p class="text-muted small mb-3">سيتم فصل جميع انضمامات الطلاب في هذه المادة من هذا الصف. لا يمكن التراجع عن هذه العملية.</p>
                    <button type="button" id="unenrollBySubjectSubmitBtn" class="btn btn-danger w-100">
                        <i class="bi bi-trash me-1"></i> فصل جميع انضمامات المادة
                    </button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        var bulkSuccess = sessionStorage.getItem('enrollments_bulk_success');
        if (bulkSuccess) {
            sessionStorage.removeItem('enrollments_bulk_success');
            var alertEl = document.getElementById('enrollmentsBulkSuccessAlert');
            var textEl = document.getElementById('enrollmentsBulkSuccessText');
            if (alertEl && textEl) {
                textEl.textContent = bulkSuccess;
                alertEl.style.display = '';
            }
        }
    } catch (e) {}

    const filterForm = document.getElementById('enrollmentsFilterForm');
    const searchQuery = document.getElementById('searchQuery');
    const userFilter = document.getElementById('userFilter');
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const enrollmentsTableBody = document.getElementById('enrollmentsTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const enrollmentsTableContainer = document.getElementById('enrollmentsTableContainer');
    
    const csrfToken = '{{ csrf_token() }}';
    const filterUrl = '{{ route("admin.enrollments.index") }}';
    const getSubjectsUrl = '{{ route("admin.enrollments.get-subjects-by-class") }}';
    
    let searchTimeout;
    let currentPage = 1;

    // دالة لجلب البيانات عبر Ajax
    function fetchEnrollments(page = 1) {
        currentPage = page;
        
        // إظهار loading indicator
        loadingIndicator.style.display = 'block';
        enrollmentsTableContainer.style.opacity = '0.5';
        
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
                enrollmentsTableBody.innerHTML = data.html;
                
                // تحديث pagination
                paginationContainer.innerHTML = data.pagination || '';
                
                // إعادة ربط event listeners للـ pagination
                attachPaginationListeners();
                
                // إعادة تهيئة tooltips
                initializeTooltips();
                
                document.getElementById('selectAllEnrollments').checked = false;
                updateBulkBar();
                
                // تحديث URL بدون إعادة تحميل الصفحة
                const newUrl = `${filterUrl}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                showError('حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error fetching enrollments:', error);
            showError('حدث خطأ أثناء جلب البيانات');
        })
        .finally(() => {
            // إخفاء loading indicator
            loadingIndicator.style.display = 'none';
            enrollmentsTableContainer.style.opacity = '1';
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
                fetchEnrollments(page);
            });
        });
    }

    // دالة لإعادة تهيئة tooltips
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // دالة لإظهار رسالة خطأ
    function showError(message) {
        enrollmentsTableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-danger fw-bold">
                    ${message}
                </td>
            </tr>
        `;
        paginationContainer.innerHTML = '';
    }

    // شريط الفصل الجماعي وتحديد الكل
    function updateBulkBar() {
        const bulkBar = document.getElementById('bulkActionsBar');
        const bulkCountEl = document.getElementById('bulkSelectedCount');
        const selectAllCheckbox = document.getElementById('selectAllEnrollments');
        const checkboxes = enrollmentsTableBody.querySelectorAll('.enrollment-row-checkbox');
        const checked = enrollmentsTableBody.querySelectorAll('.enrollment-row-checkbox:checked');
        const n = checked.length;
        if (bulkCountEl) bulkCountEl.textContent = n;
        if (bulkBar) bulkBar.style.display = n > 0 ? 'flex' : 'none';
        if (selectAllCheckbox && checkboxes.length > 0) {
            selectAllCheckbox.checked = n === checkboxes.length;
            selectAllCheckbox.indeterminate = n > 0 && n < checkboxes.length;
        }
    }
    function attachBulkListeners() {
        const selectAllCheckbox = document.getElementById('selectAllEnrollments');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');
        if (selectAllCheckbox) {
            selectAllCheckbox.onchange = function() {
                enrollmentsTableBody.querySelectorAll('.enrollment-row-checkbox').forEach(cb => { cb.checked = this.checked; });
                updateBulkBar();
            };
        }
        if (clearSelectionBtn) {
            clearSelectionBtn.onclick = function() {
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                enrollmentsTableBody.querySelectorAll('.enrollment-row-checkbox').forEach(cb => { cb.checked = false; });
                updateBulkBar();
            };
        }
        enrollmentsTableBody.addEventListener('change', function(e) {
            if (e.target.classList && e.target.classList.contains('enrollment-row-checkbox')) updateBulkBar();
        });
        var bulkForm = document.getElementById('bulkUnenrollForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var checked = enrollmentsTableBody.querySelectorAll('.enrollment-row-checkbox:checked');
                if (checked.length === 0) {
                    alert('يرجى تحديد انضمام واحد على الأقل.');
                    return false;
                }
                var countEl = document.getElementById('confirmBulkUnenrollCount');
                if (countEl) countEl.textContent = checked.length;
                var confirmModal = document.getElementById('confirmBulkUnenrollModal');
                if (confirmModal && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(confirmModal).show();
                }
            });
        }
        var confirmBulkUnenrollBtn = document.getElementById('confirmBulkUnenrollBtn');
        if (confirmBulkUnenrollBtn) {
            confirmBulkUnenrollBtn.addEventListener('click', function() {
                var confirmModal = document.getElementById('confirmBulkUnenrollModal');
                if (confirmModal && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(confirmModal).hide();
                }
                var checked = enrollmentsTableBody.querySelectorAll('.enrollment-row-checkbox:checked');
                if (checked.length === 0) {
                    return;
                }
                var formData = new FormData();
                formData.append('_token', csrfToken);
                checked.forEach(function(cb) {
                    formData.append('enrollment_ids[]', cb.value);
                });
                var bulkForm = document.getElementById('bulkUnenrollForm');
                var submitBtn = document.getElementById('bulkUnenrollSubmitBtn');
                if (submitBtn) submitBtn.disabled = true;
                fetch(bulkForm ? bulkForm.action : destroyMultipleUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    if (response.redirected && response.url) {
                        window.location.href = response.url;
                        return;
                    }
                    if (response.ok) {
                        return response.json().then(function(j) {
                            if (j && j.success && j.message) {
                                try {
                                    sessionStorage.setItem('enrollments_bulk_success', j.message);
                                } catch (e) {}
                            }
                            window.location.href = (j && j.redirect) ? j.redirect : filterUrl;
                        });
                    }
                    return response.text().then(function(text) {
                        var msg = 'حدث خطأ أثناء فصل الانضمامات.';
                        try {
                            var j = JSON.parse(text);
                            if (j.message) msg = j.message;
                            else if (j.errors && Object.keys(j.errors).length) msg = Object.values(j.errors).flat().join(' ');
                        } catch (err) {}
                        alert(msg + (response.status === 404 ? ' (404 - المسار غير موجود)' : ''));
                        if (submitBtn) submitBtn.disabled = false;
                    });
                })
                .catch(function(err) {
                    alert('حدث خطأ أثناء فصل الانضمامات.');
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }
    }
    var destroyMultipleUrl = '{{ route("admin.enrollments.destroy-multiple") }}';
    attachBulkListeners();

    // حفظ جميع المواد الأصلية
    const allSubjects = [];
    @if(isset($subjects))
        @foreach($subjects as $subject)
            allSubjects.push({
                id: {{ $subject->id }},
                name: '{{ $subject->name }}',
                class_id: {{ $subject->class_id ?? 'null' }}
            });
        @endforeach
    @endif

    // دالة لجلب المواد حسب الصف
    function loadSubjectsByClass(classId, preserveSelected = false) {
        const selectedSubjectId = preserveSelected ? subjectFilter.value : null;
        
        if (!classId || classId === '') {
            // إذا لم يتم اختيار صف، عرض جميع المواد
            subjectFilter.disabled = false;
            subjectFilter.innerHTML = '<option value="">كل المواد</option>';
            allSubjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                option.setAttribute('data-class-id', subject.class_id || '');
                if (preserveSelected && selectedSubjectId && selectedSubjectId == subject.id) {
                    option.selected = true;
                }
                subjectFilter.appendChild(option);
            });
        } else {
            // جلب المواد الخاصة بالصف المحدد عبر Ajax
            subjectFilter.disabled = true;
            subjectFilter.innerHTML = '<option value="">جاري التحميل...</option>';

            fetch(`${getSubjectsUrl}?class_id=${encodeURIComponent(classId)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                subjectFilter.disabled = false;
                subjectFilter.innerHTML = '<option value="">كل المواد</option>';
                
                if (data.success && data.data && Array.isArray(data.data)) {
                    if (data.data.length === 0) {
                        subjectFilter.innerHTML = '<option value="">لا توجد مواد لهذا الصف</option>';
                    } else {
                        data.data.forEach(subject => {
                            const option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.name;
                            option.setAttribute('data-class-id', subject.class_id || '');
                            
                            if (preserveSelected && selectedSubjectId && selectedSubjectId == subject.id) {
                                option.selected = true;
                            }
                            
                            subjectFilter.appendChild(option);
                        });
                    }
                } else {
                    subjectFilter.innerHTML = '<option value="">لا توجد مواد</option>';
                }
            })
            .catch(error => {
                console.error('Error loading subjects:', error);
                subjectFilter.disabled = false;
                subjectFilter.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
        }
    }

    // Debounce للبحث النصي
    searchQuery.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchEnrollments(1);
        }, 500);
    });

    // تحديث فوري عند تغيير الطالب
    userFilter.addEventListener('change', function() {
        fetchEnrollments(1);
    });

    // تحديث فوري عند تغيير الصف + تحديث قائمة المواد
    classFilter.addEventListener('change', function() {
        const classId = this.value;
        loadSubjectsByClass(classId, false);
        fetchEnrollments(1);
    });

    // تحديث فوري عند تغيير المادة
    subjectFilter.addEventListener('change', function() {
        fetchEnrollments(1);
    });

    // تحديث فوري عند تغيير الحالة
    statusFilter.addEventListener('change', function() {
        fetchEnrollments(1);
    });

    // زر البحث
    searchBtn.addEventListener('click', function() {
        fetchEnrollments(1);
    });

    // زر مسح الفلاتر
    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        userFilter.value = '';
        classFilter.value = '';
        subjectFilter.value = '';
        statusFilter.value = '';
        loadSubjectsByClass('', false);
        fetchEnrollments(1);
    });

    // Enter في حقل البحث
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchEnrollments(1);
        }
    });

    // تهيئة pagination listeners عند تحميل الصفحة
    attachPaginationListeners();
    
    // تهيئة tooltips عند تحميل الصفحة
    initializeTooltips();
    
    // إذا كان هناك class_id محدد، تحميل المواد الخاصة به
    const selectedClassId = classFilter.value;
    if (selectedClassId) {
        loadSubjectsByClass(selectedClassId, true);
    }

    // مودال فصل جميع انضمامات صف: اختيار الصف ثم تأكيد وإرسال destroy-by-class
    const unenrollByClassSelect = document.getElementById('unenrollByClassSelect');
    const unenrollByClassSubmitBtn = document.getElementById('unenrollByClassSubmitBtn');
    const destroyByClassUrl = '{{ route("admin.enrollments.destroy-by-class") }}';
    const countByClassUrl = '{{ route("admin.enrollments.count-by-class") }}';
    const unenrollByClassModalEl = document.getElementById('unenrollByClassModal');
    const unenrollByClassCountText = document.getElementById('unenrollByClassCountText');
    const unenrollByClassCountEl = document.getElementById('unenrollByClassCount');

    if (unenrollByClassModalEl) {
        unenrollByClassModalEl.addEventListener('show.bs.modal', function() {
            if (unenrollByClassSelect) unenrollByClassSelect.value = '';
            if (unenrollByClassCountText) unenrollByClassCountText.style.display = 'none';
        });
    }
    if (unenrollByClassSelect) {
        unenrollByClassSelect.addEventListener('change', function() {
            var classId = this.value;
            if (!unenrollByClassCountText || !unenrollByClassCountEl) return;
            if (!classId) {
                unenrollByClassCountText.style.display = 'none';
                return;
            }
            unenrollByClassCountEl.textContent = 'جاري التحميل...';
            unenrollByClassCountText.style.display = '';
            fetch(countByClassUrl + '?class_id=' + encodeURIComponent(classId), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(data) {
                if (data.success && typeof data.count !== 'undefined') {
                    unenrollByClassCountEl.textContent = data.count;
                } else {
                    unenrollByClassCountText.style.display = 'none';
                }
            })
            .catch(function() {
                unenrollByClassCountEl.textContent = '—';
            });
        });
    }
    if (unenrollByClassSubmitBtn) {
        unenrollByClassSubmitBtn.addEventListener('click', function() {
            var classId = unenrollByClassSelect ? unenrollByClassSelect.value : '';
            if (!classId) {
                alert('يرجى اختيار الصف.');
                return;
            }
            if (!confirm('هل أنت متأكد من فصل جميع انضمامات طلاب هذا الصف؟ لا يمكن التراجع عن هذه العملية.')) {
                return;
            }
            unenrollByClassSubmitBtn.disabled = true;
            var formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('class_id', classId);
            fetch(destroyByClassUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (response.ok) {
                    return response.json().then(function(j) {
                        if (j && j.success && j.message) {
                            try { sessionStorage.setItem('enrollments_bulk_success', j.message); } catch (e) {}
                        }
                        if (unenrollByClassModalEl && typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getOrCreateInstance(unenrollByClassModalEl).hide();
                        }
                        window.location.href = (j && j.redirect) ? j.redirect : filterUrl;
                    });
                }
                return response.text().then(function(text) {
                    var msg = 'حدث خطأ أثناء فصل الانضمامات.';
                    try {
                        var j = JSON.parse(text);
                        if (j.message) msg = j.message;
                    } catch (err) {}
                    alert(msg);
                    unenrollByClassSubmitBtn.disabled = false;
                });
            })
            .catch(function(err) {
                alert('حدث خطأ أثناء فصل الانضمامات.');
                unenrollByClassSubmitBtn.disabled = false;
            });
        });
    }

    // مودال فصل انضمامات مادة: اختيار الصف ثم المادة ثم إرسال destroy-by-subject
    var unenrollBySubjectModalEl = document.getElementById('unenrollBySubjectModal');
    var unenrollBySubjectClassSelect = document.getElementById('unenrollBySubjectClassSelect');
    var unenrollBySubjectSelect = document.getElementById('unenrollBySubjectSelect');
    var unenrollBySubjectSubmitBtn = document.getElementById('unenrollBySubjectSubmitBtn');
    var destroyBySubjectUrl = '{{ route("admin.enrollments.destroy-by-subject") }}';
    var countBySubjectUrl = '{{ route("admin.enrollments.count-by-subject") }}';
    var unenrollBySubjectCountText = document.getElementById('unenrollBySubjectCountText');
    var unenrollBySubjectCountEl = document.getElementById('unenrollBySubjectCount');

    if (unenrollBySubjectModalEl) {
        unenrollBySubjectModalEl.addEventListener('show.bs.modal', function() {
            if (unenrollBySubjectClassSelect) unenrollBySubjectClassSelect.value = '';
            if (unenrollBySubjectSelect) {
                unenrollBySubjectSelect.disabled = true;
                unenrollBySubjectSelect.innerHTML = '<option value="">-- اختر الصف أولاً --</option>';
            }
            if (unenrollBySubjectCountText) unenrollBySubjectCountText.style.display = 'none';
        });
    }
    if (unenrollBySubjectClassSelect) {
        unenrollBySubjectClassSelect.addEventListener('change', function() {
            var classId = this.value;
            if (unenrollBySubjectCountText) unenrollBySubjectCountText.style.display = 'none';
            if (!unenrollBySubjectSelect) return;
            if (!classId) {
                unenrollBySubjectSelect.disabled = true;
                unenrollBySubjectSelect.innerHTML = '<option value="">-- اختر الصف أولاً --</option>';
                return;
            }
            unenrollBySubjectSelect.disabled = true;
            unenrollBySubjectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            fetch(getSubjectsUrl + '?class_id=' + encodeURIComponent(classId), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(data) {
                unenrollBySubjectSelect.disabled = false;
                unenrollBySubjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                if (data.success && data.data && Array.isArray(data.data)) {
                    data.data.forEach(function(subject) {
                        var opt = document.createElement('option');
                        opt.value = subject.id;
                        opt.textContent = subject.name;
                        unenrollBySubjectSelect.appendChild(opt);
                    });
                }
            })
            .catch(function(err) {
                unenrollBySubjectSelect.disabled = false;
                unenrollBySubjectSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
        });
    }
    if (unenrollBySubjectSelect) {
        unenrollBySubjectSelect.addEventListener('change', function() {
            var subjectId = this.value;
            if (!unenrollBySubjectCountText || !unenrollBySubjectCountEl) return;
            if (!subjectId) {
                unenrollBySubjectCountText.style.display = 'none';
                return;
            }
            var classId = unenrollBySubjectClassSelect ? unenrollBySubjectClassSelect.value : '';
            unenrollBySubjectCountEl.textContent = 'جاري التحميل...';
            unenrollBySubjectCountText.style.display = '';
            var url = countBySubjectUrl + '?subject_id=' + encodeURIComponent(subjectId);
            if (classId) url += '&class_id=' + encodeURIComponent(classId);
            fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(data) {
                if (data.success && typeof data.count !== 'undefined') {
                    unenrollBySubjectCountEl.textContent = data.count;
                } else {
                    unenrollBySubjectCountText.style.display = 'none';
                }
            })
            .catch(function() {
                unenrollBySubjectCountEl.textContent = '—';
            });
        });
    }
    if (unenrollBySubjectSubmitBtn) {
        unenrollBySubjectSubmitBtn.addEventListener('click', function() {
            var classId = unenrollBySubjectClassSelect ? unenrollBySubjectClassSelect.value : '';
            var subjectId = unenrollBySubjectSelect ? unenrollBySubjectSelect.value : '';
            if (!classId) {
                alert('يرجى اختيار الصف.');
                return;
            }
            if (!subjectId) {
                alert('يرجى اختيار المادة.');
                return;
            }
            if (!confirm('هل أنت متأكد من فصل جميع انضمامات الطلاب في هذه المادة؟ لا يمكن التراجع عن هذه العملية.')) {
                return;
            }
            unenrollBySubjectSubmitBtn.disabled = true;
            var formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('class_id', classId);
            formData.append('subject_id', subjectId);
            fetch(destroyBySubjectUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (response.ok) {
                    return response.json().then(function(j) {
                        if (j && j.success && j.message) {
                            try { sessionStorage.setItem('enrollments_bulk_success', j.message); } catch (e) {}
                        }
                        if (unenrollBySubjectModalEl && typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getOrCreateInstance(unenrollBySubjectModalEl).hide();
                        }
                        window.location.href = (j && j.redirect) ? j.redirect : filterUrl;
                    });
                }
                return response.text().then(function(text) {
                    var msg = 'حدث خطأ أثناء فصل الانضمامات.';
                    try {
                        var j = JSON.parse(text);
                        if (j.message) msg = j.message;
                    } catch (err) {}
                    alert(msg);
                    unenrollBySubjectSubmitBtn.disabled = false;
                });
            })
            .catch(function(err) {
                alert('حدث خطأ أثناء فصل الانضمامات.');
                unenrollBySubjectSubmitBtn.disabled = false;
            });
        });
    }
    });
</script>
@stop
