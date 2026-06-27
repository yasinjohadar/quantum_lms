@if ($lessons instanceof \Illuminate\Pagination\LengthAwarePaginator && $lessons->hasPages())
    {{ $lessons->withQueryString()->links() }}
@endif
