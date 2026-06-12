@extends('admin.layouts.master')

@section('page-title')
    تقدم المعلمين / أهداف المعلمين
@stop

@push('styles')
    @include('admin.pages.teachers.partials.progress-styles')
@endpush

@section('content')
    <div class="main-content app-content teachers-progress-page">
        <div class="container-fluid">

            <div class="tp-hero my-4">
                <div class="tp-hero__icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="tp-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تقدم المعلمين</li>
                        </ol>
                    </nav>
                    <h4 class="tp-hero__title">تقدم المعلمين</h4>
                    <p class="tp-hero__subtitle">أهداف الصفحات والدروس الأسبوعية لكل معلم حسب المواد المخصصة</p>
                </div>
                <div class="tp-hero__actions">
                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-person-gear me-1"></i> تخصيص المعلمين
                    </a>
                </div>
            </div>

            @include('admin.pages.teachers.partials.progress-week-filter', [
                'activeWeeks' => $activeWeeks ?? collect(),
                'currentWeek' => $currentWeek ?? null,
            ])

            @if(empty($progress))
                <div class="tp-card">
                    <div class="tp-empty">
                        <i class="bi bi-people"></i>
                        <p class="mb-0">لا يوجد معلمون لعرض التقدم.</p>
                    </div>
                </div>
            @else
                @foreach($progress as $item)
                    @php
                        $teacher = $item['teacher'];
                        $pagesProgress = $item['pages_progress'];
                        $weekly = $item['weekly_progress'];
                        $initials = mb_substr($teacher->name, 0, 1);
                    @endphp
                    <article class="tp-teacher-card">
                        <header class="tp-teacher-card__header">
                            <div class="d-flex align-items-center gap-3">
                                <span class="tp-teacher-avatar">{{ $initials }}</span>
                                <div>
                                    <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="tp-teacher-name d-block">
                                        {{ $teacher->name }}
                                    </a>
                                    <span class="small text-muted">معلم</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.teachers.approved-lessons', $teacher) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-list-ul me-1"></i> الدروس المعتمدة
                                </a>
                                <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-sliders me-1"></i> تخصيص
                                </a>
                            </div>
                        </header>
                        <div class="tp-card__body">
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="tp-card__header-icon"><i class="bi bi-journal-bookmark"></i></span>
                                        <span class="fw-bold">تقدم الصفحات حسب المادة</span>
                                    </div>
                                    @include('admin.pages.teachers.partials.progress-pages-table', [
                                        'pagesProgress' => $pagesProgress,
                                        'compact' => true,
                                    ])
                                </div>
                                <div class="col-lg-5">
                                    @include('admin.pages.teachers.partials.progress-weekly-panel', ['weekly' => $weekly])
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            @endif

        </div>
    </div>
@stop
