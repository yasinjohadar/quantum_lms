@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .lessons-index-page {
        --li-radius: 14px;
        --li-accent: #7c3aed;
        --li-accent-rgb: 124, 58, 237;
        --li-surface: var(--custom-card-bg, #fff);
        --li-border: var(--default-border, #e9ecef);
        --li-muted: var(--text-muted, #6c757d);
        --li-soft: rgba(124, 58, 237, 0.06);
    }

    [data-theme-mode="dark"] .lessons-index-page,
    [data-bs-theme="dark"] .lessons-index-page {
        --li-surface: var(--custom-card-bg, #111a2e);
        --li-border: rgba(255, 255, 255, 0.1);
        --li-soft: rgba(124, 58, 237, 0.12);
    }

    .lessons-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--li-radius);
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(124, 58, 237, 0.22);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.08);
        margin-bottom: 1.25rem;
    }

    .lessons-index-hero__icon {
        width: 3rem;
        height: 3rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(124, 58, 237, 0.15);
        color: var(--li-accent);
        font-size: 1.35rem;
    }

    .lessons-index-hero__content { flex: 1; min-width: 200px; }
    .lessons-index-hero__title { font-size: 1.25rem; font-weight: 700; margin: 0; }
    .lessons-index-hero__subtitle { margin: 0.25rem 0 0; color: var(--li-muted); font-size: 0.875rem; }

    .lessons-index-stat-mini {
        text-align: center;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        background: var(--li-surface);
        border: 1px solid var(--li-border);
    }

    .lessons-index-stat-mini__value { display: block; font-size: 1.35rem; font-weight: 700; color: var(--li-accent); }
    .lessons-index-stat-mini__label { font-size: 0.75rem; color: var(--li-muted); }

    .lessons-index-card {
        background: var(--li-surface);
        border: 1px solid var(--li-border);
        border-radius: var(--li-radius);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .lessons-index-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--li-border);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .lessons-index-card__header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 8px;
        background: var(--li-soft);
        color: var(--li-accent);
        margin-inline-end: 0.5rem;
    }

    .lessons-index-card__body { padding: 1.25rem; }
    .lessons-index-card--flush .lessons-index-card__body { padding: 0; }

    .lessons-index-table-wrap { overflow-x: auto; }
    .lessons-index-table {
        font-size: 0.875rem;
        margin: 0;
        table-layout: fixed;
        width: 100%;
    }
    .lessons-index-table thead th {
        background: var(--li-soft);
        border-bottom: 1px solid var(--li-border);
        white-space: nowrap;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .li-lesson-title { font-weight: 600; color: inherit; text-decoration: none; }
    .li-lesson-title:hover { color: var(--li-accent); }

    .lessons-col-title {
        width: 13rem;
        max-width: 13rem;
        min-width: 0;
    }

    .li-lesson-cell {
        min-width: 0;
        max-width: 13rem;
    }

    .li-lesson-title-row {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        min-width: 0;
        margin-bottom: 0.15rem;
    }

    .li-lesson-title-row .li-lesson-title,
    .li-lesson-title-row .fw-semibold {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .lessons-col-subject {
        width: 8.5rem;
        max-width: 8.5rem;
    }

    .lessons-col-section {
        width: 9rem;
        max-width: 9rem;
    }

    .lessons-col-section > div {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lessons-col-video {
        width: 4.5rem;
        max-width: 4.5rem;
    }

    .lessons-col-review {
        width: 5.5rem;
        max-width: 5.5rem;
    }

    .lessons-col-actions {
        width: 8rem;
        max-width: 8rem;
    }

    .li-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .li-badge--mirror { background: rgba(13, 202, 240, 0.15); color: #0aa2c0; }
    .li-badge--sync { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .li-badge--legacy { background: rgba(255, 193, 7, 0.15); color: #997404; }
    .li-badge--review-approved { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .li-badge--review-pending { background: rgba(255, 193, 7, 0.15); color: #997404; }
    .li-badge--review-rejected { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .li-badge--review-draft { background: rgba(108, 117, 125, 0.12); color: #6c757d; }

    .li-links-list {
        margin: 0.35rem 0 0;
        padding: 0;
        list-style: none;
        font-size: 0.72rem;
        color: var(--li-muted);
        max-width: 280px;
    }

    .li-links-list li { margin-bottom: 0.15rem; }

    .li-links-cell {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        max-width: 11rem;
    }

    .li-link-stack {
        display: flex;
        flex-direction: column;
        gap: 0.05rem;
        line-height: 1.25;
    }

    .li-link-stack--item {
        padding-top: 0.2rem;
        margin-top: 0.15rem;
        border-top: 1px dashed var(--li-border);
        width: 100%;
    }

    .li-link-stack__line {
        display: block;
        font-size: 0.68rem;
        color: var(--li-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 11rem;
    }

    .li-link-stack__more {
        font-size: 0.68rem;
        color: var(--li-muted);
    }

    .lessons-index-table:not(.lessons-index-table--show-links) .lessons-col-links {
        display: none;
    }

    .lessons-index-table .lessons-col-links {
        max-width: 11rem;
        width: 11rem;
    }

    #toggleLinksColumnBtn[aria-pressed="true"] {
        color: var(--li-accent);
        border-color: rgba(124, 58, 237, 0.45);
        background: var(--li-soft);
    }

    .lessons-index-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--li-border);
    }

    .lessons-index-loading {
        text-align: center;
        padding: 2rem 1rem;
    }

    #lessonsTableContainer.is-loading {
        opacity: 0.45;
        pointer-events: none;
    }

    @media (max-width: 991.98px) {
        .lessons-index-table .lessons-col-links { max-width: 9rem; width: 9rem; }
        .li-link-stack__line { max-width: 9rem; }
        .li-links-cell { max-width: 9rem; }
    }
</style>
