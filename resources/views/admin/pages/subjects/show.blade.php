@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المادة الدراسية
@stop

@section('css')
<style>
    .btn-purple {
        background-color: #6259ca;
        border-color: #6259ca;
        color: #fff;
    }
    .btn-purple:hover {
        background-color: #524abb;
        border-color: #524abb;
        color: #fff;
    }
    .btn-purple:focus, .btn-purple:active {
        background-color: #4a42a7;
        border-color: #4a42a7;
        color: #fff;
    }
    .bg-purple-transparent {
        background-color: rgba(98, 89, 202, 0.1);
    }
    .text-purple {
        color: #6259ca !important;
    }
    .questions-list-container {
        max-height: 400px;
        overflow-y: auto;
    }
    .questions-list-container .list-group-item:hover {
        background-color: rgba(98, 89, 202, 0.05);
    }
    .questions-list-container .form-check-input:checked + .flex-grow-1 {
        background-color: rgba(98, 89, 202, 0.05);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
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

            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تفاصيل المادة: {{ $subject->name }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn btn-warning btn-sm text-white">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="{{ $subject->image ? asset('storage/'.$subject->image) : asset('assets/images/media/media-22.jpg') }}"
                                     alt="{{ $subject->name }}"
                                     class="rounded"
                                     style="width: 180px; height: 180px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold mb-1">{{ $subject->name }}</h5>
                            <p class="mb-1 text-muted">
                                الصف: {{ $subject->schoolClass?->name ?? '-' }}
                                @if($subject->schoolClass && $subject->schoolClass->stage)
                                    <span class="d-block small">
                                        ({{ $subject->schoolClass->stage->name }})
                                    </span>
                                @endif
                            </p>
                            <p class="mb-1">
                                @if ($subject->is_active)
                                    <span class="badge bg-success">مادة نشطة</span>
                                @else
                                    <span class="badge bg-danger">غير نشطة</span>
                                @endif
                            </p>
                            <p class="mb-1">
                                @if ($subject->display_in_class)
                                    <span class="badge bg-info text-dark">تظهر في صفحة الصف</span>
                                @else
                                    <span class="badge bg-secondary">لا تظهر في صفحة الصف</span>
                                @endif
                            </p>
                            <p class="text-muted mb-0">
                                ترتيب العرض: <span class="fw-semibold">{{ $subject->order }}</span>
                            </p>
                        </div>
                    </div>

                    @if ($subject->meta_title || $subject->meta_description || $subject->meta_keywords || $subject->og_image)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">بيانات الـ SEO</h6>
                            </div>
                            <div class="card-body">
                                @if ($subject->meta_title)
                                    <p class="mb-2"><span class="fw-semibold">Meta Title: </span>{{ $subject->meta_title }}</p>
                                @endif
                                @if ($subject->meta_description)
                                    <p class="mb-2"><span class="fw-semibold">Meta Description: </span>{{ $subject->meta_description }}</p>
                                @endif
                                @if ($subject->meta_keywords)
                                    <p class="mb-2"><span class="fw-semibold">Meta Keywords: </span>{{ $subject->meta_keywords }}</p>
                                @endif
                                @if ($subject->og_image)
                                    <div class="mt-2">
                                        <span class="fw-semibold d-block mb-1">صورة Open Graph:</span>
                                        <img src="{{ asset('storage/'.$subject->og_image) }}" alt="{{ $subject->name }}"
                                             class="rounded" style="width: 160px; height: 160px; object-fit: cover;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-xl-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات المادة</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                <span class="fw-semibold">الوصف:</span>
                                <br>
                                {{ $subject->description ?: 'لا يوجد وصف متاح لهذه المادة حالياً.' }}
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">الرابط الدائم:</span>
                                {{ $subject->slug ?: 'لم يتم تعيين رابط دائم' }}
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">تاريخ الإنشاء:</span>
                                {{ $subject->created_at?->format('Y-m-d H:i') }}
                            </p>
                            <p class="mb-0">
                                <span class="fw-semibold">تاريخ آخر تحديث:</span>
                                {{ $subject->updated_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- أقسام المادة (مثل أقسام الكورس في Moodle) --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-collection me-2"></i>
                                أقسام المادة
                            </h6>
                            <button type="button"
                                    class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createSectionModal">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة قسم جديد
                            </button>
                        </div>
                        <div class="card-body">
                            @if($subject->sections->count() === 0)
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-folder2-open display-4 text-muted"></i>
                                    </div>
                                    <p class="text-muted mb-0">لا توجد أقسام لهذه المادة حالياً</p>
                                    <p class="text-muted small">يمكنك إنشاء أول قسم من زر "إضافة قسم جديد"</p>
                                </div>
                            @else
                                <div class="accordion accordion-primary" id="subjectSectionsAccordion">
                                    @foreach($subject->sections as $index => $section)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="sectionHeading{{ $section->id }}">
                                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#sectionCollapse{{ $section->id }}"
                                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                                        aria-controls="sectionCollapse{{ $section->id }}">
                                                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-folder-fill text-primary me-2"></i>
                                                            <span class="fw-semibold">{{ $section->title }}</span>
                                                            @if($section->is_active)
                                                                <span class="badge bg-success-transparent text-success ms-2">نشط</span>
                                                            @else
                                                                <span class="badge bg-secondary-transparent text-secondary ms-2">مخفي</span>
                                                            @endif
                                                        </div>
                                                        <span class="badge bg-primary-transparent text-primary me-2">
                                                            ترتيب: {{ $section->order }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="sectionCollapse{{ $section->id }}"
                                                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                                 aria-labelledby="sectionHeading{{ $section->id }}"
                                                 data-bs-parent="#subjectSectionsAccordion">
                                                <div class="accordion-body">
                                                    {{-- وصف القسم --}}
                                                    @if($section->description)
                                                        <p class="text-muted mb-3">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            {{ $section->description }}
                                                        </p>
                                                    @endif

                                                    {{-- الوحدات داخل القسم --}}
                                                    <div class="section-units">
                                                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                                            <span class="text-muted small">
                                                                <i class="bi bi-layers me-1"></i>
                                                                الوحدات ({{ $section->units->count() }})
                                                            </span>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#createUnitModal{{ $section->id }}">
                                                                <i class="bi bi-plus-lg me-1"></i> إضافة وحدة
                                                            </button>
                                                        </div>

                                                        @if($section->units->count() === 0)
                                                            <div class="text-center py-4 text-muted">
                                                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                                                <span class="small">لا توجد وحدات في هذا القسم بعد</span>
                                                            </div>
                                                        @else
                                                            {{-- Accordion للوحدات --}}
                                                            <div class="accordion accordion-secondary" id="unitsAccordion{{ $section->id }}">
                                                                @foreach($section->units as $unitIndex => $unit)
                                                                    <div class="accordion-item border rounded mb-2 shadow-sm">
                                                                        <h2 class="accordion-header" id="unitHeading{{ $unit->id }}">
                                                                            <button class="accordion-button collapsed py-3" type="button"
                                                                                    data-bs-toggle="collapse"
                                                                                    data-bs-target="#unitCollapse{{ $unit->id }}"
                                                                                    aria-expanded="false"
                                                                                    aria-controls="unitCollapse{{ $unit->id }}">
                                                                                <div class="d-flex align-items-center w-100 me-3">
                                                                                    <div class="me-3">
                                                                                        <div class="avatar avatar-md bg-info-transparent text-info rounded">
                                                                                            <i class="bi bi-journal-text fs-5"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="flex-grow-1">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <span class="fw-semibold">{{ $unit->title }}</span>
                                                                                            @if($unit->is_active)
                                                                                                <span class="badge bg-success-transparent text-success ms-2">نشط</span>
                                                                                            @else
                                                                                                <span class="badge bg-secondary-transparent text-secondary ms-2">مخفي</span>
                                                                                            @endif
                                                                                        </div>
                                                                                        @if($unit->description)
                                                                                            <p class="text-muted small mb-0 mt-1">{{ Str::limit($unit->description, 60) }}</p>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="me-3">
                                                                                        <span class="badge bg-info-transparent text-info">
                                                                                            <i class="bi bi-play-circle me-1"></i> {{ $unit->lessons->count() }} درس
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </button>
                                                                        </h2>
                                                                        <div id="unitCollapse{{ $unit->id }}"
                                                                             class="accordion-collapse collapse"
                                                                             aria-labelledby="unitHeading{{ $unit->id }}"
                                                                             data-bs-parent="#unitsAccordion{{ $section->id }}">
                                                                            <div class="accordion-body pt-0">
                                                                                {{-- شريط أدوات الوحدة --}}
                                                                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                                        <button type="button" class="btn btn-sm btn-success"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#createLessonModal{{ $unit->id }}"
                                                                                                title="إضافة درس">
                                                                                            <i class="bi bi-play-circle me-1"></i> درس جديد
                                                                                        </button>
                                                                                        {{-- اختبار عام للوحدة --}}
                                                                                        <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id, 'unit_id' => $unit->id, 'scope' => 'unit']) }}" class="btn btn-sm btn-info" title="إضافة اختبار للوحدة">
                                                                                            <i class="bi bi-clipboard-check me-1"></i> اختبار الوحدة
                                                                                        </a>
                                                                                        <div class="btn-group">
                                                                                            <button type="button" class="btn btn-sm btn-purple dropdown-toggle" 
                                                                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                                                                    title="إدارة الأسئلة">
                                                                                                <i class="bi bi-question-circle me-1"></i> الأسئلة
                                                                                                @if($unit->questions->count() > 0)
                                                                                                    <span class="badge bg-light text-purple ms-1">{{ $unit->questions->count() }}</span>
                                                                                                @endif
                                                                                            </button>
                                                                                            <ul class="dropdown-menu">
                                                                                                <li>
                                                                                                    <button type="button" class="dropdown-item" 
                                                                                                            data-bs-toggle="modal" 
                                                                                                            data-bs-target="#importQuestionsModal{{ $unit->id }}">
                                                                                                        <i class="bi bi-download me-2 text-primary"></i> استيراد أسئلة من البنك
                                                                                                    </button>
                                                                                                </li>
                                                                                                <li>
                                                                                                    <a class="dropdown-item" href="{{ route('admin.questions.create', ['unit_id' => $unit->id]) }}">
                                                                                                        <i class="bi bi-plus-circle me-2 text-success"></i> إنشاء سؤال جديد
                                                                                                    </a>
                                                                                                </li>
                                                                                                @if($unit->questions->count() > 0)
                                                                                                    <li><hr class="dropdown-divider"></li>
                                                                                                    <li>
                                                                                                        <button type="button" class="dropdown-item"
                                                                                                                data-bs-toggle="modal"
                                                                                                                data-bs-target="#viewQuestionsModal{{ $unit->id }}">
                                                                                                            <i class="bi bi-eye me-2 text-info"></i> عرض الأسئلة ({{ $unit->questions->count() }})
                                                                                                        </button>
                                                                                                    </li>
                                                                                                @endif
                                                                                            </ul>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="d-flex align-items-center gap-1">
                                                                                        <button type="button"
                                                                                                class="btn btn-sm btn-outline-primary"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#editUnit{{ $unit->id }}"
                                                                                                title="تعديل الوحدة">
                                                                                            <i class="bi bi-pencil me-1"></i> تعديل
                                                                                        </button>
                                                                                        <button type="button"
                                                                                                class="btn btn-sm btn-outline-danger"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#deleteUnit{{ $unit->id }}"
                                                                                                title="حذف الوحدة">
                                                                                            <i class="bi bi-trash"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>

                                                                                {{-- الأسئلة المرتبطة بالوحدة --}}
                                                                                @if($unit->questions->count() > 0)
                                                                                <div class="unit-questions mb-3">
                                                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                                                        <h6 class="mb-0 text-purple fw-semibold small">
                                                                                            <i class="bi bi-question-circle me-1"></i>
                                                                                            أسئلة الوحدة ({{ $unit->questions->count() }})
                                                                                        </h6>
                                                                                    </div>
                                                                                    <div class="list-group list-group-flush">
                                                                                        @foreach($unit->questions as $question)
                                                                                        <div class="list-group-item d-flex align-items-center justify-content-between px-2 py-2 bg-purple-transparent rounded mb-1">
                                                                                            <div class="d-flex align-items-center flex-grow-1">
                                                                                                <div class="bg-{{ $question->type_color }} rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                                                                                    <i class="bi {{ $question->type_icon }} text-white small"></i>
                                                                                                </div>
                                                                                                <div class="flex-grow-1">
                                                                                                    <p class="mb-0 small fw-medium">{{ Str::limit($question->title, 60) }}</p>
                                                                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                                                                        <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}" style="font-size:0.6rem;">
                                                                                                            {{ $question->type_name }}
                                                                                                        </span>
                                                                                                        <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}" style="font-size:0.6rem;">
                                                                                                            {{ $question->difficulty_name }}
                                                                                                        </span>
                                                                                                        <span class="text-muted" style="font-size:0.65rem;">
                                                                                                            {{ $question->default_points }} نقطة
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="d-flex align-items-center gap-1">
                                                                                                <a href="{{ route('admin.questions.show', $question->id) }}" 
                                                                                                   class="btn btn-sm btn-icon btn-primary-transparent" title="عرض">
                                                                                                    <i class="bi bi-eye"></i>
                                                                                                </a>
                                                                                                <a href="{{ route('admin.questions.edit', $question->id) }}" 
                                                                                                   class="btn btn-sm btn-icon btn-warning-transparent" title="تعديل">
                                                                                                    <i class="bi bi-pencil"></i>
                                                                                                </a>
                                                                                                <form action="{{ route('admin.units.questions.detach', [$unit->id, $question->id]) }}" 
                                                                                                      method="POST" class="d-inline"
                                                                                                      onsubmit="return confirm('هل أنت متأكد من فك ربط هذا السؤال؟')">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="btn btn-sm btn-icon btn-danger-transparent" title="فك الربط">
                                                                                                        <i class="bi bi-x-lg"></i>
                                                                                                    </button>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                @endif

                                                                                {{-- الاختبارات المرتبطة بالوحدة (عامة) --}}
                                                                                @if($unit->unitQuizzes && $unit->unitQuizzes->count() > 0)
                                                                                <div class="unit-quizzes mb-3">
                                                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                                                        <h6 class="mb-0 text-info fw-semibold small">
                                                                                            <i class="bi bi-clipboard-check me-1"></i>
                                                                                            اختبارات الوحدة ({{ $unit->unitQuizzes->count() }})
                                                                                        </h6>
                                                                                    </div>
                                                                                    <div class="list-group list-group-flush">
                                                                                        @foreach($unit->unitQuizzes as $quiz)
                                                                                        <div class="list-group-item d-flex align-items-center justify-content-between px-2 py-2 bg-info-transparent rounded mb-1">
                                                                                            <div class="d-flex align-items-center flex-grow-1">
                                                                                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                                                                                    <i class="bi bi-clipboard-check text-white small"></i>
                                                                                                </div>
                                                                                                <div class="flex-grow-1">
                                                                                                    <p class="mb-0 small fw-medium">{{ $quiz->title }}</p>
                                                                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                                                                        @if($quiz->is_published)
                                                                                                            <span class="badge bg-success-transparent text-success" style="font-size:0.6rem;">منشور</span>
                                                                                                        @else
                                                                                                            <span class="badge bg-warning-transparent text-warning" style="font-size:0.6rem;">غير منشور</span>
                                                                                                        @endif
                                                                                                        <span class="text-muted" style="font-size:0.65rem;">
                                                                                                            <i class="bi bi-question-circle me-1"></i>{{ $quiz->questions_count ?? $quiz->questions->count() }} سؤال
                                                                                                        </span>
                                                                                                        @if($quiz->duration_minutes)
                                                                                                        <span class="text-muted" style="font-size:0.65rem;">
                                                                                                            <i class="bi bi-clock me-1"></i>{{ $quiz->duration_minutes }} دقيقة
                                                                                                        </span>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="d-flex align-items-center gap-1">
                                                                                                <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                                                                                                   class="btn btn-sm btn-icon btn-info-transparent" title="عرض">
                                                                                                    <i class="bi bi-eye"></i>
                                                                                                </a>
                                                                                                <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" 
                                                                                                   class="btn btn-sm btn-icon btn-warning-transparent" title="تعديل">
                                                                                                    <i class="bi bi-pencil"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                @endif

                                                                                {{-- محتويات الوحدة (الدروس) --}}
                                                                                <div class="unit-content">
                                                                                    @if($unit->lessons->count() === 0 && $unit->questions->count() === 0)
                                                                                        <div class="text-center py-4 text-muted bg-light rounded">
                                                                                            <i class="bi bi-collection-play display-6 d-block mb-2"></i>
                                                                                            <span class="small">لا توجد محتويات في هذه الوحدة بعد</span>
                                                                                            <p class="small text-muted mb-0 mt-1">اضغط على "درس جديد" أو "الأسئلة" لإضافة محتوى</p>
                                                                                        </div>
                                                                                    @elseif($unit->lessons->count() === 0)
                                                                                        {{-- لا شيء - الأسئلة موجودة أعلاه --}}
                                                                                    @else
                                                                                        <div class="list-group list-group-flush">
                                                                                            @foreach($unit->lessons as $lesson)
                                                                                                <div class="list-group-item d-flex flex-column px-0 py-2">
                                                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <div class="me-3 position-relative">
                                                                                                            @if($lesson->thumbnail)
                                                                                                                <img src="{{ asset('storage/'.$lesson->thumbnail) }}" 
                                                                                                                     alt="{{ $lesson->title }}"
                                                                                                                     class="rounded" 
                                                                                                                     style="width:60px;height:40px;object-fit:cover;">
                                                                                                            @else
                                                                                                                <div class="bg-danger-transparent text-danger rounded d-flex align-items-center justify-content-center" 
                                                                                                                     style="width:60px;height:40px;">
                                                                                                                    <i class="bi bi-play-circle fs-4"></i>
                                                                                                                </div>
                                                                                                            @endif
                                                                                                            @if($lesson->is_free)
                                                                                                                <span class="badge bg-success position-absolute top-0 start-0" style="font-size:0.6rem;">مجاني</span>
                                                                                                            @endif
                                                                                                        </div>
<div>
                                                                                                            <h6 class="mb-0 fw-semibold small">
                                                                                                                {{ $lesson->title }}
                                                                                                                @if(!$lesson->is_active)
                                                                                                                    <span class="badge bg-secondary-transparent text-secondary ms-1">مخفي</span>
                                                                                                                @endif
                                                                                                            </h6>
                                                                                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                                                                                <span class="badge bg-{{ $lesson->video_type === 'youtube' ? 'danger' : ($lesson->video_type === 'vimeo' ? 'info' : 'primary') }}-transparent text-{{ $lesson->video_type === 'youtube' ? 'danger' : ($lesson->video_type === 'vimeo' ? 'info' : 'primary') }}" style="font-size:0.65rem;">
                                                                                                                    <i class="bi bi-{{ $lesson->video_type === 'youtube' ? 'youtube' : ($lesson->video_type === 'vimeo' ? 'vimeo' : 'film') }} me-1"></i>
                                                                                                                    {{ \App\Models\Lesson::VIDEO_TYPES[$lesson->video_type] ?? $lesson->video_type }}
                                                                                                                </span>
                                                                                                                @if($lesson->duration)
                                                                                                                    <span class="text-muted" style="font-size:0.7rem;">
                                                                                                                        <i class="bi bi-clock me-1"></i>{{ $lesson->formatted_duration }}
                                                                                                                    </span>
                                                                                                                @endif
                                                                                                                @if($lesson->attachments->count() > 0)
                                                                                                                    <span class="text-muted" style="font-size:0.7rem;">
                                                                                                                        <i class="bi bi-paperclip me-1"></i>{{ $lesson->attachments->count() }}
                                                                                                                    </span>
                                                                                                                @endif
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                                                                                        <div class="d-flex align-items-center gap-1">
                                                                                                            <a href="{{ route('admin.lessons.show', $lesson->id) }}" 
                                                                                                               class="btn btn-sm btn-icon btn-success-transparent" title="مشاهدة">
                                                                                                                <i class="bi bi-play-fill"></i>
                                                                                                            </a>
                                                                                                            <button type="button"
                                                                                                                    class="btn btn-sm btn-icon btn-info-transparent"
                                                                                                                    data-bs-toggle="modal"
                                                                                                                    data-bs-target="#addLessonAttachment{{ $lesson->id }}"
                                                                                                                    title="إضافة مرفقات">
                                                                                                                <i class="bi bi-paperclip"></i>
                                                                                                            </button>
                                                                                                            <button type="button"
                                                                                                                    class="btn btn-sm btn-icon btn-primary-transparent"
                                                                                                                    data-bs-toggle="modal"
                                                                                                                    data-bs-target="#editLesson{{ $lesson->id }}"
                                                                                                                    title="تعديل">
                                                                                                                <i class="bi bi-pencil"></i>
                                                                                                            </button>
                                                                                                            <button type="button"
                                                                                                                    class="btn btn-sm btn-icon btn-danger-transparent"
                                                                                                                    data-bs-toggle="modal"
                                                                                                                    data-bs-target="#deleteLesson{{ $lesson->id }}"
                                                                                                                    title="حذف">
                                                                                                                <i class="bi bi-trash"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                        {{-- زر إنشاء اختبار لهذا الدرس --}}
                                                                                                        <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id, 'unit_id' => $unit->id, 'lesson_id' => $lesson->id, 'scope' => 'lesson']) }}" 
                                                                                                           class="btn btn-sm btn-outline-info" title="اختبار لهذا الدرس">
                                                                                                            <i class="bi bi-clipboard-check me-1"></i> اختبار الدرس
                                                                                                        </a>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- أزرار التحكم بالقسم --}}
                                                    <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editSection{{ $section->id }}">
                                                            <i class="bi bi-pencil me-1"></i> تعديل القسم
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteSection{{ $section->id }}">
                                                            <i class="bi bi-trash me-1"></i> حذف
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال إنشاء قسم جديد --}}
    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">إضافة قسم جديد للمادة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form action="{{ route('admin.subjects.sections.store', $subject->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
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

    {{-- مودالات تعديل / حذف الأقسام --}}
    @foreach($subject->sections as $section)
        {{-- تعديل قسم --}}
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

        {{-- حذف قسم --}}
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

        {{-- مودال إنشاء وحدة جديدة --}}
        <div class="modal fade" id="createUnitModal{{ $section->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-layers text-primary me-2"></i>
                            إضافة وحدة جديدة
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form action="{{ route('admin.sections.units.store', $section->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
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

        {{-- مودالات تعديل وحذف الوحدات --}}
        @foreach($section->units as $unit)
            {{-- تعديل وحدة --}}
            <div class="modal fade" id="editUnit{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-pencil text-primary me-2"></i>
                                تعديل الوحدة
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.units.update', $unit->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
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

            {{-- حذف وحدة --}}
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

            {{-- مودال إنشاء درس جديد --}}
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
                        <form action="{{ route('admin.units.lessons.store', $unit->id) }}" method="POST" enctype="multipart/form-data">
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
                                            <select name="video_type" class="form-select" id="videoType{{ $unit->id }}" required>
                                                <option value="youtube">يوتيوب</option>
                                                <option value="vimeo">فيميو</option>
                                                <option value="external">رابط خارجي</option>
                                                <option value="upload">رفع ملف</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3" id="videoUrlField{{ $unit->id }}">
                                    <label class="form-label">رابط الفيديو</label>
                                    <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                                    <small class="text-muted">الصق رابط الفيديو من YouTube أو Vimeo أو أي مصدر خارجي</small>
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
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="lessonActive{{ $unit->id }}" checked>
                                            <label class="form-check-label" for="lessonActive{{ $unit->id }}">الدرس نشط</label>
                                        </div>
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
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg me-1"></i> حفظ الدرس
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- مودالات تعديل وحذف الدروس --}}
            @foreach($unit->lessons as $lesson)
                {{-- تعديل درس --}}
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
                            <form action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data">
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
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" {{ $lesson->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">الدرس نشط</label>
                                            </div>
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

                {{-- حذف درس --}}
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
                            <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST">
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

                {{-- مودال إضافة مرفقات للدرس --}}
                <div class="modal fade" id="addLessonAttachment{{ $lesson->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0 bg-info-transparent">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-paperclip text-info me-2"></i>
                                    إضافة مرفق للدرس
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <form action="{{ route('admin.lessons.attachments.store', $lesson->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="alert alert-light border mb-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>الدرس:</strong> {{ $lesson->title }}
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">عنوان المرفق <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" placeholder="مثال: ملف PDF للشرح" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">نوع المرفق <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select attachment-type-select" data-lesson="{{ $lesson->id }}" required>
                                            <option value="file">ملف (PDF, Word, ZIP...)</option>
                                            <option value="document">مستند</option>
                                            <option value="image">صورة</option>
                                            <option value="audio">ملف صوتي</option>
                                            <option value="link">رابط خارجي</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 file-field-{{ $lesson->id }}">
                                        <label class="form-label">الملف</label>
                                        <input type="file" name="file" class="form-control">
                                        <small class="text-muted">الحد الأقصى: 50 ميجابايت</small>
                                    </div>

                                    <div class="mb-3 url-field-{{ $lesson->id }}" style="display: none;">
                                        <label class="form-label">الرابط <span class="text-danger">*</span></label>
                                        <input type="url" name="url" class="form-control" placeholder="https://example.com/resource">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">وصف المرفق (اختياري)</label>
                                        <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للمرفق..."></textarea>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_downloadable" checked>
                                        <label class="form-check-label">السماح بالتحميل</label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-info">
                                        <i class="bi bi-check-lg me-1"></i> حفظ المرفق
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    @endforeach

    {{-- مودالات الأسئلة لكل وحدة --}}
    @foreach($subject->sections as $section)
        @foreach($section->units as $unit)
            {{-- مودال استيراد أسئلة من البنك --}}
            <div class="modal fade" id="importQuestionsModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-primary-transparent border-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle p-2 me-3">
                                    <i class="bi bi-download text-white fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title mb-0 fw-bold">استيراد أسئلة من بنك الأسئلة</h5>
                                    <small class="text-muted">الوحدة: {{ $unit->title }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <form action="{{ route('admin.units.questions.attach', $unit->id) }}" method="POST" id="importQuestionsForm{{ $unit->id }}">
                            @csrf
                            <div class="modal-body p-4">
                                {{-- أدوات البحث والفلترة --}}
                                <div class="card border mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">البحث</label>
                                                <input type="text" class="form-control question-search-input" 
                                                       data-unit="{{ $unit->id }}" 
                                                       placeholder="ابحث عن سؤال...">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">نوع السؤال</label>
                                                <select class="form-select question-type-filter" data-unit="{{ $unit->id }}">
                                                    <option value="">كل الأنواع</option>
                                                    @foreach(\App\Models\Question::TYPES as $key => $name)
                                                        <option value="{{ $key }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">مستوى الصعوبة</label>
                                                <select class="form-select question-difficulty-filter" data-unit="{{ $unit->id }}">
                                                    <option value="">كل المستويات</option>
                                                    @foreach(\App\Models\Question::DIFFICULTIES as $key => $name)
                                                        <option value="{{ $key }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary w-100 search-questions-btn" data-unit="{{ $unit->id }}">
                                                    <i class="bi bi-search me-1"></i> بحث
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- شريط التحكم العلوي (تحديد الكل + زر الاستيراد) --}}
                                <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded border" id="selectAllBar{{ $unit->id }}" style="display: none;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAll{{ $unit->id }}" data-unit="{{ $unit->id }}" style="width: 1.2em; height: 1.2em;">
                                            <label class="form-check-label fw-semibold" for="selectAll{{ $unit->id }}">
                                                تحديد الكل
                                            </label>
                                        </div>
                                        <span class="text-muted small questions-count-label" id="questionsCountLabel{{ $unit->id }}"></span>
                                        {{-- شريط الأسئلة المحددة --}}
                                        <div class="selected-info text-success fw-semibold" id="selectedInfo{{ $unit->id }}" style="display: none;">
                                            <i class="bi bi-check2-square me-1"></i>
                                            تم تحديد <span class="selected-count">0</span> سؤال
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger clear-selection-btn" data-unit="{{ $unit->id }}" style="display: none;" id="clearBtn{{ $unit->id }}">
                                            <i class="bi bi-x-lg me-1"></i> إلغاء التحديد
                                        </button>
                                        <button type="submit" class="btn btn-success" id="importBtn{{ $unit->id }}" disabled>
                                            <i class="bi bi-plus-circle me-1"></i> 
                                            <span>ربط الأسئلة المحددة</span>
                                            <span class="badge bg-white text-success ms-1 import-count" style="display: none;">0</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- قائمة الأسئلة --}}
                                <div class="questions-list-container" id="questionsListContainer{{ $unit->id }}" style="max-height: 400px; overflow-y: auto;">
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-search display-4 d-block mb-3"></i>
                                        <p>اضغط على زر "بحث" لعرض الأسئلة المتاحة</p>
                                        <p class="small">سيتم عرض الأسئلة غير المرتبطة بهذه الوحدة</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-1"></i> إغلاق
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- مودال عرض أسئلة الوحدة --}}
            @if($unit->questions->count() > 0)
            <div class="modal fade" id="viewQuestionsModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-info-transparent border-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-info rounded-circle p-2 me-3">
                                    <i class="bi bi-question-circle text-white fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title mb-0 fw-bold">أسئلة الوحدة</h5>
                                    <small class="text-muted">{{ $unit->title }} - {{ $unit->questions->count() }} سؤال</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="list-group list-group-flush">
                                @foreach($unit->questions as $question)
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}">
                                                    <i class="bi {{ $question->type_icon }} me-1"></i>
                                                    {{ $question->type_name }}
                                                </span>
                                                <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}">
                                                    {{ $question->difficulty_name }}
                                                </span>
                                                <span class="badge bg-secondary-transparent text-secondary">
                                                    {{ $question->default_points }} نقطة
                                                </span>
                                            </div>
                                            <p class="mb-0 fw-semibold">{{ Str::limit($question->title, 100) }}</p>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.questions.show', $question->id) }}" 
                                               class="btn btn-sm btn-icon btn-primary-transparent" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.questions.edit', $question->id) }}" 
                                               class="btn btn-sm btn-icon btn-warning-transparent" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.units.questions.detach', [$unit->id, $question->id]) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('هل أنت متأكد من فك ربط هذا السؤال من الوحدة؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-danger-transparent" title="فك الربط">
                                                    <i class="bi bi-link-45deg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <a href="{{ route('admin.questions.create', ['unit_id' => $unit->id]) }}" class="btn btn-success">
                                <i class="bi bi-plus-lg me-1"></i> إضافة سؤال جديد
                            </a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endforeach
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // التبديل بين حقل الرابط وحقل الملف حسب نوع الفيديو
    document.querySelectorAll('[id^="videoType"]').forEach(function(select) {
        select.addEventListener('change', function() {
            const unitId = this.id.replace('videoType', '');
            const urlField = document.getElementById('videoUrlField' + unitId);
            const fileField = document.getElementById('videoFileField' + unitId);
            
            if (this.value === 'upload') {
                urlField.classList.add('d-none');
                fileField.classList.remove('d-none');
            } else {
                urlField.classList.remove('d-none');
                fileField.classList.add('d-none');
            }
        });
    });

    // التبديل بين حقل الملف وحقل الرابط في مودال المرفقات
    document.querySelectorAll('.attachment-type-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const lessonId = this.getAttribute('data-lesson');
            const fileField = document.querySelector('.file-field-' + lessonId);
            const urlField = document.querySelector('.url-field-' + lessonId);
            
            if (this.value === 'link') {
                fileField.style.display = 'none';
                urlField.style.display = 'block';
            } else {
                fileField.style.display = 'block';
                urlField.style.display = 'none';
            }
        });
    });

    // ==================================================
    // نظام استيراد الأسئلة من بنك الأسئلة
    // ==================================================
    
    // البحث عن الأسئلة المتاحة
    document.querySelectorAll('.search-questions-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const unitId = this.getAttribute('data-unit');
            searchQuestions(unitId);
        });
    });

    // البحث عند الضغط على Enter
    document.querySelectorAll('.question-search-input').forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const unitId = this.getAttribute('data-unit');
                searchQuestions(unitId);
            }
        });
    });

    // إلغاء التحديد
    document.querySelectorAll('.clear-selection-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const unitId = this.getAttribute('data-unit');
            clearSelection(unitId);
        });
    });

    // تحديد الكل
    document.querySelectorAll('.select-all-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const unitId = this.getAttribute('data-unit');
            const isChecked = this.checked;
            document.querySelectorAll(`.question-checkbox[data-unit="${unitId}"]`).forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            updateSelectionSummary(unitId);
        });
    });

    function searchQuestions(unitId) {
        const search = document.querySelector(`.question-search-input[data-unit="${unitId}"]`).value;
        const type = document.querySelector(`.question-type-filter[data-unit="${unitId}"]`).value;
        const difficulty = document.querySelector(`.question-difficulty-filter[data-unit="${unitId}"]`).value;
        const container = document.getElementById('questionsListContainer' + unitId);

        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري البحث...</span>
                </div>
                <p class="mt-2 text-muted">جاري البحث عن الأسئلة...</p>
            </div>
        `;

        fetch(`{{ url('admin/units') }}/${unitId}/available-questions?search=${encodeURIComponent(search)}&type=${type}&difficulty=${difficulty}`)
            .then(response => response.json())
            .then(data => {
                if (data.questions.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-3"></i>
                            <p>لا توجد أسئلة متاحة بهذه المعايير</p>
                            <p class="small">جرب تغيير معايير البحث أو إنشاء أسئلة جديدة</p>
                        </div>
                    `;
                    return;
                }

                // إظهار شريط تحديد الكل
                const selectAllBar = document.getElementById('selectAllBar' + unitId);
                const questionsCountLabel = document.getElementById('questionsCountLabel' + unitId);
                selectAllBar.style.display = 'flex';
                questionsCountLabel.textContent = `${data.questions.length} سؤال متاح`;

                let html = '<div class="list-group list-group-flush">';
                data.questions.forEach(function(q) {
                    html += `
                        <label class="list-group-item list-group-item-action py-3 border-start-0 border-end-0" style="cursor: pointer;">
                            <div class="d-flex align-items-start">
                                <input type="checkbox" name="question_ids[]" value="${q.id}" 
                                       class="form-check-input me-3 mt-1 question-checkbox" 
                                       data-unit="${unitId}" style="width: 1.2em; height: 1.2em;">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <span class="badge bg-${q.type_color}-transparent text-${q.type_color}">
                                            <i class="bi ${q.type_icon} me-1"></i>
                                            ${q.type_name}
                                        </span>
                                        <span class="badge bg-${q.difficulty_color}-transparent text-${q.difficulty_color}">
                                            ${q.difficulty_name}
                                        </span>
                                        <span class="badge bg-secondary-transparent text-secondary">
                                            ${q.default_points} نقطة
                                        </span>
                                    </div>
                                    <p class="mb-0 fw-medium">${q.title.substring(0, 150)}${q.title.length > 150 ? '...' : ''}</p>
                                </div>
                            </div>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;

                // إضافة event listeners للـ checkboxes الجديدة
                container.querySelectorAll('.question-checkbox').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        updateSelectionSummary(unitId);
                        updateSelectAllState(unitId);
                    });
                });

                // Reset select all checkbox
                const selectAllCheckbox = document.getElementById('selectAll' + unitId);
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = `
                    <div class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle display-4 d-block mb-3"></i>
                        <p>حدث خطأ أثناء البحث</p>
                        <p class="small">${error.message}</p>
                    </div>
                `;
            });
    }

    function updateSelectionSummary(unitId) {
        const checkboxes = document.querySelectorAll(`.question-checkbox[data-unit="${unitId}"]:checked`);
        const selectedInfo = document.getElementById('selectedInfo' + unitId);
        const clearBtn = document.getElementById('clearBtn' + unitId);
        const importBtn = document.getElementById('importBtn' + unitId);
        const importCount = importBtn.querySelector('.import-count');

        if (checkboxes.length > 0) {
            // إظهار معلومات التحديد
            if (selectedInfo) {
                selectedInfo.style.display = 'inline-flex';
                selectedInfo.querySelector('.selected-count').textContent = checkboxes.length;
            }
            // إظهار زر إلغاء التحديد
            if (clearBtn) clearBtn.style.display = 'inline-block';
            // تفعيل زر الاستيراد
            importBtn.disabled = false;
            importBtn.classList.remove('btn-success');
            importBtn.classList.add('btn-primary');
            if (importCount) {
                importCount.style.display = 'inline';
                importCount.textContent = checkboxes.length;
            }
        } else {
            // إخفاء معلومات التحديد
            if (selectedInfo) selectedInfo.style.display = 'none';
            // إخفاء زر إلغاء التحديد
            if (clearBtn) clearBtn.style.display = 'none';
            // تعطيل زر الاستيراد
            importBtn.disabled = true;
            importBtn.classList.remove('btn-primary');
            importBtn.classList.add('btn-success');
            if (importCount) importCount.style.display = 'none';
        }
    }

    function clearSelection(unitId) {
        document.querySelectorAll(`.question-checkbox[data-unit="${unitId}"]`).forEach(function(cb) {
            cb.checked = false;
        });
        const selectAllCheckbox = document.getElementById('selectAll' + unitId);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        updateSelectionSummary(unitId);
    }

    function updateSelectAllState(unitId) {
        const allCheckboxes = document.querySelectorAll(`.question-checkbox[data-unit="${unitId}"]`);
        const checkedCheckboxes = document.querySelectorAll(`.question-checkbox[data-unit="${unitId}"]:checked`);
        const selectAllCheckbox = document.getElementById('selectAll' + unitId);
        
        if (selectAllCheckbox && allCheckboxes.length > 0) {
            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
        }
    }
});
</script>
@stop

