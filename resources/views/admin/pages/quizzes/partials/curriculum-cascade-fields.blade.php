@php
    $cascadeRequireStage = (bool) ($cascadeRequireStage ?? false);
    $stagePlaceholder = $cascadeRequireStage ? 'اختر المرحلة' : 'كل المراحل';
    $classPlaceholder = $cascadeRequireStage ? 'اختر المرحلة أولاً' : 'كل الصفوف';
    $subjectPlaceholder = $cascadeRequireStage ? 'اختر الصف أولاً' : 'كل المواد';
    $unitPlaceholder = $cascadeRequireStage ? 'اختر المادة أولاً' : 'كل الوحدات';
@endphp
<div class="row g-3 ile-cascade">
    <div class="col-md-6">
        <label class="form-label" for="stageSelect">المرحلة</label>
        <select class="form-select" id="stageSelect">
            <option value="">{{ $stagePlaceholder }}</option>
            @foreach($stages as $stage)
                <option value="{{ $stage->id }}" {{ (string) old('stage_id', $selectedStageId ?? '') === (string) $stage->id ? 'selected' : '' }}>
                    {{ $stage->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="classSelect">الصف</label>
        <select class="form-select" id="classSelect" @disabled($cascadeRequireStage && empty($selectedStageId) && empty(old('stage_id')))>
            <option value="">{{ $classPlaceholder }}</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="subjectSelect">المادة</label>
        <select name="subject_id" class="form-select" id="subjectSelect" @disabled($cascadeRequireStage && empty($selectedClassId) && empty(old('class_id')))>
            <option value="">{{ $subjectPlaceholder }}</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="unitSelect">الوحدة <span class="text-muted fw-normal">(اختياري)</span></label>
        <select name="unit_id" class="form-select" id="unitSelect" @disabled($cascadeRequireStage && empty($selectedSubjectId) && empty(old('subject_id')))>
            <option value="">{{ $unitPlaceholder }}</option>
        </select>
    </div>
</div>
