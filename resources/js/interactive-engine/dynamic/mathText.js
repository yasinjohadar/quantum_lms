import { escapeHtml } from '../modules/_helpers.js';
import { getKatex } from './LibraryLoader.js';

/**
 * Detect strings that should render as math (KaTeX), including
 * plain algebra without backslash commands (F = m + a, |x| < 0, 4.5).
 */
export function looksLikeLatex(text) {
    const s = String(text || '').trim();
    if (!s) return false;

    // Arabic prose / UI copy — never math
    if (/[\u0600-\u06FF]/.test(s) && !/[\\^_{}|]/.test(s)) return false;

    if (/\$[^$]+\$/.test(s)) return true;
    if (/\\\(/.test(s) || /\\\[/.test(s)) return true;
    if (/\\[a-zA-Z]+/.test(s)) return true;

    // Superscripts / subscripts
    if (/\^[{\w(]|_[{(\w]/.test(s)) return true;
    if (/[A-Za-z0-9)]\s*\^\s*[{\d(]/.test(s)) return true;

    // Absolute value |x|
    if (/\|[^|]+\|/.test(s)) return true;

    // Pure number (render in math font for consistency)
    if (/^-?\d+([.,]\d+)?$/.test(s)) return true;

    // Equations / inequalities: F = m + a , x < 0 , a ≠ b
    if (/[=<>≤≥≠≈]/.test(s) && /[A-Za-z0-9]/.test(s)) return true;

    // Arithmetic expression without equals: 2 + 3, a + b
    if (/[A-Za-z0-9]\s*[+\-×÷*/]\s*[A-Za-z0-9]/.test(s)) return true;

    return false;
}

/** Alias used by option UIs */
export function isMathyLabel(text) {
    return looksLikeLatex(text);
}

function stripDelimiters(latex) {
    let s = String(latex || '').trim();
    if (s.startsWith('$$') && s.endsWith('$$')) s = s.slice(2, -2).trim();
    else if (s.startsWith('$') && s.endsWith('$')) s = s.slice(1, -1).trim();
    else if (s.startsWith('\\(') && s.endsWith('\\)')) s = s.slice(2, -2).trim();
    else if (s.startsWith('\\[') && s.endsWith('\\]')) s = s.slice(2, -2).trim();
    return s;
}

/**
 * Normalize informal math into safer KaTeX input.
 * - |x| → \lvert x\rvert
 * - ≠ → \ne (if unicode sneaks in)
 */
export function normalizeLatex(text) {
    let s = stripDelimiters(String(text || ''));
    s = s.replace(/≠/g, '\\ne').replace(/≤/g, '\\le').replace(/≥/g, '\\ge').replace(/≈/g, '\\approx');
    // Convert simple |...| absolute values (avoid already-escaped)
    if (!/\\lvert|\\left\s*\|/.test(s)) {
        s = s.replace(/\|([^|]+)\|/g, '\\lvert $1\\rvert');
    }
    // Prefer \times if someone wrote plain letter x between vars: m x a → leave alone (ambiguous)
    return s;
}

/**
 * Render a label that may contain LaTeX into safe HTML.
 */
export function renderMathLabel(text, { displayMode = false } = {}) {
    const raw = String(text ?? '');
    if (!looksLikeLatex(raw)) {
        return escapeHtml(raw);
    }

    const latex = normalizeLatex(raw);
    const katex = getKatex();
    const wrap = (inner) =>
        `<span class="ile-math" dir="ltr" style="unicode-bidi:isolate;display:inline-block;max-width:100%;text-align:center">${inner}</span>`;

    if (!katex) {
        return wrap(`<span class="ile-math-fallback">${escapeHtml(raw)}</span>`);
    }

    try {
        const html = katex.renderToString(latex, {
            throwOnError: false,
            displayMode: Boolean(displayMode),
            strict: 'ignore',
            trust: false,
        });
        return wrap(html);
    } catch {
        return wrap(`<span class="ile-math-fallback">${escapeHtml(raw)}</span>`);
    }
}

const SUPERSCRIPT_MAP = { 0: '⁰', 1: '¹', 2: '²', 3: '³', 4: '⁴', 5: '⁵', 6: '⁶', 7: '⁷', 8: '⁸', 9: '⁹', '-': '⁻', '+': '⁺', n: 'ⁿ' };
const SUBSCRIPT_MAP = { 0: '₀', 1: '₁', 2: '₂', 3: '₃', 4: '₄', 5: '₅', 6: '₆', 7: '₇', 8: '₈', 9: '₉', '-': '₋', '+': '₊' };

function toScriptUnicode(chars, map) {
    return String(chars ?? '')
        .split('')
        .map((c) => map[c] ?? c)
        .join('');
}

/**
 * Best-effort plain-Unicode rendering of LaTeX-ish math, for contexts that
 * cannot host HTML (native <select><option> text, aria-labels, TTS captions).
 * Not a full LaTeX parser — covers the constructs this engine's blank
 * templates/AI generation actually produce.
 */
export function mathPlainText(text) {
    const raw = String(text ?? '');
    if (!looksLikeLatex(raw)) return raw;

    let s = normalizeLatex(raw);
    s = s
        .replace(/\\frac\{([^{}]*)\}\{([^{}]*)\}/g, '$1⁄$2')
        .replace(/\\sqrt\{([^{}]*)\}/g, '√($1)')
        .replace(/\\lvert\s*([^\\]+?)\s*\\rvert/g, '|$1|')
        .replace(/\\times/g, '×')
        .replace(/\\cdot/g, '⋅')
        .replace(/\\div/g, '÷')
        .replace(/\\pm/g, '±')
        .replace(/\\pi/g, 'π')
        .replace(/\\alpha/g, 'α')
        .replace(/\\beta/g, 'β')
        .replace(/\\theta/g, 'θ')
        .replace(/\\ne/g, '≠')
        .replace(/\\le/g, '≤')
        .replace(/\\ge/g, '≥')
        .replace(/\\approx/g, '≈')
        .replace(/\\sin/g, 'sin')
        .replace(/\\cos/g, 'cos')
        .replace(/\\tan/g, 'tan')
        .replace(/\^\{([^{}]*)\}/g, (_, p1) => toScriptUnicode(p1, SUPERSCRIPT_MAP))
        .replace(/\^(\w)/g, (_, p1) => toScriptUnicode(p1, SUPERSCRIPT_MAP))
        .replace(/_\{([^{}]*)\}/g, (_, p1) => toScriptUnicode(p1, SUBSCRIPT_MAP))
        .replace(/_(\w)/g, (_, p1) => toScriptUnicode(p1, SUBSCRIPT_MAP))
        .replace(/\\[a-zA-Z]+/g, ' ')
        .replace(/[{}]/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    return s || raw;
}

export function latexToSpeakText(text) {
    let s = normalizeLatex(String(text || ''));
    s = s
        .replace(/\\times/g, ' في ')
        .replace(/\\cdot/g, ' في ')
        .replace(/\\div/g, ' على ')
        .replace(/\\frac\{([^}]+)\}\{([^}]+)\}/g, '$1 على $2')
        .replace(/\\sqrt\{([^}]+)\}/g, 'جذر $1')
        .replace(/\\lvert\s*([^\\]+)\\rvert/g, 'القيمة المطلقة لـ $1')
        .replace(/\\ne\b/g, ' لا يساوي ')
        .replace(/\\le\b/g, ' أصغر أو يساوي ')
        .replace(/\\ge\b/g, ' أكبر أو يساوي ')
        .replace(/\\sin/g, 'جا ')
        .replace(/\\cos/g, 'جتا ')
        .replace(/\\tan/g, 'ظا ')
        .replace(/\\[a-zA-Z]+/g, ' ')
        .replace(/[{}^_]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    return s || String(text || '');
}
