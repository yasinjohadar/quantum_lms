@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .teachers-page {
        --tv-radius: 14px;
        --tv-accent: #059669;
        --tv-surface: var(--custom-card-bg, #fff);
        --tv-border: var(--default-border, #e9ecef);
        --tv-muted: var(--text-muted, #6c757d);
        --tv-soft: rgba(5, 150, 105, 0.06);
    }

    [data-theme-mode="dark"] .teachers-page,
    [data-bs-theme="dark"] .teachers-page {
        --tv-surface: var(--custom-card-bg, #111a2e);
        --tv-border: rgba(255, 255, 255, 0.1);
        --tv-soft: rgba(5, 150, 105, 0.14);
    }

    .teachers-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--tv-radius);
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.16) 0%, rgba(5, 150, 105, 0.04) 100%);
        border: 1px solid rgba(5, 150, 105, 0.22);
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .teachers-hero,
    [data-bs-theme="dark"] .teachers-hero {
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .teachers-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--tv-accent);
        background: rgba(5, 150, 105, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .teachers-hero__icon,
    [data-bs-theme="dark"] .teachers-hero__icon { color: #6ee7b7; }

    .teachers-hero__content { flex: 1; min-width: 200px; }
    .teachers-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .teachers-hero__subtitle { color: var(--tv-muted); font-size: 0.875rem; margin-bottom: 0; }

    .teachers-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .teachers-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .teachers-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .teachers-stats { grid-template-columns: 1fr; }
        .teachers-hero__actions { width: 100%; }
        .teachers-hero__actions .btn { flex: 1; }
    }

    .teachers-stat-card {
        border-radius: var(--tv-radius);
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        transition: transform 0.2s ease;
        color: #fff;
    }

    .teachers-stat-card:hover { transform: translateY(-2px); }

    .teachers-stat-card__label { font-size: 0.78rem; font-weight: 600; opacity: 0.85; }
    .teachers-stat-card__value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }

    .teachers-stat-card__icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        background: rgba(255, 255, 255, 0.2);
    }

    .teachers-stat-card--total {
        background: linear-gradient(135deg, #047857, #059669);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.25);
    }

    .teachers-stat-card--assigned {
        background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.25);
    }

    .teachers-stat-card--unassigned {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.25);
    }

    .teachers-week-banner {
        font-size: 0.8rem;
        color: var(--tv-muted);
        padding: 0.65rem 1rem;
        border-radius: 10px;
        background: var(--tv-soft);
        border: 1px solid var(--tv-border);
        margin-bottom: 1rem;
    }

    .teachers-card {
        border-radius: var(--tv-radius);
        border: 1px solid var(--tv-border);
        background: var(--tv-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .teachers-card,
    [data-bs-theme="dark"] .teachers-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .teachers-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--tv-border);
        background: var(--tv-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .teachers-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(5, 150, 105, 0.12);
        color: var(--tv-accent);
    }

    [data-theme-mode="dark"] .teachers-card__header-icon,
    [data-bs-theme="dark"] .teachers-card__header-icon { color: #6ee7b7; }

    .teachers-card__body { padding: 1.25rem; }

    .teachers-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--tv-muted);
        margin-bottom: 0.3rem;
    }

    .teachers-filters .form-control,
    .teachers-filters .form-select {
        border-radius: 10px;
        border-color: var(--tv-border);
        font-size: 0.875rem;
    }

    .teachers-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--tv-border);
        overflow: hidden;
    }

    .teachers-table { margin-bottom: 0; }

    .teachers-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--tv-muted);
        background: var(--tv-soft);
        border-bottom: 1px solid var(--tv-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .teachers-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--tv-border);
    }

    .teachers-table tbody tr { transition: background 0.15s ease; }
    .teachers-table tbody tr:hover { background: var(--tv-soft); }
    .teachers-table tbody tr:last-child td { border-bottom: none; }

    .tv-user-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .tv-user-avatar {
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
        background: linear-gradient(135deg, #10b981, #047857);
        overflow: hidden;
    }

    .tv-user-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .tv-user-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .tv-user-name:hover { color: var(--tv-accent); }

    .tv-role-pill {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        background: var(--tv-soft);
        border: 1px solid var(--tv-border);
        color: var(--tv-muted);
        display: inline-block;
        margin: 0.1rem;
    }

    .tv-count-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        display: inline-block;
    }

    .tv-count-badge--class {
        background: rgba(5, 150, 105, 0.12);
        color: #047857;
    }

    .tv-count-badge--subject {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    [data-theme-mode="dark"] .tv-count-badge--class,
    [data-bs-theme="dark"] .tv-count-badge--class { color: #6ee7b7; }
    [data-theme-mode="dark"] .tv-count-badge--subject,
    [data-bs-theme="dark"] .tv-count-badge--subject { color: #7dd3fc; }

    .tv-meta-list { font-size: 0.72rem; color: var(--tv-muted); margin-top: 0.25rem; }

    .tv-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
    }

    .tv-status-badge--active { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .tv-status-badge--inactive { background: rgba(220, 53, 69, 0.12); color: #dc3545; }

    .tv-online-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
    }

    .tv-online-badge--on { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .tv-online-badge--off { background: rgba(100, 116, 139, 0.1); color: var(--tv-muted); }

    .tv-progress-box {
        font-size: 0.72rem;
        line-height: 1.5;
        min-width: 140px;
    }

    .tv-progress-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem;
        margin-bottom: 0.2rem;
    }

    .tv-progress-label { color: var(--tv-muted); font-weight: 600; }

    .tv-progress-pill {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.15rem 0.4rem;
        border-radius: 6px;
    }

    .tv-progress-pill--high { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .tv-progress-pill--mid { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .tv-progress-pill--low { background: rgba(245, 158, 11, 0.15); color: #d97706; }

    .tv-progress-link {
        font-size: 0.72rem;
        font-weight: 600;
        text-decoration: none;
    }

    .teachers-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--tv-muted);
    }

    .teachers-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .teachers-pagination { padding-top: 1rem; }

    @include('admin.pages.users.partials.row-action-bar-styles')
</style>
