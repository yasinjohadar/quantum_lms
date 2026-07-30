/**
 * Kid-friendly FX: real Arabic spoken voice clips + Lottie + visuals.
 * No music / no synth beeps — كلام صوتي فقط.
 */
import confetti from 'canvas-confetti';
import { playLottie, destroyLottie } from './LottieIcon.js';
import { SoundKit } from './SoundKit.js';
import { showFlourish } from './Flourish.js';

const DEFAULT_CONFETTI_COLORS = ['#f59e0b', '#22c55e', '#3b82f6', '#ec4899', '#a855f7', '#14b8a6', '#ef4444'];

export class FeedbackFx {
    constructor(options = {}) {
        this.motion = options.motion || 'full';
        this.passThreshold = Number(options.passThreshold ?? 50);
        this.voiceEnabled = options.voice !== false;
        this.baseUrl = (options.soundsBaseUrl || '/sounds/ile').replace(/\/$/, '');
        this.ttsUrl = options.ttsUrl || '';
        this.muted = this.readMuted();
        this._audio = null;
        this._cache = new Map();
        this._lottie = null;
        this._confettiFns = new WeakMap();
        SoundKit.bindMuteState(() => this.muted);
        this.clips = {
            success: [
                'success-01.mp3',
                'success-02.mp3',
                'success-03.mp3',
                'success-04.mp3',
                'success-05.mp3',
                'success-06.mp3',
                'success-07.mp3',
                'success-08.mp3',
                'success-09.mp3',
            ],
            wrong: [
                'wrong-01.mp3',
                'wrong-02.mp3',
                'wrong-03.mp3',
                'wrong-04.mp3',
                'wrong-05.mp3',
            ],
            continue: [
                'continue-01.mp3',
                'continue-02.mp3',
                'continue-03.mp3',
                'continue-04.mp3',
            ],
            pass: [
                'pass-01.mp3',
                'pass-02.mp3',
                'pass-03.mp3',
                'pass-04.mp3',
            ],
            retry: [
                'retry-01.mp3',
                'retry-02.mp3',
                'retry-03.mp3',
            ],
        };
        this.phrases = {
            success: [
                'يا بطل!',
                'أحسنت يا شاطر!',
                'ممتاز جداً!',
                'أنت نجم!',
                'رائع!',
                'صح! برافو!',
                'عاش! أنت مبدع!',
                'تسلم إيدك!',
                'ذكي جداً!',
            ],
            wrong: [
                'جرّب مرة ثانية!',
                'أنت قادر!',
                'فكّر بهدوء',
                'لا بأس، جرّب!',
                'قرّبنا، حاول!',
            ],
            continue: ['يلا نكمّل!', 'تابع يا بطل!', 'السؤال التالي!', 'هيا بنا!'],
            pass: [
                'تصفيق لك يا بطل!',
                'نجحت! أنت نجم الصف!',
                'مبروك النجاح!',
                'أنت فائز اليوم!',
            ],
            retry: [
                'لا بأس، أعد المحاولة!',
                'جرّب من جديد يا شاطر!',
                'أنت تقدر أحسن!',
            ],
        };
        this.preload();
    }

    readMuted() {
        try {
            return localStorage.getItem('ile_fx_muted') === '1';
        } catch {
            return false;
        }
    }

    setMuted(muted) {
        this.muted = Boolean(muted);
        try {
            localStorage.setItem('ile_fx_muted', this.muted ? '1' : '0');
        } catch {
            /* ignore */
        }
        if (this.muted) this.stopVoice();
        if (window.speechSynthesis) {
            try {
                window.speechSynthesis.cancel();
            } catch {
                /* ignore */
            }
        }
    }

    toggleMute() {
        this.setMuted(!this.muted);
        return this.muted;
    }

    unlock() {
        // Browsers require a user gesture before audio can play.
        if (!this._audio) this._audio = new Audio();
        try {
            this._audio.muted = true;
            const p = this._audio.play();
            if (p?.then) {
                p.then(() => {
                    this._audio.pause();
                    this._audio.currentTime = 0;
                    this._audio.muted = false;
                }).catch(() => {});
            }
        } catch {
            /* ignore */
        }
        SoundKit.warmup();
    }

    /** Play a short procedurally-generated micro sound effect (see SoundKit.js). Additive to voice clips. */
    playSfx(name) {
        SoundKit.play(name);
    }

    preload() {
        Object.values(this.clips).flat().forEach((file) => {
            if (this._cache.has(file)) return;
            const a = new Audio(`${this.baseUrl}/${file}`);
            a.preload = 'auto';
            this._cache.set(file, a);
        });
    }

    canAnimate() {
        return this.motion !== 'reduced' && this.motion !== 'off';
    }

    pick(list) {
        return list[Math.floor(Math.random() * list.length)];
    }

    pickIndex(list) {
        return Math.floor(Math.random() * list.length);
    }

    stopVoice() {
        if (this._audio) {
            try {
                this._audio.pause();
                this._audio.currentTime = 0;
            } catch {
                /* ignore */
            }
        }
        this._cache.forEach((a) => {
            try {
                a.pause();
                a.currentTime = 0;
            } catch {
                /* ignore */
            }
        });
    }

    /**
     * Play a recorded Arabic voice clip.
     * @param {'success'|'wrong'|'continue'|'pass'|'retry'} kind
     * @returns {string} matching Arabic phrase for on-screen text
     */
    playVoice(kind) {
        const files = this.clips[kind] || this.clips.success;
        const phrases = this.phrases[kind] || this.phrases.success;
        const i = this.pickIndex(files);
        const phrase = phrases[i] || phrases[0];
        const file = files[i];

        if (!this.voiceEnabled || this.muted) return phrase;

        this.unlock();
        this.stopVoice();

        const src = `${this.baseUrl}/${file}`;
        let audio = this._cache.get(file);
        if (!audio) {
            audio = new Audio(src);
            this._cache.set(file, audio);
        }
        this._audio = audio;
        audio.currentTime = 0;
        audio.volume = 1;
        const play = audio.play();
        if (play?.catch) play.catch(() => {});
        return phrase;
    }

    kidSuccessPhrase(custom) {
        if (custom && String(custom).trim()) return String(custom).split(/[—–|]/)[0].trim();
        return this.pick(this.phrases.success);
    }

    kidWrongPhrase(custom) {
        if (custom && String(custom).trim()) return String(custom).split(/[—–|]/)[0].trim();
        return this.pick(this.phrases.wrong);
    }

    kidPassPhrase() {
        return this.pick(this.phrases.pass);
    }

    kidRetryPhrase() {
        return this.pick(this.phrases.retry);
    }

    /**
     * Visual + spoken Arabic clip + Lottie (no music).
     */
    cheer(root, { correct = true, big = false, phrase = '', color = '' } = {}) {
        const kind = big
            ? (correct ? 'pass' : 'retry')
            : (correct ? 'success' : 'wrong');
        const spoken = this.playVoice(kind);
        const display = phrase || spoken;

        const lottieKind = big
            ? (correct ? 'celebrate' : 'try-again')
            : (correct ? 'success' : 'try-again');
        const lottieHost = root?.querySelector('#ile-lottie')
            || root?.querySelector('#ile-results-lottie')
            || null;
        destroyLottie(this._lottie);
        this._lottie = playLottie(lottieHost, lottieKind, {
            loop: false,
            motion: this.motion,
        });

        if (!root || !this.canAnimate()) return display;

        this.showFloatingWords(root, {
            words: correct ? [display, '⭐', '🌟', '👏'] : [display, '💪', '✨'],
            count: big ? 10 : 6,
            tone: correct ? 'ok' : 'soft',
        });
        this.burstConfetti(root, {
            count: big ? 70 : correct ? 32 : 12,
            colors: color ? this.paletteForColor(color) : undefined,
            big: big && correct,
        });
        if (correct) this.pulseFeedback(root, color);
        if (correct && !big) showFlourish(root, { color });
        if (big && correct) this.showClapBadge(root, display);
        if (!correct) this.shakeSoft(root);
        return display;
    }

    speakContinue() {
        return this.playVoice('continue');
    }

    /** Play option audioUrl or speak label (Arabic via server TTS). */
    playOptionAudio({ label = '', audioUrl = '' } = {}) {
        if (this.muted || !this.voiceEnabled) return;
        this.unlock();
        this.stopVoice();

        if (audioUrl) {
            const audio = new Audio(audioUrl);
            this._audio = audio;
            const p = audio.play();
            if (p?.catch) p.catch(() => this.speakLabel(label));
            return;
        }
        this.speakLabel(label);
    }

    containsArabic(text) {
        return /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]/.test(String(text || ''));
    }

    speakLabel(label) {
        const text = String(label || '').trim();
        if (!text || this.muted) return;

        // Prefer server TTS for Arabic (browser voices often missing)
        if (this.ttsUrl && this.containsArabic(text)) {
            this.playServerTts(text, 'ar');
            return;
        }

        // Mixed/English: try browser first, Arabic fallback to server if utterance silent
        if (this.ttsUrl && !this.containsArabic(text)) {
            // English/latin — browser usually works; still allow server en
            if (window.speechSynthesis) {
                this.speakBrowser(text, 'en-US');
                return;
            }
            this.playServerTts(text, 'en');
            return;
        }

        if (this.ttsUrl) {
            this.playServerTts(text, this.containsArabic(text) ? 'ar' : 'en');
            return;
        }

        this.speakBrowser(text, this.containsArabic(text) ? 'ar-SA' : 'en-US');
    }

    playServerTts(text, lang = 'ar') {
        const url = `${this.ttsUrl}?text=${encodeURIComponent(text)}&lang=${encodeURIComponent(lang)}`;
        const audio = new Audio(url);
        this._audio = audio;
        const p = audio.play();
        if (p?.catch) {
            p.catch(() => this.speakBrowser(text, lang === 'ar' ? 'ar-SA' : 'en-US'));
        }
    }

    speakBrowser(text, lang = 'ar-SA') {
        if (!window.speechSynthesis || this.muted) return;
        try {
            window.speechSynthesis.cancel();
            const u = new SpeechSynthesisUtterance(String(text));
            u.lang = lang;
            u.rate = 0.95;
            u.pitch = 1.1;
            const voices = window.speechSynthesis.getVoices() || [];
            const prefix = lang.slice(0, 2).toLowerCase();
            const match =
                voices.find((v) => v.lang?.toLowerCase().startsWith(prefix)) ||
                voices.find((v) => new RegExp(prefix, 'i').test(v.name || ''));
            if (match) u.voice = match;
            window.speechSynthesis.speak(u);
        } catch {
            /* ignore */
        }
    }

    playInstructions(schema) {
        const url = schema?.meta?.instructionsAudioUrl;
        if (url) {
            this.playOptionAudio({ audioUrl: url, label: 'تعليمات' });
            return;
        }
        const title = schema?.meta?.title || 'مغامرة تعليمية';
        this.speakLabel(`مرحباً! هذه مغامرة ${title}. اقرأ السؤال واختر الإجابة ثم اضغط يلا نتحقق`);
    }

    showFloatingWords(root, { words, count = 6, tone = 'ok' } = {}) {
        const host = root.querySelector('.ile-app') || root;
        const layer = document.createElement('div');
        layer.className = `ile-cheer ile-cheer--${tone}`;
        layer.setAttribute('aria-hidden', 'true');
        for (let i = 0; i < count; i++) {
            const el = document.createElement('span');
            el.className = 'ile-cheer__word';
            el.textContent = words[i % words.length];
            el.style.setProperty('--x', `${8 + Math.random() * 84}%`);
            el.style.setProperty('--d', `${0.9 + Math.random() * 1.1}s`);
            el.style.setProperty('--delay', `${i * 0.05}s`);
            el.style.setProperty('--scale', `${0.85 + Math.random() * 0.55}`);
            layer.appendChild(el);
        }
        host.appendChild(layer);
        setTimeout(() => layer.remove(), 2400);
    }

    pulseFeedback(root, color) {
        const el = root.querySelector('#ile-feedback');
        if (!el) return;
        el.style.setProperty('--ile-glow-rgba', this.hexToRgba(color || '#22c55e', 0.45));
        el.classList.remove('ile-feedback--pop');
        void el.offsetWidth;
        el.classList.add('ile-feedback--pop');
    }

    /** Small palette derived from a per-type accent color: accent + white + warm gold. */
    paletteForColor(hex) {
        return [hex, '#ffffff', '#fde047'];
    }

    hexToRgba(hex, alpha) {
        const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex || '');
        if (!m) return `rgba(34,197,94,${alpha})`;
        const [r, g, b] = m.slice(1).map((h) => parseInt(h, 16));
        return `rgba(${r},${g},${b},${alpha})`;
    }

    shakeSoft(root) {
        const main = root.querySelector('.ile-main');
        if (!main) return;
        main.classList.remove('ile-shake');
        void main.offsetWidth;
        main.classList.add('ile-shake');
        setTimeout(() => main.classList.remove('ile-shake'), 500);
    }

    showClapBadge(root, label) {
        const host = root.querySelector('#ile-results') || root.querySelector('.ile-app') || root;
        const badge = document.createElement('div');
        badge.className = 'ile-clap';
        badge.setAttribute('aria-hidden', 'true');
        badge.innerHTML = `
            <div class="ile-clap__hands">👏🎉</div>
            <div class="ile-clap__label">${escapeText(label || 'تصفيق لك يا بطل!')}</div>`;
        host.appendChild(badge);
        setTimeout(() => badge.remove(), 3400);
    }

    /** Get (or lazily create) a canvas-scoped confetti instance for this session's root. */
    getConfetti(root) {
        const host = root.querySelector('.ile-app') || root;
        let canvas = host.querySelector('.ile-confetti-canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.className = 'ile-confetti-canvas';
            host.appendChild(canvas);
        }
        let fire = this._confettiFns.get(canvas);
        if (!fire) {
            fire = confetti.create(canvas, { resize: true, useWorker: true });
            this._confettiFns.set(canvas, fire);
        }
        return fire;
    }

    burstConfetti(root, { count = 30, colors, big = false } = {}) {
        if (!root || !this.canAnimate()) return;
        const fire = this.getConfetti(root);
        const palette = colors && colors.length ? colors : DEFAULT_CONFETTI_COLORS;

        if (!big) {
            fire({
                particleCount: count,
                spread: 65,
                startVelocity: 38,
                gravity: 1,
                scalar: 0.9,
                ticks: 180,
                origin: { y: 0.65 },
                colors: palette,
            });
            return;
        }

        // Dramatic multi-burst "side cannons" for a full quiz pass.
        fire({ particleCount: Math.round(count / 2), spread: 100, startVelocity: 45, origin: { y: 0.55 }, colors: palette });
        const end = Date.now() + 800;
        (function frame() {
            fire({ particleCount: Math.round(count / 8), angle: 60, spread: 55, origin: { x: 0, y: 0.7 }, colors: palette });
            fire({ particleCount: Math.round(count / 8), angle: 120, spread: 55, origin: { x: 1, y: 0.7 }, colors: palette });
            if (Date.now() < end) requestAnimationFrame(frame);
        })();
    }
}

function escapeText(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
