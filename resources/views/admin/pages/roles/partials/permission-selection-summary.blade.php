{{-- ملخص الصلاحيات المحددة (يُحدَّث عبر JavaScript) --}}
<style>
    /* يبقى ملخص الصلاحيات ظاهراً أعلى منطقة العرض عند التمرير */
    /* top ≈ هامش .app-content تحت الهيدر الثابت (3.85rem) + هامش بسيط */
    #role-permissions-summary-panel {
        position: sticky;
        top: 4.25rem;
        z-index: 99;
        background-color: var(--bs-body-bg, #fff);
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08);
    }
    [data-theme-mode="dark"] #role-permissions-summary-panel,
    [data-bs-theme="dark"] #role-permissions-summary-panel {
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.35);
    }
    .role-permissions-summary-list-wrap {
        max-height: min(55vh, 520px);
        overflow-y: auto;
    }
    #role-permissions-summary-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 220px), 1fr));
        gap: 0.65rem;
        align-items: stretch;
    }
    #role-permissions-summary-list > li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 0.375rem;
        padding: 0.5rem 0.65rem;
        background: var(--bs-tertiary-bg, rgba(0, 0, 0, 0.02));
    }
    #role-permissions-summary-list .role-permissions-summary-index {
        flex-shrink: 0;
        min-width: 1.6rem;
        font-weight: 700;
        font-size: 0.8rem;
        line-height: 1.4;
        color: var(--bs-primary, #0d6efd);
    }
    #role-permissions-summary-list .role-permissions-summary-item-text {
        min-width: 0;
        flex: 1 1 auto;
    }
</style>
<div class="card mb-3 border-info border-opacity-50" id="role-permissions-summary-panel">
    <div class="card-header py-2 d-flex flex-wrap align-items-center gap-2">
        <span class="fw-semibold">ملخص الصلاحيات المحددة</span>
        <span class="badge bg-primary" id="role-permissions-summary-count">0</span>
    </div>
    <div class="card-body py-3">
        <p class="text-muted small mb-2 mb-0" id="role-permissions-summary-empty">لم يتم تحديد أي صلاحية بعد.</p>
        <div class="role-permissions-summary-list-wrap">
            <ul class="list-unstyled small mb-0" id="role-permissions-summary-list"></ul>
        </div>
    </div>
</div>
