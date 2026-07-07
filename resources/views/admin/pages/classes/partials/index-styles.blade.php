@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .classes-page {
        --cl-radius: 14px;
        --cl-accent: #2563eb;
        --cl-surface: var(--custom-card-bg, #fff);
        --cl-border: var(--default-border, #e9ecef);
        --cl-muted: var(--text-muted, #6c757d);
        --cl-soft: rgba(37, 99, 235, 0.06);
    }

    [data-theme-mode="dark"] .classes-page,
    [data-bs-theme="dark"] .classes-page {
        --cl-surface: var(--custom-card-bg, #111a2e);
        --cl-border: rgba(255, 255, 255, 0.1);
        --cl-soft: rgba(37, 99, 235, 0.14);
    }

    .classes-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--cl-radius);
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.16) 0%, rgba(37, 99, 235, 0.04) 100%);
        border: 1px solid rgba(37, 99, 235, 0.22);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .classes-hero,
    [data-bs-theme="dark"] .classes-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .classes-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--cl-accent);
        background: rgba(37, 99, 235, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .classes-hero__icon,
    [data-bs-theme="dark"] .classes-hero__icon { color: #93c5fd; }

    .classes-hero__content { flex: 1; min-width: 200px; }
    .classes-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .classes-hero__subtitle { color: var(--cl-muted); font-size: 0.875rem; margin-bottom: 0; }

    .classes-hero__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .classes-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .classes-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--cl-surface);
        border: 1px solid var(--cl-border);
        min-width: 110px;
    }

    .classes-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--cl-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .classes-stat-mini__value,
    [data-bs-theme="dark"] .classes-stat-mini__value { color: #93c5fd; }

    .classes-stat-mini__label { font-size: 0.72rem; color: var(--cl-muted); }

    .classes-card {
        border-radius: var(--cl-radius);
        border: 1px solid var(--cl-border);
        background: var(--cl-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .classes-card,
    [data-bs-theme="dark"] .classes-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .classes-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--cl-border);
        background: var(--cl-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .classes-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: var(--cl-accent);
    }

    .classes-card__body { padding: 1.25rem; }

    .classes-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--cl-muted);
        margin-bottom: 0.3rem;
    }

    .classes-filters .form-control,
    .classes-filters .form-select {
        border-radius: 10px;
        border-color: var(--cl-border);
        font-size: 0.875rem;
    }

    .classes-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--cl-border);
        overflow: hidden;
        position: relative;
    }

    .classes-table { margin-bottom: 0; }

    .classes-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cl-muted);
        background: var(--cl-soft);
        border-bottom: 1px solid var(--cl-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .classes-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--cl-border);
    }

    .classes-table tbody tr { transition: background 0.15s ease; }
    .classes-table tbody tr:hover { background: var(--cl-soft); }
    .classes-table tbody tr:last-child td { border-bottom: none; }

    .cl-sort-handle {
        color: var(--cl-muted);
        cursor: grab;
        opacity: 0.5;
        transition: opacity 0.15s ease;
    }

    .cl-sort-handle:hover { opacity: 1; }
    .cl-sort-handle:active { cursor: grabbing; }

    .cl-class-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .cl-class-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--cl-border);
        background: var(--cl-soft);
    }

    .cl-class-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .cl-class-name:hover { color: var(--cl-accent); }

    .cl-stage-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        display: inline-block;
    }

    [data-theme-mode="dark"] .cl-stage-pill,
    [data-bs-theme="dark"] .cl-stage-pill { color: #93c5fd; }

    .cl-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
    }

    .cl-status-badge--active { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .cl-status-badge--inactive { background: rgba(220, 53, 69, 0.12); color: #dc3545; }

    .cl-subscription-end {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.55rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
        white-space: nowrap;
    }

    .cl-subscription-end--active {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .cl-subscription-end--expired {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .cl-subscription-end__tag {
        font-size: 0.62rem;
        padding: 0.1rem 0.35rem;
        border-radius: 6px;
        background: rgba(220, 53, 69, 0.15);
        margin-inline-start: 0.15rem;
    }

    [data-theme-mode="dark"] .cl-subscription-end--active,
    [data-bs-theme="dark"] .cl-subscription-end--active { color: #93c5fd; }

    [data-theme-mode="dark"] .cl-subscription-end--expired,
    [data-bs-theme="dark"] .cl-subscription-end--expired { color: #f87171; }

    .classes-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border-radius: 12px;
    }

    [data-theme-mode="dark"] .classes-loading-overlay,
    [data-bs-theme="dark"] .classes-loading-overlay {
        background: rgba(0, 0, 0, 0.35);
    }

    .classes-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--cl-muted);
    }

    .classes-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .classes-pagination { padding-top: 1rem; }

    @media (max-width: 767.98px) {
        .classes-hero__actions { width: 100%; }
        .classes-hero__actions .btn { flex: 1; }
    }

    @include('admin.pages.users.partials.row-action-bar-styles')
</style>
