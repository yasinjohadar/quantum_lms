<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">اسحب العناصر لترتيبها بالترتيب الصحيح:</label>
        @php
            $savedOrdering = [];
            if (isset($answer) && $answer->ordering) {
                $savedOrdering = is_array($answer->ordering)
                    ? $answer->ordering
                    : (json_decode($answer->ordering, true) ?: []);
            }
            $savedOrdering = array_map('strval', $savedOrdering);

            if (! empty($savedOrdering)) {
                $displayOptions = $question->options->sortBy(function ($option) use ($savedOrdering) {
                    $index = array_search((string) $option->id, $savedOrdering, true);

                    return $index === false ? PHP_INT_MAX : $index;
                })->values();
            } else {
                // خلط العناصر حتى لا يظهر الترتيب الصحيح مسبقاً
                $displayOptions = $question->options->shuffle()->values();
            }

            $initialOrder = $displayOptions->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
        @endphp
        <ul class="list-group mb-2" id="ordering-list">
            @foreach($displayOptions as $index => $option)
                <li class="list-group-item d-flex align-items-center gap-3"
                    data-option-id="{{ $option->id }}"
                    draggable="true"
                    style="cursor: move; user-select: none;">
                    <span class="badge bg-primary rounded-pill order-badge">{{ $index + 1 }}</span>
                    <i class="bi bi-grip-vertical text-muted"></i>
                    <span class="flex-grow-1 question-text-body">{!! format_question_markup($option->content) !!}</span>
                </li>
            @endforeach
        </ul>
        <input type="hidden" name="ordering" id="ordering-input" value="{{ json_encode($initialOrder) }}">
        <small class="text-muted">اسحب العناصر لإعادة ترتيبها</small>
    </div>
</div>
