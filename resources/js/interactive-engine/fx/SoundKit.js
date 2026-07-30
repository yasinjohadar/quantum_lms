/** Tiny Web-Audio synth for short UI feedback tones. No audio files. */
let sharedCtx = null;
let mutedFn = () => false;

function getCtx() {
    const AC = window.AudioContext || window.webkitAudioContext;
    if (!AC) return null;
    if (!sharedCtx) sharedCtx = new AC();
    if (sharedCtx.state === 'suspended') sharedCtx.resume().catch(() => {});
    return sharedCtx;
}

function tone(ac, { freq = 440, freqEnd = null, duration = 0.12, type = 'sine', gain = 0.16, delay = 0 } = {}) {
    const osc = ac.createOscillator();
    const amp = ac.createGain();
    const t0 = ac.currentTime + delay;
    osc.type = type;
    osc.frequency.setValueAtTime(freq, t0);
    if (freqEnd) osc.frequency.exponentialRampToValueAtTime(Math.max(freqEnd, 1), t0 + duration);
    amp.gain.setValueAtTime(0.0001, t0);
    amp.gain.exponentialRampToValueAtTime(gain, t0 + 0.012);
    amp.gain.exponentialRampToValueAtTime(0.0001, t0 + duration);
    osc.connect(amp).connect(ac.destination);
    osc.start(t0);
    osc.stop(t0 + duration + 0.02);
}

export const SoundKit = {
    /** Wire this module's mute check to FeedbackFx's existing `muted` flag (no 2nd mute mechanism). */
    bindMuteState(fn) {
        mutedFn = typeof fn === 'function' ? fn : () => false;
    },
    /** Call on first user gesture to satisfy autoplay policies (mirrors FeedbackFx.unlock()). */
    warmup() {
        try { getCtx(); } catch { /* ignore */ }
    },
    play(name) {
        if (mutedFn()) return;
        const ac = getCtx();
        if (!ac) return;
        try {
            switch (name) {
                case 'click': tone(ac, { freq: 720, duration: 0.05, type: 'square', gain: 0.10 }); break;
                case 'pop': tone(ac, { freq: 500, freqEnd: 900, duration: 0.09, type: 'sine', gain: 0.16 }); break;
                case 'ding':
                    tone(ac, { freq: 880, duration: 0.18, type: 'triangle', gain: 0.14 });
                    tone(ac, { freq: 1320, duration: 0.22, type: 'sine', gain: 0.09, delay: 0.03 });
                    break;
                case 'swoosh': tone(ac, { freq: 300, freqEnd: 80, duration: 0.2, type: 'sawtooth', gain: 0.07 }); break;
                case 'thud': tone(ac, { freq: 140, freqEnd: 60, duration: 0.15, type: 'sine', gain: 0.18 }); break;
                default: tone(ac, { freq: 600, duration: 0.08, gain: 0.10 });
            }
        } catch { /* ignore */ }
    },
};
