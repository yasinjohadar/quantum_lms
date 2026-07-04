@extends('admin.layouts.master')

@section('page-title')
    استيراد الأسئلة
@stop

@push('styles')
    @include('admin.pages.questions.partials.excel-import-wizard-styles')
    @include('admin.pages.questions.partials.pack-import-upload-styles')
<style>
    .questions-import-sidebar {
        top: 5rem;
        z-index: 1;
    }
</style>
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid questions-import">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">استيراد الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            @if(!empty($lockedSubject))
                                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.questions.index', $lockedSubject->id) }}">{{ $lockedSubject->name }}</a></li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">بنك الأسئلة</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">استيراد الأسئلة</li>
                        </ol>
                    </nav>
                    @if(!empty($lockedSubject))
                        <p class="text-muted small mb-0 mt-1">الأسئلة المستوردة ستُربط تلقائياً بمادة: <strong>{{ $lockedSubject->name }}</strong></p>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    @if (session('import_summary'))
                        <div class="mt-2 small">
                            <strong>ملخص الاستيراد:</strong><br>
                            نجح: {{ session('import_summary')['success'] }}<br>
                            فشل: {{ session('import_summary')['errors'] }}<br>
                            الإجمالي: {{ session('import_summary')['total'] }}
                        </div>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-xl-8 col-lg-7">

                    @include('admin.pages.questions.partials.excel-import-wizard', [
                        'importFormAction' => route('admin.questions.import'),
                        'cancelUrl' => !empty($lockedSubject)
                            ? route('admin.subjects.questions.index', $lockedSubject->id)
                            : route('admin.questions.index'),
                        'curriculumSync' => true,
                        'prefillClassId' => $prefillClassId ?? null,
                        'prefillSubjectId' => $prefillSubjectId ?? null,
                        'prefillUnitId' => $prefillUnitId ?? null,
                    ])

                    @include('admin.pages.questions.partials.nerve-test-import-module', [
                        'lockedSubject' => $lockedSubject ?? null,
                        'requireSubject' => empty($lockedSubject),
                        'prefillClassId' => $prefillClassId ?? null,
                        'prefillSubjectId' => $prefillSubjectId ?? null,
                        'prefillUnitId' => $prefillUnitId ?? null,
                    ])

                    @include('admin.pages.questions.partials.question-pack-import-module', [
                        'lockedSubject' => $lockedSubject ?? null,
                        'requireSubject' => empty($lockedSubject),
                        'prefillClassId' => $prefillClassId ?? null,
                        'prefillSubjectId' => $prefillSubjectId ?? null,
                        'prefillUnitId' => $prefillUnitId ?? null,
                    ])

                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="questions-import-sidebar sticky-lg-top">
                        <div class="card custom-card mb-3">
                            <div class="card-header">
                                <div class="card-title">الربط بالمنهج</div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    حدّد الصف والمادة والوحدة لربط جميع الأسئلة المستوردة. الوحدة اختيارية — إن تركتها فارغة يبقى السؤال عاماً ما لم يُحدَّد عمود <code>units</code> في الملف.
                                </p>
                                @if(!empty($lockedSubject))
                                    <input type="hidden" id="locked_class_id" value="{{ $lockedSubject->class_id }}">
                                @endif
                                @include('admin.pages.ai.question-generations.partials.optional-curriculum-fields', [
                                    'fieldPrefix' => '',
                                    'schoolClasses' => $schoolClasses,
                                    'lockedSubject' => $lockedSubject ?? null,
                                    'prefillClassId' => $prefillClassId ?? null,
                                    'prefillSubjectId' => $prefillSubjectId ?? null,
                                    'prefillUnitId' => $prefillUnitId ?? null,
                                ])
                            </div>
                        </div>

                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">تعليمات الاستيراد</div>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">الحقول المطلوبة:</h6>
                                <ul class="small mb-4">
                                    <li><code>type</code> — نوع السؤال (single_choice, multiple_choice, …)</li>
                                    <li><code>title</code> — عنوان السؤال</li>
                                    <li><code>difficulty</code> — الصعوبة (easy, medium, hard)</li>
                                    <li><code>points</code> — الدرجة</li>
                                </ul>

                                <h6 class="fw-semibold mb-3">أنواع الأسئلة المدعومة:</h6>
                                <ul class="small mb-4">
                                    <li>single_choice (اختيار واحد)</li>
                                    <li>multiple_choice (اختيار متعدد)</li>
                                    <li>true_false (صح/خطأ)</li>
                                    <li>short_answer (إجابة قصيرة)</li>
                                    <li>essay (مقالي)</li>
                                    <li>numerical (رقمي)</li>
                                </ul>

                                <div class="d-grid">
                                    <a href="{{ route('admin.questions.export.template') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> تحميل ملف Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="{{ asset('js/admin/excel-questions-import.js') }}"></script>
@include('admin.pages.ai.question-generations.partials.optional-curriculum-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initOptionalCurriculumCascade === 'function') {
        window.initOptionalCurriculumCascade({
            classSelectId: 'class_id',
            subjectSelectId: 'subject_id',
            unitSelectId: 'unit_id',
            ajaxSubjectsBase: @json(url('admin/questions/ajax/classes')),
            ajaxUnitsBase: @json(url('admin/questions/ajax/subjects')),
            prefillSubjectId: @json($prefillSubjectId ?? ''),
            prefillUnitId: @json($prefillUnitId ?? ''),
            lockedSubjectId: @json(!empty($lockedSubject) ? $lockedSubject->id : null),
        });
    }
});
</script>
@stop
