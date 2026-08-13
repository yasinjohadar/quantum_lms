import { optionButtons, bindSpeakers, revealChoice, lockChoice } from './_helpers.js';

export const multipleChoiceModule = {
    type: 'multiple_choice',
    _el: null,
    _points: 1,
    _correctIds: [],
    async beforeMount() {},
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correctIds = (ctx.question.payload?.correctIds || []).map(String);
        const options = ctx.question.payload?.options || [];
        el.innerHTML = `<div class="ile-options">${optionButtons(options, { multiple: true, name: `mc_${ctx.question.id}` })}</div>`;
        el.querySelectorAll('input').forEach((input) => {
            input.addEventListener('change', () => {
                el.querySelectorAll('.ile-option').forEach((lab) => {
                    const inp = lab.querySelector('input');
                    lab.classList.toggle('is-selected', Boolean(inp?.checked));
                });
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
            correctIds: this._correctIds,
            chosenIds: Array.isArray(answer) ? answer : [],
        });
        lockChoice(this._el);
    },
    getAnswer() {
        return [...(this._el?.querySelectorAll('input:checked') || [])].map((i) => i.value);
    },
    grade(answer) {
        const selected = (Array.isArray(answer) ? answer : []).map(String).sort();
        const expected = [...this._correctIds].sort();
        const ok =
            selected.length === expected.length && selected.every((v, i) => v === expected[i]);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
