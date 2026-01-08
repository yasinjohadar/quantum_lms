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
                                        <th>عدد الأسئلة المطلوب</th>
                                        <th>عدد الأسئلة المولدة</th>
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
                                            <td>
                                                <span class="badge bg-info">{{ $generation->number_of_questions }}</span>
                                            </td>
                                            <td>
                                                @if($generation->status === 'completed')
                                                    @php
                                                        $rawQuestions = $generation->generated_questions;
                                                        if (is_string($rawQuestions)) {
                                                            $rawQuestions = json_decode($rawQuestions, true);
                                                        }
                                                        $generatedCount = is_array($rawQuestions) ? count($rawQuestions) : 0;
                                                        $requiredCount = $generation->number_of_questions;
                                                    @endphp
                                                    @if($generatedCount > 0)
                                                        @if($generatedCount < $requiredCount)
                                                            <span class="badge bg-warning" title="تم توليد {{ $generatedCount }} من {{ $requiredCount }} المطلوبة">
                                                                {{ $generatedCount }} / {{ $requiredCount }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-success">{{ $generatedCount }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-danger" title="لم يتم توليد أي أسئلة">0</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
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
                                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                    @if($generation->status === 'completed')
                                                        <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="مراجعة الأسئلة المولدة">
                                                            <i class="fas fa-eye me-1"></i> مراجعة
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                title="حفظ جميع الأسئلة"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#saveAllModal{{ $generation->id }}">
                                                            <i class="fas fa-save me-1"></i> حفظ الكل
                                                        </button>
                                                        
                                                        <!-- Modal for Save All -->
                                                        <div class="modal fade" id="saveAllModal{{ $generation->id }}" tabindex="-1" aria-labelledby="saveAllModalLabel{{ $generation->id }}" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header border-0 pb-0">
                                                                        <h5 class="modal-title" id="saveAllModalLabel{{ $generation->id }}">
                                                                            <i class="fas fa-save text-success me-2"></i>
                                                                            تأكيد حفظ الأسئلة
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body text-center py-4">
                                                                        <div class="mb-3">
                                                                            <i class="fas fa-question-circle fa-3x text-warning"></i>
                                                                        </div>
                                                                        <h6 class="mb-2">هل أنت متأكد من حفظ جميع الأسئلة؟</h6>
                                                                        <p class="text-muted mb-0">سيتم حفظ جميع الأسئلة المولدة في قاعدة البيانات</p>
                                                                    </div>
                                                                    <div class="modal-footer border-0 pt-0">
                                                                        <form action="{{ route('admin.ai.question-generations.save', $generation->id) }}" method="POST" class="d-inline">
                                                                            @csrf
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                                <i class="fas fa-times me-1"></i> إلغاء
                                                                            </button>
                                                                            <button type="submit" class="btn btn-success">
                                                                                <i class="fas fa-save me-1"></i> نعم، احفظ الكل
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif($generation->status === 'pending')
                                                        <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" 
                                                           class="btn btn-sm btn-info" 
                                                           title="عرض تفاصيل الطلب">
                                                            <i class="fas fa-eye me-1"></i> عرض
                                                        </a>
                                                        <form action="{{ route('admin.ai.question-generations.process', $generation->id) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-warning" title="بدء معالجة التوليد">
                                                                <i class="fas fa-play me-1"></i> معالجة
                                                            </button>
                                                        </form>
                                                    @elseif($generation->status === 'processing')
                                                        <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" 
                                                           class="btn btn-sm btn-info" 
                                                           title="عرض حالة المعالجة">
                                                            <i class="fas fa-spinner fa-spin me-1"></i> جاري المعالجة
                                                        </a>
                                                    @elseif($generation->status === 'failed')
                                                        <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" 
                                                           class="btn btn-sm btn-danger" 
                                                           title="عرض تفاصيل الخطأ">
                                                            <i class="fas fa-exclamation-triangle me-1"></i> عرض الخطأ
                                                        </a>
                                                        <form action="{{ route('admin.ai.question-generations.regenerate', $generation->id) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary" title="إعادة التوليد">
                                                                <i class="fas fa-redo me-1"></i> إعادة
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" 
                                                           class="btn btn-sm btn-secondary" 
                                                           title="عرض تفاصيل الطلب">
                                                            <i class="fas fa-eye me-1"></i> عرض
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">لا توجد طلبات.</td>
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

