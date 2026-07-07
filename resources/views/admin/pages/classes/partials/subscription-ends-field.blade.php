<div class="class-form-card">
    <div class="class-form-card__header">
        <span class="class-form-card__header-icon"><i class="bi bi-calendar-x"></i></span>
        <div class="class-form-card__header-text">
            <div class="class-form-card__title">نهاية اشتراك الصف</div>
            <p class="class-form-card__desc">تحديد موعد إنهاء اشتراك جميع الطلاب في الصف ومواد الباقة تلقائياً</p>
        </div>
    </div>
    <div class="class-form-card__body">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="class-form-field">
                    <label class="form-label" for="subscription_ends_at">
                        تاريخ نهاية الاشتراك <span class="text-muted fw-normal">(اختياري)</span>
                    </label>
                    <input type="date"
                           name="subscription_ends_at"
                           id="subscription_ends_at"
                           class="form-control @error('subscription_ends_at') is-invalid @enderror"
                           value="{{ old('subscription_ends_at', isset($class) && $class->subscription_ends_at ? $class->subscription_ends_at->format('Y-m-d') : '') }}"
                           @if(!isset($class)) min="{{ now()->format('Y-m-d') }}" @endif>
                    @error('subscription_ends_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="class-form-hint">
                        <i class="bi bi-hourglass-split"></i>
                        <span>عند الوصول لهذا التاريخ يُلغى اشتراك جميع الطلاب من الصف ومواد الباقة تلقائياً. يبقى الطالب مسجلاً حتى نهاية اليوم المحدد.</span>
                    </div>
                </div>
            </div>
            @if(isset($class) && $class->subscription_revoked_at)
                <div class="col-lg-6">
                    <div class="alert alert-warning mb-0 h-100 d-flex align-items-center">
                        <div>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            تم تنفيذ الإلغاء الجماعي للاشتراكات في
                            {{ $class->subscription_revoked_at->format('Y-m-d H:i') }}.
                            تمديد التاريخ لاحقاً لا يعيد تفعيل الطلاب الملغيين تلقائياً.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
