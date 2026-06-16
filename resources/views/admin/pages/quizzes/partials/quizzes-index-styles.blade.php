@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .quizzes-index-page {
        --ui-radius: 14px;
        --ui-accent: #2563eb;
        --ui-accent-rgb: 37, 99, 235;
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(37, 99, 235, 0.06);
    }

    [data-theme-mode="dark"] .quizzes-index-page,
    [data-bs-theme="dark"] .quizzes-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(37, 99, 235, 0.12);
    }

    .quizzes-index-page .container-fluid {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    @media (min-width: 1200px) {
        .quizzes-index-page .container-fluid {
            padding-left: 1.75rem;
            padding-right: 1.75rem;
        }
    }

    .quizzes-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.14) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(37, 99, 235, 0.22);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .quizzes-index-hero,
    [data-bs-theme="dark"] .quizzes-index-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .quizzes-index-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #2563eb;
        background: rgba(37, 99, 235, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .quizzes-index-hero__icon,
    [data-bs-theme="dark"] .quizzes-index-hero__icon { color: #93c5fd; }

    .quizzes-index-hero__content { flex: 1; min-width: 200px; }
    .quizzes-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .quizzes-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .quizzes-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .quizzes-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .quizzes-index-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .quizzes-index-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #2563eb;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .quizzes-index-stat-mini__value,
    [data-bs-theme="dark"] .quizzes-index-stat-mini__value { color: #93c5fd; }

    .quizzes-index-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .quizzes-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .quizzes-index-card,
    [data-bs-theme="dark"] .quizzes-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .quizzes-index-card__header {
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

    .quizzes-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: var(--ui-accent);
    }

    .quizzes-index-card__body { padding: 1.25rem; }

    .quizzes-index-card--flush > .quizzes-index-card__body { padding: 0; }

    .quizzes-index-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.3rem;
    }

    .quizzes-index-filters .form-control,
    .quizzes-index-filters .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .quizzes-index-filters .form-control:focus,
    .quizzes-index-filters .form-select:focus {
        border-color: rgba(37, 99, 235, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.1);
    }

    .quizzes-index-table-wrap { overflow-x: auto; }

    .quizzes-index-table { margin-bottom: 0; min-width: 960px; }

    .quizzes-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .quizzes-index-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
        font-size: 0.875rem;
    }

    .quizzes-index-table tbody tr { transition: background 0.15s ease; }
    .quizzes-index-table tbody tr:hover { background: var(--ui-soft); }
    .quizzes-index-table tbody tr:last-child td { border-bottom: none; }

    .ui-quiz-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .ui-quiz-thumb {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .ui-quiz-thumb--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        font-size: 1.1rem;
    }

    .ui-quiz-title {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .ui-quiz-title:hover { color: #2563eb; }

    .ui-quiz-meta {
        font-size: 0.75rem;
        color: var(--ui-muted);
        display: block;
        margin-top: 0.1rem;
    }

    .ui-quiz-subject-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        white-space: nowrap;
    }

    [data-theme-mode="dark"] .ui-quiz-subject-pill,
    [data-bs-theme="dark"] .ui-quiz-subject-pill { color: #cbd5e1; }

    .ui-quiz-count {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        white-space: nowrap;
    }

    .ui-quiz-count--questions {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .ui-quiz-count--attempts {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    [data-theme-mode="dark"] .ui-quiz-count--questions,
    [data-bs-theme="dark"] .ui-quiz-count--questions { color: #93c5fd; }
    [data-theme-mode="dark"] .ui-quiz-count--attempts,
    [data-bs-theme="dark"] .ui-quiz-count--attempts { color: #7dd3fc; }

    .ui-quiz-duration {
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--ui-muted);
        white-space: nowrap;
    }

    .ui-quiz-status {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
        margin: 0.1rem 0;
    }

    .ui-quiz-status--published {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .ui-quiz-status--draft {
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
    }

    .ui-quiz-status--inactive {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }

    .ui-quiz-review {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .ui-quiz-review--approved { background: rgba(5, 150, 105, 0.12); color: #059669; }
    .ui-quiz-review--pending { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .ui-quiz-review--rejected { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .ui-quiz-review--draft { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    [data-theme-mode="dark"] .ui-quiz-status--published,
    [data-bs-theme="dark"] .ui-quiz-status--published,
    [data-theme-mode="dark"] .ui-quiz-review--approved,
    [data-bs-theme="dark"] .ui-quiz-review--approved { color: #6ee7b7; }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .quizzes-index-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .quizzes-index-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
        color: #2563eb;
    }

    .quizzes-index-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--ui-border);
    }

    .quizzes-index-loading {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    #quizzesTableContainer.is-loading {
        opacity: 0.45;
        pointer-events: none;
    }

    @media (max-width: 1199.98px) {
        .quizzes-col-duration { display: none; }
    }

    @media (max-width: 991.98px) {
        .quizzes-col-attempts { display: none; }
        .quizzes-col-review { display: none; }
    }

    @media (max-width: 767.98px) {
        .quizzes-index-hero__actions { width: 100%; }
        .quizzes-index-hero__actions .btn { flex: 1 1 auto; }
        .quizzes-index-stat-mini { width: 100%; }
        .quizzes-col-subject { display: none; }
    }

    @media (max-width: 575.98px) {
        .quizzes-index-page .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .quizzes-index-card__body { padding: 1rem; }
        .quizzes-index-table thead th,
        .quizzes-index-table tbody td { padding: 0.65rem 0.75rem; }
    }
</style>
