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
})();
