@include('partials.question-math-assets')

@push('styles')
    @include('partials.questions.mcq-options-styles')
@endpush

@push('body-end')
    @include('partials.questions.math-editor-modal')
@endpush
