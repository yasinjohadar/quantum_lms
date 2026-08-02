<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    .stask-page {
        --stask-accent: #0d9488;
        --stask-daily: #0ea5e9;
        --stask-weekly: #8b5cf6;
        --stask-warn: #f59e0b;
        --stask-ok: #059669;
        --stask-danger: #e11d48;
        --stask-surface: #ffffff;
        --stask-muted: #64748b;
        --stask-border: #d1fae5;
        --stask-soft: rgba(13, 148, 136, 0.08);
        --stask-radius: 18px;
        --stask-font: "Alexandria", "Segoe UI", Tahoma, "Noto Sans Arabic", sans-serif;
        --stask-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        font-family: var(--stask-font);
    }

    .stask-page.main-content {
        background:
            radial-gradient(1100px 420px at 100% -10%, rgba(14, 165, 233, 0.14), transparent 55%),
            radial-gradient(900px 360px at 0% 0%, rgba(139, 92, 246, 0.1), transparent 50%),
            linear-gradient(180deg, #f0f9ff 0%, #f8fafc 45%, #f1f5f9 100%);
        min-height: 100vh;
    }

    [data-theme-mode="dark"] .stask-page.main-content,
    [data-bs-theme="dark"] .stask-page.main-content {
        background:
            radial-gradient(900px 360px at 100% 0%, rgba(14, 165, 233, 0.12), transparent 50%),
            radial-gradient(700px 300px at 0% 10%, rgba(139, 92, 246, 0.1), transparent 45%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%);
        --stask-surface: #111a2e;
        --stask-muted: #94a3b8;
        --stask-border: rgba(255, 255, 255, 0.1);
        --stask-soft: rgba(14, 165, 233, 0.12);
        --stask-shadow: 0 12px 30px rgba(0, 0, 0, 0.28);
    }

    .stask-page .stask-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1rem;
        border-radius: var(--stask-radius);
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.14) 0%, rgba(139, 92, 246, 0.1) 100%);
        border: 1px solid rgba(14, 165, 233, 0.28);
        box-shadow: var(--stask-shadow);
    }

    .stask-page .stask-hero__main {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        min-width: 0;
    }

    .stask-page .stask-hero__icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: rgba(14, 165, 233, 0.16);
        color: var(--stask-daily);
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stask-page .stask-hero__title {
        margin: 0 0 0.2rem;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .stask-page .stask-hero__meta {
        margin: 0;
        color: var(--stask-muted);
        font-size: 0.88rem;
        font-weight: 600;
    }

    .stask-page .stask-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .stask-page .stask-stat {
        min-width: 96px;
        padding: 0.65rem 0.85rem;
        border-radius: 14px;
        background: var(--stask-surface);
        border: 1px solid var(--stask-border);
        text-align: center;
    }

    .stask-page .stask-stat__value {
        display: block;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--stask-accent);
        line-height: 1.1;
    }

    .stask-page .stask-stat__label {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--stask-muted);
    }

    .stask-page .stask-section {
        margin-bottom: 1.25rem;
    }

    .stask-page .stask-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
        flex-wrap: wrap;
    }

    .stask-page .stask-section__title {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
    }

    .stask-page .stask-section__title-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.05rem;
    }

    .stask-page .stask-section--daily .stask-section__title-icon {
        background: rgba(14, 165, 233, 0.14);
        color: var(--stask-daily);
    }

    .stask-page .stask-section--weekly .stask-section__title-icon {
        background: rgba(139, 92, 246, 0.14);
        color: var(--stask-weekly);
    }

    .stask-page .stask-section__count {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.78rem;
        font-weight: 800;
        background: var(--stask-surface);
        border: 1px solid var(--stask-border);
        color: var(--stask-muted);
    }

    .stask-page .stask-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .stask-page .stask-card {
        --stask-type: var(--stask-accent);
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 100%;
        border-radius: var(--stask-radius);
        background: var(--stask-surface);
        border: 1px solid var(--stask-border);
        box-shadow: var(--stask-shadow);
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: staskReveal .4s ease backwards;
    }

    .stask-page .stask-card::before {
        content: "";
        position: absolute;
        inset-inline: 0;
        top: 0;
        height: 4px;
        background: var(--stask-type);
    }

    .stask-page .stask-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
        border-color: color-mix(in srgb, var(--stask-type) 45%, transparent);
    }

    .stask-page .stask-card.is-done {
        background:
            linear-gradient(180deg, rgba(5, 150, 105, 0.08) 0%, transparent 42%),
            var(--stask-surface);
    }

    .stask-page .stask-card.is-expired {
        opacity: 0.88;
    }

    .stask-page .stask-card__body {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1.1rem 1.15rem 1.15rem;
        flex: 1;
    }

    .stask-page .stask-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .stask-page .stask-card__icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: grid;
        place-items: center;
        font-size: 1.3rem;
        color: var(--stask-type);
        background: color-mix(in srgb, var(--stask-type) 14%, transparent);
        flex-shrink: 0;
        transition: transform .2s ease;
    }

    .stask-page .stask-card:hover .stask-card__icon {
        transform: scale(1.08) rotate(-4deg);
    }

    .stask-page .stask-card__status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border-radius: 999px;
        padding: 0.32rem 0.7rem;
        font-size: 0.74rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .stask-page .stask-card__status.is-progress {
        background: rgba(14, 165, 233, 0.14);
        color: #0284c7;
    }

    .stask-page .stask-card__status.is-done {
        background: rgba(5, 150, 105, 0.14);
        color: var(--stask-ok);
    }

    .stask-page .stask-card__status.is-expired {
        background: rgba(225, 29, 72, 0.12);
        color: var(--stask-danger);
    }

    .stask-page .stask-card__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.45;
    }

    .stask-page .stask-card__desc {
        margin: 0;
        color: var(--stask-muted);
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.65;
        flex: 1;
    }

    .stask-page .stask-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
    }

    .stask-page .stask-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border-radius: 999px;
        padding: 0.28rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 800;
        background: var(--stask-soft);
        color: var(--stask-accent);
    }

    .stask-page .stask-chip--points {
        background: rgba(245, 158, 11, 0.14);
        color: #d97706;
    }

    .stask-page .stask-chip--period {
        background: rgba(139, 92, 246, 0.12);
        color: #7c3aed;
    }

    .stask-page .stask-progress__label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.4rem;
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--stask-muted);
    }

    .stask-page .stask-progress__track {
        height: 11px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.22);
        overflow: hidden;
    }

    .stask-page .stask-progress__bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--stask-type), color-mix(in srgb, var(--stask-type) 65%, #fff));
        transition: width .35s ease;
    }

    .stask-page .stask-card.is-done .stask-progress__bar {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .stask-page .stask-card[data-type="attendance"] { --stask-type: #0ea5e9; }
    .stask-page .stask-card[data-type="lesson_completion"] { --stask-type: #0d9488; }
    .stask-page .stask-card[data-type="quiz"] { --stask-type: #8b5cf6; }
    .stask-page .stask-card[data-type="question"] { --stask-type: #f59e0b; }

    .stask-page .stask-section--daily .stask-card:not([data-type]) { --stask-type: var(--stask-daily); }
    .stask-page .stask-section--weekly .stask-card:not([data-type]) { --stask-type: var(--stask-weekly); }

    .stask-page .stask-empty {
        text-align: center;
        padding: 2rem 1rem;
        border-radius: var(--stask-radius);
        background: var(--stask-surface);
        border: 1px dashed var(--stask-border);
    }

    .stask-page .stask-empty__icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 0.85rem;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: var(--stask-soft);
        color: var(--stask-accent);
        font-size: 1.6rem;
    }

    .stask-page .gami-help-box {
        border-radius: var(--stask-radius);
        border: 1px solid var(--stask-border);
        background: var(--stask-surface);
        box-shadow: var(--stask-shadow);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .stask-page .gami-help-box__toggle {
        background: var(--stask-soft);
        color: inherit;
    }

    .stask-page .gami-help-box__summary,
    .stask-page .gami-help-box__list {
        color: var(--stask-muted);
    }

    [data-theme-mode="dark"] .stask-page .gami-help-box__summary,
    [data-bs-theme="dark"] .stask-page .gami-help-box__summary {
        color: #cbd5e1;
    }

    @keyframes staskReveal {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stask-page .stask-grid > .stask-card:nth-child(1) { animation-delay: .02s; }
    .stask-page .stask-grid > .stask-card:nth-child(2) { animation-delay: .05s; }
    .stask-page .stask-grid > .stask-card:nth-child(3) { animation-delay: .08s; }
    .stask-page .stask-grid > .stask-card:nth-child(4) { animation-delay: .11s; }
    .stask-page .stask-grid > .stask-card:nth-child(n+5) { animation-delay: .14s; }

    @media (max-width: 991.98px) {
        .stask-page .stask-grid {
            grid-template-columns: 1fr;
            gap: 0.85rem;
        }
    }

    @media (max-width: 767.98px) {
        .stask-page.main-content .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .stask-page .stask-hero { padding: 0.95rem 1rem; }
        .stask-page .stask-hero__icon { width: 44px; height: 44px; font-size: 1.2rem; }
        .stask-page .stask-hero__title { font-size: 1.08rem; }
        .stask-page .stask-stats { width: 100%; }
        .stask-page .stask-stat { flex: 1 1 0; min-width: 0; }
    }

    @media (max-width: 575.98px) {
        .stask-page .stask-hero__icon { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .stask-page .stask-card,
        .stask-page .stask-card__icon,
        .stask-page .stask-progress__bar {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }

    [data-theme-mode="dark"] .stask-page .stask-card:hover,
    [data-bs-theme="dark"] .stask-page .stask-card:hover {
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.35);
    }

    [data-theme-mode="dark"] .stask-page .stask-chip--points,
    [data-bs-theme="dark"] .stask-page .stask-chip--points {
        background: rgba(245, 158, 11, 0.18);
        color: #fbbf24;
    }

    [data-theme-mode="dark"] .stask-page .stask-chip--period,
    [data-bs-theme="dark"] .stask-page .stask-chip--period {
        background: rgba(139, 92, 246, 0.2);
        color: #c4b5fd;
    }

    [data-theme-mode="dark"] .stask-page .stask-card__status.is-progress,
    [data-bs-theme="dark"] .stask-page .stask-card__status.is-progress {
        background: rgba(14, 165, 233, 0.18);
        color: #7dd3fc;
    }
</style>
