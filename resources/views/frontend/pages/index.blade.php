@extends('frontend.layouts.master')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" crossorigin="anonymous">
<style>
.distinguished-students-section .distinguished-card { border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
.distinguished-students-section .distinguished-card-image { position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden; background: linear-gradient(135deg, var(--secondary-Color, #0555a2) 0%, var(--main-Color, #f29125) 100%); }
.distinguished-students-section .distinguished-card-image img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.distinguished-students-section .distinguished-card:hover .distinguished-card-image img { transform: scale(1.05); }
.distinguished-students-section .distinguished-card-image .distinguished-photo-placeholder { position: absolute; inset: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--secondary-Color, #0555a2) 0%, var(--main-Color, #f29125) 100%); }
.distinguished-students-section .distinguished-card-image .distinguished-photo-placeholder i { font-size: 4rem; color: rgba(255,255,255,0.4); }
.distinguished-students-section .distinguished-card .card-body { padding: 1rem 1.25rem; flex-grow: 1; display: flex; flex-direction: column; text-align: center; }
.distinguished-students-section .distinguished-name { font-weight: 600; margin-bottom: 0.25rem; }
.distinguished-students-section .distinguished-quote { line-height: 1.6; color: var(--body-color); font-size: 0.9rem; margin-bottom: 0; }
.distinguished-students-section .swiper-button-next,
.distinguished-students-section .swiper-button-prev { color: var(--primary, #c9a227); }
.distinguished-students-section .swiper-pagination-bullet-active { background: var(--primary, #c9a227); }
</style>
@endpush

@section('content')

<!-- Hero Swiper Section Start -->
@php
    $heroSlides = $heroSlides ?? collect();
    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([(object)[
            'title' => 'ابدأ التعلم اليوم',
            'subtitle' => '# أفضل منصة تعليمية',
            'description' => 'عزز رحلتك التعليمية مع منصة الكورسات المتطورة لدينا.',
            'button_text' => 'ابدأ الآن',
            'button_url' => '#',
            'button2_text' => 'تصفح الصفوف',
            'button2_url' => '#classes-section',
            'background_image' => null,
            'content_image' => asset('frontend/images/hero-img.png'),
            'text_position' => 'right',
        ]]);
    }
@endphp
<section class="hero-swiper-section" id="hero-swiper-section">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">
            @foreach($heroSlides as $slide)
                <div class="swiper-slide hero-slide">
                    <div class="hero-slide-bg" @if($slide->background_image ?? null) style="background-image: url('{{ media_public_url($slide->background_image) }}');" @endif></div>
                    <div class="container hero-slide-content-wrapper h-100">
                        @php
                            $textPos = $slide->text_position ?? 'right';
                            if (!in_array($textPos, ['left', 'right', 'center'])) {
                                $textPos = 'right';
                            }
                        @endphp
                        <div class="row align-items-center justify-content-center h-100 hero-layout-{{ $textPos }}">
                            <div class="col-12 col-lg-8 hero-content-col">
                                <div class="hero-content hero-pos-{{ $textPos }}">
                                    @if(!empty($slide->subtitle ?? null))
                                        <span class="hero-badge">{{ $slide->subtitle }}</span>
                                    @endif
                                    <h1 class="hero-title">{!! nl2br(e($slide->title ?? '')) !!}</h1>
                                    @if(!empty($slide->description ?? null))
                                        <p class="hero-description">{{ $slide->description }}</p>
                                    @endif
                                    @if(!empty($slide->button_text ?? null) && !empty($slide->button_url ?? null) || !empty($slide->button2_text ?? null) && !empty($slide->button2_url ?? null))
                                        <div class="hero-actions d-flex align-items-center justify-content-center gap-3 mb-4 w-100">
                                            @if(!empty($slide->button_text ?? null) && !empty($slide->button_url ?? null))
                                                <a href="{{ $slide->button_url }}" class="btn btn-primary btn-lg btn-gold">
                                                    {{ $slide->button_text }}
                                                    <i class="fa-solid fa-angles-left ms-2"></i>
                                                </a>
                                            @endif
                                            @if(!empty($slide->button2_text ?? null) && !empty($slide->button2_url ?? null))
                                                <a href="{{ $slide->button2_url }}" class="btn btn-outline-light btn-lg hero-btn-outline">
                                                    {{ $slide->button2_text }}
                                                    <i class="fa-solid fa-angles-left ms-2"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="swiper-button-prev hero-swiper-prev" aria-label="السابق"></div>
        <div class="swiper-button-next hero-swiper-next" aria-label="التالي"></div>
        <div class="swiper-pagination hero-swiper-pagination"></div>
    </div>
</section>
<!-- Hero Swiper Section End -->

<!-- Classes Section Start -->
<section class="classes-section py-5 homepage-classes" id="classes-section">
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
                                        @php
                                            $classImageUrl = !empty($class['image']) ? media_public_url($class['image']) : '';
                                        @endphp
                                        @if($classImageUrl !== '')
                                            <img src="{{ $classImageUrl }}"
                                                 alt="{{ $class['name'] }}"
                                                 class="img-fluid"
                                                 loading="lazy"
                                                 onerror="this.classList.add('d-none'); const p=this.nextElementSibling; if(p){ p.classList.remove('d-none'); }">
                                            <div class="class-card-placeholder d-none">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                            </div>
                                        @else
                                            <div class="class-card-placeholder">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                                <div class="class-card-body">
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
                                        @include('frontend.partials.price-presentation', [
                                            'presentation' => $class['price_presentation'] ?? ['mode' => 'hidden', 'text' => ''],
                                        ])
                                    </div>
                                    <div class="class-card-buttons d-flex gap-2 flex-nowrap">
                                        <a href="{{ !empty($class['slug']) ? route('frontend.class.show', $class['slug']) : '#' }}" class="class-card-btn enroll-btn">
                                            عرض المواد
                                        </a>
                                        @if(!empty($class['slug']) && !empty($class['id']))
                                            @php
                                                $checkoutUrl = route('frontend.checkout', ['purchase_type' => 'class', 'class_id' => $class['id']]);
                                            @endphp
                                            @guest
                                                <a href="{{ route('register', ['redirect' => $checkoutUrl]) }}" class="class-card-btn subscribe-btn">
                                                    اشتراك مباشر
                                                </a>
                                            @else
                                                <a href="{{ $checkoutUrl }}" class="class-card-btn subscribe-btn">
                                                    اشتراك مباشر
                                                </a>
                                            @endguest
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

@php
    $distinguishedStudents = $distinguishedStudents ?? collect();
@endphp
@if($distinguishedStudents->isNotEmpty())
<!-- Distinguished Students Section Start -->
<section class="distinguished-students-section py-5" id="distinguished-students-section">
    <div class="container distinguished-swiper-section">
        <div class="row mb-3">
            <div class="col-12 text-center">
                <h2 class="section-title">متفوقو أكاديمية كوانتم</h2>
            </div>
        </div>

        <div class="row align-items-center justify-content-between mb-3">
            <div class="col-auto ms-auto d-flex align-items-center gap-2">
                <div class="swiper-button-prev distinguished-swiper-prev" aria-label="السابق"></div>
                <div class="swiper-button-next distinguished-swiper-next" aria-label="التالي"></div>
            </div>
        </div>

        <div class="swiper distinguished-swiper">
            <div class="swiper-wrapper">
                @foreach($distinguishedStudents as $ds)
                    <div class="swiper-slide">
                        <div class="card distinguished-card h-100 border-0 shadow-sm">
                            <div class="distinguished-card-image">
                                @if($ds->photo)
                                    <img src="{{ media_public_url($ds->photo) }}" alt="{{ $ds->user->name ?? '' }}" class="img-fluid">
                                @else
                                    <div class="distinguished-photo-placeholder">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body text-center">
                                <h5 class="distinguished-name mb-1">{{ $ds->user->name ?? '—' }}</h5>
                                @if($ds->schoolClass)
                                    <p class="small text-muted mb-2">{{ $ds->schoolClass->name }}</p>
                                @endif
                                <p class="distinguished-quote small">{{ $ds->quote }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination distinguished-swiper-pagination"></div>
        </div>
    </div>
</section>
<!-- Distinguished Students Section End -->
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.documentElement;
    var siteHeaderEl = document.getElementById('siteHeader');

    function updateSiteHeaderHeight() {
        if (!siteHeaderEl) return;
        rootEl.style.setProperty('--site-header-height', siteHeaderEl.offsetHeight + 'px');
    }

    updateSiteHeaderHeight();
    window.addEventListener('resize', updateSiteHeaderHeight);
    window.addEventListener('orientationchange', updateSiteHeaderHeight);

    var heroSwiperEl = document.querySelector('.hero-swiper');
    if (heroSwiperEl) {
        new Swiper('.hero-swiper', {
            dir: 'rtl',
            loop: true,
            effect: 'slide',
            autoHeight: false,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: {
                nextEl: '.hero-swiper-next',
                prevEl: '.hero-swiper-prev'
            },
            pagination: {
                el: '.hero-swiper-pagination',
                clickable: true
            }
        });
    }
    new Swiper('.classes-swiper', {
        dir: 'rtl',
        loop: true,
        spaceBetween: 16,
        centeredSlides: true,
        centeredSlidesBounds: true,
        slidesPerView: 'auto',
        breakpoints: {
            992: { slidesPerView: 3, centeredSlides: false }
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

    var distinguishedEl = document.querySelector('.distinguished-swiper');
    if (distinguishedEl) {
        new Swiper('.distinguished-swiper', {
            dir: 'rtl',
            loop: true,
            spaceBetween: 20,
            centeredSlides: true,
            slidesPerView: 1,
            breakpoints: {
                576: { slidesPerView: 1.2 },
                768: { slidesPerView: 2 },
                992: { slidesPerView: 3, centeredSlides: false }
            },
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: {
                nextEl: '.distinguished-swiper-next',
                prevEl: '.distinguished-swiper-prev'
            },
            pagination: {
                el: '.distinguished-swiper-pagination',
                clickable: true
            }
        });
    }
});
</script>
@endpush