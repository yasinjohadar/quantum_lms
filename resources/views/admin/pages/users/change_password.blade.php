<div class="modal fade" id="change_password{{ $user->id }}" tabindex="-1" aria-labelledby="changePasswordLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            {{-- رأس المودال مع العنوان وأيقونة القفل --}}
            <div class="border-0 text-center pt-4 px-4">
                <div class="d-flex align-items-center justify-content-center mb-3 gap-2">
                    <span class="fs-4 text-primary">
                        <i class="bi bi-key-fill"></i>
                    </span>
                    <h5 class="modal-title mb-0 fw-bold" id="changePasswordLabel{{ $user->id }}">
                        تعديل كلمة المرور
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form method="POST" action="{{ route('users.update-password', $user->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-body pt-0 pb-3 px-4">
                    <div class="mb-3">
                        <label for="password{{ $user->id }}" class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="password" id="password{{ $user->id }}" class="form-control" required>
                        @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation{{ $user->id }}" class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" id="password_confirmation{{ $user->id }}" class="form-control" required>
                        @error('password_confirmation')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <p class="text-muted small mb-0">
                        سيتم تسجيل خروج المستخدم من جميع الجلسات الحالية بعد تغيير كلمة المرور.
                    </p>
                </div>

                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        حفظ كلمة المرور
                        <i class="bi bi-check2 ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
