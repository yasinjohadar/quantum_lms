<div class="question-answer">
    @php
        $savedAssignments = [];
        if (isset($answer) && $answer->answer) {
            $savedAssignments = is_array($answer->answer)
                ? $answer->answer
                : (json_decode($answer->answer, true) ?: []);
        }

        $zoneLabels = $question->options
            ->pluck('match_target')
            ->map(fn ($t) => trim(html_entity_decode(strip_tags((string) $t), ENT_QUOTES, 'UTF-8')))
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div class="mb-3">
        <label class="form-label fw-semibold">اسحب العناصر إلى المناطق المناسبة:</label>

        <div class="mb-4">
            <h6 class="mb-3">العناصر القابلة للسحب:</h6>
            <div class="d-flex flex-wrap gap-2" id="drag-drop-pool">
                @foreach($question->options as $option)
                    @php
                        $assignedZone = $savedAssignments[$option->id] ?? $savedAssignments[(string) $option->id] ?? null;
                        $isAssigned = $assignedZone !== null && $assignedZone !== '';
                        $itemHtml = format_question_markup($option->content);
                    @endphp
                    <div class="badge bg-primary p-3 cursor-move question-text-body drag-drop-item {{ $isAssigned ? 'd-none' : '' }}"
                         draggable="true"
                         data-option-id="{{ $option->id }}"
                         data-zone-label="{{ $assignedZone ?? '' }}"
                         data-rendered-html="{{ $itemHtml }}"
                         id="practice-drag-item-{{ $option->id }}"
                         style="cursor: move; user-select: none;">
                        <i class="bi bi-grip-vertical me-2"></i>
                        {!! $itemHtml !!}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="row" id="drag-drop-zones">
            @forelse($zoneLabels as $zoneLabel)
                <div class="col-md-6 mb-3">
                    <div class="border border-2 border-dashed rounded p-3 practice-drop-zone"
                         data-zone-label="{{ $zoneLabel }}"
                         style="min-height: 120px;">
                        <h6 class="mb-3">{{ $zoneLabel }}</h6>
                        <div class="dropped-items">
                            @foreach($question->options as $option)
                                @php
                                    $assigned = $savedAssignments[$option->id] ?? $savedAssignments[(string) $option->id] ?? null;
                                @endphp
                                @if($assigned !== null && (string) $assigned === (string) $zoneLabel)
                                    <div class="badge bg-success p-2 mb-2 me-1 question-text-body dropped-item"
                                         data-option-id="{{ $option->id }}">
                                        {!! format_question_markup($option->content) !!}
                                        <button type="button" class="btn-close btn-close-white ms-2 btn-sm"
                                                onclick="practiceRemoveDragDrop('{{ $option->id }}')"></button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <p class="text-muted small mb-0 mt-2">أسقط العناصر هنا</p>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-0">لم يتم تعريف مناطق الإفلات لهذا السؤال.</div>
                </div>
            @endforelse
        </div>

        <input type="hidden"
               name="drag_drop_assignments"
               id="drag-drop-answer"
               value="{{ json_encode($savedAssignments, JSON_UNESCAPED_UNICODE) }}">
        <small class="text-muted d-block mt-2">اسحب كل عنصر إلى المنطقة الصحيحة</small>
    </div>
</div>

<script>
(function () {
    let draggedItem = null;

    function updatePracticeDragDropAnswer() {
        const assignments = {};
        document.querySelectorAll('#drag-drop-pool .drag-drop-item').forEach(function (item) {
            if (item.dataset.zoneLabel) {
                assignments[item.dataset.optionId] = item.dataset.zoneLabel;
            }
        });
        const input = document.getElementById('drag-drop-answer');
        if (input) {
            input.value = JSON.stringify(assignments);
        }
    }

    window.practiceRemoveDragDrop = function (optionId) {
        const item = document.getElementById('practice-drag-item-' + optionId);
        if (!item) return;

        const dropped = document.querySelector('#drag-drop-zones .dropped-item[data-option-id="' + optionId + '"]');
        if (dropped) dropped.remove();

        item.dataset.zoneLabel = '';
        item.classList.remove('d-none');
        updatePracticeDragDropAnswer();
    };

    document.querySelectorAll('#drag-drop-pool .drag-drop-item').forEach(function (item) {
        item.addEventListener('dragstart', function (e) {
            draggedItem = item;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.dataset.optionId);
            item.style.opacity = '0.5';
        });
        item.addEventListener('dragend', function () {
            item.style.opacity = '1';
            draggedItem = null;
        });
    });

    document.querySelectorAll('.practice-drop-zone').forEach(function (zone) {
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('border-primary', 'bg-primary-transparent');
        });
        zone.addEventListener('dragleave', function () {
            zone.classList.remove('border-primary', 'bg-primary-transparent');
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('border-primary', 'bg-primary-transparent');
            if (!draggedItem) return;

            const optionId = draggedItem.dataset.optionId;
            const zoneLabel = zone.dataset.zoneLabel;
            const rendered = draggedItem.dataset.renderedHtml || draggedItem.innerHTML;

            window.practiceRemoveDragDrop(optionId);

            const dropped = document.createElement('div');
            dropped.className = 'badge bg-success p-2 mb-2 me-1 question-text-body dropped-item';
            dropped.dataset.optionId = optionId;
            dropped.innerHTML = rendered +
                '<button type="button" class="btn-close btn-close-white ms-2 btn-sm" onclick="practiceRemoveDragDrop(\'' + optionId + '\')"></button>';
            zone.querySelector('.dropped-items').appendChild(dropped);

            draggedItem.dataset.zoneLabel = zoneLabel;
            draggedItem.classList.add('d-none');
            updatePracticeDragDropAnswer();
        });
    });
})();
</script>
