@php
    $supervisorWhatsappDigits = $supervisorWhatsappDigits ?? \App\Models\SystemSetting::supervisorWhatsappDigits();
    $hasSupervisorWa = $supervisorWhatsappDigits !== '';
@endphp

@once('enrollment-pending-review-modal-styles')
    <style>
        .enrollment-pending-review-modal__content {
            border: 0;
            border-radius: 1.5rem;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(var(--primary-rgb), 0.05) 0%, var(--custom-white) 28%, var(--custom-white) 100%);
            box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, 0.2);
        }

        .enrollment-pending-review-modal__body {
            padding: 2.25rem 1.5rem 1.75rem;
        }

        .enrollment-pending-review-modal__hero {
            width: clamp(96px, 22vw, 136px);
            height: clamp(96px, 22vw, 136px);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.14) 0%, rgba(var(--primary-rgb), 0.07) 100%);
            color: var(--primary-color);
            margin-bottom: 1.25rem;
        }

        .enrollment-pending-review-modal__hero i {
            font-size: clamp(3rem, 10vw, 4.2rem);
        }

        .enrollment-pending-review-modal__alert {
            border: 1px solid rgba(239, 68, 68, 0.16);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.09) 0%, rgba(239, 68, 68, 0.03) 100%);
            padding: 1rem;
        }

        [data-theme-mode="dark"] .enrollment-pending-review-modal__content,
        [data-bs-theme="dark"] .enrollment-pending-review-modal__content {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.96) 100%);
        }

        [data-theme-mode="dark"] .enrollment-pending-review-modal__alert,
        [data-bs-theme="dark"] .enrollment-pending-review-modal__alert {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.36) 0%, rgba(69, 10, 10, 0.2) 100%);
            border-color: rgba(248, 113, 113, 0.24);
        }
    </style>
@endonce

<div class="modal fade" id="enrollmentPendingReviewModal" tabindex="-1"
     aria-labelledby="enrollmentPendingReviewModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 680px;">
        <div class="modal-content enrollment-pending-review-modal__content">
            <div class="modal-body text-center enrollment-pending-review-modal__body">
                <div class="enrollment-pending-review-modal__hero mx-auto" aria-hidden="true">
                    <i class="bi bi-send-check"></i>
                </div>
                <h3 class="fw-bold mb-3" id="enrollmentPendingReviewModalLabel">
                    تم إرسال طلب الانضمام
                </h3>
                <p class="fs-5 mb-2 px-md-4" id="enrollmentPendingReviewModalMessage"></p>
                <p class="text-muted mb-4 small px-md-4">
                    طلبك الآن بانتظار مراجعة الإدارة والقبول. بعد الموافقة سيتم تفعيل وصولك تلقائياً.
                </p>

                <div class="enrollment-pending-review-modal__alert text-start mb-4" id="enrollmentPendingReviewWhatsappAlert">
                    <div class="d-flex align-items-start gap-3 flex-wrap">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fab fa-whatsapp fs-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-2 text-danger">للمتابعة تواصل مع المشرفة عبر واتساب</strong>
                            <p class="mb-0 small text-muted">
                                لا يلزم رفع إيصال أو إدخال بيانات إضافية. يكفي انتظار القبول والتواصل مع المشرفة عند الحاجة لتسريع المتابعة.
                            </p>
                        </div>
                        @if($hasSupervisorWa)
                            @include('student.partials.supervisor-whatsapp-cta', [
                                'supervisorWhatsappDigits' => $supervisorWhatsappDigits,
                                'wrapperClass' => 'align-self-center mb-0',
                                'btnSize' => 'lg',
                            ])
                        @else
                            <span class="small text-muted">سيظهر زر الواتساب هنا بعد ضبط رقم المشرفة.</span>
                        @endif
                    </div>
                </div>

                <button type="button" class="btn btn-primary btn-lg px-5 rounded-pill" data-bs-dismiss="modal">
                    <i class="bi bi-check2-circle me-2" aria-hidden="true"></i>
                    حسناً
                </button>
            </div>
        </div>
    </div>
</div>
