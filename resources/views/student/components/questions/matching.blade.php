<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">قم بمطابقة العناصر:</label>
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">العناصر اليسرى:</h6>
                <div class="list-group mb-3" id="left-items">
                    @foreach($question->options as $option)
                        <div class="list-group-item" data-option-id="{{ $option->id }}">
                            <strong>{{ $option->content }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">العناصر اليمنى:</h6>
                <div class="list-group mb-3" id="right-items">
                    @php
                        $rightOptions = $question->options->pluck('matching_content')->filter()->shuffle();
                    @endphp
                    @foreach($rightOptions as $rightOption)
                        <div class="list-group-item matching-target" data-content="{{ $rightOption }}">
                            {{ $rightOption }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <input type="hidden" name="matching_pairs" id="matching-pairs-input" value="{{ isset($answer) && $answer->matching_pairs ? json_encode($answer->matching_pairs) : '{}' }}">
        <small class="text-muted">اسحب العناصر من اليسار إلى اليمين للمطابقة</small>
    </div>
</div>

<script>
    // سيتم إضافة منطق السحب والإفلات في question-types.js
</script>

