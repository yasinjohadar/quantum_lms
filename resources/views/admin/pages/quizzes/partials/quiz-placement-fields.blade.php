@php
    $selectedScope = old('scope', $selectedScope ?? (($selectedLessonId ?? null) ? 'lesson' : 'unit'));
    $showCopyBanner = $showCopyBanner ?? false;
    $includeRelinkFlag = $includeRelinkFlag ?? false;
    $idPrefix = $idPrefix ?? 'quizPlacement';
@endphp

@if($includeRelinkFlag)
    <input type="hidden" name="quiz_relink" value="1">
@endif

@if($showCopyBanner)
    <div class="alert alert-warning py-2 mb-3">
        <i class="bi bi-link-45deg me-1"></i>
        هذا اختبار <strong>منسوخ</strong> ويحتاج ربطاً بمكان جديد في المنهج.
        لن يظهر للطلاب حتى تكمل الربط وتفعّل الاختبار.
    </div>
@endif

<div class="mb-3">
    <label class="form-label d-block">نوع الاختبار <span class="text-danger">*</span></label>
    <div class="form-check form-check-inline">
        <input class="form-check-input quiz-placement-scope" type="radio" name="scope" id="{{ $idPrefix }}ScopeUnit" value="unit"
               {{ $selectedScope === 'unit' ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $idPrefix }}ScopeUnit">اختبار عام للوحدة</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input quiz-placement-scope" type="radio" name="scope" id="{{ $idPrefix }}ScopeLesson" value="lesson"
               {{ $selectedScope === 'lesson' ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $idPrefix }}ScopeLesson">اختبار مرتبط بدرس</label>
    </div>
    <small class="text-muted d-block mt-1">اختر «اختبار مرتبط بدرس» ثم حدّد الدرس من القائمة بعد اختيار الوحدة.</small>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="{{ $idPrefix }}StageSelect">المرحلة</label>
        <select class="form-select quiz-placement-stage" id="{{ $idPrefix }}StageSelect" data-prefix="{{ $idPrefix }}">
            <option value="">كل المراحل</option>
            @foreach($stages as $stage)
                <option value="{{ $stage->id }}" {{ (string) old('stage_id', $selectedStageId ?? '') === (string) $stage->id ? 'selected' : '' }}>
                    {{ $stage->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="{{ $idPrefix }}ClassSelect">الصف</label>
        <select class="form-select quiz-placement-class" id="{{ $idPrefix }}ClassSelect" data-prefix="{{ $idPrefix }}">
            <option value="">كل الصفوف</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="{{ $idPrefix }}SubjectSelect">المادة <span class="text-danger">*</span></label>
        <select name="subject_id" class="form-select quiz-placement-subject" id="{{ $idPrefix }}SubjectSelect" data-prefix="{{ $idPrefix }}" required>
            <option value="">-- اختر المادة --</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="{{ $idPrefix }}SectionSelect">القسم <span class="text-danger">*</span></label>
        <select name="section_id" class="form-select quiz-placement-section" id="{{ $idPrefix }}SectionSelect" data-prefix="{{ $idPrefix }}" required>
            <option value="">-- اختر القسم --</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="{{ $idPrefix }}UnitSelect">الوحدة <span class="text-danger">*</span></label>
        <select name="unit_id" class="form-select quiz-placement-unit" id="{{ $idPrefix }}UnitSelect" data-prefix="{{ $idPrefix }}" required>
            <option value="">-- اختر الوحدة --</option>
        </select>
    </div>
    <div class="col-md-6 mb-3 quiz-placement-lesson-field {{ $selectedScope === 'lesson' ? '' : 'd-none' }}" id="{{ $idPrefix }}LessonField">
        <label class="form-label" for="{{ $idPrefix }}LessonSelect">الدرس <span class="text-danger">*</span></label>
        <select name="lesson_id" class="form-select quiz-placement-lesson" id="{{ $idPrefix }}LessonSelect" data-prefix="{{ $idPrefix }}">
            <option value="">-- اختر الدرس --</option>
        </select>
    </div>
</div>
