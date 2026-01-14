@if ($students instanceof \Illuminate\Pagination\LengthAwarePaginator && $students->hasPages())
    {{ $students->withQueryString()->links() }}
@endif
