<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">اختر جميع الإجابات الصحيحة:</label>
        <div class="list-group">
            @foreach($question->options as $option)
                <label class="list-group-item d-flex align-items-center cursor-pointer">
                    <input type="checkbox" 
                           name="option_ids[]" 
                           value="{{ $option->id }}" 
                           class="form-check-input me-2"
                           {{ (isset($answer) && $answer->selected_options && in_array($option->id, $answer->selected_options)) ? 'checked' : '' }}>
                    <span class="flex-grow-1">{{ $option->content }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

