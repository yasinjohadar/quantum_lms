import { ALLOWED_LIBRARIES } from './allowlist.js';

const loaded = new Set();

/**
 * Load allowlisted presentation libraries on demand.
 * @param {string[]} keys
 */
export async function loadLibraries(keys = []) {
    const wanted = (keys || []).filter((k) => ALLOWED_LIBRARIES.includes(k));
    const tasks = [];

    for (const key of wanted) {
        if (loaded.has(key)) continue;
        if (key === 'katex') {
            tasks.push(
                Promise.all([
                    import('katex'),
                    import('katex/dist/katex.min.css'),
                ]).then(([mod]) => {
                    window.__ileKatex = mod.default || mod;
                    loaded.add('katex');
                })
            );
        } else {
            // icons/stickers/lottie/tts are always available via local assets / existing FX
            loaded.add(key);
        }
    }

    await Promise.all(tasks);
    return loaded;
}

export function getKatex() {
    return window.__ileKatex || null;
}
