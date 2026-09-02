<div class="modal fade" id="resetTeacherPassword{{ $teacher->id }}" tabindex="-1" aria-labelledby="resetTeacherPasswordLabel{{ $teacher->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            <div class="border-0 text-center pt-4 px-4">
                <div class="d-flex align-items-center justify-content-center mb-3 gap-2">
                    <span class="fs-4 text-warning">
                        <i class="bi bi-key-fill"></i>
                    </span>
                    <h5 class="modal-title mb-0 fw-bold" id="resetTeacherPasswordLabel{{ $teacher->id }}">
                        إعادة تعيين كلمة مرور المعلم
                    </h5>
                </div>
                <button type="button" class="btn-close position-relative" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form method="POST" action="{{ route('admin.teachers.reset-password', $teacher->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                <div class="modal-body pt-0 pb-3 px-4">
                    <p class="text-muted small mb-3">
                        سيتم إرسال الاسم ورقم الهاتف وكلمة المرور الجديدة إلى المعلم
                        <strong>{{ $teacher->name }}</strong> عبر واتساب فور الحفظ.
                    </p>

                    <div class="mb-3">
                        <label for="resetPhone{{ $teacher->id }}" class="form-label">رقم الهاتف (لإرسال واتساب)</label>
                        <input type="text" name="phone" id="resetPhone{{ $teacher->id }}" class="form-control"
                               value="{{ old('teacher_id') == $teacher->id ? old('phone') : $teacher->phone }}"
                               placeholder="مثال: 0501234567" required>
                        <small class="text-muted">يمكن تعديله هنا إذا كان الرقم المسجَّل غير صحيح.</small>
                        @if (old('teacher_id') == $teacher->id)
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-2">
                        <label for="resetPassword{{ $teacher->id }}" class="form-label">كلمة المرور الجديدة</label>
                        <div class="input-group">
                            <input type="text" name="password" id="resetPassword{{ $teacher->id }}"
                                   class="form-control" required minlength="8" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" title="نسخ كلمة المرور"
                                    onclick="copyTeacherPassword({{ $teacher->id }})">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        @if (old('teacher_id') == $teacher->id)
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3 d-flex flex-wrap align-items-center gap-2" id="resetPasswordSuggestions{{ $teacher->id }}">
                        {{-- تُملأ عبر JS باقتراحات كلمات مرور --}}
                    </div>
                    <button type="button" class="btn btn-sm btn-light border mb-3"
                            onclick="generateTeacherPasswordSuggestions({{ $teacher->id }})">
                        <i class="bi bi-arrow-repeat me-1"></i> توليد اقتراحات جديدة
                    </button>

                    <div class="mb-3">
                        <label for="resetPasswordConfirm{{ $teacher->id }}" class="form-label">تأكيد كلمة المرور</label>
                        <input type="text" name="password_confirmation" id="resetPasswordConfirm{{ $teacher->id }}"
                               class="form-control" required minlength="8">
                        @if (old('teacher_id') == $teacher->id)
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
{{-- ملاحظة: لا تضع هنا أي <script> خاص بهذا المعلم — الصفحة تتضمن دوال JS عامة
     (generateTeacherPasswordSuggestions / copyTeacherPassword) مُعرَّفة مرة واحدة في
     admin.pages.teachers.index، لأن هذا الجزئي يُضمَّن مرة لكل معلم في الجدول، وأي
     <script> هنا سيتكرر بعدد المعلمين ويُعيد أحدهم الكتابة فوق الآخر. --}}
