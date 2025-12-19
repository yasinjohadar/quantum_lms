<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">قم بسحب العناصر وإفلاتها في المكان الصحيح:</label>
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">العناصر المتاحة:</h6>
                <div class="list-group" id="drag-items">
                    @foreach($question->options as $option)
                        <div class="list-group-item draggable-item" 
                             draggable="true"
                             data-option-id="{{ $option->id }}">
                            {{ $option->content }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">المناطق المستهدفة:</h6>
                <div class="list-group" id="drop-zones">
                    @php
                        $dropZones = $question->drop_zones ?? [];
                    @endphp
                    @foreach($dropZones as $index => $zone)
                        <div class="list-group-item drop-zone" 
                             data-zone-id="{{ $index }}">
                            <span class="text-muted">{{ $zone['label'] ?? 'منطقة ' . ($index + 1) }}</span>
                            <div class="dropped-item" data-option-id=""></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <input type="hidden" name="answer" id="drag-drop-answer" value="{{ isset($answer) && $answer->answer ? json_encode($answer->answer) : '{}' }}">
        <small class="text-muted">اسحب العناصر من اليسار إلى المناطق المستهدفة</small>
    </div>
</div>

<script>
    // سيتم إضافة منطق السحب والإفلات في question-types.js
</script>

