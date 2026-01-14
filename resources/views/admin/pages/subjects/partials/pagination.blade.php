@if ($subjects instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $subjects->withQueryString()->links() }}
@endif
