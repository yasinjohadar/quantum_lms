@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .library-page, .library-form-page {
        --lib-radius: 14px;
        --lib-accent: #0d8f7a;
        --lib-surface: var(--custom-card-bg, #fff);
        --lib-border: var(--default-border, #e9ecef);
        --lib-muted: var(--text-muted, #6c757d);
        --lib-soft: rgba(13, 143, 122, 0.07);
    }

    [data-theme-mode="dark"] .library-page, [data-bs-theme="dark"] .library-page,
    [data-theme-mode="dark"] .library-form-page, [data-bs-theme="dark"] .library-form-page {
        --lib-surface: var(--custom-card-bg, #111a2e);
        --lib-border: rgba(255, 255, 255, 0.1);
        --lib-soft: rgba(13, 143, 122, 0.16);
    }

    /* Hero (رأس الصفحة) — مستخدم في صفحات القوائم والنماذج معاً */
    .library-hero, .library-form-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--lib-radius);
        background: linear-gradient(135deg, rgba(13, 143, 122, 0.16) 0%, rgba(13, 143, 122, 0.04) 100%);
        border: 1px solid rgba(13, 143, 122, 0.22);
        box-shadow: 0 8px 24px rgba(13, 143, 122, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .library-hero, [data-bs-theme="dark"] .library-hero,
    [data-theme-mode="dark"] .library-form-hero, [data-bs-theme="dark"] .library-form-hero {
        background: linear-gradient(135deg, rgba(13, 143, 122, 0.22) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .library-hero__icon, .library-form-hero__icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        color: var(--lib-accent);
        background: rgba(13, 143, 122, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .library-hero__icon, [data-bs-theme="dark"] .library-hero__icon,
    [data-theme-mode="dark"] .library-form-hero__icon, [data-bs-theme="dark"] .library-form-hero__icon { color: #5eead4; }

    .library-hero__content, .library-form-hero__content { flex: 1; min-width: 200px; }
    .library-hero__title, .library-form-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .library-hero__subtitle, .library-form-hero__subtitle { color: var(--lib-muted); font-size: 0.875rem; margin-bottom: 0; }

    .library-hero__actions, .library-form-hero__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .library-hero__actions .btn, .library-form-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .library-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--lib-surface);
        border: 1px solid var(--lib-border);
        min-width: 110px;
    }
    .library-stat-mini__value { display: block; font-size: 1.35rem; font-weight: 700; color: var(--lib-accent); line-height: 1.2; }
    [data-theme-mode="dark"] .library-stat-mini__value, [data-bs-theme="dark"] .library-stat-mini__value { color: #5eead4; }
    .library-stat-mini__label { font-size: 0.72rem; color: var(--lib-muted); }

    /* بطاقة القائمة/الفلاتر */
    .library-card {
        border-radius: var(--lib-radius);
        border: 1px solid var(--lib-border);
        background: var(--lib-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    [data-theme-mode="dark"] .library-card, [data-bs-theme="dark"] .library-card { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22); }

    .library-card__header {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--lib-border);
        background: var(--lib-soft);
        font-weight: 700; font-size: 0.95rem;
    }

    .library-card__header-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(13, 143, 122, 0.12);
        color: var(--lib-accent);
    }

    .library-card__body { padding: 1.25rem; }

    .library-filters .form-label { font-size: 0.78rem; font-weight: 600; color: var(--lib-muted); margin-bottom: 0.3rem; }
    .library-filters .form-control, .library-filters .form-select { border-radius: 10px; border-color: var(--lib-border); font-size: 0.875rem; }

    .library-table-wrap { border-radius: 12px; border: 1px solid var(--lib-border); overflow: hidden; }
    .library-table { margin-bottom: 0; }
    .library-table thead th {
        font-size: 0.78rem; font-weight: 700; color: var(--lib-muted);
        background: var(--lib-soft);
        border-bottom: 1px solid var(--lib-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    .library-table tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-bottom: 1px solid var(--lib-border); }
    .library-table tbody tr { transition: background 0.15s ease; }
    .library-table tbody tr:hover { background: var(--lib-soft); }
    .library-table tbody tr:last-child td { border-bottom: none; }

    .library-empty { padding: 3rem 1rem; text-align: center; color: var(--lib-muted); }
    .library-empty i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

    /* بطاقات النماذج (إضافة/تعديل) */
    .library-form-card {
        border-radius: var(--lib-radius);
        border: 1px solid var(--lib-border);
        background: var(--lib-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    [data-theme-mode="dark"] .library-form-card, [data-bs-theme="dark"] .library-form-card { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22); }

    .library-form-card__header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--lib-border); background: var(--lib-soft); }
    .library-form-card__header-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(13, 143, 122, 0.14); color: var(--lib-accent);
        flex-shrink: 0; font-size: 1.05rem;
    }
    .library-form-card__title { font-weight: 700; font-size: 0.98rem; }
    .library-form-card__desc { color: var(--lib-muted); font-size: 0.8rem; margin: 0.1rem 0 0; }
    .library-form-card__body { padding: 1.25rem; }

    .library-form-field .form-label { font-weight: 600; font-size: 0.85rem; }
    .library-hint { display: flex; align-items: center; gap: 0.35rem; color: var(--lib-muted); font-size: 0.76rem; margin-top: 0.35rem; }
    .library-hint i { color: var(--lib-accent); }

    @media (max-width: 767.98px) {
        .library-hero__actions, .library-form-hero__actions { width: 100%; }
        .library-hero__actions .btn, .library-form-hero__actions .btn { flex: 1; }
    }
</style>
