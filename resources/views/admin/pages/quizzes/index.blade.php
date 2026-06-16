@extends('admin.layouts.master')

@section('page-title')
    الاختبارات
@stop

@push('styles')
    @include('admin.pages.quizzes.partials.quizzes-index-styles')
@endpush

@section('content')
    <div class="main-content app-content quizzes-index-page">
        <div class="container-fluid">

            <div class="quizzes-index-hero my-4">
                <div class="quizzes-index-hero__icon">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div class="quizzes-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الاختبارات</li>
                        </ol>
                    </nav>
                    <h4 class="quizzes-index-hero__title">إدارة الاختبارات</h4>
                    <p class="quizzes-index-hero__subtitle">إنشاء الاختبارات ومتابعة الأسئلة والمحاولات والمراجعة</p>
                </div>
                <div class="quizzes-index-stat-mini">
                    <span class="quizzes-index-stat-mini__value" id="quizzesTotalCount">{{ number_format($quizzes->total()) }}</span>
                    <span class="quizzes-index-stat-mini__label">اختبار مطابق</span>
                </div>
                <div class="quizzes-index-hero__actions">
                    @can('quiz-attempt-needs-grading')
                        <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-clipboard-check me-1"></i> بحاجة للتصحيح
                        </a>
                    @endcan
                    @can('quiz-create')
                        <a href="{{ route('admin.quizzes.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> إنشاء اختبار جديد
                        </a>
                    @endcan
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

            <div class="quizzes-index-card">
                <div class="quizzes-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="quizzes-index-card__header-icon"><i class="bi bi-funnel"></i></span>
                        <span>تصفية وبحث</span>
                    </div>
                </div>
                <div class="quizzes-index-card__body">
                    <form id="quizzesFilterForm" class="quizzes-index-filters">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" id="searchQuery" class="form-control border-start-0"
                                           placeholder="ابحث بعنوان الاختبار..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
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
                            <div class="col-6 col-md-4 col-lg-2">
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
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" id="statusFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">النشر</label>
                                <select name="is_published" id="publishFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">حالة المراجعة</label>
                                <select name="review_status" id="reviewStatusFilter" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="draft" {{ request('review_status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="pending_review" {{ request('review_status') === 'pending_review' ? 'selected' : '' }}>قيد المراجعة</option>
                                    <option value="approved" {{ request('review_status') === 'approved' ? 'selected' : '' }}>معتمد</option>
                                    <option value="rejected" {{ request('review_status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 col-lg-1 d-flex gap-2">
                                <button type="button" id="searchBtn" class="btn btn-primary flex-fill" title="بحث">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary" title="مسح الفلاتر">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="quizzes-index-card quizzes-index-card--flush">
                <div class="quizzes-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="quizzes-index-card__header-icon"><i class="bi bi-list-ul"></i></span>
                        <span id="quizzesCount">الاختبارات ({{ $quizzes->total() }})</span>
                    </div>
                    <span class="text-muted small fw-normal">{{ $quizzes->firstItem() ?? 0 }}–{{ $quizzes->lastItem() ?? 0 }} من {{ number_format($quizzes->total()) }}</span>
                </div>
                <div class="quizzes-index-card__body">
                    <div id="loadingIndicator" class="quizzes-index-loading" style="display: none;">
                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                        <p class="mb-0 mt-2 small">جاري التحميل...</p>
                    </div>
                    <div id="quizzesTableContainer">
                        <div class="quizzes-index-table-wrap">
                            <table class="table quizzes-index-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>الاختبار</th>
                                        <th class="quizzes-col-subject">المادة</th>
                                        <th>الأسئلة</th>
                                        <th class="quizzes-col-attempts">المحاولات</th>
                                        <th class="quizzes-col-duration">المدة</th>
                                        <th>الحالة</th>
                                        <th class="quizzes-col-review">حالة المراجعة</th>
                                        <th style="width: 150px;">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="quizzesTableBody">
                                    @include('admin.pages.quizzes.partials.table', ['quizzes' => $quizzes])
                                </tbody>
                            </table>
                        </div>
                        <div class="quizzes-index-pagination" id="paginationContainer">
                            @include('admin.pages.quizzes.partials.pagination', ['quizzes' => $quizzes])
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
    const searchQuery = document.getElementById('searchQuery');
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const statusFilter = document.getElementById('statusFilter');
    const publishFilter = document.getElementById('publishFilter');
    const reviewStatusFilter = document.getElementById('reviewStatusFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const quizzesTableBody = document.getElementById('quizzesTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const quizzesTableContainer = document.getElementById('quizzesTableContainer');
    const quizzesCount = document.getElementById('quizzesCount');
    const quizzesTotalCount = document.getElementById('quizzesTotalCount');

    const fetchUrl = '{{ route("admin.quizzes.index") }}';
    const getSubjectsUrl = '{{ route("admin.quizzes.get-subjects-by-class") }}';

    let searchTimeout;

    function buildParams(page) {
        const params = new URLSearchParams();
        if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
        if (classFilter.value) params.append('class_id', classFilter.value);
        if (subjectFilter.value) params.append('subject_id', subjectFilter.value);
        if (statusFilter.value) params.append('is_active', statusFilter.value);
        if (publishFilter.value) params.append('is_published', publishFilter.value);
        if (reviewStatusFilter.value) params.append('review_status', reviewStatusFilter.value);
        params.append('page', page);
        return params;
    }

    function fetchQuizzes(page = 1) {
        const params = buildParams(page);

        loadingIndicator.style.display = 'block';
        quizzesTableContainer.classList.add('is-loading');

        fetch(`${fetchUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                quizzesTableBody.innerHTML = data.html.trim()
                    ? data.html
                    : `<tr><td colspan="9"><div class="quizzes-index-empty"><i class="bi bi-journal-x"></i><p class="mb-3">لا توجد اختبارات حالياً</p><a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> إنشاء أول اختبار</a></div></td></tr>`;

                paginationContainer.innerHTML = data.pagination || '';
                const count = data.count || 0;
                quizzesCount.textContent = `الاختبارات (${count})`;
                if (quizzesTotalCount) quizzesTotalCount.textContent = new Intl.NumberFormat('ar').format(count);

                attachPaginationListeners();

                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                showError(data.message || 'حدث خطأ غير متوقع');
            }
        })
        .catch(() => showError('حدث خطأ أثناء جلب البيانات'))
        .finally(() => {
            loadingIndicator.style.display = 'none';
            quizzesTableContainer.classList.remove('is-loading');
        });
    }

    function attachPaginationListeners() {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                fetchQuizzes(url.searchParams.get('page') || 1);
            });
        });
    }

    function showError(message) {
        quizzesTableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="alert alert-danger mb-0 d-inline-block">
                        <i class="bi bi-exclamation-triangle me-2"></i>${message}
                    </div>
                </td>
            </tr>`;
    }

    function loadSubjectsByClass(classId, preserveSelected = false) {
        const selectedSubjectId = preserveSelected ? subjectFilter.value : null;

        if (!classId) {
            subjectFilter.disabled = true;
            subjectFilter.innerHTML = '<option value="">يرجى اختيار الصف أولاً</option>';
            return;
        }

        subjectFilter.disabled = true;
        subjectFilter.innerHTML = '<option value="">جاري التحميل...</option>';

        fetch(`${getSubjectsUrl}?class_id=${encodeURIComponent(classId)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            subjectFilter.disabled = false;
            subjectFilter.innerHTML = '<option value="">كل المواد</option>';
            if (data.success && Array.isArray(data.data) && data.data.length) {
                data.data.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.id;
                    option.textContent = subject.name;
                    if (preserveSelected && selectedSubjectId == subject.id) option.selected = true;
                    subjectFilter.appendChild(option);
                });
            } else {
                subjectFilter.innerHTML = '<option value="">لا توجد مواد لهذا الصف</option>';
            }
        })
        .catch(() => {
            subjectFilter.disabled = false;
            subjectFilter.innerHTML = '<option value="">خطأ في التحميل</option>';
        });
    }

    searchQuery.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchQuizzes(1), 500);
    });

    classFilter.addEventListener('change', function() {
        loadSubjectsByClass(this.value, false);
        fetchQuizzes(1);
    });

    subjectFilter.addEventListener('change', () => fetchQuizzes(1));
    statusFilter.addEventListener('change', () => fetchQuizzes(1));
    publishFilter.addEventListener('change', () => fetchQuizzes(1));
    reviewStatusFilter.addEventListener('change', () => fetchQuizzes(1));

    searchBtn.addEventListener('click', function() {
        clearTimeout(searchTimeout);
        fetchQuizzes(1);
    });

    clearFiltersBtn.addEventListener('click', function() {
        searchQuery.value = '';
        classFilter.value = '';
        subjectFilter.value = '';
        statusFilter.value = '';
        publishFilter.value = '';
        reviewStatusFilter.value = '';
        loadSubjectsByClass('', false);
        fetchQuizzes(1);
    });

    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchQuizzes(1);
        }
    });

    attachPaginationListeners();

    if (classFilter.value) {
        loadSubjectsByClass(classFilter.value, true);
    } else {
        subjectFilter.disabled = true;
        subjectFilter.innerHTML = '<option value="">يرجى اختيار الصف أولاً</option>';
    }
});
</script>
@stop
