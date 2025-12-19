/**
 * Question Types Handlers
 * معالجة خاصة لكل نوع سؤال
 */

class QuestionTypesHandler {
    constructor() {
        this.init();
    }

    init() {
        // Matching
        this.initMatching();
        
        // Ordering
        this.initOrdering();
        
        // Drag & Drop
        this.initDragDrop();
        
        // Fill Blanks
        this.initFillBlanks();
    }

    /**
     * Matching Questions
     */
    initMatching() {
        const leftItems = document.querySelectorAll('#left-items .list-group-item[data-option-id]');
        const rightItems = document.querySelectorAll('.matching-target');
        const pairsInput = document.getElementById('matching-pairs-input');
        
        if (!leftItems.length || !rightItems.length || !pairsInput) return;
        
        let pairs = JSON.parse(pairsInput.value || '{}');
        let draggedElement = null;
        
        // جعل العناصر اليسرى قابلة للسحب
        leftItems.forEach(item => {
            item.draggable = true;
            item.addEventListener('dragstart', (e) => {
                draggedElement = item;
                e.dataTransfer.effectAllowed = 'move';
            });
        });
        
        // جعل العناصر اليمنى مناطق إفلات
        rightItems.forEach(target => {
            target.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });
            
            target.addEventListener('drop', (e) => {
                e.preventDefault();
                if (draggedElement) {
                    const optionId = draggedElement.dataset.optionId;
                    const targetContent = target.dataset.content;
                    
                    pairs[optionId] = targetContent;
                    pairsInput.value = JSON.stringify(pairs);
                    
                    // تحديث العرض
                    target.innerHTML = `<strong>${draggedElement.textContent.trim()}</strong> → ${targetContent}`;
                    draggedElement = null;
                }
            });
        });
    }

    /**
     * Ordering Questions
     */
    initOrdering() {
        const orderingList = document.getElementById('ordering-list');
        const orderingInput = document.getElementById('ordering-input');
        
        if (!orderingList || !orderingInput) return;
        
        // استخدام SortableJS إذا كان متاحاً، أو تنفيذ بسيط
        if (typeof Sortable !== 'undefined') {
            new Sortable(orderingList, {
                animation: 150,
                handle: '.bi-grip-vertical',
                onEnd: function(evt) {
                    updateOrdering();
                }
            });
        } else {
            // تنفيذ بسيط للسحب والإفلات
            let draggedItem = null;
            
            Array.from(orderingList.children).forEach(item => {
                item.draggable = true;
                
                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    e.dataTransfer.effectAllowed = 'move';
                    item.style.opacity = '0.5';
                });
                
                item.addEventListener('dragend', (e) => {
                    item.style.opacity = '1';
                    draggedItem = null;
                });
                
                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    
                    const afterElement = getDragAfterElement(orderingList, e.clientY);
                    if (afterElement == null) {
                        orderingList.appendChild(draggedItem);
                    } else {
                        orderingList.insertBefore(draggedItem, afterElement);
                    }
                    updateOrdering();
                });
            });
        }
        
        function updateOrdering() {
            const items = Array.from(orderingList.children);
            const order = items.map(item => item.dataset.optionId);
            orderingInput.value = JSON.stringify(order);
            
            // تحديث الأرقام
            items.forEach((item, index) => {
                const badge = item.querySelector('.order-badge');
                if (badge) {
                    badge.textContent = index + 1;
                }
            });
        }
        
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.list-group-item:not(.dragging)')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    }

    /**
     * Drag & Drop Questions
     */
    initDragDrop() {
        const dragItems = document.querySelectorAll('.draggable-item');
        const dropZones = document.querySelectorAll('.drop-zone');
        const answerInput = document.getElementById('drag-drop-answer');
        
        if (!dragItems.length || !dropZones.length || !answerInput) return;
        
        let answer = JSON.parse(answerInput.value || '{}');
        let draggedItem = null;
        
        dragItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                draggedItem = item;
                e.dataTransfer.effectAllowed = 'move';
            });
        });
        
        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });
            
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                if (draggedItem) {
                    const zoneId = zone.dataset.zoneId;
                    const optionId = draggedItem.dataset.optionId;
                    
                    answer[zoneId] = optionId;
                    answerInput.value = JSON.stringify(answer);
                    
                    const droppedDiv = zone.querySelector('.dropped-item');
                    if (droppedDiv) {
                        droppedDiv.dataset.optionId = optionId;
                        droppedDiv.textContent = draggedItem.textContent.trim();
                    }
                    
                    draggedItem.remove();
                    draggedItem = null;
                }
            });
        });
    }

    /**
     * Fill Blanks Questions
     */
    initFillBlanks() {
        const inputs = document.querySelectorAll('input[name="fill_blanks_answers[]"]');
        
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                // التحقق من صحة الإدخال
                validateFillBlanks();
            });
        });
    }
    
    validateFillBlanks() {
        const inputs = document.querySelectorAll('input[name="fill_blanks_answers[]"]');
        let allFilled = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                allFilled = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        return allFilled;
    }
}

// تهيئة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    new QuestionTypesHandler();
});

