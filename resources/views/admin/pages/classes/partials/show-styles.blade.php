<style>
    .class-show-page {
        --cs-radius: 14px;
        --cs-accent: #2563eb;
        --cs-surface: var(--custom-card-bg, #fff);
        --cs-border: var(--default-border, #e9ecef);
        --cs-muted: var(--text-muted, #6c757d);
        --cs-soft: rgba(37, 99, 235, 0.06);
    }

    [data-theme-mode="dark"] .class-show-page,
    [data-bs-theme="dark"] .class-show-page {
        --cs-surface: var(--custom-card-bg, #111a2e);
        --cs-border: rgba(255, 255, 255, 0.1);
        --cs-soft: rgba(37, 99, 235, 0.14);
    }

    .class-show-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: var(--cs-radius);
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.14) 0%, rgba(37, 99, 235, 0.04) 100%);
        border: 1px solid rgba(37, 99, 235, 0.2);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .class-show-hero,
    [data-bs-theme="dark"] .class-show-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .class-show-hero__cover {
        width: 88px;
        height: 88px;
        border-radius: 14px;
        object-fit: cover;
        border: 2px solid rgba(37, 99, 235, 0.2);
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .class-show-hero__content { flex: 1; min-width: 220px; }
    .class-show-hero__title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.35rem; }

    .class-show-hero__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem 0.65rem;
        margin-bottom: 0.75rem;
    }

    .class-show-hero__meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        color: var(--cs-muted);
    }

    .class-show-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .class-show-badge--active {
        background: rgba(22, 163, 74, 0.12);
        color: #16a34a;
        border: 1px solid rgba(22, 163, 74, 0.25);
    }

    .class-show-badge--inactive {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .class-show-badge--free {
        background: rgba(37, 99, 235, 0.1);
        color: var(--cs-accent);
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .class-show-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .class-show-stat {
        text-align: center;
        padding: 0.55rem 0.85rem;
        border-radius: 10px;
        background: var(--cs-surface);
        border: 1px solid var(--cs-border);
        min-width: 88px;
    }

    .class-show-stat__value {
        display: block;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--cs-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .class-show-stat__value,
    [data-bs-theme="dark"] .class-show-stat__value { color: #93c5fd; }

    .class-show-stat__label { font-size: 0.68rem; color: var(--cs-muted); }

    .class-show-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-self: center;
    }

    .class-show-hero__actions .btn { border-radius: 10px; font-weight: 600; font-size: 0.84rem; }

    .class-show-section {
        border-radius: var(--cs-radius);
        border: 1px solid var(--cs-border);
        background: var(--cs-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .class-show-section,
    [data-bs-theme="dark"] .class-show-section {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .class-show-section__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--cs-border);
        background: var(--cs-soft);
    }

    .class-show-section__title-wrap {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .class-show-section__icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: var(--cs-accent);
        font-size: 1rem;
    }

    .class-show-section__title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0; }
    .class-show-section__count {
        font-size: 0.75rem;
        color: var(--cs-muted);
        font-weight: 500;
    }

    .class-show-section__body { padding: 1.25rem; }

    .class-subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .class-subject-card {
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        border: 1px solid var(--cs-border);
        background: var(--cs-surface);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        height: 100%;
    }

    .class-subject-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.12);
        border-color: rgba(37, 99, 235, 0.3);
    }

    .class-subject-card__media {
        position: relative;
        aspect-ratio: 4 / 3;
        background: linear-gradient(145deg, var(--cs-soft) 0%, rgba(37, 99, 235, 0.02) 100%);
        overflow: hidden;
    }

    .class-subject-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .class-subject-card:hover .class-subject-card__media img {
        transform: scale(1.04);
    }

    .class-subject-card__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        color: var(--cs-muted);
    }

    .class-subject-card__placeholder i {
        font-size: 2rem;
        opacity: 0.45;
        color: var(--cs-accent);
    }

    .class-subject-card__placeholder span { font-size: 0.72rem; opacity: 0.7; }

    .class-subject-card__order {
        position: absolute;
        top: 0.5rem;
        inset-inline-start: 0.5rem;
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 600;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        backdrop-filter: blur(4px);
    }

    .class-subject-card__body {
        padding: 0.85rem 1rem 0.65rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .class-subject-card__name {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .class-subject-card__footer {
        padding: 0.65rem 1rem 0.85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        border-top: 1px solid var(--cs-border);
    }

    .class-subject-card__status {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
    }

    .class-subject-card__status--active {
        background: rgba(22, 163, 74, 0.12);
        color: #16a34a;
    }

    .class-subject-card__status--inactive {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .class-subject-card__link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--cs-accent);
        text-decoration: none;
        padding: 0.3rem 0.65rem;
        border-radius: 8px;
        border: 1px solid rgba(37, 99, 235, 0.25);
        background: rgba(37, 99, 235, 0.06);
        transition: background 0.15s ease, color 0.15s ease;
    }

    .class-subject-card__link:hover {
        background: var(--cs-accent);
        color: #fff;
        border-color: var(--cs-accent);
    }

    .class-show-empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
    }

    .class-show-empty__icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.6rem;
        color: var(--cs-accent);
        background: var(--cs-soft);
        border: 1px solid var(--cs-border);
    }

    .class-show-empty__title { font-weight: 700; font-size: 1rem; margin-bottom: 0.35rem; }
    .class-show-empty__text { color: var(--cs-muted); font-size: 0.85rem; margin-bottom: 1rem; }

    @media (max-width: 575.98px) {
        .class-show-hero__cover { width: 72px; height: 72px; }
        .class-subject-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    }
</style>
