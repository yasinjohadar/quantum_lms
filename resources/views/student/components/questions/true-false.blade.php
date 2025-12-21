<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">اختر الإجابة:</label>
        <div class="list-group">
            @foreach($question->options as $option)
                <label class="list-group-item d-flex align-items-center cursor-pointer">
                    <input type="radio" 
                           name="option_id" 
                           value="{{ $option->id }}" 
                           class="form-check-input me-2"
                           {{ (isset($answer) && $answer->selected_options && in_array($option->id, $answer->selected_options)) ? 'checked' : '' }}
                           required>
                    <span class="flex-grow-1">
                        <i class="bi bi-{{ $option->is_correct ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-2"></i>
                        {{ $option->content }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>


