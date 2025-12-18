<div class="modal fade" id="deleteStage{{ $stage->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            {{-- رأس المودال مع العنوان وأيقونة التحذير --}}
            <div class="border-0 text-center pt-4 px-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                    <span class="me-2 fs-4 text-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    <h5 class="modal-title mb-0 fw-bold">
                        تأكيد حذف المرحلة
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 start-0 m-3"
                        data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            {{-- الأيقونة الدائرية الرئيسية --}}
            <div class="text-center mt-2">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm"
                     style="width:96px;height:96px;">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
            </div>

            {{-- نص التأكيد واسم المرحلة --}}
            <div class="modal-body text-center pt-0 pb-3 px-4">
                <p class="mb-1 text-muted">
                    هل أنت متأكد من حذف المرحلة التالية؟
                </p>
                <p class="fw-bold mb-1 text-danger" style="font-size:1.15rem;">
                    {{ $stage->name }}
                </p>
                <p class="mb-3 text-muted small">
                    سيتم حذف جميع البيانات المرتبطة بهذه المرحلة (مثل الصفوف والمواد) ولا يمكن التراجع عن هذه العملية.
                </p>
            </div>

            {{-- الأزرار في أسفل المودال --}}
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                    إلغاء
                </button>
                <form action="{{ route('admin.stages.destroy', $stage->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">
                        حذف نهائياً
                        <i class="bi bi-trash ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


