@extends('student.layouts.master')

@section('page-title')
    الواجبات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الواجبات</h5>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                @forelse($assignments as $assignment)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ $assignment->title }}</h5>
                                <p class="card-text text-muted small">{{ Str::limit($assignment->description, 100) }}</p>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-book me-1"></i>
                                        @if($assignment->assignable_type === 'App\Models\Subject')
                                            {{ $assignment->assignable->name ?? 'N/A' }}
                                        @elseif($assignment->assignable_type === 'App\Models\Unit')
                                            {{ $assignment->assignable->title ?? 'N/A' }}
                                        @else
                                            {{ $assignment->assignable->title ?? 'N/A' }}
                                        @endif
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-star me-1"></i> الدرجة الكاملة: {{ $assignment->max_score }}
                                    </small>
                                </div>

                                @if($assignment->due_date)
                                    <div class="mb-3">
                                        <small class="{{ $assignment->isOverdue() ? 'text-danger' : 'text-muted' }}">
                                            <i class="fas fa-clock me-1"></i>
                                            موعد التسليم: {{ $assignment->due_date->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                @endif

                                @php
                                    $submission = $assignment->submissions->first();
                                @endphp

                                @if($submission)
                                    <div class="mb-3">
                                        <span class="badge bg-{{ $submission->status == 'graded' ? 'success' : ($submission->status == 'submitted' ? 'warning' : 'info') }}">
                                            {{ $submission->getStatusLabel() }}
                                        </span>
                                        @if($submission->total_score !== null)
                                            <span class="badge bg-primary">
                                                {{ $submission->total_score }} / {{ $assignment->max_score }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i> عرض الواجب
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            لا توجد واجبات متاحة حالياً
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
@stop

