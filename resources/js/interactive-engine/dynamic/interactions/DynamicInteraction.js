import { escapeAttr, escapeHtml, bindSpeakers, speakerButtonHtml, optionsNeedKatex } from '../../modules/_helpers.js';
import { loadLibraries } from '../LibraryLoader.js';
import { renderBlocks } from '../BlockRenderer.js';
import { renderMathLabel, latexToSpeakText, isMathyLabel } from '../mathText.js';
import { resolveSticker, stickerHtml } from '../allowlist.js';

/**
 * Question types with a native "dynamic poster" layout in this file. Any other
 * registered type is still valid in a dynamic-mode schema — QuizSession falls
 * back to rendering it via the classic module UI instead (see registry/index.js),
 * while the rich stemBlocks content above it is unaffected either way.
 * Mirrors SchemaValidator::DYNAMIC_INTERACTION_TYPES on the backend.
 */
export const DYNAMIC_RICH_LAYOUT_TYPES = [
    'true_false',
    'single_choice',
    'multiple_choice',
    'numerical',
    'short_answer',
    'listen_choose',
];

/**
 * Resolve a presentation layout for dynamic mode (not classic templates).
 * Can be forced via interaction.layout or question.layout.
 */
export function resolveDynamicLayout(rawQuestion, classicQuestion) {
    const forced =
        rawQuestion?.interaction?.layout ||
        rawQuestion?.layout ||
        classicQuestion?.payload?.layout;
    if (forced && typeof forced === 'string') return forced;

    const type = classicQuestion?.type;
    const options = classicQuestion?.payload?.options || [];
    const blocks = rawQuestion?.stemBlocks || [];
    const hasScene = blocks.some((b) => b?.type === 'scene');
    const hasMathBlock = blocks.some((b) => b?.type === 'math');
    const mathOptions = options.filter((o) => isMathyLabel(o?.latex || o?.math || o?.label)).length;

    if (type === 'true_false') return 'truth_banners';
    if (type === 'numerical') return 'hero_keypad';
    if (type === 'short_answer') return 'hero_input';
    if (type === 'listen_choose') return 'listen_stage';
    if (mathOptions >= 2 || hasMathBlock) return 'equation_grid';
    if (hasScene) return 'scene_tiles';
    if (type === 'multiple_choice') return 'chip_multi';
    return 'poster_cards';
}

function optionVisual(opt, optionBlocks) {
    const blocks = optionBlocks?.[opt.id];
    if (Array.isArray(blocks) && blocks.length) {
        return `<div class="ile-dyn-opt__blocks">${renderBlocks(blocks)}</div>`;
    }
    const rawLabel = opt.latex || opt.math || opt.label || '';
    // Math options: KaTeX only — never mix sticker/emoji icons (they break clarity)
    if (opt.latex || opt.math || isMathyLabel(rawLabel)) {
        return `<div class="ile-dyn-opt__math" dir="ltr">${renderMathLabel(rawLabel, { displayMode: false })}</div>`;
    }
    const sticker = opt.sticker ? resolveSticker(opt.sticker) : null;
    const icon = sticker || (opt.icon ? resolveSticker(opt.icon) : null);
    return `
        <div class="ile-dyn-opt__face">
            ${icon ? `<span class="ile-dyn-opt__emoji" aria-hidden="true">${stickerHtml(icon)}</span>` : ''}
            <span class="ile-dyn-opt__text">${escapeHtml(opt.label || '')}</span>
        </div>`;
}

function speakFor(opt) {
    return latexToSpeakText(opt?.label || '');
}

function bindChoice(el, ctx, { multiple = false } = {}) {
    const selector = multiple ? 'input[type=checkbox]' : 'input[type=radio]';
    el.querySelectorAll(selector).forEach((input) => {
        input.addEventListener('change', () => {
            if (!multiple) {
                el.querySelectorAll('.ile-dyn-opt').forEach((n) => n.classList.remove('is-selected'));
            }
            el.querySelectorAll(`${selector}:checked`).forEach((inp) => {
                inp.closest('.ile-dyn-opt')?.classList.add('is-selected');
            });
            if (multiple) {
                el.querySelectorAll('.ile-dyn-opt').forEach((n) => {
                    const checked = n.querySelector('input')?.checked;
                    n.classList.toggle('is-selected', !!checked);
                });
            }
            ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
        });
    });
    el.querySelectorAll('.ile-dyn-opt').forEach((card) => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.ile-speak')) return;
            const input = card.querySelector('input');
            if (!input) return;
            if (multiple) {
                input.checked = !input.checked;
            } else {
                input.checked = true;
            }
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
    bindSpeakers(el, ctx.playOptionAudio);
}

function renderChoiceLayout(layout, options, { name, multiple, optionBlocks }) {
    const cards = options
        .map(
            (opt, i) => `
        <div class="ile-dyn-opt ile-dyn-opt--${escapeAttr(layout)}" data-id="${escapeAttr(opt.id)}">
            <input type="${multiple ? 'checkbox' : 'radio'}" name="${escapeAttr(name)}" value="${escapeAttr(opt.id)}" hidden>
            <div class="ile-dyn-opt__badge">${escapeHtml(String.fromCharCode(65 + i))}</div>
            ${optionVisual(opt, optionBlocks)}
            <button type="button" class="ile-speak ile-dyn-opt__speak" data-label="${escapeAttr(speakFor(opt))}" data-audio="${escapeAttr(opt.audioUrl || '')}" title="استمع"><i class="bi bi-volume-up-fill" aria-hidden="true"></i></button>
        </div>`
        )
        .join('');

    return `<div class="ile-dyn ile-dyn--${escapeAttr(layout)}" data-layout="${escapeAttr(layout)}">
        <div class="ile-dyn__hint">${layoutHint(layout)}</div>
        <div class="ile-dyn__grid">${cards}</div>
    </div>`;
}

function layoutHint(layout) {
    const map = {
        equation_grid: 'اختر المعادلة الصحيحة',
        scene_tiles: 'اختر العدد المطابق للمشهد',
        poster_cards: 'اختر الإجابة الأنسب',
        chip_multi: 'يمكنك اختيار أكثر من إجابة',
        listen_stage: 'استمع ثم اختر',
    };
    return map[layout] ? `<p class="ile-dyn__hint-text">${map[layout]}</p>` : '';
}

function renderTruthBanners(ctx) {
    return `<div class="ile-dyn ile-dyn--truth_banners" data-layout="truth_banners">
        <div class="ile-dyn__grid ile-dyn__grid--truth">
            <button type="button" class="ile-dyn-truth" data-val="true">
                <span class="ile-dyn-truth__icon">✅</span>
                <span class="ile-dyn-truth__label">صح</span>
                ${speakerButtonHtml({ label: 'صح' })}
            </button>
            <button type="button" class="ile-dyn-truth" data-val="false">
                <span class="ile-dyn-truth__icon">❌</span>
                <span class="ile-dyn-truth__label">خطأ</span>
                ${speakerButtonHtml({ label: 'خطأ' })}
            </button>
        </div>
    </div>`;
}

function renderHeroKeypad(ctx) {
    const p = ctx.question.payload || {};
    const unit = p.unit || '';
    return `<div class="ile-dyn ile-dyn--hero_keypad" data-layout="hero_keypad">
        <p class="ile-dyn__hint-text">${escapeHtml(p.hint || 'أدخل الناتج')}</p>
        <div class="ile-dyn-keypad">
            <div class="ile-dyn-keypad__display">
                <input type="number" class="ile-dyn-keypad__input" id="ile-dyn-num" step="any" inputmode="decimal" placeholder="؟">
                ${unit ? `<span class="ile-dyn-keypad__unit">${escapeHtml(unit)}</span>` : ''}
            </div>
            <div class="ile-dyn-keypad__pad">
                ${[1, 2, 3, 4, 5, 6, 7, 8, 9, 0]
                    .map((n) => `<button type="button" class="ile-dyn-keypad__key" data-n="${n}">${n}</button>`)
                    .join('')}
                <button type="button" class="ile-dyn-keypad__key ile-dyn-keypad__key--muted" data-n="back"><i class="bi bi-backspace-fill" aria-hidden="true"></i></button>
                <button type="button" class="ile-dyn-keypad__key ile-dyn-keypad__key--muted" data-n="clear">مسح</button>
            </div>
        </div>
    </div>`;
}

function renderHeroInput(ctx) {
    const p = ctx.question.payload || {};
    return `<div class="ile-dyn ile-dyn--hero_input" data-layout="hero_input">
        <p class="ile-dyn__hint-text">اكتب إجابتك في المربع الكبير</p>
        <input type="text" class="ile-dyn-hero-input" id="ile-dyn-short" placeholder="${escapeAttr(p.placeholder || 'إجابتي…')}" autocomplete="off">
    </div>`;
}

function renderListenStage(ctx, options, optionBlocks, name) {
    const prompt = ctx.question.payload?.prompt || {};
    const speak = prompt.text || prompt.speak || prompt.word || prompt.label || 'استمع';
    return `<div class="ile-dyn ile-dyn--listen_stage" data-layout="listen_stage">
        <button type="button" class="ile-dyn-listen" id="ile-dyn-listen" data-label="${escapeAttr(speak)}" data-audio="${escapeAttr(prompt.audioUrl || '')}">
            <span class="ile-dyn-listen__pulse"><i class="bi bi-headphones" aria-hidden="true"></i></span>
            <span>اضغط للاستماع</span>
        </button>
        <div class="ile-dyn__grid">${options
            .map(
                (opt, i) => `
            <div class="ile-dyn-opt ile-dyn-opt--listen_stage" data-id="${escapeAttr(opt.id)}">
                <input type="radio" name="${escapeAttr(name)}" value="${escapeAttr(opt.id)}" hidden>
                <div class="ile-dyn-opt__badge">${escapeHtml(String.fromCharCode(65 + i))}</div>
                ${optionVisual(opt, optionBlocks)}
                <button type="button" class="ile-speak ile-dyn-opt__speak" data-label="${escapeAttr(speakFor(opt))}" data-audio="${escapeAttr(opt.audioUrl || '')}"><i class="bi bi-volume-up-fill" aria-hidden="true"></i></button>
            </div>`
            )
            .join('')}</div>
    </div>`;
}

/**
 * Factory: dynamic presentation module (UI ≠ classic), grading matches classic contracts.
 */
export function createDynamicInteractionModule(layout) {
    return {
        type: 'dynamic_interaction',
        layout,
        _el: null,
        _points: 1,
        _qType: null,
        _correctId: null,
        _correctIds: [],
        _correctBool: true,
        _correctNum: 0,
        _tolerance: 0,
        _accepted: [],
        _multiple: false,

        async beforeMount(ctx) {
            const options = ctx.question.payload?.options || [];
            if (optionsNeedKatex(options) || layout === 'equation_grid') {
                await loadLibraries(['katex']);
            }
        },

        mount(el, ctx) {
            this._el = el;
            this._points = Number(ctx.question.points ?? 1);
            this._qType = ctx.question.type;
            const p = ctx.question.payload || {};
            const options = p.options || [];
            const optionBlocks = ctx.rawQuestion?.optionBlocks || {};
            const name = `dyn_${ctx.question.id}`;
            this._multiple = this._qType === 'multiple_choice';
            this._correctId = p.correctId;
            this._correctIds = Array.isArray(p.correctIds) ? p.correctIds.map(String) : [];
            this._correctBool = Boolean(p.correct);
            this._correctNum = Number(p.correct ?? 0);
            this._tolerance = Number(p.tolerance ?? 0);
            this._accepted = (p.acceptedAnswers || [p.correct || ''])
                .map((s) => String(s).trim().toLowerCase())
                .filter(Boolean);

            if (layout === 'truth_banners') {
                el.innerHTML = renderTruthBanners(ctx);
                el.querySelectorAll('.ile-dyn-truth').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        if (e.target.closest('.ile-speak')) return;
                        el.querySelectorAll('.ile-dyn-truth').forEach((b) => b.classList.remove('is-selected'));
                        btn.classList.add('is-selected');
                        ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                    });
                });
                bindSpeakers(el, ctx.playOptionAudio);
                return;
            }

            if (layout === 'hero_keypad') {
                el.innerHTML = renderHeroKeypad(ctx);
                const input = el.querySelector('#ile-dyn-num');
                const emit = () => ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                input?.addEventListener('input', emit);
                el.querySelectorAll('.ile-dyn-keypad__key').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        if (!input) return;
                        const n = btn.getAttribute('data-n');
                        if (n === 'clear') input.value = '';
                        else if (n === 'back') input.value = String(input.value).slice(0, -1);
                        else input.value = `${input.value}${n}`;
                        emit();
                    });
                });
                return;
            }

            if (layout === 'hero_input') {
                el.innerHTML = renderHeroInput(ctx);
                el.querySelector('#ile-dyn-short')?.addEventListener('input', () => {
                    ctx.bus.emit('answer.changed', { questionId: ctx.question.id });
                });
                return;
            }

            if (layout === 'listen_stage') {
                el.innerHTML = renderListenStage(ctx, options, optionBlocks, name);
                el.querySelector('#ile-dyn-listen')?.addEventListener('click', () => {
                    const btn = el.querySelector('#ile-dyn-listen');
                    ctx.playOptionAudio?.({
                        label: btn?.getAttribute('data-label') || '',
                        audioUrl: btn?.getAttribute('data-audio') || '',
                    });
                });
                bindChoice(el, ctx, { multiple: false });
                return;
            }

            el.innerHTML = renderChoiceLayout(layout, options, {
                name,
                multiple: this._multiple,
                optionBlocks,
            });
            bindChoice(el, ctx, { multiple: this._multiple });
        },

        async afterMount() {},
        beforeDestroy() {},
        destroy() {
            if (this._el) this._el.innerHTML = '';
            this._el = null;
        },

        getAnswer() {
            if (!this._el) return null;
            if (this._qType === 'true_false') {
                const active = this._el.querySelector('.ile-dyn-truth.is-selected');
                if (!active) return null;
                return active.getAttribute('data-val') === 'true';
            }
            if (this._qType === 'numerical') {
                const v = this._el.querySelector('#ile-dyn-num')?.value;
                if (v === '' || v == null) return null;
                return Number(v);
            }
            if (this._qType === 'short_answer') {
                return this._el.querySelector('#ile-dyn-short')?.value?.trim() || '';
            }
            if (this._multiple) {
                return [...this._el.querySelectorAll('input:checked')].map((i) => i.value);
            }
            const checked = this._el.querySelector('input:checked');
            return checked ? checked.value : null;
        },

        grade(answer) {
            const max = this._points;
            if (this._qType === 'true_false') {
                const ok = answer === this._correctBool;
                return { correct: ok, score: ok ? max : 0, max };
            }
            if (this._qType === 'numerical') {
                if (answer == null || Number.isNaN(Number(answer))) {
                    return { correct: false, score: 0, max };
                }
                const ok = Math.abs(Number(answer) - this._correctNum) <= this._tolerance;
                return { correct: ok, score: ok ? max : 0, max };
            }
            if (this._qType === 'short_answer') {
                const ok = this._accepted.includes(String(answer || '').trim().toLowerCase());
                return { correct: ok, score: ok ? max : 0, max };
            }
            if (this._multiple) {
                const got = new Set((answer || []).map(String));
                const need = new Set(this._correctIds.map(String));
                const ok = got.size === need.size && [...need].every((id) => got.has(id));
                return { correct: ok, score: ok ? max : 0, max };
            }
            const ok = answer != null && String(answer) === String(this._correctId);
            return { correct: ok, score: ok ? max : 0, max };
        },
    };
}
