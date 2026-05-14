@extends('admin.layouts.master')

@section('page-title')
    تخصيص المعلم: {{ $teacher->name }}
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تخصيص المعلم: {{ $teacher->name }}</h5>
                </div>
                <div>
                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> رجوع
                    </a>
                    <a href="{{ route('admin.teachers.progress.history', $teacher->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> إحصائيات سابقة
                    </a>
                    <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-graph-up me-1"></i> تفاصيل التقدم
                    </a>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>نجح!</strong> {!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>خطأ!</strong> {!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @isset($teacherProgressStats, $yearWeeksLessons)
                @include('admin.pages.teachers.partials.assignments-sync.progress-card', [
                    'teacher' => $teacher,
                    'teacherProgressStats' => $teacherProgressStats,
                    'yearWeeksLessons' => $yearWeeksLessons,
                    'assignedClasses' => $assignedClasses,
                    'assignedSubjects' => $assignedSubjects,
                ])
            @endisset

            <form id="teacherAssignmentsForm" action="{{ route('admin.teachers.assignments.update', $teacher->id) }}" method="POST">
                @csrf
                @method('PUT')

                @php
                    $canManageClasses = auth()->user()->can('teacher-assignment-manage-classes');
                    $canManageSubjects = auth()->user()->can('teacher-assignment-manage-subjects');
                @endphp

                <div class="row g-3 align-items-stretch">
                    <!-- الصفوف المخصصة -->
                    @can('teacher-assignment-manage-classes')
                    <div class="{{ $canManageSubjects ? 'col-xl-6' : 'col-12' }}">
                        <div class="card h-100" id="classSection">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-building me-2"></i>
                                    الصفوف المخصصة
                                </h6>
                                <small class="text-muted">تحديد الصفوف التي يمكن للمعلم الوصول إليها (جميع المواد في الصف)</small>
                            </div>
                            <div class="card-body">
                                @if($allClasses->count() > 0)
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="selectAllClasses()">
                                                    <i class="bi bi-check-all me-1"></i> تحديد الكل
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="deselectAllClasses()">
                                                    <i class="bi bi-x-lg me-1"></i> إلغاء تحديد الكل
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">فلترة حسب المرحلة:</label>
                                        <select class="form-select form-select-sm" id="stageFilter" onchange="filterByStage()">
                                            <option value="">كل المراحل</option>
                                            @php
                                                $stages = \App\Models\Stage::ordered()->get();
                                            @endphp
                                            @foreach($stages as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="list-group" style="max-height: 500px; overflow-y: auto;">
                                        @foreach($allClasses as $class)
                                            <div class="list-group-item" data-stage-id="{{ $class->stage_id ?? '' }}" data-class-id="{{ $class->id }}">
                                                <div class="form-check">
                                                    <input class="form-check-input class-checkbox" 
                                                           type="checkbox" 
                                                           name="classes[]" 
                                                           value="{{ $class->id }}" 
                                                           id="class_{{ $class->id }}"
                                                           {{ $assignedClasses->contains('id', $class->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="class_{{ $class->id }}">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $class->name }}</strong>
                                                                @if($class->stage)
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-bookmark me-1"></i>
                                                                        {{ $class->stage->name }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                            <span class="badge bg-success class-assigned-badge {{ $assignedClasses->contains('id', $class->id) ? '' : 'd-none' }}">مخصص</span>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="mt-2 ps-4 d-flex flex-wrap gap-1">
                                                    @can('teacher-assignment-manage-subjects')
                                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="focusClassSubjects({{ $class->id }})">
                                                            <i class="bi bi-check2-square me-1"></i> تضمين الصف وعرض مواده
                                                        </button>
                                                    @endcan
                                                    @can('teacher-assignment-manage-classes')
                                                        <span class="class-detach-actions {{ $assignedClasses->contains('id', $class->id) ? '' : 'd-none' }}">
                                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="detachClassFromTeacher({{ $class->id }}, @json($canManageSubjects))">
                                                                <i class="bi bi-x-octagon me-1"></i> فصل الصف@if($canManageSubjects) وكل مواده@endif
                                                            </button>
                                                        </span>
                                                    @endcan
                                                </div>
                                                @can('teacher-assignment-manage-subjects')
                                                    @php
                                                        $nestedSubjectsForClass = $allSubjects->where('class_id', $class->id);
                                                    @endphp
                                                    @if($nestedSubjectsForClass->count() > 0)
                                                        <div class="class-nested-subjects mt-2 pt-2 border-top" data-nested-for-class="{{ $class->id }}" id="nestedSubjectsClass_{{ $class->id }}">
                                                            <div class="small text-muted mb-1">مواد هذا الصف</div>
                                                            <div class="list-group list-group-flush border rounded small" style="max-height: 280px; overflow-y: auto;">
                                                                @foreach($nestedSubjectsForClass as $subject)
                                                                    @include('admin.pages.teachers.partials.assignments-sync.subject-edit-row', [
                                                                        'subject' => $subject,
                                                                        'assignedSubjects' => $assignedSubjects,
                                                                        'compactClassLabel' => true,
                                                                    ])
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endcan
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted text-center py-4">لا توجد صفوف متاحة</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- لمحة المواد المخصصة + أدوات جماعية (تعديل المواد ضمن كل صف في العمود المجاور) -->
                    @can('teacher-assignment-manage-subjects')
                    @php
                        $subjectsNestedUnderClasses = $canManageClasses && $allClasses->count() > 0;
                    @endphp
                    <div class="{{ $subjectsNestedUnderClasses ? 'col-xl-6' : 'col-12' }}">
                        <div class="card h-100" id="{{ $subjectsNestedUnderClasses ? 'assignmentsOverviewColumn' : 'subjectSection' }}">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-journal-check me-2"></i>
                                    @if($subjectsNestedUnderClasses)
                                        لمحة المواد المخصصة والأدوات الجماعية
                                    @else
                                        المواد المخصصة
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    @if($subjectsNestedUnderClasses)
                                        تُعدّل المواد تحت كل صف في «الصفوف المخصصة» بعد تحديد الصف؛ هنا ملخص التخصيص والفصل الجماعي. يُحفظ التخصيص <strong>تلقائياً</strong> بعد التعديل.
                                    @else
                                        يُحفظ التخصيص <strong>تلقائياً</strong> بعد التعديل. يمكنك أيضاً الضغط على <strong>حفظ التغييرات</strong> في الأسفل.
                                    @endif
                                </small>
                            </div>
                            <div class="card-body">
                                @if($allSubjects->count() > 0)
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="selectAllSubjects()">
                                                    <i class="bi bi-check-all me-1"></i> تحديد الكل (الظاهر فقط)
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="deselectAllSubjects()">
                                                    <i class="bi bi-x-lg me-1"></i> إلغاء تحديد الكل (الظاهر فقط)
                                                </button>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="detachBulkPickedSubjects()">
                                                <i class="bi bi-collection-x me-1"></i> فصل المواد المحددة للفصل الجماعي
                                            </button>
                                            <span class="small text-muted">فعّل خانة «للفصل الجماعي» بجانب المادة ثم اضغط الزر أعلاه.</span>
                                        </div>
                                    </div>
                                    <div id="noSelectedClassesHint" class="alert alert-warning py-2 small mb-3 d-none">
                                        حدد صفاً واحداً أو أكثر من عمود «الصفوف المخصصة» لتظهر مواد الصف تحته وتتمكن من تعديل الصفحات.
                                    </div>
                                    @if($subjectsNestedUnderClasses)
                                        <div class="border rounded p-3 bg-light" id="assignedSubjectsSidePanel" style="min-height: 200px;">
                                            <div id="assignedSubjectsSidePanelInner">
                                                @include('admin.pages.teachers.partials.assignments-sync.side-panel-inner', ['assignedSubjects' => $assignedSubjects])
                                            </div>
                                        </div>
                                    @else
                                        <div class="list-group subject-main-list mb-3" style="max-height: 480px; overflow-y: auto;">
                                            @foreach($allSubjects as $subject)
                                                @include('admin.pages.teachers.partials.assignments-sync.subject-edit-row', [
                                                    'subject' => $subject,
                                                    'assignedSubjects' => $assignedSubjects,
                                                    'compactClassLabel' => false,
                                                ])
                                            @endforeach
                                        </div>
                                        <div class="border rounded p-3 bg-light" id="assignedSubjectsSidePanel" style="min-height: 200px;">
                                            <div id="assignedSubjectsSidePanelInner">
                                                @include('admin.pages.teachers.partials.assignments-sync.side-panel-inner', ['assignedSubjects' => $assignedSubjects])
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted text-center py-4">لا توجد مواد متاحة</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>

                @can('teacher-assignment-manage-subjects')
                @if($allSubjects->count() > 0)
                <div class="card border shadow-sm mt-3" id="indepSubjectAssignCard">
                    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-sliders me-2"></i>الصف الثاني — تخصيص مواد من صفوف بشكل مخصص</h6>
                            <small class="text-muted">هنا تختار <strong>صفاً واحداً للفلترة فقط</strong> لعرض المواد غير المخصصة أو المخصصة لهذا الصف وإضافة مادة دون تغيير تخصيص باقي الصفوف من هذا القسم.</small>
                        </div>
                    </div>
                    <div class="card-body" id="indepSubjectAssignCardBody">
                        @include('admin.pages.teachers.partials.assignments-sync.indep-card-body', [
                            'allClasses' => $allClasses,
                            'allSubjects' => $allSubjects,
                            'assignedSubjects' => $assignedSubjects,
                        ])
                    </div>
                </div>
                @endif
                @endcan

                @cannot('teacher-assignment-manage-classes')
                    @cannot('teacher-assignment-manage-subjects')
                        <div class="alert alert-warning mt-3 mb-0">
                            لا تملك صلاحية إدارة تخصيصات الصفوف أو المواد لهذا المعلم.
                        </div>
                    @endcannot
                @endcannot

                <!-- معلومات المعلم -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">معلومات المعلم</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>الاسم:</strong> {{ $teacher->name }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>البريد الإلكتروني:</strong> {{ $teacher->email }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1">
                                            <strong>الصفوف المخصصة حالياً:</strong> 
                                            <span class="badge bg-primary" id="teacherAssignmentsFooterClassesCount">{{ $assignedClasses->count() }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1">
                                            <strong>المواد المخصصة حالياً:</strong> 
                                            <span class="badge bg-primary" id="teacherAssignmentsFooterSubjectsCount">{{ $assignedSubjects->count() }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- زر الحفظ -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 align-items-center flex-wrap">
                            <span id="teacherAssignmentsSyncStatus" class="small text-muted me-auto" aria-live="polite"></span>
                            <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i> إلغاء
                            </a>
                            @can('teacher-assignment-update')
                                @canany(['teacher-assignment-manage-classes', 'teacher-assignment-manage-subjects'])
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> حفظ التغييرات
                                    </button>
                                @endcanany
                            @endcan
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
<script>
    function hasClassCheckboxes() {
        return document.querySelectorAll('.class-checkbox').length > 0;
    }

    function getSelectedClassIds() {
        return Array.from(document.querySelectorAll('.class-checkbox:checked')).map(function(cb) {
            return String(cb.value);
        });
    }

    function filterSubjectsBySelectedClasses() {
        const classIds = getSelectedClassIds();
        const hint = document.getElementById('noSelectedClassesHint');

        document.querySelectorAll('.class-nested-subjects').forEach(function(wrap) {
            if (!hasClassCheckboxes()) {
                wrap.style.display = '';
                return;
            }
            const nid = String(wrap.getAttribute('data-nested-for-class') || '');
            if (classIds.length === 0) {
                wrap.style.display = 'none';
            } else if (classIds.indexOf(nid) !== -1) {
                wrap.style.display = '';
            } else {
                wrap.style.display = 'none';
            }
        });

        if (!hasClassCheckboxes()) {
            document.querySelectorAll('.subject-main-row').forEach(function(row) {
                row.style.display = '';
                const scb = row.querySelector('.subject-checkbox');
                if (scb) scb.disabled = false;
                const pagesInp = row.querySelector('.subject-pages-input');
                if (pagesInp) pagesInp.disabled = false;
            });
            document.querySelectorAll('.summary-assigned-item').forEach(function(el) {
                el.classList.remove('d-none');
            });
            if (hint) hint.classList.add('d-none');
            return;
        }

        document.querySelectorAll('.subject-main-row').forEach(function(row) {
            const cid = String(row.getAttribute('data-class-id') || '');
            const scb = row.querySelector('.subject-checkbox');
            let visible;
            if (classIds.length === 0) {
                const keep = scb && scb.checked;
                row.style.display = keep ? '' : 'none';
                visible = !!keep;
            } else if (classIds.indexOf(cid) !== -1) {
                row.style.display = '';
                visible = true;
            } else {
                row.style.display = 'none';
                visible = false;
            }
            if (scb) {
                if (classIds.length === 0) {
                    scb.disabled = !visible;
                } else {
                    scb.disabled = !visible && !scb.checked;
                }
            }
            const pagesInp = row.querySelector('.subject-pages-input');
            if (pagesInp) {
                if (classIds.length === 0) {
                    pagesInp.disabled = !visible;
                } else {
                    pagesInp.disabled = !visible && !(scb && scb.checked);
                }
            }
        });

        document.querySelectorAll('.summary-assigned-item').forEach(function(el) {
            const cid = String(el.getAttribute('data-summary-class-id') || '');
            if (classIds.length === 0) {
                el.classList.add('d-none');
            } else if (classIds.indexOf(cid) !== -1) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });

        if (hint) {
            if (classIds.length === 0) {
                hint.classList.remove('d-none');
            } else {
                hint.classList.add('d-none');
            }
        }
    }

    function syncAllSubjectsWithClasses() {
        document.querySelectorAll('.class-checkbox').forEach(function(classCb) {
            const classId = String(classCb.value);
            const on = classCb.checked;
            document.querySelectorAll('.subject-main-row[data-class-id="' + classId + '"]').forEach(function(row) {
                const scb = row.querySelector('.subject-checkbox');
                if (scb) {
                    scb.disabled = false;
                    scb.checked = on;
                }
            });
        });
    }

    function bindClassCheckboxesToSubjects() {
        document.querySelectorAll('.class-checkbox').forEach(function(classCb) {
            classCb.addEventListener('change', function() {
                const classId = String(this.value);
                const on = this.checked;
                document.querySelectorAll('.subject-main-row[data-class-id="' + classId + '"]').forEach(function(row) {
                    const scb = row.querySelector('.subject-checkbox');
                    if (scb) {
                        scb.disabled = false;
                        scb.checked = on;
                    }
                });
                filterSubjectsBySelectedClasses();
            });
        });
    }

    function selectAllClasses() {
        document.querySelectorAll('.class-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
        });
        syncAllSubjectsWithClasses();
        filterSubjectsBySelectedClasses();
        if (window.scheduleTeacherAssignmentsAutoSave) {
            window.scheduleTeacherAssignmentsAutoSave();
        }
    }

    function deselectAllClasses() {
        document.querySelectorAll('.class-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
        syncAllSubjectsWithClasses();
        filterSubjectsBySelectedClasses();
        if (window.scheduleTeacherAssignmentsAutoSave) {
            window.scheduleTeacherAssignmentsAutoSave();
        }
    }

    function selectAllSubjects() {
        document.querySelectorAll('.subject-main-row').forEach(function(row) {
            if (row.style.display === 'none') return;
            const cb = row.querySelector('.subject-checkbox');
            if (cb) cb.checked = true;
        });
        if (window.scheduleTeacherAssignmentsAutoSave) {
            window.scheduleTeacherAssignmentsAutoSave();
        }
    }

    function deselectAllSubjects() {
        document.querySelectorAll('.subject-main-row').forEach(function(row) {
            if (row.style.display === 'none') return;
            const cb = row.querySelector('.subject-checkbox');
            if (cb) cb.checked = false;
        });
        if (window.scheduleTeacherAssignmentsAutoSave) {
            window.scheduleTeacherAssignmentsAutoSave();
        }
    }

    function filterByStage() {
        const stageId = document.getElementById('stageFilter').value;
        const classItems = document.querySelectorAll('#classSection .list-group-item');

        classItems.forEach(item => {
            const classStageId = item.getAttribute('data-stage-id');
            if (!stageId || classStageId === stageId) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function updateIndepSectionVisibility() {
        const indep = document.getElementById('indepClassFilter');
        const classId = indep ? indep.value : '';
        const prompt = document.getElementById('indepNeedClassPrompt');
        const listsRow = document.getElementById('indepListsRow');
        if (!prompt || !listsRow) return;
        if (!classId) {
            prompt.classList.remove('d-none');
            listsRow.classList.add('d-none');
            return;
        }
        prompt.classList.add('d-none');
        listsRow.classList.remove('d-none');
    }

    function filterIndepPanels() {
        updateIndepSectionVisibility();
        const classId = document.getElementById('indepClassFilter') ? document.getElementById('indepClassFilter').value : '';
        if (!classId) {
            return;
        }
        document.querySelectorAll('.indep-unassigned-row').forEach(function(item) {
            const subjectClassId = item.getAttribute('data-class-id');
            if (String(subjectClassId) === String(classId)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
        document.querySelectorAll('.indep-assigned-hint').forEach(function(el) {
            const cid = el.getAttribute('data-indep-class-id');
            if (String(cid) === String(classId)) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });
    }

    function syncIndepClassFilterFromIndep() {
        filterIndepPanels();
    }

    function detachClassFromTeacher(classId, withSubjects) {
        const msg = withSubjects
            ? 'فصل هذا الصف عن المعلم وإزالة تخصيص جميع مواد هذا الصف؟'
            : 'فصل هذا الصف عن المعلم؟';
        if (!confirm(msg)) {
            return;
        }
        const ccb = document.getElementById('class_' + classId);
        if (ccb) {
            ccb.checked = false;
            ccb.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (withSubjects) {
            document.querySelectorAll('.subject-main-row[data-class-id="' + String(classId) + '"]').forEach(function(row) {
                const scb = row.querySelector('.subject-checkbox');
                if (scb) {
                    scb.disabled = false;
                    scb.checked = false;
                }
                const inp = row.querySelector('.subject-pages-input');
                if (inp) {
                    inp.value = '';
                }
                const bp = row.querySelector('.subject-bulk-pick');
                if (bp) {
                    bp.checked = false;
                }
            });
        }
        filterSubjectsBySelectedClasses();
    }

    function detachSubject(subjectId) {
        if (!confirm('فصل هذه المادة عن المعلم؟')) {
            return;
        }
        const cb = document.getElementById('subject_' + subjectId);
        if (cb) {
            cb.disabled = false;
            cb.checked = false;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const inp = document.querySelector('.subject-pages-input[data-subject-id="' + subjectId + '"]');
        if (inp) {
            inp.value = '';
        }
        const bp = document.getElementById('bulk_pick_' + subjectId);
        if (bp) {
            bp.checked = false;
        }
        filterSubjectsBySelectedClasses();
    }

    function detachBulkPickedSubjects() {
        let count = 0;
        document.querySelectorAll('.subject-main-row').forEach(function(row) {
            if (row.style.display === 'none') {
                return;
            }
            const pick = row.querySelector('.subject-bulk-pick');
            if (!pick || !pick.checked) {
                return;
            }
            count++;
            const cb = row.querySelector('.subject-checkbox');
            if (cb) {
                cb.disabled = false;
                cb.checked = false;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const sid = row.getAttribute('data-subject-id');
            if (sid) {
                const inp = document.querySelector('.subject-pages-input[data-subject-id="' + sid + '"]');
                if (inp) {
                    inp.value = '';
                }
            }
            pick.checked = false;
        });
        if (count === 0) {
            alert('لم يُحدد أي مادة. فعّل خانة «للفصل الجماعي» بجانب المواد ثم أعد المحاولة.');
            return;
        }
        filterSubjectsBySelectedClasses();
    }

    function focusClassSubjects(classId) {
        const cb = document.getElementById('class_' + classId);
        if (cb) {
            cb.checked = true;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const indep = document.getElementById('indepClassFilter');
        if (indep) {
            indep.value = String(classId);
            filterIndepPanels();
        }
        const nest = document.getElementById('nestedSubjectsClass_' + classId);
        if (nest) {
            nest.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            const overview = document.getElementById('assignmentsOverviewColumn');
            const legacy = document.getElementById('subjectSection');
            const sec = overview || legacy;
            if (sec) {
                sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function assignSubjectFromHelper(subjectId) {
        const cb = document.getElementById('subject_' + subjectId);
        if (cb) {
            cb.disabled = false;
            cb.checked = true;
        }
        const wrap = document.getElementById('subject_row_wrap_' + subjectId);
        if (wrap) {
            wrap.style.display = '';
        }
        filterSubjectsBySelectedClasses();
        if (cb) {
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (wrap) {
            wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (window.flushTeacherAssignmentsJsonSave) {
            window.flushTeacherAssignmentsJsonSave();
        } else if (window.scheduleTeacherAssignmentsAutoSave) {
            window.scheduleTeacherAssignmentsAutoSave();
        }
    }

    (function() {
        let debounceTimer = null;
        let inflight = false;
        let queued = false;

        function csrfToken() {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        }

        function setSyncStatus(text, isError) {
            const el = document.getElementById('teacherAssignmentsSyncStatus');
            if (!el) {
                return;
            }
            el.textContent = text || '';
            el.classList.toggle('text-danger', !!isError);
            el.classList.toggle('text-muted', !isError);
        }

        function applyAssignmentsSyncResponse(data) {
            if (data.html && data.html.progress_card) {
                const old = document.getElementById('teacherAssignmentsProgressCard');
                if (old) {
                    old.insertAdjacentHTML('afterend', data.html.progress_card);
                    old.remove();
                }
            }
            const sideInner = document.getElementById('assignedSubjectsSidePanelInner');
            if (sideInner && data.html && data.html.side_panel) {
                sideInner.innerHTML = data.html.side_panel;
            }
            const indepBody = document.getElementById('indepSubjectAssignCardBody');
            if (indepBody && data.html && data.html.indep_body) {
                indepBody.innerHTML = data.html.indep_body;
            }

            const classSet = new Set((data.assigned_class_ids || []).map(Number));
            const subjectSet = new Set((data.assigned_subject_ids || []).map(Number));
            const rp = data.required_pages || {};

            document.querySelectorAll('.class-checkbox').forEach(function(cb) {
                const id = parseInt(cb.value, 10);
                const assigned = classSet.has(id);
                cb.checked = assigned;
                const item = cb.closest('.list-group-item');
                if (!item) {
                    return;
                }
                const badge = item.querySelector('.class-assigned-badge');
                if (badge) {
                    badge.classList.toggle('d-none', !assigned);
                }
                const detach = item.querySelector('.class-detach-actions');
                if (detach) {
                    detach.classList.toggle('d-none', !assigned);
                }
            });

            document.querySelectorAll('.subject-checkbox').forEach(function(cb) {
                const id = parseInt(cb.value, 10);
                const assigned = subjectSet.has(id);
                cb.checked = assigned;
                const row = document.getElementById('subject_row_wrap_' + id);
                if (!row) {
                    return;
                }
                const badge = row.querySelector('.subject-assigned-badge');
                if (badge) {
                    badge.classList.toggle('d-none', !assigned);
                }
                const inp = row.querySelector('.subject-pages-input');
                if (inp) {
                    const v = rp[id];
                    inp.value = (v !== null && v !== undefined && v !== '') ? String(v) : '';
                }
            });

            const fc = document.getElementById('teacherAssignmentsFooterClassesCount');
            if (fc && typeof data.assigned_classes_count === 'number') {
                fc.textContent = String(data.assigned_classes_count);
            }
            const fs = document.getElementById('teacherAssignmentsFooterSubjectsCount');
            if (fs && typeof data.assigned_subjects_count === 'number') {
                fs.textContent = String(data.assigned_subjects_count);
            }

            filterSubjectsBySelectedClasses();
            filterIndepPanels();
        }

        function runTeacherAssignmentsJsonSave() {
            const form = document.getElementById('teacherAssignmentsForm');
            if (!form) {
                return;
            }
            if (inflight) {
                queued = true;
                return;
            }
            inflight = true;
            setSyncStatus('جاري الحفظ…');
            const fd = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: fd,
                credentials: 'same-origin'
            }).then(function(r) {
                const ct = r.headers.get('content-type') || '';
                if (ct.indexOf('application/json') !== -1) {
                    return r.json().then(function(json) {
                        return { ok: r.ok, status: r.status, json: json };
                    });
                }
                return r.text().then(function(t) {
                    return { ok: r.ok, status: r.status, json: { message: t } };
                });
            }).then(function(res) {
                inflight = false;
                if (res.ok && res.json && res.json.ok) {
                    applyAssignmentsSyncResponse(res.json);
                    setSyncStatus('تم الحفظ');
                    setTimeout(function() {
                        setSyncStatus('');
                    }, 2200);
                } else {
                    let msg = 'تعذر الحفظ';
                    if (res.json) {
                        if (res.json.message) {
                            msg = res.json.message;
                        } else if (res.json.errors) {
                            msg = 'تحقق من الحقول';
                        }
                    }
                    setSyncStatus(msg, true);
                }
                if (queued) {
                    queued = false;
                    window.scheduleTeacherAssignmentsAutoSave();
                }
            }).catch(function() {
                inflight = false;
                setSyncStatus('خطأ في الاتصال', true);
                if (queued) {
                    queued = false;
                    window.scheduleTeacherAssignmentsAutoSave();
                }
            });
        }

        window.scheduleTeacherAssignmentsAutoSave = function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runTeacherAssignmentsJsonSave, 420);
        };

        window.flushTeacherAssignmentsJsonSave = function() {
            clearTimeout(debounceTimer);
            runTeacherAssignmentsJsonSave();
        };

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('teacherAssignmentsForm');
            if (!form) {
                return;
            }
            form.addEventListener('change', function(e) {
                if (e.target.matches('.class-checkbox') || e.target.matches('.subject-checkbox')) {
                    setTimeout(function() {
                        if (window.scheduleTeacherAssignmentsAutoSave) {
                            window.scheduleTeacherAssignmentsAutoSave();
                        }
                    }, 0);
                }
            });
            form.addEventListener('input', function(e) {
                if (e.target.matches('.subject-pages-input')) {
                    if (window.scheduleTeacherAssignmentsAutoSave) {
                        window.scheduleTeacherAssignmentsAutoSave();
                    }
                }
            });
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearTimeout(debounceTimer);
                runTeacherAssignmentsJsonSave();
            });
        });
    })();

    document.addEventListener('DOMContentLoaded', function() {
        bindClassCheckboxesToSubjects();
        syncAllSubjectsWithClasses();
        filterSubjectsBySelectedClasses();
        filterIndepPanels();
    });
</script>
@stop
