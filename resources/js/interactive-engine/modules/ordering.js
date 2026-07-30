import { escapeAttr, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: reorder items into correct sequence. */
export const orderingModule = {
    type: 'ordering',
    _el: null,
    _points: 1,
    _correctOrder: [],
    _order: [],
    async beforeMount(ctx) {
        const items = ctx.question.payload?.items || [];
        if (anyNeedsKatex(items)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        const items = [...(ctx.question.payload?.items || [])];
        this._correctOrder = (ctx.question.payload?.correctOrder || items.map((i) => i.id)).map(String);
        // Shuffle for play
        const shuffled = items.sort(() => Math.random() - 0.5);
        this._order = shuffled.map((i) => String(i.id));
        this._itemsById = Object.fromEntries(items.map((i) => [String(i.id), i]));
        this.render(ctx);
    },
    render(ctx) {
        const el = this._el;
        el.innerHTML = `<div class="ile-order">
            <p class="ile-hint-line">رتّب العناصر بالترتيب الصحيح (استخدم الأسهم)</p>
            <ol class="ile-order__list">
                ${this._order
                    .map((id, index) => {
                        const item = this._itemsById[id] || { id, label: id };
                        return `<li class="ile-order__item" data-id="${escapeAttr(id)}">
                            <span class="ile-order__num">${index + 1}</span>
                            ${mediaVisualHtml(item, '🔢')}
                            <span class="ile-order__label">${itemLabelHtml(item)}</span>
                            ${speakerButtonHtml(item)}
                            <div class="ile-order__actions">
                                <button type="button" class="ile-order__btn" data-act="up" data-id="${escapeAttr(id)}" ${index === 0 ? 'disabled' : ''}>▲</button>
                                <button type="button" class="ile-order__btn" data-act="down" data-id="${escapeAttr(id)}" ${index === this._order.length - 1 ? 'disabled' : ''}>▼</button>
                            </div>
                        </li>`;
                    })
                    .join('')}
            </ol>
        </div>`;
        el.querySelectorAll('.ile-order__btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const act = btn.getAttribute('data-act');
                const i = this._order.indexOf(id);
                if (i < 0) return;
                const j = act === 'up' ? i - 1 : i + 1;
                if (j < 0 || j >= this._order.length) return;
                [this._order[i], this._order[j]] = [this._order[j], this._order[i]];
                this.render(ctx);
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
        return [...this._order];
    },
    grade(answer) {
        const got = (Array.isArray(answer) ? answer : []).map(String);
        const expected = this._correctOrder.map(String);
        const ok = got.length === expected.length && got.every((v, i) => v === expected[i]);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
