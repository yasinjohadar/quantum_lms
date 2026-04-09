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
    /* تمييز مستويات الأقسام (0 = جذر، 1–5 = أبناء) */
    .section-level-0 { border-start: 3px solid var(--bs-primary); background-color: rgba(var(--bs-primary-rgb), 0.06); }
    .section-level-1 { border-start: 3px solid var(--bs-info); background-color: rgba(var(--bs-info-rgb), 0.08); }
    .section-level-2 { border-start: 3px solid var(--bs-danger); background-color: rgba(var(--bs-danger-rgb), 0.06); }
    .section-level-3 { border-start: 3px solid var(--bs-success); background-color: rgba(var(--bs-success-rgb), 0.06); }
    .section-level-4 { border-start: 3px solid var(--bs-warning); background-color: rgba(var(--bs-warning-rgb), 0.08); }
    .section-level-5 { border-start: 3px solid var(--bs-secondary); background-color: rgba(var(--bs-secondary-rgb), 0.08); }
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
            <div id="ajaxLessonAlerts" class="mt-3"></div>

            {{-- شريط علوي: صورة المادة + الاسم + أزرار --}}
            <div class="d-flex align-items-center justify-content-between gap-3 py-3 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $subject->image ? asset('storage/'.$subject->image) : asset('assets/images/media/media-22.jpg') }}"
                         alt="{{ $subject->name }}"
                         class="rounded flex-shrink-0"
                         style="width: 56px; height: 56px; object-fit: cover;">
                    <h5 class="page-title mb-0">تفاصيل المادة: {{ $subject->name }}</h5>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    @can('subject-edit')
                    <a href="{{ route('admin.subjects.edit', $subject->id) }}{{ request('return_to_class_id') ? '?return_to_class_id=' . request('return_to_class_id') : '' }}" class="btn btn-warning btn-sm text-white">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    @endcan
                    @if(request('return_to_class_id'))
                        <a href="{{ route('admin.classes.show', request('return_to_class_id')) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> رجوع للصف
                        </a>
                    @else
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                        </a>
                    @endif
                </div>
            </div>

            @php
                $primaryRoots = $subject->sections->whereNull('parent_id')->sortBy('order')->values();
                $linkedRoots = $subject->linkedSections;
                $rootSections = $primaryRoots->concat($linkedRoots)->unique('id')->values();
            @endphp
            <div class="row g-3">
                <div class="col-12">
                    {{-- محتويات المادة: أقسام المادة لبناء المحتوى --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-collection me-2"></i>
                                محتويات المادة
                            </h6>
                            @can('subject-section-create')
                                <button type="button"
                                        class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createSectionModal">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة قسم جديد
                                </button>
                            @endcan
                        </div>
                        <div class="card-body">
                            @if($rootSections->isEmpty())
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-folder2-open display-4 text-muted"></i>
                                    </div>
                                    <p class="text-muted mb-0">لا توجد أقسام لهذه المادة حالياً</p>
                                    <p class="text-muted small">يمكنك إنشاء أول قسم من زر "إضافة قسم جديد"</p>
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
                                    <option value="{{ $cls['id'] }}">{{ $cls['name'] }}</option>
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
                        <div class="mb-3">
                            <p class="small fw-semibold mb-1">القسم مربوط حالياً بـ:</p>
                            <div id="currentLinkedSubjectsSection" class="small text-muted">
                                {{-- يُملأ عبر JS من API --}}
                            </div>
                        </div>
                        <p class="text-muted small mb-3">اختر الصف ثم المادة ثم اضغط إضافة. يمكنك ربط القسم بعدة مواد. (المادة الأصلية للقسم لا تُضاف تلقائياً.)</p>
                        <div id="linkedSubjectsListSection" class="mb-3">
                            {{-- تُضاف المواد المختارة هنا via JS --}}
                        </div>
                        <div class="row g-2 align-items-end mb-2">
                            <div class="col-md-5">
                                <label class="form-label small">الصف</label>
                                <select class="form-select form-select-sm section-link-class-select" id="sectionLinkClassSelect">
                                    <option value="">-- اختر الصف --</option>
                                    @if(isset($linkableClasses))
                                        @foreach($linkableClasses as $cls)
                                            <option value="{{ $cls['id'] }}">{{ $cls['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">المادة</label>
                                <select class="form-select form-select-sm section-link-subject-select" id="sectionLinkSubjectSelect" disabled>
                                    <option value="">-- اختر المادة --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-success w-100" id="addSectionLinkedSubjectBtn" title="إضافة مادة">
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

    {{-- مودال إنشاء قسم جديد --}}
    @can('subject-section-create')
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
                            <label class="form-label">القسم الأب (اختياري)</label>
                            <select name="parent_id" id="createSectionParentId" class="form-select">
                                <option value="">— قسم رئيسي (بدون أب) —</option>
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
                            <small class="text-muted">اتركه فارغاً لإنشاء قسم رئيسي، أو اختر قسماً لإنشاء قسم فرعي تحته.</small>
                        </div>
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
                                <label class="form-label">القسم الأب (اختياري)</label>
                                <select name="parent_id" class="form-select">
                                    <option value="" {{ $section->parent_id === null ? 'selected' : '' }}>— قسم رئيسي (بدون أب) —</option>
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

        {{-- مودال إنشاء وحدة جديدة --}}
        @can('unit-create')
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
        @endcan

        {{-- مودالات تعديل وحذف الوحدات --}}
        @foreach($section->units as $unit)
            {{-- تعديل وحدة --}}
            @can('unit-edit')
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
                                        @if(auth()->user()->canReviewContent())
                                            {{-- المشرف والمدير يمكنهم التفعيل مباشرة --}}
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="lessonActive{{ $unit->id }}" checked>
                                                <label class="form-check-label" for="lessonActive{{ $unit->id }}">الدرس نشط</label>
                                            </div>
                                        @else
                                            {{-- المعلم: إرسال للمراجعة --}}
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="lessonActive{{ $unit->id }}">
                                                <label class="form-check-label" for="lessonActive{{ $unit->id }}">إرسال للمراجعة</label>
                                            </div>
                                            <small class="text-muted d-block mt-1">سيتم إرسال الدرس للمشرف للمراجعة والموافقة</small>
                                        @endif
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

                                <hr class="my-3">
                                <h6 class="mb-3">
                                    <i class="bi bi-paperclip text-info me-1"></i>
                                    مرفق الدرس (اختياري)
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">عنوان المرفق</label>
                                            <input type="text" name="attachment_title" class="form-control" placeholder="مثال: ملف شرح الدرس">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">نوع المرفق</label>
                                            <select name="attachment_type" class="form-select" id="lessonAttachmentType{{ $unit->id }}">
                                                <option value="">بدون مرفق</option>
                                                <option value="file">ملف</option>
                                                <option value="document">مستند</option>
                                                <option value="image">صورة</option>
                                                <option value="audio">ملف صوتي</option>
                                                <option value="link">رابط خارجي</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 d-none" id="lessonAttachmentFileField{{ $unit->id }}">
                                    <label class="form-label">ملف المرفق</label>
                                    <input type="file" name="attachment_file" class="form-control" id="lessonAttachmentFileInput{{ $unit->id }}">
                                    <small class="text-muted">الحد الأقصى: 50 ميجابايت</small>
                                </div>

                                <div class="mb-3 d-none" id="lessonAttachmentUrlField{{ $unit->id }}">
                                    <label class="form-label">رابط المرفق</label>
                                    <input type="url" name="attachment_url" class="form-control" id="lessonAttachmentUrlInput{{ $unit->id }}" placeholder="https://example.com/resource">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وصف المرفق (اختياري)</label>
                                    <textarea name="attachment_description" class="form-control" rows="2" placeholder="وصف مختصر للمرفق..."></textarea>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="attachment_is_downloadable" id="attachmentIsDownloadable{{ $unit->id }}" checked>
                                    <label class="form-check-label" for="attachmentIsDownloadable{{ $unit->id }}">
                                        السماح بتحميل المرفق
                                    </label>
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
                                            @if(auth()->user()->canReviewContent())
                                                {{-- المشرف والمدير يمكنهم التفعيل مباشرة --}}
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" 
                                                           id="lessonActive{{ $lesson->id }}" 
                                                           {{ $lesson->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="lessonActive{{ $lesson->id }}">الدرس نشط</label>
                                                </div>
                                            @else
                                                {{-- المعلم: يعرض حالة المراجعة --}}
                                                <div class="mb-2">
                                                    <label class="form-label small">حالة المراجعة:</label>
                                                    @if($lesson->review_status === 'pending_review')
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock-history me-1"></i> قيد المراجعة
                                                        </span>
                                                    @elseif($lesson->review_status === 'approved')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i> تمت الموافقة
                                                        </span>
                                                    @elseif($lesson->review_status === 'rejected')
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-x-circle me-1"></i> مرفوض
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">مسودة</span>
                                                    @endif
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" 
                                                           id="lessonActive{{ $lesson->id }}" 
                                                           {{ $lesson->is_active ? 'checked' : '' }}
                                                           {{ $lesson->review_status === 'pending_review' ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="lessonActive{{ $lesson->id }}">
                                                        {{ $lesson->review_status === 'pending_review' ? 'قيد المراجعة (غير قابل للتعديل)' : 'إرسال للمراجعة' }}
                                                    </label>
                                                </div>
                                                @if($lesson->review_notes)
                                                    <div class="alert alert-info mt-2 small">
                                                        <strong>ملاحظات المشرف:</strong><br>
                                                        {{ $lesson->review_notes }}
                                                    </div>
                                                @endif
                                            @endif
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

                                    @if(isset($linkableSubjects) && $linkableSubjects->isNotEmpty())
                                    <hr class="my-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-link-45deg me-1"></i> ربط الدرس بمواد وأقسام إضافية
                                        </label>
                                        <p class="small text-muted mb-2">الوحدة الأصلية: {{ $lesson->unit->section->subject->name ?? '' }} — {{ $lesson->unit->section->title ?? '' }} — {{ $lesson->unit->title ?? '' }}</p>
                                        <div id="linkedUnitsList{{ $lesson->id }}_{{ $unit->id }}" class="mb-2">
                                            @foreach($lesson->linkedUnits as $linkedUnit)
                                            <div class="d-flex align-items-center gap-2 mb-1 linked-unit-row" data-lesson-id="{{ $lesson->id }}">
                                                <span class="badge bg-secondary">{{ $linkedUnit->section->subject->name ?? '' }} — {{ $linkedUnit->section->title ?? '' }} — {{ $linkedUnit->title }}</span>
                                                <input type="hidden" name="linked_unit_ids[]" value="{{ $linkedUnit->id }}">
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="row g-2 align-items-end mb-2" data-lesson-id="{{ $lesson->id }}" data-current-subject-id="{{ $subject->id }}" data-current-class-id="{{ $subject->class_id ?? '' }}">
                                            <div class="col-md-3">
                                                <label class="form-label small">الصف</label>
                                                <select class="form-select form-select-sm link-class-select" data-lesson-id="{{ $lesson->id }}">
                                                    <option value="">-- اختر الصف --</option>
                                                    @if(isset($linkableClasses))
                                                    @foreach($linkableClasses as $cls)
                                                    <option value="{{ $cls['id'] }}">{{ $cls['name'] }}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">مادة</label>
                                                <select class="form-select form-select-sm link-subject-select" data-lesson-id="{{ $lesson->id }}" disabled>
                                                    <option value="">-- اختر المادة --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">القسم</label>
                                                <select class="form-select form-select-sm link-section-select" data-lesson-id="{{ $lesson->id }}" disabled>
                                                    <option value="">-- اختر القسم --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">الوحدة</label>
                                                <select class="form-select form-select-sm link-unit-select" data-lesson-id="{{ $lesson->id }}" data-primary-unit-id="{{ $lesson->unit_id }}" disabled>
                                                    <option value="">-- اختر الوحدة --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-sm btn-success add-linked-unit" data-lesson-id="{{ $lesson->id }}" data-list-id="linkedUnitsList{{ $lesson->id }}_{{ $unit->id }}" title="إضافة وحدة">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
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

                {{-- مودال إضافة مرفقات للدرس --}}
                @can('lesson-attachment-create')
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
                @endcan

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
                                                   poster="{{ $lesson->thumbnail ? asset('storage/'.$lesson->thumbnail) : '' }}"
                                                   controlsList="nodownload">
                                                <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                                <source src="{{ $lesson->embed_url }}" type="video/webm">
                                                <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                                المتصفح لا يدعم تشغيل الفيديو.
                                            </video>
                                        @else
                                            <video controls class="w-100 h-100"
                                                   poster="{{ $lesson->thumbnail ? asset('storage/'.$lesson->thumbnail) : '' }}">
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

    {{-- Modals للموافقة والرفض على الدروس --}}
    @foreach($subject->sections as $section)
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
@isset($linkableStructure)
<script>
window.linkableStructure = @json($linkableStructure);
window.adminQuizzesLinkUnitsBase = "{{ url('admin/quizzes') }}";
window.adminSectionsLinkSubjectsBase = "{{ url('admin/sections') }}";
</script>
@endisset
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تعيين القسم الأب عند فتح مودال إنشاء قسم من "إضافة قسم فرعي"
    var createSectionModalEl = document.getElementById('createSectionModal');
    if (createSectionModalEl) {
        createSectionModalEl.addEventListener('show.bs.modal', function(e) {
            var parentSelect = document.getElementById('createSectionParentId');
            if (!parentSelect) return;
            var trigger = e.relatedTarget;
            if (trigger && trigger.classList && trigger.classList.contains('add-child-section-btn') && trigger.getAttribute('data-parent-id')) {
                parentSelect.value = trigger.getAttribute('data-parent-id');
            } else {
                parentSelect.value = '';
            }
        });
    }

    // تعيين الوحدة الأب عند فتح مودال إنشاء وحدة من "إضافة وحدة فرعية"
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

    // مودال ربط القسم بمواد إضافية: تعيين العنوان والـ action وجلب المواد المرتبطة عند الفتح
    var linkSectionSubjectsModalEl = document.getElementById('linkSectionSubjectsModal');
    if (linkSectionSubjectsModalEl && window.adminSectionsLinkSubjectsBase) {
        linkSectionSubjectsModalEl.addEventListener('show.bs.modal', function(e) {
            var form = document.getElementById('linkSectionSubjectsForm');
            var titleEl = document.getElementById('linkSectionSubjectsModalTitle');
            var currentLinkedEl = document.getElementById('currentLinkedSubjectsSection');
            var listEl = document.getElementById('linkedSubjectsListSection');
            var trigger = e.relatedTarget;
            if (!form || !titleEl) return;
            var sectionId = trigger && trigger.getAttribute('data-section-id');
            var sectionTitle = trigger && trigger.getAttribute('data-section-title') || '';
            var primarySubjectId = trigger && trigger.getAttribute('data-section-primary-subject-id') || '';
            if (sectionId) {
                form.action = window.adminSectionsLinkSubjectsBase + '/' + sectionId + '/link-subjects';
                form.setAttribute('data-primary-subject-id', primarySubjectId);
                titleEl.textContent = 'ربط القسم بمواد إضافية' + (sectionTitle ? ': ' + sectionTitle : '');
            }
            function esc(s) {
                if (s == null || s === '') return '';
                var div = document.createElement('div');
                div.textContent = s;
                return div.innerHTML;
            }
            function fillLinkedSubjectsUI(linkedSubjects, selectedIds) {
                selectedIds = selectedIds || [];
                if (currentLinkedEl) {
                    if (!linkedSubjects || linkedSubjects.length === 0) {
                        currentLinkedEl.innerHTML = '<span class="text-muted">لا يوجد ربط لمواد إضافية</span>';
                    } else {
                        var parts = linkedSubjects.map(function(s) {
                            var label = [s.stage_name, s.class_name, s.name].filter(Boolean).join(' — ');
                            return '<span class="badge bg-secondary me-1 mb-1">' + esc(label || s.name || '#' + s.id) + '</span>';
                        });
                        currentLinkedEl.innerHTML = parts.join('');
                    }
                }
                listEl.innerHTML = '';
                (selectedIds || []).forEach(function(sid) {
                    if (String(sid) === String(primarySubjectId)) return;
                    var s = (linkedSubjects || []).find(function(x) { return String(x.id) === String(sid); });
                    var label = s ? [s.stage_name, s.class_name, s.name].filter(Boolean).join(' — ') : ('#' + sid);
                    var row = document.createElement('div');
                    row.className = 'd-flex align-items-center gap-2 mb-1 linked-subject-row';
                    row.innerHTML = '<span class="badge bg-secondary">' + esc(label) + '</span>' +
                        '<input type="hidden" name="linked_subject_ids[]" value="' + esc(String(sid)) + '">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-subject" title="إزالة"><i class="bi bi-x"></i></button>';
                    listEl.appendChild(row);
                });
            }
            if (currentLinkedEl) currentLinkedEl.innerHTML = '<span class="text-muted">جاري التحميل...</span>';
            listEl.innerHTML = '';
            var linkedUrl = window.adminSectionsLinkSubjectsBase + '/' + sectionId + '/linked-subjects';
            fetch(linkedUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(res) { return res.json(); })
                .then(function(linkedSubjects) {
                    linkedSubjects = Array.isArray(linkedSubjects) ? linkedSubjects : [];
                    var selectedIds = linkedSubjects.map(function(s) { return s.id; });
                    fillLinkedSubjectsUI(linkedSubjects, selectedIds);
                })
                .catch(function() {
                    fillLinkedSubjectsUI([], []);
                });
            var sectionLinkClassSelect = document.getElementById('sectionLinkClassSelect');
            var sectionLinkSubjectSelect = document.getElementById('sectionLinkSubjectSelect');
            if (sectionLinkClassSelect) sectionLinkClassSelect.value = '';
            if (sectionLinkSubjectSelect) {
                sectionLinkSubjectSelect.innerHTML = '<option value="">-- اختر المادة --</option>';
                sectionLinkSubjectSelect.disabled = true;
            }
        });
    }
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-linked-subject')) {
            var row = e.target.closest('.linked-subject-row');
            if (row) row.remove();
        }
    });
    // الصف -> المادة لمودال ربط القسم
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
        var addSectionLinkedSubjectBtn = document.getElementById('addSectionLinkedSubjectBtn');
        if (addSectionLinkedSubjectBtn) {
            addSectionLinkedSubjectBtn.addEventListener('click', function() {
                var form = document.getElementById('linkSectionSubjectsForm');
                var listEl = document.getElementById('linkedSubjectsListSection');
                var subjectSelect = document.getElementById('sectionLinkSubjectSelect');
                if (!listEl || !subjectSelect || !form) return;
                var primarySubjectId = form.getAttribute('data-primary-subject-id') || '';
                var subjectId = subjectSelect.value;
                if (!subjectId) {
                    alert('يرجى اختيار الصف ثم المادة قبل الإضافة');
                    return;
                }
                if (String(subjectId) === String(primarySubjectId)) {
                    alert('المادة الأصلية للقسم لا تُضاف إلى الربط');
                    return;
                }
                var existing = listEl.querySelectorAll('input[name="linked_subject_ids[]"]');
                for (var i = 0; i < existing.length; i++) {
                    if (existing[i].value === subjectId) return;
                }
                var s = window.linkableStructure.find(function(x) { return String(x.id) === String(subjectId); });
                var label = s ? [s.stage_name, s.class_name, s.name].filter(Boolean).join(' — ') : ('#' + subjectId);
                var row = document.createElement('div');
                row.className = 'd-flex align-items-center gap-2 mb-1 linked-subject-row';
                row.innerHTML = '<span class="badge bg-secondary">' + label.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                    '<input type="hidden" name="linked_subject_ids[]" value="' + subjectId + '">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-linked-subject" title="إزالة"><i class="bi bi-x"></i></button>';
                listEl.appendChild(row);
                subjectSelect.value = '';
            });
        }
    }

    // ربط الدرس بوحدات إضافية (صف / مادة / قسم / وحدة)
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
                        opt.textContent = sec.title;
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
                        sectionName = section.title || '';
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
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-linked-unit')) {
                const row = e.target.closest('.linked-unit-row');
                if (row) row.remove();
            }
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
                        opt.textContent = sec.title;
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
                        sectionName = section.title || '';
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

    // التبديل بين حقل ملف/رابط مرفق الدرس في مودال الإنشاء
    document.querySelectorAll('[id^="lessonAttachmentType"]').forEach(function(select) {
        const toggleAttachmentFields = function() {
            const unitId = select.id.replace('lessonAttachmentType', '');
            const fileField = document.getElementById('lessonAttachmentFileField' + unitId);
            const urlField = document.getElementById('lessonAttachmentUrlField' + unitId);
            const fileInput = document.getElementById('lessonAttachmentFileInput' + unitId);
            const urlInput = document.getElementById('lessonAttachmentUrlInput' + unitId);
            const selectedType = select.value;

            if (selectedType === 'link') {
                fileField.classList.add('d-none');
                urlField.classList.remove('d-none');
                if (fileInput) {
                    fileInput.value = '';
                }
            } else if (selectedType) {
                fileField.classList.remove('d-none');
                urlField.classList.add('d-none');
                if (urlInput) {
                    urlInput.value = '';
                }
            } else {
                fileField.classList.add('d-none');
                urlField.classList.add('d-none');
                if (fileInput) {
                    fileInput.value = '';
                }
                if (urlInput) {
                    urlInput.value = '';
                }
            }
        };

        select.addEventListener('change', toggleAttachmentFields);
        toggleAttachmentFields();
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

    restoreAccordionState(document);
    syncAccordionStateFromDom();

    function showAjaxLessonAlert(type, message, errors) {
        var alertsHost = document.getElementById('ajaxLessonAlerts');
        if (!alertsHost) return;
        var icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        var html = '<div class="alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show" role="alert">' +
            '<i class="bi ' + icon + ' me-2"></i>' + message;
        if (errors && Object.keys(errors).length) {
            html += '<ul class="mb-0 mt-2">';
            Object.keys(errors).forEach(function(field) {
                var fieldErrors = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                fieldErrors.forEach(function(err) {
                    html += '<li>' + err + '</li>';
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
            if (!result.ok) {
                if (result.status === 422) {
                    showAjaxLessonAlert('error', 'يرجى تصحيح البيانات ثم المحاولة مرة أخرى.', payload.errors || {});
                } else {
                    showAjaxLessonAlert('error', payload.message || 'حدث خطأ غير متوقع أثناء تنفيذ العملية.');
                }
                return;
            }

            closeFormModal(form);
            showAjaxLessonAlert('success', payload.message || 'تم تنفيذ العملية بنجاح.');
            await refreshUnitContentAndModals(payload.unit_id || form.dataset.unitId, form.dataset.lessonAction === 'destroy' ? (payload.lesson_id || form.dataset.lessonId) : null);
        } catch (error) {
            showAjaxLessonAlert('error', 'تعذر تنفيذ العملية حالياً. تحقق من الاتصال ثم أعد المحاولة.');
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
@stop

