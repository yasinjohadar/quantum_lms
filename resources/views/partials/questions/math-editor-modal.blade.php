<div class="modal fade" id="questionMathEditorModal" tabindex="-1" aria-labelledby="questionMathEditorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionMathEditorModalLabel">
                    <i class="bi bi-calculator me-2"></i> إدراج معادلة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="math-editor-latex">كود LaTeX</label>
                    <textarea id="math-editor-latex" class="form-control font-monospace" rows="3" dir="ltr" placeholder="مثال: \frac{a}{b} أو \sum_{k=1}^{n} k"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">معاينة</label>
                    <div id="math-editor-preview" class="math-live-preview-body p-3 border rounded bg-light text-center" dir="ltr" style="min-height: 4rem;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">نوع العرض</label>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="math-editor-mode" id="math-editor-inline" value="inline" checked>
                        <label class="btn btn-outline-primary btn-sm" for="math-editor-inline">داخل السطر $...$</label>
                        <input type="radio" class="btn-check" name="math-editor-mode" id="math-editor-display" value="display">
                        <label class="btn btn-outline-primary btn-sm" for="math-editor-display">معروضة $$...$$</label>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label">رموز سريعة</label>
                    <div class="d-flex flex-wrap gap-1" id="math-editor-symbols">
                        @foreach([
                            ['\\frac{a}{b}', 'كسر'],
                            ['\\sqrt{x}', 'جذر'],
                            ['x^{n}', 'أس'],
                            ['x_{n}', 'أس سفلي'],
                            ['\\int', 'تكامل'],
                            ['\\sum_{k=1}^{n}', 'مجموع'],
                            ['\\lim_{x \\to \\infty}', 'حد'],
                            ['\\alpha', 'α'],
                            ['\\beta', 'β'],
                            ['\\pi', 'π'],
                            ['\\leq', '≤'],
                            ['\\geq', '≥'],
                            ['\\neq', '≠'],
                            ['\\cdot', '·'],
                            ['\\infty', '∞'],
                            ['\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}', 'مصفوفة'],
                            ['\\ce{H2O}', 'كيمياء'],
                        ] as [$symbol, $label])
                            <button type="button" class="btn btn-sm btn-outline-secondary math-symbol-btn" data-symbol="{{ $symbol }}" title="{{ $label }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="math-editor-insert-btn">
                    <i class="bi bi-plus-lg me-1"></i> إدراج
                </button>
            </div>
        </div>
    </div>
</div>
