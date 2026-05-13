@php
    $assignedSubject = $assignedSubjects->firstWhere('id', $subject->id);
    $currentRequiredPages = $assignedSubject?->pivot?->required_pages ?? '';
    $compactClassLabel = !empty($compactClassLabel);
@endphp
<div class="list-group-item subject-main-row py-2 px-2 border-0 border-bottom" id="subject_row_wrap_{{ $subject->id }}" data-class-id="{{ $subject->class_id ?? '' }}" data-subject-id="{{ $subject->id }}">
    <div class="d-flex align-items-start flex-wrap gap-2">
        <div class="form-check d-flex align-items-center flex-wrap gap-2 flex-grow-1">
            <input class="form-check-input subject-checkbox"
                   type="checkbox"
                   name="subjects[]"
                   value="{{ $subject->id }}"
                   id="subject_{{ $subject->id }}"
                   {{ $assignedSubjects->contains('id', $subject->id) ? 'checked' : '' }}>
            <label class="form-check-label flex-grow-1" for="subject_{{ $subject->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($compactClassLabel)
                            <strong class="small">{{ $subject->name }}</strong>
                        @else
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
                        @endif
                    </div>
                    <span class="badge bg-success subject-assigned-badge {{ $assignedSubjects->contains('id', $subject->id) ? '' : 'd-none' }}">مخصص</span>
                </div>
            </label>
            <div class="d-flex align-items-center gap-1 flex-shrink-0" style="min-width: {{ $compactClassLabel ? '120' : '150' }}px;">
                <label class="form-label mb-0 small text-muted">{{ $compactClassLabel ? 'صفحات:' : 'صفحات مطلوبة:' }}</label>
                <input type="number"
                       name="required_pages[{{ $subject->id }}]"
                       class="form-control form-control-sm subject-pages-input"
                       data-subject-id="{{ $subject->id }}"
                       min="0"
                       placeholder="0"
                       value="{{ $currentRequiredPages !== '' ? (int) $currentRequiredPages : '' }}">
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0 border-start ps-2 ms-auto">
            <div class="form-check form-check-reverse m-0">
                <input type="checkbox" class="form-check-input subject-bulk-pick" id="bulk_pick_{{ $subject->id }}" title="تحديد للفصل الجماعي">
                <label class="form-check-label small text-muted" for="bulk_pick_{{ $subject->id }}" style="font-size: 0.65rem;">للفصل الجماعي</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="detachSubject({{ $subject->id }})" title="فصل هذه المادة">
                فصل
            </button>
        </div>
    </div>
</div>
