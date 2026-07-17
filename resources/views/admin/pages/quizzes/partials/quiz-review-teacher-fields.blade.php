@php
    $mandatoryReview = $mandatoryReview ?? \App\Models\SystemSetting::quizMandatoryReviewEnabled();
    $fieldId = $fieldId ?? 'quizReview';
    $isEdit = $isEdit ?? false;
    $quiz = $quiz ?? null;
    $isContentUploader = auth()->user()->isQuizContentUploader();
@endphp

{{-- المعلمون والمشرفون: مسار المراجعة. الأدمن فقط: تفعيل مباشر. --}}
@if($isContentUploader && $mandatoryReview)
    @if($isEdit && $quiz)
        <div class="mb-2">
            <label class="form-label small">حالة المراجعة:</label>
            @if($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>
            @elseif($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_APPROVED)
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> معتمد</span>
            @elseif($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_REJECTED)
                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
            @else
                <span class="badge bg-secondary">مسودة</span>
            @endif
        </div>
        @if($quiz->review_notes)
            <div class="alert alert-info mt-2 small mb-2 py-2">
                <strong>ملاحظات المراجعة:</strong><br>{{ $quiz->review_notes }}
            </div>
        @endif
    @endif
    <div class="alert alert-info mb-0 py-2 small">
        <i class="bi bi-info-circle me-1"></i>
        @if($isEdit)
            سيُعاد إرسال هذا الاختبار تلقائياً لقائمة المراجعة عند الحفظ. لن يظهر للطلاب حتى تتم الموافقة.
        @else
            سيُرسل هذا الاختبار تلقائياً لقائمة المراجعة. لن يظهر للطلاب حتى تتم الموافقة.
        @endif
    </div>
@elseif($isContentUploader)
    @if($isEdit && $quiz)
        <div class="mb-2">
            <label class="form-label small">حالة المراجعة:</label>
            @if($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>
            @elseif($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_APPROVED)
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> معتمد</span>
            @elseif($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_REJECTED)
                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
            @else
                <span class="badge bg-secondary">مسودة</span>
            @endif
        </div>
        @if($quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
            <input type="hidden" name="is_active" value="0">
            <p class="small text-muted mb-0">لا يمكن تغيير خيار النشر أثناء قيد المراجعة.</p>
        @else
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="{{ $fieldId }}"
                    {{ ($quiz->is_active || $quiz->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $fieldId }}">إرسال للمراجعة</label>
            </div>
        @endif
        @if($quiz->review_notes)
            <div class="alert alert-info mt-2 small mb-0">
                <strong>ملاحظات المراجعة:</strong><br>{{ $quiz->review_notes }}
            </div>
        @endif
    @else
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="{{ $fieldId }}">
            <label class="form-check-label" for="{{ $fieldId }}">إرسال للمراجعة</label>
        </div>
        <small class="text-muted d-block">سيتم إرسال الاختبار لقائمة المراجعة للموافقة قبل ظهوره للطلاب</small>
    @endif
@else
    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="is_active" id="{{ $fieldId }}"
            {{ ($isEdit ? (($quiz->is_active ?? false) || ($quiz->is_published ?? false)) : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $fieldId }}">تفعيل الاختبار</label>
        <small class="text-muted d-block">يُنصح بإضافة الأسئلة أولاً ثم التفعيل</small>
    </div>
@endif
