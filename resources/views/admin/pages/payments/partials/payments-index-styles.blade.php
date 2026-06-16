@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .payments-index-page {
        --ui-radius: 14px;
        --ui-accent: #0891b2;
        --ui-accent-rgb: 8, 145, 178;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(8, 145, 178, 0.06);
    }

    [data-theme-mode="dark"] .payments-index-page,
    [data-bs-theme="dark"] .payments-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(8, 145, 178, 0.12);
    }

    .payments-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(8, 145, 178, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(8, 145, 178, 0.22);
        box-shadow: 0 8px 24px rgba(8, 145, 178, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .payments-index-hero,
    [data-bs-theme="dark"] .payments-index-hero {
        background: linear-gradient(135deg, rgba(8, 145, 178, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .payments-index-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #0891b2;
        background: rgba(8, 145, 178, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .payments-index-hero__icon,
    [data-bs-theme="dark"] .payments-index-hero__icon { color: #67e8f9; }

    .payments-index-hero__content { flex: 1; min-width: 200px; }
    .payments-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .payments-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .payments-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .payments-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .payments-index-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .payments-index-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0891b2;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .payments-index-stat-mini__value,
    [data-bs-theme="dark"] .payments-index-stat-mini__value { color: #67e8f9; }

    .payments-index-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .payments-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .payments-index-card,
    [data-bs-theme="dark"] .payments-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .payments-index-card__header {
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

    .payments-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(8, 145, 178, 0.12);
        color: var(--ui-accent);
    }

    .payments-index-card__body { padding: 1.25rem; }

    .payments-index-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.3rem;
    }

    .payments-index-filters .form-control,
    .payments-index-filters .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .payments-index-filters .form-control:focus,
    .payments-index-filters .form-select:focus {
        border-color: rgba(8, 145, 178, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(8, 145, 178, 0.1);
    }

    .payments-index-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        overflow: hidden;
    }

    .payments-index-table { margin-bottom: 0; }

    .payments-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .payments-index-table tbody td,
    .payments-index-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
    }

    .payments-index-table tbody tr { transition: background 0.15s ease; }
    .payments-index-table tbody tr:hover { background: var(--ui-soft); }
    .payments-index-table tbody tr:last-child td,
    .payments-index-table tbody tr:last-child th { border-bottom: none; }

    .ui-user-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

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
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }

    .ui-user-name { font-weight: 600; color: var(--default-text-color, inherit); }

    .ui-payment-amount {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0891b2;
        white-space: nowrap;
    }

    [data-theme-mode="dark"] .ui-payment-amount,
    [data-bs-theme="dark"] .ui-payment-amount { color: #67e8f9; }

    .ui-payment-item { font-weight: 600; }
    .ui-payment-item-meta { font-size: 0.78rem; color: var(--ui-muted); }

    .ui-status-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .ui-status-pill--success { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .ui-status-pill--warning { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .ui-status-pill--danger { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .ui-status-pill--info { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .ui-status-pill--secondary { background: rgba(100, 116, 139, 0.12); color: #64748b; }
    .ui-status-pill--dark { background: rgba(30, 41, 59, 0.12); color: #334155; }

    [data-theme-mode="dark"] .ui-status-pill--success,
    [data-bs-theme="dark"] .ui-status-pill--success { color: #6ee7b7; }
    [data-theme-mode="dark"] .ui-status-pill--warning,
    [data-bs-theme="dark"] .ui-status-pill--warning { color: #fcd34d; }
    [data-theme-mode="dark"] .ui-status-pill--danger,
    [data-bs-theme="dark"] .ui-status-pill--danger { color: #fca5a5; }
    [data-theme-mode="dark"] .ui-status-pill--info,
    [data-bs-theme="dark"] .ui-status-pill--info { color: #7dd3fc; }

    .ui-method-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .ui-method-pill--wallet { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .ui-method-pill--iban { background: rgba(124, 58, 237, 0.12); color: #7c3aed; }
    .ui-method-pill--custom { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .ui-method-pill--other { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .ui-date-cell {
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .ui-date-cell small {
        display: block;
        font-size: 0.72rem;
        color: var(--ui-muted);
        font-weight: 400;
    }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .payments-index-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .payments-index-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .payments-index-pagination { padding: 1rem 1.25rem 0.25rem; }

    @media (max-width: 1199.98px) {
        .payments-col-purchase-status { display: none; }
    }

    @media (max-width: 991.98px) {
        .payments-col-method { display: none; }
        .payments-col-date { display: none; }
    }

    @media (max-width: 767.98px) {
        .payments-index-hero__actions { width: 100%; }
        .payments-index-hero__actions .btn { flex: 1 1 auto; }
        .payments-index-stat-mini { width: 100%; }
        .payments-col-item-type { display: none; }
    }

    @media (max-width: 575.98px) {
        .payments-index-card__body { padding: 1rem; }
    }
</style>
