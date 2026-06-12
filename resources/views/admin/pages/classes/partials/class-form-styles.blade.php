<style>
    .class-form-page {
        --cf-radius: 14px;
        --cf-accent: #2563eb;
        --cf-surface: var(--custom-card-bg, #fff);
        --cf-border: var(--default-border, #e9ecef);
        --cf-muted: var(--text-muted, #6c757d);
        --cf-soft: rgba(37, 99, 235, 0.06);
    }

    [data-theme-mode="dark"] .class-form-page,
    [data-bs-theme="dark"] .class-form-page {
        --cf-surface: var(--custom-card-bg, #111a2e);
        --cf-border: rgba(255, 255, 255, 0.1);
        --cf-soft: rgba(37, 99, 235, 0.14);
    }

    .class-form-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--cf-radius);
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.14) 0%, rgba(37, 99, 235, 0.04) 100%);
        border: 1px solid rgba(37, 99, 235, 0.2);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .class-form-hero,
    [data-bs-theme="dark"] .class-form-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .class-form-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--cf-accent);
        background: rgba(37, 99, 235, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .class-form-hero__icon,
    [data-bs-theme="dark"] .class-form-hero__icon { color: #93c5fd; }

    .class-form-hero__content { flex: 1; min-width: 200px; }
    .class-form-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .class-form-hero__subtitle { color: var(--cf-muted); font-size: 0.875rem; margin-bottom: 0; }

    .class-form-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .class-form-card {
        border-radius: var(--cf-radius);
        border: 1px solid var(--cf-border);
        background: var(--cf-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .class-form-card,
    [data-bs-theme="dark"] .class-form-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .class-form-card__header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--cf-border);
        background: var(--cf-soft);
    }

    .class-form-card__header-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: var(--cf-accent);
        flex-shrink: 0;
        font-size: 1rem;
    }

    .class-form-card__header-text { flex: 1; min-width: 0; }
    .class-form-card__title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.15rem; }
    .class-form-card__desc { font-size: 0.78rem; color: var(--cf-muted); margin-bottom: 0; }

    .class-form-card__body { padding: 1.25rem; }

    .class-form-field .form-label {
        font-weight: 600;
        font-size: 0.84rem;
        margin-bottom: 0.35rem;
        color: var(--default-text-color, inherit);
    }

    .class-form-field .form-label .text-danger { font-size: 0.75rem; }

    .class-form-field .form-control,
    .class-form-field .form-select {
        border-radius: 10px;
        border-color: var(--cf-border);
        font-size: 0.9rem;
    }

    .class-form-field .form-control:focus,
    .class-form-field .form-select:focus {
        border-color: rgba(37, 99, 235, 0.5);
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.1);
    }

    .class-form-hint {
        display: flex;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: var(--cf-muted);
        margin-top: 0.4rem;
        line-height: 1.45;
    }

    .class-form-hint i {
        flex-shrink: 0;
        margin-top: 0.1rem;
        opacity: 0.7;
    }

    .class-form-switch-box {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--cf-border);
        background: var(--cf-soft);
        height: 100%;
    }

    .class-form-switch-box .form-check-label {
        font-weight: 600;
        font-size: 0.84rem;
    }

    .class-form-callout {
        padding: 1rem 1.15rem;
        border-radius: 10px;
        border: 1px solid var(--cf-border);
        background: var(--cf-soft);
    }

    .class-form-callout .form-check-label {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .class-form-currency-table {
        margin-bottom: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .class-form-currency-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cf-muted);
        background: var(--cf-soft);
        border-bottom: 1px solid var(--cf-border);
        padding: 0.75rem 1rem;
    }

    .class-form-currency-table tbody td {
        padding: 0.65rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--cf-border);
        font-size: 0.875rem;
    }

    .class-form-currency-table tbody tr:last-child td { border-bottom: none; }

    .class-form-feature-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .class-form-feature-num {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cf-accent);
        background: var(--cf-soft);
        border: 1px solid var(--cf-border);
        flex-shrink: 0;
    }

    .class-form-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 1rem 1.25rem;
        border-radius: var(--cf-radius);
        border: 1px solid var(--cf-border);
        background: var(--cf-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        position: sticky;
        bottom: 1rem;
        z-index: 10;
    }

    [data-theme-mode="dark"] .class-form-footer,
    [data-bs-theme="dark"] .class-form-footer {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    .class-form-footer .btn { border-radius: 10px; font-weight: 600; min-width: 120px; }

    @media (max-width: 767.98px) {
        .class-form-footer { position: static; }
        .class-form-footer .btn { flex: 1; }
    }
</style>
