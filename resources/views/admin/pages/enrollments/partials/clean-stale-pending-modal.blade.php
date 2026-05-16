@props([
    'action',
    'modalId' => 'cleanStalePendingModal',
    'context' => 'subject',
])

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="bi bi-trash me-2"></i>تنظيف الطلبات المعلقة القديمة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ $action }}"
                  onsubmit="return confirm('هذه العملية غير قابلة للاسترجاع. سيتم حذف الطلبات المعلقة نهائياً ضمن الفلاتر الحالية إن وُجدت. هل تريد المتابعة؟');">
                @csrf
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                @if($context === 'class')
                    <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                @else
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                @endif
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        يُحذف فقط ما هو <strong>معلق</strong> وتاريخ إنشائه (<strong>created_at</strong>) أقدم من عدد الأيام أدناه.
                        تُطبَّق نفس فلاتر البحث الحالية عبر الحقول المخفية.
                    </p>
                    <div class="mb-0">
                        <label for="{{ $modalId }}_days" class="form-label">حذف الطلبات الأقدم من (بالأيام)</label>
                        <input type="number" class="form-control" id="{{ $modalId }}_days" name="days"
                               min="1" max="3650" value="30" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-trash me-1"></i>تأكيد التنظيف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
