@if ($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $logs->withQueryString()->links() }}
@endif
