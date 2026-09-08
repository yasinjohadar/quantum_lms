<style>
    .activity-log-page {
        --ui-radius: 14px;
        --ui-accent: rgb(var(--primary-rgb, 13, 110, 253));
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
    }

    [data-theme-mode="dark"] .activity-log-page,
    [data-bs-theme="dark"] .activity-log-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .activity-log-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(13, 110, 253, 0.22);
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .activity-log-hero,
    [data-bs-theme="dark"] .activity-log-hero {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .activity-log-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .activity-log-hero__icon,
    [data-bs-theme="dark"] .activity-log-hero__icon { color: #7dd3fc; }

    .activity-log-hero__content { flex: 1; min-width: 200px; }
    .activity-log-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .activity-log-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .activity-log-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .activity-log-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0d6efd;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .activity-log-stat-mini__value,
    [data-bs-theme="dark"] .activity-log-stat-mini__value { color: #7dd3fc; }

    .activity-log-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .activity-log-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .activity-log-card,
    [data-bs-theme="dark"] .activity-log-card { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22); }

    .activity-log-card__header {
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

    .activity-log-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        color: var(--ui-accent);
    }

    .activity-log-card__body { padding: 1.25rem; }

    .activity-log-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.3rem;
    }

    .activity-log-filters .form-control,
    .activity-log-filters .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .activity-log-filters .form-control:focus,
    .activity-log-filters .form-select:focus {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .activity-log-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        overflow: hidden;
    }

    .activity-log-table { margin-bottom: 0; }

    .activity-log-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .activity-log-table tbody td,
    .activity-log-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
    }

    .activity-log-table tbody tr { transition: background 0.15s ease; }
    .activity-log-table tbody tr:hover { background: var(--ui-soft); }
    .activity-log-table tbody tr:last-child td,
    .activity-log-table tbody tr:last-child th { border-bottom: none; }

    .ui-user-cell { display: flex; align-items: center; gap: 0.65rem; min-width: 0; }

    .ui-user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
        color: #fff;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
    }

    .ui-user-name { font-weight: 600; color: var(--default-text-color, inherit); }

    .ui-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        border: none;
        display: inline-block;
    }

    .ui-status-badge--active { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .ui-status-badge--inactive { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .ui-status-badge--pending { background: rgba(13, 110, 253, 0.12); color: #0d6efd; }

    [data-theme-mode="dark"] .ui-status-badge--active,
    [data-bs-theme="dark"] .ui-status-badge--active { color: #6ee7b7; }
    [data-theme-mode="dark"] .ui-status-badge--inactive,
    [data-bs-theme="dark"] .ui-status-badge--inactive { color: #fca5a5; }
    [data-theme-mode="dark"] .ui-status-badge--pending,
    [data-bs-theme="dark"] .ui-status-badge--pending { color: #7dd3fc; }

    .ui-class-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    [data-theme-mode="dark"] .ui-class-pill,
    [data-bs-theme="dark"] .ui-class-pill { color: #7dd3fc; }

    .activity-log-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .activity-log-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .activity-log-pagination { padding-top: 1rem; }

    @media (max-width: 767.98px) {
        .activity-log-hero__actions { width: 100%; }
        .activity-log-hero__actions .btn { flex: 1; }
    }
</style>
