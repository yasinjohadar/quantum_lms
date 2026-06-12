@extends('admin.layouts.master')

@section('page-title')
    @isset($viewedTeacher)
        تفاصيل الدروس المعتمدة — {{ $viewedTeacher->name }}
    @else
        تفاصيل دروسي المعتمدة
    @endisset
@stop

@push('styles')
    @include('admin.pages.teachers.partials.progress-styles')
@endpush

@section('content')
    @php
        $listRoute = isset($viewedTeacher)
            ? route('admin.teachers.approved-lessons', $viewedTeacher)
            : route('admin.my-approved-lessons');
    @endphp
    <div class="main-content app-content teachers-progress-page">
        <div class="container-fluid">

            <div class="tp-hero my-4">
                <div class="tp-hero__icon">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div class="tp-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            @isset($viewedTeacher)
                                <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.index') }}">تقدم المعلمين</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.show', $viewedTeacher) }}">{{ $viewedTeacher->name }}</a></li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            @endisset
                            <li class="breadcrumb-item active" aria-current="page">الدروس المعتمدة</li>
                        </ol>
                    </nav>
                    <h4 class="tp-hero__title">
                        @isset($viewedTeacher)
                            الدروس المعتمدة — {{ $viewedTeacher->name }}
                        @else
                            تفاصيل دروسي المعتمدة
                        @endisset
                    </h4>
                    <p class="tp-hero__subtitle">
                        الدروس المعتمدة ضمن المواد المخصّصة، مع نطاق الصفحات كما في نموذج الدرس
                    </p>
                </div>
                @isset($viewedTeacher)
                    <div class="tp-hero__actions">
                        <a href="{{ route('admin.teachers.progress.show', $viewedTeacher) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i> صفحة التقدم
                        </a>
                    </div>
                @endisset
            </div>

            @if(($grandLessonsCount ?? 0) === 0)
                <div class="tp-card">
                    <div class="tp-empty">
                        <i class="bi bi-journal-x"></i>
                        <p class="mb-0">
                            @isset($viewedTeacher)
                                لا توجد مواد مخصّصة لهذا المعلم، أو لا توجد دروس معتمدة بعد ضمن مواده.
                            @else
                                لا توجد مواد مخصّصة لك، أو لا توجد دروس معتمدة بعد ضمن موادك.
                            @endisset
                        </p>
                    </div>
                </div>
            @else
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="tp-metric tp-metric--info">
                            <div class="tp-metric__head">
                                <div class="tp-metric__title">إجمالي الدروس المعتمدة</div>
                                <span class="tp-metric__icon"><i class="bi bi-check2-circle"></i></span>
                            </div>
                            <div class="tp-metric__value" style="color: var(--tp-accent-2);">{{ $grandLessonsCount }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tp-metric tp-metric--primary">
                            <div class="tp-metric__head">
                                <div class="tp-metric__title">إجمالي الصفحات المحسوبة</div>
                                <span class="tp-metric__icon"><i class="bi bi-journal-text"></i></span>
                            </div>
                            <div class="tp-metric__value">{{ $grandTotalPages }}</div>
                        </div>
                    </div>
                </div>

                <div class="tp-card mb-4">
                    <div class="tp-card__header">
                        <span class="tp-card__header-icon"><i class="bi bi-funnel"></i></span>
                        تصفية حسب المادة
                    </div>
                    <div class="tp-card__body">
                        <form method="GET" action="{{ $listRoute }}" class="row g-2 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold mb-1">المادة</label>
                                <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل المواد ({{ $grandLessonsCount }} درس)</option>
                                    @foreach($subjectSummaries as $block)
                                        @php $subj = $block['subject']; @endphp
                                        <option value="{{ $subj->id }}" {{ (int) ($selectedSubjectId ?? 0) === (int) $subj->id ? 'selected' : '' }}>
                                            {{ $subj->name }}
                                            @if($subj->schoolClass) — {{ $subj->schoolClass->name }} @endif
                                            ({{ $block['lessons_count'] }} درس، {{ $block['total_pages'] }} صفحة)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                @if($selectedSubjectId)
                                    <a href="{{ $listRoute }}" class="btn btn-outline-secondary btn-sm w-100">إلغاء التصفية</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tp-card">
                    <div class="tp-card__header">
                        <div>
                            <span class="tp-card__header-icon"><i class="bi bi-list-ul"></i></span>
                            قائمة الدروس
                            <span class="text-muted fw-normal small ms-1">({{ $lessons->total() }} درس)</span>
                        </div>
                    </div>
                    <div class="tp-card__body p-0">
                        @if($lessons->isEmpty())
                            <div class="tp-empty py-4">
                                <p class="mb-0 text-muted">لا توجد دروس في هذا التصفية.</p>
                            </div>
                        @else
                            <div class="tp-table-wrap border-0 rounded-0">
                                <div class="table-responsive">
                                    <table class="table tp-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 3rem;">#</th>
                                                <th class="text-center" style="width: 5.5rem;">عرض</th>
                                                @if(empty($selectedSubjectId))
                                                    <th>المادة</th>
                                                @endif
                                                <th>عنوان الدرس</th>
                                                <th>القسم</th>
                                                <th>الوحدة</th>
                                                <th>نطاق الصفحات</th>
                                                <th class="text-center" style="width: 6rem;">الصفحات</th>
                                                <th class="text-center" style="width: 9rem;">تاريخ الاعتماد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lessons as $idx => $row)
                                                @php $lesson = $row['lesson']; @endphp
                                                <tr>
                                                    <td class="text-center text-secondary small">{{ $lessons->firstItem() + $idx }}</td>
                                                    <td class="text-center">
                                                        @canany(['lesson-show', 'lesson-edit'])
                                                            <div class="btn-group btn-group-sm">
                                                                @can('lesson-show')
                                                                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-outline-primary" title="معاينة"><i class="bi bi-eye"></i></a>
                                                                @endcan
                                                                @can('lesson-edit')
                                                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-outline-secondary" title="تعديل"><i class="bi bi-pencil"></i></a>
                                                                @endcan
                                                            </div>
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endcanany
                                                    </td>
                                                    @if(empty($selectedSubjectId))
                                                        <td>
                                                            @if($row['subject'])
                                                                <span class="fw-semibold small">{{ $row['subject']->name }}</span>
                                                                @if($row['subject']->schoolClass)
                                                                    <div class="tp-chip tp-chip--class mt-1">{{ $row['subject']->schoolClass->name }}</div>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                        @can('lesson-show')
                                                            <a href="{{ route('admin.lessons.show', $lesson) }}" class="fw-semibold text-decoration-none text-body">{{ $lesson->title }}</a>
                                                        @else
                                                            <span class="fw-semibold">{{ $lesson->title }}</span>
                                                        @endcan
                                                    </td>
                                                    <td class="small text-muted">{{ $row['section_title'] ?? '—' }}</td>
                                                    <td class="small text-muted">{{ $row['unit_title'] ?? '—' }}</td>
                                                    <td class="small text-muted">{{ $row['pages_label'] }}</td>
                                                    <td class="text-center">
                                                        <span class="tp-pct tp-pct--muted">{{ $row['pages_count'] }}</span>
                                                    </td>
                                                    <td class="text-center small text-muted">
                                                        @if($lesson->reviewed_at)
                                                            <span class="d-block">{{ $lesson->reviewed_at->format('Y-m-d') }}</span>
                                                            <span class="d-block" style="font-size: 0.8rem;">{{ $lesson->reviewed_at->format('H:i') }}</span>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if($lessons->hasPages())
                                <div class="p-3 border-top">
                                    {{ $lessons->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop
