@extends('admin.layouts.master')

@section('page-title')
    محاولات الاختبار
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
                    <h5 class="page-title fs-21 mb-1">محاولات: {{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">المحاولات</li>
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- فلاتر البحث --}}
    <div class="card custom-card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.quiz-attempts.index', $quiz->id) }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">بحث بالطالب</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="اسم الطالب أو البريد..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\QuizAttempt::STATUSES as $key => $value)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">النتيجة</label>
                        <select name="passed" class="form-select">
                            <option value="">الكل</option>
                            <option value="1" {{ request('passed') === '1' ? 'selected' : '' }}>ناجح</option>
                            <option value="0" {{ request('passed') === '0' ? 'selected' : '' }}>راسب</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- قائمة المحاولات --}}
    <div class="card custom-card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                المحاولات ({{ $attempts->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($attempts->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3">لا توجد محاولات</p>
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
                                <th>الوقت</th>
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
                                    <td><span class="badge bg-secondary">{{ $attempt->attempt_number }}</span></td>
                                    <td><span class="fw-semibold">{{ $attempt->score }} / {{ $attempt->max_score }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                            {{ round($attempt->percentage, 1) }}%
                                        </span>
                                    </td>
                                    <td><span class="badge bg-{{ $attempt->status_color }}">{{ $attempt->status_name }}</span></td>
                                    <td>{{ $attempt->formatted_time_spent }}</td>
                                    <td>{{ $attempt->started_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.quiz-attempts.show', $attempt->id) }}" 
                                               class="btn btn-icon btn-info-transparent" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($attempt->status === 'under_review')
                                                <a href="{{ route('admin.quiz-attempts.grade', $attempt->id) }}" 
                                                   class="btn btn-icon btn-warning-transparent" title="تصحيح">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endif
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
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

