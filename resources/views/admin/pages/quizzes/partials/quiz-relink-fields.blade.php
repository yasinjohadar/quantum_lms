@include('admin.pages.quizzes.partials.quiz-placement-fields', array_merge(get_defined_vars(), [
    'showCopyBanner' => true,
    'includeRelinkFlag' => true,
    'idPrefix' => 'quizPlacement',
]))
