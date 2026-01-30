/**
 * QuizTimer - عدّ تنازلي للاختبارات مع دعم التحديث من الخادم
 * يحدّث #timer-display، ويدعم updateUrl، onTimeout، onWarning
 */
class QuizTimer {
    /**
     * @param {Object} options
     * @param {number} options.remainingTime - الوقت المتبقي بالثواني
     * @param {string|null} [options.updateUrl] - رابط AJAX لتحديث الوقت من الخادم
     * @param {Function} [options.onTimeout] - يُستدعى عند انتهاء الوقت
     * @param {Function} [options.onWarning] - يُستدعى عند وصول الوقت لمرحلة تحذير (seconds)
     */
    constructor(options = {}) {
        this.remainingSeconds = Math.max(0, parseInt(options.remainingTime, 10) || 0);
        this.updateUrl = options.updateUrl || null;
        this.onTimeout = typeof options.onTimeout === 'function' ? options.onTimeout : null;
        this.onWarning = typeof options.onWarning === 'function' ? options.onWarning : null;
        this.intervalId = null;
        this.updateIntervalMs = 15000; // تحديث من الخادم كل 15 ثانية
        this.lastUpdateAt = 0;
        this.warningFired = {};
    }

    start() {
        const displayEl = document.getElementById('timer-display');
        const cardEl = document.getElementById('timer-card');

        const updateDisplay = () => {
            if (this.remainingSeconds <= 0) {
                this.stop();
                if (this.onTimeout) this.onTimeout();
                if (displayEl) displayEl.textContent = '0:00';
                return;
            }

            const m = Math.floor(this.remainingSeconds / 60);
            const s = this.remainingSeconds % 60;
            if (displayEl) displayEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;

            // تحذير عند 5 دقائق و 1 دقيقة
            if (this.onWarning && cardEl) {
                if (this.remainingSeconds <= 60 && !this.warningFired[60]) {
                    this.warningFired[60] = true;
                    cardEl.classList.add('danger');
                    cardEl.classList.remove('warning');
                    this.onWarning(this.remainingSeconds);
                } else if (this.remainingSeconds <= 300 && this.remainingSeconds > 60 && !this.warningFired[300]) {
                    this.warningFired[300] = true;
                    cardEl.classList.add('warning');
                    cardEl.classList.remove('danger');
                    this.onWarning(this.remainingSeconds);
                }
            }

            this.remainingSeconds--;
        };

        updateDisplay();
        this.intervalId = setInterval(updateDisplay, 1000);

        // تحديث من الخادم دورياً
        if (this.updateUrl) {
            const syncFromServer = () => {
                const now = Date.now();
                if (now - this.lastUpdateAt < this.updateIntervalMs) return;
                this.lastUpdateAt = now;

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(this.updateUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.ok ? res.json() : null)
                    .then(data => {
                        if (data && typeof data.remaining === 'number' && data.remaining >= 0) {
                            this.remainingSeconds = data.remaining;
                        }
                    })
                    .catch(() => {});
            };
            setInterval(syncFromServer, this.updateIntervalMs);
        }
    }

    stop() {
        if (this.intervalId !== null) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
}
