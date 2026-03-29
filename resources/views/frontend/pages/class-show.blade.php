@extends('frontend.layouts.master')

@section('content')

<!-- Class Show Section Start -->
<section class="class-show-section">
    <!-- Class Header - Full Width -->
    <div class="class-header mb-5">
        <div class="container">
            <div class="class-header-content">
                <h1 class="class-header-title">{{ $class->name }}</h1>
                @if($class->stage)
                    <p class="class-header-stage">
                        <i class="fa-solid fa-layer-group"></i>
                        {{ $class->stage->name }}
                    </p>
                @endif
                @if($class->description)
                    <p class="class-header-description">{{ $class->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="container">

        @auth
        @if($class->whatsapp_group_url && $isEnrolled)
        <div class="card mb-4 border-success">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="fa-brands fa-whatsapp text-success" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">انضم لمجموعة واتساب الخاصة بهذا الصف</h6>
                        <p class="text-muted mb-0 small">تواصل مع زملائك والمعلمين وكن على اطلاع بآخر المستجدات.</p>
                    </div>
                </div>
                <a href="{{ $class->whatsapp_group_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                    <i class="fa-brands fa-whatsapp me-2"></i>
                    انضم للمجموعة
                </a>
            </div>
        </div>
        @endif
        @endauth

        <!-- Subjects Section -->
        <div class="subjects-section mb-5">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="section-title">المواد الدراسية</h2>
                    <p class="section-description">اختر المادة المناسبة لك وابدأ التعلم</p>
                </div>
            </div>
            
            <div class="row">
                @forelse($subjects as $subject)
                    <div class="col-lg-3 col-md-6 col-12 mb-4" id="subject-{{ $subject['id'] }}">
                        <div class="class-card">
                            <div class="class-card-image">
                                @if($subject['image'])
                                    <img src="{{ asset('storage/' . $subject['image']) }}" alt="{{ $subject['name'] }}" class="img-fluid">
                                @else
                                    <div class="class-card-placeholder">
                                        <i class="fa-solid fa-book"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="class-card-body">
                                <h3 class="class-card-title">{{ $subject['name'] }}</h3>
                                
                                <!-- Price Section -->
                                <div class="class-card-price">
                                    @if($subject['is_free'] || $subject['price'] == 0)
                                        <div class="price-free-wrapper">
                                            <span class="price-free">مجاني</span>
                                        </div>
                                    @else
                                        <div class="price-content">
                                            <div class="price-current">
                                                <span class="price-amount">{{ number_format($subject['price'], 2) }}</span>
                                                <span class="price-currency">{{ $subject['currency']->symbol ?? $subject['currency']->code ?? '' }}</span>
                                            </div>
                                            @if($subject['old_price'] > $subject['price'])
                                                <span class="price-old">
                                                    {{ number_format($subject['old_price'], 2) }} {{ $subject['currency']->symbol ?? $subject['currency']->code ?? '' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                @guest
                                <a href="#guest-purchase-cta" class="class-card-btn enroll-btn">
                                    سجل الآن
                                    <i class="fa-solid fa-angles-left ms-2"></i>
                                </a>
                                @else
                                <a href="#purchase-section" class="class-card-btn enroll-btn">
                                    سجل الآن
                                    <i class="fa-solid fa-angles-left ms-2"></i>
                                </a>
                                @endguest
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <p class="text-muted">لا توجد مواد متاحة حالياً</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Purchase Options Section -->
        @auth
        @if(!$isEnrolled && $purchaseStatus !== 'pending')
        <div id="purchase-section" class="purchase-options-section mb-5">
            <div class="row">
                <div class="col-12">
                    <div class="purchase-options-card">
                        <h3 class="purchase-options-title">
                            <i class="fa-solid fa-shopping-cart me-2"></i>
                            خيارات الشراء
                        </h3>
                        
                        <form id="purchaseForm" method="GET" action="{{ route('frontend.checkout') }}">
                            
                            <!-- Option 1: Buy Full Class -->
                            <div class="purchase-option mb-3">
                                <label class="purchase-option-label">
                                    <input type="radio" name="purchase_type" value="class" class="purchase-radio" checked>
                                    <div class="purchase-option-content">
                                        <div class="purchase-option-header">
                                            <span class="purchase-option-title">
                                                <i class="fa-solid fa-graduation-cap me-2"></i>
                                                شراء الصف بالكامل
                                            </span>
                                            <span class="purchase-option-price">
                                                @if($class->is_free || $class->getPrice($class->defaultCurrency->id ?? null) == 0)
                                                    <span class="text-success">مجاني</span>
                                                @else
                                                    {{ number_format($class->getPrice($class->defaultCurrency->id ?? null), 2) }}
                                                    {{ $class->defaultCurrency->symbol ?? $class->defaultCurrency->code ?? '' }}
                                                @endif
                                            </span>
                                        </div>
                                        <p class="purchase-option-description">
                                            يشمل جميع المواد الدراسية في هذا الصف ({{ count($subjects) }} مادة)
                                        </p>
                                    </div>
                                </label>
                            </div>
                            
                            @if($class->allow_subjects_purchase)
                            <!-- Option 2: Buy Individual Subjects -->
                            <div class="purchase-option mb-3">
                                <label class="purchase-option-label">
                                    <input type="radio" name="purchase_type" value="subjects" class="purchase-radio">
                                    <div class="purchase-option-content">
                                        <div class="purchase-option-header">
                                            <span class="purchase-option-title">
                                                <i class="fa-solid fa-book me-2"></i>
                                                شراء مواد متفرقة
                                            </span>
                                        </div>
                                        <p class="purchase-option-description mb-3">
                                            اختر المواد التي تريد شراءها
                                        </p>
                                        
                                        <!-- Subjects Checkboxes -->
                                        <div class="subjects-checkboxes" style="display: none;">
                                            @foreach($subjects as $subject)
                                                <label class="subject-checkbox-label">
                                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject['id'] }}" class="subject-checkbox" data-price="{{ $subject['price'] }}" data-currency="{{ $subject['currency']->symbol ?? $subject['currency']->code ?? '' }}">
                                                    <span class="subject-checkbox-content">
                                                        <span class="subject-checkbox-name">{{ $subject['name'] }}</span>
                                                        <span class="subject-checkbox-price">
                                                            @if($subject['is_free'] || $subject['price'] == 0)
                                                                <span class="text-success">مجاني</span>
                                                            @else
                                                                {{ number_format($subject['price'], 2) }} {{ $subject['currency']->symbol ?? $subject['currency']->code ?? '' }}
                                                            @endif
                                                        </span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endif
                            
                            <!-- Total Price Display -->
                            <div class="purchase-total mb-3" id="purchaseTotal" style="display: none;">
                                <div class="purchase-total-content">
                                    <span class="purchase-total-label">المجموع:</span>
                                    <span class="purchase-total-price" id="totalPrice">0.00</span>
                                    <span class="purchase-total-currency" id="totalCurrency"></span>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="purchase-submit">
                                <button type="submit" class="btn btn-primary btn-lg w-100 purchase-submit-btn">
                                    <i class="fa-solid fa-arrow-left me-2"></i>
                                    متابعة إلى الدفع
                                </button>
                            </div>
                            
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @elseif($purchaseStatus === 'pending')
            <!-- Pending Purchase Message -->
            <div class="pending-purchase-message-section mb-5">
                <div class="pending-purchase-message-card">
                    <div class="pending-purchase-message-content">
                        <i class="fa-solid fa-clock pending-icon"></i>
                        <h3 class="pending-purchase-title">الطلب قيد المراجعة من قبل الإدارة</h3>
                        <p class="pending-purchase-description">
                            لديك طلب شراء قيد المراجعة لهذا الصف. سيتم إشعارك فور الموافقة على الطلب.
                        </p>
                        @if($pendingPurchase)
                            <div class="pending-purchase-info">
                                <div class="pending-purchase-info-item">
                                    <span class="info-label">رقم الطلب:</span>
                                    <span class="info-value">#{{ $pendingPurchase->id }}</span>
                                </div>
                                <div class="pending-purchase-info-item">
                                    <span class="info-label">تاريخ الطلب:</span>
                                    <span class="info-value">{{ $pendingPurchase->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="pending-purchase-info-item">
                                    <span class="info-label">المبلغ:</span>
                                    <span class="info-value">{{ number_format($pendingPurchase->price, 2) }} {{ $pendingPurchase->purchasable->defaultCurrency->symbol ?? $pendingPurchase->purchasable->defaultCurrency->code ?? '' }}</span>
                                </div>
                            </div>
                        @endif
                        <a href="{{ route('student.classes') }}" class="btn btn-warning pending-purchase-btn">
                            <i class="fa-solid fa-list me-2"></i>
                            عرض طلباتي
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Enrolled Message -->
            <div class="enrolled-message-section mb-5">
                <div class="enrolled-message-card">
                    <div class="enrolled-message-content">
                        <i class="fa-solid fa-check-circle enrolled-icon"></i>
                        <h3 class="enrolled-title">أنت مسجل في هذا الصف</h3>
                        <p class="enrolled-description">
                            يمكنك الآن الوصول إلى جميع المواد الدراسية في هذا الصف وبدء التعلم
                        </p>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary enrolled-btn">
                            <i class="fa-solid fa-graduation-cap me-2"></i>
                            الانتقال إلى لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        @endif
        @endauth

        @guest
        <div id="guest-purchase-cta" class="alert alert-info text-center mb-5">
            <i class="fa-solid fa-info-circle me-2"></i>
            يرجى <a href="{{ route('login') }}" class="alert-link">تسجيل الدخول</a> لشراء الصف أو المواد
        </div>
        @endguest

    </div>
</section>
<!-- Class Show Section End -->

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purchaseTypeRadios = document.querySelectorAll('.purchase-radio');
    const subjectsCheckboxes = document.querySelector('.subjects-checkboxes');
    const subjectCheckboxes = document.querySelectorAll('.subject-checkbox');
    const purchaseTotal = document.getElementById('purchaseTotal');
    const totalPriceEl = document.getElementById('totalPrice');
    const totalCurrencyEl = document.getElementById('totalCurrency');
    const purchaseForm = document.getElementById('purchaseForm');
    const enrollButtons = document.querySelectorAll('.enroll-btn');
    
    // Smooth scroll to purchase or guest CTA when clicking "سجل الآن" on a subject
    enrollButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const purchaseEl = document.getElementById('purchase-section');
            const guestEl = document.getElementById('guest-purchase-cta');
            const hash = (btn.getAttribute('href') || '').replace('#', '');
            if (hash === 'guest-purchase-cta' && guestEl) {
                e.preventDefault();
                guestEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (purchaseEl) {
                e.preventDefault();
                purchaseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (guestEl) {
                e.preventDefault();
                guestEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Get class price and currency
    const classPrice = {{ $class->getPrice($class->defaultCurrency->id ?? null) ?? 0 }};
    const classCurrency = '{{ $class->defaultCurrency->symbol ?? $class->defaultCurrency->code ?? "" }}';
    const classIsFree = {{ $class->is_free || $class->getPrice($class->defaultCurrency->id ?? null) == 0 ? 'true' : 'false' }};
    
    // Toggle subjects checkboxes visibility (only when subjects option exists)
    purchaseTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'subjects' && subjectsCheckboxes) {
                subjectsCheckboxes.style.display = 'block';
                calculateTotal();
            } else {
                if (subjectsCheckboxes) subjectsCheckboxes.style.display = 'none';
                subjectCheckboxes.forEach(cb => cb.checked = false);
                calculateTotal();
            }
        });
    });
    
    // Calculate total when checkboxes change
    subjectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });
    
    function calculateTotal() {
        const selectedRadio = document.querySelector('.purchase-radio:checked');
        const selectedType = selectedRadio ? selectedRadio.value : 'class';
        let total = 0;
        let currency = classCurrency;
        
        if (selectedType === 'class') {
            total = classPrice;
            currency = classCurrency;
        } else {
            subjectCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    total += parseFloat(checkbox.dataset.price) || 0;
                    if (!currency && checkbox.dataset.currency) {
                        currency = checkbox.dataset.currency;
                    }
                }
            });
        }
        
        if (purchaseTotal) {
            if (total > 0 || selectedType === 'class') {
                purchaseTotal.style.display = 'block';
                if (totalPriceEl) totalPriceEl.textContent = total.toFixed(2);
                if (totalCurrencyEl) totalCurrencyEl.textContent = currency;
            } else {
                purchaseTotal.style.display = 'none';
            }
        }
    }
    
    // Form validation and data collection
    if (purchaseForm) {
        purchaseForm.addEventListener('submit', function(e) {
            const selectedRadio = document.querySelector('.purchase-radio:checked');
            const selectedType = selectedRadio ? selectedRadio.value : 'class';
            
            if (selectedType === 'subjects') {
                const checkedSubjects = document.querySelectorAll('.subject-checkbox:checked');
                if (checkedSubjects.length === 0) {
                    e.preventDefault();
                    alert('يرجى اختيار مادة واحدة على الأقل');
                    return false;
                }
            }
        });
    }
    
    // Initial calculation
    calculateTotal();
});
</script>
@endpush
