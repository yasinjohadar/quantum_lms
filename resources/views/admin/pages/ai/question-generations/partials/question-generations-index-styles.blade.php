@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .ai-gen-index-page {
        --ui-radius: 14px;
        --ui-accent: #6366f1;
        --ui-accent-rgb: 99, 102, 241;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(99, 102, 241, 0.06);
    }

    [data-theme-mode="dark"] .ai-gen-index-page,
    [data-bs-theme="dark"] .ai-gen-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(99, 102, 241, 0.12);
    }

    .ai-gen-index-page .container-fluid {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    @media (min-width: 1200px) {
        .ai-gen-index-page .container-fluid {
            padding-left: 1.75rem;
            padding-right: 1.75rem;
        }
    }

    .ai-gen-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(99, 102, 241, 0.22);
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .ai-gen-index-hero,
    [data-bs-theme="dark"] .ai-gen-index-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .ai-gen-index-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #6366f1;
        background: rgba(99, 102, 241, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .ai-gen-index-hero__icon,
    [data-bs-theme="dark"] .ai-gen-index-hero__icon { color: #a5b4fc; }

    .ai-gen-index-hero__content { flex: 1; min-width: 200px; }
    .ai-gen-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .ai-gen-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .ai-gen-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .ai-gen-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .ai-gen-index-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .ai-gen-index-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #6366f1;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .ai-gen-index-stat-mini__value,
    [data-bs-theme="dark"] .ai-gen-index-stat-mini__value { color: #a5b4fc; }

    .ai-gen-index-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .ai-gen-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .ai-gen-index-card,
    [data-bs-theme="dark"] .ai-gen-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .ai-gen-index-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--ui-border);
        background: var(--ui-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .ai-gen-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(99, 102, 241, 0.12);
        color: var(--ui-accent);
    }

    .ai-gen-index-card__body { padding: 1.25rem; }

    .ai-gen-index-card--flush > .ai-gen-index-card__body { padding: 0; }

    .ai-gen-form-card > .ai-gen-index-card__body {
        padding: 1.5rem 1.75rem;
    }

    @media (max-width: 575.98px) {
        .ai-gen-index-page .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .ai-gen-form-card > .ai-gen-index-card__body {
            padding: 1.15rem 1.1rem;
        }

        .ai-gen-index-card__body { padding: 1rem; }
    }

    .ai-gen-index-table-wrap {
        overflow-x: auto;
    }

    .ai-gen-index-table { margin-bottom: 0; min-width: 900px; }

    .ai-gen-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .ai-gen-index-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
        font-size: 0.875rem;
    }

    .ai-gen-index-table tbody tr { transition: background 0.15s ease; }
    .ai-gen-index-table tbody tr:hover { background: var(--ui-soft); }
    .ai-gen-index-table tbody tr:last-child td { border-bottom: none; }

    .ai-gen-user-cell {
        font-weight: 600;
        white-space: nowrap;
    }

    .ai-gen-source-cell__type { font-weight: 600; }
    .ai-gen-source-cell__meta {
        display: block;
        font-size: 0.75rem;
        color: var(--ui-muted);
        margin-top: 0.15rem;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ai-gen-type-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        margin: 0.1rem;
        white-space: nowrap;
    }

    [data-theme-mode="dark"] .ai-gen-type-pill,
    [data-bs-theme="dark"] .ai-gen-type-pill { color: #a5b4fc; }

    .ai-gen-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 0.4rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .ai-gen-count--requested {
        background: rgba(99, 102, 241, 0.12);
        color: #6366f1;
    }

    .ai-gen-count--done {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .ai-gen-count--partial {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }

    .ai-gen-count--empty {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    [data-theme-mode="dark"] .ai-gen-count--done,
    [data-bs-theme="dark"] .ai-gen-count--done { color: #6ee7b7; }
    [data-theme-mode="dark"] .ai-gen-count--partial,
    [data-bs-theme="dark"] .ai-gen-count--partial { color: #fcd34d; }

    .ai-gen-status {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .ai-gen-status--completed {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .ai-gen-status--processing {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }

    .ai-gen-status--failed {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .ai-gen-status--pending {
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
    }

    [data-theme-mode="dark"] .ai-gen-status--completed,
    [data-bs-theme="dark"] .ai-gen-status--completed { color: #6ee7b7; }
    [data-theme-mode="dark"] .ai-gen-status--processing,
    [data-bs-theme="dark"] .ai-gen-status--processing { color: #fcd34d; }
    [data-theme-mode="dark"] .ai-gen-status--failed,
    [data-bs-theme="dark"] .ai-gen-status--failed { color: #fca5a5; }

    .ai-gen-model-cell {
        font-size: 0.82rem;
        font-weight: 500;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ai-gen-cost-cell {
        font-size: 0.8rem;
        font-family: ui-monospace, monospace;
        color: var(--ui-muted);
    }

    .ai-gen-saved-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
        white-space: nowrap;
    }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .ai-gen-index-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .ai-gen-index-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
        color: #6366f1;
    }

    .ai-gen-index-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--ui-border);
    }

    @media (max-width: 1199.98px) {
        .ai-gen-col-cost { display: none; }
    }

    @media (max-width: 991.98px) {
        .ai-gen-col-model { display: none; }
        .ai-gen-col-user { display: none; }
    }

    @media (max-width: 767.98px) {
        .ai-gen-index-hero__actions { width: 100%; }
        .ai-gen-index-hero__actions .btn { flex: 1 1 auto; }
        .ai-gen-index-stat-mini { width: 100%; }
        .ai-gen-col-types { display: none; }
    }

    @media (max-width: 575.98px) {
        .ai-gen-index-table thead th,
        .ai-gen-index-table tbody td {
            padding: 0.65rem 0.75rem;
        }
        .ai-gen-question-types-grid { grid-template-columns: 1fr; }
    }

    /* ── نماذج التوليد ── */
    .ai-gen-form-card .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.35rem;
    }

    .ai-gen-form-card .form-control,
    .ai-gen-form-card .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .ai-gen-form-card .form-control:focus,
    .ai-gen-form-card .form-select:focus {
        border-color: rgba(99, 102, 241, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.1);
    }

    .ai-gen-form-section {
        padding: 1.25rem 0;
        border-bottom: 1px dashed var(--ui-border);
        margin-bottom: 1.5rem;
    }

    .ai-gen-form-section:first-of-type {
        padding-top: 0;
    }

    .ai-gen-form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .ai-gen-form-section__title {
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ai-gen-form-section__title-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(99, 102, 241, 0.12);
        color: var(--ui-accent);
        font-size: 0.85rem;
    }

    .ai-gen-type-select-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .ai-gen-type-select-toolbar .btn {
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .ai-gen-question-types-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    .ai-gen-question-type-card {
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        height: 100%;
    }

    .ai-gen-question-type-card:hover {
        border-color: rgba(99, 102, 241, 0.3);
    }

    .ai-gen-question-type-card--selected {
        border-color: rgba(99, 102, 241, 0.5);
        background: rgba(99, 102, 241, 0.08);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.12);
    }

    .ai-gen-question-type-card .card-body { padding: 0.85rem 1rem; }

    .ai-gen-question-type-card .form-check-label {
        cursor: pointer;
        width: 100%;
    }

    .ai-gen-question-type-card__icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        background: var(--ui-soft);
    }

    .ai-gen-hint-box {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: rgba(14, 165, 233, 0.08);
        border: 1px solid rgba(14, 165, 233, 0.2);
        font-size: 0.85rem;
        margin-bottom: 1.25rem;
        color: var(--default-text-color, inherit);
    }

    [data-theme-mode="dark"] .ai-gen-hint-box,
    [data-bs-theme="dark"] .ai-gen-hint-box {
        background: rgba(14, 165, 233, 0.12);
    }

    .ai-gen-linked-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: rgba(99, 102, 241, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.2);
        margin-bottom: 1.25rem;
        font-size: 0.875rem;
    }

    .ai-gen-linked-alert i { color: var(--ui-accent); font-size: 1.1rem; margin-top: 0.1rem; }

    .ai-gen-file-preview {
        border-radius: 10px;
        border: 1px dashed var(--ui-border);
        padding: 0.75rem;
        background: var(--ui-soft);
    }

    .ai-gen-form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding-top: 1.5rem;
        margin-top: 1rem;
        border-top: 1px solid var(--ui-border);
    }

    .ai-gen-form-actions .btn { border-radius: 10px; font-weight: 600; }

    @media (max-width: 767.98px) {
        .ai-gen-form-actions .btn { flex: 1 1 auto; }
        .ai-gen-question-types-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
