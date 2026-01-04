@extends('admin.layouts.master')

@section('page-title')
    إنشاء تقرير جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء تقرير جديد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إنشاء تقرير</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-xl-8 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">إعدادات التقرير</h5>
                    </div>
                    <div class="card-body">
                        @if($selectedTemplate)
                            <div class="alert alert-info mb-3">
                                <strong>القالب المحدد:</strong> {{ $selectedTemplate->name }}
                            </div>
                        @endif
                        <form method="GET" action="{{ route('admin.reports.show', $selectedTemplate ? $selectedTemplate->id : ($templates->first()->id ?? 1)) }}">
                            @if(request('type') == 'student')
                                <div class="mb-3">
                                    <label class="form-label">الطالب <span class="text-danger">*</span></label>
                                    <select name="user_id" id="student-select" class="form-select" required>
                                        <option value="">ابحث عن طالب...</option>
                                        @foreach(\App\Models\User::students()->get() as $student)
                                            <option value="{{ $student->id }}" {{ request('user_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }} - {{ $student->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">يمكنك البحث بالاسم أو البريد الإلكتروني</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الفترة</label>
                                    <select name="period" class="form-select">
                                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>سنة</option>
                                    </select>
                                </div>
                            @elseif(request('type') == 'course')
                                <div class="mb-3">
                                    <label class="form-label">الكورس</label>
                                    <select name="subject_id" class="form-select" required>
                                        <option value="">اختر الكورس</option>
                                        @foreach(\App\Models\Subject::active()->get() as $subject)
                                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الفترة</label>
                                    <select name="period" class="form-select">
                                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>سنة</option>
                                    </select>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label">الفترة</label>
                                    <select name="period" class="form-select">
                                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>سنة</option>
                                    </select>
                                </div>
                            @endif

                            @if($selectedTemplate)
                                <input type="hidden" name="template" value="{{ $selectedTemplate->id }}">
                            @else
                                <div class="mb-3">
                                    <label class="form-label">اختر القالب</label>
                                    <select name="template" class="form-select" required>
                                        <option value="">اختر قالب</option>
                                        @foreach($templates as $tmpl)
                                            <option value="{{ $tmpl->id }}" {{ request('template') == $tmpl->id ? 'selected' : '' }}>
                                                {{ $tmpl->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض التقرير
                                </button>
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for student search
    $('#student-select').select2({
        placeholder: 'ابحث عن طالب...',
        allowClear: true,
        dir: 'rtl',
        language: {
            noResults: function() {
                return 'لا توجد نتائج';
            },
            searching: function() {
                return 'جاري البحث...';
            }
        },
        width: '100%'
    });
});
</script>
@endpush

@stop
