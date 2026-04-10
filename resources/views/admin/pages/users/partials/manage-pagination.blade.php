@if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $users->withQueryString()->links() }}
@endif
