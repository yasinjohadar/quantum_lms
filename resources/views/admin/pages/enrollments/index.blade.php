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
                                                    {{ $class->name }} - {{ $class->stage?->name }}
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
                            <div id="enrollmentsTableContainer">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
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
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                <td colspan="8" class="text-center text-danger fw-bold">
                    ${message}
                </td>
            </tr>
        `;
        paginationContainer.innerHTML = '';
    }

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
});
</script>
@stop
