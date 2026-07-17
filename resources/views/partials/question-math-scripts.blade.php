{{-- KaTeX محلي + احتياطي CDN إن فشل المسار المحلي على السيرفر --}}
<script src="{{ asset('assets/libs/katex/katex.min.js') }}?v=0.16.11"></script>
<script src="{{ asset('assets/libs/katex/contrib/mhchem.min.js') }}?v=0.16.11"></script>
<script src="{{ asset('assets/libs/katex/contrib/auto-render.min.js') }}?v=0.16.11"></script>
<script>
(function () {
    function needFallback() {
        return typeof katex === 'undefined' || typeof renderMathInElement !== 'function';
    }
    if (!needFallback()) {
        return;
    }
    var css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = 'https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css';
    document.head.appendChild(css);

    function load(src, next) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = next || function () {};
        s.onerror = next || function () {};
        document.head.appendChild(s);
    }
    load('https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js', function () {
        load('https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js');
    });
})();
</script>
<script src="{{ asset('assets/js/question-math-katex.js') }}?v=20260717d"></script>
