@extends('admin.layouts.master')

@section('page-title')
    قائمة المراجعة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">قائمة المراجعة</h5>
                    <p class="text-muted mb-0">مراجعة الدروس والاختبارات المقدمة من المعلمين</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">الدروس قيد المراجعة</h6>
                            <h3 class="mb-0">{{ $stats['lessons']['pending'] }}</h3>
                            <small class="text-white-50">
                                معتمدة: {{ $stats['lessons']['approved'] }} | 
                                مرفوضة: {{ $stats['lessons']['rejected'] }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">الاختبارات قيد المراجعة</h6>
                            <h3 class="mb-0">{{ $stats['quizzes']['pending'] }}</h3>
                            <small class="text-white-50">
                                معتمدة: {{ $stats['quizzes']['approved'] }} | 
                                مرفوضة: {{ $stats['quizzes']['rejected'] }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/review-queue') && !request()->has('tab') ? 'active' : '' }}" 
                               href="{{ route('admin.review-queue.index') }}">
                                <i class="fas fa-list me-1"></i> جميع العناصر
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/review-queue/lessons*') ? 'active' : '' }}" 
                               href="{{ route('admin.review-queue.lessons') }}">
                                <i class="fas fa-book me-1"></i> الدروس
                                @if($stats['lessons']['pending'] > 0)
                                    <span class="badge bg-warning ms-1">{{ $stats['lessons']['pending'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/review-queue/quizzes*') ? 'active' : '' }}" 
                               href="{{ route('admin.review-queue.quizzes') }}">
                                <i class="fas fa-clipboard-check me-1"></i> الاختبارات
                                @if($stats['quizzes']['pending'] > 0)
                                    <span class="badge bg-info ms-1">{{ $stats['quizzes']['pending'] }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- الدروس -->
                    @if($lessons->count() > 0)
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="fas fa-book me-1"></i> الدروس قيد المراجعة</h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان الدرس</th>
                                            <th>المادة</th>
                                            <th>الصف</th>
                                            <th>المعلم</th>
                                            <th>تاريخ الإرسال</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lessons as $lesson)
                                            @php
                                                $subject = $lesson->unit->section->subject ?? null;
                                                $class = $subject->schoolClass ?? null;
                                                $teachers = $subject->assignedTeachers ?? collect();
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $lesson->title }}</td>
                                                <td>{{ $subject->name ?? '-' }}</td>
                                                <td>{{ $class->name ?? '-' }}</td>
                                                <td>{{ $teachers->isNotEmpty() ? $teachers->pluck('name')->join('، ') : '-' }}</td>
                                                <td>{{ $lesson->submitted_for_review_at ? \Carbon\Carbon::parse($lesson->submitted_for_review_at)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.lessons.show', $lesson->id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i> عرض
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {{ $lessons->links() }}
                            </div>
                        </div>
                    @endif

                    <!-- الاختبارات -->
                    @if($quizzes->count() > 0)
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="fas fa-clipboard-check me-1"></i> الاختبارات قيد المراجعة</h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان الاختبار</th>
                                            <th>المادة</th>
                                            <th>المعلم</th>
                                            <th>تاريخ الإرسال</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quizzes as $quiz)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $quiz->title }}</td>
                                                <td>{{ $quiz->subject->name ?? '-' }}</td>
                                                <td>{{ $quiz->creator->name ?? 'N/A' }}</td>
                                                <td>{{ $quiz->submitted_for_review_at ? \Carbon\Carbon::parse($quiz->submitted_for_review_at)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i> عرض
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {{ $quizzes->links() }}
                            </div>
                        </div>
                    @endif

                    @if($lessons->count() == 0 && $quizzes->count() == 0)
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">لا توجد عناصر قيد المراجعة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
