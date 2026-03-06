@extends('admin.layouts.master')

@section('page-title')
    تعديل أسبوع دراسي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تعديل الأسبوع: {{ $academicWeek->title ?? 'الأسبوع ' . $academicWeek->week_number }}</h5>
                <a href="{{ route('admin.academic-weeks.index', ['academic_year_id' => $academicWeek->academic_year_id]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.academic-weeks.update', $academicWeek) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 small text-muted">السنة: {{ $academicWeek->academicYear->name ?? '—' }}</div>
                            <div class="col-md-3">
                                <label class="form-label">رقم الأسبوع <span class="text-danger">*</span></label>
                                <input type="number" name="week_number" class="form-control" min="1" value="{{ old('week_number', $academicWeek->week_number) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">عنوان (اختياري)</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $academicWeek->title) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $academicWeek->start_date->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $academicWeek->end_date->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">هدف الدروس الأسبوعية (افتراضي)</label>
                                <input type="number" name="required_lessons_target" class="form-control" min="0" value="{{ old('required_lessons_target', $academicWeek->required_lessons_target) }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $academicWeek->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">نشط</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.academic-weeks.index', ['academic_year_id' => $academicWeek->academic_year_id]) }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
