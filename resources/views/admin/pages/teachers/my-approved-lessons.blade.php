@extends('admin.layouts.master')

@section('page-title')
    @isset($viewedTeacher)
        تفاصيل الدروس المعتمدة — {{ $viewedTeacher->name }}
    @else
        تفاصيل دروسي المعتمدة
    @endisset
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    @isset($viewedTeacher)
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <a href="{{ route('admin.teachers.progress.index') }}" class="btn btn-light btn-sm border">
                                <i class="bi bi-arrow-right me-1"></i> تقدم المعلمين
                            </a>
                            <a href="{{ route('admin.teachers.progress.show', $viewedTeacher) }}" class="btn btn-light btn-sm border">
                                <i class="bi bi-person-badge me-1"></i> صفحة تقدم {{ $viewedTeacher->name }}
                            </a>
                        </div>
                    @endisset
                    <h5 class="page-title fs-21 mb-1">
                        @isset($viewedTeacher)
                            تفاصيل الدروس المعتمدة وصفحات الكتاب — {{ $viewedTeacher->name }}
                        @else
                            تفاصيل الدروس المعتمدة وصفحات الكتاب
                        @endisset
                    </h5>
                    <p class="text-muted small mb-0">
                        @isset($viewedTeacher)
                            الدروس التي حالتها «معتمد» ضمن المواد المخصّصة لهذا المعلم، مع نطاق الصفحات كما في نموذج الدرس.
                        @else
                            تُعرض هنا الدروس التي حالتها «معتمد» ضمن المواد المخصّصة لك، مع نطاق الصفحات كما في نموذج الدرس
                            (من الصفحة / إلى الصفحة). يُطابق مجموع «عدد الصفحات» ما يُحسب في عمود المنجز في إحصائيات التقدم عندما يكون الدرس مرتبطاً بالمادة عبر الوحدة أو القسم.
                        @endisset
                    </p>
                </div>
            </div>

            @if(empty($bySubject))
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted mb-0">
                            @isset($viewedTeacher)
                                لا توجد مواد مخصّصة لهذا المعلم، أو لا توجد دروس معتمدة بعد ضمن مواده.
                            @else
                                لا توجد مواد مخصّصة لك، أو لا توجد دروس معتمدة بعد ضمن موادك.
                            @endisset
                        </p>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border mb-3">
                    <div class="card-body py-3 d-flex flex-wrap gap-4 justify-content-between align-items-center bg-light bg-opacity-50 rounded-top">
                        <div>
                            <span class="text-muted small d-block mb-1">إجمالي الدروس المعتمدة</span>
                            <span class="fs-5 fw-bold text-primary">{{ $grandLessonsCount }}</span>
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">إجمالي الصفحات المحسوبة</span>
                            <span class="fs-5 fw-bold text-primary">{{ $grandTotalPages }}</span>
                        </div>
                    </div>
                </div>

                @foreach($bySubject as $block)
                    @php
                        /** @var \App\Models\Subject $subject */
                        $subject = $block['subject'];
                        $className = $subject->schoolClass?->name;
                    @endphp
                    <div class="card shadow-sm border mb-4 overflow-hidden">
                        <div class="card-header bg-light border-bottom py-3">
                            <div>
                                <h6 class="mb-1 fw-bold">
                                    <a href="{{ route('admin.subjects.show', $subject) }}" class="text-decoration-none">{{ $subject->name }}</a>
                                    @if($className)
                                        <span class="text-muted fw-normal small"> — {{ $className }}</span>
                                    @endif
                                </h6>
                                <span class="badge bg-white text-dark border">
                                    {{ $block['lessons_count'] }} درسًا معتمدًا
                                </span>
                                <span class="badge bg-primary-transparent text-primary border border-primary border-opacity-25 ms-1">
                                    {{ $block['total_pages'] }} صفحة محسوبة
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(empty($block['lessons']))
                                <p class="text-muted small mb-0 px-3 py-4 text-center">لا توجد دروس معتمدة في هذه المادة بعد.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-striped mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr class="text-nowrap">
                                                <th class="text-center" scope="col" style="width: 3rem;">#</th>
                                                <th class="text-center" scope="col" style="width: 5.5rem;">عرض</th>
                                                <th scope="col">عنوان الدرس</th>
                                                <th scope="col">القسم</th>
                                                <th scope="col">الوحدة</th>
                                                <th scope="col">نطاق الصفحات</th>
                                                <th class="text-center" scope="col" style="width: 6rem;">الصفحات</th>
                                                <th class="text-center" scope="col" style="width: 9rem;">تاريخ الاعتماد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($block['lessons'] as $idx => $row)
                                                @php
                                                    $lesson = $row['lesson'];
                                                @endphp
                                                <tr>
                                                    <td class="text-center text-secondary small" style="font-variant-numeric: tabular-nums;">{{ $idx + 1 }}</td>
                                                    <td class="text-center">
                                                        @canany(['lesson-show', 'lesson-edit'])
                                                            <div class="btn-group btn-group-sm" role="group" aria-label="إجراءات الدرس">
                                                                @can('lesson-show')
                                                                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-outline-primary" title="معاينة الدرس">
                                                                        <i class="bi bi-eye"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('lesson-edit')
                                                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-outline-secondary" title="تعديل الدرس">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endcanany
                                                    </td>
                                                    <td>
                                                        @can('lesson-show')
                                                            <a href="{{ route('admin.lessons.show', $lesson) }}" class="fw-semibold text-decoration-none text-body">{{ $lesson->title }}</a>
                                                        @else
                                                            @can('lesson-edit')
                                                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="fw-semibold text-decoration-none text-body">{{ $lesson->title }}</a>
                                                            @else
                                                                <span class="fw-semibold">{{ $lesson->title }}</span>
                                                            @endcan
                                                        @endcan
                                                    </td>
                                                    <td class="small text-muted">{{ $row['section_title'] ?? '—' }}</td>
                                                    <td class="small text-muted">{{ $row['unit_title'] ?? '—' }}</td>
                                                    <td class="small"><span class="text-muted">{{ $row['pages_label'] }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge rounded-pill bg-light text-dark border fw-normal" style="font-variant-numeric: tabular-nums;">{{ $row['pages_count'] }}</span>
                                                    </td>
                                                    <td class="text-center small text-muted" style="font-variant-numeric: tabular-nums;">
                                                        @if($lesson->reviewed_at)
                                                            <span class="d-block">{{ $lesson->reviewed_at->format('Y-m-d') }}</span>
                                                            <span class="d-block text-secondary" style="font-size: 0.8rem;">{{ $lesson->reviewed_at->format('H:i') }}</span>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
@stop
