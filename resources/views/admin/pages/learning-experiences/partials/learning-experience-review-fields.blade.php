@php
    $mandatoryReview = $mandatoryReview ?? \App\Models\SystemSetting::learningExperienceMandatoryReviewEnabled();
    $fieldId = $fieldId ?? 'ileReview';
    $isEdit = $isEdit ?? false;
    $experience = $experience ?? null;
    $isContentUploader = auth()->user()->shouldSubmitContentForReview();

    $statusBadge = function (?\App\InteractiveLearning\Models\LearningExperience $exp) {
        if (! $exp) {
            return '<span class="badge bg-secondary">مسودة</span>';
        }
        if ($exp->isRejected()) {
            return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> مرفوض</span>';
        }
        return match ($exp->status) {
            \App\InteractiveLearning\Models\LearningExperience::STATUS_REVIEW => '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>',
            \App\InteractiveLearning\Models\LearningExperience::STATUS_PUBLISHED => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> منشور</span>',
            \App\InteractiveLearning\Models\LearningExperience::STATUS_ARCHIVED => '<span class="badge bg-dark">مؤرشف</span>',
            default => '<span class="badge bg-secondary">مسودة</span>',
        };
    };
@endphp

@if($isEdit && $experience)
    {{-- صفحة التعديل: عرض الحالة/الملاحظات فقط — الإجراءات (إرسال/موافقة/رفض) أزرار مستقلة أسفل هذا القسم. --}}
    <div class="mb-2">
        <label class="form-label small">حالة المراجعة:</label>
        {!! $statusBadge($experience) !!}
    </div>
    @if($experience->review_notes)
        <div class="alert {{ $experience->isRejected() ? 'alert-danger' : 'alert-info' }} mt-2 small mb-0 py-2">
            <strong>ملاحظات المراجعة:</strong><br>{{ $experience->review_notes }}
        </div>
    @endif
@else
    {{-- المعلمون والمشرفون: مسار المراجعة. الأدمن فقط: تفعيل مباشر. --}}
    @if($isContentUploader && $mandatoryReview)
        <div class="alert alert-info mb-0 py-2 small">
            <i class="bi bi-info-circle me-1"></i>
            سيُرسل هذا الاختبار التفاعلي تلقائياً لقائمة المراجعة بعد إكمال الأسئلة. لن يظهر للطلاب حتى تتم الموافقة.
        </div>
    @elseif($isContentUploader)
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="submit_for_review" value="1" id="{{ $fieldId }}">
            <label class="form-check-label" for="{{ $fieldId }}">إرسال للمراجعة</label>
        </div>
        <small class="text-muted d-block">سيتم إرسال الاختبار التفاعلي لقائمة المراجعة للموافقة قبل ظهوره للطلاب</small>
    @else
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ $fieldId }}" checked>
            <label class="form-check-label" for="{{ $fieldId }}">تفعيل الاختبار التفاعلي</label>
            <small class="text-muted d-block">يُنصح بإضافة الأسئلة أولاً ثم التفعيل</small>
        </div>
    @endif
@endif
