@extends('student.layouts.master')

@section('page-title')
    شراء {{ $class->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">شراء {{ $class->name }}</h4>
                <p class="mb-0 text-muted">
                    @if($class->stage)
                        {{ $class->stage->name }} - 
                    @endif
                    {{ $class->name }}
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.enrollments.index') }}">طلب الانضمام</a></li>
                    <li class="breadcrumb-item active">شراء {{ $class->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <!-- معلومات الصف -->
            <div class="col-xl-8">
                <div class="card custom-card mb-4">
                    <div class="card-body">
                        @if($class->image)
                            <img src="{{ asset('storage/' . $class->image) }}" class="card-img-top mb-3" alt="{{ $class->name }}" style="max-height: 300px; object-fit: cover;">
                        @endif
                        <h5 class="mb-3">{{ $class->name }}</h5>
                        @if($class->description)
                            <p class="text-muted">{{ $class->description }}</p>
                        @endif
                        
                        <div class="mt-4">
                            <h6 class="mb-3">المواد المتضمنة في هذا الصف:</h6>
                            <ul class="list-group">
                                @foreach($class->subjects()->where('is_active', true)->get() as $subject)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-book me-2 text-primary"></i>
                                            {{ $subject->name }}
                                        </div>
                                        @if($subject->price > 0)
                                            <span class="badge bg-info">{{ number_format($subject->price, 2) }} ر.س</span>
                                        @else
                                            <span class="badge bg-success">مجاني</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
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
                            $activePrices = $class->getActivePrices();
                            $defaultCurrency = $class->defaultCurrency ?? \App\Models\Currency::getDefault();
                            $selectedCurrencyId = request('currency_id', $defaultCurrency->id);
                            $selectedCurrency = \App\Models\Currency::find($selectedCurrencyId) ?? $defaultCurrency;
                            $selectedPrice = $class->getPrice($selectedCurrencyId);
                        @endphp

                        <div class="mb-3">
                            <label for="currency_selector" class="form-label">اختر العملة</label>
                            <select class="form-select" id="currency_selector" onchange="window.location.href = '{{ route('student.purchases.class.show', $class->id) }}?currency_id=' + this.value">
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
                            <p class="text-muted mb-0">سعر الصف الكامل</p>
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
                                    الوصول لجميع المواد في الصف
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    {{ $class->subjects()->where('is_active', true)->count() }} مادة دراسية
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    وصول دائم للمحتوى
                                </li>
                            </ul>
                        </div>

                        @if($selectedPrice == 0)
                            <button class="btn btn-success w-100" onclick="initiatePurchase('class', {{ $class->id }}, {{ $selectedCurrencyId }})">
                                <i class="bi bi-check-circle me-2"></i>
                                التسجيل المجاني
                            </button>
                        @else
                            <button class="btn btn-primary w-100" onclick="initiatePurchase('class', {{ $class->id }}, {{ $selectedCurrencyId }})">
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
                            @if($wallet->balance >= $class->price)
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
