@props(['review'])

<div class="card custom-card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="mb-1">
                    @if($review->is_anonymous)
                        <span class="text-muted">مستخدم مجهول</span>
                    @else
                        {{ $review->user->name ?? 'مستخدم محذوف' }}
                    @endif
                </h6>
                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fe fe-star {{ $i <= $review->rating ? 'text-warning fill' : 'text-muted' }}"></i>
                @endfor
            </div>
        </div>

        @if($review->title)
            <h6 class="mb-2">{{ $review->title }}</h6>
        @endif

        @if($review->comment)
            <p class="mb-2">{{ $review->comment }}</p>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-sm btn-outline-primary toggle-helpful" data-review-id="{{ $review->id }}">
                <i class="fe fe-thumbs-up me-1"></i>
                مفيد (<span class="helpful-count">{{ $review->is_helpful_count }}</span>)
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.toggle-helpful').forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.dataset.reviewId;
            const countSpan = this.querySelector('.helpful-count');
            
            fetch(`/student/reviews/${reviewId}/helpful`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    countSpan.textContent = data.helpful_count;
                    if (data.is_helpful) {
                        this.classList.add('active');
                    } else {
                        this.classList.remove('active');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
</script>
@endpush

