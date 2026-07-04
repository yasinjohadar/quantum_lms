@extends('admin.layouts.master')

@section('page-title')
    استيراد أسئلة — {{ $quiz->title }}
@stop

@push('styles')
    @include('admin.pages.questions.partials.excel-import-wizard-styles')
    @include('admin.pages.questions.partials.pack-import-upload-styles')
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid questions-import">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">استيراد أسئلة للاختبار</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.edit', $quiz) }}">{{ $quiz->title }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">استيراد الأسئلة</li>
                        </ol>
                    </nav>
                    <p class="text-muted small mb-0 mt-1">
                        نفس خيارات صفحة استيراد بنك الأسئلة: Excel، حزمة اختبار الأعصاب، وحزمة أسئلة (MD/CSV).
                    </p>
                </div>
            </div>

            @include('admin.pages.quizzes.partials.create-progress-bar', ['currentStep' => 2])

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
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-xl-8 col-lg-7">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <div class="card-title mb-0">ملخص الاختبار</div>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">العنوان</dt>
                                <dd class="col-sm-9">{{ $quiz->title }}</dd>
                                <dt class="col-sm-3">المادة</dt>
                                <dd class="col-sm-9">{{ $quiz->subject?->name ?? '—' }}</dd>
                                <dt class="col-sm-3">الوحدة</dt>
                                <dd class="col-sm-9">{{ $quiz->unit?->title ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if (! $canImport)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            يجب تحديد <strong>المادة</strong> في بيانات الاختبار قبل استيراد الأسئلة.
                            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="alert-link">تعديل الاختبار</a>
                        </div>
                    @else
                        @include('admin.pages.questions.partials.excel-import-wizard', [
                            'importFormAction' => route('admin.quizzes.import-excel.store', $quiz),
                            'cancelUrl' => route('admin.quizzes.questions', $quiz),
                            'importSubmitLabel' => 'استيراد وربط بالاختبار',
                            'curriculumSync' => false,
                            'prefillClassId' => $prefillClassId,
                            'prefillSubjectId' => $prefillSubjectId,
                            'prefillUnitId' => $prefillUnitId,
                        ])

                        @include('admin.pages.questions.partials.nerve-test-import-module', [
                            'lockedSubject' => $lockedSubject ?? null,
                            'requireSubject' => false,
                            'prefillClassId' => $prefillClassId,
                            'prefillSubjectId' => $prefillSubjectId,
                            'prefillUnitId' => $prefillUnitId,
                            'importFormAction' => route('admin.quizzes.import-nerve-test.store', $quiz),
                            'importSubmitLabel' => 'استيراد وربط بالاختبار',
                        ])

                        @include('admin.pages.questions.partials.question-pack-import-module', [
                            'lockedSubject' => $lockedSubject ?? null,
                            'requireSubject' => false,
                            'prefillClassId' => $prefillClassId,
                            'prefillSubjectId' => $prefillSubjectId,
                            'prefillUnitId' => $prefillUnitId,
                            'importFormAction' => route('admin.quizzes.import-question-pack.store', $quiz),
                            'importSubmitLabel' => 'استيراد وربط بالاختبار',
                        ])
                    @endif

                    <div class="card custom-card">
                        <div class="card-body d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <p class="text-muted mb-0 small">
                                يمكنك تخطي الاستيراد وإضافة الأسئلة يدوياً من بنك الأسئلة.
                            </p>
                            <a href="{{ route('admin.quizzes.questions', $quiz) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-skip-forward me-1"></i> تخطي — إدارة الأسئلة يدوياً
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <div class="card-title">الربط بالمنهج</div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                الأسئلة المستوردة تُربط تلقائياً بمادة ووحدة الاختبار، وتُضاف إلى هذا الاختبار.
                            </p>
                            <dl class="row mb-0 small">
                                <dt class="col-sm-4">المادة</dt>
                                <dd class="col-sm-8">{{ $quiz->subject?->name ?? '—' }}</dd>
                                <dt class="col-sm-4">الوحدة</dt>
                                <dd class="col-sm-8">{{ $quiz->unit?->title ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">تعليمات الاستيراد</div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">الحقول المطلوبة (Excel):</h6>
                            <ul class="small mb-4">
                                <li><code>type</code> — نوع السؤال</li>
                                <li><code>title</code> — عنوان السؤال</li>
                                <li><code>difficulty</code> — easy, medium, hard</li>
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
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="{{ asset('js/admin/excel-questions-import.js') }}"></script>
@stop
