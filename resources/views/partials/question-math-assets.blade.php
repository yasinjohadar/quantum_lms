@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('assets/libs/katex/katex.min.css') }}?v=0.16.11">
        <style>
            /* عزل اتجاهي (bidi isolate): المعادلة تُعرض دوماً LTR داخلياً بغض النظر
               عن أنها مُضمَّنة في صفحة RTL — بدونه قد يُعيد متصفح ترتيب/عكس رموز
               محايدة مثل الأقواس ( ) المحيطة بالمعادلة (مثل u_n) عند حدود الاتجاه. */
            .katex-src { display: inline; white-space: normal; direction: ltr; unicode-bidi: isolate; }
            .katex-src[data-display="1"] { display: block; margin: .35rem 0; text-align: center; }
            /* KaTeX لا يضبط اتجاهه بنفسه، فيرث rtl من الصفحة فتُعكس الأقواس المرآتية
               [ ] ] [ (فترات مثل ]0, +∞[ تظهر مقلوبة). فرض ltr على مخرجات KaTeX يمنع ذلك. */
            .katex, .katex * { direction: ltr; }
        </style>
    @endpush
@endonce
