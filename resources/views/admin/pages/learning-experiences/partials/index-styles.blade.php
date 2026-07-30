@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .ile-index-page {
        --ui-radius: 14px;
        --ui-accent: #059669;
        --ui-accent-rgb: 5, 150, 105;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(5, 150, 105, 0.06);
    }

    [data-theme-mode="dark"] .ile-index-page,
    [data-bs-theme="dark"] .ile-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(5, 150, 105, 0.14);
    }

    .ile-index-page .container-fluid {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    @media (min-width: 1200px) {
        .ile-index-page .container-fluid {
            padding-left: 1.75rem;
            padding-right: 1.75rem;
        }
    }

    .ile-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.16) 0%, rgba(37, 99, 235, 0.06) 100%);
        border: 1px solid rgba(5, 150, 105, 0.24);
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .ile-index-hero,
    [data-bs-theme="dark"] .ile-index-hero {
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .ile-index-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #059669;
        background: rgba(5, 150, 105, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .ile-index-hero__icon,
    [data-bs-theme="dark"] .ile-index-hero__icon { color: #6ee7b7; }

    .ile-index-hero__content { flex: 1; min-width: 200px; }
    .ile-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .ile-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .ile-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .ile-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .ile-index-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .ile-index-stat-mini {
        text-align: center;
        padding: 0.65rem 0.9rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 88px;
    }

    .ile-index-stat-mini__value {
        display: block;
        font-size: 1.2rem;
        font-weight: 700;
        color: #059669;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .ile-index-stat-mini__value,
    [data-bs-theme="dark"] .ile-index-stat-mini__value { color: #6ee7b7; }

    .ile-index-stat-mini__label { font-size: 0.7rem; color: var(--ui-muted); }

    .ile-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .ile-index-card,
    [data-bs-theme="dark"] .ile-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .ile-index-card__header {
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

    .ile-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(5, 150, 105, 0.12);
        color: var(--ui-accent);
    }

    .ile-index-card__body { padding: 1.25rem; }
    .ile-index-card--flush > .ile-index-card__body { padding: 0; }

    .ile-index-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.3rem;
    }

    .ile-index-filters .form-control,
    .ile-index-filters .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .ile-index-filters .form-control:focus,
    .ile-index-filters .form-select:focus {
        border-color: rgba(5, 150, 105, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.12);
    }

    .ile-index-table-wrap { overflow-x: auto; }
    .ile-index-table { margin-bottom: 0; min-width: 920px; }

    .ile-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .ile-index-table tbody td {
        padding: 0.95rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
        font-size: 0.875rem;
    }

    .ile-index-table tbody tr { transition: background 0.15s ease; }
    .ile-index-table tbody tr:hover { background: var(--ui-soft); }
    .ile-index-table tbody tr:last-child td { border-bottom: none; }

    .ile-exp-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .ile-exp-thumb {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .ile-exp-thumb--dynamic {
        background: rgba(99, 102, 241, 0.12);
        color: #6366f1;
    }

    .ile-exp-title {
        font-weight: 700;
        margin-bottom: 0.15rem;
        line-height: 1.35;
    }

    .ile-exp-desc {
        font-size: 0.78rem;
        color: var(--ui-muted);
        margin: 0;
        line-height: 1.4;
    }

    .ile-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ile-badge--published { background: rgba(5, 150, 105, 0.14); color: #047857; }
    .ile-badge--draft { background: rgba(100, 116, 139, 0.14); color: #475569; }
    .ile-badge--review { background: rgba(245, 158, 11, 0.16); color: #b45309; }
    .ile-badge--archived { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
    .ile-badge--classic { background: rgba(14, 165, 233, 0.12); color: #0369a1; }
    .ile-badge--dynamic { background: rgba(99, 102, 241, 0.14); color: #4338ca; }

    [data-theme-mode="dark"] .ile-badge--published,
    [data-bs-theme="dark"] .ile-badge--published { color: #6ee7b7; }
    [data-theme-mode="dark"] .ile-badge--dynamic,
    [data-bs-theme="dark"] .ile-badge--dynamic { color: #a5b4fc; }

    .ile-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        border-radius: 8px;
        background: var(--ui-soft);
        border: 1px solid var(--ui-border);
        font-size: 0.72rem;
        color: var(--ui-muted);
        font-weight: 600;
    }

    .ile-q-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.5rem;
        border-radius: 999px;
        background: rgba(5, 150, 105, 0.12);
        color: #047857;
        font-weight: 800;
        font-size: 0.85rem;
    }

    .ile-row-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        justify-content: flex-end;
    }

    .ile-row-actions .btn {
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.78rem;
        padding: 0.35rem 0.65rem;
    }

    .ile-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--ui-muted);
    }

    .ile-empty__icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
        font-size: 1.6rem;
    }

    .ile-index-pagination {
        padding: 0.85rem 1.25rem;
        border-top: 1px solid var(--ui-border);
        background: var(--ui-soft);
    }
</style>
