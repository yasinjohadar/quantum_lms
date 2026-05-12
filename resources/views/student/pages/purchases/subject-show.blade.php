@extends('student.layouts.master')

@section('page-title')
    شراء {{ $subject->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">شراء {{ $subject->name }}</h4>
                <p class="mb-0 text-muted">
                    @if($subject->schoolClass)
                        {{ $subject->schoolClass->name }} - 
                    @endif
                    {{ $subject->name }}
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.enrollments.index') }}">طلب الانضمام</a></li>
                    <li class="breadcrumb-item active">شراء {{ $subject->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <!-- معلومات المادة -->
            <div class="col-xl-8">
                <div class="card custom-card mb-4">
                    <div class="card-body">
                        @if($subject->image)
                            <img src="{{ media_public_url($subject->image) }}" class="card-img-top mb-3" alt="{{ $subject->name }}" style="max-height: 300px; object-fit: cover;">
                        @endif
                        <h5 class="mb-3">{{ $subject->name }}</h5>
                        @if($subject->description)
                            <p class="text-muted">{{ $subject->description }}</p>
                        @endif
                        
                        <div class="mt-4">
                            <h6 class="mb-3">محتوى المادة:</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="bi bi-book text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2 mb-0">{{ $subject->total_lessons ?? 0 }}</h5>
                                            <small class="text-muted">درس</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="bi bi-clipboard-check text-info" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2 mb-0">{{ $subject->total_quizzes ?? 0 }}</h5>
                                            <small class="text-muted">اختبار</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="bi bi-question-circle text-success" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2 mb-0">{{ $subject->total_questions ?? 0 }}</h5>
                                            <small class="text-muted">سؤال</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات الشراء -->
            <div class="col-xl-4">
                <div class="card custom-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-cart me-2"></i>
                            معلومات الشراء
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $activePrices = $subject->getActivePrices();
                            $defaultCurrency = $subject->defaultCurrency ?? \App\Models\Currency::getDefault();
                            $selectedCurrencyId = request('currency_id', $defaultCurrency->id);
                            $selectedCurrency = \App\Models\Currency::find($selectedCurrencyId) ?? $defaultCurrency;
                            $selectedPrice = $subject->getPrice($selectedCurrencyId);
                        @endphp

                        <div class="mb-3">
                            <label for="currency_selector" class="form-label">اختر العملة</label>
                            <select class="form-select" id="currency_selector" onchange="window.location.href = '{{ route('student.purchases.subject.show', $subject->id) }}?currency_id=' + this.value">
                                @foreach(\App\Models\Currency::active()->ordered()->get() as $currency)
                                    <option value="{{ $currency->id }}" {{ $selectedCurrencyId == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4 text-center">
                            <h3 class="text-primary mb-2">
                                @if($selectedPrice == 0)
                                    <span class="text-success">مجاني</span>
                                @else
                                    {{ number_format($selectedPrice, 2) }} <small>{{ $selectedCurrency->symbol }}</small>
                                @endif
                            </h3>
                            <p class="text-muted mb-0">سعر المادة</p>
                        </div>

                        @if($activePrices->count() > 1)
                            <div class="mb-4">
                                <h6 class="mb-2">الأسعار بجميع العملات:</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach($activePrices as $price)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span>{{ $price->currency->name }} ({{ $price->currency->code }})</span>
                                            <strong>{{ number_format($price->price, 2) }} {{ $price->currency->symbol }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h6 class="mb-3">ما ستحصل عليه:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    الوصول الكامل للمادة
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    جميع الدروس والاختبارات
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    وصول دائم للمحتوى
                                </li>
                            </ul>
                        </div>

                        @if($selectedPrice == 0)
                            <button class="btn btn-success w-100" onclick="initiatePurchase('subject', {{ $subject->id }}, {{ $selectedCurrencyId }})">
                                <i class="bi bi-check-circle me-2"></i>
                                التسجيل المجاني
                            </button>
                        @else
                            <button class="btn btn-primary w-100" onclick="initiatePurchase('subject', {{ $subject->id }}, {{ $selectedCurrencyId }})">
                                <i class="bi bi-cart me-2"></i>
                                شراء الآن
                            </button>
                        @endif
                    </div>
                </div>

                <!-- معلومات المحفظة -->
                @if($wallet)
                    <div class="card custom-card">
                        <div class="card-body">
                            <h6 class="mb-3">رصيد المحفظة</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">الرصيد المتاح:</span>
                                <strong class="text-primary">{{ number_format($wallet->balance, 2) }} ر.س</strong>
                            </div>
                            @if($wallet->balance >= $subject->price)
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    رصيدك كافٍ للشراء
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    رصيدك غير كافٍ. <a href="{{ route('student.wallet.index') }}">شحن المحفظة</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
function initiatePurchase(type, id, currencyId) {
    fetch('{{ route("student.purchases.initiate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            purchasable_type: type,
            purchasable_id: id,
            currency_id: currencyId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء معالجة الطلب');
    });
}
</script>
@endpush
@endsection
