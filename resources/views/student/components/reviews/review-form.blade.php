@props(['reviewable', 'type', 'review' => null])

<form action="{{ $review ? route('student.reviews.update', $review) : route('student.reviews.store') }}" method="POST">
    @csrf
    @if($review)
        @method('PUT')
    @else
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="id" value="{{ $reviewable->id }}">
    @endif

    <div class="mb-3">
        <label class="form-label">التقييم بالنجوم <span class="text-danger">*</span></label>
        <div class="rating-input">
            <div class="d-flex align-items-center gap-2">
                @for($i = 5; $i >= 1; $i--)
                    <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" class="d-none" {{ old('rating', $review->rating ?? '') == $i ? 'checked' : '' }} required>
                    <label for="rating{{ $i }}" class="rating-star cursor-pointer">
                        <i class="fe fe-star fs-32 {{ old('rating', $review->rating ?? 0) >= $i ? 'text-warning fill' : 'text-muted' }}"></i>
                    </label>
                @endfor
            </div>
        </div>
        @error('rating')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">عنوان المراجعة (اختياري)</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $review->title ?? '') }}" maxlength="255">
        @error('title')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">نص المراجعة (اختياري)</label>
        <textarea name="comment" class="form-control" rows="5" maxlength="2000">{{ old('comment', $review->comment ?? '') }}</textarea>
        <small class="text-muted">الحد الأقصى: 2000 حرف</small>
        @error('comment')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1" {{ old('is_anonymous', $review->is_anonymous ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_anonymous">
                تقييم مجهول
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">إلغاء</a>
        <button type="submit" class="btn btn-primary">
            <i class="fe fe-send me-1"></i> {{ $review ? 'حفظ التعديلات' : 'إرسال التقييم' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Rating stars interaction
    document.querySelectorAll('.rating-star').forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = 5 - index;
            document.querySelector(`#rating${rating}`).checked = true;
            
            // Update visual stars
            document.querySelectorAll('.rating-star i').forEach((icon, i) => {
                if (i <= index) {
                    icon.classList.remove('text-muted');
                    icon.classList.add('text-warning', 'fill');
                } else {
                    icon.classList.remove('text-warning', 'fill');
                    icon.classList.add('text-muted');
                }
            });
        });

        star.addEventListener('mouseenter', function() {
            const rating = 5 - index;
            document.querySelectorAll('.rating-star i').forEach((icon, i) => {
                if (i <= index) {
                    icon.classList.add('text-warning');
                }
            });
        });
    });

    document.querySelector('.rating-input')?.addEventListener('mouseleave', function() {
        const checkedRating = document.querySelector('input[name="rating"]:checked')?.value || 0;
        document.querySelectorAll('.rating-star i').forEach((icon, i) => {
            if (i < checkedRating) {
                icon.classList.remove('text-muted');
                icon.classList.add('text-warning', 'fill');
            } else {
                icon.classList.remove('text-warning', 'fill');
                icon.classList.add('text-muted');
            }
        });
    });
</script>
@endpush

