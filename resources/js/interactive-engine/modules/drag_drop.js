import { escapeAttr, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

export const dragDropModule = {
    type: 'drag_drop',
    _el: null,
    _points: 1,
    _assignments: {},
    _map: {},
    _itemsById: {},
    async beforeMount(ctx) {
        const p = ctx.question.payload || {};
        if (anyNeedsKatex(p.items, p.zones)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._assignments = ctx.question.payload?.assignments || {};
        this._map = {};
        const items = ctx.question.payload?.items || [];
        const zones = ctx.question.payload?.zones || [];
        this._itemsById = Object.fromEntries(items.map((i) => [String(i.id), i]));

        el.innerHTML = `
            <div class="ile-dd">
                <div class="ile-dd__items">
                    ${items
                        .map(
                            (item) =>
                                `<div class="ile-dd__item" draggable="true" data-item="${escapeAttr(item.id)}">
                                    <i class="bi bi-grip-vertical ile-drag-handle" aria-hidden="true"></i>
                                    ${mediaVisualHtml(item, '🧩')}
                                    <span class="ile-dd__item-label">${itemLabelHtml(item)}</span>
                                    ${speakerButtonHtml(item)}
                                </div>`
                        )
                        .join('')}
                </div>
                <div class="ile-dd__zones">
                    ${zones
                        .map(
                            (zone) =>
                                `<div class="ile-dd__zone" data-zone="${escapeAttr(zone.id)}">
                                    <div class="ile-dd__zone-label">
                                        ${mediaVisualHtml(zone, '📦')}
                                        <span class="ile-dd__zone-title">${itemLabelHtml(zone)}</span>
                                    </div>
                                    <div class="ile-dd__zone-drop" data-drop="${escapeAttr(zone.id)}"></div>
                                </div>`
                        )
                        .join('')}
                </div>
            </div>`;

        let dragging = null;

        el.querySelectorAll('.ile-dd__item').forEach((itemEl) => {
            itemEl.addEventListener('dragstart', (e) => {
                dragging = itemEl.getAttribute('data-item');
                itemEl.classList.add('is-dragging');
                try {
                    e.dataTransfer.setData('text/plain', dragging || '');
                    e.dataTransfer.effectAllowed = 'move';
                } catch {
                    /* ignore */
                }
            });
            itemEl.addEventListener('dragend', () => {
                itemEl.classList.remove('is-dragging');
                dragging = null;
                el.querySelectorAll('.ile-dd__zone-drop').forEach((d) => d.classList.remove('is-over'));
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
                const zoneId = drop.getAttribute('data-drop');
                this._map[itemId] = zoneId;
                this.renderPlaced();
                ctx.playSfx?.('pop');
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
            });
        });

        bindSpeakers(el, ctx.playOptionAudio);
    },
    renderPlaced() {
        if (!this._el) return;
        this._el.querySelectorAll('[data-drop]').forEach((drop) => {
            const zoneId = drop.getAttribute('data-drop');
            const placed = Object.entries(this._map)
                .filter(([, z]) => z === zoneId)
                .map(([itemId]) => this._itemsById[itemId])
                .filter(Boolean);

            drop.innerHTML = placed.length
                ? placed
                    .map(
                        (item) => `<div class="ile-dd__chip" data-placed="${escapeAttr(item.id)}">
                            ${mediaVisualHtml(item, '🧩')}
                            <span class="ile-dd__item-label">${itemLabelHtml(item)}</span>
                        </div>`
                    )
                    .join('')
                : '';
        });

        // Dim source items that are already placed
        this._el.querySelectorAll('.ile-dd__item').forEach((itemEl) => {
            const id = itemEl.getAttribute('data-item');
            itemEl.classList.toggle('is-placed', Boolean(this._map[id]));
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
        const keys = Object.keys(this._assignments);
        if (keys.length === 0) {
            return { correct: false, score: 0, max: this._points };
        }
        let correctCount = 0;
        keys.forEach((itemId) => {
            if (String(map[itemId]) === String(this._assignments[itemId])) correctCount += 1;
        });
        const ratio = correctCount / keys.length;
        const score = Math.round(ratio * this._points * 100) / 100;
        return { correct: ratio === 1, score, max: this._points, detail: { correctCount, total: keys.length } };
    },
};
