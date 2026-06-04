@php
    $textLimit = $textLimit ?? 80;
    $previewText = $question->listPreviewText($textLimit);
    $imageUrls = $question->embeddedImageUrlsForList();
@endphp
<p class="mb-1 small question-list-preview-text">{{ $previewText }}</p>
@if(!empty($imageUrls))
<div class="question-list-preview-images mt-1">
    @foreach(array_slice($imageUrls, 0, 3) as $imgSrc)
    <button type="button"
            class="question-image-thumb-btn"
            title="انقر لعرض الصورة بالحجم الكامل"
            aria-label="عرض صورة السؤال"
            data-full-image="{{ $imgSrc }}">
        <img src="{{ $imgSrc }}"
             alt=""
             class="question-image-thumb"
             loading="lazy"
             onerror="this.closest('.question-image-thumb-btn')?.remove();">
    </button>
    @endforeach
    @if(count($imageUrls) > 3)
    <span class="badge bg-secondary align-self-center">+{{ count($imageUrls) - 3 }}</span>
    @endif
</div>
@endif
