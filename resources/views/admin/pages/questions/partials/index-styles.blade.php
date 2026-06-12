@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .qb-page {
        --qb-radius: 14px;
        --qb-accent: #6366f1;
        --qb-surface: var(--custom-card-bg, #fff);
        --qb-border: var(--default-border, #e9ecef);
        --qb-muted: var(--text-muted, #6c757d);
        --qb-soft: rgba(99, 102, 241, 0.06);
    }

    [data-theme-mode="dark"] .qb-page,
    [data-bs-theme="dark"] .qb-page {
        --qb-surface: var(--custom-card-bg, #111a2e);
        --qb-border: rgba(255, 255, 255, 0.1);
        --qb-soft: rgba(99, 102, 241, 0.14);
    }

    .qb-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--qb-radius);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.16) 0%, rgba(99, 102, 241, 0.04) 100%);
        border: 1px solid rgba(99, 102, 241, 0.22);
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .qb-hero,
    [data-bs-theme="dark"] .qb-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .qb-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--qb-accent);
        background: rgba(99, 102, 241, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .qb-hero__icon,
    [data-bs-theme="dark"] .qb-hero__icon { color: #a5b4fc; }

    .qb-hero__content { flex: 1; min-width: 200px; }
    .qb-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .qb-hero__subtitle { color: var(--qb-muted); font-size: 0.875rem; margin-bottom: 0; }

    .qb-hero__stat {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--qb-surface);
        border: 1px solid var(--qb-border);
        min-width: 110px;
    }

    .qb-hero__stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--qb-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .qb-hero__stat-value,
    [data-bs-theme="dark"] .qb-hero__stat-value { color: #a5b4fc; }

    .qb-hero__stat-label { font-size: 0.72rem; color: var(--qb-muted); }

    .qb-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .qb-hero__actions .btn { border-radius: 10px; font-weight: 600; font-size: 0.84rem; }

    .qb-card-panel {
        border-radius: var(--qb-radius);
        border: 1px solid var(--qb-border);
        background: var(--qb-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .qb-card-panel,
    [data-bs-theme="dark"] .qb-card-panel {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .qb-card-panel__header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--qb-border);
        background: var(--qb-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .qb-card-panel__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(99, 102, 241, 0.12);
        color: var(--qb-accent);
    }

    .qb-card-panel__body { padding: 1.25rem; }

    .qb-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--qb-muted);
        margin-bottom: 0.3rem;
    }

    .qb-filters .form-control,
    .qb-filters .form-select {
        border-radius: 10px;
        border-color: var(--qb-border);
        font-size: 0.875rem;
    }

    .qb-filters .form-control:focus,
    .qb-filters .form-select:focus {
        border-color: rgba(99, 102, 241, 0.5);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.1);
    }

    .qb-filters .input-group-text {
        border-radius: 10px 0 0 10px;
        border-color: var(--qb-border);
        background: var(--qb-soft);
    }

    .qb-filters .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }

    .qb-quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 1rem;
    }

    .qb-quick-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--qb-border);
        background: var(--qb-surface);
        color: var(--default-text-color, inherit);
        transition: all 0.15s ease;
    }

    .qb-quick-link:hover {
        border-color: rgba(99, 102, 241, 0.35);
        background: var(--qb-soft);
        color: var(--qb-accent);
    }

    .qb-quick-link--active {
        background: rgba(99, 102, 241, 0.12);
        border-color: rgba(99, 102, 241, 0.35);
        color: var(--qb-accent);
    }

    .qb-quick-link--warning.qb-quick-link--active { background: rgba(255, 193, 7, 0.15); border-color: rgba(255, 193, 7, 0.4); color: #b45309; }
    .qb-quick-link--danger.qb-quick-link--active { background: rgba(220, 53, 69, 0.1); border-color: rgba(220, 53, 69, 0.35); color: #dc3545; }

    .qb-bulk-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border: 1px solid rgba(99, 102, 241, 0.25);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, var(--qb-surface) 100%);
        margin-bottom: 1rem;
    }

    .qb-bulk-bar__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        margin-inline-start: auto;
    }

    .qb-bulk-bar .btn { border-radius: 8px; font-size: 0.8rem; font-weight: 600; }

    .qb-bulk-selected-count {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--qb-accent);
    }

    #questionBankResults {
        transition: opacity 0.2s ease;
    }

    #questionBankResults.opacity-50 {
        pointer-events: none;
    }

    .qb-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    @media (max-width: 1199.98px) {
        .qb-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 991.98px) {
        .qb-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .question-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        border: 1px solid var(--qb-border) !important;
        border-radius: 12px !important;
        border-inline-end: 4px solid transparent !important;
        color: var(--default-text-color);
        overflow: hidden;
        height: 100%;
    }

    .question-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(99, 102, 241, 0.12) !important;
    }

    .question-card.question-card-selected {
        box-shadow: 0 0 0 2px var(--qb-accent) !important;
        background-color: rgba(99, 102, 241, 0.06) !important;
    }

    [data-theme-mode="dark"] .question-card.question-card-selected,
    [data-bs-theme="dark"] .question-card.question-card-selected {
        background-color: rgba(99, 102, 241, 0.14) !important;
    }

    .question-bulk-checkbox { cursor: pointer; width: 1.05rem; height: 1.05rem; }
    .question-bulk-checkbox:disabled { cursor: not-allowed; opacity: 0.45; }

    .qb-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .qb-card-top__left {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
    }

    .qb-type-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        white-space: nowrap;
    }

    .qb-card-menu .btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--qb-border);
        background: var(--qb-soft);
    }

    .question-card h6,
    .question-card .question-text-body,
    .question-card a.question-text-body {
        color: var(--default-text-color) !important;
    }

    .question-card a.question-text-body:hover {
        color: var(--qb-accent) !important;
    }

    .question-card .text-muted { color: var(--qb-muted) !important; }

    .question-card .question-points-badge {
        background: var(--qb-soft) !important;
        color: var(--qb-accent) !important;
        border: 1px solid rgba(99, 102, 241, 0.2);
        font-weight: 600;
        font-size: 0.72rem;
    }

    .qb-meta-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
    }

    .qb-meta-badges .badge { font-size: 0.68rem; font-weight: 600; }

    .question-type-single_choice { border-inline-end-color: var(--primary-color) !important; }
    .question-type-multiple_choice { border-inline-end-color: #17a2b8 !important; }
    .question-type-true_false { border-inline-end-color: #28a745 !important; }
    .question-type-short_answer { border-inline-end-color: #ffc107 !important; }
    .question-type-essay { border-inline-end-color: #6c757d !important; }
    .question-type-matching { border-inline-end-color: #dc3545 !important; }
    .question-type-ordering { border-inline-end-color: #343a40 !important; }
    .question-type-fill_blanks { border-inline-end-color: #007bff !important; }
    .question-type-numerical { border-inline-end-color: #17a2b8 !important; }
    .question-type-drag_drop { border-inline-end-color: #fd7e14 !important; }

    .question-card-preview {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .question-card .question-text-body {
        font-size: 0.92rem;
        line-height: 1.65;
        font-weight: 600;
    }

    .question-card .question-text-body .katex {
        font-size: 1em;
        padding: 0.06em 0.3em;
        margin: 0 0.08em;
    }

    [data-theme-mode="dark"] .question-card .question-text-body .question-inline-code,
    [data-theme-mode="dark"] .question-card .question-text-body .katex {
        color: #90caf9;
        background: rgba(144, 202, 249, 0.12);
        border-color: rgba(144, 202, 249, 0.28);
    }

    .qb-curriculum {
        border-top: 1px solid var(--qb-border);
        padding-top: 0.75rem;
        margin-top: auto;
    }

    .qb-curriculum--global {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: var(--qb-muted);
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
        background: var(--qb-soft);
    }

    .qb-curriculum-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
        margin-bottom: 0.35rem;
        font-size: 0.72rem;
    }

    .qb-curriculum-pill {
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
        background: var(--qb-soft);
        border: 1px solid var(--qb-border);
        color: var(--default-text-color, inherit);
    }

    .qb-curriculum-pill--unit {
        color: var(--qb-accent);
        font-weight: 600;
        border-color: rgba(99, 102, 241, 0.25);
    }

    .qb-curriculum-more {
        font-size: 0.68rem;
        color: var(--qb-muted);
    }

    .qb-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem 1.5rem;
        border-radius: var(--qb-radius);
        border: 1px dashed var(--qb-border);
        background: var(--qb-soft);
    }

    .qb-empty__icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.6rem;
        color: var(--qb-accent);
        background: var(--qb-surface);
        border: 1px solid var(--qb-border);
    }

    .qb-pagination {
        display: flex;
        justify-content: center;
        padding-top: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .qb-hero__actions { width: 100%; }
        .qb-hero__actions .btn { flex: 1; }
        .qb-grid { grid-template-columns: 1fr !important; }
        .qb-bulk-bar__actions { width: 100%; margin-inline-start: 0; }
    }
</style>
