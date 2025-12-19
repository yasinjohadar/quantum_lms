/**
 * Auto-save Answer Component
 * حفظ تلقائي للإجابات
 */
class AutoSaveAnswer {
    constructor(options) {
        this.formId = options.formId || 'answer-form';
        this.saveUrl = options.saveUrl || null;
        this.interval = options.interval || 30000; // 30 ثانية افتراضياً
        this.saveInterval = null;
        this.formElement = document.getElementById(this.formId);
        this.lastSavedData = null;
        
        // حفظ عند مغادرة الصفحة
        this.setupBeforeUnload();
    }

    start() {
        if (this.saveInterval) {
            this.stop();
        }

        // حفظ فوري عند التغيير
        this.setupChangeListener();
        
        // حفظ دوري
        this.saveInterval = setInterval(() => {
            this.save();
        }, this.interval);
    }

    stop() {
        if (this.saveInterval) {
            clearInterval(this.saveInterval);
            this.saveInterval = null;
        }
    }

    setupChangeListener() {
        if (!this.formElement) return;
        
        const inputs = this.formElement.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                this.save();
            });
            
            // للحقول النصية، حفظ بعد توقف الكتابة
            if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                let timeout;
                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.save();
                    }, 2000); // 2 ثانية بعد توقف الكتابة
                });
            }
        });
    }

    setupBeforeUnload() {
        window.addEventListener('beforeunload', (e) => {
            // حفظ قبل مغادرة الصفحة
            if (this.hasUnsavedChanges()) {
                this.save(true); // حفظ متزامن
            }
        });
        
        // حفظ عند تغيير التبويب
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.hasUnsavedChanges()) {
                this.save();
            }
        });
    }

    hasUnsavedChanges() {
        if (!this.formElement) return false;
        
        const formData = new FormData(this.formElement);
        const currentData = this.serializeFormData(formData);
        
        return currentData !== this.lastSavedData;
    }

    serializeFormData(formData) {
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        return JSON.stringify(data);
    }

    async save(sync = false) {
        if (!this.formElement || !this.saveUrl) return;
        
        const formData = new FormData(this.formElement);
        const serialized = this.serializeFormData(formData);
        
        // تجنب حفظ نفس البيانات
        if (serialized === this.lastSavedData) {
            return;
        }
        
        try {
            const response = await fetch(this.saveUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.lastSavedData = serialized;
                this.showSaveIndicator(true);
            } else {
                console.error('Save failed:', data.message);
                this.showSaveIndicator(false);
            }
        } catch (error) {
            console.error('Error saving answer:', error);
            this.showSaveIndicator(false);
        }
    }

    showSaveIndicator(success = true) {
        // إزالة أي مؤشرات سابقة
        const existing = document.getElementById('auto-save-indicator');
        if (existing) {
            existing.remove();
        }
        
        // إنشاء مؤشر جديد
        const indicator = document.createElement('div');
        indicator.id = 'auto-save-indicator';
        indicator.className = `alert alert-${success ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        indicator.style.cssText = 'top: 20px; left: 20px; z-index: 9999; min-width: 200px;';
        indicator.innerHTML = `
            <i class="bi bi-${success ? 'check-circle' : 'x-circle'} me-2"></i>
            ${success ? 'تم حفظ الإجابة' : 'فشل الحفظ'}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(indicator);
        
        // إزالة تلقائية بعد 3 ثوان
        setTimeout(() => {
            indicator.remove();
        }, 3000);
    }
}

