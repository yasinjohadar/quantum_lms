@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .gami-page {
        --ui-radius: 14px;
        --ui-accent: #ea580c;
        --ui-accent-rgb: 234, 88, 12;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(234, 88, 12, 0.06);
    }

    [data-theme-mode="dark"] .gami-page,
    [data-bs-theme="dark"] .gami-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(234, 88, 12, 0.12);
    }

    .gami-page .container-fluid {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    @media (min-width: 1200px) {
        .gami-page .container-fluid {
            padding-left: 1.75rem;
            padding-right: 1.75rem;
        }
    }

    .gami-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(234, 88, 12, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.05) 100%);
        border: 1px solid rgba(234, 88, 12, 0.22);
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .gami-hero,
    [data-bs-theme="dark"] .gami-hero {
        background: linear-gradient(135deg, rgba(234, 88, 12, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .gami-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #ea580c;
        background: rgba(234, 88, 12, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .gami-hero__icon,
    [data-bs-theme="dark"] .gami-hero__icon { color: #fdba74; }

    .gami-hero__content { flex: 1; min-width: 200px; }
    .gami-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .gami-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .gami-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .gami-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .gami-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .gami-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #ea580c;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .gami-stat-mini__value,
    [data-bs-theme="dark"] .gami-stat-mini__value { color: #fdba74; }

    .gami-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .gami-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .gami-stat-card {
        padding: 1.15rem 1rem;
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }

    .gami-stat-card__icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.65rem;
        font-size: 1rem;
    }

    .gami-stat-card__label { font-size: 0.78rem; font-weight: 600; color: var(--ui-muted); margin-bottom: 0.25rem; }
    .gami-stat-card__value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }

    .gami-stat-card--primary .gami-stat-card__icon { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
    .gami-stat-card--primary .gami-stat-card__value { color: #2563eb; }
    .gami-stat-card--success .gami-stat-card__icon { background: rgba(5, 150, 105, 0.12); color: #059669; }
    .gami-stat-card--success .gami-stat-card__value { color: #059669; }
    .gami-stat-card--info .gami-stat-card__icon { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .gami-stat-card--info .gami-stat-card__value { color: #0284c7; }
    .gami-stat-card--warning .gami-stat-card__icon { background: rgba(234, 88, 12, 0.12); color: #ea580c; }
    .gami-stat-card--warning .gami-stat-card__value { color: #ea580c; }

    .gami-quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    .gami-quick-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        text-decoration: none;
        color: var(--default-text-color, inherit);
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .gami-quick-link:hover {
        border-color: rgba(234, 88, 12, 0.35);
        box-shadow: 0 6px 18px rgba(234, 88, 12, 0.1);
        transform: translateY(-1px);
        color: #ea580c;
    }

    .gami-quick-link__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .gami-quick-link__icon--primary { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
    .gami-quick-link__icon--success { background: rgba(5, 150, 105, 0.12); color: #059669; }
    .gami-quick-link__icon--info { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .gami-quick-link__icon--warning { background: rgba(234, 88, 12, 0.12); color: #ea580c; }
    .gami-quick-link__icon--purple { background: rgba(124, 58, 237, 0.12); color: #7c3aed; }
    .gami-quick-link__icon--rose { background: rgba(225, 29, 72, 0.1); color: #e11d48; }

    .gami-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .gami-card,
    [data-bs-theme="dark"] .gami-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .gami-card__header {
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

    .gami-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(234, 88, 12, 0.12);
        color: var(--ui-accent);
    }

    .gami-card__body { padding: 1.25rem; }

    .gami-card--flush > .gami-card__body { padding: 0; }

    .gami-form-card > .gami-card__body { padding: 1.5rem 1.75rem; }

    .gami-form-card .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.35rem;
    }

    .gami-form-card .form-control,
    .gami-form-card .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
    }

    .gami-form-card .form-control:focus,
    .gami-form-card .form-select:focus {
        border-color: rgba(234, 88, 12, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(234, 88, 12, 0.1);
    }

    .gami-form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding-top: 1.25rem;
        margin-top: 0.5rem;
        border-top: 1px solid var(--ui-border);
    }

    .gami-form-actions .btn { border-radius: 10px; font-weight: 600; }

    .gami-table-wrap { overflow-x: auto; }

    .gami-table { margin-bottom: 0; min-width: 720px; }

    .gami-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .gami-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
        font-size: 0.875rem;
    }

    .gami-table tbody tr { transition: background 0.15s ease; }
    .gami-table tbody tr:hover { background: var(--ui-soft); }
    .gami-table tbody tr:last-child td { border-bottom: none; }

    .gami-item-cell {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-width: 0;
    }

    .gami-item-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--ui-soft);
        flex-shrink: 0;
        font-size: 1rem;
    }

    .gami-item-name { font-weight: 600; }

    .gami-type-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
        white-space: nowrap;
    }

    .gami-status {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .gami-status--active { background: rgba(5, 150, 105, 0.12); color: #059669; }
    .gami-status--inactive { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .gami-status--yes { background: rgba(5, 150, 105, 0.12); color: #059669; }
    .gami-status--no { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    [data-theme-mode="dark"] .gami-status--active,
    [data-bs-theme="dark"] .gami-status--active,
    [data-theme-mode="dark"] .gami-status--yes,
    [data-bs-theme="dark"] .gami-status--yes { color: #6ee7b7; }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .gami-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .gami-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
        color: #ea580c;
    }

    .gami-info-box {
        padding: 1rem 1.15rem;
        border-radius: 10px;
        background: rgba(14, 165, 233, 0.08);
        border: 1px solid rgba(14, 165, 233, 0.2);
        font-size: 0.875rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .gami-hero__actions { width: 100%; }
        .gami-hero__actions .btn { flex: 1 1 auto; }
        .gami-stat-mini { width: 100%; }
        .gami-stat-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 575.98px) {
        .gami-page .container-fluid { padding-left: 1rem; padding-right: 1rem; }
        .gami-form-card > .gami-card__body { padding: 1.15rem 1.1rem; }
        .gami-stat-grid { grid-template-columns: 1fr; }
        .gami-quick-grid { grid-template-columns: 1fr; }
    }
</style>
