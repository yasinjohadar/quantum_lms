@extends('admin.layouts.master')

@section('page-title')
    جميع الدروس
@stop

@push('styles')
    @include('admin.pages.lessons.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content lessons-index-page">
        <div class="container-fluid">

            <div class="lessons-index-hero my-4">
                <div class="lessons-index-hero__icon">
                    <i class="bi bi-collection-play-fill"></i>
                </div>
                <div class="lessons-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">جميع الدروس</li>
                        </ol>
                    </nav>
                    <h4 class="lessons-index-hero__title">فهرس الدروس</h4>
                    <p class="lessons-index-hero__subtitle">عرض جميع الدروس مع مساراتها وارتباطاتها (sync و legacy)</p>
                </div>
                <div class="lessons-index-stat-mini">
                    <span class="lessons-index-stat-mini__value" id="lessonsTotalCount">{{ number_format($lessons->total()) }}</span>
                    <span class="lessons-index-stat-mini__label">درس مطابق</span>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="lessons-index-card">
                <div class="lessons-index-card__header">
                    <span><span class="lessons-index-card__header-icon"><i class="bi bi-funnel"></i></span> تصفية وبحث</span>
                </div>
                <div class="lessons-index-card__body">
                    @include('admin.pages.lessons.partials.filters')
                </div>
            </div>

            <div class="lessons-index-card lessons-index-card--flush">
                <div class="lessons-index-card__header">
                    <span><span class="lessons-index-card__header-icon"><i class="bi bi-list-ul"></i></span> <span id="lessonsCountLabel">الدروس ({{ $lessons->total() }})</span></span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleLinksColumnBtn" aria-pressed="false">
                            <i class="bi bi-diagram-3 me-1"></i>
                            <span id="toggleLinksColumnBtnLabel">إظهار الارتباطات</span>
                        </button>
                        <span class="text-muted small fw-normal" id="lessonsRangeLabel">{{ $lessons->firstItem() ?? 0 }}–{{ $lessons->lastItem() ?? 0 }} من {{ number_format($lessons->total()) }}</span>
                    </div>
                </div>
                <div class="lessons-index-card__body">
                    <div id="lessonsLoadingIndicator" class="lessons-index-loading" style="display: none;">
                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                        <p class="mb-0 mt-2 small text-muted">جاري التحميل...</p>
                    </div>
                    <div id="lessonsTableContainer">
                        <div class="lessons-index-table-wrap">
                            <table class="table lessons-index-table align-middle mb-0" id="lessonsIndexTable">
                                <thead>
                                    <tr>
                                        <th style="width: 3rem;">#</th>
                                        <th class="lessons-col-title">الدرس</th>
                                        <th class="lessons-col-subject">المادة</th>
                                        <th class="lessons-col-section">القسم / الوحدة</th>
                                        <th class="lessons-col-video">الفيديو</th>
                                        <th class="lessons-col-review">المراجعة</th>
                                        <th class="lessons-col-links">الارتباطات</th>
                                        <th class="lessons-col-actions">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="lessonsTableBody">
                                    @include('admin.pages.lessons.partials.table', ['lessons' => $lessons])
                                </tbody>
                            </table>
                        </div>
                        <div class="lessons-index-pagination" id="lessonsPaginationContainer">
                            @include('admin.pages.lessons.partials.pagination', ['lessons' => $lessons])
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @can('lesson-edit')
        @include('admin.pages.lessons.partials.link-units-modal')
    @endcan
@stop

@push('scripts')
<script>
    window.linkableStructure = @json($linkableStructure ?? []);
    window.adminLessonsLinkUnitsBase = @json(url('admin/lessons'));
    window.formatLinkedUnitBadge = function (u) {
        if (!u) return '';
        if (u.label) return String(u.label);
        var parts = [u.stage_name, u.class_name, u.subject_name, u.section_title, u.title].filter(Boolean);
        return parts.join(' — ');
    };
    window.curriculumCascadeRoutes = {
        subjects: @json(route('admin.subjects.linkable.subjects-by-class')),
        sections: @json(route('admin.subjects.linkable.sections')),
        units: @json(route('admin.subjects.linkable.units')),
    };
</script>
@include('admin.pages.subjects.partials.curriculum-cascade-script')
@can('lesson-edit')
    @include('admin.pages.lessons.partials.link-units-modal-script')
@endcan
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('lessonsFilterForm');
        var fetchUrl = @json(route('admin.lessons.index'));
        var tableBody = document.getElementById('lessonsTableBody');
        var paginationContainer = document.getElementById('lessonsPaginationContainer');
        var loadingIndicator = document.getElementById('lessonsLoadingIndicator');
        var tableContainer = document.getElementById('lessonsTableContainer');
        var countLabel = document.getElementById('lessonsCountLabel');
        var rangeLabel = document.getElementById('lessonsRangeLabel');
        var totalCountEl = document.getElementById('lessonsTotalCount');
        var searchInput = document.getElementById('lessonsSearchInput');
        var searchTimeout = null;
        var fetchController = null;
        var linksColumnStorageKey = 'lessons_index_show_links_column';
        var lessonsTable = document.getElementById('lessonsIndexTable');
        var toggleLinksBtn = document.getElementById('toggleLinksColumnBtn');
        var toggleLinksBtnLabel = document.getElementById('toggleLinksColumnBtnLabel');

        function setLinksColumnVisible(show) {
            if (lessonsTable) {
                lessonsTable.classList.toggle('lessons-index-table--show-links', !!show);
            }
            if (toggleLinksBtn) {
                toggleLinksBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
            }
            if (toggleLinksBtnLabel) {
                toggleLinksBtnLabel.textContent = show ? 'إخفاء الارتباطات' : 'إظهار الارتباطات';
            }
            try {
                localStorage.setItem(linksColumnStorageKey, show ? '1' : '0');
            } catch (e) {}
        }

        function initLinksColumnToggle() {
            var show = false;
            try {
                show = localStorage.getItem(linksColumnStorageKey) === '1';
            } catch (e) {}
            setLinksColumnVisible(show);
            if (toggleLinksBtn) {
                toggleLinksBtn.addEventListener('click', function () {
                    var isVisible = lessonsTable && lessonsTable.classList.contains('lessons-index-table--show-links');
                    setLinksColumnVisible(!isVisible);
                });
            }
        }

        initLinksColumnToggle();

        function buildParams(page) {
            var params = new URLSearchParams();
            if (!form) {
                return params;
            }
            form.querySelectorAll('input[name], select[name]').forEach(function (el) {
                if (el.disabled) {
                    return;
                }
                if (el.tagName === 'INPUT' && el.type === 'text') {
                    var q = el.value.trim();
                    if (q) {
                        params.set(el.name, q);
                    }
                    return;
                }
                if (el.tagName === 'SELECT' && el.value !== '') {
                    params.set(el.name, el.value);
                }
            });
            if (!params.has('link_presence')) {
                params.set('link_presence', 'any');
            }
            params.set('page', String(page || 1));
            return params;
        }

        function updateRangeLabel(count, page) {
            if (!rangeLabel) {
                return;
            }
            var perPage = 20;
            if (count === 0) {
                rangeLabel.textContent = '0–0 من 0';
                return;
            }
            var currentPage = page || 1;
            var from = (currentPage - 1) * perPage + 1;
            var to = Math.min(currentPage * perPage, count);
            rangeLabel.textContent = from + '–' + to + ' من ' + new Intl.NumberFormat('ar').format(count);
        }

        function attachPaginationListeners() {
            if (!paginationContainer) {
                return;
            }
            paginationContainer.querySelectorAll('a[href*="page="]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var url = new URL(link.href, window.location.origin);
                    fetchLessons(parseInt(url.searchParams.get('page') || '1', 10));
                });
            });
        }

        function showFetchError(message) {
            if (!tableBody) {
                return;
            }
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="alert alert-danger mb-0 d-inline-block"><i class="bi bi-exclamation-triangle me-2"></i>' + message + '</div></td></tr>';
        }

        window.fetchLessons = function (page) {
            page = page || 1;
            var params = buildParams(page);

            if (fetchController) {
                fetchController.abort();
            }
            fetchController = new AbortController();

            if (loadingIndicator) {
                loadingIndicator.style.display = 'block';
            }
            if (tableContainer) {
                tableContainer.classList.add('is-loading');
            }

            return fetch(fetchUrl + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: fetchController.signal,
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        throw new Error(data.message || 'خطأ غير متوقع');
                    }

                    if (tableBody) {
                        tableBody.innerHTML = data.html || '';
                    }
                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination || '';
                    }

                    var count = data.count || 0;
                    if (countLabel) {
                        countLabel.textContent = 'الدروس (' + new Intl.NumberFormat('ar').format(count) + ')';
                    }
                    if (totalCountEl) {
                        totalCountEl.textContent = new Intl.NumberFormat('ar').format(count);
                    }
                    updateRangeLabel(count, page);

                    attachPaginationListeners();

                    var newUrl = fetchUrl + '?' + params.toString();
                    window.history.replaceState({}, '', newUrl);
                })
                .catch(function (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    showFetchError('حدث خطأ أثناء جلب النتائج');
                })
                .finally(function () {
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                    if (tableContainer) {
                        tableContainer.classList.remove('is-loading');
                    }
                });
        };

        if (typeof window.initCurriculumCascadePicker === 'function') {
            window.initCurriculumCascadePicker({
                classSelect: document.getElementById('lessonsClassFilter'),
                subjectSelect: document.getElementById('lessonsSubjectFilter'),
                sectionSelect: document.getElementById('lessonsSectionFilter'),
                unitSelect: document.getElementById('lessonsUnitFilter'),
                onClassChange: function () { window.fetchLessons(1); },
                onSubjectChange: function () { window.fetchLessons(1); },
                onSectionChange: function () { window.fetchLessons(1); },
                onUnitChange: function () { window.fetchLessons(1); },
            });
        }

        form.querySelectorAll('.lessons-auto-filter').forEach(function (el) {
            el.addEventListener('change', function () {
                window.fetchLessons(1);
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    window.fetchLessons(1);
                }, 450);
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                window.fetchLessons(1);
            });
        }

        attachPaginationListeners();
    });
</script>
@endpush
