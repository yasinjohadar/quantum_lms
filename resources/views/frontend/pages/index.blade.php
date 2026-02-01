@extends('frontend.layouts.master')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" crossorigin="anonymous">
@endpush

@section('content')

<!-- Hero Section Start -->
<section class="hero-section">
                    <div class="container">
        <div class="row align-items-center">

                <!-- الصور/الكروت - يسار في RTL -->
            <div class="col-md-6 order-md-1">
                <div class="hero-images position-relative">
                    <!-- أيقونة قبعة التخرج -->
                    <div class="graduation-icon position-absolute">
                        <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                    <div class="hero-main-image">
                        <img src="{{ asset('frontend/images/hero-img.png') }}" alt="Hero" />
                                </div>
                    
                    <!-- أيقونة الخبرة -->
                    <div class="experience-badge position-absolute">
                        <i class="fa-solid fa-lightbulb"></i>
                        <span>25+ سنة من الخبرة</span>
            </div>
            </div>
            </div>
            <!-- النص - يمين في RTL -->
            <div class="col-md-6 order-md-2">
                <div class="hero-content text-end">
                    <span class="hero-badge"># أفضل منصة تعليمية</span>
                    <h1 class="hero-title">
                        ابدأ التعلم <span class="text-primary">اليوم</span><br>
                        <span class="text-primary">اكتشف</span> مهارتك القادمة<br>
                        العظيمة
                    </h1>
                    <p class="hero-description">
                        عزز رحلتك التعليمية مع منصة الكورسات المتطورة لدينا.
                    </p>
                    <div class="hero-actions d-flex align-items-center justify-content-end gap-3 mb-4">
                        <a href="#" class="btn btn-primary btn-lg btn-gold">
                            ابدأ الآن
                            <i class="fa-solid fa-angles-left ms-2"></i>
                                    </a>
                                </div>
                    <div class="hero-stats d-flex align-items-center justify-content-end gap-2">
                        <div class="stats-dots d-flex gap-1">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            </div>
                        <span class="stats-text">2000+ طالب ناجح</span>
                        </div>
                    </div>
                </div>
            
    

                                </div>
            </div>
        </section>
<!-- Hero Section End -->

<!-- Classes Section Start -->
<section class="classes-section py-5">
    <div class="container classes-swiper-section">
        <div class="row mb-3">
            <div class="col-12 text-center">
                <h2 class="section-title">الصفوف الدراسية</h2>
                <p class="section-description">اختر الصف المناسب لك وابدأ رحلتك التعليمية</p>
            </div>
        </div>

        <div class="row align-items-center justify-content-between mb-3">
            <div class="col-auto">
                <span class="classes-swiper-nav-label">تصفح الصفوف</span>
            </div>
            <div class="col-auto d-flex align-items-center gap-2">
                <div class="swiper-button-prev" aria-label="السابق"></div>
                <div class="swiper-button-next" aria-label="التالي"></div>
            </div>
        </div>

        @php
            $displayClasses = $classes->values();
            if ($displayClasses->isEmpty()) {
                $dummyNames = ['صف تجريبي 1', 'صف تجريبي 2', 'صف تجريبي 3'];
                $dummyStageNames = ['المرحلة الابتدائية', 'المرحلة الاعدادية', 'المرحلة الثانوية'];
                for ($i = 0; $i < 3; $i++) {
                    $displayClasses->push([
                        'slug' => '',
                        'name' => $dummyNames[$i] ?? ('صف تجريبي ' . ($i + 1)),
                        'stage' => null,
                        'stage_name' => $dummyStageNames[$i] ?? 'مرحلة تجريبية',
                        'image' => null,
                        'price' => 0,
                        'old_price' => 0,
                        'is_free' => true,
                        'currency' => null,
                        'is_dummy' => true,
                    ]);
                }
            }
        @endphp

        <div class="swiper classes-swiper">
            <div class="swiper-wrapper">
                @foreach($displayClasses as $class)
                    <div class="swiper-slide">
                        <div class="class-card-wrapper">
                            <div class="class-card">
                                <a href="{{ !empty($class['slug']) ? route('frontend.class.show', $class['slug']) : '#' }}" class="class-card-link">
                                    <div class="class-card-image">
                                        @if(!empty($class['image']))
                                            <img src="{{ asset('storage/' . $class['image']) }}" alt="{{ $class['name'] }}" class="img-fluid">
                                        @else
                                            <div class="class-card-placeholder">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                            </div>
                                        @endif
                                        <span class="class-badge">{{ isset($class['stage']) && $class['stage'] ? $class['stage']->name : ($class['stage_name'] ?? 'مرحلة تجريبية') }}</span>
                                    </div>
                                </a>
                                <div class="class-card-body">
                                    <h3 class="class-card-title">{{ $class['name'] }}</h3>
                                    @if(isset($class['features']) && $class['features']->isNotEmpty())
                                        <h4 class="class-card-features-title">خصائص الاشتراك</h4>
                                        <ul class="class-card-features list-unstyled small text-muted mb-2">
                                            @foreach($class['features'] as $label)
                                                @if(!empty(trim($label ?? '')))
                                                    <li class="mb-1"><i class="fa-solid fa-check text-success class-card-feature-icon"></i><span class="class-card-feature-label">{{ $label }}</span></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="class-card-price">
                                        @if(!empty($class['is_free']) || (isset($class['price']) && $class['price'] == 0))
                                            <div class="price-free-wrapper">
                                                <span class="price-free">مجاني</span>
                                            </div>
                                        @else
                                            <div class="price-content">
                                                <div class="price-current">
                                                    <span class="price-amount">{{ number_format($class['price'] ?? 0, 2) }}</span>
                                                    <span class="price-currency">{{ isset($class['currency']) && $class['currency'] ? ($class['currency']->symbol ?? $class['currency']->code ?? '') : '' }}</span>
                                                </div>
                                                @if(($class['old_price'] ?? 0) > ($class['price'] ?? 0))
                                                    <span class="price-old">
                                                        {{ number_format($class['old_price'], 2) }} {{ isset($class['currency']) && $class['currency'] ? ($class['currency']->symbol ?? $class['currency']->code ?? '') : '' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="class-card-buttons d-flex gap-2 flex-nowrap">
                                        <a href="{{ !empty($class['slug']) ? route('frontend.class.show', $class['slug']) : '#' }}" class="class-card-btn enroll-btn">
                                            عرض المواد
                                            <i class="fa-solid fa-angles-left ms-2"></i>
                                        </a>
                                        @if(!empty($class['slug']) && !empty($class['id']))
                                            <a href="{{ route('frontend.checkout', ['purchase_type' => 'class', 'class_id' => $class['id']]) }}" class="class-card-btn subscribe-btn">
                                                اشتراك مباشر
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
<!-- Classes Section End -->

<!-- Reviews Section Start -->
<section class="reviews-section py-5">
    <div class="container reviews-swiper-section">
        <div class="row mb-3">
            <div class="col-12 text-center">
                <h2 class="section-title">آراء الطلاب</h2>
                <p class="section-description mb-0">ماذا يقول طلابنا عن المنصة</p>
            </div>
            @auth
            <div class="col-12 text-center mt-3">
                <button type="button" class="btn btn-primary btn-gold" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="fa-solid fa-star me-2"></i>تقييم المنصة
                </button>
            </div>
            @endauth
        </div>

        @auth
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif
        @endauth

        @if($reviews->isNotEmpty())
        <div class="row align-items-center justify-content-end mb-3">
            <div class="col-auto d-flex align-items-center gap-2">
                <div class="swiper-button-prev" aria-label="السابق"></div>
                <div class="swiper-button-next" aria-label="التالي"></div>
            </div>
        </div>

        <div class="swiper reviews-swiper">
            <div class="swiper-wrapper">
                @foreach($reviews as $review)
                    <div class="swiper-slide">
                        <div class="review-card">
                            <div class="review-card-body text-center">
                                <div class="review-photo-wrapper mb-3">
                                    @if($review->display_photo_url)
                                        <img src="{{ $review->display_photo_url }}" alt="{{ $review->user->name ?? '' }}" class="review-photo rounded-circle">
                                    @else
                                        <div class="review-photo-placeholder rounded-circle">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <h4 class="review-author-name">{{ $review->user->name ?? 'طالب' }}</h4>
                                @if($review->schoolClass)
                                    <p class="review-class-name text-muted small mb-2">{{ $review->schoolClass->name }}</p>
                                @endif
                                <div class="review-stars mb-2" aria-label="{{ $review->stars }} نجوم">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $review->stars ? 'text-warning' : 'text-muted opacity-50' }}"></i>
                                    @endfor
                                </div>
                                <p class="review-comment text-muted">{{ $review->comment }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
        @else
        <p class="text-center text-muted">لا توجد آراء معتمدة لعرضها حالياً.</p>
        @auth
        <p class="text-center mt-2">
            <button type="button" class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                <i class="fa-solid fa-star me-1"></i>تقييم المنصة
            </button>
        </p>
        @endauth
        @endif
    </div>

    @auth
    <!-- مودال تقييم المنصة -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="reviewModalLabel">أضف رأيك</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('frontend.platform-review.store') }}" method="post" class="review-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">التقييم (نجوم)</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @for($s = 1; $s <= 5; $s++)
                                    <label class="star-option">
                                        <input type="radio" name="stars" value="{{ $s }}" {{ old('stars') == $s ? 'checked' : '' }} required>
                                        <span class="star-label">{{ $s }} <i class="fa-solid fa-star text-warning"></i></span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="comment" class="form-label">تعليقك</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4" maxlength="2000" required placeholder="اكتب رأيك عن المنصة...">{{ old('comment') }}</textarea>
                            <small class="text-muted">سيتم مراجعة التعليق من الإدارة قبل النشر.</small>
                        </div>
                        <div class="d-flex gap-2 justify-content-start">
                            <button type="submit" class="btn btn-primary btn-gold">إرسال الرأي</button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth
</section>
<!-- Reviews Section End -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.classes-swiper', {
        dir: 'rtl',
        loop: true,
        spaceBetween: 24,
        slidesPerView: 1,
        breakpoints: {
            576: { slidesPerView: 2 },
            992: { slidesPerView: 3 }
        },
        navigation: {
            nextEl: '.classes-swiper-section .swiper-button-next',
            prevEl: '.classes-swiper-section .swiper-button-prev'
        },
        pagination: {
            el: '.classes-swiper .swiper-pagination',
            clickable: true
        }
    });
    var reviewsSwiper = document.querySelector('.reviews-swiper');
    if (reviewsSwiper) {
        new Swiper('.reviews-swiper', {
            dir: 'rtl',
            loop: true,
            spaceBetween: 24,
            slidesPerView: 1,
            breakpoints: {
                576: { slidesPerView: 2 },
                992: { slidesPerView: 3 }
            },
            navigation: {
                nextEl: '.reviews-swiper-section .swiper-button-next',
                prevEl: '.reviews-swiper-section .swiper-button-prev'
            },
            pagination: {
                el: '.reviews-swiper .swiper-pagination',
                clickable: true
            }
        });
    }
});
</script>
@endpush