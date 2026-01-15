@if ($quizzes instanceof \Illuminate\Pagination\LengthAwarePaginator && $quizzes->hasPages())
    {{ $quizzes->withQueryString()->links() }}
@endif
