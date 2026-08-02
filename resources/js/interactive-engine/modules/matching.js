import { escapeAttr, escapeHtml, mediaVisualHtml, speakerButtonHtml, bindSpeakers, itemLabelHtml, anyNeedsKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';
import { mathPlainText } from '../dynamic/mathText.js';

function optionLabel(r) {
    return (r.icon ? r.icon + ' ' : '') + mathPlainText(r.label);
}

export const matchingModule = {
    type: 'matching',
    _el: null,
    _points: 1,
    _pairs: {},
    _map: {},
    _docClose: null,
    _escClose: null,
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

        const optionsHtml = right
            .map(
                (r) => `<button type="button" class="ile-match__opt" role="option" data-value="${escapeAttr(r.id)}" data-label="${escapeAttr(optionLabel(r))}">
                    <span class="ile-match__opt-check" aria-hidden="true"><i class="bi bi-check2"></i></span>
                    <span class="ile-match__opt-text">${escapeHtml(optionLabel(r))}</span>
                </button>`
            )
            .join('');

        el.innerHTML = `<div class="ile-match">
            ${left
                .map(
                    (l) => `<div class="ile-match__row">
                    <div class="ile-match__left">
                        ${mediaVisualHtml(l, '🔗')}
                        <span>${itemLabelHtml(l)}</span>
                        ${speakerButtonHtml(l)}
                    </div>
                    <div class="ile-match__dd" data-left="${escapeAttr(l.id)}">
                        <button type="button" class="ile-match__trigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="ile-match__trigger-icon" aria-hidden="true"><i class="bi bi-list-check"></i></span>
                            <span class="ile-match__trigger-label">اختر الإجابة</span>
                            <span class="ile-match__trigger-caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>
                        <div class="ile-match__menu" role="listbox" hidden>
                            <button type="button" class="ile-match__opt ile-match__opt--clear" data-value="" data-label="اختر الإجابة">
                                <span class="ile-match__opt-check" aria-hidden="true"><i class="bi bi-dash"></i></span>
                                <span class="ile-match__opt-text">— اختر —</span>
                            </button>
                            ${optionsHtml}
                        </div>
                    </div>
                </div>`
                )
                .join('')}
        </div>`;

        const closeAll = (except = null) => {
            el.querySelectorAll('.ile-match__dd.is-open').forEach((dd) => {
                if (except && dd === except) return;
                dd.classList.remove('is-open');
                const menu = dd.querySelector('.ile-match__menu');
                const trigger = dd.querySelector('.ile-match__trigger');
                if (menu) menu.hidden = true;
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        };

        el.querySelectorAll('.ile-match__dd').forEach((dd) => {
            const leftId = dd.getAttribute('data-left');
            const trigger = dd.querySelector('.ile-match__trigger');
            const labelEl = dd.querySelector('.ile-match__trigger-label');
            const menu = dd.querySelector('.ile-match__menu');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const willOpen = !dd.classList.contains('is-open');
                closeAll();
                if (willOpen) {
                    dd.classList.add('is-open');
                    menu.hidden = false;
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });

            menu.querySelectorAll('.ile-match__opt').forEach((opt) => {
                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = opt.getAttribute('data-value') || '';
                    const label = opt.getAttribute('data-label') || 'اختر الإجابة';

                    menu.querySelectorAll('.ile-match__opt').forEach((o) => o.classList.remove('is-active'));
                    if (value) {
                        opt.classList.add('is-active');
                        this._map[leftId] = value;
                        labelEl.textContent = label;
                        dd.classList.add('is-selected');
                        trigger.classList.add('is-selected');
                    } else {
                        delete this._map[leftId];
                        labelEl.textContent = 'اختر الإجابة';
                        dd.classList.remove('is-selected');
                        trigger.classList.remove('is-selected');
                    }

                    closeAll();
                    ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                });
            });
        });

        this._docClose = (e) => {
            if (!el.contains(e.target)) closeAll();
        };
        this._escClose = (e) => {
            if (e.key === 'Escape') closeAll();
        };
        document.addEventListener('click', this._docClose);
        document.addEventListener('keydown', this._escClose);

        bindSpeakers(el, ctx.playOptionAudio);
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._docClose) {
            document.removeEventListener('click', this._docClose);
            this._docClose = null;
        }
        if (this._escClose) {
            document.removeEventListener('keydown', this._escClose);
            this._escClose = null;
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
