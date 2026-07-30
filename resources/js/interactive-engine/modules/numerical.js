import { escapeHtml } from './_helpers.js';

/** Independent module: numerical answer. */
export const numericalModule = {
    type: 'numerical',
    _el: null,
    _points: 1,
    _correct: 0,
    _tolerance: 0,
    async beforeMount() {},
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correct = Number(ctx.question.payload?.correct ?? 0);
        this._tolerance = Number(ctx.question.payload?.tolerance ?? 0);
        const unit = ctx.question.payload?.unit || '';
        const hint = ctx.question.payload?.hint || 'اكتب الرقم الصحيح';

        el.innerHTML = `<div class="ile-num">
            <p class="ile-hint-line">${escapeHtml(hint)}</p>
            <div class="ile-num__row">
                <input type="number" class="ile-num__input" id="ile-num-input" step="any" inputmode="decimal" placeholder="0">
                ${unit ? `<span class="ile-num__unit">${escapeHtml(unit)}</span>` : ''}
            </div>
            <div class="ile-num__pad">
                ${[1, 2, 3, 4, 5, 6, 7, 8, 9, 0]
                    .map((n) => `<button type="button" class="ile-num__key" data-n="${n}">${n}</button>`)
                    .join('')}
                <button type="button" class="ile-num__key" data-n="back"><i class="bi bi-backspace-fill" aria-hidden="true"></i></button>
                <button type="button" class="ile-num__key" data-n="clear">مسح</button>
            </div>
        </div>`;

        const input = el.querySelector('#ile-num-input');
        const emit = () => ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
        input?.addEventListener('input', emit);
        el.querySelectorAll('.ile-num__key').forEach((btn) => {
            btn.addEventListener('click', () => {
                const n = btn.getAttribute('data-n');
                ctx.playSfx?.(n === 'back' || n === 'clear' ? 'swoosh' : 'click');
                if (!input) return;
                if (n === 'clear') input.value = '';
                else if (n === 'back') input.value = String(input.value).slice(0, -1);
                else input.value = `${input.value}${n}`;
                emit();
            });
        });
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        const v = this._el?.querySelector('#ile-num-input')?.value;
        if (v === '' || v == null) return null;
        return Number(v);
    },
    grade(answer) {
        if (answer == null || Number.isNaN(Number(answer))) {
            return { correct: false, score: 0, max: this._points };
        }
        const ok = Math.abs(Number(answer) - this._correct) <= this._tolerance;
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
