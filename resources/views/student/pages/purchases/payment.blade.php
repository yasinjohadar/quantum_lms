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
                <p class="mb-0 text-muted">أكمل التحويل البنكي لإرسال طلبك</p>
            </div>
        </div>
        <!-- End Page Header -->

        @include('student.pages.purchases.partials.payment-form-body', [
            'purchase' => $purchase,
            'formId' => 'paymentForm',
            'processPaymentUrl' => route('student.purchases.process-payment', $purchase),
            'successUrl' => route('student.classes'),
        ])
    </div>
</div>
<!-- End::app-content -->

@include('student.pages.purchases.partials.payment-pending-modal')
@stop

@section('script')
@include('student.pages.enrollments.partials.inline-purchase-payment-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('.main-content');
    if (root && window.EnrollmentInlinePurchase) {
        window.EnrollmentInlinePurchase.bindForm(root);
    }
});
</script>
@stop
