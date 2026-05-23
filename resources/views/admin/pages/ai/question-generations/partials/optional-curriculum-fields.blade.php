@php
    $fieldPrefix = $fieldPrefix ?? 'opt';
    $classSelectId = $fieldPrefix === '' ? 'class_id' : $fieldPrefix . '_class_id';
    $subjectSelectId = $fieldPrefix === '' ? 'subject_id' : $fieldPrefix . '_subject_id';
    $unitSelectId = $fieldPrefix === '' ? 'unit_id' : $fieldPrefix . '_unit_id';
    $lockedSubject = $lockedSubject ?? null;
    $prefillClassId = $prefillClassId ?? null;
    $prefillSubjectId = $prefillSubjectId ?? null;
    $prefillUnitId = $prefillUnitId ?? null;
    $disabledByDefault = $disabledByDefault ?? false;
@endphp

@if(empty($lockedSubject))
    <div class="mb-3">
        <label for="{{ $classSelectId }}" class="form-label">الصف (اختياري)</label>
        <select class="form-select optional-curriculum-class" id="{{ $classSelectId }}" name="class_id" @if($disabledByDefault) disabled @endif>
            <option value="">— بدون تحديد —</option>
            @foreach($schoolClasses as $schoolClass)
                <option value="{{ $schoolClass->id }}" {{ (string) old('class_id', $prefillClassId ?? '') === (string) $schoolClass->id ? 'selected' : '' }}>{{ $schoolClass->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="{{ $subjectSelectId }}" class="form-label">المادة (اختياري)</label>
        <select class="form-select optional-curriculum-subject" id="{{ $subjectSelectId }}" name="subject_id" @if($disabledByDefault || !$prefillClassId) disabled @endif>
            <option value="">{{ $prefillClassId ? 'اختر المادة' : 'اختر الصف أولاً' }}</option>
        </select>
    </div>
@else
    <div class="mb-3">
        <label class="form-label">المادة</label>
        <p class="form-control-plaintext mb-0">
            <strong>{{ $lockedSubject->name }}</strong>
            @if($lockedSubject->schoolClass)
                <span class="text-muted">({{ $lockedSubject->schoolClass->name }})</span>
            @endif
        </p>
    </div>
@endif

<div class="mb-3">
    <label for="{{ $unitSelectId }}" class="form-label">الوحدة (اختياري)</label>
    <select class="form-select optional-curriculum-unit" id="{{ $unitSelectId }}" name="unit_id" @if($disabledByDefault || empty($lockedSubject) && !$prefillSubjectId) disabled @endif>
        <option value="">{{ ($lockedSubject || $prefillSubjectId) ? 'اختر الوحدة' : 'اختر المادة أولاً' }}</option>
    </select>
</div>
