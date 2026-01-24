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
                        <img src="{{ asset('frontend/images/logo-footer.webp') }}" alt="logo">
                    </a>
                    <p class="frontend-footer__desc">
                        منصة تعليمية حديثة تساعدك على اختيار الصف والمادة المناسبة والبدء بالتعلم بسهولة.
                    </p>

                    <div class="frontend-footer__social">
                        <a href="#" class="social-link" aria-label="facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="telegram">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="youtube">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="frontend-footer__title">روابط سريعة</h5>
                <ul class="frontend-footer__links">
                    <li><a href="/">الرئيسية</a></li>
                    <li><a href="/courses">الكورسات</a></li>
                    <li><a href="/blog">المدونة</a></li>
                    <li><a href="#">من نحن</a></li>
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

            <div class="col-lg-3 col-md-6">
                <h5 class="frontend-footer__title">تواصل معنا</h5>
                <ul class="frontend-footer__meta">
                    <li>
                        <i class="fa-solid fa-location-dot"></i>
                        <span>العنوان: دمشق - سوريا</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-phone"></i>
                        <span>الهاتف: 000 000 000</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-envelope"></i>
                        <span>البريد: info@example.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="frontend-footer__bottom">
            <div class="frontend-footer__copy">
                © {{ date('Y') }} جميع الحقوق محفوظة
            </div>
            <div class="frontend-footer__mini-links">
                <a href="#">سياسة الخصوصية</a>
                <span class="sep">|</span>
                <a href="#">الشروط والأحكام</a>
            </div>
        </div>
    </div>
</footer>