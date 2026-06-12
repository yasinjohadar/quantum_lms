@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .teachers-progress-page {
        --tp-radius: 14px;
        --tp-accent: #059669;
        --tp-accent-2: #2563eb;
        --tp-warning: #d97706;
        --tp-surface: var(--custom-card-bg, #fff);
        --tp-border: var(--default-border, #e9ecef);
        --tp-muted: var(--text-muted, #6c757d);
        --tp-soft: rgba(5, 150, 105, 0.07);
    }

    [data-theme-mode="dark"] .teachers-progress-page,
    [data-bs-theme="dark"] .teachers-progress-page {
        --tp-surface: var(--custom-card-bg, #111a2e);
        --tp-border: rgba(255, 255, 255, 0.1);
        --tp-soft: rgba(5, 150, 105, 0.14);
    }

    .tp-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--tp-radius);
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.18) 0%, rgba(5, 150, 105, 0.04) 100%);
        border: 1px solid rgba(5, 150, 105, 0.24);
        box-shadow: 0 8px 28px rgba(5, 150, 105, 0.1);
    }

    [data-theme-mode="dark"] .tp-hero,
    [data-bs-theme="dark"] .tp-hero {
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.22) 0%, rgba(0, 0, 0, 0.14) 100%);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
    }

    .tp-hero__icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: var(--tp-accent);
        background: rgba(5, 150, 105, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .tp-hero__icon,
    [data-bs-theme="dark"] .tp-hero__icon { color: #6ee7b7; }

    .tp-hero__content { flex: 1; min-width: 220px; }
    .tp-hero__title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.2rem; }
    .tp-hero__subtitle { color: var(--tp-muted); font-size: 0.875rem; margin-bottom: 0; }
    .tp-hero__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .tp-hero__actions .btn { border-radius: 10px; font-weight: 600; font-size: 0.8rem; }

    .tp-card {
        border-radius: var(--tp-radius);
        border: 1px solid var(--tp-border);
        background: var(--tp-surface);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    [data-theme-mode="dark"] .tp-card,
    [data-bs-theme="dark"] .tp-card { box-shadow: 0 4px 18px rgba(0, 0, 0, 0.24); }

    .tp-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid var(--tp-border);
        background: var(--tp-soft);
        font-weight: 700;
        font-size: 0.92rem;
    }

    .tp-card__header-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(5, 150, 105, 0.14);
        color: var(--tp-accent);
        margin-inline-end: 0.55rem;
        font-size: 1rem;
    }

    .tp-card__body { padding: 1.15rem 1.25rem; }

    .tp-week-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1.1rem;
        border-radius: 12px;
        border: 1px dashed rgba(5, 150, 105, 0.35);
        background: var(--tp-soft);
    }

    .tp-week-filter .form-select { border-radius: 10px; min-width: 220px; font-size: 0.85rem; }

    .tp-metric {
        height: 100%;
        padding: 1.1rem 1.15rem;
        border-radius: 12px;
        border: 1px solid var(--tp-border);
        background: var(--tp-surface);
        position: relative;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .tp-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(5, 150, 105, 0.1);
    }

    .tp-metric::before {
        content: '';
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 4px 0 0 4px;
    }

    .tp-metric--primary::before { background: var(--tp-accent); }
    .tp-metric--info::before { background: var(--tp-accent-2); }
    .tp-metric--warning::before { background: var(--tp-warning); }
    .tp-metric--dark::before { background: #334155; }

    .tp-metric__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.65rem;
    }

    .tp-metric__icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .tp-metric--primary .tp-metric__icon { background: rgba(5, 150, 105, 0.12); color: var(--tp-accent); }
    .tp-metric--info .tp-metric__icon { background: rgba(37, 99, 235, 0.1); color: var(--tp-accent-2); }
    .tp-metric--warning .tp-metric__icon { background: rgba(217, 119, 6, 0.12); color: var(--tp-warning); }
    .tp-metric--dark .tp-metric__icon { background: rgba(51, 65, 85, 0.12); color: #334155; }

    .tp-metric__title { font-size: 0.78rem; font-weight: 700; color: var(--tp-muted); margin-bottom: 0.15rem; }
    .tp-metric__hint { font-size: 0.7rem; color: var(--tp-muted); line-height: 1.4; margin-bottom: 0.5rem; }
    .tp-metric__value { font-size: 1.55rem; font-weight: 800; line-height: 1.1; }
    .tp-metric--primary .tp-metric__value { color: var(--tp-accent); }
    .tp-metric--info .tp-metric__value { color: var(--tp-accent-2); }
    .tp-metric--warning .tp-metric__value { color: var(--tp-warning); }

    .tp-progress {
        height: 8px;
        border-radius: 999px;
        background: rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-top: 0.5rem;
    }

    [data-theme-mode="dark"] .tp-progress,
    [data-bs-theme="dark"] .tp-progress { background: rgba(255, 255, 255, 0.08); }

    .tp-progress__bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .tp-progress__bar--success { background: linear-gradient(90deg, #059669, #34d399); }
    .tp-progress__bar--info { background: linear-gradient(90deg, #2563eb, #60a5fa); }
    .tp-progress__bar--warning { background: linear-gradient(90deg, #d97706, #fbbf24); }
    .tp-progress__bar--dark { background: linear-gradient(90deg, #334155, #64748b); }

    .tp-pct {
        display: inline-flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
    }

    .tp-pct--success { background: rgba(5, 150, 105, 0.14); color: #059669; }
    .tp-pct--info { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
    .tp-pct--warning { background: rgba(217, 119, 6, 0.14); color: #b45309; }
    .tp-pct--muted { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .tp-teacher-card {
        border-radius: var(--tp-radius);
        border: 1px solid var(--tp-border);
        background: var(--tp-surface);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .tp-teacher-card:hover {
        border-color: rgba(5, 150, 105, 0.35);
        box-shadow: 0 10px 28px rgba(5, 150, 105, 0.1);
    }

    .tp-teacher-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        background: linear-gradient(90deg, var(--tp-soft) 0%, transparent 100%);
        border-bottom: 1px solid var(--tp-border);
    }

    .tp-teacher-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        color: var(--tp-accent);
        background: rgba(5, 150, 105, 0.14);
        flex-shrink: 0;
    }

    .tp-teacher-name {
        font-weight: 700;
        font-size: 1rem;
        color: inherit;
        text-decoration: none;
    }

    .tp-teacher-name:hover { color: var(--tp-accent); }

    .tp-weekly-panel {
        padding: 1rem 1.1rem;
        border-radius: 12px;
        border: 1px solid var(--tp-border);
        background: linear-gradient(180deg, var(--tp-soft) 0%, var(--tp-surface) 100%);
        height: 100%;
    }

    .tp-weekly-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.45rem 0;
        border-bottom: 1px dashed var(--tp-border);
        font-size: 0.85rem;
    }

    .tp-weekly-row:last-child { border-bottom: none; }

    .tp-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--tp-border);
        overflow: hidden;
    }

    .tp-table {
        margin-bottom: 0;
        font-size: 0.85rem;
    }

    .tp-table thead th {
        background: var(--tp-soft);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--tp-border);
        white-space: nowrap;
    }

    .tp-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-color: var(--tp-border);
    }

    .tp-table tbody tr:hover { background: rgba(5, 150, 105, 0.04); }

    .tp-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        background: rgba(5, 150, 105, 0.1);
        color: var(--tp-accent);
    }

    .tp-chip--class { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .tp-empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--tp-muted);
    }

    .tp-empty i { font-size: 2.2rem; color: var(--tp-accent); opacity: 0.6; display: block; margin-bottom: 0.75rem; }

    .teacher-weeks-table-wrap {
        padding: 0.35rem;
        background: var(--tp-soft);
        border-radius: 12px;
    }

    .teacher-weeks-table-wrap table.teacher-weeks-targets-table {
        border-collapse: separate;
        border-spacing: 0 0.55rem;
        width: 100%;
        margin-bottom: 0;
    }

    .teacher-weeks-table-wrap table.teacher-weeks-targets-table thead th {
        border: none;
        background: transparent;
        font-size: 0.75rem;
        font-weight: 700;
        padding-bottom: 0.4rem;
    }

    .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody td {
        border-block: 1px solid var(--tp-border);
        background: var(--tp-surface);
        padding: 0.7rem 0.85rem !important;
        vertical-align: middle;
    }

    .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody tr td:first-child {
        border-inline-start: 1px solid var(--tp-border);
        border-start-start-radius: 10px;
        border-end-start-radius: 10px;
    }

    .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody tr td:last-child {
        border-inline-end: 1px solid var(--tp-border);
        border-start-end-radius: 10px;
        border-end-end-radius: 10px;
    }

    @media (max-width: 767.98px) {
        .tp-hero { padding: 1rem; }
        .tp-card__body { padding: 1rem; }
    }
</style>
