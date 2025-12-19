/**
 * Quiz Timer Component
 * عداد تنازلي للاختبارات والأسئلة
 */
class QuizTimer {
    constructor(options) {
        this.remainingTime = options.remainingTime || 0; // بالثواني
        this.updateUrl = options.updateUrl || null;
        this.onTimeout = options.onTimeout || function() {};
        this.onWarning = options.onWarning || function() {};
        this.interval = null;
        this.displayElement = document.getElementById('timer-display');
        this.cardElement = document.getElementById('timer-card');
        
        // تحذيرات
        this.warningThreshold = options.warningThreshold || 300; // 5 دقائق
        this.dangerThreshold = options.dangerThreshold || 60; // دقيقة واحدة
    }

    start() {
        if (this.interval) {
            this.stop();
        }

        this.updateDisplay();
        
        this.interval = setInterval(() => {
            this.remainingTime--;
            this.updateDisplay();
            
            // تحذيرات
            if (this.remainingTime <= this.dangerThreshold) {
                this.cardElement?.classList.add('danger');
                this.cardElement?.classList.remove('warning');
                if (this.onWarning) {
                    this.onWarning(this.remainingTime);
                }
            } else if (this.remainingTime <= this.warningThreshold) {
                this.cardElement?.classList.add('warning');
                if (this.onWarning) {
                    this.onWarning(this.remainingTime);
                }
            }
            
            // انتهاء الوقت
            if (this.remainingTime <= 0) {
                this.stop();
                if (this.onTimeout) {
                    this.onTimeout();
                }
            }
            
            // تحديث من السيرفر كل 30 ثانية
            if (this.updateUrl && this.remainingTime % 30 === 0) {
                this.syncWithServer();
            }
        }, 1000);
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }

    updateDisplay() {
        if (!this.displayElement) return;
        
        const hours = Math.floor(this.remainingTime / 3600);
        const minutes = Math.floor((this.remainingTime % 3600) / 60);
        const seconds = this.remainingTime % 60;
        
        if (hours > 0) {
            this.displayElement.textContent = 
                `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        } else {
            this.displayElement.textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
    }

    async syncWithServer() {
        if (!this.updateUrl) return;
        
        try {
            const response = await fetch(this.updateUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.remaining !== undefined) {
                this.remainingTime = data.remaining;
                this.updateDisplay();
            }
            
            if (data.timeout) {
                this.stop();
                if (this.onTimeout) {
                    this.onTimeout();
                }
            }
        } catch (error) {
            console.error('Error syncing timer:', error);
        }
    }

    getRemainingTime() {
        return this.remainingTime;
    }

    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
}

