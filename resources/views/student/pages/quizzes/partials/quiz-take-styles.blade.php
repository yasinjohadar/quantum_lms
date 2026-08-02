<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    .sqt-take {
        --sqt-accent: #0d9488;
        --sqt-accent-2: #059669;
        --sqt-warn: #f59e0b;
        --sqt-danger: #e11d48;
        --sqt-surface: #ffffff;
        --sqt-muted: #64748b;
        --sqt-border: #d1fae5;
        --sqt-soft: rgba(13, 148, 136, 0.08);
        --sqt-radius: 18px;
        --sqt-font: "Alexandria", "Segoe UI", Tahoma, "Noto Sans Arabic", sans-serif;
        font-family: var(--sqt-font);
    }

    .sqt-take .main-content.app-content,
    .sqt-take.main-content {
        background:
            radial-gradient(1200px 420px at 100% -10%, rgba(16, 185, 129, 0.16), transparent 55%),
            radial-gradient(900px 380px at 0% 0%, rgba(14, 165, 233, 0.1), transparent 50%),
            linear-gradient(180deg, #f0fdfa 0%, #f8fafc 42%, #f1f5f9 100%);
        min-height: 100vh;
    }

    [data-theme-mode="dark"] .sqt-take .main-content.app-content,
    [data-bs-theme="dark"] .sqt-take .main-content.app-content,
    [data-theme-mode="dark"] .sqt-take.main-content,
    [data-bs-theme="dark"] .sqt-take.main-content {
        background:
            radial-gradient(900px 360px at 100% 0%, rgba(16, 185, 129, 0.12), transparent 50%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%);
        --sqt-surface: #111a2e;
        --sqt-muted: #94a3b8;
        --sqt-border: rgba(255, 255, 255, 0.1);
        --sqt-soft: rgba(16, 185, 129, 0.12);
    }

    .sqt-take .sqt-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.85rem 1.25rem;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.15rem;
        border-radius: var(--sqt-radius);
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.16) 0%, rgba(14, 165, 233, 0.08) 100%);
        border: 1px solid rgba(13, 148, 136, 0.28);
        box-shadow: 0 10px 28px rgba(13, 148, 136, 0.08);
    }

    .sqt-take .sqt-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(13, 148, 136, 0.16);
        color: var(--sqt-accent);
        font-size: 1.45rem;
        flex-shrink: 0;
    }

    .sqt-take .sqt-hero__title {
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0 0 0.15rem;
        line-height: 1.35;
    }

    .sqt-take .sqt-hero__meta {
        margin: 0;
        color: var(--sqt-muted);
        font-size: 0.86rem;
        font-weight: 600;
    }

    .sqt-take .sqt-panel {
        border: 1px solid var(--sqt-border);
        border-radius: var(--sqt-radius);
        background: var(--sqt-surface);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .sqt-take .sqt-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1.15rem;
        background: var(--sqt-soft);
        border-bottom: 1px solid var(--sqt-border);
        font-weight: 800;
        font-size: 0.95rem;
    }

    .sqt-take .sqt-panel__body { padding: 1.15rem; }

    .sqt-take .sqt-stats {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .sqt-take .sqt-stats:has(#timer-card) {
        grid-template-columns: minmax(140px, 0.9fr) 1.1fr;
        align-items: stretch;
    }

    .sqt-take #timer-card {
        border: 1px solid var(--sqt-border);
        border-radius: 16px;
        background: var(--sqt-surface);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        height: 100%;
        margin-bottom: 0 !important;
    }

    .sqt-take #timer-card .card-body {
        padding: 0.75rem 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 0;
    }

    .sqt-take #timer-card .sqt-timer {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sqt-take #timer-card .sqt-timer__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: rgba(13, 148, 136, 0.14);
        color: var(--sqt-accent);
        font-size: 1.25rem;
    }

    .sqt-take #timer-display {
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        margin: 0;
        font-variant-numeric: tabular-nums;
        color: #0f766e;
    }

    .sqt-take #timer-card.warning {
        background: #fffbeb;
        border-color: #fcd34d;
    }

    .sqt-take #timer-card.warning .sqt-timer__icon {
        background: rgba(245, 158, 11, 0.18);
        color: #d97706;
    }

    .sqt-take #timer-card.warning #timer-display { color: #b45309; }

    .sqt-take #timer-card.danger {
        background: #fff1f2;
        border-color: #fda4af;
        animation: sqtPulse 1s infinite;
    }

    .sqt-take #timer-card.danger .sqt-timer__icon {
        background: rgba(225, 29, 72, 0.14);
        color: var(--sqt-danger);
    }

    .sqt-take #timer-card.danger #timer-display { color: #be123c; }

    @keyframes sqtPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.012); }
    }

    .sqt-take .sqt-progress-card {
        border: 1px solid var(--sqt-border);
        border-radius: 16px;
        background: var(--sqt-surface);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        padding: 0.95rem 1.15rem;
    }

    .sqt-take .sqt-progress-card .progress {
        height: 14px;
        border-radius: 999px;
        background: rgba(13, 148, 136, 0.12);
        overflow: hidden;
    }

    .sqt-take .sqt-progress-card .progress-bar,
    .sqt-take #progress-bar {
        background: linear-gradient(90deg, #0d9488, #10b981);
        border-radius: 999px;
        transition: width .35s ease;
    }

    .sqt-take #progress-text {
        font-weight: 800;
        color: #0f766e;
    }

    .sqt-take #question-card {
        border: 1px solid var(--sqt-border);
        border-radius: var(--sqt-radius);
        background: var(--sqt-surface);
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        animation: sqtCardIn .28s ease;
    }

    @keyframes sqtCardIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sqt-take #question-card > .card-header,
    .sqt-take #question-card .sqt-q-head {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 55%, #0891b2 100%) !important;
        color: #fff !important;
        border: 0;
        padding: 1rem 1.25rem;
    }

    .sqt-take #question-card .sqt-q-head h5,
    .sqt-take #question-card .card-header h5 {
        font-weight: 800;
        font-size: 1.05rem;
    }

    .sqt-take #question-card .badge.bg-light {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.28);
        font-weight: 700;
        border-radius: 999px;
        padding: 0.4rem 0.75rem;
    }

    .sqt-take #question-content {
        min-height: 140px;
        padding: 0.25rem 0.15rem;
        font-size: 1.08rem;
        line-height: 1.75;
        font-weight: 600;
    }

    .sqt-take #question-content .question-stem,
    .sqt-take #question-content .question-content-html {
        font-size: 1.12rem;
        font-weight: 700;
        line-height: 1.8;
        margin-bottom: 1.15rem;
        color: inherit;
    }

    /* قائمة الأسئلة */
    .sqt-take .sqt-side {
        border: 1px solid var(--sqt-border);
        border-radius: var(--sqt-radius);
        background: var(--sqt-surface);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: visible;
    }

    .sqt-take .sqt-side .card-header,
    .sqt-take .sqt-side__head,
    .sqt-take .sqt-side__toggle {
        background: linear-gradient(135deg, #0f766e, #0d9488) !important;
        color: #fff !important;
        border: 0;
        padding: 0.95rem 1.1rem;
        font-weight: 800;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        text-align: start;
        border-radius: 0;
    }

    .sqt-take .sqt-side__toggle {
        cursor: default;
        appearance: none;
        -webkit-appearance: none;
        box-shadow: none;
        outline: none;
        font: inherit;
        line-height: 1.3;
    }

    .sqt-take .sqt-side__toggle:focus-visible {
        outline: 2px solid rgba(255, 255, 255, 0.65);
        outline-offset: -4px;
    }

    .sqt-take .sqt-side__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.4rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.2);
        font-size: 0.8rem;
        font-weight: 800;
    }

    .sqt-take .sqt-side__chevron {
        transition: transform .2s ease;
        font-size: 1rem;
    }

    .sqt-take .sqt-side.is-collapsed .sqt-side__chevron {
        transform: rotate(-90deg);
    }

    .sqt-take .sqt-side__panel {
        display: block;
    }

    .sqt-take #questions-list {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .sqt-take .question-nav-btn,
    .sqt-take .question-nav-btn-compact {
        width: 100% !important;
        min-width: 0 !important;
        height: 2.65rem !important;
        min-height: 2.65rem !important;
        border-radius: 12px !important;
        font-weight: 800 !important;
        border-width: 2px !important;
        padding: 0 !important;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease !important;
    }

    .sqt-take .question-nav-btn:hover {
        transform: translateY(-2px);
    }

    .sqt-take .question-nav-btn.btn-outline-secondary {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    .sqt-take .question-nav-btn.btn-success,
    .sqt-take .question-nav-btn.answered {
        background: linear-gradient(180deg, #34d399, #059669) !important;
        border-color: #059669 !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .sqt-take .question-nav-btn.btn-primary,
    .sqt-take .question-nav-btn.active {
        background: linear-gradient(180deg, #22d3ee, #0d9488) !important;
        border-color: #0d9488 !important;
        color: #fff !important;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.22);
        transform: scale(1.04);
    }

    .sqt-take #submit-quiz-btn {
        border: 0;
        border-radius: 14px;
        font-weight: 800;
        min-height: 48px;
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        box-shadow: 0 8px 20px rgba(225, 29, 72, 0.28);
    }

    .sqt-take #submit-quiz-btn:hover {
        filter: brightness(1.05);
    }

    .sqt-take .sqt-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .sqt-take #prev-btn,
    .sqt-take #next-btn {
        border-radius: 14px !important;
        font-weight: 800 !important;
        min-height: 48px;
        min-width: 140px;
        padding-inline: 1.25rem !important;
        border-width: 2px !important;
    }

    .sqt-take #prev-btn {
        background: #fff;
        border-color: #cbd5e1 !important;
        color: #475569 !important;
    }

    .sqt-take #next-btn.btn-outline-primary,
    .sqt-take #next-btn.btn-primary {
        background: linear-gradient(135deg, #0d9488, #059669) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(13, 148, 136, 0.25);
    }

    .sqt-take #next-btn.btn-danger {
        background: linear-gradient(135deg, #f43f5e, #e11d48) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(225, 29, 72, 0.28);
    }

    /* MCQ داخل وضع الاختبار */
    .sqt-take .mcq-options-list { gap: 0.85rem; }

    .sqt-take .mcq-option-card {
        border-radius: 16px;
        border-width: 2px;
        padding: 1.05rem 1.2rem;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .sqt-take .mcq-option-card.is-interactive:hover:not(.is-selected):not(.is-correct):not(.is-wrong) {
        border-color: rgba(13, 148, 136, 0.45);
        background: rgba(13, 148, 136, 0.05);
        box-shadow: 0 8px 20px rgba(13, 148, 136, 0.1);
        transform: translateY(-1px);
    }

    .sqt-take .mcq-option-card.is-selected {
        border-color: #0d9488;
        background: rgba(13, 148, 136, 0.1);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14);
    }

    .sqt-take .mcq-option-card.is-selected .mcq-option-card__letter {
        background: #0d9488;
        color: #fff;
    }

    .sqt-take .mcq-option-card__letter {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1rem;
        background: #ecfdf5;
        color: #0f766e;
    }

    .sqt-take .option-item:hover {
        border-color: #0d9488 !important;
        background-color: rgba(13, 148, 136, 0.06);
    }

    .sqt-take .form-check.p-4 {
        border-radius: 16px !important;
        border-width: 2px !important;
    }

    .sqt-take .form-check.p-4:hover {
        border-color: #0d9488 !important;
        background: rgba(13, 148, 136, 0.05);
    }

    .sqt-take .drop-zone.drag-over,
    .sqt-take .drop-zone.border-primary {
        border-color: #0d9488 !important;
        background-color: rgba(13, 148, 136, 0.1) !important;
    }

    @media (max-width: 991.98px) {
        .sqt-take.main-content,
        .sqt-take .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .sqt-take .sqt-hero {
            padding: 0.75rem 0.9rem;
            margin-bottom: 0.75rem;
            gap: 0.65rem;
        }

        .sqt-take .sqt-hero__icon {
            width: 42px;
            height: 42px;
            font-size: 1.15rem;
            border-radius: 12px;
        }

        .sqt-take .sqt-hero__title { font-size: 1rem; }

        .sqt-take .sqt-hero__meta { font-size: 0.8rem; }

        .sqt-take #timer-card .sqt-timer__icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }

        .sqt-take #timer-display { font-size: 1.2rem; }

        .sqt-take .sqt-progress-card {
            padding: 0.7rem 0.85rem;
            border-radius: 14px;
        }

        .sqt-take .sqt-progress-card .progress { height: 10px; }

        .sqt-take #question-card .sqt-q-head,
        .sqt-take #question-card > .card-header {
            padding: 0.75rem 0.9rem;
        }

        .sqt-take #question-card .sqt-q-head h5,
        .sqt-take #question-card .card-header h5 {
            font-size: 0.95rem;
        }

        .sqt-take #question-content {
            min-height: 100px;
            font-size: 1rem;
        }

        .sqt-take .sqt-side.sticky-top {
            position: static !important;
            top: auto !important;
        }

        .sqt-take .sqt-side {
            overflow: hidden;
        }

        .sqt-take .sqt-side__toggle {
            cursor: pointer;
            border-radius: var(--sqt-radius) var(--sqt-radius) 0 0;
            min-height: 48px;
            -webkit-tap-highlight-color: transparent;
        }

        .sqt-take .sqt-side.is-collapsed .sqt-side__toggle {
            border-radius: var(--sqt-radius);
        }

        .sqt-take .sqt-side.is-collapsed .sqt-side__panel {
            display: none;
        }

        .sqt-take #questions-list {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.45rem;
        }

        .sqt-take .question-nav-btn,
        .sqt-take .question-nav-btn-compact {
            height: 2.45rem !important;
            min-height: 2.45rem !important;
            border-radius: 10px !important;
            font-size: 0.95rem;
        }

        .sqt-take .sqt-nav {
            flex-direction: row;
            gap: 0.55rem;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .sqt-take #prev-btn,
        .sqt-take #next-btn {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
            min-height: 44px;
            padding-inline: 0.65rem !important;
            font-size: 0.92rem;
        }

        .sqt-take #submit-quiz-btn {
            min-height: 46px;
            border-radius: 12px;
        }

        .sqt-take .mcq-option-card {
            padding: 0.85rem 0.95rem;
            border-radius: 14px;
        }
    }

    @media (max-width: 575.98px) {
        .sqt-take .sqt-stats:has(#timer-card) {
            grid-template-columns: 1fr 1fr;
            gap: 0.55rem;
        }

        .sqt-take #timer-card .card-body {
            padding: 0.65rem 0.55rem;
        }

        .sqt-take #timer-card .sqt-timer {
            gap: 0.4rem;
            flex-direction: column;
            text-align: center;
        }

        .sqt-take #timer-card .sqt-timer .text-start {
            text-align: center !important;
        }

        .sqt-take #timer-display { font-size: 1.1rem; }

        .sqt-take #questions-list {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .sqt-take .sqt-hero__icon { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .sqt-take #question-card,
        .sqt-take #timer-card.danger,
        .sqt-take .question-nav-btn,
        .sqt-take .mcq-option-card,
        .sqt-take #progress-bar {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>
