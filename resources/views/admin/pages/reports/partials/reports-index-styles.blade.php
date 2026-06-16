@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .reports-index-page {
        --ui-radius: 14px;
        --ui-accent: #059669;
        --ui-accent-rgb: 5, 150, 105;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(5, 150, 105, 0.06);
    }

    [data-theme-mode="dark"] .reports-index-page,
    [data-bs-theme="dark"] .reports-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(5, 150, 105, 0.12);
    }

    .reports-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(5, 150, 105, 0.22);
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .reports-index-hero,
    [data-bs-theme="dark"] .reports-index-hero {
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .reports-index-hero__icon {
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

    [data-theme-mode="dark"] .reports-index-hero__icon,
    [data-bs-theme="dark"] .reports-index-hero__icon { color: #6ee7b7; }

    .reports-index-hero__content { flex: 1; min-width: 200px; }
    .reports-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .reports-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .reports-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .reports-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .reports-index-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .reports-index-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #059669;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .reports-index-stat-mini__value,
    [data-bs-theme="dark"] .reports-index-stat-mini__value { color: #6ee7b7; }

    .reports-index-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .reports-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .reports-index-card,
    [data-bs-theme="dark"] .reports-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .reports-index-card__header {
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

    .reports-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(5, 150, 105, 0.12);
        color: var(--ui-accent);
    }

    .reports-index-card__body { padding: 1.25rem; }

    .reports-type-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .reports-type-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 0.9rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--ui-border);
        color: var(--default-text-color, inherit);
        background: var(--ui-surface);
        transition: all 0.15s ease;
    }

    .reports-type-tab:hover {
        border-color: rgba(5, 150, 105, 0.35);
        color: #059669;
        background: var(--ui-soft);
    }

    .reports-type-tab.is-active {
        background: #059669;
        border-color: #059669;
        color: #fff;
    }

    [data-theme-mode="dark"] .reports-type-tab.is-active,
    [data-bs-theme="dark"] .reports-type-tab.is-active {
        background: #10b981;
        border-color: #10b981;
    }

    .reports-templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }

    .reports-template-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .reports-template-card:hover {
        border-color: rgba(5, 150, 105, 0.35);
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.1);
    }

    [data-theme-mode="dark"] .reports-template-card,
    [data-bs-theme="dark"] .reports-template-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .reports-template-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 1rem 1.15rem 0.75rem;
        border-bottom: 1px solid var(--ui-border);
    }

    .reports-template-card__title {
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.4;
    }

    .reports-template-card__body {
        padding: 1rem 1.15rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .reports-template-card__desc {
        color: var(--ui-muted);
        font-size: 0.85rem;
        margin-bottom: 1rem;
        flex: 1;
    }

    .reports-type-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        white-space: nowrap;
    }

    .reports-type-badge--student {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    .reports-type-badge--course {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .reports-type-badge--system {
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
    }

    [data-theme-mode="dark"] .reports-type-badge--student,
    [data-bs-theme="dark"] .reports-type-badge--student { color: #7dd3fc; }
    [data-theme-mode="dark"] .reports-type-badge--course,
    [data-bs-theme="dark"] .reports-type-badge--course { color: #6ee7b7; }
    [data-theme-mode="dark"] .reports-type-badge--system,
    [data-bs-theme="dark"] .reports-type-badge--system { color: #cbd5e1; }

    .reports-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .reports-status-badge--active {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .reports-status-badge--inactive {
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
    }

    [data-theme-mode="dark"] .reports-status-badge--active,
    [data-bs-theme="dark"] .reports-status-badge--active { color: #6ee7b7; }

    .reports-index-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        overflow: hidden;
    }

    .reports-index-table { margin-bottom: 0; }

    .reports-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .reports-index-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
    }

    .reports-index-table tbody tr { transition: background 0.15s ease; }
    .reports-index-table tbody tr:hover { background: var(--ui-soft); }
    .reports-index-table tbody tr:last-child td { border-bottom: none; }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .reports-index-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .reports-index-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
        color: #059669;
    }

    .reports-form-card .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.35rem;
    }

    .reports-form-card .form-control,
    .reports-form-card .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
    }

    .reports-form-card .form-control:focus,
    .reports-form-card .form-select:focus {
        border-color: rgba(5, 150, 105, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.1);
    }

    .reports-selected-template {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: rgba(5, 150, 105, 0.08);
        border: 1px solid rgba(5, 150, 105, 0.2);
        margin-bottom: 1.25rem;
        font-size: 0.9rem;
    }

    .reports-selected-template i { color: #059669; font-size: 1.1rem; }

    .reports-export-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        justify-content: flex-end;
    }

    .reports-export-toolbar .btn { border-radius: 10px; font-weight: 600; }

    .reports-index-page .custom-card,
    .reports-index-page .report-section-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    .reports-index-page .custom-card > .card-header:not(.bg-primary):not(.text-white) {
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0.9rem 1.25rem;
    }

    .reports-index-page .custom-card > .card-header.bg-primary {
        background: linear-gradient(135deg, #059669, #047857) !important;
        border-bottom: none;
    }

    .reports-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .reports-stat-card {
        padding: 1.15rem 1rem;
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        text-align: center;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }

    .reports-stat-card__label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.35rem;
    }

    .reports-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .reports-stat-card--primary .reports-stat-card__value { color: #059669; }
    .reports-stat-card--info .reports-stat-card__value { color: #0284c7; }
    .reports-stat-card--warning .reports-stat-card__value { color: #d97706; }
    .reports-stat-card--success .reports-stat-card__value { color: #16a34a; }

    .reports-export-footer {
        text-align: center;
        padding: 1.5rem;
    }

    .reports-export-footer__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        margin-top: 1rem;
    }

    .reports-export-footer__actions .btn { border-radius: 10px; font-weight: 600; }

    @media (max-width: 991.98px) {
        .reports-col-desc { display: none; }
    }

    @media (max-width: 767.98px) {
        .reports-index-hero__actions { width: 100%; }
        .reports-index-hero__actions .btn { flex: 1 1 auto; }
        .reports-index-stat-mini { width: 100%; }
        .reports-export-toolbar { justify-content: stretch; }
        .reports-export-toolbar .btn,
        .reports-export-toolbar .btn-group { flex: 1 1 auto; }
        .reports-type-tabs { gap: 0.35rem; }
        .reports-type-tab { flex: 1 1 calc(50% - 0.35rem); justify-content: center; font-size: 0.78rem; padding: 0.45rem 0.5rem; }
    }

    @media (max-width: 575.98px) {
        .reports-index-card__body { padding: 1rem; }
        .reports-col-default { display: none; }
        .reports-type-tab { flex: 1 1 100%; }
        .reports-stat-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
