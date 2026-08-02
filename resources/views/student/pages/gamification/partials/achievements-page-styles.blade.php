<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    .sach-page {
        --sach-accent: #0d9488;
        --sach-accent-2: #059669;
        --sach-warn: #f59e0b;
        --sach-surface: #ffffff;
        --sach-muted: #64748b;
        --sach-border: #d1fae5;
        --sach-soft: rgba(13, 148, 136, 0.08);
        --sach-radius: 18px;
        --sach-font: "Alexandria", "Segoe UI", Tahoma, "Noto Sans Arabic", sans-serif;
        --sach-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        font-family: var(--sach-font);
    }

    .sach-page.main-content {
        background:
            radial-gradient(1100px 420px at 100% -10%, rgba(16, 185, 129, 0.16), transparent 55%),
            radial-gradient(900px 360px at 0% 0%, rgba(245, 158, 11, 0.1), transparent 50%),
            linear-gradient(180deg, #f0fdfa 0%, #f8fafc 45%, #f1f5f9 100%);
        min-height: 100vh;
    }

    [data-theme-mode="dark"] .sach-page.main-content,
    [data-bs-theme="dark"] .sach-page.main-content {
        background:
            radial-gradient(900px 360px at 100% 0%, rgba(16, 185, 129, 0.14), transparent 50%),
            radial-gradient(700px 300px at 0% 10%, rgba(245, 158, 11, 0.08), transparent 45%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%);
        --sach-surface: #111a2e;
        --sach-muted: #94a3b8;
        --sach-border: rgba(255, 255, 255, 0.1);
        --sach-soft: rgba(16, 185, 129, 0.12);
        --sach-shadow: 0 12px 30px rgba(0, 0, 0, 0.28);
    }

    .sach-page .sach-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1rem;
        border-radius: var(--sach-radius);
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.16) 0%, rgba(245, 158, 11, 0.1) 100%);
        border: 1px solid rgba(13, 148, 136, 0.28);
        box-shadow: var(--sach-shadow);
    }

    .sach-page .sach-hero__main {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        min-width: 0;
    }

    .sach-page .sach-hero__icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: rgba(13, 148, 136, 0.16);
        color: var(--sach-accent);
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .sach-page .sach-hero__title {
        margin: 0 0 0.2rem;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .sach-page .sach-hero__meta {
        margin: 0;
        color: var(--sach-muted);
        font-size: 0.88rem;
        font-weight: 600;
    }

    .sach-page .sach-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .sach-page .sach-stat {
        min-width: 96px;
        padding: 0.65rem 0.85rem;
        border-radius: 14px;
        background: var(--sach-surface);
        border: 1px solid var(--sach-border);
        text-align: center;
    }

    .sach-page .sach-stat__value {
        display: block;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--sach-accent);
        line-height: 1.1;
    }

    .sach-page .sach-stat__label {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--sach-muted);
    }

    .sach-page .sach-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 1rem;
    }

    .sach-page .sach-filter {
        border: 1px solid var(--sach-border);
        background: var(--sach-surface);
        color: var(--sach-muted);
        border-radius: 999px;
        padding: 0.45rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
        transition: transform .15s ease, background .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .sach-page .sach-filter:hover {
        transform: translateY(-1px);
        border-color: rgba(13, 148, 136, 0.4);
        color: var(--sach-accent);
    }

    .sach-page .sach-filter.is-active {
        background: linear-gradient(135deg, #0d9488, #059669);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 6px 16px rgba(13, 148, 136, 0.25);
    }

    .sach-page .sach-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .sach-page .sach-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 100%;
        border-radius: var(--sach-radius);
        background: var(--sach-surface);
        border: 1px solid var(--sach-border);
        box-shadow: var(--sach-shadow);
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: sachReveal .4s ease backwards;
    }

    .sach-page .sach-card::before {
        content: "";
        position: absolute;
        inset-inline: 0;
        top: 0;
        height: 4px;
        background: var(--sach-type, var(--sach-accent));
    }

    .sach-page .sach-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
        border-color: color-mix(in srgb, var(--sach-type, var(--sach-accent)) 45%, transparent);
    }

    .sach-page .sach-card:focus-within {
        outline: 2px solid rgba(13, 148, 136, 0.35);
        outline-offset: 2px;
    }

    .sach-page .sach-card.is-locked {
        opacity: 0.92;
    }

    .sach-page .sach-card.is-locked .sach-card__icon {
        filter: grayscale(0.35);
    }

    .sach-page .sach-card.is-done {
        background:
            linear-gradient(180deg, rgba(16, 185, 129, 0.08) 0%, transparent 42%),
            var(--sach-surface);
    }

    .sach-page .sach-card.is-done::after {
        content: "";
        position: absolute;
        inset-inline-end: -18px;
        top: -18px;
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.22), transparent 68%);
        pointer-events: none;
    }

    .sach-page .sach-card__body {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1.1rem 1.15rem 1.15rem;
        flex: 1;
    }

    .sach-page .sach-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .sach-page .sach-card__icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.4rem;
        color: var(--sach-type, var(--sach-accent));
        background: color-mix(in srgb, var(--sach-type, var(--sach-accent)) 14%, transparent);
        flex-shrink: 0;
        transition: transform .2s ease;
    }

    .sach-page .sach-card:hover .sach-card__icon {
        transform: scale(1.08) rotate(-4deg);
    }

    .sach-page .sach-card__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 800;
        background: color-mix(in srgb, var(--sach-type, var(--sach-accent)) 12%, transparent);
        color: var(--sach-type, var(--sach-accent));
        white-space: nowrap;
    }

    .sach-page .sach-card__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.4;
    }

    .sach-page .sach-card__desc {
        margin: 0;
        color: var(--sach-muted);
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.65;
        flex: 1;
    }

    .sach-page .sach-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
    }

    .sach-page .sach-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border-radius: 999px;
        padding: 0.28rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 800;
        background: var(--sach-soft);
        color: var(--sach-accent);
    }

    .sach-page .sach-chip--points {
        background: rgba(245, 158, 11, 0.14);
        color: #d97706;
    }

    .sach-page .sach-chip--done {
        background: rgba(5, 150, 105, 0.14);
        color: #059669;
    }

    .sach-page .sach-progress {
        margin-top: auto;
    }

    .sach-page .sach-progress__label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.4rem;
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--sach-muted);
    }

    .sach-page .sach-progress__track {
        height: 10px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.22);
        overflow: hidden;
    }

    .sach-page .sach-progress__bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--sach-type, var(--sach-accent)), color-mix(in srgb, var(--sach-type, var(--sach-accent)) 65%, #fff));
        transition: width .35s ease;
    }

    .sach-page .sach-card.is-done .sach-progress__bar {
        background: linear-gradient(90deg, #10b981, #f59e0b);
    }

    .sach-page .sach-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2.5rem 1rem;
        border-radius: var(--sach-radius);
        background: var(--sach-surface);
        border: 1px dashed var(--sach-border);
    }

    .sach-page .sach-empty__icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 20px;
        display: grid;
        place-items: center;
        background: var(--sach-soft);
        color: var(--sach-accent);
        font-size: 1.8rem;
    }

    .sach-page .sach-card[data-type="attendance"] { --sach-type: #0ea5e9; }
    .sach-page .sach-card[data-type="quiz"] { --sach-type: #0d9488; }
    .sach-page .sach-card[data-type="course"] { --sach-type: #8b5cf6; }
    .sach-page .sach-card[data-type="streak"] { --sach-type: #f59e0b; }
    .sach-page .sach-card[data-type="special"] { --sach-type: #e11d48; }

    .sach-page .gami-help-box {
        border-radius: var(--sach-radius);
        border: 1px solid var(--sach-border);
        background: var(--sach-surface);
        box-shadow: var(--sach-shadow);
        overflow: hidden;
    }

    .sach-page .gami-help-box__toggle {
        background: var(--sach-soft);
        color: inherit;
    }

    .sach-page .gami-help-box__summary,
    .sach-page .gami-help-box__list {
        color: var(--sach-muted);
    }

    [data-theme-mode="dark"] .sach-page .gami-help-box__summary,
    [data-bs-theme="dark"] .sach-page .gami-help-box__summary {
        color: #cbd5e1;
    }

    @keyframes sachReveal {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sach-page .sach-grid > .sach-card:nth-child(1) { animation-delay: .02s; }
    .sach-page .sach-grid > .sach-card:nth-child(2) { animation-delay: .05s; }
    .sach-page .sach-grid > .sach-card:nth-child(3) { animation-delay: .08s; }
    .sach-page .sach-grid > .sach-card:nth-child(4) { animation-delay: .11s; }
    .sach-page .sach-grid > .sach-card:nth-child(5) { animation-delay: .14s; }
    .sach-page .sach-grid > .sach-card:nth-child(n+6) { animation-delay: .17s; }

    @media (max-width: 1199.98px) {
        .sach-page .sach-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .sach-page.main-content .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .sach-page .sach-hero {
            padding: 0.95rem 1rem;
        }

        .sach-page .sach-hero__icon { width: 44px; height: 44px; font-size: 1.2rem; }
        .sach-page .sach-hero__title { font-size: 1.08rem; }

        .sach-page .sach-stats { width: 100%; }
        .sach-page .sach-stat { flex: 1 1 0; min-width: 0; }

        .sach-page .sach-grid {
            grid-template-columns: 1fr;
            gap: 0.85rem;
        }

        .sach-page .sach-filters {
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 0.25rem;
            -webkit-overflow-scrolling: touch;
        }

        .sach-page .sach-filter { flex: 0 0 auto; }
    }

    @media (max-width: 575.98px) {
        .sach-page .sach-hero__icon { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .sach-page .sach-card,
        .sach-page .sach-filter,
        .sach-page .sach-card__icon,
        .sach-page .sach-progress__bar {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }

    [data-theme-mode="dark"] .sach-page .sach-card:hover,
    [data-bs-theme="dark"] .sach-page .sach-card:hover {
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.35);
    }

    [data-theme-mode="dark"] .sach-page .sach-chip--points,
    [data-bs-theme="dark"] .sach-page .sach-chip--points {
        background: rgba(245, 158, 11, 0.18);
        color: #fbbf24;
    }

    [data-theme-mode="dark"] .sach-page .sach-chip--done,
    [data-bs-theme="dark"] .sach-page .sach-chip--done {
        background: rgba(16, 185, 129, 0.18);
        color: #34d399;
    }
</style>
