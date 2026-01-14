@if ($enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator && $enrollments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $enrollments->withQueryString()->links() }}
    </div>
@endif
