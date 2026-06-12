@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .settings-page {
        --st-radius: 14px;
        --st-accent: #0d9488;
        --st-accent-soft: rgba(13, 148, 136, 0.12);
        --st-surface: var(--custom-card-bg, #fff);
        --st-border: var(--default-border, #e9ecef);
        --st-muted: var(--text-muted, #6c757d);
        --st-soft: rgba(13, 148, 136, 0.06);
    }

    [data-theme-mode="dark"] .settings-page,
    [data-bs-theme="dark"] .settings-page {
        --st-surface: var(--custom-card-bg, #111a2e);
        --st-border: rgba(255, 255, 255, 0.1);
        --st-soft: rgba(13, 148, 136, 0.14);
        --st-accent-soft: rgba(45, 212, 191, 0.16);
    }

    .settings-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--st-radius);
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.18) 0%, rgba(13, 148, 136, 0.04) 100%);
        border: 1px solid rgba(13, 148, 136, 0.24);
        box-shadow: 0 8px 28px rgba(13, 148, 136, 0.1);
    }

    [data-theme-mode="dark"] .settings-hero,
    [data-bs-theme="dark"] .settings-hero {
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.22) 0%, rgba(0, 0, 0, 0.14) 100%);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
    }

    .settings-hero__icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: var(--st-accent);
        background: var(--st-accent-soft);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .settings-hero__icon,
    [data-bs-theme="dark"] .settings-hero__icon { color: #5eead4; }

    .settings-hero__content { flex: 1; min-width: 220px; }
    .settings-hero__title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.2rem; }
    .settings-hero__subtitle { color: var(--st-muted); font-size: 0.875rem; margin-bottom: 0; }

    .settings-hero__stat {
        text-align: center;
        padding: 0.75rem 1.1rem;
        border-radius: 12px;
        background: var(--st-surface);
        border: 1px solid var(--st-border);
        min-width: 108px;
    }

    .settings-hero__stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--st-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .settings-hero__stat-value,
    [data-bs-theme="dark"] .settings-hero__stat-value { color: #5eead4; }

    .settings-hero__stat-label { font-size: 0.72rem; color: var(--st-muted); }

    .settings-groups {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.25rem 0 0.15rem;
    }

    .settings-groups__pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--st-muted);
        background: var(--st-surface);
        border: 1px solid var(--st-border);
        transition: all 0.18s ease;
        white-space: nowrap;
    }

    .settings-groups__pill:hover {
        color: var(--st-accent);
        border-color: rgba(13, 148, 136, 0.35);
        background: var(--st-soft);
    }

    .settings-groups__pill.is-active {
        color: #fff;
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);
    }

    [data-theme-mode="dark"] .settings-groups__pill.is-active,
    [data-bs-theme="dark"] .settings-groups__pill.is-active {
        color: #042f2e;
    }

    .settings-card {
        border-radius: var(--st-radius);
        border: 1px solid var(--st-border);
        background: var(--st-surface);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    [data-theme-mode="dark"] .settings-card,
    [data-bs-theme="dark"] .settings-card {
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.24);
    }

    .settings-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--st-border);
        background: var(--st-soft);
    }

    .settings-card__header-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 0.95rem;
        margin: 0;
    }

    .settings-card__header-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--st-accent-soft);
        color: var(--st-accent);
        font-size: 1rem;
    }

    [data-theme-mode="dark"] .settings-card__header-icon,
    [data-bs-theme="dark"] .settings-card__header-icon { color: #5eead4; }

    .settings-card__body { padding: 1.25rem; }

    .settings-field {
        height: 100%;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        border: 1px solid var(--st-border);
        background: var(--st-surface);
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .settings-field:hover {
        border-color: rgba(13, 148, 136, 0.28);
        box-shadow: 0 6px 18px rgba(13, 148, 136, 0.07);
    }

    [data-theme-mode="dark"] .settings-field:hover,
    [data-bs-theme="dark"] .settings-field:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.22);
    }

    .settings-field.is-featured {
        border-color: rgba(13, 148, 136, 0.35);
        background: linear-gradient(180deg, var(--st-soft) 0%, var(--st-surface) 100%);
    }

    .settings-field__head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .settings-field__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1rem;
        background: var(--st-accent-soft);
        color: var(--st-accent);
    }

    .settings-field__icon--warning { background: rgba(245, 158, 11, 0.14); color: #d97706; }
    .settings-field__icon--primary { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
    .settings-field__icon--success { background: rgba(22, 163, 74, 0.12); color: #16a34a; }
    .settings-field__icon--info { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
    .settings-field__icon--danger { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
    .settings-field__icon--muted { background: rgba(100, 116, 139, 0.12); color: #64748b; }

    .settings-field__title {
        font-size: 0.92rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
        line-height: 1.35;
    }

    .settings-field__hint {
        font-size: 0.78rem;
        color: var(--st-muted);
        margin-bottom: 0;
        line-height: 1.5;
    }

    .settings-field__control .form-control,
    .settings-field__control .form-select {
        border-radius: 10px;
        font-size: 0.875rem;
    }

    .settings-field__control .form-check-input {
        width: 2.5em;
        height: 1.25em;
        cursor: pointer;
    }

    .settings-field__control .form-check-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--st-muted);
    }

    .settings-field__badge {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        background: rgba(13, 148, 136, 0.14);
        color: var(--st-accent);
    }

    .settings-form-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding: 1rem 1.15rem;
        border-radius: 12px;
        border: 1px dashed rgba(13, 148, 136, 0.35);
        background: var(--st-soft);
    }

    .settings-form-footer .btn { border-radius: 10px; font-weight: 600; }

    .settings-empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--st-muted);
    }

    .settings-empty i {
        font-size: 2.2rem;
        color: var(--st-accent);
        opacity: 0.65;
        margin-bottom: 0.75rem;
        display: block;
    }

    @media (max-width: 767.98px) {
        .settings-hero { padding: 1rem; }
        .settings-card__body { padding: 1rem; }
        .settings-form-footer { flex-direction: column; align-items: stretch; }
        .settings-form-footer .btn { width: 100%; }
    }
</style>
