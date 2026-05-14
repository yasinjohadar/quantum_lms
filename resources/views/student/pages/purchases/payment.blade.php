@extends('student.layouts.master')

@section('page-title')
    معالجة الدفع
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">معالجة الدفع</h4>
                <p class="mb-0 text-muted">اختر طريقة الدفع المناسبة</p>
            </div>
        </div>
        <!-- End Page Header -->

        @include('student.pages.purchases.partials.payment-form-body', [
            'purchase' => $purchase,
            'wallet' => $wallet,
            'customPaymentMethods' => $customPaymentMethods,
            'formId' => 'paymentForm',
            'processPaymentUrl' => route('student.purchases.process-payment', $purchase),
            'successUrl' => route('student.classes'),
        ])
    </div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    if (!form) return;

    function hideAllPaymentDetails() {
        form.querySelectorAll('.js-iban-details, .js-custom-details').forEach(function (detail) {
            detail.style.display = 'none';
        });
    }

    form.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            hideAllPaymentDetails();
            if (this.value === 'iban') {
                const el = form.querySelector('[id$="_ibanDetails"]');
                if (el) el.style.display = 'block';
            } else if (this.value === 'custom') {
                const methodId = this.getAttribute('data-method-id');
                if (methodId) {
                    const el = document.getElementById(form.id + '_custom_' + methodId + '_details');
                    if (el) el.style.display = 'block';
                }
            }
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const paymentMethod = form.querySelector('input[name="payment_method"]:checked');

        if (!paymentMethod) {
            alert('يرجى اختيار طريقة الدفع');
            return;
        }

        if (paymentMethod.value === 'custom') {
            const methodId = paymentMethod.getAttribute('data-method-id');
            formData.append('custom_payment_method_id', methodId);
        }

        const submitBtn = form.querySelector('.js-inline-payment-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري المعالجة...';
        }

        const processUrl = form.getAttribute('data-process-url');
        const successUrl = form.getAttribute('data-success-url') || '{{ route("student.classes") }}';

        fetch(processUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = successUrl;
                }
            } else {
                alert(data.message || 'حدث خطأ أثناء معالجة الدفع');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع';
                }
            }
        })
        .catch(function (error) {
            console.error('Error:', error);
            alert('حدث خطأ أثناء معالجة الدفع');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع';
            }
        });
    });
});
</script>
@endpush
@endsection
