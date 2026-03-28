@if ($supervisors instanceof \Illuminate\Pagination\LengthAwarePaginator && $supervisors->hasPages())
    {{ $supervisors->withQueryString()->links() }}
@endif
