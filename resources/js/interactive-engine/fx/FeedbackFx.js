/**
 * Kid-friendly FX: real Arabic spoken voice clips + Lottie + visuals.
 * No music / no synth beeps — كلام صوتي فقط.
 */
import confetti from 'canvas-confetti';
import { playLottie, destroyLottie } from './LottieIcon.js';
import { SoundKit } from './SoundKit.js';
import { showFlourish } from './Flourish.js';
import { buildPhraseIndex, lookupPhrase } from './phraseKey.js';

const DEFAULT_CONFETTI_COLORS = ['#f59e0b', '#22c55e', '#3b82f6', '#ec4899', '#a855f7', '#14b8a6', '#ef4444'];

export class FeedbackFx {
    constructor(options = {}) {
        this.motion = options.motion || 'full';
        this.passThreshold = Number(options.passThreshold ?? 50);
        this.voiceEnabled = options.voice !== false;
        this.ttsUrl = options.ttsUrl || '';
        // الصوت مفعّل دوماً بلا خيار كتم — لا يُقرأ ولا يُحفظ أي تفضيل كتم سابق.
        this.muted = false;
        this._audio = null;
        this._cache = new Map();
        this._lottie = null;
        this._confettiFns = new WeakMap();
        SoundKit.bindMuteState(() => this.muted);

        /**
         * عبارات التغذية الراجعة مع تسجيلاتها، قادمة من السيرفر
         * (App\InteractiveLearning\Support\FeedbackPhrases::forPlayer) عبر
         * window.__interactiveConfig.feedbackPhrases — كل عبارة مقرونة بالملف
         * الذي ينطقها حرفياً، فيبقى المكتوب على الشاشة هو المسموع.
         */
        const phraseSets = options.feedbackPhrases || {};
        const successPool = Array.isArray(phraseSets.success) ? phraseSets.success : [];
        const failPool = Array.isArray(phraseSets.fail) ? phraseSets.fail : [];

        this.pools = {
            success: successPool,
            wrong: failPool,
            pass: successPool, // نهاية الاختبار بنجاح تعيد استخدام تسجيلات النجاح
            retry: failPool, // ونهايته برسوب تعيد استخدام تسجيلات الفشل
            continue: [], // زر «يلا نكمّل» بلا تسجيل حقيقي بعد — يُنطق آلياً (انظر ttsFallbackKinds)
        };

        const successIndex = buildPhraseIndex(successPool);
        const failIndex = buildPhraseIndex(failPool);
        this.phraseIndex = {
            success: successIndex,
            pass: successIndex,
            wrong: failIndex,
            retry: failIndex,
            continue: new Map(),
        };

        /**
         * أنواع بلا تسجيلات صوتية حقيقية بعد — تُنطق آلياً (TTS) كحل مؤقت
         * إلى حين تسجيل مقاطع صوتية حقيقية لها. لا يشمل success/wrong: النطق
         * الآلي ممنوع صراحة في تغذية الإجابة (انظر playPhrase أدناه).
         */
        this.ttsFallbackKinds = new Set(['continue']);

        /** نص احتياطي يُستخدم فقط إذا لم تصل خريطة العبارات من السيرفر. */
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
        const urls = new Set();
        Object.values(this.pools).forEach((pool) => {
            (pool || []).forEach((row) => {
                if (row?.url) urls.add(row.url);
            });
        });
        urls.forEach((url) => {
            if (this._cache.has(url)) return;
            const a = new Audio(url);
            a.preload = 'auto';
            this._cache.set(url, a);
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
     * حدّد العبارة وتسجيلها: العبارة المكتوبة في السؤال إن كان لها تسجيل،
     * وإلا نصّها كما هو (يُنطق آلياً)، وإلا عبارة عشوائية من مجموعة النوع.
     *
     * @param {'success'|'wrong'|'continue'|'pass'|'retry'} kind
     * @param {string} authoredText رسالة السؤال المحفوظة (successMessage / errorMessage)
     * @returns {{text: string, url: string}}
     */
    resolvePhrase(kind, authoredText = '') {
        const pool = this.pools[kind] || this.pools.success;
        const index = this.phraseIndex[kind] || this.phraseIndex.success;
        const authored = String(authoredText || '').trim();

        if (authored) {
            const hit = lookupPhrase(index, authored);
            if (hit) return { text: hit.text, url: hit.url || '' };
            // رسالة خارج القائمة (لا يُتوقع حدوثها بعد الترحيل والفرض على السيرفر):
            // يُعرض نصها بلا صوت — لا نستبدلها ولا نستعين بنطق آلي
            return { text: authored, url: '' };
        }

        if (pool.length) {
            const row = pool[this.pickIndex(pool)];
            return { text: row.text, url: row.url || '' };
        }

        const fallback = this.phrases[kind] || this.phrases.success;
        return { text: this.pick(fallback), url: '' };
    }

    /**
     * شغّل تسجيل العبارة وأعِد النص الذي سُمع فعلاً — على المُنادي أن يعرض
     * هذا النص بالذات حتى لا يختلف المكتوب عن المسموع.
     *
     * @param {'success'|'wrong'|'continue'|'pass'|'retry'} kind
     * @param {string} authoredText
     * @returns {string} النص المطابق للصوت
     */
    playPhrase(kind, authoredText = '') {
        const { text, url } = this.resolvePhrase(kind, authoredText);

        if (!this.voiceEnabled || this.muted) return text;

        this.unlock();
        this.stopVoice();

        if (!url) {
            // لا تسجيل مطابق: success/wrong يبقيان صامتين عمداً — ممنوع النطق
            // الآلي في تغذية الإجابة، فالمسموع عندهما يجب أن يكون من
            // التسجيلات العشرين فقط. الأنواع الأخرى (كـ continue، بلا
            // تسجيلات بعد) تُنطق آلياً كحل مؤقت بدل البقاء صامتة دوماً.
            if (this.ttsFallbackKinds.has(kind)) this.speakLabel(text);
            return text;
        }

        this.playRecordedAudio(url);
        return text;
    }

    /**
     * تشغيل تسجيل بمسار الصوت المحدَّد، مع إعادة محاولة واحدة عند الفشل
     * (كإلغاء التشغيل بسبب تداخل مقطع سابق لم ينتهِ) بدل الاستسلام صامتاً
     * من أول فشل — يقلّل حالات "أحياناً لا نسمع الصوت" رغم وجود التسجيل.
     */
    playRecordedAudio(url, isRetry = false) {
        let audio = this._cache.get(url);
        if (!audio) {
            audio = new Audio(url);
            this._cache.set(url, audio);
        }
        this._audio = audio;
        try {
            audio.currentTime = 0;
        } catch {
            /* ignore */
        }
        audio.volume = 1;
        const play = audio.play();
        if (play?.catch) {
            play.catch(() => {
                if (!isRetry) {
                    setTimeout(() => this.playRecordedAudio(url, true), 120);
                }
            });
        }
    }

    /**
     * تسجيل عشوائي من مجموعة النوع بلا رسالة محدّدة.
     * @param {'success'|'wrong'|'continue'|'pass'|'retry'} kind
     * @returns {string} matching Arabic phrase for on-screen text
     */
    playVoice(kind) {
        return this.playPhrase(kind, '');
    }

    /**
     * Visual + spoken Arabic clip + Lottie (no music).
     *
     * `phrase` هي رسالة السؤال المحفوظة؛ المعروض هو ما نطقه التسجيل بالضبط
     * (playPhrase تُرجع النص القانوني للتسجيل) فلا يحدث اختلاف بين المكتوب والمسموع.
     */
    cheer(root, { correct = true, big = false, phrase = '', color = '' } = {}) {
        const kind = big
            ? (correct ? 'pass' : 'retry')
            : (correct ? 'success' : 'wrong');
        const spoken = this.playPhrase(kind, phrase);
        const display = spoken || phrase || '';

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
