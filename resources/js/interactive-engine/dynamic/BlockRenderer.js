import { escapeHtml, escapeAttr } from '../modules/_helpers.js';
import { resolveSticker, stickerHtml } from './allowlist.js';
import { getKatex } from './LibraryLoader.js';

function renderText(block) {
    return `<p class="ile-block ile-block--text">${escapeHtml(block.text || '')}</p>`;
}

function renderMath(block) {
    const latex = String(block.latex || '');
    const katex = getKatex();
    if (katex) {
        try {
            const html = katex.renderToString(latex, { throwOnError: false, displayMode: true, strict: 'ignore' });
            return `<div class="ile-block ile-block--math" dir="ltr" style="unicode-bidi:isolate">${html}</div>`;
        } catch {
            /* fall through */
        }
    }
    return `<pre class="ile-block ile-block--math-fallback" dir="ltr">${escapeHtml(latex)}</pre>`;
}

function renderIconOrSticker(block) {
    const resolved = resolveSticker(block.name);
    return `<span class="ile-block ile-block--sticker" aria-hidden="true">${stickerHtml(resolved)}</span>`;
}

function renderImage(block) {
    const url = String(block.url || '');
    if (!url) return '';
    return `<img class="ile-block ile-block--image" src="${escapeAttr(url)}" alt="${escapeAttr(block.alt || '')}" loading="lazy">`;
}

function renderAudio(block) {
    const label = escapeAttr(block.text || 'استمع');
    const audio = escapeAttr(block.audioUrl || '');
    return `<button type="button" class="ile-speak ile-block ile-block--audio" data-label="${label}" data-audio="${audio}" title="استمع"><i class="bi bi-volume-up-fill" aria-hidden="true"></i> ${escapeHtml(block.text || 'استمع')}</button>`;
}

function renderScene(block) {
    const count = Math.max(0, Math.min(30, Number(block.count) || 0));
    const resolved = resolveSticker(block.item);
    const layout = block.layout === 'grid' ? 'grid' : 'row';
    const items = Array.from({ length: count }, () => `<span class="ile-scene__item">${stickerHtml(resolved)}</span>`).join('');
    return `<div class="ile-block ile-block--scene ile-scene ile-scene--${layout}" data-count="${count}" role="img" aria-label="${count}">${items}</div>`;
}

const RENDERERS = {
    text: renderText,
    math: renderMath,
    icon: renderIconOrSticker,
    sticker: renderIconOrSticker,
    image: renderImage,
    audio: renderAudio,
    scene: renderScene,
};

/**
 * @param {Array} blocks
 * @returns {string}
 */
export function renderBlocks(blocks = []) {
    if (!Array.isArray(blocks) || blocks.length === 0) return '';
    return `<div class="ile-blocks">${blocks
        .map((b) => {
            const type = b?.type;
            const fn = RENDERERS[type];
            if (!fn) return '';
            try {
                return fn(b);
            } catch {
                return `<p class="ile-block ile-block--fallback">${escapeHtml(JSON.stringify(b))}</p>`;
            }
        })
        .join('')}</div>`;
}

export function blocksToPlainText(blocks = []) {
    if (!Array.isArray(blocks)) return '';
    return blocks
        .map((b) => {
            if (b?.type === 'text') return String(b.text || '');
            if (b?.type === 'math') return String(b.latex || '');
            if (b?.type === 'scene') return `عدد ${b.count}`;
            return '';
        })
        .filter(Boolean)
        .join(' ');
}
