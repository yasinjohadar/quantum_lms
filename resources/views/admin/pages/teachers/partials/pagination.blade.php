@if ($teachers instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $teachers->withQueryString()->links() }}
@endif
