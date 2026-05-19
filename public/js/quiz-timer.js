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
        this.tickIntervalId = null;
        this.syncIntervalId = null;
        this.updateIntervalMs = 15000; // تحديث من الخادم كل 15 ثانية
        this.lastUpdateAt = 0;
        this.warningFired = {};
        this.timeoutFired = false;
    }

    formatSeconds(totalSeconds) {
        const seconds = Math.max(0, parseInt(totalSeconds, 10) || 0);
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    getDisplayElement() {
        return document.getElementById('timer-display');
    }

    getCardElement() {
        return document.getElementById('timer-card');
    }

    start() {
        this.stop();

        const tick = () => {
            const displayEl = this.getDisplayElement();
            const cardEl = this.getCardElement();

            if (this.remainingSeconds <= 0) {
                if (displayEl) {
                    displayEl.textContent = '0:00';
                }
                this.stop();
                if (!this.timeoutFired && this.onTimeout) {
                    this.timeoutFired = true;
                    this.onTimeout();
                }
                return;
            }

            if (displayEl) {
                displayEl.textContent = this.formatSeconds(this.remainingSeconds);
            }

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

            this.remainingSeconds -= 1;
        };

        tick();
        this.tickIntervalId = window.setInterval(tick, 1000);

        if (this.updateUrl) {
            const syncFromServer = () => {
                const now = Date.now();
                if (now - this.lastUpdateAt < this.updateIntervalMs) {
                    return;
                }
                this.lastUpdateAt = now;

                const token = document.querySelector('meta[name="csrf-token"]');
                const csrf = token ? token.getAttribute('content') : '';

                fetch(this.updateUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then((res) => (res.ok ? res.json() : null))
                    .then((data) => {
                        if (data && typeof data.remaining === 'number' && data.remaining >= 0) {
                            this.remainingSeconds = data.remaining;
                            const displayEl = this.getDisplayElement();
                            if (displayEl) {
                                displayEl.textContent = this.formatSeconds(this.remainingSeconds);
                            }
                        }
                    })
                    .catch(() => {});
            };

            syncFromServer();
            this.syncIntervalId = window.setInterval(syncFromServer, this.updateIntervalMs);
        }
    }

    stop() {
        if (this.tickIntervalId !== null) {
            window.clearInterval(this.tickIntervalId);
            this.tickIntervalId = null;
        }
        if (this.syncIntervalId !== null) {
            window.clearInterval(this.syncIntervalId);
            this.syncIntervalId = null;
        }
    }
}

if (typeof window !== 'undefined') {
    window.QuizTimer = QuizTimer;
}
