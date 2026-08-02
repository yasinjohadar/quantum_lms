@extends('admin.layouts.master')

@section('page-title')
    @if(!empty($isFromLesson))
        اختبار تفاعلي للدرس
    @elseif(!empty($isFromSubjectOrUnit) && !empty($selectedUnit))
        اختبار تفاعلي للوحدة
    @else
        اختبار تفاعلي جديد
    @endif
@stop

@push('styles')
    @include('admin.pages.learning-experiences.partials.index-styles')
    <style>
        .ile-create-page .ile-mode-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        @media (max-width: 767.98px) {
            .ile-create-page .ile-mode-grid { grid-template-columns: 1fr; }
        }

        .ile-create-page .ile-mode-card {
            position: relative;
            display: block;
            margin: 0;
            cursor: pointer;
            border: 1px solid var(--ui-border);
            border-radius: 14px;
            background: var(--ui-surface);
            padding: 1rem 1.1rem;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease, background .15s ease;
        }

        .ile-create-page .ile-mode-card:hover {
            transform: translateY(-1px);
            border-color: rgba(5, 150, 105, 0.35);
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.08);
        }

        .ile-create-page .ile-mode-card.is-active {
            border-color: rgba(5, 150, 105, 0.55);
            background: rgba(5, 150, 105, 0.06);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .ile-create-page .ile-mode-card.is-active.ile-mode-card--dynamic {
            border-color: rgba(99, 102, 241, 0.55);
            background: rgba(99, 102, 241, 0.07);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .ile-create-page .ile-mode-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .ile-create-page .ile-mode-card__top {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.45rem;
        }

        .ile-create-page .ile-mode-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.15rem;
            background: rgba(14, 165, 233, 0.12);
            color: #0369a1;
            flex-shrink: 0;
        }

        .ile-create-page .ile-mode-card--dynamic .ile-mode-card__icon {
            background: rgba(99, 102, 241, 0.14);
            color: #4338ca;
        }

        .ile-create-page .ile-mode-card__title {
            font-weight: 800;
            font-size: 0.98rem;
            margin: 0;
            line-height: 1.3;
        }

        .ile-create-page .ile-mode-card__desc {
            margin: 0;
            font-size: 0.8rem;
            color: var(--ui-muted);
            line-height: 1.5;
        }

        .ile-create-page .ile-cascade .form-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--ui-muted);
            margin-bottom: 0.35rem;
        }

        .ile-create-page .form-control,
        .ile-create-page .form-select {
            border-radius: 11px;
            border-color: var(--ui-border);
            min-height: 44px;
            font-size: 0.9rem;
        }

        .ile-create-page textarea.form-control { min-height: 96px; }

        .ile-create-page .form-control:focus,
        .ile-create-page .form-select:focus {
            border-color: rgba(5, 150, 105, 0.45);
            box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.12);
        }

        .ile-create-page .form-select:disabled {
            background: rgba(100, 116, 139, 0.06);
            cursor: not-allowed;
        }

        .ile-create-page .ile-create-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            justify-content: flex-end;
            padding-top: 0.25rem;
        }

        .ile-create-page .ile-create-actions .btn {
            border-radius: 11px;
            font-weight: 700;
            min-height: 44px;
            padding-inline: 1.15rem;
        }

        .ile-create-page .ile-hint-box {
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            background: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(14, 165, 233, 0.18);
            color: #0369a1;
            font-size: 0.82rem;
            line-height: 1.55;
        }

        [data-theme-mode="dark"] .ile-create-page .ile-hint-box,
        [data-bs-theme="dark"] .ile-create-page .ile-hint-box {
            color: #7dd3fc;
        }

        .ile-create-page .ile-hint-box i {
            font-size: 1.05rem;
            margin-top: 0.1rem;
        }

        .ile-create-page .ile-step-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            background: rgba(5, 150, 105, 0.12);
            color: #047857;
            font-size: 0.72rem;
            font-weight: 700;
        }

        [data-theme-mode="dark"] .ile-create-page .ile-step-pill,
        [data-bs-theme="dark"] .ile-create-page .ile-step-pill { color: #6ee7b7; }
    </style>
@endpush

@php
    $oldMode = old('experience_mode', 'classic');
    $isFromSubjectOrUnit = (bool) ($isFromSubjectOrUnit ?? false);
    $isFromLesson = (bool) ($isFromLesson ?? false);
    $heroTitle = $isFromLesson
        ? 'اختبار تفاعلي للدرس'
        : ($isFromSubjectOrUnit && ($selectedUnit ?? null)
            ? 'اختبار تفاعلي للوحدة'
            : 'إنشاء اختبار تفاعلي');
    $heroSubtitle = $isFromLesson
        ? 'سيتم ربط الاختبار بدرس الفيديو المحدد ثم تنتقل لمحرر الأسئلة'
        : ($isFromSubjectOrUnit && ($selectedUnit ?? null)
            ? 'سيتم ربط الاختبار بالوحدة المحددة ثم تنتقل لمحرر الأسئلة'
            : 'عرّف العنوان والوضع، واربطه بالمنهج اختيارياً، ثم انتقل لمحرر الأسئلة');
    $backUrl = ($selectedSubject ?? null)
        ? route('admin.subjects.show', $selectedSubject)
        : route('admin.learning-experiences.index');
@endphp

@section('content')
<div class="main-content app-content ile-index-page ile-create-page">
    <div class="container-fluid">

        <div class="ile-index-hero my-4">
            <div class="ile-index-hero__icon">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div class="ile-index-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        @if($selectedSubject ?? null)
                            <li class="breadcrumb-item"><a href="{{ route('admin.subjects.show', $selectedSubject) }}">{{ $selectedSubject->name }}</a></li>
                        @endif
                        <li class="breadcrumb-item"><a href="{{ route('admin.learning-experiences.index') }}">اختبارات تفاعلية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إنشاء</li>
                    </ol>
                </nav>
                <h4 class="ile-index-hero__title">{{ $heroTitle }}</h4>
                <p class="ile-index-hero__subtitle">{{ $heroSubtitle }}</p>
            </div>
            <div class="ile-index-hero__actions">
                <span class="ile-step-pill"><i class="bi bi-1-circle"></i> الأساسيات</span>
                <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>تحقق من الحقول المطلوبة ثم أعد المحاولة.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.learning-experiences.store') }}" id="ileCreateForm">
            @csrf

            <div class="ile-index-card">
                <div class="ile-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ile-index-card__header-icon"><i class="bi bi-card-heading"></i></span>
                        <span>بيانات الاختبار</span>
                    </div>
                </div>
                <div class="ile-index-card__body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="ileTitle">العنوان <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="ileTitle" class="form-control" value="{{ old('title') }}" required placeholder="مثال: مراجعة تفاعلية — الطاقة الشمسية">
                            @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ileDescription">الوصف</label>
                            <textarea name="description" id="ileDescription" class="form-control" rows="3" placeholder="وصف مختصر يظهر في المكتبة (اختياري)">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ile-index-card">
                <div class="ile-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ile-index-card__header-icon"><i class="bi bi-magic"></i></span>
                        <span>وضع الاختبار</span>
                    </div>
                    <span class="text-muted small fw-normal">لا يمكن خلط الوضعين لاحقاً</span>
                </div>
                <div class="ile-index-card__body">
                    <div class="ile-mode-grid">
                        <label class="ile-mode-card {{ $oldMode === 'classic' ? 'is-active' : '' }}" data-mode="classic">
                            <input type="radio" name="experience_mode" value="classic" @checked($oldMode === 'classic')>
                            <div class="ile-mode-card__top">
                                <span class="ile-mode-card__icon"><i class="bi bi-grid-3x3-gap"></i></span>
                                <h6 class="ile-mode-card__title">كلاسيك</h6>
                            </div>
                            <p class="ile-mode-card__desc">قوالب أسئلة ثابتة وسريعة — مناسبة لمعظم المراجعات والاختبارات التفاعلية.</p>
                        </label>
                        <label class="ile-mode-card ile-mode-card--dynamic {{ $oldMode === 'dynamic' ? 'is-active' : '' }}" data-mode="dynamic">
                            <input type="radio" name="experience_mode" value="dynamic" @checked($oldMode === 'dynamic')>
                            <div class="ile-mode-card__top">
                                <span class="ile-mode-card__icon"><i class="bi bi-stars"></i></span>
                                <h6 class="ile-mode-card__title">ديناميك</h6>
                            </div>
                            <p class="ile-mode-card__desc">كتل عرض ومشاهد ورياضيات تفاعلية (Schema 2.0) — أغنى بصرياً للأطفال.</p>
                        </label>
                    </div>
                </div>
            </div>

            <div class="ile-index-card">
                <div class="ile-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ile-index-card__header-icon"><i class="bi bi-diagram-3"></i></span>
                        <span>ربط بالمنهج</span>
                    </div>
                    <span class="text-muted small fw-normal">
                        {{ $isFromSubjectOrUnit ? 'محدد من صفحة المادة' : 'اختياري بالكامل' }}
                    </span>
                </div>
                <div class="ile-index-card__body">
                    @if($isFromSubjectOrUnit && ($selectedSubject ?? null))
                        <div class="ile-hint-box mb-3">
                            <i class="bi bi-lock"></i>
                            <div>
                                الربط محدد مسبقاً من صفحة المادة ولا يمكن تغييره من هنا.
                                @if($isFromLesson)
                                    هذا اختبار تفاعلي مرتبط بدرس فيديو.
                                @elseif($selectedUnit ?? null)
                                    هذا اختبار تفاعلي مرتبط بالوحدة.
                                @endif
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">المادة</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedSubject->name }}</strong>
                                    @if($selectedClass ?? null)
                                        <span class="text-muted">({{ $selectedClass->name }})</span>
                                    @endif
                                </div>
                                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الوحدة</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedUnit->title ?? '—' }}</strong>
                                </div>
                                @if($selectedUnit ?? null)
                                    <input type="hidden" name="unit_id" value="{{ $selectedUnit->id }}">
                                @endif
                            </div>
                            @if($isFromLesson && ($selectedLesson ?? null))
                                <div class="col-12">
                                    <label class="form-label">الدرس</label>
                                    <div class="form-control bg-light" style="cursor: not-allowed;">
                                        <strong>{{ $selectedLesson->title }}</strong>
                                    </div>
                                    <input type="hidden" name="lesson_id" value="{{ $selectedLesson->id }}">
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="ile-hint-box mb-3">
                            <i class="bi bi-info-circle"></i>
                            <div>
                                اختر <strong>المرحلة</strong> أولاً لتظهر صفوفها فقط، ثم <strong>الصف</strong> لتظهر مواده، ثم المادة والوحدة.
                                يمكنك ترك الربط فارغاً وإنشاء الاختبار بدون منهج.
                            </div>
                        </div>

                        @include('admin.pages.quizzes.partials.curriculum-cascade-fields', [
                            'stages' => $stages,
                            'selectedStageId' => $selectedStageId ?? null,
                            'selectedClassId' => $selectedClassId ?? null,
                            'selectedSubjectId' => old('subject_id', $selectedSubjectId ?? ''),
                            'selectedUnitId' => old('unit_id', $selectedUnitId ?? ''),
                            'cascadeRequireStage' => true,
                        ])
                    @endif
                    @error('subject_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    @error('unit_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    @error('lesson_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="ile-create-actions mb-4">
                <a href="{{ $backUrl }}" class="btn btn-outline-secondary">إلغاء</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i>
                    إنشاء والانتقال للتحرير
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
    @unless($isFromSubjectOrUnit)
        @include('admin.pages.quizzes.partials.curriculum-cascade-script', [
            'selectedStageId' => $selectedStageId ?? null,
            'selectedClassId' => $selectedClassId ?? null,
            'selectedSubjectId' => old('subject_id', $selectedSubjectId ?? ''),
            'selectedUnitId' => old('unit_id', $selectedUnitId ?? ''),
            'cascadeRequireStage' => true,
        ])
    @endunless
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ile-mode-card').forEach(function (card) {
            card.addEventListener('click', function () {
                document.querySelectorAll('.ile-mode-card').forEach(function (c) {
                    c.classList.remove('is-active');
                });
                card.classList.add('is-active');
            });
        });
    });
    </script>
@endsection
