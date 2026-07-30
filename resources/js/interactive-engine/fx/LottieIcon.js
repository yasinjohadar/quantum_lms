import lottie from 'lottie-web';

// Each kind rotates among an array of clips (mirrors FeedbackFx's audio-clip rotation),
// so adding more .json files later needs no further code changes.
const PATHS = {
    success: ['/lottie/ile/success.json'],
    'try-again': ['/lottie/ile/try-again.json'],
    celebrate: ['/lottie/ile/celebrate.json'],
};

function pick(list) {
    return list[Math.floor(Math.random() * list.length)];
}

/**
 * Mount a Lottie animation into a host element.
 */
export function playLottie(host, kind = 'success', { loop = false, motion = 'full' } = {}) {
    if (!host) return null;
    host.innerHTML = '';

    if (motion === 'reduced' || motion === 'off') {
        const fallback = document.createElement('div');
        fallback.className = 'ile-lottie-fallback';
        fallback.textContent = kind === 'celebrate' ? '🎉' : kind === 'try-again' ? '💪' : '⭐';
        host.appendChild(fallback);
        return null;
    }

    const list = PATHS[kind] || PATHS.success;
    const path = pick(list);
    try {
        return lottie.loadAnimation({
            container: host,
            renderer: 'svg',
            loop,
            autoplay: true,
            path,
        });
    } catch (e) {
        console.warn('[ILE] lottie failed', e);
        host.textContent = '⭐';
        return null;
    }
}

export function destroyLottie(anim) {
    try {
        anim?.destroy?.();
    } catch {
        /* ignore */
    }
}
