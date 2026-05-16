@php
    $freeJoinOld = old('free_join_auto_approve');
    $freeJoinChecked = $freeJoinOld === null
        ? ($freeJoinDefault ?? true)
        : filter_var($freeJoinOld, FILTER_VALIDATE_BOOLEAN);
    $isFreeOverrideChecked = old('is_free_override', $isFreeOverrideDefault ?? false);
@endphp
<div class="col-12 {{ $isFreeOverrideChecked ? '' : 'd-none' }}" id="subject_free_join_block">
    <div class="border rounded p-3 bg-light">
        <input type="hidden" name="free_join_auto_approve" id="free_join_auto_approve_value"
               value="{{ $freeJoinChecked ? '1' : '0' }}">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="free_join_auto_approve_cb"
                   {{ $freeJoinChecked ? 'checked' : '' }}
                   onchange="document.getElementById('free_join_auto_approve_value').value = this.checked ? '1' : '0'">
            <label class="form-check-label" for="free_join_auto_approve_cb">
                قبول الانضمام للمادة المجانية تلقائياً (بدون انتظار موافقة الإدارة)
            </label>
        </div>
        <small class="form-text text-muted d-block mt-2">
            عند الإيقاف يبقى طلب الانضمام قيد المراجعة حتى موافقة الإدارة من شاشة الطلبات المعلقة؛ يُعرض للطالب تنبيه بعد إرسال الطلب.
        </small>
    </div>
</div>
