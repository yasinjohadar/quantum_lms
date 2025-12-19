<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">املأ الفراغات:</label>
        <div class="fill-blanks-content">
            @php
                $content = $question->content ?? '';
                $blanks = $question->blank_answers ?? [];
                $savedAnswers = isset($answer) && $answer->fill_blanks_answers ? $answer->fill_blanks_answers : [];
                
                // استخراج الفراغات من المحتوى (افتراضياً بصيغة {1}, {2}, ...)
                preg_match_all('/\{(\d+)\}/', $content, $matches);
                $blankNumbers = $matches[1] ?? [];
            @endphp
            
            {!! preg_replace_callback('/\{(\d+)\}/', function($match) use ($blankNumbers, $savedAnswers) {
                $blankIndex = array_search($match[1], $blankNumbers);
                $value = $savedAnswers[$blankIndex] ?? '';
                return '<input type="text" name="fill_blanks_answers[]" class="form-control d-inline-block" style="width: 150px; margin: 0 5px;" value="' . htmlspecialchars($value) . '" placeholder="...">';
            }, $content) !!}
        </div>
        <small class="text-muted">املأ كل فراغ بالإجابة الصحيحة</small>
    </div>
</div>

