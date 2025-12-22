@extends('admin.layouts.master')

@section('page-title')
    حلول AI للأسئلة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">حلول AI للأسئلة</h5>
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
                                        <th>السؤال</th>
                                        <th>الموديل</th>
                                        <th>درجة الثقة</th>
                                        <th>الحالة</th>
                                        <th>المتحقق</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($solutions as $solution)
                                        <tr>
                                            <td>{{ $solution->id }}</td>
                                            <td>{{ Str::limit($solution->question->title ?? $solution->question->content ?? 'سؤال #' . $solution->question_id, 50) }}</td>
                                            <td>{{ $solution->model->name ?? '-' }}</td>
                                            <td>
                                                @if($solution->confidence_score)
                                                    <span class="badge bg-{{ $solution->confidence_score >= 0.8 ? 'success' : ($solution->confidence_score >= 0.5 ? 'warning' : 'danger') }}">
                                                        {{ number_format($solution->confidence_score * 100, 1) }}%
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($solution->is_verified)
                                                    <span class="badge bg-success">تم التحقق</span>
                                                @else
                                                    <span class="badge bg-warning">غير محقق</span>
                                                @endif
                                            </td>
                                            <td>{{ $solution->verifier->name ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('admin.ai.question-solutions.show', $solution->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!$solution->is_verified)
                                                        <form action="{{ route('admin.ai.question-solutions.verify', $solution->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">لا توجد حلول.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $solutions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

