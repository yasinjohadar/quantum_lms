import { optionButtons, bindSpeakers, revealChoice, lockChoice } from './_helpers.js';

export const singleChoiceModule = {
    type: 'single_choice',
    _el: null,
    _points: 1,
    _correctId: null,
    _name: '',
    async beforeMount() {},
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correctId = ctx.question.payload?.correctId;
        this._name = `sc_${ctx.question.id}`;
        const options = ctx.question.payload?.options || [];
        el.innerHTML = `<div class="ile-options">${optionButtons(options, { multiple: false, name: this._name })}</div>`;
        el.querySelectorAll('input').forEach((input) => {
            input.addEventListener('change', () => {
                el.querySelectorAll('.ile-option').forEach((lab) => lab.classList.remove('is-selected'));
                input.closest('.ile-option')?.classList.add('is-selected');
                ctx.playSfx?.('pop');
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
    reveal({ answer } = {}) {
        revealChoice(this._el, {
            correctIds: [this._correctId],
            chosenIds: [answer],
        });
        lockChoice(this._el);
    },
    getAnswer() {
        const checked = this._el?.querySelector('input:checked');
        return checked ? checked.value : null;
    },
    grade(answer) {
        const ok = answer != null && String(answer) === String(this._correctId);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
