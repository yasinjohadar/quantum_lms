@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المادة الدراسية
@stop

@push('styles')
    @include('admin.pages.subjects.partials.show-styles')
@endpush


@section('content')
    @php
        $lessonMandatoryReview = \App\Models\SystemSetting::lessonMandatoryReviewEnabled();
        $lessonSaveButtonLabel = (!auth()->user()->canReviewContent() && $lessonMandatoryReview)
            ? 'حفظ وإرسال للمراجعة'
            : 'حفظ الدرس';
        $lessonUpdateButtonLabel = (!auth()->user()->canReviewContent() && $lessonMandatoryReview)
            ? 'حفظ وإرسال للمراجعة'
            : 'حفظ التعديلات';
        $primaryRoots = $subject->sections->whereNull('parent_id')->sortBy('order')->values();
        $linkedRoots = $subject->linkedSections;
        $rootSections = $primaryRoots->concat($linkedRoots)->unique('id')->values();
        $subjectImage = $subject->image ? media_public_url($subject->image) : null;
        $subjectDurationLabel = auth()->user()?->hasRole('admin')
            ? \App\Support\LessonDurationFormatter::formatHoursMinutes($subject->totalLessonsDurationSecondsForDisplay())
            : null;
    @endphp
    <div class="main-content app-content subject-show-page">
        <div class="container-fluid">

            {{-- رسائل النجاح --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            {{-- رسائل الأخطاء العامة --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            {{-- أخطاء التحقق من الفورمات (مثل إنشاء/تعديل قسم) --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>يوجد أخطاء:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif
            <div id="ajaxLessonAlerts" class="mt-3"></div>

            <div class="subject-show-hero my-4">
                @if($subjectImage)
                    <img src="{{ $subjectImage }}" alt="{{ $subject->name }}" class="subject-show-hero__cover">
                @else
                    <div class="subject-show-hero__cover subject-show-hero__cover--placeholder">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                @endif

                <div class="subject-show-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد الدراسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
                        </ol>
                    </nav>

                    <h4 class="subject-show-hero__title">{{ $subject->name }}</h4>

                    <div class="subject-show-hero__meta">
                        @if($subject->schoolClass)
                            <a href="{{ route('admin.classes.show', $subject->schoolClass->id) }}" class="subject-show-class-chip" title="عرض الصف">
                                <i class="bi bi-mortarboard-fill"></i>
                                @if($subject->schoolClass->stage)
                                    <span class="subject-show-class-chip__stage">{{ $subject->schoolClass->stage->name }}</span>
                                    <span class="subject-show-class-chip__dot">·</span>
                                @endif
                                <span>{{ $subject->schoolClass->name }}</span>
                            </a>
                        @endif
                        @if($subject->is_active)
                            <span class="subject-show-badge subject-show-badge--active">
                                <i class="bi bi-check-circle-fill"></i> مادة نشطة
                            </span>
                        @else
                            <span class="subject-show-badge subject-show-badge--inactive">
                                <i class="bi bi-x-circle-fill"></i> غير نشطة
                            </span>
                        @endif
                    </div>

                    <div class="subject-show-stats">
                        <div class="subject-show-stat">
                            <span class="subject-show-stat__value">{{ $rootSections->count() }}</span>
                            <span class="subject-show-stat__label">قسم</span>
                        </div>
                        <div class="subject-show-stat">
                            <span class="subject-show-stat__value">{{ $subject->total_lessons }}</span>
                            <span class="subject-show-stat__label">درس</span>
                        </div>
                        <div class="subject-show-stat">
                            <span class="subject-show-stat__value">{{ number_format($subject->total_questions) }}</span>
                            <span class="subject-show-stat__label">سؤال</span>
                        </div>
                        @if($subjectDurationLabel)
                            <div class="subject-show-stat">
                                <span class="subject-show-stat__value">{{ $subjectDurationLabel }}</span>
                                <span class="subject-show-stat__label">مدة الدروس</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="subject-show-hero__actions">
                    @can('question-list')
                        <a href="{{ route('admin.subjects.questions.index', $subject->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-journal-text me-1"></i> بنك الأسئلة
                            <span class="badge bg-light text-dark ms-1">{{ $subject->total_questions }}</span>
                        </a>
                    @endcan
                    @can('subject-edit')
                        <a href="{{ route('admin.subjects.edit', $subject->id) }}{{ request('return_to_class_id') ? '?return_to_class_id=' . request('return_to_class_id') : '' }}" class="btn btn-warning btn-sm text-white">
                            <i class="bi bi-pencil me-1"></i> تعديل
                        </a>
                    @endcan
                    @if(request('return_to_class_id'))
                        <a href="{{ route('admin.classes.show', request('return_to_class_id')) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i> رجوع للصف
                        </a>
                    @else
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                        </a>
                    @endif
                </div>
            </div>

            <div class="subject-show-section">
                <div class="subject-show-section__header">
                    <div class="subject-show-section__title-wrap">
                        <span class="subject-show-section__icon"><i class="bi bi-collection"></i></span>
                        <div>
                            <h6 class="subject-show-section__title">محتويات المادة</h6>
                            <span class="subject-show-section__count">{{ $rootSections->count() }} قسم رئيسي</span>
                        </div>
                    </div>
                    <div class="subject-show-section__actions">
                        <button type="button"
                                id="closeAllAccordionsBtn"
                                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center">
                            <i class="bi bi-chevron-up me-1"></i>
                            إغلاق الكل
                        </button>
                        @can('subject-section-create')
                            <button type="button"
                                    class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createSectionModal">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة قسم رئيسي
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="subject-show-section__body">
                    @if($rootSections->isEmpty())
                        <div class="subject-show-empty">
                            <div class="subject-show-empty__icon">
                                <i class="bi bi-folder2-open"></i>
                            </div>
                            <h6 class="subject-show-empty__title">لا توجد أقسام لهذه المادة</h6>
                            <p class="subject-show-empty__text">ابدأ ببناء المنهج بإضافة أول قسم رئيسي للدروس أو الاختبارات.</p>
                            @can('subject-section-create')
                                <button type="button"
                                        class="btn btn-primary btn-sm mt-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createSectionModal">
                                    <i class="bi bi-plus-lg me-1"></i> إضافة قسم رئيسي
                                </button>
                            @endcan
                        </div>
                    @else
                        <div class="accordion accordion-primary accordions-items-seperate" id="subjectSectionsAccordion" data-sortable="sections" data-subject-id="{{ $subject->id }}" data-parent-id="" data-reorder-url="{{ route('admin.subjects.sections.reorder', $subject) }}">
                            @foreach($rootSections->values() as $index => $section)
                                @include('admin.pages.subjects.partials.section-item', [
                                    'section' => $section,
                                    'allSections' => $subject->sections,
                                    'subject' => $subject,
                                    'sectionIndex' => $index,
                                    'parentAccordionId' => 'subjectSectionsAccordion',
                                    'sectionLevel' => 0,
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- مودال ربط الاختبار بوحدات إضافية --}}
    @can('quiz-edit')
    <div class="modal fade" id="linkQuizUnitsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="linkQuizUnitsModalTitle">ربط الاختبار بوحدات إضافية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form id="linkQuizUnitsForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p class="small fw-semibold mb-1">الاختبار مربوط حالياً بـ:</p>
                            <div id="currentLinkedUnitsQuiz" class="small text-muted">
                                {{-- يُملأ عبر JS من data-linked-units --}}
                            </div>
                        </div>
                        <p class="text-muted small mb-3">اختر الصف ثم المادة ثم القسم ثم الوحدة، ثم اضغط إضافة. يمكنك إضافة أكثر من وحدة.</p>
                        <div id="linkedUnitsListQuiz" class="mb-3">
                            {{-- تُضاف الوحدات المختارة هنا via JS --}}
                        </div>
                        <div class="row g-2 align-items-end mb-2" id="quizLinkUnitsRow">
                            <div class="col-md-3">
                                <label class="form-label small">الصف</label>
                                <select class="form-select form-select-sm quiz-link-class-select" id="quizLinkClassSelect">
                                    <option value="">-- اختر الصف --</option>
                                    @if(isset($linkableClasses))
                                    @foreach($linkableClasses as $cls)
                                    <option value="{{ $cls['id'] }}">{{ !empty($cls['stage_name'] ?? null) ? $cls['stage_name'].' / ' : '' }}{{ $cls['name'] }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">المادة</label>
                                <select class="form-select form-select-sm quiz-link-subject-select" id="quizLinkSubjectSelect" disabled>
                                    <option value="">-- اختر المادة --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">القسم</label>
                                <select class="form-select form-select-sm quiz-link-section-select" id="quizLinkSectionSelect" disabled>
                                    <option value="">-- اختر القسم --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">الوحدة</label>
                                <select class="form-select form-select-sm quiz-link-unit-select" id="quizLinkUnitSelect" disabled>
                                    <option value="">-- اختر الوحدة --</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-success w-100 add-quiz-linked-unit" id="addQuizLinkedUnitBtn" title="إضافة وحدة">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ الربط
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- مودال ربط القسم بمواد إضافية --}}
    @can('subject-section-edit')
    <div class="modal fade" id="linkSectionSubjectsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="linkSectionSubjectsModalTitle">ربط القسم بمواد إضافية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form id="linkSectionSubjectsForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3" role="note">
                            <i class="bi bi-info-circle me-1"></i>
                            هذا الربط لنسخ القسم في <strong>مواد أخرى</strong>، وليس لوضع قسم داخل قسم في نفس المادة.
                            لكل مادة هدف يمكنك اختيار: <strong>قسم رئيسي</strong> فيها أو <strong>تحت قسم محدد</strong>.
                            لقسم فرعي داخل نفس المادة استخدم زر <strong>إضافة قسم فرعي</strong>.
                        </div>
                        <div class="mb-3">
                            <p class="small fw-semibold mb-1">القسم مربوط حالياً بـ:</p>
                            <div id="currentLinkedSubjectsSection" class="small text-muted">
                                {{-- يُملأ عبر JS من API --}}
                            </div>
                        </div>
                        <p class="text-muted small mb-3">اختر الصف ثم المادة ثم مكان النسخة في المادة الهدف، ثم اضغط <strong>إضافة</strong> أو مباشرة <strong>حفظ الربط</strong>. سيتم إنشاء <strong>نسخة كاملة متزامنة</strong> من القسم (وحدات، دروس، اختبارات).</p>
                        <div id="linkedSubjectsListSection" class="mb-3">
                            <p id="linkedSubjectsListEmptyHint" class="small text-muted mb-0">لم تُضف مواد للربط بعد.</p>
                        </div>
                        <div class="row g-2 align-items-end mb-2">
                            <div class="col-md-3">
                                <label class="form-label small">الصف</label>
                                <select class="form-select form-select-sm section-link-class-select" id="sectionLinkClassSelect">
                                    <option value="">-- اختر الصف --</option>
                                    @if(isset($linkableClasses))
                                        @foreach($linkableClasses as $cls)
                                            <option value="{{ $cls['id'] }}">{{ !empty($cls['stage_name'] ?? null) ? $cls['stage_name'].' / ' : '' }}{{ $cls['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">المادة</label>
                                <select class="form-select form-select-sm section-link-subject-select" id="sectionLinkSubjectSelect" disabled>
                                    <option value="">-- اختر المادة --</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="sectionLinkPlacementWrap" style="display: none;">
                                <label class="form-label small d-block">مكان النسخة في المادة</label>
                                <div class="form-check form-check-inline mb-1">
                                    <input class="form-check-input" type="radio" name="section_link_placement" id="sectionLinkPlacementRoot" value="root" checked>
                                    <label class="form-check-label small" for="sectionLinkPlacementRoot">قسم رئيسي</label>
                                </div>
                                <div class="form-check form-check-inline mb-1">
                                    <input class="form-check-input" type="radio" name="section_link_placement" id="sectionLinkPlacementChild" value="child">
                                    <label class="form-check-label small" for="sectionLinkPlacementChild">تحت قسم</label>
                                </div>
                                <select class="form-select form-select-sm mt-1 d-none" id="sectionLinkParentSelect" disabled>
                                    <option value="">-- اختر القسم الأب --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-success w-100" id="addSectionLinkedSubjectBtn" title="إضافة مادة" disabled>
                                    <i class="bi bi-plus-lg"></i> إضافة
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ الربط
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- مودال ربط الدرس بوحدات إضافية --}}
    @can('lesson-edit')
    <div class="modal fade" id="linkLessonUnitsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="linkLessonUnitsModalTitle">ربط الدرس بوحدات إضافية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form id="linkLessonUnitsForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3" role="note">
                            <i class="bi bi-info-circle me-1"></i>
                            هذا الربط لنسخ الدرس في <strong>وحدات أخرى</strong> (نسخة كاملة متزامنة مع المرفقات واختبارات الدرس).
                            التعديل على الأصل أو النسخة يُحدَّث في الطرفين. حذف الأصل يُبقي النسخة دون تغيير.
                        </div>
                        <div class="mb-3">
                            <p class="small fw-semibold mb-1">الدرس مربوط حالياً بـ:</p>
                            <div id="currentLinkedUnitsLesson" class="small text-muted"></div>
                        </div>
                        <p class="text-muted small mb-3">اختر الصف ثم المادة ثم القسم ثم الوحدة الهدف، ثم اضغط <strong>إضافة</strong> أو مباشرة <strong>حفظ الربط</strong>.</p>
                        <div id="linkedUnitsListLesson" class="mb-3">
                            <p id="linkedUnitsListLessonEmptyHint" class="small text-muted mb-0">لم تُضف وحدات للربط بعد.</p>
                        </div>
                        <div class="row g-2 align-items-end mb-2">
                            <div class="col-md-3">
                                <label class="form-label small">الصف</label>
                                <select class="form-select form-select-sm" id="lessonLinkClassSelect">
                                    <option value="">-- اختر الصف --</option>
                                    @if(isset($linkableClasses))
                                        @foreach($linkableClasses as $cls)
                                            <option value="{{ $cls['id'] }}">{{ !empty($cls['stage_name'] ?? null) ? $cls['stage_name'].' / ' : '' }}{{ $cls['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">المادة</label>
                                <select class="form-select form-select-sm" id="lessonLinkSubjectSelect" disabled>
                                    <option value="">-- اختر المادة --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">القسم</label>
                                <select class="form-select form-select-sm" id="lessonLinkSectionSelect" disabled>
                                    <option value="">-- اختر القسم --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">الوحدة</label>
                                <select class="form-select form-select-sm" id="lessonLinkUnitSelect" disabled>
                                    <option value="">-- اختر الوحدة --</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-success w-100" id="addLessonLinkedUnitBtn" title="إضافة وحدة" disabled>
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ الربط
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- مودال إنشاء قسم جديد --}}
    @can('subject-section-create')
    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="createSectionModalTitle">إضافة قسم رئيسي للمادة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form action="{{ route('admin.subjects.sections.store', $subject->id) }}" method="POST" id="createSectionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">مكان القسم في المادة</label>
                            <div class="form-check">
                                <input class="form-check-input section-placement-radio" type="radio" name="create_section_placement" id="createSectionModeRoot" value="root" checked data-modal-id="create">
                                <label class="form-check-label" for="createSectionModeRoot">قسم رئيسي (بدون أب)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input section-placement-radio" type="radio" name="create_section_placement" id="createSectionModeChild" value="child" data-modal-id="create">
                                <label class="form-check-label" for="createSectionModeChild">قسم فرعي تحت قسم آخر</label>
                            </div>
                        </div>
                        <div class="mb-3 d-none" id="createSectionParentWrap">
                            <label class="form-label">القسم الأب</label>
                            <select name="parent_id" id="createSectionParentId" class="form-select" disabled>
                                <option value="">— اختر القسم الأب —</option>
                                @php
                                    $buildSectionOptions = function ($allSections, $parentId = null, $prefix = '') use (&$buildSectionOptions) {
                                        $out = collect();
                                        $items = $parentId === null
                                            ? $allSections->whereNull('parent_id')
                                            : $allSections->where('parent_id', $parentId);
                                        foreach ($items->sortBy('order') as $s) {
                                            $out->push(['id' => $s->id, 'title' => $prefix . $s->title]);
                                            $out = $out->merge($buildSectionOptions($allSections, $s->id, $prefix . '— '));
                                        }
                                        return $out;
                                    };
                                    $sectionOptions = $buildSectionOptions($subject->sections);
                                @endphp
                                @foreach($sectionOptions as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['title'] }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">يظهر القسم الجديد ضمن «الأقسام الفرعية» للقسم المختار.</small>
                        </div>
                        <input type="hidden" name="parent_id" id="createSectionParentIdHidden" value="">
                        <div class="mb-3">
                            <label class="form-label">نوع القسم</label>
                            <select name="type" id="createSectionType" class="form-select" required>
                                <option value="lessons">دروس</option>
                                <option value="quizzes">اختبارات</option>
                            </select>
                            <small class="text-muted">قسم الدروس: يظهر مشغّل الفيديو والأقسام/الوحدات. قسم الاختبارات: يظهر قائمة الاختبارات.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">عنوان القسم</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">وصف القسم (اختياري)</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3 d-flex gap-3 align-items-center">
                            <div class="flex-grow-1">
                                <label class="form-label">ترتيب العرض (اختياري)</label>
                                <input type="number" name="order" class="form-control" min="0" placeholder="اتركه فارغاً لوضعه في النهاية">
                            </div>
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="createSectionIsActive" checked>
                                <label class="form-check-label" for="createSectionIsActive">
                                    القسم نشط
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ القسم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- مودالات تعديل / حذف الأقسام --}}
    @foreach($subject->sections as $section)
        {{-- تعديل قسم --}}
        @can('subject-section-edit')
        <div class="modal fade" id="editSection{{ $section->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">تعديل القسم: {{ $section->title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form action="{{ route('admin.subject-sections.update', $section->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">مكان القسم في المادة</label>
                                <div class="form-check">
                                    <input class="form-check-input section-placement-radio" type="radio" name="edit_section_placement_{{ $section->id }}" id="editSectionModeRoot{{ $section->id }}" value="root" {{ $section->parent_id === null ? 'checked' : '' }} data-modal-id="edit-{{ $section->id }}">
                                    <label class="form-check-label" for="editSectionModeRoot{{ $section->id }}">قسم رئيسي (بدون أب)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input section-placement-radio" type="radio" name="edit_section_placement_{{ $section->id }}" id="editSectionModeChild{{ $section->id }}" value="child" {{ $section->parent_id !== null ? 'checked' : '' }} data-modal-id="edit-{{ $section->id }}">
                                    <label class="form-check-label" for="editSectionModeChild{{ $section->id }}">قسم فرعي تحت قسم آخر</label>
                                </div>
                            </div>
                            <div class="mb-3 {{ $section->parent_id === null ? 'd-none' : '' }}" id="editSectionParentWrap{{ $section->id }}">
                                <label class="form-label">القسم الأب</label>
                                <select name="parent_id" id="editSectionParentId{{ $section->id }}" class="form-select edit-section-parent-select" {{ $section->parent_id === null ? 'disabled' : '' }}>
                                    <option value="">— اختر القسم الأب —</option>
                                    @php
                                        $excludeIds = collect([$section->id]);
                                        $queue = [$section->id];
                                        while (!empty($queue)) {
                                            $pid = array_shift($queue);
                                            foreach ($subject->sections->where('parent_id', $pid) as $c) {
                                                $excludeIds->push($c->id);
                                                $queue[] = $c->id;
                                            }
                                        }
                                        $excludeIds = $excludeIds->unique()->values();
                                        $buildEditSectionOptions = function ($allSections, $parentId, $prefix, $excludeIds) use (&$buildEditSectionOptions) {
                                            $out = collect();
                                            $items = $parentId === null
                                                ? $allSections->whereNull('parent_id')
                                                : $allSections->where('parent_id', $parentId);
                                            foreach ($items->sortBy('order') as $s) {
                                                if ($excludeIds->contains($s->id)) continue;
                                                $out->push(['id' => $s->id, 'title' => $prefix . $s->title]);
                                                $out = $out->merge($buildEditSectionOptions($allSections, $s->id, $prefix . '— ', $excludeIds));
                                            }
                                            return $out;
                                        };
                                        $editSectionOptions = $buildEditSectionOptions($subject->sections, null, '', $excludeIds);
                                    @endphp
                                    @foreach($editSectionOptions as $opt)
                                        <option value="{{ $opt['id'] }}" {{ (string)$section->parent_id === (string)$opt['id'] ? 'selected' : '' }}>{{ $opt['title'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">لا يمكن جعل القسم أباً لنفسه أو لأحد أحفاده.</small>
                            </div>
                            <input type="hidden" name="parent_id" id="editSectionParentIdHidden{{ $section->id }}" value="" {{ $section->parent_id !== null ? 'disabled' : '' }}>
                            <div class="mb-3">
                                <label class="form-label">نوع القسم</label>
                                <select name="type" class="form-select" required>
                                    <option value="lessons" {{ ($section->type ?? 'lessons') === 'lessons' ? 'selected' : '' }}>دروس</option>
                                    <option value="quizzes" {{ ($section->type ?? '') === 'quizzes' ? 'selected' : '' }}>اختبارات</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عنوان القسم</label>
                                <input type="text" name="title" class="form-control" value="{{ $section->title }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">وصف القسم (اختياري)</label>
                                <textarea name="description" class="form-control" rows="3">{{ $section->description }}</textarea>
                            </div>
                            <div class="mb-3 d-flex gap-3 align-items-center">
                                <div class="flex-grow-1">
                                    <label class="form-label">ترتيب العرض</label>
                                    <input type="number" name="order" class="form-control" min="0" value="{{ $section->order }}">
                                </div>
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="editSectionIsActive{{ $section->id }}" {{ $section->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="editSectionIsActive{{ $section->id }}">
                                        القسم نشط
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        {{-- حذف قسم --}}
        @can('subject-section-delete')
        <div class="modal fade" id="deleteSection{{ $section->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold text-danger">حذف القسم</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form action="{{ route('admin.subject-sections.destroy', $section->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-center">
                            <p class="mb-2">
                                هل أنت متأكد من حذف القسم:
                                <span class="fw-bold text-danger">{{ $section->title }}</span>؟
                            </p>
                            <p class="text-muted small mb-0">
                                يمكن إنشاء أقسام جديدة لاحقاً، لكن لا يمكن استرجاع هذا القسم بعد الحذف.
                            </p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                                إلغاء
                            </button>
                            <button type="submit" class="btn btn-danger px-4">
                                حذف القسم
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        {{-- مودال إنشاء درس مباشر داخل القسم (نفس مودال إنشاء الدرس الأساسي) --}}
        @can('lesson-create')
        @if((int) $section->subject_id === (int) $subject->id)
        <div class="modal fade" id="createSectionLessonModal{{ $section->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0 bg-success-transparent">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-play-circle text-success me-2"></i>
                            إضافة درس جديد
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form action="{{ route('admin.sections.lessons.store', $section->id) }}" method="POST" enctype="multipart/form-data" class="js-lesson-ajax-form" data-lesson-action="store" data-section-id="{{ $section->id }}">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" placeholder="مثال: مقدمة في الأعداد الطبيعية" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">نوع الفيديو <span class="text-danger">*</span></label>
                                        <select name="video_type" class="form-select lesson-video-type-select" data-media-context="section" data-media-id="{{ $section->id }}" id="sectionVideoType{{ $section->id }}" required>
                                            <option value="youtube">يوتيوب</option>
                                            <option value="vimeo" selected>فيميو</option>
                                            <option value="external">رابط خارجي</option>
                                            <option value="upload">رفع ملف</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3" id="sectionVideoUrlField{{ $section->id }}">
                                <label class="form-label">رابط الفيديو</label>
                                <input type="url" name="video_url" class="form-control" placeholder="https://vimeo.com/...">
                                <small class="text-muted">الصق رابط الفيديو من Vimeo أو YouTube أو أي مصدر خارجي</small>
                            </div>

                            <div class="mb-3 d-none" id="sectionVideoFileField{{ $section->id }}">
                                <label class="form-label">ملف الفيديو</label>
                                <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/ogg">
                                <small class="text-muted">الحد الأقصى: 500 ميجابايت (MP4, WebM, OGG)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">وصف الدرس</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="وصف مختصر لمحتوى الدرس..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">الصورة المصغرة</label>
                                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">مدة الفيديو (ثانية)</label>
                                        <input type="number" name="duration" class="form-control" min="0" placeholder="مثال: 600">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">ترتيب العرض</label>
                                        <input type="number" name="order" class="form-control" min="0" placeholder="تلقائي">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">من الصفحة</label>
                                        <input type="number" name="book_page_from" id="bookPageFrom{{ $section->id }}" class="form-control" min="1" placeholder="مثال: 10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">إلى الصفحة</label>
                                        <input type="number" name="book_page_to" id="bookPageTo{{ $section->id }}" class="form-control" min="1" placeholder="مثال: 25">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    @include('admin.pages.subjects.partials.lesson-review-teacher-fields', [
                                        'mandatoryReview' => $lessonMandatoryReview,
                                        'fieldId' => 'lessonActive'.$section->id,
                                    ])
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_free" id="lessonFree{{ $section->id }}">
                                        <label class="form-check-label" for="lessonFree{{ $section->id }}">درس مجاني</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_preview" id="lessonPreview{{ $section->id }}">
                                        <label class="form-check-label" for="lessonPreview{{ $section->id }}">متاح للمعاينة</label>
                                    </div>
                                </div>
                            </div>

                            @include('admin.pages.lessons.partials.lesson-create-attachments-fields', ['fieldId' => 'section-' . $section->id])
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> {{ $lessonSaveButtonLabel }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @endcan

        {{-- مودال إنشاء وحدة جديدة (لأقسام المادة الحالية فقط؛ الأقسام المرتبطة بمادة أخرى لا تُعرض مودالاً منها) --}}
        @can('unit-create')
        @if((int) $section->subject_id === (int) $subject->id)
        <div class="modal fade" id="createUnitModal{{ $section->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-layers text-primary me-2"></i>
                            إضافة قسم جديد لرفع الدروس
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form action="{{ route('admin.sections.units.store', $section->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">الوحدة الأب (اختياري)</label>
                                <select name="parent_id" class="form-select create-unit-parent-select" data-section-id="{{ $section->id }}">
                                    <option value="">— وحدة رئيسية (بدون أب) —</option>
                                    @php
                                        $buildUnitOptions = function ($allUnits, $parentId, $prefix) use (&$buildUnitOptions) {
                                            $out = collect();
                                            $items = $parentId === null
                                                ? $allUnits->whereNull('parent_id')
                                                : $allUnits->where('parent_id', $parentId);
                                            foreach ($items->sortBy('order') as $u) {
                                                $out->push(['id' => $u->id, 'title' => $prefix . $u->title]);
                                                $out = $out->merge($buildUnitOptions($allUnits, $u->id, $prefix . '— '));
                                            }
                                            return $out;
                                        };
                                        $unitOptions = $buildUnitOptions($section->units, null, '');
                                    @endphp
                                    @foreach($unitOptions as $opt)
                                        <option value="{{ $opt['id'] }}">{{ $opt['title'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">اتركه فارغاً لوحدة رئيسية، أو اختر وحدة لإنشاء وحدة فرعية تحتها.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عنوان الوحدة <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="مثال: الوحدة الأولى - الأعداد" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">وصف الوحدة (اختياري)</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="وصف مختصر لمحتوى الوحدة..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">ترتيب العرض</label>
                                        <input type="number" name="order" class="form-control" min="0" placeholder="تلقائي">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3 pt-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="createUnitIsActive{{ $section->id }}" checked>
                                            <label class="form-check-label" for="createUnitIsActive{{ $section->id }}">
                                                الوحدة نشطة
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> حفظ الوحدة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @endcan

        {{-- مودالات تعديل وحذف الوحدات --}}
        @foreach($section->units as $unit)
            {{-- تعديل وحدة --}}
            @can('unit-edit')
            <div class="modal fade" id="editUnit{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-pencil text-primary me-2"></i>
                                تعديل الوحدة
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.units.update', $unit->id) }}" method="POST" data-unit-home-section-id="{{ $unit->section_id }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="sync_mirrored_sections" value="1">
                            <div class="modal-body">
                                @php
                                    $unitExcludeIds = collect([$unit->id]);
                                    $unitQueue = [$unit->id];
                                    while (!empty($unitQueue)) {
                                        $pid = array_shift($unitQueue);
                                        foreach ($section->units->where('parent_id', $pid) as $c) {
                                            $unitExcludeIds->push($c->id);
                                            $unitQueue[] = $c->id;
                                        }
                                    }
                                    $unitExcludeIds = $unitExcludeIds->unique()->values();
                                    $buildEditUnitOptions = function ($allUnits, $parentId, $prefix, $excludeIds) use (&$buildEditUnitOptions) {
                                        $out = collect();
                                        $items = $parentId === null
                                            ? $allUnits->whereNull('parent_id')
                                            : $allUnits->where('parent_id', $parentId);
                                        foreach ($items->sortBy('order') as $u) {
                                            if ($excludeIds->contains($u->id)) continue;
                                            $out->push(['id' => $u->id, 'title' => $prefix . $u->title]);
                                            $out = $out->merge($buildEditUnitOptions($allUnits, $u->id, $prefix . '— ', $excludeIds));
                                        }
                                        return $out;
                                    };
                                    $editUnitOptions = $buildEditUnitOptions($section->units, null, '', $unitExcludeIds);
                                @endphp
                                <div class="mb-3">
                                    <label class="form-label">الوحدة الأب (اختياري)</label>
                                    <select name="parent_id" class="form-select">
                                        <option value="" {{ $unit->parent_id === null ? 'selected' : '' }}>— وحدة رئيسية (بدون أب) —</option>
                                        @foreach($editUnitOptions as $opt)
                                            <option value="{{ $opt['id'] }}" {{ (string)$unit->parent_id === (string)$opt['id'] ? 'selected' : '' }}>{{ $opt['title'] }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">لا يمكن جعل الوحدة أباً لنفسها أو لأحد أحفادها.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">عنوان الوحدة <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ $unit->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">وصف الوحدة (اختياري)</label>
                                    <textarea name="description" class="form-control" rows="3">{{ $unit->description }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">ترتيب العرض</label>
                                            <input type="number" name="order" class="form-control" min="0" value="{{ $unit->order }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3 pt-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active"
                                                       id="editUnitIsActive{{ $unit->id }}" {{ $unit->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="editUnitIsActive{{ $unit->id }}">
                                                    الوحدة نشطة
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $isUnitSyncMirror = $unit->isSyncMirror();
                                    $unitLinkedSections = collect();
                                    if (! $isUnitSyncMirror) {
                                        $unitLinkedSections = $unit->linkedSectionsViaSync();
                                        foreach ($unit->mirroredInSections as $mirSec) {
                                            if (! $unitLinkedSections->contains('id', $mirSec->id)) {
                                                $unitLinkedSections->push($mirSec);
                                            }
                                        }
                                    }
                                    $showUnitMirrorLinks = ! $isUnitSyncMirror && ((isset($linkableSubjects) && $linkableSubjects->isNotEmpty()) || $unitLinkedSections->isNotEmpty());
                                @endphp
                                @if($showUnitMirrorLinks)
                                <hr class="my-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="bi bi-link-45deg me-1"></i> ظهور الوحدة في أقسام إضافية</label>
                                    <p class="small text-muted mb-2">سيتم إنشاء <strong>نسخة كاملة متزامنة</strong> من الوحدة (وحدات فرعية، دروس، اختبارات) في كل قسم تختاره. التعديلات تُزامَن ثنائياً بين النسخ.</p>
                                    <p class="small text-muted mb-2">القسم المنزل للوحدة: {{ $unit->section->title ?? $section->title }} — المادة: {{ $subject->name }}</p>
                                    <div id="linkedSectionsListUnit{{ $unit->id }}" class="mb-2">
                                        @foreach($unitLinkedSections as $mirSec)
                                        @php
                                            $mirSub = $mirSec->subject;
                                            $mirStage = optional(optional($mirSub?->schoolClass)->stage)->name ?? '';
                                            $mirClass = $mirSub?->schoolClass?->name ?? '';
                                            $mirSubjectName = $mirSub?->name ?? '';
                                            $mirSectionPath = $mirSec->path_title ?? $mirSec->title ?? '';
                                            $mirPrefix = $mirStage !== ''
                                                ? $mirStage.($mirClass !== '' ? ' / '.$mirClass : '')
                                                : $mirClass;
                                            $mirBadgeText = $mirPrefix !== ''
                                                ? $mirPrefix.' — '.$mirSubjectName.' — '.$mirSectionPath
                                                : ($mirSubjectName !== '' ? $mirSubjectName.' — '.$mirSectionPath : $mirSectionPath);
                                        @endphp
                                        <div class="d-flex align-items-center gap-2 mb-1 linked-section-mirror-row">
                                            <span class="badge bg-secondary text-wrap text-start" style="max-width: 100%; white-space: normal;" title="{{ e($mirBadgeText) }}">{{ $mirBadgeText }}</span>
                                            <input type="hidden" name="linked_section_ids[]" value="{{ $mirSec->id }}">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>
                                        </div>
                                        @endforeach
                                    </div>
                                    @if(isset($linkableSubjects) && $linkableSubjects->isNotEmpty())
                                    <div class="row g-2 align-items-end mb-2" data-current-subject-id="{{ $subject->id }}" data-current-class-id="{{ $subject->class_id ?? '' }}">
                                        <div class="col-md-3">
                                            <label class="form-label small">الصف</label>
                                            <select class="form-select form-select-sm unit-mirror-class-select">
                                                <option value="">-- اختر الصف --</option>
                                                @if(isset($linkableClasses))
                                                @foreach($linkableClasses as $cls)
                                                <option value="{{ $cls['id'] }}">{{ !empty($cls['stage_name'] ?? null) ? $cls['stage_name'].' / ' : '' }}{{ $cls['name'] }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">مادة</label>
                                            <select class="form-select form-select-sm unit-mirror-subject-select" disabled>
                                                <option value="">-- اختر المادة --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">القسم</label>
                                            <select class="form-select form-select-sm unit-mirror-section-select" disabled>
                                                <option value="">-- اختر القسم --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-success add-linked-section-for-unit" data-list-id="linkedSectionsListUnit{{ $unit->id }}" title="إضافة ظهور في قسم"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                    @else
                                    <p class="small text-muted mb-0">لا تتوفر مواد أخرى للربط من حسابك؛ يمكنك إزالة الأقسام الحالية فقط.</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> حفظ التعديلات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            {{-- حذف وحدة --}}
            @can('unit-delete')
            <div class="modal fade" id="deleteUnit{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4">
                        <div class="border-0 text-center pt-4 px-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3">
                                <span class="me-2 fs-4 text-warning">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </span>
                                <h5 class="modal-title mb-0 fw-bold">حذف الوحدة</h5>
                            </div>
                            <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <div class="text-center mt-2">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" style="width:80px;height:80px;">
                                <i class="bi bi-trash fs-2"></i>
                            </div>
                        </div>
                        <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-body text-center pt-0 pb-3 px-4">
                                <p class="mb-1 text-muted">هل أنت متأكد من حذف الوحدة:</p>
                                <p class="fw-bold mb-1" style="font-size:1.05rem;">{{ $unit->title }}</p>
                                <p class="text-danger small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    سيتم حذف جميع الدروس المرتبطة بهذه الوحدة!
                                </p>
                            </div>
                            <div class="modal-footer border-0 justify-content-center pb-4">
                                <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="bi bi-trash me-1"></i> حذف الوحدة
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            {{-- مودال إنشاء درس جديد --}}
            @can('lesson-create')
            <div class="modal fade" id="createLessonModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0 bg-success-transparent">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-play-circle text-success me-2"></i>
                                إضافة درس جديد
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.units.lessons.store', $unit->id) }}" method="POST" enctype="multipart/form-data" class="js-lesson-ajax-form" data-lesson-action="store" data-unit-id="{{ $unit->id }}">
                            @csrf
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control" placeholder="مثال: مقدمة في الأعداد الطبيعية" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">نوع الفيديو <span class="text-danger">*</span></label>
                                            <select name="video_type" class="form-select lesson-video-type-select" data-media-context="unit" data-media-id="{{ $unit->id }}" id="videoType{{ $unit->id }}" required>
                                                <option value="youtube">يوتيوب</option>
                                                <option value="vimeo" selected>فيميو</option>
                                                <option value="external">رابط خارجي</option>
                                                <option value="upload">رفع ملف</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3" id="videoUrlField{{ $unit->id }}">
                                    <label class="form-label">رابط الفيديو</label>
                                    <input type="url" name="video_url" class="form-control" placeholder="https://vimeo.com/...">
                                    <small class="text-muted">الصق رابط الفيديو من Vimeo أو YouTube أو أي مصدر خارجي</small>
                                </div>

                                <div class="mb-3 d-none" id="videoFileField{{ $unit->id }}">
                                    <label class="form-label">ملف الفيديو</label>
                                    <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/ogg">
                                    <small class="text-muted">الحد الأقصى: 500 ميجابايت (MP4, WebM, OGG)</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وصف الدرس</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="وصف مختصر لمحتوى الدرس..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">الصورة المصغرة</label>
                                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">مدة الفيديو (ثانية)</label>
                                            <input type="number" name="duration" class="form-control" min="0" placeholder="مثال: 600">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">ترتيب العرض</label>
                                            <input type="number" name="order" class="form-control" min="0" placeholder="تلقائي">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">من الصفحة</label>
                                            <input type="number" name="book_page_from" id="bookPageFrom{{ $unit->id }}" class="form-control" min="1" placeholder="مثال: 10">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">إلى الصفحة</label>
                                            <input type="number" name="book_page_to" id="bookPageTo{{ $unit->id }}" class="form-control" min="1" placeholder="مثال: 25">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        @include('admin.pages.subjects.partials.lesson-review-teacher-fields', [
                                            'mandatoryReview' => $lessonMandatoryReview,
                                            'fieldId' => 'lessonActive'.$unit->id,
                                        ])
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_free" id="lessonFree{{ $unit->id }}">
                                            <label class="form-check-label" for="lessonFree{{ $unit->id }}">درس مجاني</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_preview" id="lessonPreview{{ $unit->id }}">
                                            <label class="form-check-label" for="lessonPreview{{ $unit->id }}">متاح للمعاينة</label>
                                        </div>
                                    </div>
                                </div>

                                @include('admin.pages.lessons.partials.lesson-create-attachments-fields', ['fieldId' => 'unit-' . $unit->id])
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg me-1"></i> {{ $lessonSaveButtonLabel }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            {{-- مودالات تعديل وحذف الدروس --}}
            @foreach($unit->allLessons() as $lesson)
                {{-- تعديل درس --}}
                @can('lesson-edit')
                <div class="modal fade" id="editLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0 bg-primary-transparent">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-pencil text-primary me-2"></i>
                                    تعديل الدرس
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <form action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data" class="js-lesson-ajax-form" data-lesson-action="update" data-unit-id="{{ $lesson->unit_id }}" data-lesson-id="{{ $lesson->id }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                                                <input type="text" name="title" class="form-control" value="{{ $lesson->title }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">نوع الفيديو</label>
                                                <select name="video_type" class="form-select" required>
                                                    @foreach(\App\Models\Lesson::VIDEO_TYPES as $key => $label)
                                                        <option value="{{ $key }}" {{ $lesson->video_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">رابط الفيديو</label>
                                        <input type="text" name="video_url" class="form-control" value="{{ $lesson->video_url }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">وصف الدرس</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $lesson->description }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">الصورة المصغرة</label>
                                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                                @if($lesson->thumbnail)
                                                    <small class="text-muted">الصورة الحالية موجودة</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">مدة الفيديو (ثانية)</label>
                                                <input type="number" name="duration" class="form-control" min="0" value="{{ $lesson->duration }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">ترتيب العرض</label>
                                                <input type="number" name="order" class="form-control" min="0" value="{{ $lesson->order }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">من الصفحة</label>
                                                <input type="number" name="book_page_from" class="form-control" min="1" value="{{ $lesson->book_page_from }}" placeholder="مثال: 10">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">إلى الصفحة</label>
                                                <input type="number" name="book_page_to" class="form-control" min="1" value="{{ $lesson->book_page_to }}" placeholder="مثال: 25">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            @include('admin.pages.subjects.partials.lesson-review-teacher-fields', [
                                                'mandatoryReview' => $lessonMandatoryReview,
                                                'fieldId' => 'lessonActive'.$lesson->id,
                                                'isEdit' => true,
                                                'lesson' => $lesson,
                                            ])
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_free" {{ $lesson->is_free ? 'checked' : '' }}>
                                                <label class="form-check-label">درس مجاني</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_preview" {{ $lesson->is_preview ? 'checked' : '' }}>
                                                <label class="form-check-label">متاح للمعاينة</label>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!$lesson->isSyncMirror() && isset($linkableSubjects) && $linkableSubjects->isNotEmpty())
                                    <p class="small text-muted mb-0 mt-2">
                                        <i class="bi bi-link-45deg me-1"></i>
                                        لنسخ الدرس في وحدات أخرى استخدم زر <strong>ربط</strong> بجانب الدرس في القائمة.
                                    </p>
                                    @endif
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> {{ $lessonUpdateButtonLabel }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
</div>
                @endcan

                {{-- حذف درس --}}
                @can('lesson-delete')
                <div class="modal fade" id="deleteLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="border-0 text-center pt-4 px-4">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                                    <span class="me-2 fs-4 text-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </span>
                                    <h5 class="modal-title mb-0 fw-bold">حذف الدرس</h5>
                                </div>
                                <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="text-center mt-2">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" style="width:80px;height:80px;">
                                    <i class="bi bi-trash fs-2"></i>
                                </div>
                            </div>
                            <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST" class="js-lesson-ajax-form" data-lesson-action="destroy" data-unit-id="{{ $lesson->unit_id }}" data-lesson-id="{{ $lesson->id }}">
                                @csrf
                                @method('DELETE')
                                <div class="modal-body text-center pt-0 pb-3 px-4">
                                    <p class="mb-1 text-muted">هل أنت متأكد من حذف الدرس:</p>
                                    <p class="fw-bold mb-1" style="font-size:1.05rem;">{{ $lesson->title }}</p>
                                    <p class="text-danger small mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        سيتم حذف جميع المرفقات المرتبطة بهذا الدرس!
                                    </p>
                                </div>
                                <div class="modal-footer border-0 justify-content-center pb-4">
                                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="bi bi-trash me-1"></i> حذف الدرس
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endcan

                @include('admin.pages.lessons.partials.attachment-modals', [
                    'lesson' => $lesson,
                    'returnTo' => route('admin.subjects.show', $subject),
                ])

                {{-- مودال تشغيل الفيديو - معاينة سريعة --}}
                @can('lesson-show')
                <div class="modal fade" id="playVideoModal{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0 d-flex align-items-center">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-play-circle text-warning me-2"></i>
                                    {{ $lesson->title }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body p-0">
                                @if($lesson->embed_url)
                                    @php $actualType = $lesson->actual_video_type; @endphp
                                    <div class="ratio ratio-16x9 bg-dark">
                                        @if($actualType === 'youtube')
                                            <iframe
                                                src="{{ $lesson->embed_url }}?rel=0&modestbranding=1"
                                                title="{{ $lesson->title }}"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen
                                                loading="lazy"
                                            ></iframe>
                                        @elseif($actualType === 'vimeo')
                                            <iframe
                                                src="{{ $lesson->embed_url }}?title=0&byline=0&portrait=0"
                                                title="{{ $lesson->title }}"
                                                frameborder="0"
                                                allow="autoplay; fullscreen; picture-in-picture"
                                                allowfullscreen
                                                loading="lazy"
                                            ></iframe>
                                        @elseif($actualType === 'upload')
                                            <video controls class="w-100 h-100"
                                                   poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}"
                                                   controlsList="nodownload">
                                                <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                                <source src="{{ $lesson->embed_url }}" type="video/webm">
                                                <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                                المتصفح لا يدعم تشغيل الفيديو.
                                            </video>
                                        @else
                                            <video controls class="w-100 h-100"
                                                   poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}">
                                                <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                                المتصفح لا يدعم تشغيل الفيديو.
                                            </video>
                                        @endif
                                    </div>
                                @elseif($lesson->video_url)
                                    <div class="alert alert-warning m-3 mb-0">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        تعذر تشغيل الفيديو. تأكد من صحة الرابط.
                                    </div>
                                @else
                                    <div class="text-center py-5 text-muted bg-light m-3 rounded">
                                        <i class="bi bi-film display-4 d-block mb-2"></i>
                                        لا يوجد فيديو لهذا الدرس.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            @endforeach
        @endforeach
    @endforeach

    {{-- مودالات الدروس المباشرة داخل القسم --}}
    @foreach($subject->sections as $section)
        @php
            $sectionDirectLessons = \App\Models\Lesson::query()
                ->where('section_id', $section->id)
                ->whereNull('unit_id')
                ->with(['attachments', 'quizzes', 'linkedUnits.section.subject'])
                ->get();
        @endphp
        @foreach($sectionDirectLessons as $lesson)
            @can('lesson-edit')
            <div class="modal fade" id="editLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0 bg-primary-transparent">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-pencil text-primary me-2"></i>
                                تعديل الدرس
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data" class="js-lesson-ajax-form" data-lesson-action="update" data-section-id="{{ $lesson->section_id }}" data-lesson-id="{{ $lesson->id }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control" value="{{ $lesson->title }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">نوع الفيديو</label>
                                            <select name="video_type" class="form-select" required>
                                                @foreach(\App\Models\Lesson::VIDEO_TYPES as $key => $label)
                                                    <option value="{{ $key }}" {{ $lesson->video_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">رابط الفيديو</label>
                                    <input type="text" name="video_url" class="form-control" value="{{ $lesson->video_url }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">وصف الدرس</label>
                                    <textarea name="description" class="form-control" rows="3">{{ $lesson->description }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">الصورة المصغرة</label>
                                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                            @if($lesson->thumbnail)
                                                <small class="text-muted">الصورة الحالية موجودة</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">مدة الفيديو (ثانية)</label>
                                            <input type="number" name="duration" class="form-control" min="0" value="{{ $lesson->duration }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">ترتيب العرض</label>
                                            <input type="number" name="order" class="form-control" min="0" value="{{ $lesson->order }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">من الصفحة</label>
                                            <input type="number" name="book_page_from" class="form-control" min="1" value="{{ $lesson->book_page_from }}" placeholder="مثال: 10">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">إلى الصفحة</label>
                                            <input type="number" name="book_page_to" class="form-control" min="1" value="{{ $lesson->book_page_to }}" placeholder="مثال: 25">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        @include('admin.pages.subjects.partials.lesson-review-teacher-fields', [
                                            'mandatoryReview' => $lessonMandatoryReview,
                                            'fieldId' => 'lessonActiveSection'.$lesson->id,
                                            'isEdit' => true,
                                            'lesson' => $lesson,
                                        ])
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_free" {{ $lesson->is_free ? 'checked' : '' }}>
                                            <label class="form-check-label">درس مجاني</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_preview" {{ $lesson->is_preview ? 'checked' : '' }}>
                                            <label class="form-check-label">متاح للمعاينة</label>
                                        </div>
                                    </div>
                                </div>
                                @if(!$lesson->isSyncMirror() && isset($linkableSubjects) && $linkableSubjects->isNotEmpty())
                                <p class="small text-muted mb-0 mt-2">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    لنسخ الدرس في وحدات أخرى استخدم زر <strong>ربط</strong> بجانب الدرس في القائمة.
                                </p>
                                @endif
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> {{ $lessonUpdateButtonLabel }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            @can('lesson-delete')
            <div class="modal fade" id="deleteLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4">
                        <div class="border-0 text-center pt-4 px-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3">
                                <span class="me-2 fs-4 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                <h5 class="modal-title mb-0 fw-bold">حذف الدرس</h5>
                            </div>
                            <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <div class="text-center mt-2">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" style="width:80px;height:80px;">
                                <i class="bi bi-trash fs-2"></i>
                            </div>
                        </div>
                        <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST" class="js-lesson-ajax-form" data-lesson-action="destroy" data-section-id="{{ $lesson->section_id }}" data-lesson-id="{{ $lesson->id }}">
                            @csrf
                            @method('DELETE')
                            <div class="modal-body text-center pt-0 pb-3 px-4">
                                <p class="mb-1 text-muted">هل أنت متأكد من حذف الدرس:</p>
                                <p class="fw-bold mb-1" style="font-size:1.05rem;">{{ $lesson->title }}</p>
                                <p class="text-danger small mb-0"><i class="bi bi-info-circle me-1"></i> سيتم حذف جميع المرفقات المرتبطة بهذا الدرس!</p>
                            </div>
                            <div class="modal-footer border-0 justify-content-center pb-4">
                                <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i> حذف الدرس</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            @include('admin.pages.lessons.partials.attachment-modals', [
                    'lesson' => $lesson,
                    'returnTo' => route('admin.subjects.show', $subject),
                ])

            @can('lesson-show')
            <div class="modal fade" id="playVideoModal{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0 d-flex align-items-center">
                            <h5 class="modal-title fw-bold"><i class="bi bi-play-circle text-warning me-2"></i> {{ $lesson->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <div class="modal-body p-0">
                            @if($lesson->embed_url)
                                @php $actualType = $lesson->actual_video_type; @endphp
                                <div class="ratio ratio-16x9 bg-dark">
                                    @if($actualType === 'youtube')
                                        <iframe src="{{ $lesson->embed_url }}?rel=0&modestbranding=1" title="{{ $lesson->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
                                    @elseif($actualType === 'vimeo')
                                        <iframe src="{{ $lesson->embed_url }}?title=0&byline=0&portrait=0" title="{{ $lesson->title }}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                                    @elseif($actualType === 'upload')
                                        <video controls class="w-100 h-100" poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}" controlsList="nodownload">
                                            <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                            <source src="{{ $lesson->embed_url }}" type="video/webm">
                                            <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                            المتصفح لا يدعم تشغيل الفيديو.
                                        </video>
                                    @else
                                        <video controls class="w-100 h-100" poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}">
                                            <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                            المتصفح لا يدعم تشغيل الفيديو.
                                        </video>
                                    @endif
                                </div>
                            @elseif($lesson->video_url)
                                <div class="alert alert-warning m-3 mb-0"><i class="bi bi-exclamation-triangle me-2"></i> تعذر تشغيل الفيديو. تأكد من صحة الرابط.</div>
                            @else
                                <div class="text-center py-5 text-muted bg-light m-3 rounded"><i class="bi bi-film display-4 d-block mb-2"></i> لا يوجد فيديو لهذا الدرس.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        @endforeach
    @endforeach

    {{-- Modals للموافقة والرفض على الدروس --}}
    @foreach($subject->sections as $section)
        @php
            $sectionDirectLessonsForReview = \App\Models\Lesson::query()
                ->where('section_id', $section->id)
                ->whereNull('unit_id')
                ->where('review_status', 'pending_review')
                ->get();
        @endphp
        @foreach($sectionDirectLessonsForReview as $lesson)
            @can('lesson-approve-review')
            <div class="modal fade" id="approveLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0 bg-success-transparent">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                الموافقة على تفعيل الدرس
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.lessons.approve-review', $lesson->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="mb-3">هل تريد الموافقة على تفعيل الدرس: <strong>{{ $lesson->title }}</strong>؟</p>
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <textarea name="review_notes" class="form-control" rows="3" placeholder="يمكنك إضافة ملاحظات للمعلم..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> موافقة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            @can('lesson-reject-review')
            <div class="modal fade" id="rejectLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0 bg-danger-transparent">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                رفض تفعيل الدرس
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.lessons.reject-review', $lesson->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="mb-3">هل تريد رفض تفعيل الدرس: <strong>{{ $lesson->title }}</strong>؟</p>
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات (مطلوب) <span class="text-danger">*</span></label>
                                    <textarea name="review_notes" class="form-control" rows="3" required placeholder="يرجى كتابة الملاحظات التي ستُرسل للمعلم..."></textarea>
                                    <small class="text-muted">سيتم إرسال هذه الملاحظات للمعلم</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i> رفض</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan
        @endforeach

        @foreach($section->units as $unit)
            @foreach($unit->allLessons() as $lesson)
                @if(
                    $lesson->review_status === 'pending_review' &&
                    auth()->user()->canAny(['lesson-approve-review', 'lesson-reject-review'])
                )
                    {{-- Modal الموافقة على الدرس --}}
                    @can('lesson-approve-review')
                    <div class="modal fade" id="approveLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <div class="modal-header border-0 bg-success-transparent">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        الموافقة على تفعيل الدرس
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                </div>
                                <form action="{{ route('admin.lessons.approve-review', $lesson->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p class="mb-3">هل تريد الموافقة على تفعيل الدرس: <strong>{{ $lesson->title }}</strong>؟</p>
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات (اختياري)</label>
                                            <textarea name="review_notes" class="form-control" rows="3" placeholder="يمكنك إضافة ملاحظات للمعلم..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-lg me-1"></i> موافقة
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan

                    {{-- Modal رفض الدرس --}}
                    @can('lesson-reject-review')
                    <div class="modal fade" id="rejectLesson{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <div class="modal-header border-0 bg-danger-transparent">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                        رفض تفعيل الدرس
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                </div>
                                <form action="{{ route('admin.lessons.reject-review', $lesson->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p class="mb-3">هل تريد رفض تفعيل الدرس: <strong>{{ $lesson->title }}</strong>؟</p>
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات (مطلوب) <span class="text-danger">*</span></label>
                                            <textarea name="review_notes" class="form-control" rows="3" required placeholder="يرجى كتابة الملاحظات التي ستُرسل للمعلم..."></textarea>
                                            <small class="text-muted">سيتم إرسال هذه الملاحظات للمعلم</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-lg me-1"></i> رفض
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan
                @endif
            @endforeach
        @endforeach
    @endforeach
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
window.linkableStructure = @json($linkableStructure ?? []);
window.adminQuizzesLinkUnitsBase = "{{ url('admin/quizzes') }}";
window.adminSectionsLinkSubjectsBase = "{{ url('admin/sections') }}";
window.adminLessonsLinkUnitsBase = "{{ url('admin/lessons') }}";
window.formatLinkedUnitBadge = function(u) {
    if (!u) return '';
    if (u.label) return String(u.label);
    var parts = [u.stage_name, u.class_name, u.subject_name, u.section_title, u.title].filter(Boolean);
    return parts.join(' — ');
};
window.formatLinkedSubjectBadge = function(s) {
    if (!s) return '';
    if (s.label) return String(s.label);
    var stage = s.stage_name || '';
    var cls = s.class_name || '';
    var name = s.name || '';
    var prefix = stage ? (stage + (cls ? ' / ' + cls : '')) : cls;
    return prefix ? (prefix + ' — ' + name) : name;
};
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function esc(s) {
        if (s == null || s === '') return '';
        var div = document.createElement('div');
        div.textContent = String(s);
        return div.innerHTML;
    }
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-linked-unit')) {
            const row = e.target.closest('.linked-unit-row') || e.target.closest('.linked-section-mirror-row');
            if (row) row.remove();
        }
    });
    function syncSectionPlacement(modalId, mode) {
        var isCreate = modalId === 'create';
        var rootRadio = document.getElementById(isCreate ? 'createSectionModeRoot' : 'editSectionModeRoot' + modalId.replace('edit-', ''));
        var childRadio = document.getElementById(isCreate ? 'createSectionModeChild' : 'editSectionModeChild' + modalId.replace('edit-', ''));
        var parentWrap = document.getElementById(isCreate ? 'createSectionParentWrap' : 'editSectionParentWrap' + modalId.replace('edit-', ''));
        var parentSelect = document.getElementById(isCreate ? 'createSectionParentId' : 'editSectionParentId' + modalId.replace('edit-', ''));
        var parentHidden = document.getElementById(isCreate ? 'createSectionParentIdHidden' : 'editSectionParentIdHidden' + modalId.replace('edit-', ''));
        if (!rootRadio || !childRadio || !parentWrap || !parentSelect || !parentHidden) return;

        var isChild = mode === 'child';
        childRadio.checked = isChild;
        rootRadio.checked = !isChild;
        parentWrap.classList.toggle('d-none', !isChild);
        parentSelect.disabled = !isChild;
        parentSelect.required = isChild;
        parentHidden.disabled = isChild;
        if (!isChild) {
            parentSelect.value = '';
            parentHidden.value = '';
        }
    }

    document.querySelectorAll('.section-placement-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            syncSectionPlacement(radio.getAttribute('data-modal-id'), radio.value);
        });
    });

    var createSectionForm = document.getElementById('createSectionForm');
    if (createSectionForm) {
        createSectionForm.addEventListener('submit', function() {
            syncSectionPlacement('create', document.getElementById('createSectionModeChild').checked ? 'child' : 'root');
        });
    }

    document.querySelectorAll('.modal[id^="editSection"]').forEach(function(modal) {
        var form = modal.querySelector('form');
        if (!form || !form.action || form.action.indexOf('subject-sections') === -1) return;
        form.addEventListener('submit', function() {
            var sectionId = modal.id.replace('editSection', '');
            var childRadio = document.getElementById('editSectionModeChild' + sectionId);
            syncSectionPlacement('edit-' + sectionId, childRadio && childRadio.checked ? 'child' : 'root');
        });
    });

    var createSectionModalEl = document.getElementById('createSectionModal');
    if (createSectionModalEl) {
        createSectionModalEl.addEventListener('show.bs.modal', function(e) {
            var titleEl = document.getElementById('createSectionModalTitle');
            var trigger = e.relatedTarget;
            if (trigger && trigger.classList && trigger.classList.contains('add-child-section-btn') && trigger.getAttribute('data-parent-id')) {
                syncSectionPlacement('create', 'child');
                var parentSelect = document.getElementById('createSectionParentId');
                if (parentSelect) parentSelect.value = trigger.getAttribute('data-parent-id');
                var parentTitle = trigger.getAttribute('data-parent-title') || '';
                if (titleEl) {
                    titleEl.textContent = parentTitle !== ''
                        ? 'إضافة قسم فرعي تحت: ' + parentTitle
                        : 'إضافة قسم فرعي';
                }
            } else {
                syncSectionPlacement('create', 'root');
                if (titleEl) titleEl.textContent = 'إضافة قسم رئيسي للمادة';
            }
        });
    }

    // تعيين الوحدة الأب عند فتح مودال إنشاء قسم لرفع الدروس من الزر الفرعي
    document.querySelectorAll('[id^="createUnitModal"]').forEach(function(modalEl) {
        modalEl.addEventListener('show.bs.modal', function(e) {
            var modal = e.target;
            var parentSelect = modal.querySelector('select[name="parent_id"].create-unit-parent-select') || modal.querySelector('select[name="parent_id"]');
            if (!parentSelect) return;
            var trigger = e.relatedTarget;
            if (trigger && trigger.classList && trigger.classList.contains('add-child-unit-btn') && trigger.getAttribute('data-parent-id')) {
                parentSelect.value = trigger.getAttribute('data-parent-id');
            } else {
                parentSelect.value = '';
            }
        });
    });

    // مودال ربط الاختبار بوحدات إضافية: تعيين الـ action والعنوان وجلب الأماكن المرتبطة من الخادم عند الفتح
    var linkQuizUnitsModalEl = document.getElementById('linkQuizUnitsModal');
    if (linkQuizUnitsModalEl) {
        linkQuizUnitsModalEl.addEventListener('show.bs.modal', function(e) {
            var form = document.getElementById('linkQuizUnitsForm');
            var titleEl = document.getElementById('linkQuizUnitsModalTitle');
            var listEl = document.getElementById('linkedUnitsListQuiz');
            var currentLinkedEl = document.getElementById('currentLinkedUnitsQuiz');
            var trigger = e.relatedTarget;
            if (trigger && form && titleEl && listEl) {
                var quizId = trigger.getAttribute('data-quiz-id');
                var quizTitle = trigger.getAttribute('data-quiz-title') || '';
                var primaryUnitId = trigger.getAttribute('data-quiz-primary-unit-id') || '';
                if (quizId && window.adminQuizzesLinkUnitsBase) {
                    form.action = window.adminQuizzesLinkUnitsBase + '/' + quizId + '/link-units';
                    form.setAttribute('data-primary-unit-id', primaryUnitId);
                    titleEl.textContent = 'ربط الاختبار بوحدات إضافية' + (quizTitle ? ': ' + quizTitle : '');
                }
                function esc(s) {
                    if (s == null || s === '') return '';
                    var div = document.createElement('div');
                    div.textContent = s;
                    return div.innerHTML;
                }
                function fillLinkedUnitsUI(linkedUnits) {
                    listEl.innerHTML = '';
                    linkedUnits.forEach(function(u) {
                        var label = [u.stage_name, u.class_name, u.subject_name, u.section_title, u.title].filter(Boolean).join(' — ');
                        var badgeText = esc(label || u.title || '#' + u.id);
                        var row = document.createElement('div');
                        row.className = 'd-flex align-items-center gap-2 mb-1 linked-unit-row';
                        row.innerHTML = '<span class="badge bg-secondary">' + badgeText + '</span>' +
                            '<input type="hidden" name="linked_unit_ids[]" value="' + esc(String(u.id)) + '">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>';
                        listEl.appendChild(row);
                    });
                    var classSelect = document.getElementById('quizLinkClassSelect');
                    var subjectSelect = document.getElementById('quizLinkSubjectSelect');
                    var sectionSelect = document.getElementById('quizLinkSectionSelect');
                    var unitSelect = document.getElementById('quizLinkUnitSelect');
                    if (classSelect) classSelect.value = '';
                    if (subjectSelect) { subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>'; subjectSelect.disabled = true; }
                    if (sectionSelect) { sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>'; sectionSelect.disabled = true; }
                    if (unitSelect) { unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>'; unitSelect.disabled = true; }
                    if (currentLinkedEl) {
                        if (linkedUnits.length === 0) {
                            currentLinkedEl.innerHTML = '<span class="text-muted">لا يوجد ربط لوحدات إضافية</span>';
                        } else {
                            var parts = linkedUnits.map(function(u) {
                                var label = [u.stage_name, u.class_name, u.subject_name, u.section_title, u.title].filter(Boolean).join(' — ');
                                return '<span class="badge bg-secondary me-1 mb-1">' + esc(label || u.title || '#' + u.id) + '</span>';
                            });
                            currentLinkedEl.innerHTML = parts.join('');
                        }
                    }
                }
                if (currentLinkedEl) currentLinkedEl.innerHTML = '<span class="text-muted">جاري التحميل...</span>';
                listEl.innerHTML = '';
                if (quizId && window.adminQuizzesLinkUnitsBase) {
                    var linkedUnitsUrl = window.adminQuizzesLinkUnitsBase + '/' + quizId + '/linked-units';
                    fetch(linkedUnitsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function(res) { return res.json(); })
                        .then(function(linkedUnits) {
                            fillLinkedUnitsUI(Array.isArray(linkedUnits) ? linkedUnits : []);
                        })
                        .catch(function() {
                            var linkedUnitsJson = trigger.getAttribute('data-linked-units') || '[]';
                            var linkedUnits = [];
                            try {
                                linkedUnits = JSON.parse(linkedUnitsJson);
                            } catch (err) {
                                linkedUnits = [];
                            }
                            fillLinkedUnitsUI(linkedUnits);
                        });
                } else {
                    var linkedUnitsJson = trigger.getAttribute('data-linked-units') || '[]';
                    var linkedUnits = [];
                    try {
                        linkedUnits = JSON.parse(linkedUnitsJson);
                    } catch (err) {
                        linkedUnits = [];
                    }
                    fillLinkedUnitsUI(linkedUnits);
                }
            }
        });
    }

    function formatSectionLinkTargetBadge(subjectMeta, parentSectionId, parentSectionLabel) {
        var base = (typeof window.formatLinkedSubjectBadge === 'function')
            ? window.formatLinkedSubjectBadge(subjectMeta || {})
            : (subjectMeta && subjectMeta.name ? subjectMeta.name : '');
        if (!parentSectionId) {
            return (base || '') + ' — قسم رئيسي';
        }
        return (base || '') + ' — تحت: ' + (parentSectionLabel || ('#' + parentSectionId));
    }

    function syncSectionLinkedTargetsEmptyHint() {
        var listEl = document.getElementById('linkedSubjectsListSection');
        var hint = document.getElementById('linkedSubjectsListEmptyHint');
        if (!listEl || !hint) return;
        var hasRows = listEl.querySelectorAll('.linked-subject-row').length > 0;
        hint.classList.toggle('d-none', hasRows);
    }

    function appendSectionLinkedTargetRow(listEl, index, subjectMeta, parentSectionId, parentSectionLabel) {
        if (!listEl) return;
        var subjectId = subjectMeta && subjectMeta.id ? subjectMeta.id : '';
        var badgeText = formatSectionLinkTargetBadge(subjectMeta, parentSectionId, parentSectionLabel);
        var row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 mb-1 linked-subject-row';
        row.setAttribute('data-subject-id', String(subjectId));
        row.innerHTML = '<span class="badge bg-secondary text-wrap text-start" style="max-width: 100%; white-space: normal;">' + esc(badgeText) + '</span>' +
            '<input type="hidden" name="linked_targets[' + index + '][subject_id]" value="' + esc(String(subjectId)) + '">' +
            '<input type="hidden" name="linked_targets[' + index + '][parent_section_id]" value="' + esc(parentSectionId ? String(parentSectionId) : '') + '">' +
            '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-subject" title="إزالة"><i class="bi bi-x"></i></button>';
        listEl.appendChild(row);
        syncSectionLinkedTargetsEmptyHint();
    }

    function reindexSectionLinkedTargetRows(listEl) {
        if (!listEl) return;
        listEl.querySelectorAll('.linked-subject-row').forEach(function(row, index) {
            row.querySelectorAll('input[type="hidden"]').forEach(function(input) {
                var field = input.name.indexOf('[subject_id]') !== -1 ? 'subject_id' : 'parent_section_id';
                input.name = 'linked_targets[' + index + '][' + field + ']';
            });
        });
        syncSectionLinkedTargetsEmptyHint();
    }

    function tryAddPendingSectionLinkTarget() {
        var form = document.getElementById('linkSectionSubjectsForm');
        var listEl = document.getElementById('linkedSubjectsListSection');
        var subjectSelect = document.getElementById('sectionLinkSubjectSelect');
        var placementChild = document.getElementById('sectionLinkPlacementChild');
        var parentSelect = document.getElementById('sectionLinkParentSelect');
        if (!listEl || !subjectSelect || !form) return false;
        var primarySubjectId = form.getAttribute('data-primary-subject-id') || '';
        var subjectId = subjectSelect.value;
        if (!subjectId || String(subjectId) === String(primarySubjectId)) return false;

        var parentSectionId = null;
        var parentSectionLabel = null;
        if (placementChild && placementChild.checked) {
            parentSectionId = parentSelect ? parentSelect.value : '';
            if (!parentSectionId) {
                alert('يرجى اختيار القسم الأب في المادة الهدف');
                return false;
            }
            parentSectionLabel = parentSelect && parentSelect.selectedOptions.length
                ? parentSelect.selectedOptions[0].textContent
                : null;
        }

        var existingRows = listEl.querySelectorAll('.linked-subject-row');
        for (var i = 0; i < existingRows.length; i++) {
            if (String(existingRows[i].getAttribute('data-subject-id')) === String(subjectId)) {
                existingRows[i].remove();
                break;
            }
        }
        var s = (typeof window.linkableStructure !== 'undefined')
            ? window.linkableStructure.find(function(x) { return String(x.id) === String(subjectId); })
            : null;
        var nextIndex = listEl.querySelectorAll('.linked-subject-row').length;
        appendSectionLinkedTargetRow(listEl, nextIndex, s || { id: subjectId }, parentSectionId, parentSectionLabel);
        reindexSectionLinkedTargetRows(listEl);
        resetSectionLinkSubjectPicker();
        return true;
    }

    function resetSectionLinkSubjectPicker() {
        var sectionLinkSubjectSelect = document.getElementById('sectionLinkSubjectSelect');
        var placementWrap = document.getElementById('sectionLinkPlacementWrap');
        var parentSelect = document.getElementById('sectionLinkParentSelect');
        var addBtn = document.getElementById('addSectionLinkedSubjectBtn');
        var placementRoot = document.getElementById('sectionLinkPlacementRoot');
        var placementChild = document.getElementById('sectionLinkPlacementChild');
        if (sectionLinkSubjectSelect) {
            sectionLinkSubjectSelect.value = '';
        }
        if (placementWrap) placementWrap.style.display = 'none';
        if (parentSelect) {
            parentSelect.innerHTML = '<option value="">-- اختر القسم الأب --</option>';
            parentSelect.classList.add('d-none');
            parentSelect.disabled = true;
        }
        if (placementRoot) placementRoot.checked = true;
        if (placementChild) placementChild.checked = false;
        if (addBtn) addBtn.disabled = true;
    }

    function resetSectionLinkPicker() {
        var sectionLinkClassSelect = document.getElementById('sectionLinkClassSelect');
        var sectionLinkSubjectSelect = document.getElementById('sectionLinkSubjectSelect');
        if (sectionLinkClassSelect) sectionLinkClassSelect.value = '';
        if (sectionLinkSubjectSelect) {
            sectionLinkSubjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
            sectionLinkSubjectSelect.disabled = true;
        }
        resetSectionLinkSubjectPicker();
    }

    function syncSectionLinkPlacementUI() {
        var placementChild = document.getElementById('sectionLinkPlacementChild');
        var parentSelect = document.getElementById('sectionLinkParentSelect');
        if (!parentSelect) return;
        var isChild = placementChild && placementChild.checked;
        parentSelect.classList.toggle('d-none', !isChild);
        parentSelect.disabled = !isChild;
        if (!isChild) parentSelect.value = '';
    }

    function populateSectionLinkParentSelect(subjectId) {
        var parentSelect = document.getElementById('sectionLinkParentSelect');
        if (!parentSelect || typeof window.linkableStructure === 'undefined') return;
        parentSelect.innerHTML = '<option value="">-- اختر القسم الأب --</option>';
        var subject = window.linkableStructure.find(function(x) { return String(x.id) === String(subjectId); });
        if (!subject || !subject.sections) return;
        subject.sections.forEach(function(sec) {
            var opt = document.createElement('option');
            opt.value = sec.id;
            opt.textContent = sec.path_title || sec.title || ('#' + sec.id);
            parentSelect.appendChild(opt);
        });
    }

    // مودال ربط القسم بمواد إضافية: تعيين العنوان والـ action وجلب المواد المرتبطة عند الفتح
    var linkSectionSubjectsModalEl = document.getElementById('linkSectionSubjectsModal');
    if (linkSectionSubjectsModalEl && window.adminSectionsLinkSubjectsBase) {
        linkSectionSubjectsModalEl.addEventListener('show.bs.modal', function(e) {
            var form = document.getElementById('linkSectionSubjectsForm');
            var titleEl = document.getElementById('linkSectionSubjectsModalTitle');
            var currentLinkedEl = document.getElementById('currentLinkedSubjectsSection');
            var listEl = document.getElementById('linkedSubjectsListSection');
            var trigger = e.relatedTarget;
            if (!form || !titleEl || !listEl) return;
            var sectionId = trigger && trigger.getAttribute('data-section-id');
            var sectionTitle = trigger && trigger.getAttribute('data-section-title') || '';
            var primarySubjectId = trigger && trigger.getAttribute('data-section-primary-subject-id') || '';
            if (sectionId) {
                form.action = window.adminSectionsLinkSubjectsBase + '/' + sectionId + '/link-subjects';
                form.setAttribute('data-primary-subject-id', primarySubjectId);
                titleEl.textContent = 'ربط القسم بمواد إضافية' + (sectionTitle ? ': ' + sectionTitle : '');
            }
            function fillLinkedSubjectsUI(linkedSubjects) {
                linkedSubjects = Array.isArray(linkedSubjects) ? linkedSubjects : [];
                if (currentLinkedEl) {
                    if (linkedSubjects.length === 0) {
                        currentLinkedEl.innerHTML = '<span class="text-muted">لا يوجد ربط لمواد إضافية</span>';
                    } else {
                        var parts = linkedSubjects.map(function(s) {
                            var badge = formatSectionLinkTargetBadge(s, s.parent_section_id, s.parent_section_label);
                            return '<span class="badge bg-secondary me-1 mb-1">' + esc(badge || s.name || '#' + s.id) + '</span>';
                        });
                        currentLinkedEl.innerHTML = parts.join('');
                    }
                }
                listEl.querySelectorAll('.linked-subject-row').forEach(function(row) { row.remove(); });
                linkedSubjects.forEach(function(s, index) {
                    if (String(s.id) === String(primarySubjectId)) return;
                    appendSectionLinkedTargetRow(listEl, index, s, s.parent_section_id || null, s.parent_section_label || null);
                });
                reindexSectionLinkedTargetRows(listEl);
                window.sectionLinkInitialRowCount = listEl.querySelectorAll('.linked-subject-row').length;
            }
            if (currentLinkedEl) currentLinkedEl.innerHTML = '<span class="text-muted">جاري التحميل...</span>';
            listEl.querySelectorAll('.linked-subject-row').forEach(function(row) { row.remove(); });
            syncSectionLinkedTargetsEmptyHint();
            resetSectionLinkPicker();
            var linkedUrl = window.adminSectionsLinkSubjectsBase + '/' + sectionId + '/linked-subjects';
            fetch(linkedUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(res) { return res.json(); })
                .then(function(linkedSubjects) {
                    fillLinkedSubjectsUI(linkedSubjects);
                })
                .catch(function() {
                    fillLinkedSubjectsUI([]);
                });
        });
    }
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-linked-subject')) {
            var row = e.target.closest('.linked-subject-row');
            var listEl = document.getElementById('linkedSubjectsListSection');
            if (row) row.remove();
            reindexSectionLinkedTargetRows(listEl);
        }
    });
    // الصف -> المادة -> مكان النسخة لمودال ربط القسم
    if (typeof window.linkableStructure !== 'undefined') {
        var sectionLinkClassSelect = document.getElementById('sectionLinkClassSelect');
        if (sectionLinkClassSelect) {
            sectionLinkClassSelect.addEventListener('change', function() {
                var form = document.getElementById('linkSectionSubjectsForm');
                var subjectSelect = document.getElementById('sectionLinkSubjectSelect');
                if (!subjectSelect || !form) return;
                var classId = this.value;
                var primarySubjectId = form.getAttribute('data-primary-subject-id') || '';
                subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                subjectSelect.disabled = !classId;
                resetSectionLinkSubjectPicker();
                if (!classId) return;
                var filtered = window.linkableStructure.filter(function(s) { return String(s.class_id) === String(classId); });
                filtered.forEach(function(s) {
                    if (String(s.id) === String(primarySubjectId)) return;
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = (s.stage_name ? s.stage_name + ' / ' : '') + (s.class_name ? s.class_name + ' — ' : '') + s.name + ' (#' + s.id + ')';
                    subjectSelect.appendChild(opt);
                });
            });
        }
        var sectionLinkSubjectSelect = document.getElementById('sectionLinkSubjectSelect');
        if (sectionLinkSubjectSelect) {
            sectionLinkSubjectSelect.addEventListener('change', function() {
                var placementWrap = document.getElementById('sectionLinkPlacementWrap');
                var addBtn = document.getElementById('addSectionLinkedSubjectBtn');
                var subjectId = this.value;
                if (!subjectId) {
                    if (placementWrap) placementWrap.style.display = 'none';
                    if (addBtn) addBtn.disabled = true;
                    return;
                }
                if (placementWrap) placementWrap.style.display = '';
                if (addBtn) addBtn.disabled = false;
                document.getElementById('sectionLinkPlacementRoot').checked = true;
                document.getElementById('sectionLinkPlacementChild').checked = false;
                syncSectionLinkPlacementUI();
                populateSectionLinkParentSelect(subjectId);
            });
        }
        document.querySelectorAll('input[name="section_link_placement"]').forEach(function(radio) {
            radio.addEventListener('change', syncSectionLinkPlacementUI);
        });
        var addSectionLinkedSubjectBtn = document.getElementById('addSectionLinkedSubjectBtn');
        if (addSectionLinkedSubjectBtn) {
            addSectionLinkedSubjectBtn.addEventListener('click', function() {
                if (!tryAddPendingSectionLinkTarget()) {
                    var subjectSelect = document.getElementById('sectionLinkSubjectSelect');
                    if (!subjectSelect || !subjectSelect.value) {
                        alert('يرجى اختيار الصف ثم المادة قبل الإضافة');
                    }
                }
            });
        }
        var linkSectionSubjectsForm = document.getElementById('linkSectionSubjectsForm');
        if (linkSectionSubjectsForm) {
            linkSectionSubjectsForm.addEventListener('submit', function(e) {
                var listEl = document.getElementById('linkedSubjectsListSection');
                var subjectSelect = document.getElementById('sectionLinkSubjectSelect');
                var hadPendingSelection = !!(subjectSelect && subjectSelect.value);
                if (hadPendingSelection) {
                    if (!tryAddPendingSectionLinkTarget()) {
                        e.preventDefault();
                        return;
                    }
                }
                var rowCount = listEl ? listEl.querySelectorAll('.linked-subject-row').length : 0;
                var initialCount = window.sectionLinkInitialRowCount || 0;
                if (rowCount === 0 && initialCount === 0) {
                    e.preventDefault();
                    alert('لم تُضف أي مادة للربط. اختر الصف والمادة الهدف (وقسم رئيسي أو تحت قسم) ثم اضغط حفظ الربط.');
                    return;
                }
                if (rowCount === 0 && initialCount > 0) {
                    if (!confirm('سيتم إزالة كل الروابط الحالية لهذا القسم من المواد الأخرى. متابعة؟')) {
                        e.preventDefault();
                    }
                }
            });
        }
    }

    function syncLessonLinkedTargetsEmptyHint() {
        var listEl = document.getElementById('linkedUnitsListLesson');
        var hint = document.getElementById('linkedUnitsListLessonEmptyHint');
        if (!listEl || !hint) return;
        var hasRows = listEl.querySelectorAll('.linked-unit-target-row').length > 0;
        hint.classList.toggle('d-none', hasRows);
    }

    function appendLessonLinkedTargetRow(listEl, index, unitMeta) {
        if (!listEl || !unitMeta || !unitMeta.id) return;
        var badgeText = (typeof window.formatLinkedUnitBadge === 'function')
            ? window.formatLinkedUnitBadge(unitMeta)
            : (unitMeta.title || '#' + unitMeta.id);
        var row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 mb-1 linked-unit-target-row';
        row.setAttribute('data-unit-id', String(unitMeta.id));
        row.innerHTML = '<span class="badge bg-secondary text-wrap text-start" style="max-width: 100%; white-space: normal;">' + esc(badgeText) + '</span>' +
            '<input type="hidden" name="linked_targets[' + index + '][unit_id]" value="' + esc(String(unitMeta.id)) + '">' +
            '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-lesson-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>';
        listEl.appendChild(row);
        syncLessonLinkedTargetsEmptyHint();
    }

    function reindexLessonLinkedTargetRows(listEl) {
        if (!listEl) return;
        listEl.querySelectorAll('.linked-unit-target-row').forEach(function(row, index) {
            row.querySelectorAll('input[type="hidden"]').forEach(function(input) {
                input.name = 'linked_targets[' + index + '][unit_id]';
            });
        });
        syncLessonLinkedTargetsEmptyHint();
    }

    function resetLessonLinkUnitPicker() {
        var unitSelect = document.getElementById('lessonLinkUnitSelect');
        var sectionSelect = document.getElementById('lessonLinkSectionSelect');
        var subjectSelect = document.getElementById('lessonLinkSubjectSelect');
        var addBtn = document.getElementById('addLessonLinkedUnitBtn');
        if (unitSelect) {
            unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
            unitSelect.disabled = true;
        }
        if (sectionSelect) {
            sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
            sectionSelect.disabled = true;
        }
        if (subjectSelect) {
            subjectSelect.value = '';
            subjectSelect.disabled = true;
        }
        if (addBtn) addBtn.disabled = true;
    }

    function resetLessonLinkPicker() {
        var classSelect = document.getElementById('lessonLinkClassSelect');
        if (classSelect) classSelect.value = '';
        resetLessonLinkUnitPicker();
    }

    function tryAddPendingLessonLinkTarget() {
        var form = document.getElementById('linkLessonUnitsForm');
        var listEl = document.getElementById('linkedUnitsListLesson');
        var unitSelect = document.getElementById('lessonLinkUnitSelect');
        if (!listEl || !unitSelect || !form) return false;
        var primaryUnitId = form.getAttribute('data-primary-unit-id') || '';
        var unitId = unitSelect.value;
        if (!unitId || String(unitId) === String(primaryUnitId)) return false;

        var sectionSelect = document.getElementById('lessonLinkSectionSelect');
        var subjectSelect = document.getElementById('lessonLinkSubjectSelect');
        var subject = (typeof window.linkableStructure !== 'undefined' && subjectSelect && subjectSelect.value)
            ? window.linkableStructure.find(function(x) { return String(x.id) === String(subjectSelect.value); })
            : null;
        var section = subject && subject.sections && sectionSelect
            ? subject.sections.find(function(x) { return String(x.id) === String(sectionSelect.value); })
            : null;
        var unit = section && section.units
            ? section.units.find(function(x) { return String(x.id) === String(unitId); })
            : null;

        var existingRows = listEl.querySelectorAll('.linked-unit-target-row');
        for (var i = 0; i < existingRows.length; i++) {
            if (String(existingRows[i].getAttribute('data-unit-id')) === String(unitId)) {
                existingRows[i].remove();
                break;
            }
        }

        var meta = {
            id: unitId,
            title: unit ? (unit.title || '') : '',
            section_title: section ? (section.path_title || section.title || '') : '',
            subject_name: subject ? (subject.name || '') : '',
            class_name: subject ? (subject.class_name || '') : '',
            stage_name: subject ? (subject.stage_name || '') : '',
            label: (typeof window.formatLinkedUnitBadge === 'function')
                ? window.formatLinkedUnitBadge({
                    id: unitId,
                    title: unit ? unit.title : '',
                    section_title: section ? (section.path_title || section.title) : '',
                    subject_name: subject ? subject.name : '',
                    class_name: subject ? subject.class_name : '',
                    stage_name: subject ? subject.stage_name : ''
                })
                : ''
        };

        var nextIndex = listEl.querySelectorAll('.linked-unit-target-row').length;
        appendLessonLinkedTargetRow(listEl, nextIndex, meta);
        reindexLessonLinkedTargetRows(listEl);
        resetLessonLinkUnitPicker();
        return true;
    }

    var linkLessonUnitsModalEl = document.getElementById('linkLessonUnitsModal');
    if (linkLessonUnitsModalEl && window.adminLessonsLinkUnitsBase) {
        linkLessonUnitsModalEl.addEventListener('show.bs.modal', function(e) {
            var form = document.getElementById('linkLessonUnitsForm');
            var titleEl = document.getElementById('linkLessonUnitsModalTitle');
            var currentLinkedEl = document.getElementById('currentLinkedUnitsLesson');
            var listEl = document.getElementById('linkedUnitsListLesson');
            var trigger = e.relatedTarget;
            if (!form || !titleEl || !listEl) return;
            var lessonId = trigger && trigger.getAttribute('data-lesson-id');
            var lessonTitle = trigger && trigger.getAttribute('data-lesson-title') || '';
            var primaryUnitId = trigger && trigger.getAttribute('data-lesson-primary-unit-id') || '';
            if (lessonId) {
                form.action = window.adminLessonsLinkUnitsBase + '/' + lessonId + '/link-units';
                form.setAttribute('data-primary-unit-id', primaryUnitId);
                titleEl.textContent = 'ربط الدرس بوحدات إضافية' + (lessonTitle ? ': ' + lessonTitle : '');
            }
            function fillLinkedUnitsUI(linkedUnits) {
                linkedUnits = Array.isArray(linkedUnits) ? linkedUnits : [];
                if (currentLinkedEl) {
                    if (linkedUnits.length === 0) {
                        currentLinkedEl.innerHTML = '<span class="text-muted">لا يوجد ربط لوحدات إضافية</span>';
                    } else {
                        var parts = linkedUnits.map(function(u) {
                            var badge = (typeof window.formatLinkedUnitBadge === 'function') ? window.formatLinkedUnitBadge(u) : (u.title || '#' + u.id);
                            return '<span class="badge bg-secondary me-1 mb-1">' + esc(badge || u.title || '#' + u.id) + '</span>';
                        });
                        currentLinkedEl.innerHTML = parts.join('');
                    }
                }
                listEl.querySelectorAll('.linked-unit-target-row').forEach(function(row) { row.remove(); });
                linkedUnits.forEach(function(u, index) {
                    if (primaryUnitId && String(u.id) === String(primaryUnitId)) return;
                    appendLessonLinkedTargetRow(listEl, index, u);
                });
                reindexLessonLinkedTargetRows(listEl);
                window.lessonLinkInitialRowCount = listEl.querySelectorAll('.linked-unit-target-row').length;
            }
            if (currentLinkedEl) currentLinkedEl.innerHTML = '<span class="text-muted">جاري التحميل...</span>';
            listEl.querySelectorAll('.linked-unit-target-row').forEach(function(row) { row.remove(); });
            syncLessonLinkedTargetsEmptyHint();
            resetLessonLinkPicker();
            var linkedUrl = window.adminLessonsLinkUnitsBase + '/' + lessonId + '/linked-units';
            fetch(linkedUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(res) { return res.json(); })
                .then(function(linkedUnits) { fillLinkedUnitsUI(linkedUnits); })
                .catch(function() { fillLinkedUnitsUI([]); });
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-lesson-linked-unit')) {
            var row = e.target.closest('.linked-unit-target-row');
            var listEl = document.getElementById('linkedUnitsListLesson');
            if (row) row.remove();
            reindexLessonLinkedTargetRows(listEl);
        }
    });

    if (typeof window.linkableStructure !== 'undefined') {
        var lessonLinkClassSelect = document.getElementById('lessonLinkClassSelect');
        if (lessonLinkClassSelect) {
            lessonLinkClassSelect.addEventListener('change', function() {
                var subjectSelect = document.getElementById('lessonLinkSubjectSelect');
                if (!subjectSelect) return;
                var classId = this.value;
                subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                subjectSelect.disabled = !classId;
                resetLessonLinkUnitPicker();
                if (!classId) return;
                window.linkableStructure.filter(function(s) { return String(s.class_id) === String(classId); }).forEach(function(s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = (s.stage_name ? s.stage_name + ' / ' : '') + (s.class_name ? s.class_name + ' — ' : '') + s.name + ' (#' + s.id + ')';
                    subjectSelect.appendChild(opt);
                });
                subjectSelect.disabled = false;
            });
        }
        var lessonLinkSubjectSelect = document.getElementById('lessonLinkSubjectSelect');
        if (lessonLinkSubjectSelect) {
            lessonLinkSubjectSelect.addEventListener('change', function() {
                var sectionSelect = document.getElementById('lessonLinkSectionSelect');
                var unitSelect = document.getElementById('lessonLinkUnitSelect');
                var addBtn = document.getElementById('addLessonLinkedUnitBtn');
                if (!sectionSelect || !unitSelect) return;
                var subjectId = this.value;
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                unitSelect.disabled = true;
                sectionSelect.disabled = !subjectId;
                if (addBtn) addBtn.disabled = true;
                if (!subjectId) return;
                var subject = window.linkableStructure.find(function(x) { return String(x.id) === String(subjectId); });
                if (subject && subject.sections) {
                    subject.sections.forEach(function(sec) {
                        var opt = document.createElement('option');
                        opt.value = sec.id;
                        opt.textContent = sec.path_title || sec.title || ('#' + sec.id);
                        sectionSelect.appendChild(opt);
                    });
                }
                sectionSelect.disabled = false;
            });
        }
        var lessonLinkSectionSelect = document.getElementById('lessonLinkSectionSelect');
        if (lessonLinkSectionSelect) {
            lessonLinkSectionSelect.addEventListener('change', function() {
                var subjectSelect = document.getElementById('lessonLinkSubjectSelect');
                var unitSelect = document.getElementById('lessonLinkUnitSelect');
                var addBtn = document.getElementById('addLessonLinkedUnitBtn');
                var form = document.getElementById('linkLessonUnitsForm');
                if (!unitSelect || !subjectSelect) return;
                var sectionId = this.value;
                var primaryUnitId = form ? (form.getAttribute('data-primary-unit-id') || '') : '';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                unitSelect.disabled = !sectionId;
                if (addBtn) addBtn.disabled = true;
                if (!sectionId) return;
                var subject = window.linkableStructure.find(function(x) { return String(x.id) === String(subjectSelect.value); });
                var section = subject && subject.sections
                    ? subject.sections.find(function(x) { return String(x.id) === String(sectionId); })
                    : null;
                if (section && section.units) {
                    section.units.forEach(function(u) {
                        if (primaryUnitId && String(u.id) === String(primaryUnitId)) return;
                        var opt = document.createElement('option');
                        opt.value = u.id;
                        opt.textContent = u.title || ('#' + u.id);
                        unitSelect.appendChild(opt);
                    });
                }
                unitSelect.disabled = false;
            });
        }
        var lessonLinkUnitSelect = document.getElementById('lessonLinkUnitSelect');
        if (lessonLinkUnitSelect) {
            lessonLinkUnitSelect.addEventListener('change', function() {
                var addBtn = document.getElementById('addLessonLinkedUnitBtn');
                if (addBtn) addBtn.disabled = !this.value;
            });
        }
        var addLessonLinkedUnitBtn = document.getElementById('addLessonLinkedUnitBtn');
        if (addLessonLinkedUnitBtn) {
            addLessonLinkedUnitBtn.addEventListener('click', function() {
                if (!tryAddPendingLessonLinkTarget()) {
                    var unitSelect = document.getElementById('lessonLinkUnitSelect');
                    if (!unitSelect || !unitSelect.value) {
                        alert('يرجى اختيار الصف والمادة والقسم والوحدة قبل الإضافة');
                    }
                }
            });
        }
        var linkLessonUnitsForm = document.getElementById('linkLessonUnitsForm');
        if (linkLessonUnitsForm) {
            linkLessonUnitsForm.addEventListener('submit', function(e) {
                var listEl = document.getElementById('linkedUnitsListLesson');
                var unitSelect = document.getElementById('lessonLinkUnitSelect');
                var hadPendingSelection = !!(unitSelect && unitSelect.value);
                if (hadPendingSelection) {
                    if (!tryAddPendingLessonLinkTarget()) {
                        e.preventDefault();
                        return;
                    }
                }
                var rowCount = listEl ? listEl.querySelectorAll('.linked-unit-target-row').length : 0;
                var initialCount = window.lessonLinkInitialRowCount || 0;
                if (rowCount === 0 && initialCount === 0) {
                    e.preventDefault();
                    alert('لم تُضف أي وحدة للربط. اختر الصف والمادة والقسم والوحدة الهدف ثم اضغط حفظ الربط.');
                    return;
                }
                if (rowCount === 0 && initialCount > 0) {
                    if (!confirm('سيتم إزالة كل الروابط الحالية لهذا الدرس من الوحدات الأخرى. متابعة؟')) {
                        e.preventDefault();
                    }
                }
            });
        }
    }

    // ربط الدرس بوحدات إضافية (legacy pivot في مودالات أخرى)
    if (typeof window.linkableStructure !== 'undefined') {
        const structure = window.linkableStructure;
        document.querySelectorAll('.link-class-select').forEach(function(classSelect) {
            classSelect.addEventListener('change', function() {
                const modal = this.closest('.modal');
                if (!modal) return;
                const subjectSelect = modal.querySelector('.link-subject-select');
                const sectionSelect = modal.querySelector('.link-section-select');
                const unitSelect = modal.querySelector('.link-unit-select');
                if (!subjectSelect || !sectionSelect || !unitSelect) return;
                const classId = this.value;
                subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                sectionSelect.disabled = true;
                unitSelect.disabled = true;
                if (!classId) {
                    subjectSelect.disabled = true;
                    return;
                }
                const row = this.closest('[data-current-subject-id]');
                const currentSubjectId = row ? row.getAttribute('data-current-subject-id') : null;
                const currentClassId = row ? row.getAttribute('data-current-class-id') : null;
                const filtered = structure.filter(s => String(s.class_id) === String(classId));
                const currentFirst = currentSubjectId && String(currentClassId) === String(classId)
                    ? filtered.find(s => String(s.id) === String(currentSubjectId))
                    : null;
                if (currentFirst) {
                    const opt = document.createElement('option');
                    opt.value = currentFirst.id;
                    opt.textContent = 'المادة الحالية: ' + (currentFirst.stage_name ? currentFirst.stage_name + ' / ' : '') + (currentFirst.class_name ? currentFirst.class_name + ' — ' : '') + currentFirst.name + ' (#' + currentFirst.id + ')';
                    subjectSelect.appendChild(opt);
                }
                filtered.forEach(function(s) {
                    if (currentSubjectId && String(s.id) === String(currentSubjectId)) return;
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = (s.stage_name ? s.stage_name + ' / ' : '') + (s.class_name ? s.class_name + ' — ' : '') + s.name + ' (#' + s.id + ')';
                    subjectSelect.appendChild(opt);
                });
                subjectSelect.disabled = false;
            });
        });
        document.querySelectorAll('.link-subject-select').forEach(function(subjectSelect) {
            subjectSelect.addEventListener('change', function() {
                const modal = this.closest('.modal');
                if (!modal) return;
                const sectionSelect = modal.querySelector('.link-section-select');
                const unitSelect = modal.querySelector('.link-unit-select');
                if (!sectionSelect || !unitSelect) return;
                const subjectId = this.value;
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                unitSelect.disabled = true;
                if (!subjectId) {
                    sectionSelect.disabled = true;
                    return;
                }
                const subject = structure.find(s => String(s.id) === String(subjectId));
                if (subject && subject.sections) {
                    subject.sections.forEach(function(sec) {
                        const opt = document.createElement('option');
                        opt.value = sec.id;
                        opt.textContent = sec.path_title || sec.title || '';
                        sectionSelect.appendChild(opt);
                    });
                    sectionSelect.disabled = false;
                }
            });
        });
        document.querySelectorAll('.link-section-select').forEach(function(sectionSelect) {
            sectionSelect.addEventListener('change', function() {
                const modal = this.closest('.modal');
                if (!modal) return;
                const unitSelect = modal.querySelector('.link-unit-select');
                const subjectSelect = modal.querySelector('.link-subject-select');
                if (!unitSelect || !subjectSelect) return;
                const subjectId = subjectSelect.value || null;
                const sectionId = this.value;
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                if (!subjectId || !sectionId) {
                    unitSelect.disabled = true;
                    return;
                }
                const subject = structure.find(s => String(s.id) === String(subjectId));
                if (subject && subject.sections) {
                    const section = subject.sections.find(sec => String(sec.id) === String(sectionId));
                    if (section && section.units) {
                        section.units.forEach(function(u) {
                            const opt = document.createElement('option');
                            opt.value = u.id;
                            opt.textContent = u.title;
                            unitSelect.appendChild(opt);
                        });
                        unitSelect.disabled = false;
                    }
                }
            });
        });
        document.querySelectorAll('.add-linked-unit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const listId = this.getAttribute('data-list-id');
                const list = listId ? document.getElementById(listId) : null;
                if (!list) return;
                const modal = this.closest('.modal');
                if (!modal) return;
                const unitSelect = modal.querySelector('.link-unit-select');
                const subjectSelect = modal.querySelector('.link-subject-select');
                const sectionSelect = modal.querySelector('.link-section-select');
                const primaryUnitId = unitSelect ? unitSelect.getAttribute('data-primary-unit-id') : null;
                const unitId = unitSelect ? unitSelect.value : null;
                if (!unitId) {
                    alert('يرجى اختيار الصف ثم المادة ثم القسم ثم الوحدة قبل الإضافة');
                    return;
                }
                if (primaryUnitId && String(unitId) === String(primaryUnitId)) return;
                const existing = list.querySelectorAll('input[name="linked_unit_ids[]"]');
                for (let i = 0; i < existing.length; i++) {
                    if (existing[i].value === unitId) return;
                }
                const subject = structure.find(s => String(s.id) === String(subjectSelect.value));
                let subjectName = ''; let sectionName = ''; let unitTitle = ''; let className = ''; let stageName = '';
                if (subject) {
                    subjectName = subject.name || '';
                    className = subject.class_name || '';
                    stageName = subject.stage_name || '';
                    const section = subject.sections && subject.sections.find(sec => String(sec.id) === String(sectionSelect.value));
                    if (section) {
                        sectionName = section.path_title || section.title || '';
                        const u = section.units && section.units.find(ux => String(ux.id) === String(unitId));
                        if (u) unitTitle = u.title || '';
                    }
                }
                const badgeText = (stageName ? stageName + ' / ' : '') + (className ? className + ' — ' : '') + subjectName + ' — ' + sectionName + ' — ' + unitTitle;
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center gap-2 mb-1 linked-unit-row';
                row.setAttribute('data-lesson-id', subjectSelect.getAttribute('data-lesson-id'));
                row.innerHTML = '<span class="badge bg-secondary">' + badgeText + '</span>' +
                    '<input type="hidden" name="linked_unit_ids[]" value="' + unitId + '">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>';
                list.appendChild(row);
                unitSelect.value = '';
                if (sectionSelect) sectionSelect.value = '';
                if (subjectSelect) subjectSelect.value = '';
                if (sectionSelect) sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                if (unitSelect) unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                if (unitSelect) unitSelect.disabled = true;
                if (sectionSelect) sectionSelect.disabled = true;
            });
        });

        document.querySelectorAll('.unit-mirror-class-select').forEach(function(classSelect) {
            classSelect.addEventListener('change', function() {
                const modal = this.closest('.modal');
                if (!modal) return;
                const subjectSelect = modal.querySelector('.unit-mirror-subject-select');
                const sectionSelect = modal.querySelector('.unit-mirror-section-select');
                if (!subjectSelect || !sectionSelect) return;
                const classId = this.value;
                subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                sectionSelect.disabled = true;
                if (!classId) {
                    subjectSelect.disabled = true;
                    return;
                }
                const row = this.closest('[data-current-subject-id]');
                const currentSubjectId = row ? row.getAttribute('data-current-subject-id') : null;
                const currentClassId = row ? row.getAttribute('data-current-class-id') : null;
                const filtered = structure.filter(s => String(s.class_id) === String(classId));
                const currentFirst = currentSubjectId && String(currentClassId) === String(classId)
                    ? filtered.find(s => String(s.id) === String(currentSubjectId))
                    : null;
                if (currentFirst) {
                    const opt = document.createElement('option');
                    opt.value = currentFirst.id;
                    opt.textContent = 'المادة الحالية: ' + (currentFirst.stage_name ? currentFirst.stage_name + ' / ' : '') + (currentFirst.class_name ? currentFirst.class_name + ' — ' : '') + currentFirst.name + ' (#' + currentFirst.id + ')';
                    subjectSelect.appendChild(opt);
                }
                filtered.forEach(function(s) {
                    if (currentSubjectId && String(s.id) === String(currentSubjectId)) return;
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = (s.stage_name ? s.stage_name + ' / ' : '') + (s.class_name ? s.class_name + ' — ' : '') + s.name + ' (#' + s.id + ')';
                    subjectSelect.appendChild(opt);
                });
                subjectSelect.disabled = false;
            });
        });
        document.querySelectorAll('.unit-mirror-subject-select').forEach(function(subjectSelect) {
            subjectSelect.addEventListener('change', function() {
                const modal = this.closest('.modal');
                if (!modal) return;
                const sectionSelect = modal.querySelector('.unit-mirror-section-select');
                if (!sectionSelect) return;
                const subjectId = this.value;
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                if (!subjectId) {
                    sectionSelect.disabled = true;
                    return;
                }
                const subject = structure.find(s => String(s.id) === String(subjectId));
                if (subject && subject.sections) {
                    subject.sections.forEach(function(sec) {
                        const opt = document.createElement('option');
                        opt.value = sec.id;
                        opt.textContent = sec.path_title || sec.title || '';
                        sectionSelect.appendChild(opt);
                    });
                    sectionSelect.disabled = false;
                }
            });
        });
        document.querySelectorAll('.add-linked-section-for-unit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const listId = this.getAttribute('data-list-id');
                const list = listId ? document.getElementById(listId) : null;
                const modal = this.closest('.modal');
                if (!list || !modal) return;
                const sectionSelect = modal.querySelector('.unit-mirror-section-select');
                const subjectSelect = modal.querySelector('.unit-mirror-subject-select');
                const classSelect = modal.querySelector('.unit-mirror-class-select');
                const sectionId = sectionSelect ? sectionSelect.value : '';
                const form = modal.querySelector('form[data-unit-home-section-id]');
                const homeSectionId = form ? form.getAttribute('data-unit-home-section-id') : '';
                if (!sectionId) {
                    alert('يرجى اختيار الصف ثم المادة ثم القسم قبل الإضافة');
                    return;
                }
                if (homeSectionId && String(sectionId) === String(homeSectionId)) {
                    alert('لا يمكن إضافة القسم المنزل للوحدة كظهور إضافي');
                    return;
                }
                const existing = list.querySelectorAll('input[name="linked_section_ids[]"]');
                for (let i = 0; i < existing.length; i++) {
                    if (existing[i].value === sectionId) return;
                }
                const subject = structure.find(s => String(s.id) === String(subjectSelect.value));
                let subjectName = ''; let sectionName = ''; let className = ''; let stageName = '';
                if (subject) {
                    subjectName = subject.name || '';
                    className = subject.class_name || '';
                    stageName = subject.stage_name || '';
                    const sec = subject.sections && subject.sections.find(se => String(se.id) === String(sectionId));
                    if (sec) sectionName = (sec.path_title || sec.title || '');
                }
                const badgeText = (stageName ? stageName + ' / ' : '') + (className ? className + ' — ' : '') + subjectName + ' — ' + sectionName;
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center gap-2 mb-1 linked-section-mirror-row';
                row.innerHTML = '<span class="badge bg-secondary">' + badgeText + '</span>' +
                    '<input type="hidden" name="linked_section_ids[]" value="' + sectionId + '">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>';
                list.appendChild(row);
                if (sectionSelect) sectionSelect.value = '';
                if (subjectSelect) subjectSelect.value = '';
                if (classSelect) classSelect.value = '';
                if (sectionSelect) {
                    sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                    sectionSelect.disabled = true;
                }
                if (subjectSelect) {
                    subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                    subjectSelect.disabled = true;
                }
            });
        });

        // مودال ربط الاختبار: تسلسل الصف -> المادة -> القسم -> الوحدة
        var quizLinkClassSelect = document.getElementById('quizLinkClassSelect');
        if (quizLinkClassSelect) {
            quizLinkClassSelect.addEventListener('change', function() {
                var subjectSelect = document.getElementById('quizLinkSubjectSelect');
                var sectionSelect = document.getElementById('quizLinkSectionSelect');
                var unitSelect = document.getElementById('quizLinkUnitSelect');
                if (!subjectSelect || !sectionSelect || !unitSelect) return;
                var classId = this.value;
                subjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                sectionSelect.disabled = true;
                unitSelect.disabled = true;
                if (!classId) { subjectSelect.disabled = true; return; }
                var filtered = structure.filter(s => String(s.class_id) === String(classId));
                filtered.forEach(function(s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name || '';
                    subjectSelect.appendChild(opt);
                });
                subjectSelect.disabled = false;
            });
        }
        var quizLinkSubjectSelect = document.getElementById('quizLinkSubjectSelect');
        if (quizLinkSubjectSelect) {
            quizLinkSubjectSelect.addEventListener('change', function() {
                var sectionSelect = document.getElementById('quizLinkSectionSelect');
                var unitSelect = document.getElementById('quizLinkUnitSelect');
                if (!sectionSelect || !unitSelect) return;
                var subjectId = this.value;
                sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                unitSelect.disabled = true;
                if (!subjectId) { sectionSelect.disabled = true; return; }
                var subject = structure.find(s => String(s.id) === String(subjectId));
                if (subject && subject.sections) {
                    subject.sections.forEach(function(sec) {
                        var opt = document.createElement('option');
                        opt.value = sec.id;
                        opt.textContent = sec.path_title || sec.title || '';
                        sectionSelect.appendChild(opt);
                    });
                    sectionSelect.disabled = false;
                }
            });
        }
        var quizLinkSectionSelect = document.getElementById('quizLinkSectionSelect');
        if (quizLinkSectionSelect) {
            quizLinkSectionSelect.addEventListener('change', function() {
                var unitSelect = document.getElementById('quizLinkUnitSelect');
                var subjectSelect = document.getElementById('quizLinkSubjectSelect');
                var form = document.getElementById('linkQuizUnitsForm');
                if (!unitSelect || !subjectSelect) return;
                var subjectId = subjectSelect.value || null;
                var sectionId = this.value;
                var primaryUnitId = form ? form.getAttribute('data-primary-unit-id') : '';
                unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
                if (!subjectId || !sectionId) { unitSelect.disabled = true; return; }
                var subject = structure.find(s => String(s.id) === String(subjectId));
                if (subject && subject.sections) {
                    var section = subject.sections.find(sec => String(sec.id) === String(sectionId));
                    if (section && section.units) {
                        section.units.forEach(function(u) {
                            if (primaryUnitId && String(u.id) === String(primaryUnitId)) return;
                            var opt = document.createElement('option');
                            opt.value = u.id;
                            opt.textContent = u.title;
                            unitSelect.appendChild(opt);
                        });
                        unitSelect.disabled = false;
                    }
                }
            });
        }
        var addQuizLinkedUnitBtn = document.getElementById('addQuizLinkedUnitBtn');
        if (addQuizLinkedUnitBtn) {
            addQuizLinkedUnitBtn.addEventListener('click', function() {
                var list = document.getElementById('linkedUnitsListQuiz');
                var form = document.getElementById('linkQuizUnitsForm');
                var unitSelect = document.getElementById('quizLinkUnitSelect');
                var subjectSelect = document.getElementById('quizLinkSubjectSelect');
                var sectionSelect = document.getElementById('quizLinkSectionSelect');
                if (!list || !form || !unitSelect) return;
                var unitId = unitSelect.value;
                var primaryUnitId = form.getAttribute('data-primary-unit-id') || '';
                if (!unitId) {
                    alert('يرجى اختيار الصف ثم المادة ثم القسم ثم الوحدة قبل الإضافة');
                    return;
                }
                if (primaryUnitId && String(unitId) === String(primaryUnitId)) return;
                var existing = list.querySelectorAll('input[name="linked_unit_ids[]"]');
                for (var i = 0; i < existing.length; i++) {
                    if (existing[i].value === unitId) return;
                }
                var subject = structure.find(s => String(s.id) === String(subjectSelect.value));
                var subjectName = ''; var sectionName = ''; var unitTitle = '';
                if (subject) {
                    subjectName = subject.name || '';
                    var section = subject.sections && subject.sections.find(sec => String(sec.id) === String(sectionSelect.value));
                    if (section) {
                        sectionName = section.path_title || section.title || '';
                        var u = section.units && section.units.find(ux => String(ux.id) === String(unitId));
                        if (u) unitTitle = u.title || '';
                    }
                }
                var badgeText = subjectName + ' — ' + sectionName + ' — ' + unitTitle;
                var row = document.createElement('div');
                row.className = 'd-flex align-items-center gap-2 mb-1 linked-unit-row';
                row.innerHTML = '<span class="badge bg-secondary">' + badgeText + '</span>' +
                    '<input type="hidden" name="linked_unit_ids[]" value="' + unitId + '">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>';
                list.appendChild(row);
                unitSelect.value = '';
            });
        }
    }

    function bindLessonVideoTypeToggle(scope) {
        var root = scope || document;
        root.querySelectorAll('.lesson-video-type-select').forEach(function(select) {
            if (select.dataset.videoToggleBound === '1') return;
            select.dataset.videoToggleBound = '1';

            var toggleVideoFields = function() {
                var mediaContext = select.dataset.mediaContext || 'unit';
                var mediaId = select.dataset.mediaId || '';
                var prefix = mediaContext === 'section' ? 'sectionVideo' : 'video';
                var urlField = document.getElementById(prefix + 'UrlField' + mediaId);
                var fileField = document.getElementById(prefix + 'FileField' + mediaId);

                if (!urlField || !fileField) return;

                if (select.value === 'upload') {
                    urlField.classList.add('d-none');
                    fileField.classList.remove('d-none');
                } else {
                    urlField.classList.remove('d-none');
                    fileField.classList.add('d-none');
                }
            };

            select.addEventListener('change', toggleVideoFields);
            toggleVideoFields();
        });
    }
    bindLessonVideoTypeToggle(document);

    if (typeof window.initLessonCreateAttachments === 'function') {
        window.initLessonCreateAttachments(document);
    }

    });

    // التحقق من صحة حقول صفحات الكتاب
    document.querySelectorAll('[id^="bookPageFrom"], [name="book_page_from"]').forEach(function(fromField) {
        const unitId = fromField.id ? fromField.id.replace('bookPageFrom', '') : '';
        const toField = unitId 
            ? document.getElementById('bookPageTo' + unitId)
            : fromField.closest('form')?.querySelector('[name="book_page_to"]');
        
        if (!toField) return;

        function validatePages() {
            const fromValue = parseInt(fromField.value);
            const toValue = parseInt(toField.value);
            
            if (fromField.value && toField.value) {
                if (fromValue > toValue) {
                    toField.setCustomValidity('إلى الصفحة يجب أن تكون أكبر من أو تساوي من الصفحة');
                    toField.classList.add('is-invalid');
                } else {
                    toField.setCustomValidity('');
                    toField.classList.remove('is-invalid');
                }
            } else {
                toField.setCustomValidity('');
                toField.classList.remove('is-invalid');
            }
        }

        fromField.addEventListener('input', validatePages);
        fromField.addEventListener('blur', validatePages);
        toField.addEventListener('input', validatePages);
        toField.addEventListener('blur', validatePages);
    });

    // إعادة ترتيب الأقسام / الوحدات / الدروس بالسحب والإفلات
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
    function initializeSortable(scope) {
        if (typeof Sortable === 'undefined') return;

        scope.querySelectorAll('[data-sortable][data-reorder-url]').forEach(function(container) {
            if (container.dataset.sortableInitialized === '1') return;
            container.dataset.sortableInitialized = '1';

            var reorderUrl = container.getAttribute('data-reorder-url');
            var parentIdAttr = container.getAttribute('data-parent-id');
            new Sortable(container, {
                handle: '.sortable-handle',
                animation: 150,
                onEnd: function() {
                    var order = [];
                    var children = container.querySelectorAll(':scope > [data-id]');
                    for (var i = 0; i < children.length; i++) {
                        var id = children[i].getAttribute('data-id');
                        if (id) order.push(id);
                    }
                    var body = { order: order, _token: csrfToken };
                    if (parentIdAttr !== null && parentIdAttr !== undefined && parentIdAttr !== '') {
                        body.parent_id = parentIdAttr;
                    }
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', reorderUrl);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            var indices = container.querySelectorAll(':scope > [data-id] .sortable-index');
                            for (var j = 0; j < indices.length; j++) {
                                indices[j].textContent = j + 1;
                            }
                        } else {
                            var msg = 'فشل حفظ الترتيب.';
                            try {
                                var res = JSON.parse(xhr.responseText);
                                if (res.message) msg = res.message;
                            } catch (e) {}
                            alert(msg);
                        }
                    };
                    xhr.onerror = function() {
                        alert('فشل حفظ الترتيب. تحقق من الاتصال.');
                    };
                    xhr.send(JSON.stringify(body));
                }
            });
        });
    }
    initializeSortable(document);

    var accordionStateStorageKey = 'admin.subjects.show.open-collapses.{{ $subject->id }}';

    function isTrackedCollapseElement(el) {
        if (!el || !el.id) return false;
        return el.id.indexOf('sectionCollapse') === 0 || el.id.indexOf('unitCollapse') === 0;
    }

    function getStoredOpenCollapseIds() {
        try {
            var raw = sessionStorage.getItem(accordionStateStorageKey);
            if (!raw) return [];
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.filter(function(id) { return typeof id === 'string' && id.length > 0; }) : [];
        } catch (e) {
            return [];
        }
    }

    function setStoredOpenCollapseIds(ids) {
        try {
            var uniqueIds = Array.from(new Set((ids || []).filter(function(id) { return typeof id === 'string' && id.length > 0; })));
            sessionStorage.setItem(accordionStateStorageKey, JSON.stringify(uniqueIds));
        } catch (e) {}
    }

    function pruneAccordionStateToExistingDom() {
        var ids = getStoredOpenCollapseIds();
        var kept = ids.filter(function(id) {
            var el = document.getElementById(id);
            return !!el && isTrackedCollapseElement(el);
        });
        setStoredOpenCollapseIds(kept);
        return kept;
    }

    function syncAccordionStateFromDom() {
        var openEls = document.querySelectorAll('.accordion-collapse.show[id^="sectionCollapse"], .accordion-collapse.show[id^="unitCollapse"]');
        var ids = [];
        openEls.forEach(function(el) {
            if (el.id) ids.push(el.id);
        });
        setStoredOpenCollapseIds(ids);
    }

    function restoreAccordionState(root) {
        if (typeof bootstrap === 'undefined' || !bootstrap.Collapse) return;
        var scope = root || document;
        var ids = pruneAccordionStateToExistingDom();
        ids.forEach(function(id) {
            var el = document.getElementById(id);
            if (!el || (scope !== document && !scope.contains(el))) return;
            if (!el || !isTrackedCollapseElement(el) || el.classList.contains('show')) return;
            try {
                var instance = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
                instance.show();
            } catch (e) {}
        });
    }

    document.addEventListener('shown.bs.collapse', function(e) {
        var collapseEl = e.target;
        if (!isTrackedCollapseElement(collapseEl)) return;
        var ids = getStoredOpenCollapseIds().filter(function(id) { return id !== collapseEl.id; });
        ids.push(collapseEl.id);
        setStoredOpenCollapseIds(ids);
    });

    document.addEventListener('hidden.bs.collapse', function(e) {
        var collapseEl = e.target;
        if (!isTrackedCollapseElement(collapseEl)) return;
        var ids = getStoredOpenCollapseIds().filter(function(id) { return id !== collapseEl.id; });
        setStoredOpenCollapseIds(ids);
    });

    var closeAllAccordionsBtn = document.getElementById('closeAllAccordionsBtn');
    if (closeAllAccordionsBtn) {
        closeAllAccordionsBtn.addEventListener('click', function() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Collapse) return;
            var openEls = document.querySelectorAll('.accordion-collapse.show[id^="sectionCollapse"], .accordion-collapse.show[id^="unitCollapse"]');
            openEls.forEach(function(el) {
                try {
                    var instance = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
                    instance.hide();
                } catch (e) {}
            });
            setTimeout(function() {
                syncAccordionStateFromDom();
            }, 0);
        });
    }

    restoreAccordionState(document);
    syncAccordionStateFromDom();

    function showAjaxLessonAlert(type, message, errors) {
        var alertsHost = document.getElementById('ajaxLessonAlerts');
        if (!alertsHost) return;
        var fieldNameMap = {
            attachment_title: 'عنوان المرفق',
            attachment_files: 'ملفات المرفقات',
            'attachment_files.0': 'ملفات المرفقات',
            attachment_file: 'ملف المرفق',
            attachment_url: 'رابط المرفق',
            attachment_description: 'وصف المرفق',
            file: 'ملف المرفق',
            url: 'رابط المرفق',
            type: 'نوع المرفق',
            title: 'العنوان',
        };
        var icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        var html = '<div class="alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show" role="alert">' +
            '<i class="bi ' + icon + ' me-2"></i>' + message;
        if (errors && Object.keys(errors).length) {
            html += '<ul class="mb-0 mt-2">';
            Object.keys(errors).forEach(function(field) {
                var fieldErrors = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                var readableField = fieldNameMap[field]
                    || (field.indexOf('attachment_files.') === 0 ? 'ملفات المرفقات' : field);
                fieldErrors.forEach(function(err) {
                    html += '<li><strong>' + readableField + ':</strong> ' + err + '</li>';
                });
            });
            html += '</ul>';
        }
        html += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button></div>';
        alertsHost.innerHTML = html;
    }

    function closeFormModal(form) {
        var modal = form.closest('.modal');
        if (!modal || typeof bootstrap === 'undefined') return;
        var instance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        instance.hide();
    }

    async function refreshUnitContentAndModals(unitId, deletedLessonId) {
        if (!unitId) return;
        syncAccordionStateFromDom();
        var response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) return;
        var html = await response.text();
        var parser = new DOMParser();
        var newDoc = parser.parseFromString(html, 'text/html');

        var currentUnitContent = document.querySelector('[data-unit-content-id="' + unitId + '"]');
        var nextUnitContent = newDoc.querySelector('[data-unit-content-id="' + unitId + '"]');
        if (currentUnitContent && nextUnitContent) {
            currentUnitContent.replaceWith(nextUnitContent);
            initializeSortable(nextUnitContent);
        }

        var createModalId = 'createLessonModal' + unitId;
        var currentCreateModal = document.getElementById(createModalId);
        var nextCreateModal = newDoc.getElementById(createModalId);
        if (currentCreateModal && nextCreateModal) {
            currentCreateModal.replaceWith(nextCreateModal);
            if (typeof window.initLessonCreateAttachments === 'function') {
                window.initLessonCreateAttachments(nextCreateModal);
            }
        }

        var lessonRows = document.querySelectorAll('[data-unit-content-id="' + unitId + '"] [data-lesson-id]');
        lessonRows.forEach(function(row) {
            var lessonId = row.getAttribute('data-lesson-id');
            ['editLesson', 'deleteLesson', 'addLessonAttachment', 'playVideoModal', 'approveLesson', 'rejectLesson'].forEach(function(prefix) {
                var id = prefix + lessonId;
                var existing = document.getElementById(id);
                var incoming = newDoc.getElementById(id);
                if (existing && incoming) {
                    existing.replaceWith(incoming);
                } else if (!existing && incoming) {
                    document.body.appendChild(incoming);
                }
            });
        });

        if (deletedLessonId) {
            ['editLesson', 'deleteLesson', 'addLessonAttachment', 'playVideoModal', 'approveLesson', 'rejectLesson'].forEach(function(prefix) {
                var stale = document.getElementById(prefix + deletedLessonId);
                if (stale) stale.remove();
            });
        }

        restoreAccordionState(document);
        pruneAccordionStateToExistingDom();
        bindLessonVideoTypeToggle(document);
        if (typeof window.initLessonCreateAttachments === 'function') {
            window.initLessonCreateAttachments(document);
        }
    }

    async function refreshSectionContentAndModals(sectionId, deletedLessonId) {
        if (!sectionId) return;
        syncAccordionStateFromDom();
        var response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) return;
        var html = await response.text();
        var parser = new DOMParser();
        var newDoc = parser.parseFromString(html, 'text/html');

        var sectionSelector = '.accordion-item[data-section-id="' + sectionId + '"]';
        var currentSectionItem = document.querySelector(sectionSelector);
        var nextSectionItem = newDoc.querySelector(sectionSelector);
        if (currentSectionItem && nextSectionItem) {
            currentSectionItem.replaceWith(nextSectionItem);
            initializeSortable(nextSectionItem);
        }

        var sectionModalId = 'createSectionLessonModal' + sectionId;
        var currentSectionModal = document.getElementById(sectionModalId);
        var nextSectionModal = newDoc.getElementById(sectionModalId);
        if (currentSectionModal && nextSectionModal) {
            currentSectionModal.replaceWith(nextSectionModal);
            bindLessonVideoTypeToggle(nextSectionModal);
            if (typeof window.initLessonCreateAttachments === 'function') {
                window.initLessonCreateAttachments(nextSectionModal);
            }
        }

        if (deletedLessonId) {
            ['editLesson', 'deleteLesson', 'addLessonAttachment', 'playVideoModal', 'approveLesson', 'rejectLesson'].forEach(function(prefix) {
                var stale = document.getElementById(prefix + deletedLessonId);
                if (stale) stale.remove();
            });
        }

        restoreAccordionState(document);
        pruneAccordionStateToExistingDom();
        bindLessonVideoTypeToggle(document);
        if (typeof window.initLessonCreateAttachments === 'function') {
            window.initLessonCreateAttachments(document);
        }
    }

    document.addEventListener('submit', async function(e) {
        var form = e.target.closest('.js-lesson-ajax-form');
        if (!form) return;
        e.preventDefault();

        var submitBtn = form.querySelector('button[type="submit"]');
        var originalBtnHtml = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>جارٍ الحفظ...';
        }

        try {
            var formData = new FormData(form);
            var result = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            var payload = await result.json().catch(function() { return {}; });
            if (!result.ok || payload.success === false) {
                if (result.status === 422) {
                    showAjaxLessonAlert('error', 'يرجى تصحيح البيانات ثم المحاولة مرة أخرى.', payload.errors || {});
                } else {
                    showAjaxLessonAlert('error', payload.message || 'فشل حفظ الدرس على الخادم. يرجى المحاولة مرة أخرى.');
                }
                return;
            }

            closeFormModal(form);
            showAjaxLessonAlert('success', payload.message || (payload.submitted_for_review ? 'تم حفظ الدرس وإرساله للمراجعة.' : 'تم حفظ الدرس بنجاح.'));

            try {
                var updatedUnitId = payload.unit_id || form.dataset.unitId;
                var updatedSectionId = payload.section_id || form.dataset.sectionId;
                var deletedLessonId = form.dataset.lessonAction === 'destroy' ? (payload.lesson_id || form.dataset.lessonId) : null;

                if (updatedUnitId) {
                    await refreshUnitContentAndModals(updatedUnitId, deletedLessonId);
                } else if (updatedSectionId) {
                    await refreshSectionContentAndModals(updatedSectionId, deletedLessonId);
                }
            } catch (refreshError) {
                console.error('Partial UI refresh failed after successful lesson save:', refreshError);
                showAjaxLessonAlert(
                    'success',
                    'تم حفظ الدرس بنجاح، ولكن تعذر تحديث العرض تلقائيًا. <a href="' + window.location.href + '" class="alert-link">اضغط هنا لتحديث الصفحة</a>.'
                );
            }
        } catch (error) {
            console.error('Lesson save request failed:', error);
            showAjaxLessonAlert('error', 'تعذر إرسال طلب الحفظ حالياً. تحقق من الاتصال ثم أعد المحاولة.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        }
    });

    // إيقاف الفيديو عند إغلاق مودال المعاينة (يوتيوب/فيميو/HTML5) واستعادته عند الفتح
    document.querySelectorAll('[id^="playVideoModal"]').forEach(function(modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function(e) {
            var modal = e.target;
            var iframe = modal.querySelector('iframe');
            if (iframe && iframe.src) {
                iframe.setAttribute('data-video-src', iframe.src);
                iframe.src = '';
            }
            var video = modal.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });
        modalEl.addEventListener('show.bs.modal', function(e) {
            var modal = e.target;
            var iframe = modal.querySelector('iframe');
            if (iframe) {
                var saved = iframe.getAttribute('data-video-src');
                if (saved) {
                    iframe.src = saved;
                }
            }
        });
    });
});
</script>
@include('admin.pages.lessons.partials.attachment-modals-script')
@include('admin.pages.lessons.partials.lesson-create-attachments-script')
@stop

