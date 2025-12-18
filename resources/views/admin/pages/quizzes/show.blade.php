@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الاختبار
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
                    <h5 class="page-title fs-21 mb-1">{{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $quiz->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-list-check me-1"></i> إدارة الأسئلة
                    </a>
                    <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> تعديل
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- معلومات الاختبار --}}
            <div class="card custom-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> معلومات الاختبار</h6>
                    <div>
                        @if($quiz->is_published)
                            <span class="badge bg-success">منشور</span>
                        @else
                            <span class="badge bg-secondary">مسودة</span>
                        @endif
                        @if($quiz->is_active)
                            <span class="badge bg-primary">نشط</span>
                        @else
                            <span class="badge bg-warning">معطل</span>
                        @endif
                        <span class="badge bg-{{ $quiz->availability_status_color }}">
                            {{ $quiz->availability_status_name }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($quiz->image)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/'.$quiz->image) }}" 
                                 class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    @endif
                    
                    @if($quiz->description)
                        <p class="text-muted">{{ $quiz->description }}</p>
                    @endif

                    @if($quiz->instructions)
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-1"></i> تعليمات الاختبار:</h6>
                            {{ $quiz->instructions }}
                        </div>
                    @endif

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">المادة:</span>
                                    <span class="fw-semibold">{{ $quiz->subject->name ?? '-' }}</span>
                                </li>
                                @if($quiz->unit)
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">الوحدة:</span>
                                        <span class="fw-semibold">{{ $quiz->unit->title }}</span>
                                    </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">المدة:</span>
                                    <span class="fw-semibold">{{ $quiz->formatted_duration }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">نسبة النجاح:</span>
                                    <span class="fw-semibold">{{ $quiz->pass_percentage }}%</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">عدد الأسئلة:</span>
                                    <span class="badge bg-primary">{{ $quiz->questions_count }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">إجمالي الدرجات:</span>
                                    <span class="fw-semibold">{{ $quiz->total_points }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">المحاولات:</span>
                                    <span class="fw-semibold">
                                        {{ $quiz->max_attempts > 0 ? $quiz->max_attempts : 'غير محدود' }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">طريقة التقييم:</span>
                                    <span class="fw-semibold">{{ $quiz->grading_method_name }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if($quiz->available_from || $quiz->available_to)
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6><i class="bi bi-calendar me-1"></i> الجدول الزمني:</h6>
                            <div class="row">
                                @if($quiz->available_from)
                                    <div class="col-md-6">
                                        <small class="text-muted">يبدأ من:</small>
                                        <div>{{ $quiz->available_from->format('Y/m/d h:i A') }}</div>
                                    </div>
                                @endif
                                @if($quiz->available_to)
                                    <div class="col-md-6">
                                        <small class="text-muted">ينتهي في:</small>
                                        <div>{{ $quiz->available_to->format('Y/m/d h:i A') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- الأسئلة --}}
            <div class="card custom-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> أسئلة الاختبار</h6>
                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-gear me-1"></i> إدارة الأسئلة
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($quiz->questions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-question-circle display-4 text-muted"></i>
                            <p class="text-muted mt-3">لم يتم إضافة أسئلة بعد</p>
                            <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> إضافة أسئلة
                            </a>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($quiz->questions as $index => $question)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-start">
                                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                            <div>
                                                <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }} mb-1">
                                                    <i class="bi {{ $question->type_icon }} me-1"></i>
                                                    {{ $question->type_name }}
                                                </span>
                                                <p class="mb-0">{{ Str::limit($question->title, 100) }}</p>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary">{{ $question->pivot->points }} درجة</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- آخر المحاولات --}}
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i> آخر المحاولات</h6>
                    <a href="{{ route('admin.quizzes.results', $quiz->id) }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($quiz->attempts->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            لا توجد محاولات بعد
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>الدرجة</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quiz->attempts as $attempt)
                                        <tr>
                                            <td>{{ $attempt->user->name ?? 'محذوف' }}</td>
                                            <td>
                                                <span class="fw-semibold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                                    {{ $attempt->percentage }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $attempt->status_color }}">
                                                    {{ $attempt->status_name }}
                                                </span>
                                            </td>
                                            <td>{{ $attempt->started_at->format('Y/m/d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.quiz-attempts.show', $attempt->id) }}" 
                                                   class="btn btn-sm btn-info-transparent">
                                                    عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- إحصائيات --}}
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i> إحصائيات</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-primary-transparent rounded text-center">
                                <h3 class="mb-0 text-primary">{{ $stats['total_attempts'] }}</h3>
                                <small class="text-muted">إجمالي المحاولات</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-success-transparent rounded text-center">
                                <h3 class="mb-0 text-success">{{ $stats['passed_count'] }}</h3>
                                <small class="text-muted">ناجحون</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-danger-transparent rounded text-center">
                                <h3 class="mb-0 text-danger">{{ $stats['failed_count'] }}</h3>
                                <small class="text-muted">راسبون</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-info-transparent rounded text-center">
                                <h3 class="mb-0 text-info">{{ round($stats['average_score']) }}%</h3>
                                <small class="text-muted">متوسط الدرجات</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($stats['total_attempts'] > 0)
                        <hr>
                        <div class="d-flex justify-content-between small">
                            <span>أعلى درجة:</span>
                            <span class="text-success fw-semibold">{{ $stats['highest_score'] }}%</span>
                        </div>
                        <div class="d-flex justify-content-between small mt-1">
                            <span>أدنى درجة:</span>
                            <span class="text-danger fw-semibold">{{ $stats['lowest_score'] }}%</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- إعدادات سريعة --}}
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i> الإعدادات</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-2">
                            <i class="bi bi-{{ $quiz->shuffle_questions ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            خلط الأسئلة
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bi bi-{{ $quiz->shuffle_options ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            خلط الخيارات
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bi bi-{{ $quiz->show_timer ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            إظهار المؤقت
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bi bi-{{ $quiz->show_result_immediately ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            إظهار النتيجة فوراً
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bi bi-{{ $quiz->show_correct_answers ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            إظهار الإجابات الصحيحة
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bi bi-{{ $quiz->requires_password ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            يتطلب كلمة مرور
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-{{ $quiz->prevent_copy_paste ? 'check-circle text-success' : 'x-circle text-muted' }} me-2"></i>
                            منع النسخ واللصق
                        </li>
                    </ul>
                </div>
            </div>

            {{-- إجراءات --}}
            <div class="card custom-card">
                <div class="card-body">
                    <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-eye me-1"></i> معاينة الاختبار
                    </a>
                    <a href="{{ route('admin.quizzes.results', $quiz->id) }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="bi bi-bar-chart me-1"></i> النتائج والتقارير
                    </a>
                    <form action="{{ route('admin.quizzes.duplicate', $quiz->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                            <i class="bi bi-copy me-1"></i> نسخ الاختبار
                        </button>
                    </form>
                    @if($quiz->attempts()->count() == 0)
                        <button type="button" class="btn btn-outline-danger w-100" 
                                data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> حذف الاختبار
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- مودال الحذف --}}
@if($quiz->attempts()->count() == 0)
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="border-0 text-center pt-4 px-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3">
                        <span class="me-2 fs-4 text-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </span>
                        <h5 class="modal-title mb-0 fw-bold">حذف الاختبار</h5>
                    </div>
                    <button type="button" class="btn-close position-absolute top-0 start-0 m-3" 
                            data-bs-dismiss="modal"></button>
                </div>
                <div class="text-center mt-2">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" 
                         style="width:80px;height:80px;">
                        <i class="bi bi-trash fs-2"></i>
                    </div>
                </div>
                <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center pt-0 pb-3 px-4">
                        <p class="mb-1 text-muted">هل أنت متأكد من حذف الاختبار:</p>
                        <p class="fw-bold mb-1">{{ $quiz->title }}</p>
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

