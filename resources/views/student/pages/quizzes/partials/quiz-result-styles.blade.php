<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    .sqr-result {
        --sqr-accent: #0d9488;
        --sqr-accent-2: #059669;
        --sqr-pass: #059669;
        --sqr-fail: #e11d48;
        --sqr-surface: #ffffff;
        --sqr-muted: #64748b;
        --sqr-border: #d1fae5;
        --sqr-soft: rgba(13, 148, 136, 0.08);
        --sqr-radius: 18px;
        --sqr-font: "Alexandria", "Segoe UI", Tahoma, "Noto Sans Arabic", sans-serif;
        font-family: var(--sqr-font);
    }

    .sqr-result.main-content {
        background:
            radial-gradient(1100px 400px at 100% -8%, rgba(16, 185, 129, 0.16), transparent 55%),
            radial-gradient(800px 340px at 0% 0%, rgba(14, 165, 233, 0.1), transparent 50%),
            linear-gradient(180deg, #f0fdfa 0%, #f8fafc 45%, #f1f5f9 100%);
        min-height: 100vh;
    }

    [data-theme-mode="dark"] .sqr-result.main-content,
    [data-bs-theme="dark"] .sqr-result.main-content {
        background:
            radial-gradient(900px 360px at 100% 0%, rgba(16, 185, 129, 0.12), transparent 50%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%);
        --sqr-surface: #111a2e;
        --sqr-muted: #94a3b8;
        --sqr-border: rgba(255, 255, 255, 0.1);
        --sqr-soft: rgba(16, 185, 129, 0.12);
    }

    .sqr-result .sqr-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem 1.25rem;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.15rem;
        border-radius: var(--sqr-radius);
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.16) 0%, rgba(14, 165, 233, 0.08) 100%);
        border: 1px solid rgba(13, 148, 136, 0.28);
        box-shadow: 0 10px 28px rgba(13, 148, 136, 0.08);
    }

    .sqr-result .sqr-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(13, 148, 136, 0.16);
        color: var(--sqr-accent);
        font-size: 1.45rem;
        flex-shrink: 0;
    }

    .sqr-result .sqr-hero__title {
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0 0 0.15rem;
        line-height: 1.35;
    }

    .sqr-result .sqr-hero__meta {
        margin: 0;
        color: var(--sqr-muted);
        font-size: 0.86rem;
        font-weight: 600;
    }

    .sqr-result .sqr-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
        margin: 0;
        padding: 0;
        list-style: none;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .sqr-result .sqr-breadcrumb a {
        color: var(--sqr-accent);
        text-decoration: none;
    }

    .sqr-result .sqr-breadcrumb .active { color: var(--sqr-muted); }

    .sqr-result .sqr-card {
        border: 1px solid var(--sqr-border);
        border-radius: var(--sqr-radius);
        background: var(--sqr-surface);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .sqr-result .sqr-card__head {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.95rem 1.15rem;
        background: linear-gradient(135deg, #0f766e, #0d9488);
        color: #fff;
        font-weight: 800;
        font-size: 0.95rem;
        border: 0;
    }

    .sqr-result .sqr-card__body { padding: 1.15rem; }

    .sqr-result .sqr-score {
        text-align: center;
        padding: 1.5rem 1.25rem 1.25rem;
    }

    .sqr-result .sqr-score__ring {
        width: 132px;
        height: 132px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        position: relative;
        background: conic-gradient(var(--sqr-ring) var(--sqr-pct), rgba(148, 163, 184, 0.22) 0);
        animation: sqrPop .35s ease;
    }

    .sqr-result .sqr-score__ring::before {
        content: "";
        position: absolute;
        inset: 10px;
        border-radius: 50%;
        background: var(--sqr-surface);
    }

    .sqr-result .sqr-score__ring-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.15rem;
    }

    .sqr-result .sqr-score__pct {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
        color: var(--sqr-ring);
    }

    .sqr-result .sqr-score__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        margin: 0 auto 0.85rem;
        font-size: 1.65rem;
    }

    .sqr-result .sqr-score.is-pass {
        --sqr-ring: var(--sqr-pass);
    }

    .sqr-result .sqr-score.is-pass .sqr-score__icon {
        background: rgba(5, 150, 105, 0.14);
        color: var(--sqr-pass);
    }

    .sqr-result .sqr-score.is-fail {
        --sqr-ring: var(--sqr-fail);
    }

    .sqr-result .sqr-score.is-fail .sqr-score__icon {
        background: rgba(225, 29, 72, 0.12);
        color: var(--sqr-fail);
    }

    .sqr-result .sqr-score__status {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0 0 0.35rem;
        color: var(--sqr-ring);
    }

    .sqr-result .sqr-score__points {
        font-size: 1rem;
        font-weight: 700;
        color: var(--sqr-muted);
        margin: 0 0 1rem;
    }

    .sqr-result .sqr-score__bar {
        height: 12px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.2);
        overflow: hidden;
        margin-bottom: 1.15rem;
    }

    .sqr-result .sqr-score__bar > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--sqr-ring), color-mix(in srgb, var(--sqr-ring) 70%, #fff));
        transition: width .4s ease;
    }

    .sqr-result .sqr-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.55rem;
    }

    .sqr-result .sqr-actions .btn {
        border-radius: 12px;
        font-weight: 800;
        min-height: 44px;
        padding-inline: 1.1rem;
    }

    .sqr-result .sqr-actions .btn-primary {
        background: linear-gradient(135deg, #0d9488, #059669);
        border: 0;
        box-shadow: 0 8px 18px rgba(13, 148, 136, 0.25);
    }

    .sqr-result .sqr-actions .btn-outline-secondary {
        border-width: 2px;
        color: #475569;
        border-color: #cbd5e1;
        background: #fff;
    }

    .sqr-result .sqr-info-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.55rem;
    }

    .sqr-result .sqr-info-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        border-radius: 12px;
        background: var(--sqr-soft);
        border: 1px solid transparent;
    }

    .sqr-result .sqr-info-list .label {
        color: var(--sqr-muted);
        font-weight: 600;
        font-size: 0.88rem;
    }

    .sqr-result .sqr-info-list .value {
        font-weight: 800;
        font-size: 0.9rem;
        text-align: end;
    }

    .sqr-result .sqr-answer {
        border: 2px solid var(--sqr-border);
        border-radius: 16px;
        padding: 1rem 1.1rem;
        margin-bottom: 0.9rem;
        background: var(--sqr-surface);
        transition: transform .15s ease, box-shadow .15s ease;
        animation: sqrCardIn .28s ease;
    }

    .sqr-result .sqr-answer.is-correct {
        border-color: rgba(5, 150, 105, 0.45);
        background: rgba(5, 150, 105, 0.06);
    }

    .sqr-result .sqr-answer.is-wrong {
        border-color: rgba(225, 29, 72, 0.35);
        background: rgba(225, 29, 72, 0.05);
    }

    .sqr-result .sqr-answer__top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .sqr-result .sqr-answer__num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.9rem;
        color: #fff;
        margin-inline-end: 0.45rem;
        flex-shrink: 0;
    }

    .sqr-result .sqr-answer.is-correct .sqr-answer__num { background: var(--sqr-pass); }
    .sqr-result .sqr-answer.is-wrong .sqr-answer__num { background: var(--sqr-fail); }

    .sqr-result .sqr-answer__title {
        font-weight: 700;
        font-size: 1.02rem;
        line-height: 1.7;
    }

    .sqr-result .sqr-answer__badge {
        border-radius: 999px;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
        white-space: nowrap;
    }

    .sqr-result .sqr-explain {
        margin-top: 0.85rem;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: rgba(14, 165, 233, 0.08);
        border-inline-start: 4px solid #0ea5e9;
    }

    .sqr-result .sqr-explain.is-correct {
        background: rgba(5, 150, 105, 0.08);
        border-inline-start-color: var(--sqr-pass);
    }

    .sqr-result .sqr-empty {
        text-align: center;
        padding: 2.5rem 1rem;
    }

    .sqr-result .sqr-empty__icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        margin: 0 auto 1rem;
        background: var(--sqr-soft);
        color: var(--sqr-accent);
        font-size: 1.8rem;
    }

    .sqr-result .mcq-option-card {
        border-radius: 14px;
        background: #fff;
    }

    .sqr-result .mcq-option-card.is-selected {
        border-color: #0d9488;
        background: rgba(13, 148, 136, 0.08);
    }

    .sqr-result .mcq-option-card.is-correct {
        border-color: #059669;
        background: rgba(5, 150, 105, 0.08);
    }

    .sqr-result .mcq-option-card.is-wrong {
        border-color: #e11d48;
        background: rgba(225, 29, 72, 0.06);
    }

    @keyframes sqrPop {
        from { transform: scale(0.92); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    @keyframes sqrCardIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 991.98px) {
        .sqr-result.main-content .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .sqr-result .sqr-hero {
            padding: 0.85rem 1rem;
            margin-bottom: 0.85rem;
        }

        .sqr-result .sqr-hero__icon { width: 42px; height: 42px; font-size: 1.15rem; }
        .sqr-result .sqr-hero__title { font-size: 1.05rem; }

        .sqr-result .sqr-score { padding: 1.15rem 1rem; }
        .sqr-result .sqr-score__ring { width: 112px; height: 112px; }
        .sqr-result .sqr-score__pct { font-size: 1.45rem; }

        .sqr-result .sqr-actions { flex-direction: column; }
        .sqr-result .sqr-actions .btn { width: 100%; }

        .sqr-result .sqr-answer__top {
            flex-direction: column;
            align-items: stretch;
        }

        .sqr-result .sqr-answer__badge { align-self: flex-start; }
    }

    @media (max-width: 575.98px) {
        .sqr-result .sqr-hero__icon { display: none; }
        .sqr-result .sqr-breadcrumb { width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .sqr-result .sqr-score__ring,
        .sqr-result .sqr-answer,
        .sqr-result .sqr-score__bar > span {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
