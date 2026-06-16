@if ($enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator && $enrollments->hasPages())
    <div class="d-flex justify-content-center">
        {{ $enrollments->withQueryString()->links() }}
    </div>
@endif
