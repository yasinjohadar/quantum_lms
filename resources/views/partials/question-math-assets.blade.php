@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('assets/libs/katex/katex.min.css') }}?v=0.16.11">
        <style>
            .katex-src { display: inline; white-space: normal; }
            .katex-src[data-display="1"] { display: block; margin: .35rem 0; text-align: center; }
        </style>
    @endpush
@endonce
