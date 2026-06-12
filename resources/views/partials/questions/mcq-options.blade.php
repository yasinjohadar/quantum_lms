@php
    $selectedIds = collect($selectedOptionIds ?? [])->map(fn ($id) => (string) $id)->all();
    $questionType = $questionType ?? 'single_choice';
    $inputType = in_array($questionType, ['multiple_choice', 'multi_select'], true) ? 'checkbox' : 'radio';
    $inputName = $inputName ?? 'answer_option';
    $questionId = $questionId ?? null;
    $nameSuffix = $questionId ? '_' . $questionId : '';
    $fieldName = $inputType === 'checkbox' ? $inputName . $nameSuffix . '[]' : $inputName . $nameSuffix;
@endphp

<div class="mcq-options-list">
    @foreach($options as $optIndex => $option)
        @php
            $letter = chr(65 + $optIndex);
            $optionId = (string) $option->id;
            $isSelected = in_array($optionId, $selectedIds, true);
            $inputId = 'mcq_opt_' . ($questionId ?? 'q') . '_' . $option->id;
        @endphp

        <label class="mcq-option-card is-interactive {{ $isSelected ? 'is-selected' : '' }}" for="{{ $inputId }}">
            <input type="{{ $inputType }}"
                   name="{{ $fieldName }}"
                   id="{{ $inputId }}"
                   value="{{ $option->id }}"
                   {{ $isSelected ? 'checked' : '' }}>
            <span class="mcq-option-card__letter" aria-hidden="true">{{ $letter }}</span>
            <span class="mcq-option-card__body question-text-body">
                @if(!empty($option->image))
                    <img src="{{ media_public_url($option->image) }}" class="mb-2 rounded" style="max-height: 48px;" alt="">
                @endif
                {!! format_question_markup($option->content) !!}
            </span>
        </label>
    @endforeach
</div>
