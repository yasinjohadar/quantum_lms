/**
 * QuizTimer - عدّ تنازلي أو تصاعدي للاختبارات مع دعم التحديث من الخادم
 * يحدّث #timer-display، ويدعم updateUrl، onTimeout، onWarning
 */
class QuizTimer {
    /**
     * @param {Object} options
     * @param {'countdown'|'elapsed'} [options.mode] - countdown (افتراضي) أو elapsed للمدة المفتوحة
     * @param {number} [options.remainingTime] - الوقت المتبقي بالثواني (countdown)
     * @param {number} [options.elapsedSeconds] - الوقت المنقضي بالثواني (elapsed)
     * @param {string|null} [options.updateUrl] - رابط AJAX لتحديث الوقت من الخادم
     * @param {Function} [options.onTimeout] - يُستدعى عند انتهاء الوقت (countdown فقط)
     * @param {Function} [options.onWarning] - يُستدعى عند وصول الوقت لمرحلة تحذير (countdown)
     */
    constructor(options = {}) {
        this.mode = options.mode === 'elapsed' ? 'elapsed' : 'countdown';
        this.updateUrl = options.updateUrl || null;
        this.onTimeout = typeof options.onTimeout === 'function' ? options.onTimeout : null;
        this.onWarning = typeof options.onWarning === 'function' ? options.onWarning : null;
        this.tickIntervalId = null;
        this.syncIntervalId = null;
        this.updateIntervalMs = 15000;
        this.lastUpdateAt = 0;
        this.warningFired = {};
        this.timeoutFired = false;
        this.started = false;

        if (this.mode === 'elapsed') {
            this.elapsedSeconds = Math.max(0, parseInt(options.elapsedSeconds, 10) || 0);
            this.remainingSeconds = 0;
        } else {
            const parsed = parseInt(options.remainingTime, 10);
            this.remainingSeconds = Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
            this.elapsedSeconds = 0;
        }
    }

    formatSeconds(totalSeconds) {
        const seconds = Math.max(0, parseInt(totalSeconds, 10) || 0);
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;

        if (h > 0) {
            return h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        }

        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    getDisplayElement() {
        return document.getElementById('timer-display');
    }

    getCardElement() {
        return document.getElementById('timer-card');
    }

    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    updateElapsedDisplay() {
        const displayEl = this.getDisplayElement();
        if (!displayEl) {
            return;
        }
        displayEl.textContent = this.formatSeconds(this.elapsedSeconds);
        displayEl.setAttribute('data-elapsed-seconds', String(this.elapsedSeconds));
    }

    handleSyncResponse(data) {
        if (!data) {
            return false;
        }

        if (data.timeout && data.redirect_url) {
            this.stop();
            window.location.href = data.redirect_url;
            return true;
        }

        if (data.timeout) {
            this.stop();
            if (this.mode === 'countdown') {
                this.fireTimeout();
            }
            return true;
        }

        if (data.unlimited) {
            this.mode = 'elapsed';
            if (typeof data.elapsed === 'number' && data.elapsed >= 0) {
                this.elapsedSeconds = data.elapsed;
            }
            const displayEl = this.getDisplayElement();
            if (displayEl) {
                displayEl.textContent = data.formatted_elapsed || this.formatSeconds(this.elapsedSeconds);
                displayEl.setAttribute('data-elapsed-seconds', String(this.elapsedSeconds));
                displayEl.setAttribute('data-timer-mode', 'elapsed');
            }
            return false;
        }

        if (typeof data.remaining === 'number' && data.remaining >= 0) {
            this.mode = 'countdown';
            this.remainingSeconds = data.remaining;
            const displayEl = this.getDisplayElement();
            if (displayEl) {
                displayEl.textContent = data.formatted || this.formatSeconds(this.remainingSeconds);
                displayEl.setAttribute('data-remaining-seconds', String(this.remainingSeconds));
                displayEl.setAttribute('data-timer-mode', 'countdown');
            }
        }

        return false;
    }

    syncFromServer(options = {}) {
        const { immediate = false, onComplete } = options;

        if (!this.updateUrl) {
            if (typeof onComplete === 'function') {
                onComplete(false);
            }
            return Promise.resolve(false);
        }

        const now = Date.now();
        if (!immediate && now - this.lastUpdateAt < this.updateIntervalMs) {
            if (typeof onComplete === 'function') {
                onComplete(false);
            }
            return Promise.resolve(false);
        }

        this.lastUpdateAt = now;

        return fetch(this.updateUrl, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then((res) => (res.ok ? res.json() : null))
            .then((data) => {
                const handled = this.handleSyncResponse(data);
                if (typeof onComplete === 'function') {
                    onComplete(handled);
                }
                return handled;
            })
            .catch(() => {
                if (typeof onComplete === 'function') {
                    onComplete(false);
                }
                return false;
            });
    }

    fireTimeout() {
        if (this.timeoutFired) {
            return;
        }
        this.timeoutFired = true;
        if (this.onTimeout) {
            this.onTimeout();
        }
    }

    startElapsedTicking() {
        const tick = () => {
            this.updateElapsedDisplay();
            this.elapsedSeconds += 1;
        };

        tick();
        this.tickIntervalId = window.setInterval(tick, 1000);
    }

    startCountdownTicking() {
        const tick = () => {
            const displayEl = this.getDisplayElement();
            const cardEl = this.getCardElement();

            if (this.remainingSeconds <= 0) {
                if (displayEl) {
                    displayEl.textContent = '0:00';
                }
                this.stopTickOnly();
                this.syncFromServer({
                    immediate: true,
                    onComplete: (handled) => {
                        if (!handled && !this.timeoutFired) {
                            this.fireTimeout();
                        }
                    },
                });
                return;
            }

            if (displayEl) {
                displayEl.textContent = this.formatSeconds(this.remainingSeconds);
                displayEl.setAttribute('data-remaining-seconds', String(this.remainingSeconds));
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
    }

    stopTickOnly() {
        if (this.tickIntervalId !== null) {
            window.clearInterval(this.tickIntervalId);
            this.tickIntervalId = null;
        }
    }

    start() {
        if (this.started) {
            return;
        }
        this.started = true;
        this.stop();

        if (this.mode === 'elapsed') {
            this.updateElapsedDisplay();
            this.startElapsedTicking();

            if (this.updateUrl) {
                this.syncFromServer({ immediate: true });
                this.syncIntervalId = window.setInterval(() => {
                    this.syncFromServer();
                }, this.updateIntervalMs);
            }
            return;
        }

        const begin = () => {
            this.startCountdownTicking();

            if (this.updateUrl) {
                this.syncFromServer({ immediate: true });
                this.syncIntervalId = window.setInterval(() => {
                    this.syncFromServer();
                }, this.updateIntervalMs);
            }
        };

        if (this.remainingSeconds <= 0 && this.updateUrl) {
            this.syncFromServer({
                immediate: true,
                onComplete: (handled) => {
                    if (!handled && this.remainingSeconds <= 0) {
                        this.fireTimeout();
                        return;
                    }
                    if (!handled) {
                        begin();
                    }
                },
            });
            return;
        }

        if (this.remainingSeconds <= 0) {
            this.fireTimeout();
            return;
        }

        begin();
    }

    stop() {
        this.stopTickOnly();
        if (this.syncIntervalId !== null) {
            window.clearInterval(this.syncIntervalId);
            this.syncIntervalId = null;
        }
    }
}

if (typeof window !== 'undefined') {
    window.QuizTimer = QuizTimer;
}
