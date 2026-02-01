@extends('frontend.layouts.master')

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
            <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="section-title">الصفوف الدراسية</h2>
                <p class="section-description">اختر الصف المناسب لك وابدأ رحلتك التعليمية</p>
                        </div>
                    </div>
        
                <div class="row">
            @forelse($classes as $class)
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <a href="{{ route('frontend.class.show', $class['slug']) }}" class="class-card-link">
                        <div class="class-card">
                            <div class="class-card-image">
                                @if($class['image'])
                                    <img src="{{ asset('storage/' . $class['image']) }}" alt="{{ $class['name'] }}" class="img-fluid">
                                @else
                                    <div class="class-card-placeholder">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                    </div>
                                @endif
                                @if($class['stage'])
                                    <span class="class-badge">{{ $class['stage']->name }}</span>
                                @endif
                            </div>
                            <div class="class-card-body">
                                <h3 class="class-card-title">{{ $class['name'] }}</h3>
                                
                                <!-- Price Section -->
                                <div class="class-card-price">
                                    @if($class['is_free'] || $class['price'] == 0)
                                        <div class="price-free-wrapper">
                                            <span class="price-free">مجاني</span>
                                        </div>
                                    @else
                                        <div class="price-content">
                                            <div class="price-current">
                                                <span class="price-amount">{{ number_format($class['price'], 2) }}</span>
                                                <span class="price-currency">{{ $class['currency']->symbol ?? $class['currency']->code ?? '' }}</span>
                                            </div>
                                            @if($class['old_price'] > $class['price'])
                                                <span class="price-old">
                                                    {{ number_format($class['old_price'], 2) }} {{ $class['currency']->symbol ?? $class['currency']->code ?? '' }}
                                                </span>
                                            @endif
                                            </div>
                                    @endif
                                </div>

                                <div class="class-card-btn enroll-btn">
                                    عرض المواد
                                    <i class="fa-solid fa-angles-left ms-2"></i>
                                        </div>
                                            </div>
                                            </div>
                    </a>
                                        </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <p class="text-muted">لا توجد صفوف متاحة حالياً</p>
                                    </div>
                                </div>
            @endforelse
                </div>
            </div>
        </section>
<!-- Classes Section End -->

@endsection