import { bindSpeakers, speakerButtonHtml } from './_helpers.js';

export const trueFalseModule = {
    type: 'true_false',
    _el: null,
    _points: 1,
    _correct: true,
    async beforeMount() {},
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correct = Boolean(ctx.question.payload?.correct);
        el.innerHTML = `
            <div class="ile-tf">
                <button type="button" class="ile-tf__btn" data-val="true">
                    <span class="ile-media__icon" aria-hidden="true">✅</span>
                    <span>صح</span>
                    ${speakerButtonHtml({ label: 'صح' })}
                </button>
                <button type="button" class="ile-tf__btn" data-val="false">
                    <span class="ile-media__icon" aria-hidden="true">❌</span>
                    <span>خطأ</span>
                    ${speakerButtonHtml({ label: 'خطأ' })}
                </button>
            </div>`;
        el.querySelectorAll('.ile-tf__btn').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                if (e.target.closest('.ile-speak')) return;
                el.querySelectorAll('.ile-tf__btn').forEach((b) => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
            });
        });
        bindSpeakers(el, ctx.playOptionAudio);
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        const active = this._el?.querySelector('.is-active');
        if (!active) return null;
        return active.getAttribute('data-val') === 'true';
    },
    grade(answer) {
        const ok = answer === this._correct;
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
