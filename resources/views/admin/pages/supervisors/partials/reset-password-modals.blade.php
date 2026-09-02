@foreach($supervisors as $supervisor)
    @can('supervisor-assignment-update')
        <div class="modal fade" id="resetSupervisorPassword{{ $supervisor->id }}" tabindex="-1" aria-labelledby="resetSupervisorPasswordLabel{{ $supervisor->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">

                    <div class="border-0 text-center pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-center mb-3 gap-2">
                            <span class="fs-4 text-warning">
                                <i class="bi bi-key-fill"></i>
                            </span>
                            <h5 class="modal-title mb-0 fw-bold" id="resetSupervisorPasswordLabel{{ $supervisor->id }}">
                                إعادة تعيين كلمة مرور المشرف
                            </h5>
                        </div>
                        <button type="button" class="btn-close position-relative" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>

                    <form method="POST" action="{{ route('admin.supervisors.reset-password', $supervisor->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="supervisor_id" value="{{ $supervisor->id }}">

                        <div class="modal-body pt-0 pb-3 px-4">
                            <p class="text-muted small mb-3">
                                سيتم إرسال الاسم ورقم الهاتف وكلمة المرور الجديدة إلى المشرف
                                <strong>{{ $supervisor->name }}</strong> عبر واتساب فور الحفظ.
                            </p>

                            <div class="mb-3">
                                <label for="resetSupervisorPhone{{ $supervisor->id }}" class="form-label">رقم الهاتف (لإرسال واتساب)</label>
                                <input type="text" name="phone" id="resetSupervisorPhone{{ $supervisor->id }}" class="form-control"
                                       value="{{ old('supervisor_id') == $supervisor->id ? old('phone') : $supervisor->phone }}"
                                       placeholder="مثال: 0501234567" required>
                                <small class="text-muted">يمكن تعديله هنا إذا كان الرقم المسجَّل غير صحيح.</small>
                                @if (old('supervisor_id') == $supervisor->id)
                                    @error('phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <div class="mb-2">
                                <label for="resetSupervisorPasswordField{{ $supervisor->id }}" class="form-label">كلمة المرور الجديدة</label>
                                <div class="input-group">
                                    <input type="text" name="password" id="resetSupervisorPasswordField{{ $supervisor->id }}"
                                           class="form-control" required minlength="8" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary" title="نسخ كلمة المرور"
                                            onclick="copySupervisorPassword({{ $supervisor->id }})">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                @if (old('supervisor_id') == $supervisor->id)
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <div class="mb-3 d-flex flex-wrap align-items-center gap-2" id="resetSupervisorPasswordSuggestions{{ $supervisor->id }}">
                                {{-- تُملأ عبر JS باقتراحات كلمات مرور --}}
                            </div>
                            <button type="button" class="btn btn-sm btn-light border mb-3"
                                    onclick="generateSupervisorPasswordSuggestions({{ $supervisor->id }})">
                                <i class="bi bi-arrow-repeat me-1"></i> توليد اقتراحات جديدة
                            </button>

                            <div class="mb-3">
                                <label for="resetSupervisorPasswordConfirm{{ $supervisor->id }}" class="form-label">تأكيد كلمة المرور</label>
                                <input type="text" name="password_confirmation" id="resetSupervisorPasswordConfirm{{ $supervisor->id }}"
                                       class="form-control" required minlength="8">
                                @if (old('supervisor_id') == $supervisor->id)
                                    @error('password_confirmation')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">
                                إلغاء
                            </button>
                            <button type="submit" class="btn btn-warning px-4">
                                تعيين وإرسال عبر واتساب
                                <i class="bi bi-whatsapp ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endforeach
