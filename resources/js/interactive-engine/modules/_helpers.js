import { loadLibraries } from '../dynamic/LibraryLoader.js';
import { renderMathLabel, latexToSpeakText, isMathyLabel } from '../dynamic/mathText.js';
import { resolveSticker, stickerHtml } from '../dynamic/allowlist.js';

export function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text ?? '';
    return d.innerHTML;
}

export function escapeAttr(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

/** Visual media for an option/item (image or emoji icon). */
export function mediaVisualHtml(item, fallback = '⭐') {
    // Skip decorative icons when the option is a formula / number
    if (isMathyLabel(item?.latex || item?.math || item?.label)) {
        return '';
    }
    const imageUrl = item?.imageUrl;
    if (imageUrl) {
        return `<img class="ile-media__img" src="${escapeAttr(imageUrl)}" alt="" loading="lazy">`;
    }
    const icon = (item?.icon && String(item.icon).trim()) || fallback;
    return `<span class="ile-media__icon" aria-hidden="true">${stickerHtml(resolveSticker(icon))}</span>`;
}

/** Speaker button — plays audioUrl or speaks Arabic label. */
export function speakerButtonHtml(item) {
    const speak = latexToSpeakText(item?.label || '');
    const label = escapeAttr(speak);
    const audio = escapeAttr(item?.audioUrl || '');
    return `<button type="button" class="ile-speak" data-label="${label}" data-audio="${audio}" title="استمع" aria-label="استمع"><i class="bi bi-volume-up-fill" aria-hidden="true"></i></button>`;
}

export function bindSpeakers(root, playFn) {
    root?.querySelectorAll('.ile-speak').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            playFn?.({
                label: btn.getAttribute('data-label') || '',
                audioUrl: btn.getAttribute('data-audio') || '',
            });
        });
    });
}

/**
 * كشف الإجابة بعد التحقق: تلوين الخيار الصحيح، وتلوين الخيار الخاطئ الذي
 * اختاره الطالب فقط (باقي الخيارات تبقى محيّدة حتى لا تتحول الشاشة لفوضى ألوان).
 *
 * هوية الخيار تُقرأ من data-id (بطاقات .ile-dyn-opt) أو من قيمة input الداخلي
 * (.ile-option) — فتعمل مع النمطين بلا أي تغيير في الـ HTML.
 *
 * @param {HTMLElement} root
 * @param {{correctIds?: Array, chosenIds?: Array, selector?: string}} opts
 */
export function revealChoice(root, { correctIds = [], chosenIds = [], selector = '.ile-option' } = {}) {
    if (!root) return;

    const correct = new Set(correctIds.filter((v) => v != null).map(String));
    const chosen = new Set(chosenIds.filter((v) => v != null).map(String));

    root.querySelectorAll(selector).forEach((el) => {
        const id = el.getAttribute('data-id') ?? el.querySelector('input')?.value;
        if (id == null) return;
        const key = String(id);

        if (correct.has(key)) {
            el.classList.add('is-correct');
        } else if (chosen.has(key)) {
            el.classList.add('is-wrong');
        }
    });
}

/** يمنع تغيير الإجابة بعد كشفها. */
export function lockChoice(root, selector = '.ile-option') {
    root?.querySelectorAll(`${selector} input`).forEach((input) => {
        input.disabled = true;
    });
}

export function optionLabelHtml(opt) {
    const latex = opt?.latex || opt?.math;
    if (latex || isMathyLabel(opt?.label)) return renderMathLabel(latex || opt.label, { displayMode: false });
    return escapeHtml(opt?.label || '');
}

export function optionsNeedKatex(options = []) {
    return (options || []).some((opt) => isMathyLabel(opt?.latex || opt?.math || opt?.label));
}

/** True if any of the given item lists (payload.items, .zones, .left, .right…) contains a math-looking label. */
export function anyNeedsKatex(...lists) {
    return lists.some((list) => optionsNeedKatex(list || []));
}

/** Generic item label (drag/drop items, zones, categories, pieces, spots…) — same rendering rules as an option label. */
export function itemLabelHtml(item) {
    return optionLabelHtml(item);
}

export function optionButtons(options, { multiple = false, name = 'opt' } = {}) {
    return options
        .map(
            (opt) => `
        <label class="ile-option" data-id="${escapeAttr(opt.id)}">
            <input type="${multiple ? 'checkbox' : 'radio'}" name="${name}" value="${escapeAttr(opt.id)}">
            <span class="ile-option__media">${mediaVisualHtml(opt)}</span>
            <span class="ile-option__label">${optionLabelHtml(opt)}</span>
            ${speakerButtonHtml(opt)}
        </label>`
        )
        .join('');
}

export function createChoiceModule(type, { multiple }) {
    return {
        type,
        _el: null,
        _name: null,
        async beforeMount(ctx) {
            const options = ctx.question.payload?.options || [];
            if (optionsNeedKatex(options)) {
                await loadLibraries(['katex']);
            }
        },
        mount(el, ctx) {
            this._el = el;
            this._name = `q_${ctx.question.id}`;
            const options = ctx.question.payload?.options || [];
            el.innerHTML = `<div class="ile-options">${optionButtons(options, { multiple, name: this._name })}</div>`;
            el.querySelectorAll('input').forEach((input) => {
                input.addEventListener('change', () => {
                    el.querySelectorAll('.ile-option').forEach((lab) => lab.classList.remove('is-selected'));
                    el.querySelectorAll('input:checked').forEach((inp) => inp.closest('.ile-option')?.classList.add('is-selected'));
                    ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                });
            });
            bindSpeakers(el, ctx.playOptionAudio);
        },
        async afterMount() {},
        beforeDestroy() {},
        destroy() {
            if (this._el) this._el.innerHTML = '';
            this._el = null;
        },
        getAnswer() {
            if (!this._el) return multiple ? [] : null;
            if (multiple) {
                return [...this._el.querySelectorAll('input:checked')].map((i) => i.value);
            }
            const checked = this._el.querySelector('input:checked');
            return checked ? checked.value : null;
        },
        grade() {
            return { correct: false, score: 0, max: 1 };
        },
    };
}
