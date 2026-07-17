@php
    // دليل كتابة المعادلات — LaTeX + عرض KaTeX
@endphp
<div class="card custom-card mb-3">
    <div class="card-header py-2">
        <button class="btn btn-link text-decoration-none p-0 w-100 text-start d-flex align-items-center justify-content-between"
                type="button" data-bs-toggle="collapse" data-bs-target="#mathAuthoringGuide" aria-expanded="false">
            <span><i class="bi bi-question-circle me-2"></i> دليل كتابة المعادلات (LaTeX)</span>
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div id="mathAuthoringGuide" class="collapse">
        <div class="card-body small text-muted">
            <p class="mb-2"><strong>الصيغة الرسمية الإلزامية:</strong> استخدم <code>$...$</code> للمعادلات داخل السطر و<code>$$...$$</code> للمعادلات المعروضة. العرض عبر <strong>KaTeX</strong> فقط.</p>

            <p class="mb-2"><strong>مثال مطابق للعرض الصحيح:</strong></p>
            <div class="bg-light rounded p-2 mb-2 question-text-body math-live-preview-body" dir="rtl">
                ليكن <code>$f(x)=\sqrt{x^{2}+4x+5}$</code> لماذا يعتبر هذا التابع مستمراً على <code>$\mathbb{R}$</code>؟
            </div>
            <pre class="small bg-dark text-white rounded p-2 mb-3" dir="ltr">ليكن $f(x)=\sqrt{x^{2}+4x+5}$ لماذا يعتبر هذا التابع مستمراً على $\mathbb{R}$؟</pre>

            <p class="mb-2"><strong>أمثلة شائعة:</strong></p>
            <ul class="mb-2">
                <li><code>$\frac{a}{b}$</code> — كسر</li>
                <li><code>$\int_0^1 x\,dx$</code> — تكامل</li>
                <li><code>$\sum_{k=1}^{n} k$</code> — مجموع</li>
                <li><code>$\mathbb{R}$</code> — مجموعة الأعداد الحقيقية</li>
                <li><code>$\ce{H2O}$</code> — صيغة كيميائية (mhchem)</li>
            </ul>

            <p class="mb-2"><strong>عند الاستيراد من Excel / NotebookLM:</strong></p>
            <ul class="mb-2">
                <li>يُفضَّل كتابة المعادلات مسبقاً داخل <code>$...$</code>.</li>
                <li>الرموز مثل <code>√</code> و<code>ℝ</code> و<code>²</code> تُحوَّل تلقائياً عند الاستيراد.</li>
                <li>في معالج Excel: راجع «معاينة المعادلات» وأكّدها قبل الاستيراد النهائي.</li>
            </ul>

            <p class="mb-0"><strong>تحويل تلقائي إضافي:</strong> صيغ مثل <code>`sum_{k=1 to n}`</code> أو <code>n &gt;= 1</code> داخل backticks تُحوَّل عند الحفظ والعرض.</p>
        </div>
    </div>
</div>
