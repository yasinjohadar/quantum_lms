<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="stageSelect">المرحلة (اختياري)</label>
        <select class="form-select" id="stageSelect">
            <option value="">كل المراحل</option>
            @foreach($stages as $stage)
                <option value="{{ $stage->id }}" {{ (string) old('stage_id', $selectedStageId ?? '') === (string) $stage->id ? 'selected' : '' }}>
                    {{ $stage->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="classSelect">الصف (اختياري)</label>
        <select class="form-select" id="classSelect">
            <option value="">كل الصفوف</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="subjectSelect">المادة (اختياري)</label>
        <select name="subject_id" class="form-select" id="subjectSelect">
            <option value="">كل المواد</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="unitSelect">الوحدة (اختياري)</label>
        <select name="unit_id" class="form-select" id="unitSelect">
            <option value="">كل الوحدات</option>
        </select>
    </div>
</div>
