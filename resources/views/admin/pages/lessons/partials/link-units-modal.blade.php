@can('lesson-edit')
<div class="modal fade" id="linkLessonUnitsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="linkLessonUnitsModalTitle">ربط الدرس بوحدات إضافية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="linkLessonUnitsForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small mb-3" role="note">
                        <i class="bi bi-info-circle me-1"></i>
                        هذا الربط لنسخ الدرس في <strong>وحدات أخرى</strong> (نسخة كاملة متزامنة مع المرفقات واختبارات الدرس).
                        التعديل على الأصل أو النسخة يُحدَّث في الطرفين. حذف الأصل يُبقي النسخة دون تغيير.
                    </div>
                    <div class="mb-3">
                        <p class="small fw-semibold mb-1">الدرس مربوط حالياً بـ:</p>
                        <div id="currentLinkedUnitsLesson" class="small text-muted"></div>
                    </div>
                    <p class="text-muted small mb-3">اختر الصف ثم المادة ثم القسم ثم الوحدة الهدف، ثم اضغط <strong>إضافة</strong> أو مباشرة <strong>حفظ الربط</strong>.</p>
                    <div id="linkedUnitsListLesson" class="mb-3">
                        <p id="linkedUnitsListLessonEmptyHint" class="small text-muted mb-0">لم تُضف وحدات للربط بعد.</p>
                    </div>
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-3">
                            <label class="form-label small">الصف</label>
                            <select class="form-select form-select-sm" id="lessonLinkClassSelect">
                                <option value="">-- اختر الصف --</option>
                                @foreach($linkableClasses ?? [] as $cls)
                                    <option value="{{ $cls['id'] }}">{{ !empty($cls['stage_name'] ?? null) ? $cls['stage_name'].' / ' : '' }}{{ $cls['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">المادة</label>
                            <select class="form-select form-select-sm" id="lessonLinkSubjectSelect" disabled>
                                <option value="">-- اختر المادة --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">القسم</label>
                            <select class="form-select form-select-sm" id="lessonLinkSectionSelect" disabled>
                                <option value="">-- اختر القسم --</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">الوحدة</label>
                            <select class="form-select form-select-sm" id="lessonLinkUnitSelect" disabled>
                                <option value="">-- اختر الوحدة --</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-success w-100" id="addLessonLinkedUnitBtn" title="إضافة وحدة" disabled>
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> حفظ الربط
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
