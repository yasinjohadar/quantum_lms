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

            <form action="{{ route('admin.teachers.assignments.update', $teacher->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- الصفوف المخصصة -->
                    <div class="col-xl-6">
                        <div class="card" id="classSection">
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
                                            <div class="list-group-item" data-stage-id="{{ $class->stage_id ?? '' }}">
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
                                                            @if($assignedClasses->contains('id', $class->id))
                                                                <span class="badge bg-success">مخصص</span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted text-center py-4">لا توجد صفوف متاحة</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- المواد المخصصة -->
                    <div class="col-xl-6">
                        <div class="card" id="subjectSection">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-book me-2"></i>
                                    المواد المخصصة
                                </h6>
                                <small class="text-muted">تحديد المواد التي يمكن للمعلم الوصول إليها مباشرة</small>
                            </div>
                            <div class="card-body">
                                @if($allSubjects->count() > 0)
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="selectAllSubjects()">
                                                    <i class="bi bi-check-all me-1"></i> تحديد الكل
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="deselectAllSubjects()">
                                                    <i class="bi bi-x-lg me-1"></i> إلغاء تحديد الكل
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">فلترة حسب الصف:</label>
                                        <select class="form-select form-select-sm" id="classFilter" onchange="filterByClass()">
                                            <option value="">كل الصفوف</option>
                                            @foreach($allClasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="list-group" style="max-height: 500px; overflow-y: auto;">
                                        @foreach($allSubjects as $subject)
                                            @php
                                                $assignedSubject = $assignedSubjects->firstWhere('id', $subject->id);
                                                $currentRequiredPages = $assignedSubject?->pivot?->required_pages ?? '';
                                            @endphp
                                            <div class="list-group-item" data-class-id="{{ $subject->class_id ?? '' }}">
                                                <div class="form-check d-flex align-items-center flex-wrap gap-2">
                                                    <input class="form-check-input subject-checkbox" 
                                                           type="checkbox" 
                                                           name="subjects[]" 
                                                           value="{{ $subject->id }}" 
                                                           id="subject_{{ $subject->id }}"
                                                           {{ $assignedSubjects->contains('id', $subject->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label flex-grow-1" for="subject_{{ $subject->id }}">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $subject->name }}</strong>
                                                                @if($subject->schoolClass)
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-building me-1"></i>
                                                                        {{ $subject->schoolClass->name }}
                                                                        @if($subject->schoolClass->stage)
                                                                            - {{ $subject->schoolClass->stage->name }}
                                                                        @endif
                                                                    </small>
                                                                @endif
                                                            </div>
                                                            @if($assignedSubjects->contains('id', $subject->id))
                                                                <span class="badge bg-success">مخصص</span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                    <div class="d-flex align-items-center gap-1" style="min-width: 140px;">
                                                        <label class="form-label mb-0 small text-muted">صفحات مطلوبة:</label>
                                                        <input type="number" 
                                                               name="required_pages[{{ $subject->id }}]" 
                                                               class="form-control form-control-sm" 
                                                               min="0" 
                                                               placeholder="0" 
                                                               value="{{ $currentRequiredPages !== '' ? (int)$currentRequiredPages : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted text-center py-4">لا توجد مواد متاحة</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

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
                                            <span class="badge bg-primary">{{ $assignedClasses->count() }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1">
                                            <strong>المواد المخصصة حالياً:</strong> 
                                            <span class="badge bg-primary">{{ $assignedSubjects->count() }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4 mt-2">
                                        <label class="form-label mb-1 small"><strong>عدد الدروس الأسبوعية المطلوبة:</strong></label>
                                        <input type="number" name="weekly_lessons_target" class="form-control form-control-sm" min="0" placeholder="0" value="{{ $teacher->weekly_lessons_target ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- زر الحفظ -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> حفظ التغييرات
                            </button>
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
    function selectAllClasses() {
        document.querySelectorAll('.class-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
        });
    }

    function deselectAllClasses() {
        document.querySelectorAll('.class-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
    }

    function selectAllSubjects() {
        document.querySelectorAll('.subject-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
        });
    }

    function deselectAllSubjects() {
        document.querySelectorAll('.subject-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
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

    function filterByClass() {
        const classId = document.getElementById('classFilter').value;
        const subjectItems = document.querySelectorAll('#subjectSection .list-group-item');
        
        subjectItems.forEach(item => {
            const subjectClassId = item.getAttribute('data-class-id');
            if (!classId || subjectClassId === classId) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@stop
