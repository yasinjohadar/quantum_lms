import { EventBus, ENGINE_VERSION } from '../core/event-bus.js';
import { getModule, registryMetadata } from '../registry/index.js';
import { escapeHtml } from '../modules/_helpers.js';
import { FeedbackFx } from '../fx/FeedbackFx.js';
import { renderBlocks } from '../dynamic/BlockRenderer.js';
import { bindSpeakers } from '../modules/_helpers.js';
import { loadLibraries } from '../dynamic/LibraryLoader.js';
import { toClassicQuestion, isDynamicSchema, collectLibraries } from '../dynamic/toClassicQuestion.js';
import { resolveDynamicLayout, createDynamicInteractionModule, DYNAMIC_RICH_LAYOUT_TYPES } from '../dynamic/interactions/DynamicInteraction.js';

export class QuizSession {
    constructor(schema, config = {}) {
        this.schema = schema;
        this.config = config;
        this.isDynamic = isDynamicSchema(schema);
        this.bus = new EventBus();
        this.rules = schema.rules || {};
        this.questions = [...(schema.questions || [])];
        if (this.rules.shuffleQuestions) {
            this.questions.sort(() => Math.random() - 0.5);
        }
        this.index = 0;
        this.answers = [];
        this.score = 0;
        this.total = this.questions.reduce((sum, q) => {
            const classic = this.isDynamic ? toClassicQuestion(q) : q;
            return sum + Number(classic.points ?? q.points ?? 1);
        }, 0);
        this.wrongCount = 0;
        this.streak = 0;
        this.bestStreak = 0;
        this.startedAt = null;
        this.finishedAt = null;
        this.currentModule = null;
        this.root = null;
        this.attemptsUsed = {};
        this.fx = new FeedbackFx({
            motion: schema.theme?.motion || 'full',
            passThreshold: config.passThreshold ?? 50,
            ttsUrl: config.ttsUrl || '',
            feedbackPhrases: config.feedbackPhrases || {},
        });
    }

    /** Per-type accent color from the module registry, used to tint celebration FX. */
    typeColor(type) {
        const meta = registryMetadata.find((m) => m.type === type);
        return meta?.color || '';
    }

    async start(root) {
        this.root = root;
        this.startedAt = new Date();
        this.bus.emit('session.started', { at: this.startedAt.toISOString() });
        this.renderShell();
        await this.showQuestion(0);
    }

    renderShell() {
        const theme = this.schema.theme || {};
        const themeId = !theme.themeId || theme.themeId === 'default' ? 'kids' : theme.themeId;
        const mode = this.isDynamic ? 'dynamic' : 'classic';
        this.root.innerHTML = `
            <div class="ile-app" data-theme-id="${escapeHtml(themeId)}" data-mode="${mode}" data-density="${escapeHtml(theme.density || 'comfortable')}" data-motion="${escapeHtml(theme.motion || 'full')}">
                <header class="ile-header">
                    <div class="ile-header__title">${escapeHtml(this.schema.meta?.title || 'مغامرة تعليمية')}</div>
                    <p class="ile-header__tagline">${this.isDynamic ? 'عرض ديناميكي · استكشف وتعلّم!' : 'العب، استمع، واكتشف!'}</p>
                    <button type="button" class="ile-btn ile-btn--listen" id="ile-instructions"><i class="bi bi-volume-up-fill" aria-hidden="true"></i> استمع للتعليمات</button>
                    <div class="ile-progress-wrap">
                        <div class="ile-progress-label">نسبة التقدم <strong id="ile-progress-pct">0%</strong></div>
                        <div class="ile-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                            <div class="ile-progress__bar" id="ile-progress-bar"></div>
                        </div>
                    </div>
                    <div class="ile-header__meta">
                        <span id="ile-step"></span>
                        <span class="ile-hud ile-hud--score" id="ile-score" title="نجومك">⭐ <strong id="ile-score-value">0</strong></span>
                        <span class="ile-hud ile-hud--streak" id="ile-streak" title="إجابات صحيحة متتالية" hidden>🔥 <strong id="ile-streak-value">0</strong></span>
                        <button type="button" class="ile-mute" id="ile-mute" title="${this.fx.muted ? 'تشغيل الصوت' : 'كتم الصوت'}" aria-label="الصوت"><i class="bi ${this.fx.muted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}" aria-hidden="true"></i></button>
                    </div>
                </header>
                <main class="ile-main">
                    <div class="ile-stem" id="ile-stem"></div>
                    <div class="ile-module" id="ile-module"></div>
                    <div class="ile-feedback" id="ile-feedback" hidden>
                        <div class="ile-lottie" id="ile-lottie" aria-hidden="true"></div>
                        <div class="ile-feedback__body">
                            <span class="ile-feedback__text" id="ile-feedback-text"></span>
                            <div class="ile-feedback__hint" id="ile-feedback-hint" hidden></div>
                        </div>
                    </div>
                </main>
                <footer class="ile-footer">
                    <button type="button" class="ile-btn ile-btn--ghost" id="ile-back" ${this.rules.allowBack === false ? 'hidden' : ''}>رجوع</button>
                    <button type="button" class="ile-btn ile-btn--primary" id="ile-submit">يلا نتحقق!</button>
                    <button type="button" class="ile-btn ile-btn--primary ile-btn--next" id="ile-next" hidden>يلا نكمّل! 🚀</button>
                </footer>
                <div class="ile-results" id="ile-results" hidden></div>
            </div>`;

        this.root.querySelector('#ile-back')?.addEventListener('click', () => this.prev());
        this.root.querySelector('#ile-submit')?.addEventListener('click', () => {
            this.fx.unlock();
            this.check();
        });
        this.root.querySelector('#ile-next')?.addEventListener('click', () => {
            this.fx.speakContinue();
            this.next();
        });
        this.root.querySelector('#ile-instructions')?.addEventListener('click', () => {
            this.fx.unlock();
            this.fx.playInstructions(this.schema);
        });
        this.root.querySelector('#ile-mute')?.addEventListener('click', () => {
            const muted = this.fx.toggleMute();
            const btn = this.root.querySelector('#ile-mute');
            if (btn) {
                btn.innerHTML = `<i class="bi ${muted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}" aria-hidden="true"></i>`;
                btn.title = muted ? 'تشغيل الصوت' : 'كتم الصوت';
            }
            if (!muted) this.fx.playVoice('success');
        });
        this.root.addEventListener('pointerdown', () => this.fx.unlock(), { once: true });
    }

    updateProgress() {
        const pct = this.questions.length ? Math.round(((this.index + 1) / this.questions.length) * 100) : 0;
        const bar = this.root.querySelector('#ile-progress-bar');
        if (bar) bar.style.width = `${pct}%`;
        const pctEl = this.root.querySelector('#ile-progress-pct');
        if (pctEl) pctEl.textContent = `${pct}%`;
        const step = this.root.querySelector('#ile-step');
        if (step) step.textContent = `سؤال ${this.index + 1} من ${this.questions.length}`;
    }

    /** مجموع النجوم المكتسبة حتى الآن — مشتق من this.answers فلا يتعارض مع حساب complete(). */
    earnedScore() {
        return this.answers.reduce((sum, a) => sum + Number(a.score || 0), 0);
    }

    /**
     * تحديث رقاقتَي النجوم والسلسلة في الرأس.
     * @param {boolean} bump هل نُشغّل حركة القفزة (عند كسب نجوم جديدة)
     */
    updateHud({ bump = false } = {}) {
        const earned = this.earnedScore();

        const scoreEl = this.root?.querySelector('#ile-score');
        const scoreValue = this.root?.querySelector('#ile-score-value');
        if (scoreValue) scoreValue.textContent = String(Math.round(earned * 10) / 10);
        if (scoreEl && bump) {
            scoreEl.classList.remove('ile-hud--bump');
            void scoreEl.offsetWidth; // إعادة تشغيل الحركة
            scoreEl.classList.add('ile-hud--bump');
        }

        // السلسلة تظهر كمكافأة من الإجابة الصحيحة الثانية المتتالية
        const streakEl = this.root?.querySelector('#ile-streak');
        const streakValue = this.root?.querySelector('#ile-streak-value');
        if (streakValue) streakValue.textContent = String(this.streak);
        if (streakEl) {
            const show = this.streak >= 2;
            streakEl.hidden = !show;
            if (show && bump) {
                streakEl.classList.remove('ile-hud--bump');
                void streakEl.offsetWidth;
                streakEl.classList.add('ile-hud--bump');
            }
        }
    }

    async destroyCurrent() {
        if (!this.currentModule) return;
        const q = this.questions[this.index];
        this.bus.emit('question.leave', { questionId: q?.id });
        await this.currentModule.beforeDestroy?.();
        this.currentModule.destroy();
        this.currentModule = null;
    }

    async showQuestion(index) {
        await this.destroyCurrent();
        this.index = index;
        const raw = this.questions[index];
        if (!raw) {
            await this.complete();
            return;
        }

        const question = this.isDynamic ? toClassicQuestion(raw) : raw;

        this.updateProgress();
        this.updateHud();

        const stemEl = this.root.querySelector('#ile-stem');
        if (this.isDynamic && Array.isArray(raw.stemBlocks) && raw.stemBlocks.length) {
            await loadLibraries(collectLibraries(this.schema, raw));
            stemEl.innerHTML = renderBlocks(raw.stemBlocks);
            bindSpeakers(stemEl, (payload) => this.fx.playOptionAudio(payload));
        } else {
            stemEl.textContent = question.stem || '';
        }

        this.root.querySelector('#ile-feedback').hidden = true;
        this.root.querySelector('#ile-submit').hidden = false;
        this.root.querySelector('#ile-next').hidden = true;
        this.root.querySelector('#ile-back').disabled = index === 0 || this.rules.allowBack === false;

        let factory;
        if (this.isDynamic && DYNAMIC_RICH_LAYOUT_TYPES.includes(question.type)) {
            const layout = resolveDynamicLayout(raw, question);
            factory = createDynamicInteractionModule(layout);
            this.root.querySelector('.ile-app')?.setAttribute('data-layout', layout);
        } else {
            // Classic module UI — used directly in classic mode, and as the
            // fallback for dynamic-mode questions outside the 6 rich-layout
            // types (the rich stemBlocks content above still renders either way).
            factory = getModule(question.type);
            this.root.querySelector('.ile-app')?.setAttribute('data-layout', this.isDynamic ? 'classic_fallback' : 'classic');
        }

        if (!factory) {
            this.root.querySelector('#ile-module').innerHTML = `<p class="ile-error">نوع غير مدعوم: ${escapeHtml(question.type)}</p>`;
            return;
        }

        // Fresh instance per question (clone methods onto new object)
        this.currentModule = Object.create(factory);
        Object.assign(this.currentModule, { ...factory });
        const mountEl = this.root.querySelector('#ile-module');
        const ctx = {
            question,
            rawQuestion: raw,
            bus: this.bus,
            rules: this.rules,
            playOptionAudio: (payload) => this.fx.playOptionAudio(payload),
            playSfx: (name) => this.fx.playSfx(name),
        };
        await this.currentModule.beforeMount?.(ctx);
        this.currentModule.mount(mountEl, ctx);
        await this.currentModule.afterMount?.(ctx);
        this.bus.emit('question.enter', { questionId: question.id, type: question.type });
    }

    check() {
        const raw = this.questions[this.index];
        const question = this.isDynamic ? toClassicQuestion(raw) : raw;
        const answer = this.currentModule?.getAnswer();
        const used = (this.attemptsUsed[question.id] || 0) + 1;
        this.attemptsUsed[question.id] = used;

        const result = this.currentModule.grade(answer);
        const feedback = this.root.querySelector('#ile-feedback');
        feedback.hidden = false;

        const color = this.typeColor(question.type);

        if (result.correct) {
            const okMsg = this.fx.cheer(this.root, {
                correct: true,
                color,
                phrase: question.successMessage || '',
            });
            feedback.hidden = false;
            feedback.className = 'ile-feedback ile-feedback--ok';
            const textEl = feedback.querySelector('#ile-feedback-text');
            const hintEl = feedback.querySelector('#ile-feedback-hint');
            if (textEl) textEl.textContent = okMsg;
            if (hintEl) {
                if (this.rules.showExplanation && question.explanation) {
                    hintEl.hidden = false;
                    hintEl.textContent = question.explanation;
                } else {
                    hintEl.hidden = true;
                    hintEl.textContent = '';
                }
            }
            this.bus.emit('question.correct', { questionId: question.id, result });
            this.recordAnswer(question, answer, result, used);
            // الإجابة صحيحة: نكشف اختياره الصحيح فوراً ونزيد السلسلة
            this.currentModule.reveal?.({ answer, result });
            this.streak += 1;
            this.bestStreak = Math.max(this.bestStreak, this.streak);
            this.updateHud({ bump: true });
            this.root.querySelector('#ile-submit').hidden = true;
            this.root.querySelector('#ile-next').hidden = false;
        } else {
            this.wrongCount += 1;
            this.streak = 0;
            this.updateHud();
            const badMsg = this.fx.cheer(this.root, {
                correct: false,
                color,
                phrase: question.errorMessage || '',
            });
            feedback.hidden = false;
            feedback.className = 'ile-feedback ile-feedback--bad';
            const textEl = feedback.querySelector('#ile-feedback-text');
            const hintEl = feedback.querySelector('#ile-feedback-hint');
            if (textEl) textEl.textContent = badMsg;
            if (hintEl) {
                hintEl.hidden = true;
                hintEl.textContent = '';
            }
            this.bus.emit('question.wrong', { questionId: question.id, result });

            const maxAttempts = Number(this.rules.attemptsPerQuestion ?? 1);
            if (used >= maxAttempts) {
                this.recordAnswer(question, answer, result, used);
                // نفدت المحاولات: الآن فقط نكشف الصواب حتى لا نُفسد محاولة متبقية
                this.currentModule.reveal?.({ answer, result });
                this.updateHud();
                this.root.querySelector('#ile-submit').hidden = true;
                this.root.querySelector('#ile-next').hidden = false;
                if (this.rules.showExplanation && question.explanation && hintEl) {
                    hintEl.hidden = false;
                    hintEl.textContent = question.explanation;
                }
            }

            if (this.rules.maxWrong != null && this.wrongCount >= Number(this.rules.maxWrong)) {
                this.complete();
            }
        }
    }

    recordAnswer(question, answer, result, attemptsUsed) {
        const existing = this.answers.findIndex((a) => a.questionId === question.id);
        const row = {
            questionId: question.id,
            type: question.type,
            correct: Boolean(result.correct),
            score: Number(result.score || 0),
            max: Number(result.max || question.points || 1),
            answer,
            attemptsUsed,
        };
        if (existing >= 0) this.answers[existing] = row;
        else this.answers.push(row);
    }

    async next() {
        if (this.index >= this.questions.length - 1) {
            await this.complete();
            return;
        }
        await this.showQuestion(this.index + 1);
    }

    async prev() {
        if (this.rules.allowBack === false || this.index <= 0) return;
        await this.showQuestion(this.index - 1);
    }

    pickMessage(kind) {
        const list = this.schema.messages?.[kind] || [];
        if (!list.length) return '';
        return list[Math.floor(Math.random() * list.length)];
    }

    async complete() {
        await this.destroyCurrent();
        this.finishedAt = new Date();
        this.score = this.answers.reduce((s, a) => s + Number(a.score || 0), 0);
        const percentage = this.total > 0 ? Math.round((this.score / this.total) * 1000) / 10 : 0;
        const duration = Math.max(0, Math.round((this.finishedAt - this.startedAt) / 1000));
        const payload = {
            score: this.score,
            total: this.total,
            percentage,
            duration,
            startedAt: this.startedAt?.toISOString(),
            finishedAt: this.finishedAt.toISOString(),
            sessionVersion: this.schema.version || '1.0',
            engineVersion: this.config.engineVersion || ENGINE_VERSION,
            answers: this.answers,
        };

        this.bus.emit('session.completed', payload);
        await this.submitResult(payload);
        this.showResults(payload);
    }

    async submitResult(payload) {
        this._submitError = null;
        if (!this.config.submitUrl || this.config.isPreview) {
            return;
        }
        try {
            const res = await fetch(this.config.submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const msg = data?.message || data?.errors?.experience?.[0] || `HTTP ${res.status}`;
                throw new Error(msg);
            }
            this.bus.emit('result.sent', { ok: true, attemptId: data.attempt_id });
        } catch (e) {
            console.error('[ILE] result submit failed', e);
            this._submitError = String(e?.message || e);
            this.bus.emit('result.sent', { ok: false, error: this._submitError });
        }
    }

    showResults(payload) {
        const results = this.root.querySelector('#ile-results');
        this.root.querySelector('.ile-main').hidden = true;
        this.root.querySelector('.ile-footer').hidden = true;
        results.hidden = false;
        const correct = this.answers.filter((a) => a.correct).length;
        const wrong = this.answers.length - correct;
        const passed = payload.percentage >= this.fx.passThreshold;
        const headline = passed ? 'يااا بطل! نجحت 🏆' : 'هيا نحاول من جديد!';

        const saveErrorHtml = this._submitError
            ? `<div class="ile-results__save-error" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>تعذر حفظ النتيجة في حسابك (${escapeHtml(this._submitError)}). تحقق من الاتصال ثم أعد المحاولة.</span>
               </div>`
            : '';

        results.innerHTML = `
            ${saveErrorHtml}
            <div class="ile-results__card ${passed ? 'ile-results__card--pass' : 'ile-results__card--fail'}">
                <div class="ile-lottie ile-lottie--lg" id="ile-results-lottie" aria-hidden="true"></div>
                <h2>${headline}</h2>
                <p class="ile-results__encourage" id="ile-results-encourage"></p>
                <div class="ile-results__score">${payload.percentage}%</div>
                <p class="ile-results__points">${payload.score} من ${payload.total} نجمة</p>
                <ul class="ile-results__stats">
                    <li>صح: ${correct}</li>
                    <li>جرّبنا: ${wrong}</li>
                    <li>الوقت: ${payload.duration} ث</li>
                    ${this.bestStreak >= 2 ? `<li>أطول سلسلة: 🔥 ${this.bestStreak}</li>` : ''}
                </ul>
                <div class="ile-results__actions">
                    <button type="button" class="ile-btn ile-btn--primary" id="ile-retry">${passed ? 'العب مرة ثانية!' : 'يلا نعيد!'}</button>
                    <button type="button" class="ile-btn ile-btn--ghost" id="ile-review">شوف إجاباتك</button>
                </div>
                <div class="ile-review" id="ile-review-box" hidden></div>
            </div>`;

        const encourage = this.fx.cheer(this.root, { correct: passed, big: true });
        const encourageEl = results.querySelector('#ile-results-encourage');
        if (encourageEl) encourageEl.textContent = encourage;

        results.querySelector('#ile-retry')?.addEventListener('click', () => window.location.reload());
        results.querySelector('#ile-review')?.addEventListener('click', () => {
            const box = results.querySelector('#ile-review-box');
            box.hidden = false;
            box.innerHTML = this.answers
                .map((a) => {
                    const q = this.questions.find((x) => x.id === a.questionId);
                    return `<div class="ile-review__item ${a.correct ? 'ok' : 'bad'}">
                        <strong>${escapeHtml(q?.stem || a.questionId)}</strong>
                        <span>${a.correct ? 'صح ⭐' : 'جرّب لاحقاً'} (${a.score}/${a.max})</span>
                    </div>`;
                })
                .join('');
        });
    }
}
