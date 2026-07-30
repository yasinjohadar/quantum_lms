import { escapeAttr, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: categorize items into bins. */
export const categorizeModule = {
    type: 'categorize',
    _el: null,
    _points: 1,
    _correct: {},
    _map: {},
    _itemsById: {},
    async beforeMount(ctx) {
        const p = ctx.question.payload || {};
        if (anyNeedsKatex(p.items, p.categories)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correct = ctx.question.payload?.correct || {};
        this._map = {};
        const items = ctx.question.payload?.items || [];
        const categories = ctx.question.payload?.categories || [];
        this._itemsById = Object.fromEntries(items.map((i) => [String(i.id), i]));

        el.innerHTML = `<div class="ile-cat">
            <div class="ile-cat__items">
                ${items
                    .map(
                        (item) => `<div class="ile-cat__item" draggable="true" data-item="${escapeAttr(item.id)}">
                            <i class="bi bi-grip-vertical ile-drag-handle" aria-hidden="true"></i>
                            ${mediaVisualHtml(item, '📦')}
                            <span class="ile-dd__item-label">${itemLabelHtml(item)}</span>
                            ${speakerButtonHtml(item)}
                        </div>`
                    )
                    .join('')}
            </div>
            <div class="ile-cat__bins">
                ${categories
                    .map(
                        (cat) => `<div class="ile-cat__bin" data-cat="${escapeAttr(cat.id)}">
                            <div class="ile-cat__bin-title">${mediaVisualHtml(cat, '🗂️')} <span>${itemLabelHtml(cat)}</span></div>
                            <div class="ile-cat__bin-drop" data-drop="${escapeAttr(cat.id)}"></div>
                        </div>`
                    )
                    .join('')}
            </div>
        </div>`;

        let dragging = null;
        el.querySelectorAll('.ile-cat__item').forEach((itemEl) => {
            itemEl.addEventListener('dragstart', (e) => {
                dragging = itemEl.getAttribute('data-item');
                try {
                    e.dataTransfer.setData('text/plain', dragging || '');
                } catch {
                    /* ignore */
                }
            });
        });
        el.querySelectorAll('[data-drop]').forEach((drop) => {
            drop.addEventListener('dragover', (e) => {
                e.preventDefault();
                drop.classList.add('is-over');
            });
            drop.addEventListener('dragleave', () => drop.classList.remove('is-over'));
            drop.addEventListener('drop', (e) => {
                e.preventDefault();
                drop.classList.remove('is-over');
                const itemId = dragging || e.dataTransfer?.getData('text/plain');
                if (!itemId) return;
                this._map[itemId] = drop.getAttribute('data-drop');
                this.renderPlaced();
                ctx.playSfx?.('pop');
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
            });
        });
        bindSpeakers(el, ctx.playOptionAudio);
    },
    renderPlaced() {
        this._el.querySelectorAll('[data-drop]').forEach((drop) => {
            const catId = drop.getAttribute('data-drop');
            const placed = Object.entries(this._map)
                .filter(([, c]) => c === catId)
                .map(([id]) => this._itemsById[id])
                .filter(Boolean);
            drop.innerHTML = placed
                .map(
                    (item) => `<div class="ile-dd__chip">${mediaVisualHtml(item, '📦')}<span>${itemLabelHtml(item)}</span></div>`
                )
                .join('');
        });
        this._el.querySelectorAll('.ile-cat__item').forEach((itemEl) => {
            itemEl.classList.toggle('is-placed', Boolean(this._map[itemEl.getAttribute('data-item')]));
        });
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
        const keys = Object.keys(this._correct);
        if (!keys.length) return { correct: false, score: 0, max: this._points };
        let okCount = 0;
        keys.forEach((id) => {
            if (String(map[id]) === String(this._correct[id])) okCount += 1;
        });
        const ratio = okCount / keys.length;
        return {
            correct: ratio === 1,
            score: Math.round(ratio * this._points * 100) / 100,
            max: this._points,
        };
    },
};
