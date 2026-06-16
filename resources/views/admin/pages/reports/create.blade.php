@extends('admin.layouts.master')

@section('page-title')
    إنشاء تقرير جديد
@stop

@push('styles')
    @include('admin.pages.reports.partials.reports-index-styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="main-content app-content reports-index-page">
        <div class="container-fluid">

            <div class="reports-index-hero my-4">
                <div class="reports-index-hero__icon">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>
                <div class="reports-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إنشاء تقرير</li>
                        </ol>
                    </nav>
                    <h4 class="reports-index-hero__title">إنشاء تقرير جديد</h4>
                    <p class="reports-index-hero__subtitle">اختر القالب والمعايير ثم اعرض التقرير أو صدّره</p>
                </div>
                <div class="reports-index-hero__actions">
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="reports-index-card reports-form-card">
                        <div class="reports-index-card__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="reports-index-card__header-icon"><i class="bi bi-sliders"></i></span>
                                <span>إعدادات التقرير</span>
                            </div>
                        </div>
                        <div class="reports-index-card__body">
                            @if($selectedTemplate)
                                <div class="reports-selected-template">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <div>
                                        <strong>القالب المحدد:</strong> {{ $selectedTemplate->name }}
                                    </div>
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

                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-eye me-1"></i> عرض التقرير
                                    </button>
                                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> إلغاء
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#student-select').select2({
        placeholder: 'ابحث عن طالب...',
        allowClear: true,
        dir: 'rtl',
        language: {
            noResults: function() { return 'لا توجد نتائج'; },
            searching: function() { return 'جاري البحث...'; }
        },
        width: '100%'
    });
});
</script>
@endpush
