@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .subjects-page {
        --sb-radius: 14px;
        --sb-accent: #d97706;
        --sb-surface: var(--custom-card-bg, #fff);
        --sb-border: var(--default-border, #e9ecef);
        --sb-muted: var(--text-muted, #6c757d);
        --sb-soft: rgba(217, 119, 6, 0.06);
    }

    [data-theme-mode="dark"] .subjects-page,
    [data-bs-theme="dark"] .subjects-page {
        --sb-surface: var(--custom-card-bg, #111a2e);
        --sb-border: rgba(255, 255, 255, 0.1);
        --sb-soft: rgba(217, 119, 6, 0.14);
    }

    .subjects-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--sb-radius);
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.16) 0%, rgba(217, 119, 6, 0.04) 100%);
        border: 1px solid rgba(217, 119, 6, 0.22);
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .subjects-hero,
    [data-bs-theme="dark"] .subjects-hero {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .subjects-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--sb-accent);
        background: rgba(217, 119, 6, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .subjects-hero__icon,
    [data-bs-theme="dark"] .subjects-hero__icon { color: #fcd34d; }

    .subjects-hero__content { flex: 1; min-width: 200px; }
    .subjects-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .subjects-hero__subtitle { color: var(--sb-muted); font-size: 0.875rem; margin-bottom: 0; }

    .subjects-hero__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .subjects-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .subjects-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--sb-surface);
        border: 1px solid var(--sb-border);
        min-width: 110px;
    }

    .subjects-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--sb-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .subjects-stat-mini__value,
    [data-bs-theme="dark"] .subjects-stat-mini__value { color: #fcd34d; }

    .subjects-stat-mini__label { font-size: 0.72rem; color: var(--sb-muted); }

    .subjects-card {
        border-radius: var(--sb-radius);
        border: 1px solid var(--sb-border);
        background: var(--sb-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .subjects-card,
    [data-bs-theme="dark"] .subjects-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .subjects-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--sb-border);
        background: var(--sb-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .subjects-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(217, 119, 6, 0.12);
        color: var(--sb-accent);
    }

    .subjects-card__body { padding: 1.25rem; }

    .subjects-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--sb-muted);
        margin-bottom: 0.3rem;
    }

    .subjects-filters .form-control,
    .subjects-filters .form-select {
        border-radius: 10px;
        border-color: var(--sb-border);
        font-size: 0.875rem;
    }

    .subjects-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--sb-border);
        overflow: hidden;
        position: relative;
    }

    .subjects-table { margin-bottom: 0; }

    .subjects-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--sb-muted);
        background: var(--sb-soft);
        border-bottom: 1px solid var(--sb-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .subjects-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--sb-border);
    }

    .subjects-table tbody tr { transition: background 0.15s ease; }
    .subjects-table tbody tr:hover { background: var(--sb-soft); }
    .subjects-table tbody tr:last-child td { border-bottom: none; }

    .sb-sort-handle {
        color: var(--sb-muted);
        cursor: grab;
        opacity: 0.5;
        transition: opacity 0.15s ease;
    }

    .sb-sort-handle:hover { opacity: 1; }
    .sb-sort-handle:active { cursor: grabbing; }

    .sb-subject-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .sb-subject-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--sb-border);
        background: var(--sb-soft);
    }

    .sb-subject-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .sb-subject-name:hover { color: var(--sb-accent); }

    .sb-class-meta {
        font-size: 0.78rem;
        color: var(--sb-muted);
    }

    .sb-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
    }

    .sb-status-badge--active { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .sb-status-badge--inactive { background: rgba(220, 53, 69, 0.12); color: #dc3545; }

    .subjects-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border-radius: 12px;
    }

    [data-theme-mode="dark"] .subjects-loading-overlay,
    [data-bs-theme="dark"] .subjects-loading-overlay {
        background: rgba(0, 0, 0, 0.35);
    }

    .subjects-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--sb-muted);
    }

    .subjects-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .subjects-pagination { padding-top: 1rem; }

    @media (max-width: 767.98px) {
        .subjects-hero__actions { width: 100%; }
        .subjects-hero__actions .btn { flex: 1; }
    }

    @include('admin.pages.users.partials.row-action-bar-styles')
</style>
