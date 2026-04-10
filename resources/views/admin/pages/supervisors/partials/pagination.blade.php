@if ($supervisors instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $supervisors->withQueryString()->links() }}
@endif
