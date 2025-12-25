<div class="question-answer">
    <div class="mb-3">
        <label class="form-label fw-semibold">قم بمطابقة العناصر:</label>
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">العناصر اليسرى:</h6>
                <div class="list-group mb-3" id="left-items">
                    @php
                        $savedPairs = [];
                        if (isset($answer) && $answer->matching_pairs) {
                            $savedPairs = is_array($answer->matching_pairs) ? $answer->matching_pairs : json_decode($answer->matching_pairs, true);
                        }
                    @endphp
                    @foreach($question->options as $option)
                        @php
                            $isMatched = isset($savedPairs[$option->id]);
                        @endphp
                        <div class="list-group-item matching-draggable {{ $isMatched ? 'd-none' : '' }}" 
                             data-option-id="{{ $option->id }}"
                             draggable="true"
                             ondragstart="return handleDragStart(event)"
                             style="cursor: move !important; user-select: none !important; -webkit-user-drag: element !important; touch-action: none !important; pointer-events: auto !important; -moz-user-select: none !important;">
                            <strong>{{ $option->content }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">العناصر اليمنى:</h6>
                <div class="list-group mb-3" id="right-items">
                    @php
                        $rightOptions = $question->options->pluck('match_target')->filter()->shuffle();
                    @endphp
                    @foreach($rightOptions as $rightOption)
                        @php
                            $matchedOptionId = null;
                            foreach ($savedPairs as $optId => $target) {
                                if ($target == $rightOption) {
                                    $matchedOptionId = $optId;
                                    break;
                                }
                            }
                            $matchedOption = $matchedOptionId ? $question->options->firstWhere('id', $matchedOptionId) : null;
                        @endphp
                        <div class="list-group-item matching-target {{ $matchedOptionId ? 'border-success' : '' }}" 
                             data-content="{{ $rightOption }}" 
                             data-matched-option-id="{{ $matchedOptionId ?? '' }}"
                             draggable="false"
                             ondragover="return handleDragOver(event)"
                             ondragenter="return handleDragEnter(event)"
                             ondragleave="handleDragLeave(event)"
                             ondrop="return handleDrop(event)"
                             style="min-height: 50px; pointer-events: auto !important;">
                            @if($matchedOption)
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $matchedOption->content }}</strong>
                                        <i class="bi bi-arrow-left-right mx-2 text-muted"></i>
                                        <span>{{ $rightOption }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-match" onclick="removeMatch('{{ $matchedOptionId }}')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @else
                                {{ $rightOption }}
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <input type="hidden" name="matching_pairs" id="matching-pairs-input" value="{{ isset($answer) && $answer->matching_pairs ? (is_array($answer->matching_pairs) ? json_encode($answer->matching_pairs) : $answer->matching_pairs) : '{}' }}">
        <small class="text-muted">اسحب العناصر من اليسار إلى اليمين للمطابقة</small>
    </div>
</div>

<style>
    .matching-draggable {
        transition: opacity 0.2s, background-color 0.2s;
        pointer-events: auto !important;
        -webkit-user-drag: element !important;
        user-select: none !important;
        touch-action: none !important;
        -moz-user-select: none !important;
    }
    .matching-draggable:hover {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    .matching-draggable.dragging {
        opacity: 0.5 !important;
        background-color: rgba(13, 110, 253, 0.2) !important;
    }
    .matching-target {
        pointer-events: auto !important;
    }
    .matching-target.drag-over {
        background-color: rgba(13, 110, 253, 0.15) !important;
        border: 2px solid #0d6efd !important;
    }
    #left-items, #right-items {
        pointer-events: auto !important;
    }
    .question-answer {
        pointer-events: auto !important;
    }
    .question-answer * {
        pointer-events: auto !important;
    }
</style>

<script>
    // التأكد من أن الـ functions في global scope
    window.draggedElement = null;
    
    window.handleDragStart = function(e) {
        console.log('handleDragStart called', e);
        e = e || window.event;
        window.draggedElement = e.target || e.srcElement;
        if (e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', window.draggedElement.dataset.optionId);
        }
        window.draggedElement.classList.add('dragging');
        console.log('Drag started successfully', window.draggedElement.dataset.optionId);
        return true;
    };
    
    window.handleDragOver = function(e) {
        e = e || window.event;
        if (e.preventDefault) {
            e.preventDefault();
        }
        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'move';
        }
        var target = e.currentTarget || e.target;
        target.classList.add('drag-over');
        return false;
    };
    
    window.handleDragEnter = function(e) {
        e = e || window.event;
        if (e.preventDefault) {
            e.preventDefault();
        }
        var target = e.currentTarget || e.target;
        target.classList.add('drag-over');
        return false;
    };
    
    window.handleDragLeave = function(e) {
        e = e || window.event;
        var target = e.currentTarget || e.target;
        target.classList.remove('drag-over');
    };
    
    window.handleDrop = function(e) {
        e = e || window.event;
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        if (e.preventDefault) {
            e.preventDefault();
        }
        var target = e.currentTarget || e.target;
        target.classList.remove('drag-over');
        
        if (window.draggedElement && !target.dataset.matchedOptionId) {
            const optionId = window.draggedElement.dataset.optionId;
            const targetContent = target.dataset.content;
            const leftContent = window.draggedElement.querySelector('strong')?.textContent.trim() || window.draggedElement.textContent.trim();
            
            const pairsInput = document.getElementById('matching-pairs-input');
            const pairs = JSON.parse(pairsInput.value || '{}');
            pairs[optionId] = targetContent;
            pairsInput.value = JSON.stringify(pairs);
            
            console.log('Match created', { optionId, targetContent, pairs });
            
            // تحديث العرض
            target.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${leftContent}</strong>
                    <i class="bi bi-arrow-left-right mx-2 text-muted"></i>
                    <span>${targetContent}</span>
                </div>
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-match" onclick="removeMatch('${optionId}')">
                    <i class="bi bi-x"></i>
                </button>
            </div>`;
            target.dataset.matchedOptionId = optionId;
            target.classList.add('border-success');
            
            // إخفاء العنصر اليسرى المطابق
            window.draggedElement.classList.add('d-none');
            window.draggedElement.style.display = 'none';
            window.draggedElement = null;
        }
        
        return false;
    };
    
    // دالة لإزالة المطابقة
    window.removeMatch = function(optionId) {
        const pairsInput = document.getElementById('matching-pairs-input');
        const pairs = JSON.parse(pairsInput.value || '{}');
        delete pairs[optionId];
        pairsInput.value = JSON.stringify(pairs);
        
        // إظهار العنصر اليسرى مرة أخرى
        const leftItem = document.querySelector(`#left-items .matching-draggable[data-option-id="${optionId}"]`);
        if (leftItem) {
            leftItem.classList.remove('d-none');
            leftItem.style.display = '';
            leftItem.draggable = true;
            leftItem.setAttribute('draggable', 'true');
            leftItem.setAttribute('ondragstart', 'return handleDragStart(event)');
        }
        
        // إعادة تعيين العنصر اليمنى
        const rightItem = document.querySelector(`#right-items .matching-target[data-matched-option-id="${optionId}"]`);
        if (rightItem) {
            const originalContent = rightItem.dataset.content;
            rightItem.innerHTML = originalContent;
            rightItem.classList.remove('border-success');
            rightItem.removeAttribute('data-matched-option-id');
            rightItem.setAttribute('ondragover', 'return handleDragOver(event)');
            rightItem.setAttribute('ondragenter', 'return handleDragEnter(event)');
            rightItem.setAttribute('ondragleave', 'handleDragLeave(event)');
            rightItem.setAttribute('ondrop', 'return handleDrop(event)');
        }
    };
    
    // التأكد من أن جميع العناصر قابلة للسحب
    (function() {
        function initMatching() {
            const draggables = document.querySelectorAll('.matching-draggable:not(.d-none)');
            console.log('Initializing draggables:', draggables.length);
            draggables.forEach(item => {
                item.draggable = true;
                item.setAttribute('draggable', 'true');
                item.setAttribute('ondragstart', 'return handleDragStart(event)');
                item.style.pointerEvents = 'auto';
                item.style.webkitUserDrag = 'element';
                item.style.cursor = 'move';
                console.log('Initialized:', item.dataset.optionId, 'draggable:', item.draggable);
            });
            
            const targets = document.querySelectorAll('.matching-target:not([data-matched-option-id])');
            targets.forEach(target => {
                target.setAttribute('ondragover', 'return handleDragOver(event)');
                target.setAttribute('ondragenter', 'return handleDragEnter(event)');
                target.setAttribute('ondragleave', 'handleDragLeave(event)');
                target.setAttribute('ondrop', 'return handleDrop(event)');
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initMatching, 300);
            });
        } else {
            setTimeout(initMatching, 300);
        }
    })();
</script>