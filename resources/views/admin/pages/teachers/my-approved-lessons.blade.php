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
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-3 d-flex flex-wrap gap-4 justify-content-between align-items-center">
                        <div>
                            <span class="text-muted">إجمالي الدروس المعتمدة:</span>
                            <strong class="ms-1">{{ $grandLessonsCount }}</strong>
                        </div>
                        <div>
                            <span class="text-muted">إجمالي الصفحات المحسوبة:</span>
                            <strong class="ms-1">{{ $grandTotalPages }}</strong>
                        </div>
                    </div>
                </div>

                @foreach($bySubject as $block)
                    @php
                        /** @var \App\Models\Subject $subject */
                        $subject = $block['subject'];
                        $className = $subject->schoolClass?->name;
                    @endphp
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="mb-0 fw-bold">
                                    <a href="{{ route('admin.subjects.show', $subject) }}" class="text-decoration-none">{{ $subject->name }}</a>
                                    @if($className)
                                        <span class="text-muted fw-normal small"> — {{ $className }}</span>
                                    @endif
                                </h6>
                                <span class="small text-muted">
                                    {{ $block['lessons_count'] }} درسًا معتمدًا · {{ $block['total_pages'] }} صفحة (محسوبة)
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(empty($block['lessons']))
                                <p class="text-muted small mb-0 px-3 py-3">لا توجد دروس معتمدة في هذه المادة بعد.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 3rem;">#</th>
                                                <th class="text-center" style="width: 5.5rem;">عرض</th>
                                                <th>عنوان الدرس</th>
                                                <th>القسم</th>
                                                <th>الوحدة</th>
                                                <th>نطاق الصفحات في الكتاب</th>
                                                <th class="text-center">عدد الصفحات</th>
                                                <th class="text-center">تاريخ الاعتماد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($block['lessons'] as $idx => $row)
                                                @php
                                                    $lesson = $row['lesson'];
                                                @endphp
                                                <tr>
                                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            @can('lesson-show')
                                                                <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="معاينة الدرس">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                            @endcan
                                                            @can('lesson-edit')
                                                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="تعديل الدرس">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                            @endcan
                                                            @canany(['lesson-show', 'lesson-edit'])
                                                            @else
                                                                <span class="text-muted small">—</span>
                                                            @endcanany
                                                        </div>
                                                    </td>
                                                    <td class="fw-semibold">
                                                        @can('lesson-show')
                                                            <a href="{{ route('admin.lessons.show', $lesson) }}" class="text-decoration-none">{{ $lesson->title }}</a>
                                                        @else
                                                            @can('lesson-edit')
                                                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-decoration-none">{{ $lesson->title }}</a>
                                                            @else
                                                                {{ $lesson->title }}
                                                            @endcan
                                                        @endcan
                                                    </td>
                                                    <td class="small">{{ $row['section_title'] ?? '—' }}</td>
                                                    <td class="small">{{ $row['unit_title'] ?? '—' }}</td>
                                                    <td class="small">{{ $row['pages_label'] }}</td>
                                                    <td class="text-center">{{ $row['pages_count'] }}</td>
                                                    <td class="text-center small">
                                                        @if($lesson->reviewed_at)
                                                            {{ $lesson->reviewed_at->format('Y-m-d H:i') }}
                                                        @else
                                                            <span class="text-muted">—</span>
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
