@php
    /** @var \Illuminate\Support\Collection|\App\Models\SchoolClass[] $footerClasses */
    $footerClasses = $footerClasses ?? collect();
@endphp

<footer class="frontend-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="frontend-footer__brand">
                    <a href="/" class="frontend-footer__logo">
                        <img src="{{ asset('frontend/images/logo.png') }}" alt="logo">
                    </a>
                    <p class="frontend-footer__desc">
                        منصة تعليمية حديثة تساعدك على اختيار الصف والمادة المناسبة والبدء بالتعلم بسهولة.
                    </p>

                    <div class="frontend-footer__social">
                        @foreach($socialLinks ?? [] as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="{{ $link->name }}">
                                <i class="{{ $link->icon_class }}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="frontend-footer__title">روابط سريعة</h5>
                <ul class="frontend-footer__links">
                    <li><a href="/">الرئيسية</a></li>
                    <li><a href="#">اتصل بنا</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="frontend-footer__title">الصفوف الدراسية</h5>
                <ul class="frontend-footer__links">
                    @forelse($footerClasses as $class)
                        <li>
                            <a href="{{ route('frontend.class.show', $class->slug) }}">
                                {{ $class->name }}
                            </a>
                        </li>
                    @empty
                        <li class="text-muted">لا توجد صفوف حالياً</li>
                    @endforelse
                </ul>
            </div>

            @if(!empty($footerContactAddress ?? '') || !empty($footerContactPhone ?? '') || !empty($footerContactEmail ?? ''))
            <div class="col-lg-3 col-md-6">
                <h5 class="frontend-footer__title">تواصل معنا</h5>
                <ul class="frontend-footer__meta">
                    @if(!empty($footerContactAddress ?? ''))
                    <li>
                        <i class="fa-solid fa-location-dot"></i>
                        <span>العنوان: {{ $footerContactAddress }}</span>
                    </li>
                    @endif
                    @if(!empty($footerContactPhone ?? ''))
                    <li>
                        <i class="fa-solid fa-phone"></i>
                        <span>الهاتف: {{ $footerContactPhone }}</span>
                    </li>
                    @endif
                    @if(!empty($footerContactEmail ?? ''))
                    <li>
                        <i class="fa-solid fa-envelope"></i>
                        <span>البريد: {{ $footerContactEmail }}</span>
                    </li>
                    @endif
                </ul>
            </div>
            @endif
        </div>

        <div class="frontend-footer__bottom">
            <div class="frontend-footer__copy">
                © {{ date('Y') }} جميع الحقوق محفوظة
            </div>
        </div>
    </div>
</footer>