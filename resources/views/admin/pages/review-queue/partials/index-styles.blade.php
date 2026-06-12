@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .review-queue-page {
        --rq-radius: 14px;
        --rq-accent: #d97706;
        --rq-accent-2: #2563eb;
        --rq-surface: var(--custom-card-bg, #fff);
        --rq-border: var(--default-border, #e9ecef);
        --rq-muted: var(--text-muted, #6c757d);
        --rq-soft: rgba(217, 119, 6, 0.07);
    }

    [data-theme-mode="dark"] .review-queue-page,
    [data-bs-theme="dark"] .review-queue-page {
        --rq-surface: var(--custom-card-bg, #111a2e);
        --rq-border: rgba(255, 255, 255, 0.1);
        --rq-soft: rgba(217, 119, 6, 0.14);
    }

    .rq-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--rq-radius);
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.18) 0%, rgba(217, 119, 6, 0.04) 100%);
        border: 1px solid rgba(217, 119, 6, 0.24);
        box-shadow: 0 8px 28px rgba(217, 119, 6, 0.1);
    }

    [data-theme-mode="dark"] .rq-hero,
    [data-bs-theme="dark"] .rq-hero {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.22) 0%, rgba(0, 0, 0, 0.14) 100%);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
    }

    .rq-hero__icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: var(--rq-accent);
        background: rgba(217, 119, 6, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .rq-hero__icon,
    [data-bs-theme="dark"] .rq-hero__icon { color: #fbbf24; }

    .rq-hero__content { flex: 1; min-width: 220px; }
    .rq-hero__title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.2rem; }
    .rq-hero__subtitle { color: var(--rq-muted); font-size: 0.875rem; margin-bottom: 0; }
    .rq-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .rq-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .rq-stat {
        padding: 1rem 1.15rem;
        border-radius: var(--rq-radius);
        border: 1px solid var(--rq-border);
        background: var(--rq-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    [data-theme-mode="dark"] .rq-stat,
    [data-bs-theme="dark"] .rq-stat { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22); }

    .rq-stat::before {
        content: '';
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 4px 0 0 4px;
    }

    .rq-stat--lessons::before { background: var(--rq-accent); }
    .rq-stat--quizzes::before { background: var(--rq-accent-2); }

    .rq-stat__label {
        font-size: 0.78rem;
        color: var(--rq-muted);
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .rq-stat__value {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 0.35rem;
    }

    .rq-stat--lessons .rq-stat__value { color: var(--rq-accent); }
    .rq-stat--quizzes .rq-stat__value { color: var(--rq-accent-2); }

    [data-theme-mode="dark"] .rq-stat--lessons .rq-stat__value,
    [data-bs-theme="dark"] .rq-stat--lessons .rq-stat__value { color: #fbbf24; }

    .rq-stat__meta { font-size: 0.72rem; color: var(--rq-muted); }

    .rq-card {
        border-radius: var(--rq-radius);
        border: 1px solid var(--rq-border);
        background: var(--rq-surface);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    [data-theme-mode="dark"] .rq-card,
    [data-bs-theme="dark"] .rq-card { box-shadow: 0 4px 18px rgba(0, 0, 0, 0.24); }

    .rq-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid var(--rq-border);
        background: var(--rq-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .rq-card__header-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(217, 119, 6, 0.14);
        color: var(--rq-accent);
        margin-inline-end: 0.55rem;
    }

    .rq-card__body { padding: 1.15rem 1.25rem; }

    .rq-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.15rem 0;
    }

    .rq-nav__pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.95rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--rq-muted);
        background: var(--rq-surface);
        border: 1px solid var(--rq-border);
        transition: all 0.18s ease;
    }

    .rq-nav__pill:hover {
        color: var(--rq-accent);
        border-color: rgba(217, 119, 6, 0.35);
        background: var(--rq-soft);
    }

    .rq-nav__pill.is-active {
        color: #fff;
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.32);
    }

    .rq-nav__badge {
        font-size: 0.68rem;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        color: inherit;
    }

    .rq-nav__pill:not(.is-active) .rq-nav__badge {
        background: rgba(217, 119, 6, 0.14);
        color: var(--rq-accent);
    }

    .rq-filters .form-control,
    .rq-filters .form-select { border-radius: 10px; font-size: 0.875rem; }
    .rq-filters .btn { border-radius: 10px; font-weight: 600; }

    .rq-table-wrap { border-radius: 12px; border: 1px solid var(--rq-border); overflow: hidden; }

    .rq-table {
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    .rq-table thead th {
        background: var(--rq-soft);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: none;
        border-bottom: 1px solid var(--rq-border);
        white-space: nowrap;
        padding: 0.75rem 1rem;
    }

    .rq-table tbody td {
        padding: 0.8rem 1rem;
        vertical-align: middle;
        border-color: var(--rq-border);
    }

    .rq-table tbody tr:hover { background: rgba(217, 119, 6, 0.04); }

    .rq-item-title {
        font-weight: 600;
        line-height: 1.35;
        margin-bottom: 0.1rem;
    }

    .rq-item-meta {
        font-size: 0.72rem;
        color: var(--rq-muted);
    }

    .rq-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: rgba(217, 119, 6, 0.12);
        color: var(--rq-accent);
    }

    .rq-chip--subject { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .rq-chip--class { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .rq-status {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(245, 158, 11, 0.15);
        color: #b45309;
    }

    .rq-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.92rem;
        font-weight: 700;
        margin-bottom: 0.85rem;
        color: var(--rq-accent);
    }

    .rq-empty {
        text-align: center;
        padding: 2.75rem 1.5rem;
        color: var(--rq-muted);
    }

    .rq-empty i {
        font-size: 2.4rem;
        color: #16a34a;
        opacity: 0.75;
        margin-bottom: 0.75rem;
        display: block;
    }

    .rq-pagination { margin-top: 1rem; }

    @media (max-width: 767.98px) {
        .rq-hero { padding: 1rem; }
        .rq-card__body { padding: 1rem; }
        .rq-table thead { display: none; }
        .rq-table tbody tr {
            display: block;
            border-bottom: 1px solid var(--rq-border);
            padding: 0.75rem 0;
        }
        .rq-table tbody td {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            border: none;
            padding: 0.35rem 1rem;
        }
        .rq-table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--rq-muted);
            font-size: 0.75rem;
            flex-shrink: 0;
        }
    }

    @include('admin.pages.users.partials.row-action-bar-styles')
</style>
