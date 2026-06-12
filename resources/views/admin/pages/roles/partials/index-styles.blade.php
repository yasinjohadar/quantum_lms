@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .roles-page {
        --rl-radius: 14px;
        --rl-accent: #7c3aed;
        --rl-surface: var(--custom-card-bg, #fff);
        --rl-border: var(--default-border, #e9ecef);
        --rl-muted: var(--text-muted, #6c757d);
        --rl-soft: rgba(124, 58, 237, 0.06);
    }

    [data-theme-mode="dark"] .roles-page,
    [data-bs-theme="dark"] .roles-page {
        --rl-surface: var(--custom-card-bg, #111a2e);
        --rl-border: rgba(255, 255, 255, 0.1);
        --rl-soft: rgba(124, 58, 237, 0.14);
    }

    .roles-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--rl-radius);
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.16) 0%, rgba(124, 58, 237, 0.04) 100%);
        border: 1px solid rgba(124, 58, 237, 0.22);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .roles-hero,
    [data-bs-theme="dark"] .roles-hero {
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .roles-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--rl-accent);
        background: rgba(124, 58, 237, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .roles-hero__icon,
    [data-bs-theme="dark"] .roles-hero__icon { color: #c4b5fd; }

    .roles-hero__content { flex: 1; min-width: 200px; }
    .roles-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .roles-hero__subtitle { color: var(--rl-muted); font-size: 0.875rem; margin-bottom: 0; }

    .roles-hero__stat {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--rl-surface);
        border: 1px solid var(--rl-border);
        min-width: 110px;
    }

    .roles-hero__stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--rl-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .roles-hero__stat-value,
    [data-bs-theme="dark"] .roles-hero__stat-value { color: #c4b5fd; }

    .roles-hero__stat-label { font-size: 0.72rem; color: var(--rl-muted); }

    .roles-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .roles-card {
        border-radius: var(--rl-radius);
        border: 1px solid var(--rl-border);
        background: var(--rl-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    [data-theme-mode="dark"] .roles-card,
    [data-bs-theme="dark"] .roles-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .roles-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--rl-border);
        background: var(--rl-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .roles-card__header-left {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .roles-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(124, 58, 237, 0.12);
        color: var(--rl-accent);
    }

    .roles-card__body { padding: 1.25rem; }

    .roles-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--rl-border);
        overflow: hidden;
    }

    .roles-table { margin-bottom: 0; }

    .roles-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--rl-muted);
        background: var(--rl-soft);
        border-bottom: 1px solid var(--rl-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .roles-table tbody td,
    .roles-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--rl-border);
    }

    .roles-table tbody tr { transition: background 0.15s ease; }
    .roles-table tbody tr:hover { background: var(--rl-soft); }
    .roles-table tbody tr:last-child td,
    .roles-table tbody tr:last-child th { border-bottom: none; }

    .rl-role-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .rl-role-avatar {
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
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .rl-role-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        word-break: break-word;
        line-height: 1.35;
    }

    .rl-panel-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .rl-panel-badge--admin {
        background: rgba(124, 58, 237, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(124, 58, 237, 0.2);
    }

    .rl-panel-badge--student {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
        border: 1px solid rgba(14, 165, 233, 0.2);
    }

    [data-theme-mode="dark"] .rl-panel-badge--admin,
    [data-bs-theme="dark"] .rl-panel-badge--admin { color: #c4b5fd; }
    [data-theme-mode="dark"] .rl-panel-badge--student,
    [data-bs-theme="dark"] .rl-panel-badge--student { color: #7dd3fc; }

    .rl-perm-count {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        background: var(--rl-soft);
        border: 1px solid var(--rl-border);
        color: var(--rl-muted);
        white-space: nowrap;
    }

    .roles-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--rl-muted);
    }

    .roles-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
        color: var(--rl-accent);
    }

    @include('admin.pages.users.partials.row-action-bar-styles')

    @media (max-width: 767.98px) {
        .roles-hero__actions { width: 100%; }
        .roles-hero__actions .btn { flex: 1; }
    }
</style>
