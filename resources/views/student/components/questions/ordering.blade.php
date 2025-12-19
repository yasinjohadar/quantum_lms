<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">قم بترتيب العناصر بالترتيب الصحيح:</label>
        <div class="list-group" id="ordering-list">
            @php
                $options = $question->options->shuffle();
                $savedOrder = isset($answer) && $answer->ordering ? $answer->ordering : [];
            @endphp
            @foreach($options as $option)
                <div class="list-group-item d-flex align-items-center cursor-move" 
                     data-option-id="{{ $option->id }}"
                     style="cursor: move;">
                    <i class="bi bi-grip-vertical me-2 text-muted"></i>
                    <span class="flex-grow-1">{{ $option->content }}</span>
                    <span class="badge bg-secondary order-badge">{{ $loop->index + 1 }}</span>
                </div>
            @endforeach
        </div>
        <input type="hidden" name="ordering" id="ordering-input" value="{{ isset($answer) && $answer->ordering ? json_encode($answer->ordering) : '[]' }}">
        <small class="text-muted">اسحب العناصر لإعادة ترتيبها</small>
    </div>
</div>

<script>
    // سيتم إضافة منطق السحب والإفلات في question-types.js
</script>

