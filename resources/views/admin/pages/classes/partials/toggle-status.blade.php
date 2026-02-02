<div class="modal fade" id="toggleClassStatus{{ $class->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            {{-- رأس المودال --}}
            <div class="border-0 text-center pt-4 px-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                    <span class="me-2 fs-4 {{ $class->is_active ? 'text-warning' : 'text-success' }}">
                        <i class="{{ $class->is_active ? 'bi bi-exclamation-triangle-fill' : 'bi bi-check-circle-fill' }}"></i>
                    </span>
                    <h5 class="modal-title mb-0 fw-bold">
                        تغيير حالة الصف
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 start-0 m-3"
                        data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            {{-- الأيقونة الدائرية --}}
            <div class="text-center mt-2">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 {{ $class->is_active ? 'bg-warning' : 'bg-success' }} text-white shadow-sm"
                     style="width:96px;height:96px;">
                    <i class="{{ $class->is_active ? 'bi bi-power fs-1' : 'bi bi-building fs-1' }}"></i>
                </div>
            </div>

            {{-- النص + الفورم --}}
            <form method="POST" action="{{ route('admin.classes.toggle-status', $class) }}">
                @csrf

                <div class="modal-body text-center pt-0 pb-3 px-4">
                    <p class="mb-1 text-muted">
                        @if($class->is_active)
                            هل تريد <span class="fw-bold text-danger">إلغاء تفعيل</span> هذا الصف؟
                        @else
                            هل تريد <span class="fw-bold text-success">تفعيل</span> هذا الصف؟
                        @endif
                    </p>
                    <p class="fw-bold mb-1" style="font-size:1.05rem;">
                        {{ $class->name }}
                        @if($class->stage)
                            <span class="text-muted">({{ $class->stage->name }})</span>
                        @endif
                    </p>
                    <p class="mb-3 text-muted small">
                        يمكنك تغيير حالة الصف في أي وقت من قائمة الصفوف.
                    </p>
                </div>

                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                    <button type="submit"
                            class="btn px-4 {{ $class->is_active ? 'btn-danger' : 'btn-success' }}">
                        @if($class->is_active)
                            إلغاء تفعيل الصف
                        @else
                            تفعيل الصف
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
