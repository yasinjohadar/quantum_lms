<div class="card custom-card mb-3 math-backfill-tool" id="mathBackfillTool"
     data-status-url="{{ route('admin.questions.math-backfill.status') }}"
     data-process-url="{{ route('admin.questions.math-backfill.process-batch') }}">
    <div class="card-header bg-warning-transparent">
        <div class="card-title">
            <i class="bi bi-magic me-1"></i>
            إصلاح شامل لعرض المعادلات الرياضية (LaTeX) في كل بنك الأسئلة
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            تعالج هذه الأداة <strong>كل</strong> الأسئلة والخيارات المخزَّنة حالياً (وليس فقط ما يُستورد بعد الآن)
            وتعيد بناء صيغة LaTeX لكل معادلة بأحدث منطق تنسيق، لإصلاح أي أسئلة قديمة قد تظهر فيها معادلات
            كنص خام أو أخطاء عرض. العملية آمنة (لا تُغيّر إلا ما يحتاج تصحيحاً) ويمكن تشغيلها أكثر من مرة.
            <strong>ابدأ بهذه المرحلة المجانية أولاً</strong> قبل الانتقال لمرحلة الإصلاح الذكي أدناه.
        </p>

        <div id="mathBackfillIdle">
            <button type="button" class="btn btn-warning" id="mathBackfillStartBtn">
                <i class="bi bi-magic me-1"></i> بدء الإصلاح الشامل الآن
            </button>
        </div>

        <div id="mathBackfillProgress" style="display: none;">
            <div class="mb-2 d-flex justify-content-between small">
                <span id="mathBackfillPhaseLabel">جاري تجهيز الفحص…</span>
                <span id="mathBackfillCounts">0 / 0</span>
            </div>
            <div class="progress mb-3" style="height: 10px;">
                <div class="progress-bar bg-warning" id="mathBackfillProgressBar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="small text-muted" id="mathBackfillUpdatedCount">تم تصحيح 0 عنصر حتى الآن.</div>
        </div>

        <div id="mathBackfillDone" class="alert alert-success mt-3" style="display: none;">
            <i class="bi bi-check-circle me-1"></i>
            <span id="mathBackfillDoneMessage"></span>
        </div>

        <div id="mathBackfillError" class="alert alert-danger mt-3" style="display: none;"></div>
    </div>
</div>

<div class="card custom-card mb-3 math-ai-repair-tool" id="mathAiRepairTool"
     data-status-url="{{ route('admin.questions.math-backfill.ai-repair-status') }}"
     data-process-url="{{ route('admin.questions.math-backfill.ai-repair-batch') }}">
    <div class="card-header bg-info-transparent">
        <div class="card-title">
            <i class="bi bi-stars me-1"></i>
            إصلاح ذكي بالذكاء الاصطناعي للحالات المعقّدة (بعد الإصلاح الشامل أعلاه)
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            بعض الأسئلة المستورَدة تحتوي كسوراً (<code>\frac</code>) فقدت الشرطة المعكوسة <strong>والأقواس معاً</strong>،
            فالتصق البسط بالمقام بلا أي فاصل (مثل <code>frac2√(4x+1)</code>) — لا يمكن حل هذه الحالة بثقة بالأنماط
            النصية فقط. تفحص هذه الأداة <strong>الأسئلة المشتبه بها فقط</strong> (وليس كل بنك الأسئلة) وتستخدم نموذج
            الذكاء الاصطناعي المُهيَّأ في النظام لإعادة بناء صيغة LaTeX الصحيحة، مستفيدةً من سياق السؤال كاملاً
            (الشرح والخيارات) لتحديد التقسيم الصحيح. تستهلك وقتاً وتكلفة API لكل سؤال مشتبه به.
        </p>

        <div id="mathAiRepairIdle">
            <div class="small text-muted mb-2" id="mathAiRepairSuspiciousCount">جاري فحص عدد الأسئلة المشتبه بها…</div>
            <button type="button" class="btn btn-info" id="mathAiRepairStartBtn" disabled>
                <i class="bi bi-stars me-1"></i> بدء الإصلاح الذكي الآن
            </button>
        </div>

        <div id="mathAiRepairProgress" style="display: none;">
            <div class="mb-2 d-flex justify-content-between small">
                <span id="mathAiRepairPhaseLabel">جاري مراجعة الأسئلة المشتبه بها بالذكاء الاصطناعي…</span>
                <span id="mathAiRepairCounts">0 / 0</span>
            </div>
            <div class="progress mb-3" style="height: 10px;">
                <div class="progress-bar bg-info" id="mathAiRepairProgressBar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="small text-muted" id="mathAiRepairUpdatedCount">تم فحص 0 سؤال بالذكاء الاصطناعي، وتصحيح 0 سؤال حتى الآن.</div>
        </div>

        <div id="mathAiRepairDone" class="alert alert-success mt-3" style="display: none;">
            <i class="bi bi-check-circle me-1"></i>
            <span id="mathAiRepairDoneMessage"></span>
        </div>

        <div id="mathAiRepairError" class="alert alert-danger mt-3" style="display: none;"></div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/math-backfill.js') }}" defer></script>
@endpush
