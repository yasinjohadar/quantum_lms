@extends('student.layouts.master')

@section('page-title')
    نتائج الاختبارات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">نتائج الاختبارات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">نتائج الاختبارات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('student.quizzes.results') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">المادة</label>
                        <select name="subject_id" class="form-select" onchange="this.form.submit()">
                            <option value="">جميع المواد</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">جميع الحالات</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                            <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>مصحح</option>
                            <option value="timeout" {{ request('status') == 'timeout' ? 'selected' : '' }}>انتهى الوقت</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">النتيجة</label>
                        <select name="passed" class="form-select" onchange="this.form.submit()">
                            <option value="">الكل</option>
                            <option value="1" {{ request('passed') == '1' ? 'selected' : '' }}>ناجح</option>
                            <option value="0" {{ request('passed') == '0' ? 'selected' : '' }}>راسب</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card custom-card">
            <div class="card-body">
                @if($attempts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاختبار</th>
                                    <th>المادة</th>
                                    <th>تاريخ البدء</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>الدرجة</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attempts as $attempt)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $attempt->quiz->title }}</strong>
                                            @if($attempt->attempt_number > 1)
                                                <span class="badge bg-secondary-transparent ms-1">
                                                    محاولة {{ $attempt->attempt_number }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $attempt->quiz->subject->name ?? 'عام' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $attempt->started_at->format('Y-m-d H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $attempt->finished_at ? $attempt->finished_at->format('Y-m-d H:i') : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($attempt->score !== null)
                                                <span class="fw-semibold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                                    {{ $attempt->score }} / {{ $attempt->max_score }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ number_format(($attempt->score / $attempt->max_score) * 100, 1) }}%
                                                </small>
                                            @else
                                                <span class="text-muted">قيد التصحيح</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->status === 'completed')
                                                <span class="badge bg-info-transparent">مكتمل</span>
                                            @elseif($attempt->status === 'graded')
                                                <span class="badge bg-success-transparent">مصحح</span>
                                            @elseif($attempt->status === 'timeout')
                                                <span class="badge bg-warning-transparent">انتهى الوقت</span>
                                            @endif
                                            @if($attempt->passed !== null)
                                                @if($attempt->passed)
                                                    <span class="badge bg-success-transparent ms-1">ناجح</span>
                                                @else
                                                    <span class="badge bg-danger-transparent ms-1">راسب</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('student.quizzes.result', ['quiz' => $attempt->quiz_id, 'attempt' => $attempt->id]) }}" 
                                               class="btn btn-sm btn-primary-transparent">
                                                <i class="bi bi-eye me-1"></i>
                                                عرض التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($attempts->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $attempts->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا توجد نتائج</h5>
                        <p class="text-muted">لم يتم العثور على أي نتائج اختبارات</p>
                        <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left me-1"></i>
                            عرض الاختبارات المتاحة
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

