(function () {
    'use strict';

    const root = document.getElementById('mathBackfillTool');
    if (!root) {
        return;
    }

    const statusUrl = root.dataset.statusUrl;
    const processUrl = root.dataset.processUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const idleEl = document.getElementById('mathBackfillIdle');
    const startBtn = document.getElementById('mathBackfillStartBtn');
    const progressEl = document.getElementById('mathBackfillProgress');
    const phaseLabelEl = document.getElementById('mathBackfillPhaseLabel');
    const countsEl = document.getElementById('mathBackfillCounts');
    const progressBarEl = document.getElementById('mathBackfillProgressBar');
    const updatedCountEl = document.getElementById('mathBackfillUpdatedCount');
    const doneEl = document.getElementById('mathBackfillDone');
    const doneMessageEl = document.getElementById('mathBackfillDoneMessage');
    const errorEl = document.getElementById('mathBackfillError');

    const BATCH_LIMIT = 300;
    const PHASES = [
        { entity: 'questions', label: 'جاري إصلاح الأسئلة (العنوان/المحتوى/الشرح)…' },
        { entity: 'options', label: 'جاري إصلاح خيارات الإجابة…' },
    ];

    let totalUpdated = 0;

    function showError(message) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="bi bi-magic me-1"></i> بدء الإصلاح الشامل الآن';
    }

    async function fetchJson(url, options) {
        const response = await fetch(url, Object.assign({
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }, options || {}));

        const data = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            throw new Error(data.message || 'تعذر الاتصال بالخادم.');
        }

        return data;
    }

    async function runPhase(phaseIndex, totals) {
        if (phaseIndex >= PHASES.length) {
            return;
        }

        const phase = PHASES[phaseIndex];
        const totalForEntity = totals[phase.entity] || 0;
        phaseLabelEl.textContent = phase.label;

        let afterId = 0;
        let scannedSoFar = 0;
        let done = false;

        while (!done) {
            const params = new URLSearchParams();
            params.set('entity', phase.entity);
            params.set('after_id', String(afterId));
            params.set('limit', String(BATCH_LIMIT));
            params.set('_token', csrfToken);

            const result = await fetchJson(processUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString(),
            });

            scannedSoFar += result.scanned;
            totalUpdated += result.updated;
            afterId = result.next_after_id;
            done = result.done;

            const pct = totalForEntity > 0 ? Math.min(100, Math.round((scannedSoFar / totalForEntity) * 100)) : 100;
            progressBarEl.style.width = pct + '%';
            countsEl.textContent = scannedSoFar + ' / ' + totalForEntity;
            updatedCountEl.textContent = 'تم تصحيح ' + totalUpdated + ' عنصر حتى الآن.';

            if (result.scanned === 0) {
                break;
            }
        }

        await runPhase(phaseIndex + 1, totals);
    }

    startBtn?.addEventListener('click', async function () {
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الإصلاح...';
        errorEl.style.display = 'none';
        doneEl.style.display = 'none';
        idleEl.style.display = 'none';
        progressEl.style.display = 'block';
        totalUpdated = 0;

        try {
            const totals = await fetchJson(statusUrl);
            await runPhase(0, totals);

            progressEl.style.display = 'none';
            doneMessageEl.textContent = totalUpdated > 0
                ? 'اكتمل الإصلاح: تم تصحيح ' + totalUpdated + ' عنصر (سؤال/خيار) كانت معادلاته غير مُنسَّقة بشكل صحيح.'
                : 'اكتمل الفحص: كل الأسئلة والخيارات كانت مُنسَّقة بشكل صحيح مسبقاً، لم يلزم أي تعديل.';
            doneEl.style.display = 'block';
            startBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> إعادة تشغيل الإصلاح';
            startBtn.disabled = false;
        } catch (err) {
            progressEl.style.display = 'none';
            idleEl.style.display = 'block';
            showError(err.message || 'حدث خطأ غير متوقع أثناء الإصلاح.');
        }
    });

    // ==========================================================
    // المرحلة الثانية: الإصلاح الذكي بالذكاء الاصطناعي (اختياري، أبطأ وأغلى)
    // ==========================================================
    const aiRoot = document.getElementById('mathAiRepairTool');
    if (!aiRoot) {
        return;
    }

    const aiStatusUrl = aiRoot.dataset.statusUrl;
    const aiProcessUrl = aiRoot.dataset.processUrl;

    const aiIdleEl = document.getElementById('mathAiRepairIdle');
    const aiSuspiciousCountEl = document.getElementById('mathAiRepairSuspiciousCount');
    const aiStartBtn = document.getElementById('mathAiRepairStartBtn');
    const aiProgressEl = document.getElementById('mathAiRepairProgress');
    const aiCountsEl = document.getElementById('mathAiRepairCounts');
    const aiProgressBarEl = document.getElementById('mathAiRepairProgressBar');
    const aiUpdatedCountEl = document.getElementById('mathAiRepairUpdatedCount');
    const aiDoneEl = document.getElementById('mathAiRepairDone');
    const aiDoneMessageEl = document.getElementById('mathAiRepairDoneMessage');
    const aiErrorEl = document.getElementById('mathAiRepairError');

    const AI_BATCH_LIMIT = 10;

    function showAiError(message) {
        aiErrorEl.textContent = message;
        aiErrorEl.style.display = 'block';
        aiStartBtn.disabled = false;
        aiStartBtn.innerHTML = '<i class="bi bi-stars me-1"></i> بدء الإصلاح الذكي الآن';
    }

    async function refreshAiStatus() {
        try {
            const status = await fetchJson(aiStatusUrl);

            if (!status.has_model) {
                aiSuspiciousCountEl.textContent = 'لا يوجد موديل ذكاء اصطناعي مفعَّل حالياً — فعِّل موديلاً في إدارة الذكاء الاصطناعي أولاً لاستخدام هذه الأداة.';
                aiStartBtn.disabled = true;
                return status;
            }

            aiSuspiciousCountEl.textContent = status.suspicious_questions > 0
                ? 'عدد الأسئلة المشتبه بها حالياً: ' + status.suspicious_questions + ' سؤال من إجمالي ' + status.total_questions + '.'
                : 'لا توجد أسئلة مشتبه بها حالياً — لا حاجة لتشغيل هذه الأداة.';
            aiStartBtn.disabled = status.suspicious_questions <= 0;

            return status;
        } catch (err) {
            aiSuspiciousCountEl.textContent = 'تعذّر فحص حالة الإصلاح الذكي.';
            aiStartBtn.disabled = true;

            return null;
        }
    }

    refreshAiStatus();

    aiStartBtn?.addEventListener('click', async function () {
        aiStartBtn.disabled = true;
        aiStartBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الإصلاح بالذكاء الاصطناعي...';
        aiErrorEl.style.display = 'none';
        aiDoneEl.style.display = 'none';
        aiIdleEl.style.display = 'none';
        aiProgressEl.style.display = 'block';

        let afterId = 0;
        let scannedSoFar = 0;
        let aiCheckedTotal = 0;
        let updatedTotal = 0;
        let done = false;

        try {
            const status = await fetchJson(aiStatusUrl);
            const totalQuestions = status.total_questions || 0;

            while (!done) {
                const params = new URLSearchParams();
                params.set('after_id', String(afterId));
                params.set('limit', String(AI_BATCH_LIMIT));
                params.set('_token', csrfToken);

                const result = await fetchJson(aiProcessUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: params.toString(),
                });

                if (result.error) {
                    throw new Error(result.error);
                }

                scannedSoFar += result.scanned;
                aiCheckedTotal += result.ai_checked;
                updatedTotal += result.updated;
                afterId = result.next_after_id;
                done = result.done;

                const pct = totalQuestions > 0 ? Math.min(100, Math.round((scannedSoFar / totalQuestions) * 100)) : 100;
                aiProgressBarEl.style.width = pct + '%';
                aiCountsEl.textContent = scannedSoFar + ' / ' + totalQuestions;
                aiUpdatedCountEl.textContent = 'تم فحص ' + aiCheckedTotal + ' سؤال مشتبه به بالذكاء الاصطناعي، وتصحيح ' + updatedTotal + ' سؤال حتى الآن.';

                if (result.scanned === 0) {
                    break;
                }
            }

            aiProgressEl.style.display = 'none';
            aiDoneMessageEl.textContent = aiCheckedTotal > 0
                ? 'اكتمل الفحص: تمت مراجعة ' + aiCheckedTotal + ' سؤال مشتبه به بالذكاء الاصطناعي، وتصحيح ' + updatedTotal + ' منها.'
                : 'اكتمل الفحص: لم يتبقَّ أي سؤال مشتبه به يحتاج مراجعة الذكاء الاصطناعي.';
            aiDoneEl.style.display = 'block';
            aiStartBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> إعادة الفحص';
            aiStartBtn.disabled = false;

            await refreshAiStatus();
        } catch (err) {
            aiProgressEl.style.display = 'none';
            aiIdleEl.style.display = 'block';
            showAiError(err.message || 'حدث خطأ غير متوقع أثناء الإصلاح الذكي.');
        }
    });
})();
