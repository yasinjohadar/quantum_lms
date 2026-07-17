@once
{{-- KaTeX من CDN أولاً (موثوق على السيرفر) + احتياطي محلي --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/mhchem.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    if (typeof katex !== 'undefined' && typeof katex.render === 'function') {
        return;
    }
    var localBase = @json(rtrim(asset('assets/libs/katex'), '/'));
    var localCss = document.createElement('link');
    localCss.rel = 'stylesheet';
    localCss.href = localBase + '/katex.min.css?v=0.16.11';
    document.head.appendChild(localCss);

    function load(src, next) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = next || function () {};
        s.onerror = next || function () {};
        document.head.appendChild(s);
    }
    load(localBase + '/katex.min.js?v=0.16.11', function () {
        load(localBase + '/contrib/mhchem.min.js?v=0.16.11', function () {
            load(localBase + '/contrib/auto-render.min.js?v=0.16.11');
        });
    });
})();
</script>
<script src="{{ asset('assets/js/question-math-katex.js') }}?v=20260717e"></script>
@endonce
