@extends('admin.layouts.master')

@section('page-title')
    إرسالات الواجب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إرسالات الواجب: {{ $assignment->title }}</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة الإرسالات</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>الطالب</th>
                                        <th>المحاولة</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>الحالة</th>
                                        <th>الدرجة</th>
                                        <th>العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($submissions as $submission)
                                        <tr>
                                            <td>{{ $submission->id }}</td>
                                            <td>{{ $submission->student->name }}</td>
                                            <td>{{ $submission->attempt_number }}</td>
                                            <td>{{ $submission->submitted_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $submission->status == 'graded' ? 'success' : ($submission->status == 'submitted' ? 'warning' : 'info') }}">
                                                    {{ $submission->getStatusLabel() }}
                                                </span>
                                                @if($submission->is_late)
                                                    <span class="badge bg-danger">متأخر</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($submission->total_score !== null)
                                                    {{ $submission->total_score }} / {{ $assignment->max_score }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.assignments.submissions.show', [$assignment, $submission]) }}" 
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <p class="text-muted mb-0">لا توجد إرسالات</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $submissions->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

