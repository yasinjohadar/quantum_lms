import { escapeAttr, escapeHtml, mediaVisualHtml, speakerButtonHtml, bindSpeakers, optionLabelHtml, optionsNeedKatex } from './_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';

/** Independent module: listen then choose. */
export const listenChooseModule = {
    type: 'listen_choose',
    _el: null,
    _points: 1,
    _correctId: null,
    async beforeMount(ctx) {
        const options = ctx.question.payload?.options || [];
        if (optionsNeedKatex(options)) await loadLibraries(['katex']);
    },
    mount(el, ctx) {
        this._el = el;
        this._points = Number(ctx.question.points ?? 1);
        this._correctId = ctx.question.payload?.correctId;
        const prompt = ctx.question.payload?.prompt || {};
        const options = ctx.question.payload?.options || [];
        this._speakText = this.resolveSpeakText(ctx.question, prompt, options);
        this._speakAudio = prompt.audioUrl || '';

        el.innerHTML = `<div class="ile-listen">
            <button type="button" class="ile-btn ile-btn--listen ile-listen__play" id="ile-listen-play">
                <i class="bi bi-volume-up-fill" aria-hidden="true"></i> استمع
            </button>
            <p class="ile-hint-line">اضغط استمع ثم اختر ما سمعت</p>
            <div class="ile-options">
                ${options
                    .map(
                        (opt) => `<label class="ile-option">
                            <input type="radio" name="lc_${escapeAttr(ctx.question.id)}" value="${escapeAttr(opt.id)}">
                            <span class="ile-option__media">${mediaVisualHtml(opt)}</span>
                            <span class="ile-option__label">${optionLabelHtml(opt)}</span>
                            ${speakerButtonHtml(opt)}
                        </label>`
                    )
                    .join('')}
            </div>
        </div>`;

        el.querySelector('#ile-listen-play')?.addEventListener('click', () => {
            ctx.playOptionAudio?.({
                label: this._speakText,
                audioUrl: this._speakAudio,
            });
        });
        el.querySelectorAll('input').forEach((input) => {
            input.addEventListener('change', () => {
                el.querySelectorAll('.ile-option').forEach((lab) => lab.classList.remove('is-selected'));
                input.closest('.ile-option')?.classList.add('is-selected');
                ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
            });
        });
        bindSpeakers(el, ctx.playOptionAudio);
    },
    resolveSpeakText(question, prompt, options) {
        const direct = prompt.text || prompt.speak || prompt.word || '';
        if (String(direct).trim()) return String(direct).trim();

        const stem = String(question.stem || '');
        const m = stem.match(/صوت\s*:\s*([^)\]]+)/i) || stem.match(/\(صوت:\s*([^)]+)\)/i);
        if (m?.[1]) return m[1].trim();

        const correct = options.find((o) => String(o.id) === String(this._correctId));
        const digitMap = {
            0: 'صفر', 1: 'واحد', 2: 'اثنان', 3: 'ثلاثة', 4: 'أربعة',
            5: 'خمسة', 6: 'ستة', 7: 'سبعة', 8: 'ثمانية', 9: 'تسعة', 10: 'عشرة',
        };
        if (correct?.label != null) {
            const raw = String(correct.label).trim();
            if (digitMap[raw] != null) return digitMap[raw];
            return raw;
        }

        return prompt.label && !/استمع/.test(prompt.label) ? prompt.label : 'مرحبا';
    },
    async afterMount() {},
    beforeDestroy() {},
    destroy() {
        if (this._el) this._el.innerHTML = '';
        this._el = null;
    },
    getAnswer() {
        return this._el?.querySelector('input:checked')?.value || null;
    },
    grade(answer) {
        const ok = answer != null && String(answer) === String(this._correctId);
        return { correct: ok, score: ok ? this._points : 0, max: this._points };
    },
};
