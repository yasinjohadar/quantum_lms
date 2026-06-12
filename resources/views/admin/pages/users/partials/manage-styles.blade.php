@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .users-manage-page {
        --um-radius: 14px;
        --um-accent: rgb(var(--primary-rgb, 13, 110, 253));
        --um-surface: var(--custom-card-bg, #fff);
        --um-border: var(--default-border, #e9ecef);
        --um-muted: var(--text-muted, #6c757d);
        --um-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
    }

    [data-theme-mode="dark"] .users-manage-page,
    [data-bs-theme="dark"] .users-manage-page {
        --um-surface: var(--custom-card-bg, #111a2e);
        --um-border: rgba(255, 255, 255, 0.1);
        --um-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .users-manage-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--um-radius);
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 100%);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.2);
        box-shadow: 0 8px 24px rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .users-manage-hero,
    [data-bs-theme="dark"] .users-manage-hero {
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .users-manage-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--um-accent);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.14);
        flex-shrink: 0;
    }

    .users-manage-hero__content { flex: 1; min-width: 200px; }
    .users-manage-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .users-manage-hero__subtitle { color: var(--um-muted); font-size: 0.875rem; margin-bottom: 0; }

    .users-manage-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .users-manage-hero__actions .btn {
        border-radius: 10px;
        font-weight: 600;
    }

    .users-manage-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--um-surface);
        border: 1px solid var(--um-border);
        min-width: 110px;
    }

    .users-manage-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--um-accent);
        line-height: 1.2;
    }

    .users-manage-stat-mini__label {
        font-size: 0.72rem;
        color: var(--um-muted);
    }

    .users-manage-card {
        border-radius: var(--um-radius);
        border: 1px solid var(--um-border);
        background: var(--um-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .users-manage-card,
    [data-bs-theme="dark"] .users-manage-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .users-manage-card__header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--um-border);
        background: var(--um-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .users-manage-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        color: var(--um-accent);
    }

    .users-manage-card__body { padding: 1.25rem; }

    .users-manage-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--um-muted);
        margin-bottom: 0.3rem;
    }

    .users-manage-filters .form-control,
    .users-manage-filters .form-select {
        border-radius: 10px;
        border-color: var(--um-border);
        font-size: 0.875rem;
    }

    .users-manage-filters .form-control:focus,
    .users-manage-filters .form-select:focus {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .users-manage-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--um-border);
        overflow: hidden;
    }

    .users-manage-table {
        margin-bottom: 0;
    }

    .users-manage-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
        color: var(--um-muted);
        background: var(--um-soft);
        border-bottom: 1px solid var(--um-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .users-manage-table tbody td,
    .users-manage-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--um-border);
    }

    .users-manage-table tbody tr {
        transition: background 0.15s ease;
    }

    .users-manage-table tbody tr:hover {
        background: var(--um-soft);
    }

    .users-manage-table tbody tr:last-child td,
    .users-manage-table tbody tr:last-child th {
        border-bottom: none;
    }

    .um-user-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .um-user-avatar {
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
        background: linear-gradient(135deg, #4a7cff, #2563eb);
    }

    .um-user-avatar--admin { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .um-user-avatar--teacher { background: linear-gradient(135deg, #10b981, #059669); }
    .um-user-avatar--supervisor { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .um-user-avatar--student { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
    .um-user-avatar--other { background: linear-gradient(135deg, #64748b, #475569); }

    .um-user-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .um-user-name:hover {
        color: var(--um-accent);
    }

    .um-type-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        display: inline-block;
    }

    .um-type-badge--طالب { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .um-type-badge--معلم { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .um-type-badge--مشرف { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
    .um-type-badge--أدمن { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .um-type-badge--مستخدم { background: rgba(100, 116, 139, 0.12); color: #475569; }

    [data-theme-mode="dark"] .um-type-badge--طالب,
    [data-bs-theme="dark"] .um-type-badge--طالب { color: #7dd3fc; }
    [data-theme-mode="dark"] .um-type-badge--معلم,
    [data-bs-theme="dark"] .um-type-badge--معلم { color: #6ee7b7; }
    [data-theme-mode="dark"] .um-type-badge--مشرف,
    [data-bs-theme="dark"] .um-type-badge--مشرف { color: #c4b5fd; }
    [data-theme-mode="dark"] .um-type-badge--أدمن,
    [data-bs-theme="dark"] .um-type-badge--أدمن { color: #fcd34d; }

    .um-role-pill {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        margin: 0.1rem;
        background: var(--um-soft);
        border: 1px solid var(--um-border);
        color: var(--um-muted);
    }

    .um-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
    }

    .um-status-badge--active {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .um-status-badge--inactive {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }

    [data-theme-mode="dark"] .um-status-badge--active,
    [data-bs-theme="dark"] .um-status-badge--active { color: #6ee7b7; }
    [data-theme-mode="dark"] .um-status-badge--inactive,
    [data-bs-theme="dark"] .um-status-badge--inactive { color: #fca5a5; }

    .um-phone-link {
        font-size: 0.85rem;
        font-weight: 500;
    }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .users-manage-pagination {
        padding-top: 1rem;
    }

    .users-manage-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--um-muted);
    }

    .users-manage-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    @media (max-width: 767.98px) {
        .users-manage-hero__actions {
            width: 100%;
        }
        .users-manage-hero__actions .btn {
            flex: 1;
        }
    }
</style>
