/** Lightweight SVG/CSS success flourishes — no external assets, no Lottie dependency. */
const KINDS = ['ring', 'check'];

const RING_SVG = `<svg viewBox="0 0 100 100" class="ile-flourish__svg" aria-hidden="true">
    <circle cx="50" cy="50" r="10" class="ile-flourish__ring" /></svg>`;

const CHECK_SVG = `<svg viewBox="0 0 100 100" class="ile-flourish__svg" aria-hidden="true">
    <path d="M22 52 L42 72 L80 30" class="ile-flourish__check" /></svg>`;

export function showFlourish(root, { kind, color = '' } = {}) {
    const host = root?.querySelector('.ile-app') || root;
    if (!host) return;
    const pick = kind || KINDS[Math.floor(Math.random() * KINDS.length)];
    const wrap = document.createElement('div');
    wrap.className = `ile-flourish ile-flourish--${pick}`;
    wrap.setAttribute('aria-hidden', 'true');
    if (color) wrap.style.setProperty('--flourish-color', color);
    wrap.innerHTML = pick === 'ring' ? RING_SVG : CHECK_SVG;
    host.appendChild(wrap);
    window.setTimeout(() => wrap.remove(), 900);
}
