import { escapeAttr, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: simple puzzle — place pieces in slots by order. */
export const puzzlePiecesModule = {
    type: 'puzzle_pieces',
    _el: null,
    _points: 1,
    _correctOrder: [],
    _slots: [],
    _piecesById: {},
    async beforeMount(ctx) {
        const pieces = ctx.question.payload?.pieces || [];
        if (anyNeedsKatex(pieces)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        const pieces = ctx.question.payload?.pieces || [];
        this._correctOrder = (ctx.question.payload?.correctOrder || pieces.map((p) => p.id)).map(String);
        this._slots = Array(this._correctOrder.length).fill(null);
        this._piecesById = Object.fromEntries(pieces.map((p) => [String(p.id), p]));
        const pool = [...pieces].sort(() => Math.random() - 0.5);

        el.innerHTML = `<div class="ile-puzzle">
            <p class="ile-hint-line">ضع القطع في أماكنها بالترتيب</p>
            <div class="ile-puzzle__slots">
                ${this._slots
                    .map(
                        (_, i) => `<div class="ile-puzzle__slot" data-slot="${i}">
                            <span class="ile-puzzle__slot-num">${i + 1}</span>
                            <div class="ile-puzzle__slot-drop" data-drop="${i}"></div>
                        </div>`
                    )
                    .join('')}
            </div>
            <div class="ile-puzzle__pool" id="ile-puzzle-pool">
                ${pool
                    .map(
                        (p) => `<div class="ile-puzzle__piece" draggable="true" data-piece="${escapeAttr(p.id)}">
                            <i class="bi bi-grip-vertical ile-drag-handle" aria-hidden="true"></i>
                            ${mediaVisualHtml(p, '🧩')}
                            <span>${itemLabelHtml(p)}</span>
                            ${speakerButtonHtml(p)}
                        </div>`
                    )
                    .join('')}
            </div>
        </div>`;

        let dragging = null;
        el.querySelectorAll('.ile-puzzle__piece').forEach((piece) => {
            piece.addEventListener('dragstart', (e) => {
                dragging = piece.getAttribute('data-piece');
                try {
                    e.dataTransfer.setData('text/plain', dragging || '');
                } catch {
                    /* ignore */
                }
            });
        });
        el.querySelectorAll('[data-drop]').forEach((drop) => {
            drop.addEventListener('dragover', (e) => e.preventDefault());
            drop.addEventListener('drop', (e) => {
                e.preventDefault();
                const pieceId = dragging || e.dataTransfer?.getData('text/plain');
                if (!pieceId) return;
                const slot = Number(drop.getAttribute('data-drop'));
                // Remove from other slots
                this._slots = this._slots.map((v) => (v === pieceId ? null : v));
                this._slots[slot] = pieceId;
                this.renderSlots();
                ctx.playSfx?.('pop');
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
            });
        });
        bindSpeakers(el, ctx.playOptionAudio);
    },
    renderSlots() {
        this._el.querySelectorAll('[data-drop]').forEach((drop) => {
            const slot = Number(drop.getAttribute('data-drop'));
            const id = this._slots[slot];
            const piece = id ? this._piecesById[id] : null;
            drop.innerHTML = piece
                ? `<div class="ile-dd__chip">${mediaVisualHtml(piece, '🧩')}<span>${itemLabelHtml(piece)}</span></div>`
                : '';
        });
        this._el.querySelectorAll('.ile-puzzle__piece').forEach((p) => {
            p.classList.toggle('is-placed', this._slots.includes(p.getAttribute('data-piece')));
        });
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        return [...this._slots];
    },
    grade(answer) {
        const got = (Array.isArray(answer) ? answer : []).map((v) => (v == null ? '' : String(v)));
        const expected = this._correctOrder.map(String);
        const ok = got.length === expected.length && got.every((v, i) => v === expected[i]);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
