@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .quiz-show-page {
        --qs-radius: 16px;
        --qs-accent: #0d9488;
        --qs-accent-2: #2563eb;
        --qs-warning: #d97706;
        --qs-surface: var(--custom-card-bg, #fff);
        --qs-border: var(--default-border, #e9ecef);
        --qs-muted: var(--text-muted, #6c757d);
        --qs-soft: rgba(13, 148, 136, 0.08);
    }

    [data-theme-mode="dark"] .quiz-show-page,
    [data-bs-theme="dark"] .quiz-show-page {
        --qs-surface: var(--custom-card-bg, #111a2e);
        --qs-border: rgba(255, 255, 255, 0.1);
        --qs-soft: rgba(13, 148, 136, 0.16);
    }

    .qs-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1rem 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: var(--qs-radius);
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.16) 0%, rgba(37, 99, 235, 0.05) 100%);
        border: 1px solid rgba(13, 148, 136, 0.22);
        box-shadow: 0 10px 32px rgba(13, 148, 136, 0.1);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .qs-hero,
    [data-bs-theme="dark"] .qs-hero {
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.22) 0%, rgba(0, 0, 0, 0.14) 100%);
        box-shadow: 0 10px 32px rgba(0, 0, 0, 0.28);
    }

    .qs-hero__media {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        flex-shrink: 0;
        overflow: hidden;
        border: 2px solid rgba(13, 148, 136, 0.2);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .qs-hero__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .qs-hero__icon {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: var(--qs-accent);
        background: rgba(13, 148, 136, 0.14);
        flex-shrink: 0;
    }

    .qs-hero__content { flex: 1; min-width: 240px; }
    .qs-hero__title { font-size: 1.3rem; font-weight: 800; margin-bottom: 0.35rem; line-height: 1.35; }
    .qs-hero__chips { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.65rem; }

    .qs-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: var(--qs-surface);
        border: 1px solid var(--qs-border);
        color: var(--qs-muted);
    }

    .qs-chip--success { background: rgba(5, 150, 105, 0.12); border-color: rgba(5, 150, 105, 0.3); color: #059669; }
    .qs-chip--primary { background: rgba(37, 99, 235, 0.1); border-color: rgba(37, 99, 235, 0.25); color: #2563eb; }
    .qs-chip--warning { background: rgba(217, 119, 6, 0.12); border-color: rgba(217, 119, 6, 0.3); color: #b45309; }
    .qs-chip--muted { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .qs-hero__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.85rem;
    }

    .qs-hero-stat {
        min-width: 88px;
        padding: 0.45rem 0.75rem;
        border-radius: 12px;
        background: var(--qs-surface);
        border: 1px solid var(--qs-border);
        text-align: center;
    }

    .qs-hero-stat__value { display: block; font-size: 1.05rem; font-weight: 800; color: var(--qs-accent); line-height: 1.2; }
    .qs-hero-stat__label { display: block; font-size: 0.68rem; color: var(--qs-muted); margin-top: 0.1rem; }

    .qs-hero__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-start; }

    .qs-review-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        border-radius: var(--qs-radius);
        background: linear-gradient(90deg, rgba(217, 119, 6, 0.12) 0%, rgba(217, 119, 6, 0.04) 100%);
        border: 1px solid rgba(217, 119, 6, 0.28);
    }

    .qs-review-bar__text { font-size: 0.9rem; font-weight: 600; color: #92400e; }
    [data-theme-mode="dark"] .qs-review-bar__text,
    [data-bs-theme="dark"] .qs-review-bar__text { color: #fbbf24; }

    .qs-card {
        border-radius: var(--qs-radius);
        border: 1px solid var(--qs-border);
        background: var(--qs-surface);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .qs-card,
    [data-bs-theme="dark"] .qs-card { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.24); }

    .qs-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.95rem 1.2rem;
        border-bottom: 1px solid var(--qs-border);
        background: var(--qs-soft);
        font-weight: 700;
        font-size: 0.92rem;
    }

    .qs-card__header-title {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .qs-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 148, 136, 0.14);
        color: var(--qs-accent);
        font-size: 0.95rem;
    }

    .qs-card__body { padding: 1.15rem 1.2rem; }
    .qs-card__body--flush { padding: 0; }

    .qs-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem 1rem;
    }

    @media (max-width: 575.98px) {
        .qs-meta-grid { grid-template-columns: 1fr; }
    }

    .qs-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.7rem 0.85rem;
        border-radius: 12px;
        background: var(--qs-soft);
        border: 1px solid var(--qs-border);
    }

    .qs-meta-item__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--qs-surface);
        color: var(--qs-accent);
        flex-shrink: 0;
    }

    .qs-meta-item__label { font-size: 0.7rem; font-weight: 600; color: var(--qs-muted); margin-bottom: 0.1rem; }
    .qs-meta-item__value { font-size: 0.88rem; font-weight: 700; line-height: 1.35; word-break: break-word; }

    .qs-desc {
        padding: 1rem 1.1rem;
        border-radius: 12px;
        background: var(--qs-soft);
        border: 1px solid var(--qs-border);
        margin-bottom: 1rem;
    }

    .qs-desc__title { font-size: 0.78rem; font-weight: 700; color: var(--qs-muted); margin-bottom: 0.35rem; }

    .qs-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .qs-stat-tile {
        padding: 0.85rem 0.75rem;
        border-radius: 14px;
        text-align: center;
        border: 1px solid transparent;
    }

    .qs-stat-tile--primary { background: rgba(37, 99, 235, 0.1); border-color: rgba(37, 99, 235, 0.15); }
    .qs-stat-tile--success { background: rgba(5, 150, 105, 0.1); border-color: rgba(5, 150, 105, 0.15); }
    .qs-stat-tile--danger { background: rgba(220, 38, 38, 0.08); border-color: rgba(220, 38, 38, 0.12); }
    .qs-stat-tile--info { background: rgba(13, 148, 136, 0.1); border-color: rgba(13, 148, 136, 0.15); }

    .qs-stat-tile__value { font-size: 1.35rem; font-weight: 800; line-height: 1.1; margin-bottom: 0.15rem; }
    .qs-stat-tile--primary .qs-stat-tile__value { color: #2563eb; }
    .qs-stat-tile--success .qs-stat-tile__value { color: #059669; }
    .qs-stat-tile--danger .qs-stat-tile__value { color: #dc2626; }
    .qs-stat-tile--info .qs-stat-tile__value { color: #0d9488; }
    .qs-stat-tile__label { font-size: 0.72rem; color: var(--qs-muted); }

    .qs-setting-list { display: flex; flex-direction: column; gap: 0.55rem; margin: 0; padding: 0; list-style: none; }
    .qs-setting-list li {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.86rem;
        padding: 0.45rem 0.55rem;
        border-radius: 10px;
        background: var(--qs-soft);
    }

    .qs-action-stack { display: flex; flex-direction: column; gap: 0.5rem; }
    .qs-action-stack .btn { border-radius: 11px; font-weight: 600; }

    .qs-question-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1.2rem;
        border-bottom: 1px solid var(--qs-border);
        transition: background 0.15s ease;
    }

    .qs-question-item:last-child { border-bottom: none; }
    .qs-question-item:hover { background: rgba(13, 148, 136, 0.04); }

    .qs-question-item__index {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(13, 148, 136, 0.12);
        color: var(--qs-accent);
        flex-shrink: 0;
    }

    .qs-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--qs-border);
        overflow: hidden;
    }

    .qs-table { margin-bottom: 0; font-size: 0.85rem; }
    .qs-table thead th {
        background: var(--qs-soft);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--qs-border);
        white-space: nowrap;
    }
    .qs-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; border-color: var(--qs-border); }
    .qs-table tbody tr:hover { background: rgba(13, 148, 136, 0.04); }

    .qs-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--qs-muted);
    }

    .qs-empty i {
        font-size: 2.25rem;
        color: var(--qs-accent);
        opacity: 0.55;
        display: block;
        margin-bottom: 0.65rem;
    }

    .qs-sidebar-sticky {
        position: sticky;
        top: 5.5rem;
    }

    @media (max-width: 991.98px) {
        .qs-sidebar-sticky { position: static; }
        .qs-hero__title { font-size: 1.1rem; }
    }
</style>
