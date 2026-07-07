@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .enrollments-index-page {
        --ui-radius: 14px;
        --ui-accent: #7c3aed;
        --ui-accent-rgb: 124, 58, 237;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(124, 58, 237, 0.06);
    }

    [data-theme-mode="dark"] .enrollments-index-page,
    [data-bs-theme="dark"] .enrollments-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(124, 58, 237, 0.12);
    }

    .enrollments-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(124, 58, 237, 0.22);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .enrollments-index-hero,
    [data-bs-theme="dark"] .enrollments-index-hero {
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .enrollments-index-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #7c3aed;
        background: rgba(124, 58, 237, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .enrollments-index-hero__icon,
    [data-bs-theme="dark"] .enrollments-index-hero__icon {
        color: #c4b5fd;
    }

    .enrollments-index-hero__content { flex: 1; min-width: 200px; }
    .enrollments-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .enrollments-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .enrollments-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .enrollments-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .enrollments-index-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .enrollments-index-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #7c3aed;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .enrollments-index-stat-mini__value,
    [data-bs-theme="dark"] .enrollments-index-stat-mini__value { color: #c4b5fd; }

    .enrollments-index-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .enrollments-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .enrollments-index-card,
    [data-bs-theme="dark"] .enrollments-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .enrollments-index-card__header {
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

    .enrollments-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(124, 58, 237, 0.12);
        color: var(--ui-accent);
    }

    .enrollments-index-card__body { padding: 1.25rem; }

    .enrollments-index-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.3rem;
    }

    .enrollments-index-filters .form-control,
    .enrollments-index-filters .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .enrollments-index-filters .form-control:focus,
    .enrollments-index-filters .form-select:focus {
        border-color: rgba(124, 58, 237, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.1);
    }

    .enrollments-bulk-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        background: rgba(220, 53, 69, 0.06);
        border: 1px dashed rgba(220, 53, 69, 0.28);
        margin-bottom: 1rem;
    }

    [data-theme-mode="dark"] .enrollments-bulk-toolbar,
    [data-bs-theme="dark"] .enrollments-bulk-toolbar {
        background: rgba(220, 53, 69, 0.1);
    }

    .enrollments-index-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        overflow: hidden;
    }

    .enrollments-index-table { margin-bottom: 0; }

    .enrollments-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .enrollments-index-table tbody td,
    .enrollments-index-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
    }

    .enrollments-index-table tbody tr { transition: background 0.15s ease; }
    .enrollments-index-table tbody tr:hover { background: var(--ui-soft); }
    .enrollments-index-table tbody tr:last-child td,
    .enrollments-index-table tbody tr:last-child th { border-bottom: none; }

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
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .ui-user-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
    }

    .ui-enrollment-subject {
        font-weight: 600;
        color: var(--default-text-color, inherit);
    }

    .ui-enrollment-subject-meta {
        font-size: 0.78rem;
        color: var(--ui-muted);
    }

    .ui-enrollment-status {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .ui-enrollment-status--active {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .ui-enrollment-status--pending,
    .ui-enrollment-status--suspended {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }

    .ui-enrollment-status--completed {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    .ui-enrollment-status--other {
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
    }

    [data-theme-mode="dark"] .ui-enrollment-status--active,
    [data-bs-theme="dark"] .ui-enrollment-status--active { color: #6ee7b7; }
    [data-theme-mode="dark"] .ui-enrollment-status--pending,
    [data-bs-theme="dark"] .ui-enrollment-status--pending,
    [data-theme-mode="dark"] .ui-enrollment-status--suspended,
    [data-bs-theme="dark"] .ui-enrollment-status--suspended { color: #fcd34d; }
    [data-theme-mode="dark"] .ui-enrollment-status--completed,
    [data-bs-theme="dark"] .ui-enrollment-status--completed { color: #7dd3fc; }

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

    .enrollments-index-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .enrollments-index-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .enrollments-index-pagination {
        padding: 1rem 1.25rem 0.25rem;
    }

    .enrollments-stats-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .enrollments-stat-card {
        padding: 1.15rem 1rem;
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        text-align: center;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }

    [data-theme-mode="dark"] .enrollments-stat-card,
    [data-bs-theme="dark"] .enrollments-stat-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .enrollments-stat-card__label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.35rem;
    }

    .enrollments-stat-card__value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .enrollments-stat-card--pending .enrollments-stat-card__value { color: #d97706; }
    .enrollments-stat-card--success .enrollments-stat-card__value { color: #059669; }

    [data-theme-mode="dark"] .enrollments-stat-card--pending .enrollments-stat-card__value,
    [data-bs-theme="dark"] .enrollments-stat-card--pending .enrollments-stat-card__value { color: #fcd34d; }
    [data-theme-mode="dark"] .enrollments-stat-card--success .enrollments-stat-card__value,
    [data-bs-theme="dark"] .enrollments-stat-card--success .enrollments-stat-card__value { color: #6ee7b7; }

    .enrollments-pending-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        background: rgba(124, 58, 237, 0.06);
        border: 1px dashed rgba(124, 58, 237, 0.2);
        margin-bottom: 1rem;
    }

    [data-theme-mode="dark"] .enrollments-pending-toolbar,
    [data-bs-theme="dark"] .enrollments-pending-toolbar {
        background: rgba(124, 58, 237, 0.1);
    }

    @media (max-width: 575.98px) {
        .enrollments-stats-row { grid-template-columns: 1fr; }
    }

    #enrollmentsTableContainer.is-loading {
        opacity: 0.5;
        pointer-events: none;
    }

    .enrollments-loading {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    @media (max-width: 1199.98px) {
        .enrollments-col-added-by { display: none; }
    }

    @media (max-width: 991.98px) {
        .enrollments-col-date { display: none; }
    }

    @media (max-width: 767.98px) {
        .enrollments-col-class { display: none; }
        .enrollments-index-hero__actions { width: 100%; }
        .enrollments-index-hero__actions .btn { flex: 1 1 auto; }
        .enrollments-index-stat-mini { width: 100%; }
    }

    @media (max-width: 575.98px) {
        .enrollments-index-card__body { padding: 1rem; }
        .enrollments-index-table thead th,
        .enrollments-index-table tbody td,
        .enrollments-index-table tbody th {
            padding: 0.65rem 0.75rem;
        }
    }

    /* بطاقات طلبات الصفوف المدفوعة المعلقة */
    .pending-purchase-card {
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: box-shadow 0.15s, border-color 0.15s;
    }

    .pending-purchase-card:hover {
        border-color: rgba(124, 58, 237, 0.25);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.1);
    }

    .pending-purchase-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1rem 0.75rem;
        border-bottom: 1px dashed var(--ui-border);
    }

    .pending-purchase-card__student {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        flex: 1;
    }

    .pending-purchase-card__avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        color: #fff;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        flex-shrink: 0;
    }

    .pending-purchase-card__identity {
        min-width: 0;
        flex: 1;
    }

    .pending-purchase-card__name {
        margin: 0 0 0.2rem;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pending-purchase-card__phone {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #059669;
        text-decoration: none;
    }

    .pending-purchase-card__phone:hover { color: #047857; }

    .pending-purchase-card__meta {
        font-size: 0.78rem;
        color: var(--ui-muted);
    }

    .pending-purchase-card__price-wrap {
        text-align: end;
        flex-shrink: 0;
    }

    .pending-purchase-card__price {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
        line-height: 1.2;
    }

    .pending-purchase-card__price small {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--ui-muted);
    }

    .pending-purchase-card__badge {
        display: inline-block;
        margin-top: 0.25rem;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: rgba(245, 158, 11, 0.15);
        color: #b45309;
    }

    .pending-purchase-card__body {
        padding: 0.85rem 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .pending-purchase-card__row {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: var(--ui-muted);
        line-height: 1.45;
    }

    .pending-purchase-card__row i {
        color: #7c3aed;
        font-size: 0.95rem;
        margin-top: 0.1rem;
        flex-shrink: 0;
    }

    .pending-purchase-card__row strong {
        color: var(--default-text-color, #0f172a);
        font-weight: 700;
    }

    .pending-purchase-card__row--expiry {
        padding: 0.55rem 0.65rem;
        border-radius: 10px;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .pending-purchase-card__row--expiry i { color: #2563eb; }

    .pending-purchase-card__hint {
        display: block;
        font-size: 0.72rem;
        color: var(--ui-muted);
        margin-top: 0.1rem;
    }

    .pending-purchase-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
        padding: 0.75rem 1rem 1rem;
        border-top: 1px solid var(--ui-border);
        background: rgba(124, 58, 237, 0.02);
    }

    .pending-purchase-card__actions .btn {
        border-radius: 9px;
        font-weight: 700;
        font-size: 0.8rem;
    }
</style>
