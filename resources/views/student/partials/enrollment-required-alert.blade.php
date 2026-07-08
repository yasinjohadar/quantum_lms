@if(($studentNeedsEnrollment ?? false) || session('enrollment_required_warning'))
    @once('student-enrollment-required-alert-styles')
        <style>
            .enrollment-required-banner {
                border: 1px solid rgba(var(--danger-rgb), 0.28);
                border-inline-start: 4px solid rgb(var(--danger-rgb));
                border-radius: 0.75rem;
                background: linear-gradient(
                    135deg,
                    rgba(var(--danger-rgb), 0.1) 0%,
                    rgba(var(--danger-rgb), 0.03) 45%,
                    var(--custom-white) 100%
                );
                box-shadow: 0 0.35rem 1.25rem rgba(var(--danger-rgb), 0.12);
                overflow: hidden;
            }

            .enrollment-required-banner__icon-wrap {
                width: 3.25rem;
                height: 3.25rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(var(--danger-rgb), 0.14);
                color: rgb(var(--danger-rgb));
                flex-shrink: 0;
            }

            .enrollment-required-banner__badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.25rem 0.65rem;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgb(var(--danger-rgb));
                background: rgba(var(--danger-rgb), 0.12);
                border: 1px solid rgba(var(--danger-rgb), 0.2);
            }

            .enrollment-required-banner__title {
                font-size: 1.05rem;
                font-weight: 700;
                color: rgb(var(--danger-rgb));
                margin-bottom: 0.35rem;
            }

            .enrollment-required-banner__text {
                color: var(--default-text-color);
                line-height: 1.7;
                margin-bottom: 0;
            }

            .enrollment-required-banner__steps {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.85rem;
            }

            .enrollment-required-banner__steps-title {
                display: block;
                margin-top: 0.95rem;
                margin-bottom: 0.55rem;
                font-size: 0.82rem;
                font-weight: 700;
                color: rgb(var(--danger-rgb));
            }

            .enrollment-required-banner__step {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.35rem 0.7rem;
                border-radius: 999px;
                font-size: 0.8rem;
                font-weight: 600;
                color: var(--default-text-color);
                background: rgba(var(--danger-rgb), 0.06);
                border: 1px dashed rgba(var(--danger-rgb), 0.2);
                cursor: default;
                user-select: none;
                pointer-events: none;
            }

            .enrollment-required-banner__step-num {
                width: 1.35rem;
                height: 1.35rem;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.72rem;
                font-weight: 700;
                color: #fff;
                background: rgb(var(--danger-rgb));
            }

        </style>
    @endonce

    <div class="enrollment-required-banner mb-4" role="alert" aria-live="polite">
        <div class="p-3 p-md-4 d-flex align-items-start gap-3">
            <div class="enrollment-required-banner__icon-wrap" aria-hidden="true">
                <i class="bi bi-shield-exclamation fs-4"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <span class="enrollment-required-banner__badge mb-2">
                    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                    إجراء مطلوب
                </span>
                <h5 class="enrollment-required-banner__title">لم يتم تسجيلك بعد في أي صف أو مادة</h5>
                <p class="enrollment-required-banner__text">
                    للاشتراك بأحد الصفوف فقط.
                </p>
                <span class="enrollment-required-banner__steps-title">
                    هذه خطوات توضيحية يجب اتباعها بالترتيب، وليست أزرارًا للضغط:
                </span>
                <div class="enrollment-required-banner__steps">
                    <span class="enrollment-required-banner__step">
                        <span class="enrollment-required-banner__step-num">1</span>
                        تصفّح الصفوف المتاحة
                    </span>
                    <span class="enrollment-required-banner__step">
                        <span class="enrollment-required-banner__step-num">2</span>
                        اطلب الانضمام للصف
                    </span>
                    <span class="enrollment-required-banner__step">
                        <span class="enrollment-required-banner__step-num">3</span>
                        انتظر القبول ثم تظهر دروسك
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif
