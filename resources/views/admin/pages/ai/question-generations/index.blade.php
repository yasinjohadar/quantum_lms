@extends('admin.layouts.master')

@section('page-title')
    طلبات توليد الأسئلة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">طلبات توليد الأسئلة</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.question-generations.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> توليد أسئلة جديدة
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>المستخدم</th>
                                        <th>المصدر</th>
                                        <th>نوع السؤال</th>
                                        <th>عدد الأسئلة</th>
                                        <th>الحالة</th>
                                        <th>الموديل</th>
                                        <th>التكلفة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($generations as $generation)
                                        <tr>
                                            <td>{{ $generation->id }}</td>
                                            <td>{{ $generation->user->name }}</td>
                                            <td>
                                                {{ \App\Models\AIQuestionGeneration::SOURCE_TYPES[$generation->source_type] ?? $generation->source_type }}
                                                @if($generation->subject)
                                                    <br><small class="text-muted">{{ $generation->subject->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ \App\Models\AIQuestionGeneration::QUESTION_TYPES[$generation->question_type] ?? $generation->question_type }}
                                                </span>
                                            </td>
                                            <td>{{ $generation->number_of_questions }}</td>
                                            <td>
                                                @if($generation->status === 'completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                @elseif($generation->status === 'processing')
                                                    <span class="badge bg-warning">قيد المعالجة</span>
                                                @elseif($generation->status === 'failed')
                                                    <span class="badge bg-danger">فشل</span>
                                                @else
                                                    <span class="badge bg-secondary">معلق</span>
                                                @endif
                                            </td>
                                            <td>{{ $generation->model->name ?? '-' }}</td>
                                            <td>{{ $generation->cost ? number_format($generation->cost, 6) : '-' }}</td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($generation->status === 'pending')
                                                        <form action="{{ route('admin.ai.question-generations.process', $generation->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($generation->status === 'completed')
                                                        <form action="{{ route('admin.ai.question-generations.save', $generation->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">لا توجد طلبات.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $generations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

