/**
 * AutoSaveAnswer - حفظ تلقائي دوري للإجابة الحالية
 */
class AutoSaveAnswer {
    constructor(options = {}) {
        this.formId = options.formId || null;
        this.saveUrl = options.saveUrl || null;
        this.interval = parseInt(options.interval, 10) || 30000;
        this.intervalId = null;
        this.isSaving = false;
    }

    save() {
        if (!this.saveUrl || this.isSaving) {
            return Promise.resolve(null);
        }

        const form = this.formId ? document.getElementById(this.formId) : null;
        if (!form) {
            return Promise.resolve(null);
        }

        this.isSaving = true;
        const formData = new FormData(form);
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            formData.set('_token', token.getAttribute('content'));
        }

        return fetch(this.saveUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then((res) => (res.ok ? res.json() : null))
            .catch(() => null)
            .finally(() => {
                this.isSaving = false;
            });
    }

    start() {
        this.stop();
        if (this.saveUrl) {
            this.intervalId = window.setInterval(() => {
                this.save();
            }, this.interval);
        }
        return this;
    }

    stop() {
        if (this.intervalId !== null) {
            window.clearInterval(this.intervalId);
            this.intervalId = null;
        }
        return this;
    }
}

if (typeof window !== 'undefined') {
    window.AutoSaveAnswer = AutoSaveAnswer;
}
