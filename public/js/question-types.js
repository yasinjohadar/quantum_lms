/**
 * Question Types Handlers
 * معالجة خاصة لكل نوع سؤال
 */

class QuestionTypesHandler {
    constructor() {
        this.draggedElement = null;
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
        const leftItemsContainer = document.getElementById('left-items');
        const rightItemsContainer = document.getElementById('right-items');
        const pairsInput = document.getElementById('matching-pairs-input');
        
        if (!leftItemsContainer || !rightItemsContainer || !pairsInput) {
            console.log('Matching containers not found');
            return;
        }
        
        let pairs = JSON.parse(pairsInput.value || '{}');
        
        // جعل العناصر اليسرى قابلة للسحب - بدون cloneNode
        const leftItems = leftItemsContainer.querySelectorAll('.matching-draggable:not(.d-none)');
        
        leftItems.forEach((item) => {
            // التأكد من أن العنصر قابل للسحب
            item.draggable = true;
            item.setAttribute('draggable', 'true');
            item.style.cursor = 'move';
            item.style.userSelect = 'none';
            item.style.pointerEvents = 'auto';
            item.style.webkitUserDrag = 'element';
            item.style.touchAction = 'none';
            
            // إزالة event listeners السابقة إذا كانت موجودة
            const newDragStart = (e) => {
                this.draggedElement = item;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.optionId);
                e.dataTransfer.setData('application/json', JSON.stringify({
                    optionId: item.dataset.optionId,
                    content: item.textContent.trim()
                }));
                item.classList.add('dragging');
                item.style.opacity = '0.5';
                console.log('Drag started', item.dataset.optionId);
            };
            
            const newDragEnd = (e) => {
                item.classList.remove('dragging');
                item.style.opacity = '1';
                if (this.draggedElement === item) {
                    this.draggedElement = null;
                }
            };
            
            // إزالة listeners السابقة
            item.removeEventListener('dragstart', item._dragStartHandler);
            item.removeEventListener('dragend', item._dragEndHandler);
            
            // إضافة listeners جديدة
            item._dragStartHandler = newDragStart;
            item._dragEndHandler = newDragEnd;
            item.addEventListener('dragstart', newDragStart, false);
            item.addEventListener('dragend', newDragEnd, false);
        });
        
        // جعل العناصر اليمنى مناطق إفلات
        const rightItems = rightItemsContainer.querySelectorAll('.matching-target:not([data-matched-option-id])');
        
        rightItems.forEach((target) => {
            target.style.pointerEvents = 'auto';
            
            const newDragOver = (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'move';
                target.classList.add('drag-over');
            };
            
            const newDragEnter = (e) => {
                e.preventDefault();
                e.stopPropagation();
                target.classList.add('drag-over');
            };
            
            const newDragLeave = (e) => {
                e.preventDefault();
                e.stopPropagation();
                target.classList.remove('drag-over');
            };
            
            const newDrop = (e) => {
                e.preventDefault();
                e.stopPropagation();
                target.classList.remove('drag-over');
                
                if (this.draggedElement) {
                    const optionId = this.draggedElement.dataset.optionId;
                    const targetContent = target.dataset.content;
                    const leftContent = this.draggedElement.querySelector('strong')?.textContent.trim() || this.draggedElement.textContent.trim();
                    
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
                    this.draggedElement.classList.add('d-none');
                    this.draggedElement.style.display = 'none';
                    this.draggedElement = null;
                    
                    // إعادة تهيئة
                    setTimeout(() => {
                        if (typeof QuestionTypesHandler !== 'undefined') {
                            new QuestionTypesHandler();
                        }
                    }, 100);
                }
            };
            
            // إزالة listeners السابقة
            target.removeEventListener('dragover', target._dragOverHandler);
            target.removeEventListener('dragenter', target._dragEnterHandler);
            target.removeEventListener('dragleave', target._dragLeaveHandler);
            target.removeEventListener('drop', target._dropHandler);
            
            // إضافة listeners جديدة
            target._dragOverHandler = newDragOver;
            target._dragEnterHandler = newDragEnter;
            target._dragLeaveHandler = newDragLeave;
            target._dropHandler = newDrop;
            
            target.addEventListener('dragover', newDragOver, false);
            target.addEventListener('dragenter', newDragEnter, false);
            target.addEventListener('dragleave', newDragLeave, false);
            target.addEventListener('drop', newDrop, false);
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
            item.draggable = true;
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
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            new QuestionTypesHandler();
        }, 200);
    });
} else {
    // الصفحة محملة بالفعل
    setTimeout(() => {
        new QuestionTypesHandler();
    }, 200);
}

// تهيئة مرة أخرى بعد تحميل المحتوى الديناميكي
document.addEventListener('questionContentLoaded', () => {
    setTimeout(() => {
        new QuestionTypesHandler();
    }, 200);
});
