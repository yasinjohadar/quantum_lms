<style>
    .subject-form-page {
        --sf-radius: 14px;
        --sf-accent: #d97706;
        --sf-surface: var(--custom-card-bg, #fff);
        --sf-border: var(--default-border, #e9ecef);
        --sf-muted: var(--text-muted, #6c757d);
        --sf-soft: rgba(217, 119, 6, 0.06);
    }

    [data-theme-mode="dark"] .subject-form-page,
    [data-bs-theme="dark"] .subject-form-page {
        --sf-surface: var(--custom-card-bg, #111a2e);
        --sf-border: rgba(255, 255, 255, 0.1);
        --sf-soft: rgba(217, 119, 6, 0.14);
    }

    .subject-form-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--sf-radius);
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.14) 0%, rgba(217, 119, 6, 0.04) 100%);
        border: 1px solid rgba(217, 119, 6, 0.22);
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .subject-form-hero,
    [data-bs-theme="dark"] .subject-form-hero {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .subject-form-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--sf-accent);
        background: rgba(217, 119, 6, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .subject-form-hero__icon,
    [data-bs-theme="dark"] .subject-form-hero__icon { color: #fcd34d; }

    .subject-form-hero__content { flex: 1; min-width: 200px; }
    .subject-form-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .subject-form-hero__subtitle { color: var(--sf-muted); font-size: 0.875rem; margin-bottom: 0; }
    .subject-form-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .subject-form-card {
        border-radius: var(--sf-radius);
        border: 1px solid var(--sf-border);
        background: var(--sf-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .subject-form-card,
    [data-bs-theme="dark"] .subject-form-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .subject-form-card__header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--sf-border);
        background: var(--sf-soft);
    }

    .subject-form-card__header-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(217, 119, 6, 0.12);
        color: var(--sf-accent);
        flex-shrink: 0;
        font-size: 1rem;
    }

    .subject-form-card__header-text { flex: 1; min-width: 0; }
    .subject-form-card__title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.15rem; }
    .subject-form-card__desc { font-size: 0.78rem; color: var(--sf-muted); margin-bottom: 0; }

    .subject-form-card__body { padding: 1.25rem; }

    .subject-form-field .form-label {
        font-weight: 600;
        font-size: 0.84rem;
        margin-bottom: 0.35rem;
    }

    .subject-form-field .form-control,
    .subject-form-field .form-select {
        border-radius: 10px;
        border-color: var(--sf-border);
        font-size: 0.9rem;
    }

    .subject-form-field .form-control:focus,
    .subject-form-field .form-select:focus {
        border-color: rgba(217, 119, 6, 0.5);
        box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.1);
    }

    .subject-form-field .form-control[readonly] {
        background: var(--sf-soft);
        cursor: not-allowed;
    }

    .subject-form-hint {
        display: flex;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: var(--sf-muted);
        margin-top: 0.4rem;
        line-height: 1.45;
    }

    .subject-form-hint i {
        flex-shrink: 0;
        margin-top: 0.1rem;
        opacity: 0.7;
    }

    .subject-form-switch-box {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--sf-border);
        background: var(--sf-soft);
        height: 100%;
    }

    .subject-form-switch-box .form-check-label {
        font-weight: 600;
        font-size: 0.84rem;
    }

    .subject-form-callout {
        padding: 1rem 1.15rem;
        border-radius: 10px;
        border: 1px solid var(--sf-border);
        background: var(--sf-soft);
    }

    .subject-form-callout .form-check-label {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .subject-form-currency-table {
        margin-bottom: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .subject-form-currency-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--sf-muted);
        background: var(--sf-soft);
        border-bottom: 1px solid var(--sf-border);
        padding: 0.75rem 1rem;
    }

    .subject-form-currency-table tbody td {
        padding: 0.65rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--sf-border);
        font-size: 0.875rem;
    }

    .subject-form-currency-table tbody tr:last-child td { border-bottom: none; }

    .subject-form-image-preview {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--sf-border);
    }

    .subject-form-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 1rem 1.25rem;
        border-radius: var(--sf-radius);
        border: 1px solid var(--sf-border);
        background: var(--sf-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        position: sticky;
        bottom: 1rem;
        z-index: 10;
    }

    [data-theme-mode="dark"] .subject-form-footer,
    [data-bs-theme="dark"] .subject-form-footer {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    .subject-form-footer .btn { border-radius: 10px; font-weight: 600; min-width: 120px; }

    @media (max-width: 767.98px) {
        .subject-form-footer { position: static; }
        .subject-form-footer .btn { flex: 1; }
    }
</style>
