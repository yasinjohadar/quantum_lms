@if ($classes instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $classes->withQueryString()->links() }}
@endif
