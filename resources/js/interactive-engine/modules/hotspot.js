import { escapeAttr, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: click the correct hotspot on an image/area grid. */
export const hotspotModule = {
    type: 'hotspot',
    _el: null,
    _points: 1,
    _correctId: null,
    _selected: null,
    async beforeMount(ctx) {
        const spots = ctx.question.payload?.spots || [];
        if (anyNeedsKatex(spots)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correctId = ctx.question.payload?.correctId;
        this._selected = null;
        const imageUrl = ctx.question.payload?.imageUrl || '';
        const spots = ctx.question.payload?.spots || [];

        el.innerHTML = `<div class="ile-hotspot">
            <p class="ile-hint-line">اضغط على المكان الصحيح</p>
            <div class="ile-hotspot__stage" style="${imageUrl ? `background-image:url('${escapeAttr(imageUrl)}')` : ''}">
                ${!imageUrl ? '<div class="ile-hotspot__placeholder"><i class="bi bi-geo-alt" aria-hidden="true"></i> اختر المنطقة</div>' : ''}
                ${spots
                    .map(
                        (s) => `<button type="button" class="ile-hotspot__spot" data-id="${escapeAttr(s.id)}"
                            style="inset-inline-start:${Number(s.x || 10)}%;top:${Number(s.y || 10)}%;width:${Number(s.w || 20)}%;height:${Number(s.h || 20)}%"
                            title="${escapeAttr(s.label || '')}">
                            <span>${s.label ? itemLabelHtml(s) : '●'}</span>
                        </button>`
                    )
                    .join('')}
            </div>
        </div>`;

        el.querySelectorAll('.ile-hotspot__spot').forEach((btn) => {
            btn.addEventListener('click', () => {
                el.querySelectorAll('.ile-hotspot__spot').forEach((b) => b.classList.remove('is-selected'));
                btn.classList.add('is-selected');
                this._selected = btn.getAttribute('data-id');
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
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
        return this._selected;
    },
    grade(answer) {
        const ok = answer != null && String(answer) === String(this._correctId);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
