<div class="card custom-card mb-3">
    <div class="card-header py-2">
        <button class="btn btn-link text-decoration-none p-0 w-100 text-start d-flex align-items-center justify-content-between"
                type="button" data-bs-toggle="collapse" data-bs-target="#mathAuthoringGuide" aria-expanded="false">
            <span><i class="bi bi-question-circle me-2"></i> دليل كتابة المعادلات</span>
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div id="mathAuthoringGuide" class="collapse">
        <div class="card-body small text-muted">
            <p class="mb-2"><strong>الصيغة الرسمية:</strong> استخدم <code>$...$</code> للمعادلات داخل السطر و<code>$$...$$</code> للمعادلات المعروضة.</p>
            <p class="mb-2"><strong>أمثلة:</strong></p>
            <ul class="mb-2">
                <li><code>$\frac{a}{b}$</code> — كسر</li>
                <li><code>$\int_0^1 x\,dx$</code> — تكامل</li>
                <li><code>$\sum_{k=1}^{n} k$</code> — مجموع</li>
                <li><code>$\ce{H2O}$</code> — صيغة كيميائية (يتطلب mhchem)</li>
            </ul>
            <p class="mb-0"><strong>تحويل تلقائي:</strong> يمكنك أيضاً كتابة صيغ مبسطة مثل <code>`sum_{k=1 to n}`</code> أو <code>n &gt;= 1</code> داخل backticks — تُحوَّل تلقائياً عند العرض والحفظ.</p>
        </div>
    </div>
</div>
