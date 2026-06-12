@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .admins-page {
        --ad-radius: 14px;
        --ad-accent: #be123c;
        --ad-surface: var(--custom-card-bg, #fff);
        --ad-border: var(--default-border, #e9ecef);
        --ad-muted: var(--text-muted, #6c757d);
        --ad-soft: rgba(190, 18, 60, 0.06);
    }

    [data-theme-mode="dark"] .admins-page,
    [data-bs-theme="dark"] .admins-page {
        --ad-surface: var(--custom-card-bg, #111a2e);
        --ad-border: rgba(255, 255, 255, 0.1);
        --ad-soft: rgba(190, 18, 60, 0.14);
    }

    .admins-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ad-radius);
        background: linear-gradient(135deg, rgba(190, 18, 60, 0.14) 0%, rgba(190, 18, 60, 0.04) 100%);
        border: 1px solid rgba(190, 18, 60, 0.22);
        box-shadow: 0 8px 24px rgba(190, 18, 60, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .admins-hero,
    [data-bs-theme="dark"] .admins-hero {
        background: linear-gradient(135deg, rgba(190, 18, 60, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .admins-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--ad-accent);
        background: rgba(190, 18, 60, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .admins-hero__icon,
    [data-bs-theme="dark"] .admins-hero__icon { color: #fda4af; }

    .admins-hero__content { flex: 1; min-width: 200px; }
    .admins-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .admins-hero__subtitle { color: var(--ad-muted); font-size: 0.875rem; margin-bottom: 0; }

    .admins-hero__stat {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ad-surface);
        border: 1px solid var(--ad-border);
        min-width: 110px;
    }

    .admins-hero__stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--ad-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .admins-hero__stat-value,
    [data-bs-theme="dark"] .admins-hero__stat-value { color: #fda4af; }

    .admins-hero__stat-label { font-size: 0.72rem; color: var(--ad-muted); }

    .admins-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .admins-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .admins-card {
        border-radius: var(--ad-radius);
        border: 1px solid var(--ad-border);
        background: var(--ad-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .admins-card,
    [data-bs-theme="dark"] .admins-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .admins-card__header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--ad-border);
        background: var(--ad-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .admins-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(190, 18, 60, 0.12);
        color: var(--ad-accent);
    }

    .admins-card__body { padding: 1.25rem; }

    .admins-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ad-muted);
        margin-bottom: 0.3rem;
    }

    .admins-filters .form-control,
    .admins-filters .form-select {
        border-radius: 10px;
        border-color: var(--ad-border);
        font-size: 0.875rem;
    }

    .admins-filters .form-control:focus,
    .admins-filters .form-select:focus {
        border-color: rgba(190, 18, 60, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(190, 18, 60, 0.1);
    }

    .admins-filters .input-group-text {
        border-radius: 10px 0 0 10px;
        border-color: var(--ad-border);
        background: var(--ad-soft);
    }

    .admins-filters .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }

    .admins-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ad-border);
        overflow: hidden;
        position: relative;
    }

    .admins-table { margin-bottom: 0; }

    .admins-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ad-muted);
        background: var(--ad-soft);
        border-bottom: 1px solid var(--ad-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .admins-table tbody td,
    .admins-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ad-border);
    }

    .admins-table tbody tr { transition: background 0.15s ease; }
    .admins-table tbody tr:hover { background: var(--ad-soft); }
    .admins-table tbody tr:last-child td,
    .admins-table tbody tr:last-child th { border-bottom: none; }

    .ad-admin-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .ad-admin-avatar {
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
        background: linear-gradient(135deg, #f43f5e, #be123c);
    }

    .ad-admin-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .ad-admin-name:hover { color: var(--ad-accent); }

    .ad-email-link {
        font-size: 0.85rem;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .ad-email-link:hover { color: var(--ad-accent); }

    .ad-phone-link { font-size: 0.85rem; font-weight: 500; }

    .ad-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    .ad-status-badge--active {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .ad-status-badge--inactive {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }

    [data-theme-mode="dark"] .ad-status-badge--active,
    [data-bs-theme="dark"] .ad-status-badge--active { color: #6ee7b7; }
    [data-theme-mode="dark"] .ad-status-badge--inactive,
    [data-bs-theme="dark"] .ad-status-badge--inactive { color: #fca5a5; }

    .admins-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border-radius: 12px;
    }

    .admins-loading-overlay.is-active { display: flex; }

    [data-theme-mode="dark"] .admins-loading-overlay,
    [data-bs-theme="dark"] .admins-loading-overlay {
        background: rgba(0, 0, 0, 0.35);
    }

    .admins-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ad-muted);
    }

    .admins-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
        color: var(--ad-accent);
    }

    .admins-pagination { padding-top: 1rem; display: flex; justify-content: center; }

    @include('admin.pages.users.partials.row-action-bar-styles')

    @media (max-width: 767.98px) {
        .admins-hero__actions { width: 100%; }
        .admins-hero__actions .btn { flex: 1; }
    }
</style>
