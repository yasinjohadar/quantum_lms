<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">اكتب مقالك:</label>
        <textarea name="answer_text" 
                  class="form-control" 
                  rows="8" 
                  placeholder="اكتب مقالك هنا..."
                  required>{{ isset($answer) ? $answer->answer_text : '' }}</textarea>
        <small class="text-muted">يرجى كتابة مقال شامل ومفصل</small>
    </div>
</div>

