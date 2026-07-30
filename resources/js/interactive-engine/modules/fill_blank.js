import { escapeAttr, escapeHtml, mediaVisualHtml, speakerButtonHtml, bindSpeakers, optionLabelHtml, optionsNeedKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: fill blank by choosing/typing the missing word. */
export const fillBlankModule = {
    type: 'fill_blank',
    _el: null,
    _points: 1,
    _mode: 'choice',
    _correct: '',
    _accepted: [],
    async beforeMount(ctx) {
        const options = ctx.question.payload?.options || [];
        if (optionsNeedKatex(options)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        const p = ctx.question.payload || {};
        this._mode = p.mode === 'text' ? 'text' : 'choice';
        this._correct = String(p.correct ?? p.correctId ?? '');
        this._accepted = (p.acceptedAnswers || [this._correct]).map((s) => String(s).trim().toLowerCase());
        const template = String(p.template || 'أكمل: ___');
        const parts = template.split('___');
        const options = p.options || [];
        this._options = options;
        if (this._mode === 'text') {
            el.innerHTML = `<div class="ile-blank">
                <div class="ile-blank__sentence">
                    <span>${escapeHtml(parts[0] || '')}</span>
                    <input type="text" class="ile-blank__input" id="ile-blank-input" placeholder="اكتب هنا" autocomplete="off">
                    <span>${escapeHtml(parts[1] || '')}</span>
                </div>
            </div>`;
            el.querySelector('#ile-blank-input')?.addEventListener('input', () => {
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
            });
        } else {
            el.innerHTML = `<div class="ile-blank">
                <div class="ile-blank__sentence">
                    <span>${escapeHtml(parts[0] || '')}</span>
                    <span class="ile-blank__slot" id="ile-blank-slot">؟</span>
                    <span>${escapeHtml(parts[1] || '')}</span>
                </div>
                <div class="ile-options">
                    ${options
                        .map(
                            (opt) => `<button type="button" class="ile-option ile-blank__opt" data-id="${escapeAttr(opt.id)}">
                                ${mediaVisualHtml(opt, '✏️')}
                                <span class="ile-option__label">${optionLabelHtml(opt)}</span>
                                ${speakerButtonHtml(opt)}
                            </button>`
                        )
                        .join('')}
                </div>
            </div>`;
            el.querySelectorAll('.ile-blank__opt').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    if (e.target.closest('.ile-speak')) return;
                    el.querySelectorAll('.ile-blank__opt').forEach((b) => b.classList.remove('is-selected'));
                    btn.classList.add('is-selected');
                    const opt = options.find((o) => String(o.id) === btn.getAttribute('data-id'));
                    const slot = el.querySelector('#ile-blank-slot');
                    if (slot) slot.innerHTML = opt ? optionLabelHtml(opt) : '؟';
                    this._selected = btn.getAttribute('data-id');
                    ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                });
            });
            bindSpeakers(el, ctx.playOptionAudio);
        }
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        if (this._mode === 'text') {
            return this._el?.querySelector('#ile-blank-input')?.value?.trim() || '';
        }
        return this._selected || null;
    },
    grade(answer) {
        if (this._mode === 'text') {
            const got = String(answer || '').trim().toLowerCase();
            const ok = this._accepted.includes(got);
            return { correct: ok, score: ok ? this._points : 0, max: this._points };
        }

        const selectedId = answer == null ? '' : String(answer);
        const correctRaw = String(this._correct ?? '');
        const options = this._el
            ? [...(this._options || [])]
            : [];

        // Resolve correct whether AI stored option id OR label text
        let correctId = correctRaw;
        const byId = options.find((o) => String(o.id) === correctRaw);
        if (!byId) {
            const byLabel = options.find(
                (o) => String(o.label).trim() === correctRaw.trim()
                    || String(o.label).trim().toLowerCase() === correctRaw.trim().toLowerCase()
            );
            if (byLabel) correctId = String(byLabel.id);
        }

        const ok = selectedId !== '' && selectedId === String(correctId);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
