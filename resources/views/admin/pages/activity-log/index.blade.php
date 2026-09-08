@extends('admin.layouts.master')

@section('page-title')
    سجل النشاطات
@stop

@push('styles')
    @include('admin.pages.activity-log.partials.activity-log-styles')
@endpush

@section('content')
<div class="main-content app-content activity-log-page">
    <div class="container-fluid">

        <div class="activity-log-hero my-4">
            <div class="activity-log-hero__icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="activity-log-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">سجل النشاطات</li>
                    </ol>
                </nav>
                <h4 class="activity-log-hero__title">سجل النشاطات</h4>
                <p class="activity-log-hero__subtitle">تتبّع كل إضافة وتعديل وحذف على المنهج والأسئلة والاختبارات</p>
            </div>
            <div class="activity-log-stat-mini">
                <span class="activity-log-stat-mini__value" id="activityLogMatchingCount">{{ number_format($logs->total()) }}</span>
                <span class="activity-log-stat-mini__label">نشاط مطابق</span>
            </div>
        </div>

        <!-- إحصائيات عامة -->
        <div class="row mb-3">
            <div class="col-6 col-md-3">
                <div class="activity-log-card mb-0">
                    <div class="activity-log-card__body text-center">
                        <h4 class="mb-1">{{ number_format($stats['total']) }}</h4>
                        <p class="mb-0 text-muted small">إجمالي النشاطات</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="activity-log-card mb-0">
                    <div class="activity-log-card__body text-center">
                        <h4 class="mb-1">{{ number_format($stats['today']) }}</h4>
                        <p class="mb-0 text-muted small">اليوم</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="activity-log-card mb-0">
                    <div class="activity-log-card__body text-center">
                        <h4 class="mb-1 text-success">{{ number_format($stats['created']) }}</h4>
                        <p class="mb-0 text-muted small">إضافات</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="activity-log-card mb-0">
                    <div class="activity-log-card__body text-center">
                        <h4 class="mb-1 text-danger">{{ number_format($stats['deleted']) }}</h4>
                        <p class="mb-0 text-muted small">حذف</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- فلترة -->
        <div class="activity-log-card">
            <div class="activity-log-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="activity-log-card__header-icon"><i class="bi bi-funnel"></i></span>
                    تصفية وبحث
                </div>
            </div>
            <div class="activity-log-card__body">
                <form id="activityLogFiltersForm" action="{{ route('admin.activity-log.index') }}" method="GET" class="activity-log-filters">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">بحث</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" id="activityLogSearchInput" class="form-control border-start-0"
                                       placeholder="اسم العنصر أو المستخدم..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">نوع العنصر</label>
                            <select name="subject_type" id="activityLogSubjectType" class="form-select">
                                <option value="">الكل</option>
                                @foreach($subjectLabels as $key => $label)
                                    <option value="{{ $key }}" {{ request('subject_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">الإجراء</label>
                            <select name="event" id="activityLogEvent" class="form-select">
                                <option value="">الكل</option>
                                @foreach($eventLabels as $key => $label)
                                    <option value="{{ $key }}" {{ request('event') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">الصف</label>
                            <select name="class_id" id="activityLogClassId" class="form-select">
                                <option value="">كل الصفوف</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">المادة</label>
                            <select name="curriculum_subject_id" id="activityLogSubjectId" class="form-select">
                                <option value="">كل المواد</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" data-class-id="{{ $subject->class_id }}" {{ (string) request('curriculum_subject_id') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" id="activityLogDateFrom" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" id="activityLogDateTo" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-6 col-lg-1">
                            <button type="submit" class="btn btn-primary btn-sm d-block w-100">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- جدول النشاطات -->
        <div class="activity-log-card">
            <div class="activity-log-card__body p-0">
                <div class="activity-log-table-wrap">
                    <div class="table-responsive">
                        <table class="table activity-log-table text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>نوع العنصر</th>
                                    <th>العنصر</th>
                                    <th>الصف / المادة</th>
                                    <th>الإجراء</th>
                                    <th>الوقت</th>
                                    <th>التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody id="activityLogTableBody">
                                @include('admin.pages.activity-log.partials.table-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-3 pb-3 activity-log-pagination" id="activityLogPaginationContainer">
                    {{ $logs->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('activityLogFiltersForm');
        const tableBody = document.getElementById('activityLogTableBody');
        const paginationContainer = document.getElementById('activityLogPaginationContainer');
        const matchingCount = document.getElementById('activityLogMatchingCount');
        const searchInput = document.getElementById('activityLogSearchInput');
        const subjectTypeSelect = document.getElementById('activityLogSubjectType');
        const eventSelect = document.getElementById('activityLogEvent');
        const classSelect = document.getElementById('activityLogClassId');
        const subjectSelect = document.getElementById('activityLogSubjectId');
        const dateFrom = document.getElementById('activityLogDateFrom');
        const dateTo = document.getElementById('activityLogDateTo');
        const fetchUrl = '{{ route("admin.activity-log.index") }}';

        let debounceTimer = null;

        // إظهار المواد التابعة للصف المختار فقط (تصفية من جانب المتصفح، بلا طلب إضافي)
        function filterSubjectOptions() {
            if (!classSelect || !subjectSelect) return;
            const classId = classSelect.value;
            Array.from(subjectSelect.options).forEach(function (opt) {
                if (!opt.value) return;
                opt.hidden = !!classId && opt.dataset.classId !== classId;
            });
            if (subjectSelect.selectedOptions[0]?.hidden) {
                subjectSelect.value = '';
            }
        }
        filterSubjectOptions();

        function buildFetchParams(page) {
            const params = new URLSearchParams();
            if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
            if (subjectTypeSelect.value) params.set('subject_type', subjectTypeSelect.value);
            if (eventSelect.value) params.set('event', eventSelect.value);
            if (classSelect && classSelect.value) params.set('class_id', classSelect.value);
            if (subjectSelect && subjectSelect.value) params.set('curriculum_subject_id', subjectSelect.value);
            if (dateFrom.value) params.set('date_from', dateFrom.value);
            if (dateTo.value) params.set('date_to', dateTo.value);
            params.set('page', page || 1);
            return params.toString();
        }

        function fetchLogs(page) {
            const url = `${fetchUrl}?${buildFetchParams(page)}`;
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) return;
                if (tableBody && data.html !== undefined) tableBody.innerHTML = data.html;
                if (paginationContainer && data.pagination !== undefined) paginationContainer.innerHTML = data.pagination;
                if (matchingCount && data.total_matching !== undefined) matchingCount.textContent = new Intl.NumberFormat().format(data.total_matching);
                history.replaceState(null, '', url.replace(fetchUrl, window.location.pathname));
            })
            .catch(err => console.error('AJAX fetchLogs error:', err));
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                fetchLogs(1);
            });
        }

        [subjectTypeSelect, eventSelect, subjectSelect, dateFrom, dateTo].forEach(function (el) {
            if (el) el.addEventListener('change', function () { fetchLogs(1); });
        });

        if (classSelect) {
            classSelect.addEventListener('change', function () {
                filterSubjectOptions();
                fetchLogs(1);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () { fetchLogs(1); }, 400);
            });
        }

        if (paginationContainer) {
            paginationContainer.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || !href.includes('page=')) return;
                e.preventDefault();
                const url = new URL(href, window.location.origin);
                fetchLogs(url.searchParams.get('page') || 1);
            });
        }
    });
</script>
@endpush
