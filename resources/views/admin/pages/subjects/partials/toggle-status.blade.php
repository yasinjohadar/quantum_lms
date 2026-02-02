<div class="modal fade" id="toggleSubjectStatus{{ $subject->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            {{-- رأس المودال --}}
            <div class="border-0 text-center pt-4 px-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                    <span class="me-2 fs-4 {{ $subject->is_active ? 'text-warning' : 'text-success' }}">
                        <i class="{{ $subject->is_active ? 'bi bi-exclamation-triangle-fill' : 'bi bi-check-circle-fill' }}"></i>
                    </span>
                    <h5 class="modal-title mb-0 fw-bold">
                        تغيير حالة المادة
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 start-0 m-3"
                        data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            {{-- الأيقونة الدائرية --}}
            <div class="text-center mt-2">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 {{ $subject->is_active ? 'bg-warning' : 'bg-success' }} text-white shadow-sm"
                     style="width:96px;height:96px;">
                    <i class="{{ $subject->is_active ? 'bi bi-power fs-1' : 'bi bi-book fs-1' }}"></i>
                </div>
            </div>

            {{-- النص + الفورم --}}
            <form method="POST" action="{{ route('admin.subjects.toggle-status', $subject) }}">
                @csrf

                <div class="modal-body text-center pt-0 pb-3 px-4">
                    <p class="mb-1 text-muted">
                        @if($subject->is_active)
                            هل تريد <span class="fw-bold text-danger">إلغاء تفعيل</span> هذه المادة؟
                        @else
                            هل تريد <span class="fw-bold text-success">تفعيل</span> هذه المادة؟
                        @endif
                    </p>
                    <p class="fw-bold mb-1" style="font-size:1.05rem;">
                        {{ $subject->name }}
                    </p>
                    <p class="mb-3 text-muted small">
                        يمكنك تغيير حالة المادة في أي وقت من قائمة المواد.
                    </p>
                </div>

                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                    <button type="submit"
                            class="btn px-4 {{ $subject->is_active ? 'btn-danger' : 'btn-success' }}">
                        @if($subject->is_active)
                            إلغاء تفعيل المادة
                        @else
                            تفعيل المادة
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
