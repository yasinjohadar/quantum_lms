@extends('admin.layouts.master')

@section('page-title')
    بنك الأسئلة
@stop

@section('css')
<style>
    .question-card {
        transition: all 0.3s ease;
        border-right: 4px solid transparent;
    }
    .question-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .question-type-single_choice { border-right-color: var(--primary-color) !important; }
    .question-type-multiple_choice { border-right-color: #17a2b8 !important; }
    .question-type-true_false { border-right-color: #28a745 !important; }
    .question-type-short_answer { border-right-color: #ffc107 !important; }
    .question-type-essay { border-right-color: #6c757d !important; }
    .question-type-matching { border-right-color: #dc3545 !important; }
    .question-type-ordering { border-right-color: #343a40 !important; }
    .question-type-fill_blanks { border-right-color: #007bff !important; }
    .question-type-numerical { border-right-color: #17a2b8 !important; }
    .question-type-drag_drop { border-right-color: #fd7e14 !important; }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">بنك الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">بنك الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.questions.import.show') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-upload me-1"></i> استيراد أسئلة
                    </a>
                    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة سؤال جديد
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->

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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            {{-- فلاتر البحث --}}
            <div class="card custom-card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.questions.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="ابحث بعنوان السؤال..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">النوع</label>
                                <select name="type" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="single_choice" {{ request('type') == 'single_choice' ? 'selected' : '' }}>اختيار واحد</option>
                                    <option value="multiple_choice" {{ request('type') == 'multiple_choice' ? 'selected' : '' }}>اختيار متعدد</option>
                                    <option value="true_false" {{ request('type') == 'true_false' ? 'selected' : '' }}>صح/خطأ</option>
                                    <option value="short_answer" {{ request('type') == 'short_answer' ? 'selected' : '' }}>إجابة قصيرة</option>
                                    <option value="essay" {{ request('type') == 'essay' ? 'selected' : '' }}>مقالي</option>
                                    <option value="matching" {{ request('type') == 'matching' ? 'selected' : '' }}>مطابقة</option>
                                    <option value="ordering" {{ request('type') == 'ordering' ? 'selected' : '' }}>ترتيب</option>
                                    <option value="fill_blanks" {{ request('type') == 'fill_blanks' ? 'selected' : '' }}>ملء الفراغات</option>
                                    <option value="numerical" {{ request('type') == 'numerical' ? 'selected' : '' }}>رقمي</option>
                                    <option value="drag_drop" {{ request('type') == 'drag_drop' ? 'selected' : '' }}>سحب وإفلات</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الصعوبة</label>
                                <select name="difficulty" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">ترتيب</label>
                                <select name="sort" class="form-select">
                                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>الأقدم</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>العنوان</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- أدوات إضافية --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('admin.questions.index', ['filter' => 'orphan']) }}" class="btn btn-sm {{ request('filter') == 'orphan' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="bi bi-exclamation-circle me-1"></i> أسئلة غير مرتبطة
                </a>
                <a href="{{ route('admin.questions.index', ['with_deleted' => '1']) }}" class="btn btn-sm {{ request('with_deleted') == '1' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-trash me-1"></i> سلة المحذوفات
                </a>
                <span class="badge bg-info-transparent text-info d-flex align-items-center">
                    <i class="bi bi-info-circle me-1"></i>
                    إجمالي الأسئلة: {{ $questions->total() }}
                </span>
            </div>

            {{-- قائمة الأسئلة --}}
            <div class="row">
                @forelse($questions as $question)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card custom-card question-card question-type-{{ $question->type }} h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}">
                                        {{ $question->type_label }}
                                    </span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('admin.questions.show', $question->id) }}"><i class="bi bi-eye me-2"></i>عرض</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.questions.edit', $question->id) }}"><i class="bi bi-pencil me-2"></i>تعديل</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteQuestion{{ $question->id }}">
                                                    <i class="bi bi-trash me-2"></i>حذف
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <h6 class="fw-semibold mb-2">
                                    <a href="{{ route('admin.questions.show', $question->id) }}" class="text-decoration-none">
                                        {{ Str::limit($question->title, 60) }}
                                    </a>
                                </h6>

                                @if($question->content)
                                    <p class="text-muted small mb-2">{!! Str::limit(strip_tags($question->content), 80) !!}</p>
                                @endif

                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-star me-1"></i>{{ $question->default_points }} نقطة
                                    </span>
                                    <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}">
                                        {{ $question->difficulty_label }}
                                    </span>
                                    @if(!$question->is_active)
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </div>

                                @if($question->units->isNotEmpty())
                                    <div class="border-top pt-2 mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-folder me-1"></i>
                                            {{ $question->units->take(2)->pluck('title')->join('، ') }}
                                            @if($question->units->count() > 2)
                                                <span class="badge bg-light text-dark">+{{ $question->units->count() - 2 }}</span>
                                            @endif
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- مودال الحذف --}}
                    <div class="modal fade" id="deleteQuestion{{ $question->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <div class="border-0 text-center pt-4 px-4">
                                    <div class="d-inline-flex align-items-center justify-content-center mb-3">
                                        <span class="me-2 fs-4 text-warning">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                        </span>
                                        <h5 class="modal-title mb-0 fw-bold">حذف السؤال</h5>
                                    </div>
                                    <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="text-center mt-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" style="width:80px;height:80px;">
                                        <i class="bi bi-trash fs-2"></i>
                                    </div>
                                </div>
                                <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-body text-center pt-0 pb-3 px-4">
                                        <p class="mb-1 text-muted">هل أنت متأكد من حذف السؤال:</p>
                                        <p class="fw-bold mb-1">{{ Str::limit($question->title, 50) }}</p>
                                    </div>
                                    <div class="modal-footer border-0 justify-content-center pb-4">
                                        <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bi bi-trash me-1"></i> حذف
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-3">لا توجد أسئلة حالياً</p>
                                <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> إضافة أول سؤال
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- الترقيم --}}
            @if($questions->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $questions->links() }}
                </div>
            @endif

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop