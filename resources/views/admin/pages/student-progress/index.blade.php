@extends('admin.layouts.master')

@section('page-title')
    مراقبة تقدم الطلاب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">مراقبة تقدم الطلاب</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">مراقبة تقدم الطلاب</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form id="studentProgressFilterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">البحث</label>
                        <input type="text" name="search" id="searchQuery" class="form-control" 
                               placeholder="اسم الطالب أو البريد الإلكتروني" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الصف</label>
                        <select name="class_id" id="classFilter" class="form-select">
                            <option value="">جميع الصفوف</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                    @if($class->stage)
                                        - {{ $class->stage->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الكورس</label>
                        <select name="subject_id" id="subjectFilter" class="form-select" {{ !request('class_id') ? 'disabled' : '' }}>
                            <option value="">{{ request('class_id') ? 'جميع الكورسات' : 'يرجى اختيار الصف أولاً' }}</option>
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
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="searchBtn" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <button type="button" id="clearFiltersBtn" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            إعادة تعيين
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">قائمة الطلاب</h5>
            </div>
            <div class="card-body">
                <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="text-muted mt-2">جاري التحميل...</p>
                </div>
                <div id="studentsTableContainer">
                    @if($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>عدد الكورسات</th>
                                        <th>متوسط التقدم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody">
                                    @include('admin.pages.student-progress.partials.table', ['students' => $students, 'studentsStats' => $studentsStats])
                                </tbody>
                            </table>
                        </div>
                        <div id="paginationContainer" class="mt-3">
                            @include('admin.pages.student-progress.partials.pagination', ['students' => $students])
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>
                            <h5 class="mb-2">لا يوجد طلاب</h5>
                            <p class="text-muted">لم يتم العثور على أي طلاب</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('studentProgressFilterForm');
    const searchQuery = document.getElementById('searchQuery');
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    let studentsTableBody = document.getElementById('studentsTableBody');
    let paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const studentsTableContainer = document.getElementById('studentsTableContainer');
    
    const fetchUrl = '{{ route("admin.student-progress.index") }}';
    const getSubjectsUrl = '{{ route("admin.student-progress.get-subjects-by-class") }}';
    
    let searchTimeout;
    let currentPage = 1;

    // دالة لجلب الطلاب عبر Ajax
    function fetchStudents(page = 1) {
        currentPage = page;
        const params = new URLSearchParams();
        
        if (searchQuery.value.trim()) {
            params.append('search', searchQuery.value.trim());
        }
        if (classFilter.value) {
            params.append('class_id', classFilter.value);
        }
        if (subjectFilter.value) {
            params.append('subject_id', subjectFilter.value);
        }
        
        params.append('page', page);
        
        // إظهار loading indicator
        loadingIndicator.style.display = 'block';
        studentsTableContainer.style.display = 'none';
        
        fetch(`${fetchUrl}?${params.toString()}`, {
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
            if (data.success) {
                // تحديث الجدول
                if (data.html.trim()) {
                    // إظهار الجدول إذا كان هناك محتوى
                    let tableWrapper = studentsTableContainer.querySelector('.table-responsive');
                    if (!tableWrapper) {
                        // إنشاء الجدول إذا لم يكن موجوداً
                        studentsTableContainer.innerHTML = '';
                        const tableDiv = document.createElement('div');
                        tableDiv.className = 'table-responsive';
                        tableDiv.innerHTML = `
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>عدد الكورسات</th>
                                        <th>متوسط التقدم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody"></tbody>
                            </table>
                        `;
                        studentsTableContainer.appendChild(tableDiv);
                        const paginationDiv = document.createElement('div');
                        paginationDiv.id = 'paginationContainer';
                        paginationDiv.className = 'mt-3';
                        studentsTableContainer.appendChild(paginationDiv);
                        studentsTableBody = document.getElementById('studentsTableBody');
                        paginationContainer = paginationDiv;
                    } else {
                        // التأكد من وجود paginationContainer
                        if (!paginationContainer || !studentsTableContainer.contains(paginationContainer)) {
                            // إزالة paginationContainer القديم إذا كان موجوداً خارج studentsTableContainer
                            const oldPagination = document.getElementById('paginationContainer');
                            if (oldPagination && !studentsTableContainer.contains(oldPagination)) {
                                oldPagination.remove();
                            }
                            // إنشاء paginationContainer جديد داخل studentsTableContainer
                            const paginationDiv = document.createElement('div');
                            paginationDiv.id = 'paginationContainer';
                            paginationDiv.className = 'mt-3';
                            studentsTableContainer.appendChild(paginationDiv);
                            paginationContainer = paginationDiv;
                        }
                        // التأكد من وجود studentsTableBody
                        if (!studentsTableBody) {
                            studentsTableBody = tableWrapper.querySelector('#studentsTableBody');
                        }
                    }
                    
                    if (studentsTableBody) {
                        studentsTableBody.innerHTML = data.html;
                    }
                } else {
                    // إخفاء الجدول إذا لم يكن هناك محتوى
                    studentsTableContainer.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>
                            <h5 class="mb-2">لا يوجد طلاب</h5>
                            <p class="text-muted">لم يتم العثور على أي طلاب</p>
                        </div>
                    `;
                    studentsTableBody = null;
                    paginationContainer = null;
                }
                
                // تحديث pagination
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination || '';
                    // إعادة ربط pagination listeners
                    attachPaginationListeners();
                }
                
                // تحديث URL بدون إعادة تحميل
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                console.error('Error:', data.message || 'حدث خطأ غير متوقع');
                showError('حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error fetching students:', error);
            showError('حدث خطأ أثناء جلب البيانات');
        })
        .finally(() => {
            loadingIndicator.style.display = 'none';
            studentsTableContainer.style.display = 'block';
        });
    }

    // دالة لإعادة ربط pagination listeners
    function attachPaginationListeners() {
        if (!paginationContainer) return;
        const paginationLinks = paginationContainer.querySelectorAll('a[href*="page="]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                fetchStudents(page);
            });
        });
    }

    // دالة لعرض رسالة خطأ
    function showError(message) {
        if (studentsTableBody) {
            studentsTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${message}
                        </div>
                    </td>
                </tr>
            `;
        } else {
            studentsTableContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                </div>
            `;
        }
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
            // إذا لم يتم اختيار صف، تعطيل المواد وإفراغها
            subjectFilter.disabled = true;
            subjectFilter.innerHTML = '<option value="">يرجى اختيار الصف أولاً</option>';
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
                subjectFilter.innerHTML = '<option value="">جميع الكورسات</option>';
                
                if (data.success && data.data && Array.isArray(data.data)) {
                    if (data.data.length === 0) {
                        subjectFilter.innerHTML = '<option value="">لا توجد كورسات لهذا الصف</option>';
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
                    subjectFilter.innerHTML = '<option value="">لا توجد كورسات</option>';
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
            fetchStudents(1);
        }, 500);
    });

    // تحديث فوري عند تغيير الصف + تحديث قائمة الكورسات
    classFilter.addEventListener('change', function() {
        const classId = this.value;
        loadSubjectsByClass(classId, false);
        fetchStudents(1);
    });

    // تحديث فوري عند تغيير الكورس
    subjectFilter.addEventListener('change', function() {
        fetchStudents(1);
    });

    // زر البحث
    searchBtn.addEventListener('click', function() {
        clearTimeout(searchTimeout);
        fetchStudents(1);
    });

    // زر مسح الفلاتر
    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        classFilter.value = '';
        subjectFilter.value = '';
        loadSubjectsByClass('', false);
        fetchStudents(1);
    });

    // Enter في حقل البحث
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchStudents(1);
        }
    });

    // تهيئة pagination listeners عند تحميل الصفحة
    attachPaginationListeners();
    
    // إذا كان هناك class_id محدد، تحميل المواد الخاصة به
    // وإلا تعطيل المواد
    const selectedClassId = classFilter.value;
    if (selectedClassId) {
        loadSubjectsByClass(selectedClassId, true);
    } else {
        // تعطيل المواد في البداية إذا لم يكن هناك صف محدد
        subjectFilter.disabled = true;
        subjectFilter.innerHTML = '<option value="">يرجى اختيار الصف أولاً</option>';
    }
});
</script>
@stop
