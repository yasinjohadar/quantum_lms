<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">اكتب إجابتك:</label>
        <textarea name="answer_text" 
                  class="form-control" 
                  rows="3" 
                  placeholder="اكتب إجابتك هنا..."
                  required>{{ isset($answer) ? $answer->answer_text : '' }}</textarea>
        <small class="text-muted">الإجابة يجب أن تكون قصيرة ومباشرة</small>
    </div>
</div>


