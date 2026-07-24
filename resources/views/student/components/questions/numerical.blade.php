<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="numeric-answer-input">الإجابة الرقمية:</label>
        <input type="number"
               step="any"
               class="form-control form-control-lg"
               name="numeric_answer"
               id="numeric-answer-input"
               value="{{ isset($answer) && $answer->numeric_answer !== null ? $answer->numeric_answer : old('numeric_answer') }}"
               placeholder="أدخل الرقم..."
               required>
        @if($question->tolerance)
            <small class="text-muted d-block mt-2">
                <i class="bi bi-info-circle me-1"></i>
                يُسمح بفرق قدره ± {{ $question->tolerance }}
            </small>
        @endif
    </div>
</div>
