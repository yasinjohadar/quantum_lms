@extends('admin.layouts.master')

@section('page-title')
    نتائج الاختبار
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">نتائج: {{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">النتائج</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.quiz-attempts.statistics', $quiz->id) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-graph-up me-1"></i> الإحصائيات
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card custom-card">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-people me-2"></i> جميع المحاولات ({{ $attempts->total() }})</h6>
        </div>
        <div class="card-body p-0">
            @if($attempts->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3">لا توجد محاولات بعد</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الطالب</th>
                                <th>رقم المحاولة</th>
                                <th>الدرجة</th>
                                <th>النسبة</th>
                                <th>الحالة</th>
                                <th>الوقت المستغرق</th>
                                <th>تاريخ البدء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attempts as $attempt)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2 bg-primary-transparent text-primary rounded-circle">
                                                {{ mb_substr($attempt->user->name ?? 'X', 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="fw-semibold">{{ $attempt->user->name ?? 'محذوف' }}</span>
                                                <small class="text-muted d-block">{{ $attempt->user->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $attempt->attempt_number }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $attempt->score }} / {{ $attempt->max_score }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                            {{ round($attempt->percentage, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $attempt->status_color }}">
                                            {{ $attempt->status_name }}
                                        </span>
                                    </td>
                                    <td>{{ $attempt->formatted_time_spent }}</td>
                                    <td>{{ $attempt->started_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.quiz-attempts.show', $attempt->id) }}" 
                                               class="btn btn-icon btn-info-transparent" title="عرض التفاصيل">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($attempt->status === 'under_review')
                                                <a href="{{ route('admin.quiz-attempts.grade', $attempt->id) }}" 
                                                   class="btn btn-icon btn-warning-transparent" title="تصحيح">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-icon btn-danger-transparent" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteAttempt{{ $attempt->id }}"
                                                    title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $attempts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- مودالات الحذف --}}
@foreach($attempts as $attempt)
    <div class="modal fade" id="deleteAttempt{{ $attempt->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="border-0 text-center pt-4 px-4">
                    <h5 class="modal-title mb-0 fw-bold">حذف المحاولة</h5>
                    <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.quiz-attempts.destroy', $attempt->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-3"></i>
                        <p>هل تريد حذف محاولة الطالب <strong>{{ $attempt->user->name ?? 'محذوف' }}</strong>؟</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

