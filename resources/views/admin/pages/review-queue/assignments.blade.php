@extends('admin.layouts.master')

@section('page-title')
    الواجبات قيد المراجعة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الواجبات قيد المراجعة</h5>
                    <p class="text-muted mb-0">مراجعة الواجبات المقدمة من المعلمين</p>
                </div>
                <div>
                    <a href="{{ route('admin.review-queue.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- فلترة -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="بحث..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="class_id" class="form-select">
                                <option value="">كل الصفوف</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="subject_id" class="form-select">
                                <option value="">كل المواد</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">بحث</button>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('admin.review-queue.assignments') }}" class="btn btn-secondary w-100">إعادة</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- الجدول -->
            @if($assignments->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>عنوان الواجب</th>
                                        <th>المرتبط بـ</th>
                                        <th>المعلم</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        <tr>
                                            <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                                            <td>{{ $assignment->title }}</td>
                                            <td>
                                                @if($assignment->assignable_type === 'App\Models\Subject')
                                                    {{ $assignment->assignable->name ?? '-' }}
                                                @else
                                                    {{ $assignment->assignable->title ?? '-' }}
                                                @endif
                                            </td>
                                            <td>{{ $assignment->creator->name ?? '-' }}</td>
                                            <td>{{ $assignment->submitted_for_review_at ? \Carbon\Carbon::parse($assignment->submitted_for_review_at)->format('Y-m-d H:i') : '-' }}</td>
                                            <td>
                                                <span class="badge bg-warning">قيد المراجعة</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.assignments.show', $assignment->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $assignments->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">لا توجد واجبات قيد المراجعة حالياً</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
