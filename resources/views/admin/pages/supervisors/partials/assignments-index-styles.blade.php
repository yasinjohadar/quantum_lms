@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .supervisors-page {
        --sv-radius: 14px;
        --sv-accent: #7c3aed;
        --sv-surface: var(--custom-card-bg, #fff);
        --sv-border: var(--default-border, #e9ecef);
        --sv-muted: var(--text-muted, #6c757d);
        --sv-soft: rgba(124, 58, 237, 0.06);
    }

    [data-theme-mode="dark"] .supervisors-page,
    [data-bs-theme="dark"] .supervisors-page {
        --sv-surface: var(--custom-card-bg, #111a2e);
        --sv-border: rgba(255, 255, 255, 0.1);
        --sv-soft: rgba(124, 58, 237, 0.14);
    }

    .supervisors-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--sv-radius);
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.16) 0%, rgba(124, 58, 237, 0.04) 100%);
        border: 1px solid rgba(124, 58, 237, 0.22);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .supervisors-hero,
    [data-bs-theme="dark"] .supervisors-hero {
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.22) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .supervisors-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--sv-accent);
        background: rgba(124, 58, 237, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .supervisors-hero__icon,
    [data-bs-theme="dark"] .supervisors-hero__icon { color: #c4b5fd; }

    .supervisors-hero__content { flex: 1; min-width: 200px; }
    .supervisors-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .supervisors-hero__subtitle { color: var(--sv-muted); font-size: 0.875rem; margin-bottom: 0; }

    .supervisors-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .supervisors-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .supervisors-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .supervisors-stats { grid-template-columns: 1fr; }
        .supervisors-hero__actions { width: 100%; }
        .supervisors-hero__actions .btn { flex: 1; }
    }

    .supervisors-stat-card {
        border-radius: var(--sv-radius);
        padding: 1rem 1.15rem;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .supervisors-stat-card:hover {
        transform: translateY(-2px);
    }

    .supervisors-stat-card__label {
        font-size: 0.78rem;
        font-weight: 600;
        opacity: 0.85;
        margin-bottom: 0.15rem;
    }

    .supervisors-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .supervisors-stat-card__icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        background: rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
    }

    .supervisors-stat-card--total {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.25);
    }

    .supervisors-stat-card--assigned {
        background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        color: #fff;
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.25);
    }

    .supervisors-stat-card--unassigned {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        color: #fff;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.25);
    }

    .supervisors-card {
        border-radius: var(--sv-radius);
        border: 1px solid var(--sv-border);
        background: var(--sv-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .supervisors-card,
    [data-bs-theme="dark"] .supervisors-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .supervisors-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--sv-border);
        background: var(--sv-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .supervisors-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(124, 58, 237, 0.12);
        color: var(--sv-accent);
    }

    [data-theme-mode="dark"] .supervisors-card__header-icon,
    [data-bs-theme="dark"] .supervisors-card__header-icon { color: #c4b5fd; }

    .supervisors-card__body { padding: 1.25rem; }

    .supervisors-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--sv-muted);
        margin-bottom: 0.3rem;
    }

    .supervisors-filters .form-control,
    .supervisors-filters .form-select {
        border-radius: 10px;
        border-color: var(--sv-border);
        font-size: 0.875rem;
    }

    .supervisors-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--sv-border);
        overflow: hidden;
        position: relative;
    }

    .supervisors-table { margin-bottom: 0; }

    .supervisors-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--sv-muted);
        background: var(--sv-soft);
        border-bottom: 1px solid var(--sv-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .supervisors-table tbody td,
    .supervisors-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--sv-border);
    }

    .supervisors-table tbody tr { transition: background 0.15s ease; }
    .supervisors-table tbody tr:hover { background: var(--sv-soft); }
    .supervisors-table tbody tr:last-child td { border-bottom: none; }

    .sv-user-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .sv-user-avatar {
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
        background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        overflow: hidden;
    }

    .sv-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sv-user-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .sv-user-name:hover { color: var(--sv-accent); }

    .sv-role-pill {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        background: var(--sv-soft);
        border: 1px solid var(--sv-border);
        color: var(--sv-muted);
        display: inline-block;
        margin: 0.1rem;
    }

    .sv-count-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        display: inline-block;
    }

    .sv-count-badge--class {
        background: rgba(79, 70, 229, 0.12);
        color: #4f46e5;
    }

    .sv-count-badge--subject {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    .sv-count-badge--quiz {
        background: rgba(245, 158, 11, 0.14);
        color: #d97706;
    }

    [data-theme-mode="dark"] .sv-count-badge--class,
    [data-bs-theme="dark"] .sv-count-badge--class { color: #a5b4fc; }
    [data-theme-mode="dark"] .sv-count-badge--subject,
    [data-bs-theme="dark"] .sv-count-badge--subject { color: #7dd3fc; }
    [data-theme-mode="dark"] .sv-count-badge--quiz,
    [data-bs-theme="dark"] .sv-count-badge--quiz { color: #fbbf24; }

    .sv-meta-list {
        font-size: 0.72rem;
        color: var(--sv-muted);
        margin-top: 0.25rem;
    }

    .sv-online-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
    }

    .sv-online-badge--on {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .sv-online-badge--off {
        background: rgba(100, 116, 139, 0.1);
        color: var(--sv-muted);
    }

    [data-theme-mode="dark"] .sv-online-badge--on,
    [data-bs-theme="dark"] .sv-online-badge--on { color: #6ee7b7; }

    .supervisors-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--sv-muted);
    }

    .supervisors-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .supervisors-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border-radius: 12px;
    }

    [data-theme-mode="dark"] .supervisors-loading-overlay,
    [data-bs-theme="dark"] .supervisors-loading-overlay {
        background: rgba(0, 0, 0, 0.35);
    }

    @include('admin.pages.users.partials.row-action-bar-styles')
</style>
