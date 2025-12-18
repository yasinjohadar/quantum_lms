@extends('admin.layouts.master')

@section('page-title')
    عرض السؤال
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
                    <h5 class="page-title fs-21 mb-1">{{ Str::limit($question->title, 50) }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">عرض السؤال</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.questions.edit', $question->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> تعديل
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->
    <div class="row">
        <div class="col-lg-8">
            {{-- محتوى السؤال --}}
            <div class="card custom-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <span class="badge bg-{{ $question->type_color }} me-2">
                            <i class="bi {{ $question->type_icon }} me-1"></i>
                            {{ $question->type_name }}
                        </span>
                        محتوى السؤال
                    </h6>
                    @if($question->is_active)
                        <span class="badge bg-success">نشط</span>
                    @else
                        <span class="badge bg-secondary">غير نشط</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($question->image)
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/'.$question->image) }}" 
                                 class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    @endif
                    
                    <h5 class="mb-3">{{ $question->title }}</h5>
                    
                    @if($question->content)
                        <div class="text-muted mb-3">{!! $question->content !!}</div>
                    @endif
                </div>
            </div>

            {{-- الخيارات / الإجابات --}}
            @if($question->has_options)
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> خيارات الإجابة</h6>
                    </div>
                    <div class="card-body">
                        @if($question->type === 'matching')
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>العنصر</th>
                                        <th>الهدف المطابق</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($question->options as $option)
                                        <tr>
                                            <td>{{ $option->content }}</td>
                                            <td>{{ $option->match_target }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @elseif($question->type === 'ordering')
                            <ol class="list-group list-group-numbered">
                                @foreach($question->options->sortBy('correct_order') as $option)
                                    <li class="list-group-item">{{ $option->content }}</li>
                                @endforeach
                            </ol>
                        @else
                            <div class="list-group">
                                @foreach($question->options as $option)
                                    <div class="list-group-item d-flex align-items-center {{ $option->is_correct ? 'list-group-item-success' : '' }}">
                                        @if($option->is_correct)
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        @else
                                            <i class="bi bi-circle me-2 text-muted"></i>
                                        @endif
                                        <div class="flex-grow-1">
                                            @if($option->image)
                                                <img src="{{ asset('storage/'.$option->image) }}" 
                                                     class="me-2 rounded" style="height: 30px;">
                                            @endif
                                            {{ $option->content }}
                                            @if($option->feedback)
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-chat-dots me-1"></i>
                                                    {{ $option->feedback }}
                                                </small>
                                            @endif
                                        </div>
                                        @if($option->points)
                                            <span class="badge bg-info">{{ $option->points }} درجة</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($question->type === 'numerical')
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-123 me-2"></i> الإجابة الرقمية</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="text-muted">الإجابة الصحيحة:</span>
                                <span class="fs-4 fw-bold text-success ms-2">
                                    {{ $question->options->first()->content ?? 'غير محدد' }}
                                </span>
                            </div>
                            @if($question->tolerance)
                                <div>
                                    <span class="text-muted">نسبة التسامح:</span>
                                    <span class="badge bg-info ms-1">± {{ $question->tolerance }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($question->type === 'fill_blanks')
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-input-cursor me-2"></i> إجابات الفراغات</h6>
                    </div>
                    <div class="card-body">
                        @if($question->blank_answers)
                            <ol>
                                @foreach($question->blank_answers as $answer)
                                    <li class="mb-2">
                                        <span class="badge bg-success">{{ $answer }}</span>
                                    </li>
                                @endforeach
                            </ol>
                            @if($question->case_sensitive)
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    يتطلب مطابقة حالة الأحرف
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- شرح الإجابة --}}
            @if($question->explanation)
                <div class="card custom-card mb-3">
                    <div class="card-header bg-success-transparent">
                        <h6 class="mb-0 text-success">
                            <i class="bi bi-lightbulb me-2"></i> شرح الإجابة
                        </h6>
                    </div>
                    <div class="card-body">
                        {!! nl2br(e($question->explanation)) !!}
                    </div>
                </div>
            @endif

            {{-- الاختبارات المستخدم فيها --}}
            @if($question->quizzes->isNotEmpty())
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-journal-check me-2"></i>
                            الاختبارات المستخدم فيها ({{ $question->quizzes->count() }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>الاختبار</th>
                                        <th>المادة</th>
                                        <th>الدرجة</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($question->quizzes as $quiz)
                                        <tr>
                                            <td>{{ $quiz->title }}</td>
                                            <td>{{ $quiz->subject->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $quiz->pivot->points }} درجة
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                                                   class="btn btn-sm btn-primary-transparent">
                                                    عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- معلومات السؤال --}}
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> معلومات السؤال</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">رقم السؤال:</span>
                            <span class="fw-semibold">#{{ $question->id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">مستوى الصعوبة:</span>
                            <span class="badge bg-{{ $question->difficulty_color }}">
                                {{ $question->difficulty_name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الدرجة الافتراضية:</span>
                            <span class="fw-semibold">{{ $question->default_points }}</span>
                        </li>
                        @if($question->category)
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">التصنيف:</span>
                                <span class="badge bg-secondary">{{ $question->category }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">المنشئ:</span>
                            <span>{{ $question->creator->name ?? 'غير معروف' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">تاريخ الإنشاء:</span>
                            <span>{{ $question->created_at->format('Y/m/d') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- الوحدات المرتبطة --}}
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i> الوحدات المرتبطة</h6>
                </div>
                <div class="card-body">
                    @if($question->units->isEmpty())
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-globe fs-3 d-block mb-2"></i>
                            سؤال عام (غير مرتبط بوحدة)
                        </div>
                    @else
                        @foreach($question->units as $unit)
                            <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                <i class="bi bi-layers me-2 text-primary"></i>
                                <div>
                                    <span class="fw-semibold">{{ $unit->title }}</span>
                                    @if($unit->section && $unit->section->subject)
                                        <small class="text-muted d-block">
                                            {{ $unit->section->subject->name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- الإجراءات --}}
            <div class="card custom-card">
                <div class="card-body">
                    <form action="{{ route('admin.questions.toggle-status', $question->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-{{ $question->is_active ? 'warning' : 'success' }} w-100 mb-2">
                            <i class="bi bi-{{ $question->is_active ? 'pause' : 'play' }}-fill me-1"></i>
                            {{ $question->is_active ? 'إيقاف السؤال' : 'تفعيل السؤال' }}
                        </button>
                    </form>
                    
                    @if($question->quizzes->isEmpty())
                        <button type="button" class="btn btn-danger w-100" 
                                data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> حذف السؤال
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary w-100" disabled title="لا يمكن الحذف - مستخدم في اختبارات">
                            <i class="bi bi-trash me-1"></i> حذف السؤال
                        </button>
                        <small class="text-muted d-block text-center mt-1">
                            لا يمكن الحذف لأن السؤال مستخدم في اختبارات
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- مودال الحذف --}}
@if($question->quizzes->isEmpty())
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="border-0 text-center pt-4 px-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3">
                        <span class="me-2 fs-4 text-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </span>
                        <h5 class="modal-title mb-0 fw-bold">حذف السؤال</h5>
                    </div>
                    <button type="button" class="btn-close position-absolute top-0 start-0 m-3" 
                            data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="text-center mt-2">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" 
                         style="width:80px;height:80px;">
                        <i class="bi bi-trash fs-2"></i>
                    </div>
                </div>
                <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center pt-0 pb-3 px-4">
                        <p class="mb-1 text-muted">هل أنت متأكد من حذف هذا السؤال نهائياً؟</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-outline-secondary px-4 me-2" 
                                data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-trash me-1"></i> حذف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

