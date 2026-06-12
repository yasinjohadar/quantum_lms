@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .lesson-show-page {
        --ls-radius: 16px;
        --ls-accent: #6366f1;
        --ls-accent-2: #059669;
        --ls-warning: #d97706;
        --ls-surface: var(--custom-card-bg, #fff);
        --ls-border: var(--default-border, #e9ecef);
        --ls-muted: var(--text-muted, #6c757d);
        --ls-soft: rgba(99, 102, 241, 0.07);
    }

    [data-theme-mode="dark"] .lesson-show-page,
    [data-bs-theme="dark"] .lesson-show-page {
        --ls-surface: var(--custom-card-bg, #111a2e);
        --ls-border: rgba(255, 255, 255, 0.1);
        --ls-soft: rgba(99, 102, 241, 0.14);
    }

    .ls-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1rem 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: var(--ls-radius);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.16) 0%, rgba(5, 150, 105, 0.05) 100%);
        border: 1px solid rgba(99, 102, 241, 0.22);
        box-shadow: 0 10px 32px rgba(99, 102, 241, 0.1);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .ls-hero,
    [data-bs-theme="dark"] .ls-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.22) 0%, rgba(0, 0, 0, 0.14) 100%);
        box-shadow: 0 10px 32px rgba(0, 0, 0, 0.28);
    }

    .ls-hero__icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--ls-accent-2);
        background: rgba(5, 150, 105, 0.14);
        flex-shrink: 0;
    }

    .ls-hero__content { flex: 1; min-width: 240px; }
    .ls-hero__title { font-size: 1.35rem; font-weight: 800; margin-bottom: 0.35rem; line-height: 1.35; }
    .ls-hero__chips { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.65rem; }
    .ls-hero__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-start; }

    .ls-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: var(--ls-surface);
        border: 1px solid var(--ls-border);
        color: var(--ls-muted);
    }

    .ls-chip--status-pending { background: rgba(217, 119, 6, 0.12); border-color: rgba(217, 119, 6, 0.3); color: #b45309; }
    .ls-chip--status-approved { background: rgba(5, 150, 105, 0.12); border-color: rgba(5, 150, 105, 0.3); color: #059669; }
    .ls-chip--status-rejected { background: rgba(220, 38, 38, 0.1); border-color: rgba(220, 38, 38, 0.25); color: #dc2626; }
    .ls-chip--status-draft { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .ls-review-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        border-radius: var(--ls-radius);
        background: linear-gradient(90deg, rgba(217, 119, 6, 0.12) 0%, rgba(217, 119, 6, 0.04) 100%);
        border: 1px solid rgba(217, 119, 6, 0.28);
    }

    .ls-review-bar__text { font-size: 0.9rem; font-weight: 600; color: #92400e; }
    [data-theme-mode="dark"] .ls-review-bar__text,
    [data-bs-theme="dark"] .ls-review-bar__text { color: #fbbf24; }

    .ls-review-bar__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }

    .ls-card {
        border-radius: var(--ls-radius);
        border: 1px solid var(--ls-border);
        background: var(--ls-surface);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        height: 100%;
    }

    [data-theme-mode="dark"] .ls-card,
    [data-bs-theme="dark"] .ls-card { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.24); }

    .ls-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.95rem 1.2rem;
        border-bottom: 1px solid var(--ls-border);
        background: var(--ls-soft);
        font-weight: 700;
        font-size: 0.92rem;
    }

    .ls-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(99, 102, 241, 0.14);
        color: var(--ls-accent);
        margin-inline-end: 0.5rem;
        font-size: 0.95rem;
    }

    .ls-card__body { padding: 1.15rem 1.2rem; }

    .ls-player {
        border-radius: 14px;
        overflow: hidden;
        background: #0f172a;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.25);
        margin-bottom: 1rem;
    }

    .ls-player .ratio { --bs-aspect-ratio: 56.25%; }

    .ls-empty-video {
        text-align: center;
        padding: 3.5rem 1.5rem;
        border-radius: 14px;
        background: var(--ls-soft);
        border: 2px dashed rgba(99, 102, 241, 0.25);
        color: var(--ls-muted);
    }

    .ls-empty-video i { font-size: 2.5rem; color: var(--ls-accent); opacity: 0.65; display: block; margin-bottom: 0.75rem; }

    .ls-desc {
        padding: 1rem 1.1rem;
        border-radius: 12px;
        background: var(--ls-soft);
        border: 1px solid var(--ls-border);
    }

    .ls-desc__title { font-size: 0.8rem; font-weight: 700; color: var(--ls-muted); margin-bottom: 0.4rem; }

    .ls-meta-list { display: flex; flex-direction: column; gap: 0.15rem; }

    .ls-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px dashed var(--ls-border);
    }

    .ls-meta-item:last-child { border-bottom: none; }

    .ls-meta-item__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--ls-soft);
        color: var(--ls-accent);
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .ls-meta-item__label { font-size: 0.72rem; font-weight: 600; color: var(--ls-muted); margin-bottom: 0.1rem; }
    .ls-meta-item__value { font-size: 0.88rem; font-weight: 600; line-height: 1.4; word-break: break-word; }
    .ls-meta-item__value a { font-weight: 500; }

    .ls-review-status {
        text-align: center;
        padding: 1rem 0 0.5rem;
    }

    .ls-review-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.45rem 1rem;
        border-radius: 999px;
        margin-bottom: 1rem;
    }

    .ls-review-badge--warning { background: rgba(217, 119, 6, 0.14); color: #b45309; }
    .ls-review-badge--success { background: rgba(5, 150, 105, 0.14); color: #059669; }
    .ls-review-badge--danger { background: rgba(220, 38, 38, 0.12); color: #dc2626; }
    .ls-review-badge--secondary { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .ls-timeline { display: flex; flex-direction: column; gap: 0.65rem; margin-top: 0.5rem; }

    .ls-timeline__item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.82rem;
    }

    .ls-timeline__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--ls-accent);
        flex-shrink: 0;
    }

    .ls-timeline__label { color: var(--ls-muted); min-width: 120px; }
    .ls-timeline__value { font-weight: 600; }

    .ls-review-actions { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem; }
    .ls-review-actions .btn { border-radius: 11px; font-weight: 700; padding: 0.55rem 1rem; }

    .ls-attachment {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--ls-border);
        margin-bottom: 0.55rem;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .ls-attachment:hover {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.08);
    }

    .ls-attachment__icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .ls-attachment__icon--file { background: rgba(99, 102, 241, 0.12); color: var(--ls-accent); }
    .ls-attachment__icon--link { background: rgba(37, 99, 235, 0.1); color: #2563eb; }

    .ls-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ls-border);
        overflow: hidden;
    }

    .ls-table { margin-bottom: 0; font-size: 0.85rem; }
    .ls-table thead th {
        background: var(--ls-soft);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--ls-border);
        white-space: nowrap;
    }
    .ls-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; border-color: var(--ls-border); }
    .ls-table tbody tr:hover { background: rgba(99, 102, 241, 0.04); }

    .ls-empty { text-align: center; padding: 2rem 1rem; color: var(--ls-muted); }
    .ls-empty i { font-size: 2rem; color: var(--ls-accent); opacity: 0.55; display: block; margin-bottom: 0.5rem; }

    @media (max-width: 1199.98px) {
        .ls-hero__title { font-size: 1.15rem; }
    }
</style>
