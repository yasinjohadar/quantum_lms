import { escapeAttr, escapeHtml, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';
import { mathPlainText } from '../dynamic/mathText.js';

export const matchingModule = {
    type: 'matching',
    _el: null,
    _points: 1,
    _pairs: {},
    _map: {},
    async beforeMount(ctx) {
        const p = ctx.question.payload || {};
        if (anyNeedsKatex(p.left, p.right)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._pairs = ctx.question.payload?.pairs || {};
        this._map = {};
        const left = ctx.question.payload?.left || [];
        const right = [...(ctx.question.payload?.right || [])].sort(() => Math.random() - 0.5);
        el.innerHTML = `<div class="ile-match">
            ${left
                .map(
                    (l) => `<div class="ile-match__row">
                    <div class="ile-match__left">
                        ${mediaVisualHtml(l, '🔗')}
                        <span>${itemLabelHtml(l)}</span>
                        ${speakerButtonHtml(l)}
                    </div>
                    <select data-left="${escapeAttr(l.id)}" class="ile-match__select">
                        <option value="">— اختر —</option>
                        ${right
                            .map(
                                (r) =>
                                    `<option value="${escapeAttr(r.id)}">${escapeHtml((r.icon ? r.icon + ' ' : '') + mathPlainText(r.label))}</option>`
                            )
                            .join('')}
                    </select>
                </div>`
                )
                .join('')}
        </div>`;
        el.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => {
                this._map[select.getAttribute('data-left')] = select.value;
                select.classList.toggle('is-selected', Boolean(select.value));
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
        return { ...this._map };
    },
    grade(answer) {
        const map = answer && typeof answer === 'object' ? answer : {};
        const keys = Object.keys(this._pairs);
        if (keys.length === 0) {
            return { correct: false, score: 0, max: this._points };
        }
        let correctCount = 0;
        keys.forEach((leftId) => {
            if (String(map[leftId]) === String(this._pairs[leftId])) correctCount += 1;
        });
        const ratio = correctCount / keys.length;
        const score = Math.round(ratio * this._points * 100) / 100;
        return { correct: ratio === 1, score, max: this._points, detail: { correctCount, total: keys.length } };
    },
};
