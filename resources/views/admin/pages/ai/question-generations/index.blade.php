@extends('admin.layouts.master')

@section('page-title')
    طلبات توليد الأسئلة
@stop

@push('styles')
    @include('admin.pages.ai.question-generations.partials.question-generations-index-styles')
@endpush

@section('content')
    <div class="main-content app-content ai-gen-index-page">
        <div class="container-fluid">

            <div class="ai-gen-index-hero my-4">
                <div class="ai-gen-index-hero__icon">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="ai-gen-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">طلبات توليد الأسئلة</li>
                        </ol>
                    </nav>
                    <h4 class="ai-gen-index-hero__title">طلبات توليد الأسئلة</h4>
                    <p class="ai-gen-index-hero__subtitle">متابعة طلبات الذكاء الاصطناعي ومراجعة الأسئلة المولدة</p>
                </div>
                <div class="ai-gen-index-stat-mini">
                    <span class="ai-gen-index-stat-mini__value">{{ number_format($generations->total()) }}</span>
                    <span class="ai-gen-index-stat-mini__label">طلب إجمالي</span>
                </div>
                <div class="ai-gen-index-hero__actions">
                    <a href="{{ route('admin.ai.question-generations.create-from-image') }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-image me-1"></i> من صورة
                    </a>
                    <a href="{{ route('admin.ai.question-generations.create-advanced') }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-stars me-1"></i> توليد متقدم
                    </a>
                    <a href="{{ route('admin.ai.question-generations.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> توليد جديد
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="ai-gen-index-card ai-gen-index-card--flush">
                <div class="ai-gen-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ai-gen-index-card__header-icon"><i class="bi bi-list-ul"></i></span>
                        <span>سجل الطلبات</span>
                    </div>
                    <span class="text-muted small fw-normal">{{ $generations->firstItem() ?? 0 }}–{{ $generations->lastItem() ?? 0 }} من {{ number_format($generations->total()) }}</span>
                </div>
                <div class="ai-gen-index-card__body">
                    <div class="ai-gen-index-table-wrap">
                        <table class="table ai-gen-index-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th class="ai-gen-col-user">المستخدم</th>
                                    <th>المصدر</th>
                                    <th class="ai-gen-col-types">نوع السؤال</th>
                                    <th>مطلوب</th>
                                    <th>مولّد</th>
                                    <th>الحالة</th>
                                    <th class="ai-gen-col-model">الموديل</th>
                                    <th class="ai-gen-col-cost">التكلفة</th>
                                    <th style="width: 120px;">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($generations as $generation)
                                    <tr>
                                        <td class="text-muted">{{ $generation->id }}</td>
                                        <td class="ai-gen-col-user">
                                            <span class="ai-gen-user-cell">{{ $generation->user->name }}</span>
                                        </td>
                                        <td>
                                            <span class="ai-gen-source-cell__type">
                                                {{ \App\Models\AIQuestionGeneration::SOURCE_TYPES[$generation->source_type] ?? $generation->source_type }}
                                            </span>
                                            @if($generation->subject)
                                                <span class="ai-gen-source-cell__meta" title="{{ $generation->subject->name }}">{{ $generation->subject->name }}</span>
                                            @endif
                                        </td>
                                        <td class="ai-gen-col-types">
                                            @php
                                                $selectedTypes = $generation->getSelectedQuestionTypes();
                                            @endphp
                                            @if(!empty($selectedTypes) && count($selectedTypes) > 0)
                                                @foreach($selectedTypes as $type)
                                                    <span class="ai-gen-type-pill">
                                                        {{ \App\Models\Question::TYPES[$type] ?? $type }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="ai-gen-type-pill">
                                                    {{ \App\Models\AIQuestionGeneration::QUESTION_TYPES[$generation->question_type] ?? $generation->question_type }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="ai-gen-count ai-gen-count--requested">{{ $generation->number_of_questions }}</span>
                                        </td>
                                        <td>
                                            @if($generation->status === 'completed')
                                                @php
                                                    $generatedCount = $generation->getResolvedGeneratedQuestionsCount();
                                                    $requiredCount = $generation->number_of_questions;
                                                @endphp
                                                @if($generatedCount > 0)
                                                    @if($generatedCount < $requiredCount)
                                                        <span class="ai-gen-count ai-gen-count--partial" title="تم توليد {{ $generatedCount }} من {{ $requiredCount }}">
                                                            {{ $generatedCount }}/{{ $requiredCount }}
                                                        </span>
                                                    @else
                                                        <span class="ai-gen-count ai-gen-count--done">{{ $generatedCount }}</span>
                                                    @endif
                                                @else
                                                    <span class="ai-gen-count ai-gen-count--empty" title="لم يتم توليد أي أسئلة">0</span>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($generation->status === 'completed')
                                                <span class="ai-gen-status ai-gen-status--completed"><i class="bi bi-check-circle"></i> مكتمل</span>
                                            @elseif($generation->status === 'processing')
                                                <span class="ai-gen-status ai-gen-status--processing"><i class="bi bi-hourglass-split"></i> قيد المعالجة</span>
                                            @elseif($generation->status === 'failed')
                                                <span class="ai-gen-status ai-gen-status--failed"><i class="bi bi-x-circle"></i> فشل</span>
                                            @else
                                                <span class="ai-gen-status ai-gen-status--pending"><i class="bi bi-clock"></i> معلق</span>
                                            @endif
                                        </td>
                                        <td class="ai-gen-col-model">
                                            <span class="ai-gen-model-cell" title="{{ $generation->model->name ?? '-' }}">
                                                {{ $generation->model->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="ai-gen-col-cost">
                                            <span class="ai-gen-cost-cell">
                                                {{ $generation->cost ? number_format($generation->cost, 6) : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="row-action-bar">
                                                @if($generation->status === 'completed')
                                                    <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}"
                                                       class="row-action-btn row-action-btn--primary"
                                                       title="مراجعة الأسئلة المولدة">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if(!$generation->hasSavedQuestions())
                                                        <button type="button"
                                                                class="row-action-btn row-action-btn--success"
                                                                title="حفظ جميع الأسئلة"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#saveAllModal{{ $generation->id }}">
                                                            <i class="bi bi-save"></i>
                                                        </button>
                                                        <div class="modal fade" id="saveAllModal{{ $generation->id }}" tabindex="-1" aria-labelledby="saveAllModalLabel{{ $generation->id }}" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header border-0 pb-0">
                                                                        <h5 class="modal-title" id="saveAllModalLabel{{ $generation->id }}">
                                                                            <i class="bi bi-save text-success me-2"></i>
                                                                            تأكيد حفظ الأسئلة
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                                    </div>
                                                                    <div class="modal-body text-center py-4">
                                                                        <div class="mb-3">
                                                                            <i class="bi bi-question-circle fs-1 text-warning"></i>
                                                                        </div>
                                                                        <h6 class="mb-2">هل أنت متأكد من حفظ جميع الأسئلة؟</h6>
                                                                        <p class="text-muted mb-2">يُفضّل معاينة الأسئلة من زر المراجعة قبل الحفظ.</p>
                                                                        <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}" class="btn btn-sm btn-outline-primary">
                                                                            <i class="bi bi-eye me-1"></i> مراجعة أولاً
                                                                        </a>
                                                                    </div>
                                                                    <div class="modal-footer border-0 pt-0">
                                                                        <form action="{{ route('admin.ai.question-generations.save', $generation->id) }}" method="POST" class="d-inline">
                                                                            @csrf
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                                <i class="bi bi-x-lg me-1"></i> إلغاء
                                                                            </button>
                                                                            <button type="submit" class="btn btn-success">
                                                                                <i class="bi bi-save me-1"></i> نعم، احفظ الكل
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="ai-gen-saved-badge"><i class="bi bi-check-lg"></i> محفوظ</span>
                                                    @endif
                                                @elseif($generation->status === 'pending')
                                                    <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}"
                                                       class="row-action-btn row-action-btn--info"
                                                       title="عرض تفاصيل الطلب">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <form action="{{ route('admin.ai.question-generations.process', $generation->id) }}"
                                                          method="POST"
                                                          class="row-action-form">
                                                        @csrf
                                                        <button type="submit" class="row-action-btn row-action-btn--warning" title="بدء المعالجة">
                                                            <i class="bi bi-play-fill"></i>
                                                        </button>
                                                    </form>
                                                @elseif($generation->status === 'processing')
                                                    <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}"
                                                       class="row-action-btn row-action-btn--info"
                                                       title="عرض حالة المعالجة">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </a>
                                                @elseif($generation->status === 'failed')
                                                    <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}"
                                                       class="row-action-btn row-action-btn--danger"
                                                       title="عرض تفاصيل الخطأ">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </a>
                                                    <form action="{{ route('admin.ai.question-generations.regenerate', $generation->id) }}"
                                                          method="POST"
                                                          class="row-action-form">
                                                        @csrf
                                                        <button type="submit" class="row-action-btn row-action-btn--primary" title="إعادة التوليد">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('admin.ai.question-generations.show', $generation->id) }}"
                                                       class="row-action-btn row-action-btn--secondary"
                                                       title="عرض تفاصيل الطلب">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10">
                                            <div class="ai-gen-index-empty">
                                                <i class="bi bi-robot"></i>
                                                <h5 class="mb-2">لا توجد طلبات</h5>
                                                <p class="mb-3">ابدأ بتوليد أسئلة جديدة بالذكاء الاصطناعي</p>
                                                <a href="{{ route('admin.ai.question-generations.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-plus-circle me-1"></i> توليد أسئلة جديدة
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($generations->hasPages())
                        <div class="ai-gen-index-pagination">
                            {{ $generations->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop
