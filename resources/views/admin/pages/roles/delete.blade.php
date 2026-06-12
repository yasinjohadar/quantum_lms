<div class="modal fade" id="delete{{ $role->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="border-0 text-center pt-4 px-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                    <span class="me-2 fs-4 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <h5 class="modal-title mb-0 fw-bold">حذف الدور</h5>
                </div>
                <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('roles.destroy', 'test') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" value="{{ $role->id }}">
                <div class="modal-body text-center pt-0 pb-3 px-4">
                    <p class="mb-1 text-muted">هل تريد حذف الدور:</p>
                    <p class="fw-bold mb-0">{{ $role->name }}</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i> حذف</button>
                </div>
            </form>
        </div>
    </div>
</div>
