@extends('frontend.layouts.master')

@section('content')

<!-- Checkout Section Start -->
<section class="checkout-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <!-- Order Summary -->
                <div class="checkout-card mb-4">
                    <h3 class="checkout-card-title">
                        <i class="fa-solid fa-shopping-bag me-2"></i>
                        ملخص الطلب
                    </h3>
                    
                    <div class="checkout-items">
                        @foreach($items as $item)
                            <div class="checkout-item">
                                <div class="checkout-item-info">
                                    <h4 class="checkout-item-name">
                                        @if($item['type'] === 'class')
                                            <i class="fa-solid fa-graduation-cap me-2"></i>
                                        @else
                                            <i class="fa-solid fa-book me-2"></i>
                                        @endif
                                        {{ $item['name'] }}
                                    </h4>
                                    <span class="checkout-item-type">
                                        {{ $item['type'] === 'class' ? 'صف كامل' : 'مادة' }}
                                    </span>
                                </div>
                                <div class="checkout-item-price">
                                    @if($item['is_free'])
                                        <span class="text-success">مجاني</span>
                                    @else
                                        {{ number_format($item['price'], 2) }}
                                        {{ $item['currency']->symbol ?? $item['currency']->code ?? '' }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Total -->
                <div class="checkout-card mb-4">
                    <h3 class="checkout-card-title">
                        <i class="fa-solid fa-calculator me-2"></i>
                        الإجمالي
                    </h3>
                    
                    <div class="checkout-total">
                        <div class="checkout-total-row">
                            <span>المجموع الفرعي:</span>
                            <span>{{ number_format($totalPrice, 2) }} {{ $defaultCurrency->symbol ?? $defaultCurrency->code ?? '' }}</span>
                        </div>
                        <div class="checkout-total-row checkout-total-final">
                            <span>الإجمالي:</span>
                            <span class="checkout-total-amount">
                                {{ number_format($totalPrice, 2) }} {{ $defaultCurrency->symbol ?? $defaultCurrency->code ?? '' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Options or Free Enrollment -->
                @if($allFree)
                    <!-- Free Enrollment -->
                    <div class="checkout-card">
                        <div class="checkout-free-message">
                            <i class="fa-solid fa-gift text-success mb-3" style="font-size: 3rem;"></i>
                            <h4 class="mb-3">هذا المحتوى مجاني!</h4>
                            <p class="text-muted mb-4">يمكنك الاشتراك الآن بدون دفع</p>
                            
                            <form method="POST" action="{{ route('frontend.checkout.process') }}">
                                @csrf
                                <input type="hidden" name="purchase_type" value="{{ request('purchase_type') }}">
                                <input type="hidden" name="class_id" value="{{ $class->id }}">
                                @if(request('purchase_type') === 'subjects')
                                    @foreach(request('subject_ids', []) as $subjectId)
                                        <input type="hidden" name="subject_ids[]" value="{{ $subjectId }}">
                                    @endforeach
                                @endif
                                
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fa-solid fa-check me-2"></i>
                                    اشترك الآن
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Payment Methods -->
                    <div class="checkout-card">
                        <h3 class="checkout-card-title">
                            <i class="fa-solid fa-credit-card me-2"></i>
                            طرق الدفع
                        </h3>
                        
                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            سيتم توجيهك إلى صفحة الدفع بعد تأكيد الطلب
                        </div>
                        
                        <form method="POST" action="{{ route('frontend.checkout.process') }}">
                            @csrf
                            <input type="hidden" name="purchase_type" value="{{ request('purchase_type') }}">
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            @if(request('purchase_type') === 'subjects')
                                @foreach(request('subject_ids', []) as $subjectId)
                                    <input type="hidden" name="subject_ids[]" value="{{ $subjectId }}">
                                @endforeach
                            @endif
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa-solid fa-arrow-left me-2"></i>
                                متابعة إلى الدفع
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
<!-- Checkout Section End -->

@endsection
