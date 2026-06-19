@if($lesson->attachments && $lesson->attachments->count() > 0)
    @php
        $attachmentCount = $lesson->attachments->count();
        $attachmentLabel = $attachmentCount === 1
            ? 'مرفق واحد — عرض المرفقات'
            : $attachmentCount . ' مرفقات — عرض الكل';
    @endphp
    <a href="{{ route('student.lessons.show.folders', $lesson) }}#collapseAttachments"
       class="btn btn-sm btn-link p-1 text-success position-relative lesson-attachment-quick-link"
       title="{{ $attachmentLabel }}"
       aria-label="{{ $attachmentCount === 1 ? 'عرض مرفق الدرس' : 'عرض ' . $attachmentCount . ' مرفقات' }}">
        <i class="bi bi-paperclip fs-5"></i>
        @if($attachmentCount > 1)
            <span class="lesson-attachment-quick-link__count">{{ $attachmentCount }}</span>
        @endif
    </a>
@endif

@once
    @push('styles')
    <style>
        .lesson-attachment-quick-link__count {
            position: absolute;
            top: 0;
            inset-inline-start: 100%;
            transform: translate(-65%, -15%);
            min-width: 1rem;
            height: 1rem;
            padding: 0 0.25rem;
            border-radius: 999px;
            background: #198754;
            color: #fff;
            font-size: 0.625rem;
            font-weight: 700;
            line-height: 1rem;
            text-align: center;
            pointer-events: none;
        }
    </style>
    @endpush
@endonce
