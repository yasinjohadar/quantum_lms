@extends('admin.layouts.master')

@section('page-title')
    إدارة أسئلة الاختبار
@stop

@section('css')
<style>
    .question-item {
        cursor: grab;
        transition: all 0.2s ease;
    }
    .question-item:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
    }
    .question-item.dragging {
        opacity: 0.5;
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة أسئلة: {{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">إدارة الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary fs-6 d-flex align-items-center">
                        {{ $quiz->questions->count() }} سؤال | {{ $quiz->total_points }} درجة
                    </span>
                </div>
            </div>
            <!-- Page Header Close -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- أسئلة الاختبار الحالية --}}
        <div class="col-lg-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ol me-2"></i>
                        أسئلة الاختبار ({{ $quiz->questions->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($quiz->questions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">لم يتم إضافة أسئلة بعد</p>
                            <p class="text-muted small">اختر أسئلة من بنك الأسئلة على اليسار</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush" id="quizQuestions">
                            @foreach($quiz->questions as $index => $question)
                                <div class="list-group-item question-item" data-id="{{ $question->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-start flex-grow-1">
                                            <i class="bi bi-grip-vertical text-muted me-2 mt-1" style="cursor: grab;"></i>
                                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                            <div class="flex-grow-1">
                                                <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }} mb-1" style="font-size: 0.65rem;">
                                                    <i class="bi {{ $question->type_icon }}"></i>
                                                    {{ $question->type_name }}
                                                </span>
                                                <p class="mb-1 small">{{ Str::limit($question->title, 80) }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <form action="{{ route('admin.quizzes.update-question-points', [$quiz->id, $question->id]) }}" 
                                                          method="POST" class="d-inline-flex align-items-center">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="number" name="points" value="{{ $question->pivot->points }}" 
                                                               class="form-control form-control-sm" style="width: 60px;" step="0.5" min="0">
                                                        <button type="submit" class="btn btn-sm btn-primary-transparent ms-1" title="حفظ">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    </form>
                                                    <span class="text-muted small">درجة</span>
                                                </div>
                                            </div>
                                        </div>
                                        <form action="{{ route('admin.quizzes.remove-question', [$quiz->id, $question->id]) }}" 
                                              method="POST" class="ms-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-danger-transparent" 
                                                    title="إزالة من الاختبار"
                                                    onclick="return confirm('هل تريد إزالة هذا السؤال؟')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- بنك الأسئلة المتاحة --}}
        <div class="col-lg-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-database me-2"></i>
                        بنك الأسئلة المتاحة
                    </h6>
                </div>
                <div class="card-body">
                    {{-- فلتر --}}
                    <form action="{{ route('admin.quizzes.questions', $quiz->id) }}" method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       placeholder="بحث..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">كل الأنواع</option>
                                    @foreach(\App\Models\Question::TYPES as $key => $value)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="difficulty" class="form-select form-select-sm">
                                    <option value="">كل المستويات</option>
                                    @foreach(\App\Models\Question::DIFFICULTIES as $key => $value)
                                        <option value="{{ $key }}" {{ request('difficulty') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($availableQuestions->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-search display-6 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد أسئلة متاحة</p>
                            <a href="{{ route('admin.questions.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> إنشاء سؤال جديد
                            </a>
                        </div>
                    @else
                        <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                            @foreach($availableQuestions as $question)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}" style="font-size: 0.65rem;">
                                                    <i class="bi {{ $question->type_icon }}"></i>
                                                    {{ $question->type_name }}
                                                </span>
                                                <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}" style="font-size: 0.65rem;">
                                                    {{ $question->difficulty_name }}
                                                </span>
                                            </div>
                                            <p class="mb-1 small">{{ Str::limit($question->title, 60) }}</p>
                                            <small class="text-muted">{{ $question->default_points }} درجة</small>
                                        </div>
                                        <form action="{{ route('admin.quizzes.add-question', $quiz->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="question_id" value="{{ $question->id }}">
                                            <input type="hidden" name="points" value="{{ $question->default_points }}">
                                            <button type="submit" class="btn btn-sm btn-success-transparent" title="إضافة للاختبار">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            {{ $availableQuestions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- أزرار التحكم --}}
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">إجمالي الأسئلة:</span>
                        <span class="fw-bold">{{ $quiz->questions->count() }}</span>
                        <span class="mx-2">|</span>
                        <span class="text-muted">إجمالي الدرجات:</span>
                        <span class="fw-bold">{{ $quiz->total_points }}</span>
                    </div>
                    <div class="btn-list">
                        <a href="{{ route('admin.questions.create') }}" class="btn btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i> إنشاء سؤال جديد
                        </a>
                        <a href="{{ route('admin.quizzes.show', $quiz->id) }}" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> انتهيت
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quizQuestions = document.getElementById('quizQuestions');
    
    if (quizQuestions) {
        new Sortable(quizQuestions, {
            animation: 150,
            handle: '.bi-grip-vertical',
            ghostClass: 'dragging',
            onEnd: function(evt) {
                const items = quizQuestions.querySelectorAll('.question-item');
                const order = Array.from(items).map(item => item.dataset.id);
                
                fetch('{{ route("admin.quizzes.reorder-questions", $quiz->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث الأرقام
                        items.forEach((item, index) => {
                            item.querySelector('.badge.bg-secondary').textContent = index + 1;
                        });
                    }
                });
            }
        });
    }
});
</script>
@stop

