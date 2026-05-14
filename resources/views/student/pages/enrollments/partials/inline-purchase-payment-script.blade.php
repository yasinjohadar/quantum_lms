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

    window.EnrollmentInlinePurchase.bindForm = function (root) {
        if (!root) {
            return;
        }
        var form = root.querySelector('form.js-inline-purchase-payment-form');
        if (!form || form.getAttribute('data-inline-bound') === '1') {
            return;
        }
        form.setAttribute('data-inline-bound', '1');

        function hidePaymentDetails() {
            form.querySelectorAll('.js-iban-details, .js-custom-details').forEach(function (el) {
                el.style.display = 'none';
            });
        }

        form.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                hidePaymentDetails();
                if (this.value === 'iban') {
                    var ibanEl = form.querySelector('[id$="_ibanDetails"]');
                    if (ibanEl) {
                        ibanEl.style.display = 'block';
                    }
                } else if (this.value === 'custom') {
                    var methodId = this.getAttribute('data-method-id');
                    if (methodId) {
                        var customEl = document.getElementById(form.id + '_custom_' + methodId + '_details');
                        if (customEl) {
                            customEl.style.display = 'block';
                        }
                    }
                }
            });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var paymentMethod = form.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                alert('يرجى اختيار طريقة الدفع');
                return;
            }

            var formData = new FormData(form);
            if (paymentMethod.value === 'custom') {
                formData.append('custom_payment_method_id', paymentMethod.getAttribute('data-method-id'));
            }

            var submitBtn = form.querySelector('.js-inline-payment-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري المعالجة...';
            }

            var processUrl = form.getAttribute('data-process-url');
            var successUrl = form.getAttribute('data-success-url') || '/student/classes';
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
                        alert(data.message || 'تم بنجاح');
                        window.location.href = data.redirect || successUrl;
                        return;
                    }
                    alert(data.message || 'حدث خطأ أثناء معالجة الدفع');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع';
                    }
                })
                .catch(function () {
                    alert('حدث خطأ أثناء معالجة الدفع');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع';
                    }
                });
        });
    };

    window.EnrollmentInlinePurchase.openPaymentModal = function (modalEl, bodyEl, purchaseId, queryParams) {
        var url = window.EnrollmentInlinePurchase.fragmentUrl(purchaseId, queryParams);
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
