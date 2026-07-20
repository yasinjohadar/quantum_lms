@php
    $selectedIds = collect($selectedOptionIds ?? [])->map(fn ($id) => (string) $id)->all();
    $reviewMode = $reviewMode ?? true;
    $highlightCorrect = $highlightCorrect ?? true;
    $questionType = $questionType ?? 'single_choice';
    $inputType = in_array($questionType, ['multiple_choice', 'multi_select'], true) ? 'checkbox' : 'radio';
    $inputName = $inputName ?? 'answer_option';
@endphp

<div class="mcq-options-list">
    @foreach($options as $optIndex => $option)
        @php
            $letter = chr(65 + $optIndex);
            $optionId = (string) $option->id;
            $isSelected = in_array($optionId, $selectedIds, true) || in_array((int) $option->id, array_map('intval', $selectedIds), true);
            $isCorrect = (bool) $option->is_correct;

            $stateClass = '';
            $statusLabel = null;
            $statusClass = '';

            if ($reviewMode) {
                if ($isSelected && $isCorrect) {
                    $stateClass = 'is-correct';
                    $statusLabel = 'إجابتك صحيحة';
                    $statusClass = 'text-success';
                } elseif ($isSelected && ! $isCorrect) {
                    $stateClass = 'is-wrong';
                    $statusLabel = 'إجابتك خاطئة';
                    $statusClass = 'text-danger';
                } elseif (! $isSelected && $isCorrect && $highlightCorrect) {
                    $stateClass = 'is-correct-missed';
                    $statusLabel = 'الإجابة الصحيحة';
                    $statusClass = 'text-success';
                }
            } elseif ($isSelected) {
                $stateClass = 'is-selected';
            } elseif ($isCorrect && $highlightCorrect) {
                $stateClass = 'is-correct';
            }
        @endphp

        <div class="mcq-option-card {{ $stateClass }}">
            <span class="mcq-option-card__letter" aria-hidden="true">{{ $letter }}</span>
            <div class="mcq-option-card__body question-text-body">
                @if(!empty($option->image))
                    <img src="{{ media_public_url($option->image) }}" class="mb-2 rounded" style="max-height: 48px;" alt="">
                @endif
                {!! format_question_markup($option->content) !!}
                @if(!empty($option->feedback))
                    <div class="mcq-option-card__feedback">
                        <i class="bi bi-chat-dots me-1"></i>{!! format_question_markup($option->feedback) !!}
                    </div>
                @endif
            </div>
            @if($statusLabel)
                <span class="mcq-option-card__status {{ $statusClass }}">{{ $statusLabel }}</span>
            @endif
        </div>
    @endforeach
</div>
