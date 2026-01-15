@extends('admin.layouts.master')

@section('page-title')
    الاختبارات
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الاختبارات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الاختبارات</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-clipboard-check me-1"></i> بحاجة للتصحيح
                    </a>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إنشاء اختبار جديد
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->

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

            {{-- فلاتر البحث --}}
            <div class="card custom-card mb-3">
                <div class="card-body">
                    <form id="quizzesFilterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" id="searchQuery" class="form-control" 
                                       placeholder="ابحث بعنوان الاختبار..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الصف</label>
                                <select name="class_id" id="classFilter" class="form-select">
                                    <option value="">كل الصفوف</option>
                                    @foreach($classes ?? [] as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                            @if($class->stage)
                                                - {{ $class->stage->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">المادة</label>
                                <select name="subject_id" id="subjectFilter" class="form-select" {{ !request('class_id') ? 'disabled' : '' }}>
                                    <option value="">{{ request('class_id') ? 'كل المواد' : 'يرجى اختيار الصف أولاً' }}</option>
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
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" id="statusFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">النشر</label>
                                <select name="is_published" id="publishFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end gap-2">
                                <button type="button" id="searchBtn" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- قائمة الاختبارات --}}
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        <span id="quizzesCount">الاختبارات ({{ $quizzes->total() }})</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="text-muted mt-2">جاري التحميل...</p>
                    </div>
                    <div id="quizzesTableContainer">
                        @if($quizzes->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-journal-x display-4 text-muted"></i>
                                <p class="text-muted mt-3">لا توجد اختبارات حالياً</p>
                                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> إنشاء أول اختبار
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px">#</th>
                                            <th>الاختبار</th>
                                            <th style="width: 150px">المادة</th>
                                            <th style="width: 100px">الأسئلة</th>
                                            <th style="width: 100px">المحاولات</th>
                                            <th style="width: 100px">المدة</th>
                                            <th style="width: 100px">الحالة</th>
                                            <th style="width: 180px">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="quizzesTableBody">
                                        @include('admin.pages.quizzes.partials.table', ['quizzes' => $quizzes])
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer" id="paginationContainer">
                                @include('admin.pages.quizzes.partials.pagination', ['quizzes' => $quizzes])
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->

@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('quizzesFilterForm');
    const searchQuery = document.getElementById('searchQuery');
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const statusFilter = document.getElementById('statusFilter');
    const publishFilter = document.getElementById('publishFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const quizzesTableBody = document.getElementById('quizzesTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const quizzesTableContainer = document.getElementById('quizzesTableContainer');
    const quizzesCount = document.getElementById('quizzesCount');
    
    const fetchUrl = '{{ route("admin.quizzes.index") }}';
    const getSubjectsUrl = '{{ route("admin.quizzes.get-subjects-by-class") }}';
    
    let searchTimeout;
    let currentPage = 1;

    // دالة لجلب الاختبارات عبر Ajax
    function fetchQuizzes(page = 1) {
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
        if (statusFilter.value) {
            params.append('is_active', statusFilter.value);
        }
        if (publishFilter.value) {
            params.append('is_published', publishFilter.value);
        }
        
        params.append('page', page);
        
        // إظهار loading indicator
        loadingIndicator.style.display = 'block';
        quizzesTableContainer.style.display = 'none';
        
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
                    quizzesTableBody.innerHTML = data.html;
                } else {
                    quizzesTableBody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-journal-x display-4 text-muted"></i>
                                <p class="text-muted mt-3">لا توجد اختبارات حالياً</p>
                                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> إنشاء أول اختبار
                                </a>
                            </td>
                        </tr>
                    `;
                }
                
                // تحديث pagination
                paginationContainer.innerHTML = data.pagination || '';
                
                // تحديث العدد
                quizzesCount.textContent = `الاختبارات (${data.count || 0})`;
                
                // إعادة ربط pagination listeners
                attachPaginationListeners();
                
                // تحديث URL بدون إعادة تحميل
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                console.error('Error:', data.message || 'حدث خطأ غير متوقع');
                showError('حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(error => {
            console.error('Error fetching quizzes:', error);
            showError('حدث خطأ أثناء جلب البيانات');
        })
        .finally(() => {
            loadingIndicator.style.display = 'none';
            quizzesTableContainer.style.display = 'block';
        });
    }

    // دالة لإعادة ربط pagination listeners
    function attachPaginationListeners() {
        const paginationLinks = paginationContainer.querySelectorAll('a[href*="page="]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                fetchQuizzes(page);
            });
        });
    }

    // دالة لعرض رسالة خطأ
    function showError(message) {
        quizzesTableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                </td>
            </tr>
        `;
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
            fetchQuizzes(1);
        }, 500);
    });

    // تحديث فوري عند تغيير الصف + تحديث قائمة المواد
    classFilter.addEventListener('change', function() {
        const classId = this.value;
        loadSubjectsByClass(classId, false);
        fetchQuizzes(1);
    });

    // تحديث فوري عند تغيير المادة
    subjectFilter.addEventListener('change', function() {
        fetchQuizzes(1);
    });

    // تحديث فوري عند تغيير الحالة
    statusFilter.addEventListener('change', function() {
        fetchQuizzes(1);
    });

    // تحديث فوري عند تغيير النشر
    publishFilter.addEventListener('change', function() {
        fetchQuizzes(1);
    });

    // زر البحث
    searchBtn.addEventListener('click', function() {
        clearTimeout(searchTimeout);
        fetchQuizzes(1);
    });

    // زر مسح الفلاتر
    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        classFilter.value = '';
        subjectFilter.value = '';
        statusFilter.value = '';
        publishFilter.value = '';
        loadSubjectsByClass('', false);
        fetchQuizzes(1);
    });

    // Enter في حقل البحث
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchQuizzes(1);
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
