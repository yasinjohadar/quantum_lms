@php
    $selectedScope = old('scope', ($originalWasLessonQuiz ?? false) ? 'lesson' : 'unit');
@endphp

<input type="hidden" name="quiz_relink" value="1">

<div class="alert alert-warning py-2 mb-3">
    <i class="bi bi-link-45deg me-1"></i>
    هذا اختبار <strong>منسوخ</strong> ويحتاج ربطاً بمكان جديد في المنهج.
    لن يظهر للطلاب حتى تكمل الربط وتفعّل الاختبار.
</div>

<div class="mb-3">
    <label class="form-label d-block">نوع الاختبار <span class="text-danger">*</span></label>
    <div class="form-check form-check-inline">
        <input class="form-check-input quiz-relink-scope" type="radio" name="scope" id="relinkScopeUnit" value="unit"
               {{ $selectedScope === 'unit' ? 'checked' : '' }}>
        <label class="form-check-label" for="relinkScopeUnit">اختبار عام للوحدة</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input quiz-relink-scope" type="radio" name="scope" id="relinkScopeLesson" value="lesson"
               {{ $selectedScope === 'lesson' ? 'checked' : '' }}>
        <label class="form-check-label" for="relinkScopeLesson">اختبار مرتبط بدرس</label>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="relinkStageSelect">المرحلة</label>
        <select class="form-select" id="relinkStageSelect">
            <option value="">كل المراحل</option>
            @foreach($stages as $stage)
                <option value="{{ $stage->id }}" {{ (string) old('stage_id', $selectedStageId ?? '') === (string) $stage->id ? 'selected' : '' }}>
                    {{ $stage->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="relinkClassSelect">الصف</label>
        <select class="form-select" id="relinkClassSelect">
            <option value="">كل الصفوف</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="relinkSubjectSelect">المادة <span class="text-danger">*</span></label>
        <select name="subject_id" class="form-select" id="relinkSubjectSelect" required>
            <option value="">-- اختر المادة --</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="relinkSectionSelect">القسم <span class="text-danger">*</span></label>
        <select name="section_id" class="form-select" id="relinkSectionSelect" required>
            <option value="">-- اختر القسم --</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="relinkUnitSelect">الوحدة <span class="text-danger">*</span></label>
        <select name="unit_id" class="form-select" id="relinkUnitSelect" required>
            <option value="">-- اختر الوحدة --</option>
        </select>
    </div>
    <div class="col-md-6 mb-3 quiz-relink-lesson-field {{ $selectedScope === 'lesson' ? '' : 'd-none' }}" id="relinkLessonField">
        <label class="form-label" for="relinkLessonSelect">الدرس <span class="text-danger">*</span></label>
        <select name="lesson_id" class="form-select" id="relinkLessonSelect">
            <option value="">-- اختر الدرس --</option>
        </select>
    </div>
</div>
