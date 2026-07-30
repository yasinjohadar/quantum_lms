import { escapeAttr, escapeHtml, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: memory matching cards. */
export const memoryCardsModule = {
    type: 'memory_cards',
    _el: null,
    _points: 1,
    _pairs: {},
    _flipped: [],
    _matched: new Set(),
    _lock: false,
    _cards: [],
    async beforeMount(ctx) {
        const p = ctx.question.payload || {};
        if (anyNeedsKatex(p.left, p.right, p.cards)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._pairs = ctx.question.payload?.pairs || {};
        this._flipped = [];
        this._matched = new Set();
        this._lock = false;
        this._cards = this.buildCards(ctx.question.payload || {});

        el.innerHTML = `<div class="ile-memory">
            <p class="ile-hint-line">اضغط بطاقة لإظهارها، ثم ابحث عن توأمها</p>
            <div class="ile-memory__grid">
                ${this._cards
                    .map(
                        (c) => `<button type="button" class="ile-memory__card" data-key="${escapeAttr(c.key)}" data-id="${escapeAttr(c.id)}" data-side="${escapeAttr(c.side || '')}">
                            <span class="ile-memory__face ile-memory__back" aria-hidden="true"><i class="bi bi-question-lg"></i></span>
                            <span class="ile-memory__face ile-memory__front">
                                <span class="ile-memory__icon">${escapeHtml(c.icon || '⭐')}</span>
                                <span class="ile-memory__label">${itemLabelHtml(c)}</span>
                            </span>
                        </button>`
                    )
                    .join('')}
            </div>
        </div>`;

        el.querySelectorAll('.ile-memory__card').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.flip(btn, ctx);
            });
        });
    },
    buildCards(payload) {
        const left = payload.left || [];
        const right = payload.right || [];
        if (left.length && right.length) {
            const cards = [];
            left.forEach((l) => cards.push({
                id: String(l.id),
                label: l.label,
                icon: l.icon || '🔢',
                side: 'left',
                key: `L_${l.id}`,
            }));
            right.forEach((r) => cards.push({
                id: String(r.id),
                label: r.label,
                icon: r.icon || '🌟',
                side: 'right',
                key: `R_${r.id}`,
            }));
            return cards.sort(() => Math.random() - 0.5);
        }

        // Fallback: duplicate each card into a pair (same id)
        const source = payload.cards || [];
        const cards = [];
        source.forEach((c, i) => {
            const id = String(c.id || `c${i}`);
            cards.push({ id, label: c.label, icon: c.icon || '⭐', side: '', key: `${id}_1` });
            cards.push({ id, label: c.label, icon: c.icon || '⭐', side: '', key: `${id}_2` });
        });
        return cards.sort(() => Math.random() - 0.5);
    },
    flip(btn, ctx) {
        if (this._lock) return;
        if (btn.classList.contains('is-matched') || btn.classList.contains('is-flipped')) return;

        btn.classList.add('is-flipped');
        ctx.playSfx?.('click');
        this._flipped.push(btn);
        if (this._flipped.length < 2) return;

        this._lock = true;
        const [a, b] = this._flipped;
        const match = this.isMatch(a, b);
        ctx.playSfx?.(match ? 'ding' : 'thud');

        window.setTimeout(() => {
            if (match) {
                a.classList.add('is-matched');
                b.classList.add('is-matched');
                this._matched.add(a.getAttribute('data-key'));
                this._matched.add(b.getAttribute('data-key'));
            } else {
                a.classList.remove('is-flipped');
                b.classList.remove('is-flipped');
            }
            this._flipped = [];
            this._lock = false;
            ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
        }, match ? 280 : 700);
    },
    isMatch(a, b) {
        const idA = a.getAttribute('data-id');
        const idB = b.getAttribute('data-id');
        const sideA = a.getAttribute('data-side') || '';
        const sideB = b.getAttribute('data-side') || '';

        if (sideA && sideB && sideA !== sideB) {
            const leftId = sideA === 'left' ? idA : idB;
            const rightId = sideA === 'right' ? idA : idB;
            return String(this._pairs[leftId]) === String(rightId);
        }

        return idA === idB && a.getAttribute('data-key') !== b.getAttribute('data-key');
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        return { matched: this._matched.size, total: this._cards.length };
    },
    grade() {
        const ok = this._cards.length > 0 && this._matched.size === this._cards.length;
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
