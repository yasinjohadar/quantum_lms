@extends('admin.layouts.master')

@section('page-title')
    الاختبارات
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
                    <h5 class="page-title fs-21 mb-1">إدارة الاختبارات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الاختبارات</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-clipboard-check me-1"></i> بحاجة للتصحيح
                    </a>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إنشاء اختبار جديد
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
                    <form action="{{ route('admin.quizzes.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="ابحث بعنوان الاختبار..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">المادة</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">كل المواد</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                            @if($subject->schoolClass)
                                                ({{ $subject->schoolClass->name }})
                                            @endif
                                        </option>
                                    @endforeach
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
                                <label class="form-label">النشر</label>
                                <select name="is_published" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>مسودة</option>
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

            {{-- قائمة الاختبارات --}}
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        الاختبارات ({{ $quizzes->total() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($quizzes->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x display-4 text-muted"></i>
                            <p class="text-muted mt-3">لا توجد اختبارات حالياً</p>
                            <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> إنشاء أول اختبار
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th>الاختبار</th>
                                        <th style="width: 150px">المادة</th>
                                        <th style="width: 100px">الأسئلة</th>
                                        <th style="width: 100px">المحاولات</th>
                                        <th style="width: 100px">المدة</th>
                                        <th style="width: 100px">الحالة</th>
                                        <th style="width: 180px">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizzes as $quiz)
                                        <tr>
                                            <td>{{ $quiz->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($quiz->image)
                                                        <img src="{{ asset('storage/'.$quiz->image) }}" 
                                                             class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary-transparent text-primary rounded d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="bi bi-journal-check"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                                                           class="fw-semibold text-decoration-none">
                                                            {{ $quiz->title }}
                                                        </a>
                                                        @if($quiz->unit)
                                                            <small class="text-muted d-block">{{ $quiz->unit->title }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $quiz->subject->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $quiz->questions_count }} سؤال</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $quiz->attempts_count }} محاولة</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $quiz->formatted_duration }}</span>
                                            </td>
                                            <td>
                                                @if($quiz->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-secondary">مسودة</span>
                                                @endif
                                                @if(!$quiz->is_active)
                                                    <span class="badge bg-warning">معطل</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                                                       class="btn btn-icon btn-info-transparent" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" 
                                                       class="btn btn-icon btn-success-transparent" title="الأسئلة">
                                                        <i class="bi bi-list-check"></i>
                                                    </a>
                                                    <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" 
                                                       class="btn btn-icon btn-primary-transparent" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('admin.quizzes.results', $quiz->id) }}" 
                                                       class="btn btn-icon btn-warning-transparent" title="النتائج">
                                                        <i class="bi bi-bar-chart"></i>
                                                    </a>
                                                    @if($quiz->attempts_count == 0)
                                                        <button type="button" class="btn btn-icon btn-danger-transparent" 
                                                                data-bs-toggle="modal" data-bs-target="#deleteQuiz{{ $quiz->id }}"
                                                                title="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $quizzes->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->

    {{-- مودالات الحذف --}}
    @foreach($quizzes as $quiz)
        @if($quiz->attempts_count == 0)
            <div class="modal fade" id="deleteQuiz{{ $quiz->id }}" tabindex="-1" aria-hidden="true">
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
    @endforeach
@stop

@section('js')
@stop
