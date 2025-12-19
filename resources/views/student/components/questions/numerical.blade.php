<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">أدخل القيمة الرقمية:</label>
        <input type="number" 
               name="numeric_answer" 
               class="form-control" 
               step="any"
               placeholder="أدخل الرقم..."
               value="{{ isset($answer) ? $answer->numeric_answer : '' }}"
               required>
        <small class="text-muted">يمكنك إدخال أرقام عشرية</small>
    </div>
</div>

