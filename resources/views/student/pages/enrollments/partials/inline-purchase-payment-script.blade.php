<script>
(function () {
    window.EnrollmentInlinePurchase = window.EnrollmentInlinePurchase || {};

    window.EnrollmentInlinePurchase.fragmentUrl = function (purchaseId, queryParams) {
        var base = '/student/purchases/payment/' + encodeURIComponent(purchaseId) + '/fragment';
        if (!queryParams || typeof queryParams !== 'object') {
            return base;
        }
        var q = new URLSearchParams(queryParams).toString();
        return q ? (base + '?' + q) : base;
    };

    window.EnrollmentInlinePurchase.prepareClassFragmentUrl = function (classId, queryParams) {
        var base = '/student/purchases/prepare-class/' + encodeURIComponent(classId) + '/fragment';
        if (!queryParams || typeof queryParams !== 'object') {
            return base;
        }
        var params = Object.assign({}, queryParams);
        delete params.purchase_type;
        delete params.class_id;
        var q = new URLSearchParams(params).toString();
        return q ? (base + '?' + q) : base;
    };

    window.EnrollmentInlinePurchase.prepareSubjectFragmentUrl = function (subjectId, queryParams) {
        var base = '/student/purchases/prepare-subject/' + encodeURIComponent(subjectId) + '/fragment';
        if (!queryParams || typeof queryParams !== 'object') {
            return base;
        }
        var params = Object.assign({}, queryParams);
        delete params.purchase_type;
        delete params.subject_id;
        var q = new URLSearchParams(params).toString();
        return q ? (base + '?' + q) : base;
    };

    window.EnrollmentInlinePurchase.resolveFragmentUrl = function (purchaseId, queryParams) {
        queryParams = queryParams || {};
        if (queryParams.purchase_type === 'class' && queryParams.class_id) {
            return window.EnrollmentInlinePurchase.prepareClassFragmentUrl(queryParams.class_id, queryParams);
        }
        if (queryParams.purchase_type === 'subject' && queryParams.subject_id) {
            return window.EnrollmentInlinePurchase.prepareSubjectFragmentUrl(queryParams.subject_id, queryParams);
        }
        if (purchaseId) {
            return window.EnrollmentInlinePurchase.fragmentUrl(purchaseId, queryParams);
        }
        return null;
    };

    window.EnrollmentInlinePurchase.showPaymentPendingModal = function (message, redirectUrl) {
        var modalEl = document.getElementById('paymentPendingReviewModal');
        var messageEl = document.getElementById('paymentPendingReviewModalMessage');
        var okBtn = document.getElementById('paymentPendingReviewModalOk');
        if (!modalEl || !messageEl) {
            alert(message || 'تم إرسال طلب الدفع بنجاح');
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
            return;
        }

        messageEl.textContent = message || '';
        ['enrollmentPaymentModal', 'classEnrollmentPaymentModal'].forEach(function (modalId) {
            var paymentModal = document.getElementById(modalId);
            if (paymentModal) {
                var inst = bootstrap.Modal.getInstance(paymentModal);
                if (inst) {
                    inst.hide();
                }
            }
        });

        var pendingModal = bootstrap.Modal.getInstance(modalEl);
        if (!pendingModal) {
            pendingModal = new bootstrap.Modal(modalEl);
        }

        var onHidden = function () {
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        };
        modalEl.addEventListener('hidden.bs.modal', onHidden);

        if (okBtn) {
            okBtn.onclick = function () {
                pendingModal.hide();
            };
        }

        pendingModal.show();
    };

    window.EnrollmentInlinePurchase.bindForm = function (root) {
        if (!root) {
            return;
        }
        var form = root.querySelector('form.js-inline-purchase-payment-form');
        if (!form || form.getAttribute('data-inline-bound') === '1') {
            return;
        }
        form.setAttribute('data-inline-bound', '1');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(form);
            if (!formData.get('payment_method')) {
                formData.set('payment_method', 'iban');
            }

            var submitBtn = form.querySelector('.js-inline-payment-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري المعالجة...';
            }

            var processUrl = form.getAttribute('data-process-url');
            var successUrl = form.getAttribute('data-success-url') || '/student/enrollments';
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrf ? csrf.getAttribute('content') : '';

            fetch(processUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (data) {
                    if (data.success) {
                        if (data.pending_review) {
                            window.EnrollmentInlinePurchase.showPaymentPendingModal(
                                data.message,
                                data.redirect || successUrl
                            );
                            return;
                        }
                        alert(data.message || 'تم بنجاح');
                        window.location.href = data.redirect || successUrl;
                        return;
                    }
                    alert(data.message || 'حدث خطأ أثناء معالجة الدفع');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع وإرسال الطلب';
                    }
                })
                .catch(function () {
                    alert('حدث خطأ أثناء معالجة الدفع');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع وإرسال الطلب';
                    }
                });
        });
    };

    window.EnrollmentInlinePurchase.openPaymentModal = function (modalEl, bodyEl, purchaseId, queryParams) {
        var url = window.EnrollmentInlinePurchase.resolveFragmentUrl(purchaseId, queryParams || {});
        if (!url) {
            bodyEl.innerHTML = '<div class="alert alert-danger m-3">تعذر تحميل نموذج الدفع.</div>';
            return;
        }

        bodyEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">جاري التحميل...</p></div>';

        var modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }
        modal.show();

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('load failed');
                }
                return r.text();
            })
            .then(function (html) {
                bodyEl.innerHTML = html;
                window.EnrollmentInlinePurchase.bindForm(bodyEl);
            })
            .catch(function () {
                bodyEl.innerHTML = '<div class="alert alert-danger m-3">تعذر تحميل نموذج الدفع. حاول مرة أخرى.</div>';
            });
    };
})();
</script>
