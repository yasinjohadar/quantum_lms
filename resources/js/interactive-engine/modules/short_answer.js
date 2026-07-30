import { escapeHtml } from './_helpers.js';

/** Independent module: short text answer with accepted variants. */
export const shortAnswerModule = {
    type: 'short_answer',
    _el: null,
    _points: 1,
    _accepted: [],
    async beforeMount() {},
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        const p = ctx.question.payload || {};
        this._accepted = (p.acceptedAnswers || [p.correct || ''])
            .map((s) => String(s).trim().toLowerCase())
            .filter(Boolean);
        const placeholder = p.placeholder || 'اكتب إجابتك هنا';

        el.innerHTML = `<div class="ile-short">
            <p class="ile-hint-line">اكتب إجابة قصيرة</p>
            <input type="text" class="ile-short__input" id="ile-short-input" placeholder="${escapeHtml(placeholder)}" autocomplete="off">
        </div>`;
        el.querySelector('#ile-short-input')?.addEventListener('input', () => {
            ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
        });
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        return this._el?.querySelector('#ile-short-input')?.value?.trim() || '';
    },
    grade(answer) {
        const got = String(answer || '').trim().toLowerCase();
        const ok = this._accepted.includes(got);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
