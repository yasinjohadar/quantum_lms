{{-- إجبار موضع سايدبار الجوال تحت الناف (يتجاوز تعارض أنماط الثيم) --}}
<style id="student-mobile-sidebar-css">
@media (max-width: 991.98px) {
    html.student-panel .app-header {
        z-index: 110 !important;
    }
    html.student-panel #sidebar.app-sidebar {
        z-index: 104 !important;
        border-start-end-radius: 1.15rem !important;
        border-end-end-radius: 1.15rem !important;
        overflow: hidden !important;
    }
}
</style>
<script>
(function () {
    function isMobileNav() {
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function primaryColor() {
        var raw = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
        return raw || '#0162e8';
    }

    function applyStudentMobileSidebar() {
        var sidebar = document.getElementById('sidebar');
        var header = document.querySelector('.app-header');
        if (!sidebar || !header) return;

        if (!isMobileNav()) {
            ['top', 'inset-block-start', 'bottom', 'inset-block-end', 'height', 'max-height', 'border-top', 'border-block-start'].forEach(function (prop) {
                sidebar.style.removeProperty(prop);
            });
            return;
        }

        var headerH = Math.ceil(header.getBoundingClientRect().height) || 60;
        var topPx = headerH + 3;
        var color = primaryColor();

        sidebar.style.setProperty('top', topPx + 'px', 'important');
        sidebar.style.setProperty('inset-block-start', topPx + 'px', 'important');
        sidebar.style.setProperty('bottom', '0px', 'important');
        sidebar.style.setProperty('inset-block-end', '0px', 'important');
        sidebar.style.setProperty('height', 'auto', 'important');
        sidebar.style.setProperty('max-height', 'none', 'important');
        sidebar.style.setProperty('border-top', '3px solid ' + color, 'important');
        sidebar.style.setProperty('border-block-start', '3px solid ' + color, 'important');
        sidebar.style.setProperty('border-start-end-radius', '1.15rem', 'important');
        sidebar.style.setProperty('border-end-end-radius', '1.15rem', 'important');
        sidebar.style.setProperty('overflow', 'hidden', 'important');
        sidebar.style.setProperty('z-index', '104', 'important');
    }

    function boot() {
        document.documentElement.classList.add('student-panel');
        if (document.body) document.body.classList.add('student-panel');
        applyStudentMobileSidebar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.addEventListener('resize', applyStudentMobileSidebar);
    window.addEventListener('orientationchange', applyStudentMobileSidebar);
    window.addEventListener('load', applyStudentMobileSidebar);

    var html = document.documentElement;
    if (window.MutationObserver) {
        new MutationObserver(function () {
            applyStudentMobileSidebar();
        }).observe(html, { attributes: true, attributeFilter: ['data-toggled', 'data-theme-mode', 'class'] });
    }

    // بعد فتح/إغلاق القائمة بقليل (أنيميشن الثيم)
    document.addEventListener('click', function (e) {
        if (!e.target || !e.target.closest) return;
        if (e.target.closest('.sidemenu-toggle, .animated-arrow, [data-bs-toggle="sidebar"]')) {
            setTimeout(applyStudentMobileSidebar, 50);
            setTimeout(applyStudentMobileSidebar, 320);
        }
    }, true);
})();
</script>
