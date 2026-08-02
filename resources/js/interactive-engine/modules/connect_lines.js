import { escapeAttr, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

const LINE_COLORS = ['#059669', '#2563eb', '#d97706', '#db2777', '#7c3aed', '#0891b2'];

/** Independent module: connect left items to right by selecting pairs, with SVG lines. */
export const connectLinesModule = {
    type: 'connect_lines',
    _el: null,
    _points: 1,
    _pairs: {},
    _map: {},
    _selectedLeft: null,
    _leftById: null,
    _rightById: null,
    _ro: null,
    _onResize: null,
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
            <p class="ile-hint-line">اضغط عنصراً من العمود الأيمن ثم من الأيسر للربط بخط ملون</p>
            <div class="ile-connect__board">
                <svg class="ile-connect__svg" aria-hidden="true"></svg>
                <div class="ile-connect__cols">
                    <div class="ile-connect__col" data-side="left">
                        ${left
                            .map(
                                (l) => `<button type="button" class="ile-connect__node" data-side="left" data-id="${escapeAttr(l.id)}">
                                    <span class="ile-connect__dot" aria-hidden="true"></span>
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
                                    <span class="ile-connect__dot" aria-hidden="true"></span>
                                    ${mediaVisualHtml(r, '🟢')}
                                    <span>${itemLabelHtml(r)}</span>
                                    ${speakerButtonHtml(r)}
                                </button>`
                            )
                            .join('')}
                    </div>
                </div>
            </div>
        </div>`;

        el.querySelectorAll('.ile-connect__node').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                if (e.target.closest('.ile-speak')) return;
                const side = btn.getAttribute('data-side');
                const id = btn.getAttribute('data-id');
                if (side === 'left') {
                    el.querySelectorAll('.ile-connect__node[data-side="left"]').forEach((n) => n.classList.remove('is-picked'));
                    btn.classList.add('is-picked');
                    this._selectedLeft = id;
                } else if (this._selectedLeft) {
                    // إذا كان الطرف الأيمن مربوطاً بعنصر آخر، افصل الرابط القديم
                    Object.keys(this._map).forEach((leftId) => {
                        if (String(this._map[leftId]) === String(id) && String(leftId) !== String(this._selectedLeft)) {
                            delete this._map[leftId];
                        }
                    });
                    // إعادة النقر على نفس الزوج تلغي الربط
                    if (String(this._map[this._selectedLeft]) === String(id)) {
                        delete this._map[this._selectedLeft];
                    } else {
                        this._map[this._selectedLeft] = id;
                    }
                    this._selectedLeft = null;
                    el.querySelectorAll('.ile-connect__node').forEach((n) => n.classList.remove('is-picked'));
                    this.refreshLinks();
                    ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                }
            });
        });

        this._onResize = () => this.refreshLinks();
        window.addEventListener('resize', this._onResize);
        if (typeof ResizeObserver !== 'undefined') {
            const board = el.querySelector('.ile-connect__board');
            this._ro = new ResizeObserver(() => this.refreshLinks());
            if (board) this._ro.observe(board);
        }

        bindSpeakers(el, ctx.playOptionAudio);
        // رسم أولي بعد اكتمال التخطيط
        requestAnimationFrame(() => this.refreshLinks());
    },
    refreshLinks() {
        if (!this._el) return;
        const board = this._el.querySelector('.ile-connect__board');
        const svg = this._el.querySelector('.ile-connect__svg');
        if (!board || !svg) return;

        this._el.querySelectorAll('.ile-connect__node').forEach((n) => {
            n.classList.remove('is-linked');
            n.style.removeProperty('--ile-link-color');
        });

        const boardRect = board.getBoundingClientRect();
        const w = Math.max(boardRect.width, 1);
        const h = Math.max(boardRect.height, 1);
        svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
        svg.setAttribute('width', String(w));
        svg.setAttribute('height', String(h));

        const entries = Object.entries(this._map);
        const parts = [
            `<defs>
                <filter id="ile-connect-glow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="rgba(0,0,0,.18)"/>
                </filter>
            </defs>`,
        ];

        entries.forEach(([leftId, rightId], index) => {
            const leftNode = this._el.querySelector(`.ile-connect__node[data-side="left"][data-id="${CSS.escape(String(leftId))}"]`);
            const rightNode = this._el.querySelector(`.ile-connect__node[data-side="right"][data-id="${CSS.escape(String(rightId))}"]`);
            if (!leftNode || !rightNode) return;

            const color = LINE_COLORS[index % LINE_COLORS.length];
            leftNode.classList.add('is-linked');
            rightNode.classList.add('is-linked');
            leftNode.style.setProperty('--ile-link-color', color);
            rightNode.style.setProperty('--ile-link-color', color);

            const lr = leftNode.getBoundingClientRect();
            const rr = rightNode.getBoundingClientRect();

            // من منتصف الحافة الداخلية لكل بطاقة باتجاه العمود الآخر
            const x1 = lr.left + lr.width / 2 - boardRect.left;
            const y1 = lr.top + lr.height / 2 - boardRect.top;
            const x2 = rr.left + rr.width / 2 - boardRect.left;
            const y2 = rr.top + rr.height / 2 - boardRect.top;
            const mx = (x1 + x2) / 2;

            parts.push(`
                <path class="ile-connect__line" pathLength="1" d="M ${x1} ${y1} C ${mx} ${y1}, ${mx} ${y2}, ${x2} ${y2}"
                      fill="none" stroke="${color}" stroke-width="4" stroke-linecap="round"
                      filter="url(#ile-connect-glow)" />
                <circle cx="${x1}" cy="${y1}" r="6" fill="${color}" class="ile-connect__endpoint" />
                <circle cx="${x2}" cy="${y2}" r="6" fill="${color}" class="ile-connect__endpoint" />
            `);
        });

        svg.innerHTML = parts.join('');
    },
    async afterMount() {
        requestAnimationFrame(() => this.refreshLinks());
    },
    beforeDestroy() {},
    destroy() {
        if (this._onResize) {
            window.removeEventListener('resize', this._onResize);
            this._onResize = null;
        }
        if (this._ro) {
            this._ro.disconnect();
            this._ro = null;
        }
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
