<!-- Scroll To Top -->
<button type="button" class="scrollToTop student-scroll-top" aria-label="الرجوع للأعلى">
    <span class="student-scroll-top__glow" aria-hidden="true"></span>
    <i class="bi bi-arrow-up-short student-scroll-top__icon" aria-hidden="true"></i>
</button>
<div id="responsive-overlay"></div>
<!-- Scroll To Top -->

<!-- Popper JS -->
<script src="{{ asset('assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
{{-- <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script> --}}
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- Defaultmenu JS -->
<script src="{{ asset('assets/js/defaultmenu.min.js') }}"></script>

<!-- Node Waves JS -->
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

<!-- Sticky JS -->
<script src="{{ asset('assets/js/sticky.js') }}"></script>

<!-- Simplebar JS -->
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/simplebar.js') }}"></script>

<!-- Color Picker JS -->
<script src="{{ asset('assets/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>

{{-- ApexCharts / jsvectormap / assets/js/index.js are dashboard widgets; لا تُحمّل على صفحات الطالب
     لتجنب أخطاء "Element not found" وبطء التحميل. صفحات التقارير تدفع ApexCharts محلياً عند الحاجة. --}}

<!-- Custom-Switcher JS -->
<script src="{{ asset('assets/js/custom-switcher.min.js') }}{{ file_exists(public_path('assets/js/custom-switcher.min.js')) ? '?v=' . filemtime(public_path('assets/js/custom-switcher.min.js')) : '' }}"></script>

<!-- Custom JS -->
<script src="{{ asset('assets/js/custom.js') }}"></script>

<!-- CSRF Token Setup for AJAX -->
<script>
    // إعداد CSRF token لجميع طلبات AJAX
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    
    // إعداد fetch لاستخدام CSRF token
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (!options.headers) {
            options.headers = {};
        }
        if (!options.headers['X-CSRF-TOKEN']) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                options.headers['X-CSRF-TOKEN'] = token;
            }
        }
        return originalFetch(url, options);
    };
</script>

@stack('scripts')
@yield('script')

<!-- Real-time Notifications (Reverb + Echo) -->
@auth
<script>
    window.currentUserId = {{ auth()->id() }};
</script>
@endauth
