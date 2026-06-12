@php
    $mandatoryReview = $mandatoryReview ?? \App\Models\SystemSetting::lessonMandatoryReviewEnabled();
    $fieldId = $fieldId ?? 'lessonReview';
    $isEdit = $isEdit ?? false;
    $lesson = $lesson ?? null;
@endphp

@if(auth()->user()->canReviewContent())
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="{{ $fieldId }}"
            {{ ($isEdit ? ($lesson->is_active ?? false) : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $fieldId }}">الدرس نشط</label>
    </div>
@elseif($mandatoryReview)
    @if($isEdit && $lesson)
        <div class="mb-2">
            <label class="form-label small">حالة المراجعة:</label>
            @if($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_PENDING)
                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>
            @elseif($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_APPROVED)
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> معتمد</span>
            @elseif($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_REJECTED)
                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
            @else
                <span class="badge bg-secondary">مسودة</span>
            @endif
        </div>
        @if($lesson->review_notes)
            <div class="alert alert-info mt-2 small mb-2 py-2">
                <strong>ملاحظات المشرف:</strong><br>{{ $lesson->review_notes }}
            </div>
        @endif
    @endif
    <div class="alert alert-info mb-0 py-2 small">
        <i class="bi bi-info-circle me-1"></i>
        @if($isEdit)
            سيُعاد إرسال هذا الدرس تلقائياً للمشرف المسؤول عن الصف والمادة عند الحفظ. لن يظهر للطلاب حتى تتم الموافقة.
        @else
            سيُرسل هذا الدرس تلقائياً للمشرف المسؤول عن الصف والمادة. لن يظهر للطلاب حتى تتم الموافقة.
        @endif
    </div>
@else
    @if($isEdit && $lesson)
        <div class="mb-2">
            <label class="form-label small">حالة المراجعة:</label>
            @if($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_PENDING)
                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>
            @elseif($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_APPROVED)
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> معتمد</span>
            @elseif($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_REJECTED)
                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
            @else
                <span class="badge bg-secondary">مسودة</span>
            @endif
        </div>
        @if($lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_PENDING)
            <input type="hidden" name="is_active" value="0">
            <p class="small text-muted mb-0">لا يمكن تغيير خيار النشر أثناء قيد المراجعة.</p>
        @else
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="{{ $fieldId }}"
                    {{ $lesson->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $fieldId }}">إرسال للمراجعة</label>
            </div>
        @endif
        @if($lesson->review_notes)
            <div class="alert alert-info mt-2 small mb-0">
                <strong>ملاحظات المشرف:</strong><br>{{ $lesson->review_notes }}
            </div>
        @endif
    @else
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="{{ $fieldId }}">
            <label class="form-check-label" for="{{ $fieldId }}">إرسال للمراجعة</label>
        </div>
        <small class="text-muted d-block mt-1">سيتم إرسال الدرس للمشرف للمراجعة والموافقة</small>
    @endif
@endif
