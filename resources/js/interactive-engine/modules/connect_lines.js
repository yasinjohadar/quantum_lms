import { escapeAttr, escapeHtml, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: connect left items to right by selecting pairs. */
export const connectLinesModule = {
    type: 'connect_lines',
    _el: null,
    _points: 1,
    _pairs: {},
    _map: {},
    _selectedLeft: null,
    async beforeMount(ctx) {
        const p = ctx.question.payload || {};
        if (anyNeedsKatex(p.left, p.right)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._pairs = ctx.question.payload?.pairs || {};
        this._map = {};
        this._selectedLeft = null;
        const left = ctx.question.payload?.left || [];
        const right = [...(ctx.question.payload?.right || [])].sort(() => Math.random() - 0.5);
        this._leftById = Object.fromEntries(left.map((l) => [String(l.id), l]));
        this._rightById = Object.fromEntries(right.map((r) => [String(r.id), r]));

        el.innerHTML = `<div class="ile-connect">
            <p class="ile-hint-line">اضغط عنصراً من اليمين ثم من اليسار للربط</p>
            <div class="ile-connect__cols">
                <div class="ile-connect__col" data-side="left">
                    ${left
                        .map(
                            (l) => `<button type="button" class="ile-connect__node" data-side="left" data-id="${escapeAttr(l.id)}">
                                ${mediaVisualHtml(l, '🔵')}
                                <span>${itemLabelHtml(l)}</span>
                                ${speakerButtonHtml(l)}
                            </button>`
                        )
                        .join('')}
                </div>
                <div class="ile-connect__col" data-side="right">
                    ${right
                        .map(
                            (r) => `<button type="button" class="ile-connect__node" data-side="right" data-id="${escapeAttr(r.id)}">
                                ${mediaVisualHtml(r, '🟢')}
                                <span>${itemLabelHtml(r)}</span>
                                ${speakerButtonHtml(r)}
                            </button>`
                        )
                        .join('')}
                </div>
            </div>
            <div class="ile-connect__links" id="ile-connect-links"></div>
        </div>`;

        el.querySelectorAll('.ile-connect__node').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                if (e.target.closest('.ile-speak')) return;
                const side = btn.getAttribute('data-side');
                const id = btn.getAttribute('data-id');
                if (side === 'left') {
                    el.querySelectorAll('[data-side="left"]').forEach((n) => n.classList.remove('is-picked'));
                    btn.classList.add('is-picked');
                    this._selectedLeft = id;
                } else if (this._selectedLeft) {
                    this._map[this._selectedLeft] = id;
                    this._selectedLeft = null;
                    el.querySelectorAll('.ile-connect__node').forEach((n) => n.classList.remove('is-picked'));
                    this.refreshLinks();
                    ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                }
            });
        });
        bindSpeakers(el, ctx.playOptionAudio);
    },
    refreshLinks() {
        const box = this._el.querySelector('#ile-connect-links');
        if (!box) return;
        const leftItems = this._el.querySelectorAll('[data-side="left"] .ile-connect__node, .ile-connect__node[data-side="left"]');
        // Mark paired nodes
        this._el.querySelectorAll('.ile-connect__node').forEach((n) => n.classList.remove('is-linked'));
        Object.entries(this._map).forEach(([l, r]) => {
            this._el.querySelector(`.ile-connect__node[data-side="left"][data-id="${CSS.escape(l)}"]`)?.classList.add('is-linked');
            this._el.querySelector(`.ile-connect__node[data-side="right"][data-id="${CSS.escape(r)}"]`)?.classList.add('is-linked');
        });
        box.innerHTML = Object.entries(this._map)
            .map(([l, r]) => {
                const leftItem = this._leftById?.[l];
                const rightItem = this._rightById?.[r];
                return `<div class="ile-connect__link-row">${leftItem ? itemLabelHtml(leftItem) : escapeHtml(l)} ↔ ${rightItem ? itemLabelHtml(rightItem) : escapeHtml(r)}</div>`;
            })
            .join('');
        void leftItems;
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
        if (!keys.length) return { correct: false, score: 0, max: this._points };
        let ok = 0;
        keys.forEach((k) => {
            if (String(map[k]) === String(this._pairs[k])) ok += 1;
        });
        const ratio = ok / keys.length;
        return { correct: ratio === 1, score: Math.round(ratio * this._points * 100) / 100, max: this._points };
    },
};
