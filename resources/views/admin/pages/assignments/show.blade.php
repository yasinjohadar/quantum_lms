@extends('admin.layouts.master')

@section('page-title')
    {{ $assignment->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $assignment->title }}</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="{{ route('admin.assignments.submissions.index', $assignment) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-list me-1"></i> الإرسالات ({{ $stats['total_submissions'] }})
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">تفاصيل الواجب</h5>
                            
                            <div class="mb-3">
                                <strong>الوصف:</strong>
                                <p class="text-muted">{{ $assignment->description ?? 'لا يوجد وصف' }}</p>
                            </div>

                            @if($assignment->instructions)
                                <div class="mb-3">
                                    <strong>التعليمات:</strong>
                                    <div class="bg-light p-3 rounded">
                                        {!! nl2br(e($assignment->instructions)) !!}
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>المرتبط بـ:</strong>
                                    @if($assignment->assignable_type === 'App\Models\Subject')
                                        <span class="badge bg-info">{{ $assignment->assignable->name ?? 'N/A' }}</span>
                                    @elseif($assignment->assignable_type === 'App\Models\Unit')
                                        <span class="badge bg-warning">{{ $assignment->assignable->title ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $assignment->assignable->title ?? 'N/A' }}</span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>الدرجة الكاملة:</strong> {{ $assignment->max_score }}
                                </div>
                                <div class="col-md-6">
                                    <strong>موعد التسليم:</strong> {{ $assignment->due_date ? $assignment->due_date->format('Y-m-d H:i') : 'لا يوجد' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>عدد المحاولات:</strong> {{ $assignment->max_attempts }}
                                </div>
                                <div class="col-md-6">
                                    <strong>نوع التصحيح:</strong> {{ $assignment::GRADING_TYPES[$assignment->grading_type] ?? $assignment->grading_type }}
                                </div>
                                <div class="col-md-6">
                                    <strong>الحالة:</strong>
                                    @if($assignment->is_published)
                                        <span class="badge bg-success">منشور</span>
                                    @else
                                        <span class="badge bg-secondary">غير منشور</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($assignment->questions->count() > 0)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">أسئلة الواجب ({{ $assignment->questions->count() }})</h5>
                                <ul class="list-group">
                                    @foreach($assignment->questions as $question)
                                        <li class="list-group-item">
                                            <strong>السؤال {{ $loop->iteration }}:</strong> {{ $question->question_text }}
                                            <span class="badge bg-info float-end">{{ $question->points }} نقطة</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="card-title">الإحصائيات</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>إجمالي الإرسالات:</strong> {{ $stats['total_submissions'] }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>تم التصحيح:</strong> {{ $stats['graded_submissions'] }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    <strong>في الانتظار:</strong> {{ $stats['pending_submissions'] }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    <strong>متوسط الدرجات:</strong> {{ $stats['average_score'] }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

