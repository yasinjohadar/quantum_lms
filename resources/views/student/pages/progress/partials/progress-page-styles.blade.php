<style>
    .student-progress-grid > [class*="col"] {
        animation: studentProgressCardReveal 0.45s ease backwards;
    }

    .student-progress-grid > [class*="col"]:nth-child(1) { animation-delay: 0.03s; }
    .student-progress-grid > [class*="col"]:nth-child(2) { animation-delay: 0.06s; }
    .student-progress-grid > [class*="col"]:nth-child(3) { animation-delay: 0.09s; }
    .student-progress-grid > [class*="col"]:nth-child(4) { animation-delay: 0.12s; }
    .student-progress-grid > [class*="col"]:nth-child(5) { animation-delay: 0.15s; }
    .student-progress-grid > [class*="col"]:nth-child(n+6) { animation-delay: 0.18s; }

    .student-progress-card {
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        color: var(--default-text-color, #0f172a);
    }

    .student-progress-card:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.22);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
    }

    .student-progress-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.05rem 0.85rem;
        border-bottom: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-progress-card__title-wrap {
        min-width: 0;
        flex: 1;
    }

    .student-progress-card__title {
        margin: 0 0 0.2rem;
        font-size: 0.98rem;
        font-weight: 700;
        line-height: 1.35;
        color: var(--default-text-color, #0f172a);
    }

    .student-progress-card__meta {
        font-size: 0.74rem;
        color: var(--text-muted, #64748b);
    }

    .student-progress-card__badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.75rem;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
    }

    .student-progress-card__body {
        padding: 0.95rem 1.05rem 1.05rem;
    }

    .student-progress-card__bar-wrap {
        margin-bottom: 0.9rem;
    }

    .student-progress-card__bar {
        height: 8px;
        border-radius: 999px;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        overflow: hidden;
    }

    .student-progress-card__bar .progress-bar {
        border-radius: 999px;
        background: linear-gradient(90deg, #4a7cff 0%, #2563eb 100%);
        transition: width 0.6s ease;
    }

    .student-progress-card__stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin-bottom: 0.95rem;
    }

    .student-progress-card__stat {
        text-align: center;
        padding: 0.55rem 0.35rem;
        border-radius: 10px;
        border: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);
        transition: transform 0.22s ease, background 0.22s ease;
    }

    .student-progress-card:hover .student-progress-card__stat {
        transform: translateY(-2px);
    }

    .student-progress-card__stat-value {
        display: block;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .student-progress-card__stat-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted, #64748b);
        margin-top: 0.1rem;
    }

    .student-progress-card__stat-total {
        display: block;
        font-size: 0.68rem;
        color: var(--text-muted, #94a3b8);
        margin-top: 0.05rem;
    }

    .student-progress-card__stat--lessons .student-progress-card__stat-value { color: #059669; }
    .student-progress-card__stat--quizzes .student-progress-card__stat-value { color: #0284c7; }
    .student-progress-card__stat--questions .student-progress-card__stat-value { color: #d97706; }

    .student-progress-card__stat--lessons { border-color: rgba(5, 150, 105, 0.15); background: rgba(5, 150, 105, 0.04); }
    .student-progress-card__stat--quizzes { border-color: rgba(2, 132, 199, 0.15); background: rgba(2, 132, 199, 0.04); }
    .student-progress-card__stat--questions { border-color: rgba(217, 119, 6, 0.15); background: rgba(217, 119, 6, 0.04); }

    .student-progress-card__btn {
        border-radius: 10px;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .student-progress-card:hover .student-progress-card__btn {
        box-shadow: 0 6px 16px rgba(var(--primary-rgb, 13, 110, 253), 0.25);
    }

    .student-progress-empty {
        border-radius: 14px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-progress-empty__icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    @keyframes studentProgressCardReveal {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    [data-theme-mode="dark"] .student-progress-card,
    [data-bs-theme="dark"] .student-progress-card {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-progress-card:hover,
    [data-bs-theme="dark"] .student-progress-card:hover {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.35);
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.42);
    }

    [data-theme-mode="dark"] .student-progress-card__header,
    [data-bs-theme="dark"] .student-progress-card__header {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.14) 0%, rgba(28, 31, 40, 0.95) 100%);
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-progress-card__title,
    [data-bs-theme="dark"] .student-progress-card__title {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-progress-card__meta,
    [data-bs-theme="dark"] .student-progress-card__meta,
    [data-theme-mode="dark"] .student-progress-card__stat-label,
    [data-bs-theme="dark"] .student-progress-card__stat-label,
    [data-theme-mode="dark"] .student-progress-card__stat-total,
    [data-bs-theme="dark"] .student-progress-card__stat-total {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-progress-card__badge,
    [data-bs-theme="dark"] .student-progress-card__badge {
        color: #93c5fd;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.18);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.28);
    }

    [data-theme-mode="dark"] .student-progress-card__bar,
    [data-bs-theme="dark"] .student-progress-card__bar {
        background: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-progress-card__stat,
    [data-bs-theme="dark"] .student-progress-card__stat {
        border-color: rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
    }

    [data-theme-mode="dark"] .student-progress-card__stat--lessons .student-progress-card__stat-value,
    [data-bs-theme="dark"] .student-progress-card__stat--lessons .student-progress-card__stat-value {
        color: #6ee7b7;
    }

    [data-theme-mode="dark"] .student-progress-card__stat--quizzes .student-progress-card__stat-value,
    [data-bs-theme="dark"] .student-progress-card__stat--quizzes .student-progress-card__stat-value {
        color: #7dd3fc;
    }

    [data-theme-mode="dark"] .student-progress-card__stat--questions .student-progress-card__stat-value,
    [data-bs-theme="dark"] .student-progress-card__stat--questions .student-progress-card__stat-value {
        color: #fcd34d;
    }

    [data-theme-mode="dark"] .student-progress-card__stat--lessons,
    [data-bs-theme="dark"] .student-progress-card__stat--lessons {
        border-color: rgba(110, 231, 183, 0.2);
        background: rgba(5, 150, 105, 0.1);
    }

    [data-theme-mode="dark"] .student-progress-card__stat--quizzes,
    [data-bs-theme="dark"] .student-progress-card__stat--quizzes {
        border-color: rgba(125, 211, 252, 0.2);
        background: rgba(2, 132, 199, 0.1);
    }

    [data-theme-mode="dark"] .student-progress-card__stat--questions,
    [data-bs-theme="dark"] .student-progress-card__stat--questions {
        border-color: rgba(252, 211, 77, 0.2);
        background: rgba(217, 119, 6, 0.1);
    }

    [data-theme-mode="dark"] .student-progress-empty,
    [data-bs-theme="dark"] .student-progress-empty {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, transparent 100%);
        border-color: rgba(255, 255, 255, 0.12);
    }

    @media (max-width: 767.98px) {
        .student-progress-card__header {
            padding: 0.75rem 0.8rem 0.65rem;
        }

        .student-progress-card__body {
            padding: 0.75rem 0.8rem 0.85rem;
        }

        .student-progress-card__title {
            font-size: 0.88rem;
        }

        .student-progress-card__meta {
            font-size: 0.68rem;
        }

        .student-progress-card__stat {
            padding: 0.4rem 0.2rem;
        }

        .student-progress-card__stat-value {
            font-size: 0.88rem;
        }

        .student-progress-card__stat-label {
            font-size: 0.65rem;
        }

        .student-progress-card__stat-total {
            font-size: 0.6rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-progress-grid > [class*="col"],
        .student-progress-card,
        .student-progress-card:hover,
        .student-progress-card__stat,
        .student-progress-card__btn,
        .student-progress-section-row,
        .student-progress-section-row:hover {
            animation: none;
            transition: none;
            transform: none;
        }
    }

    /* ── Subject detail page ── */
    .student-progress-overview {
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .student-progress-overview__head {
        padding: 1rem 1.15rem 0.85rem;
        border-bottom: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 0%, transparent 100%);
    }

    .student-progress-overview__body {
        padding: 1rem 1.15rem 1.1rem;
    }

    .student-progress-overview__percent {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        margin-bottom: 0.65rem;
    }

    .student-progress-overview__bar {
        height: 10px;
        border-radius: 999px;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .student-progress-overview__bar .progress-bar {
        border-radius: 999px;
        background: linear-gradient(90deg, #4a7cff 0%, #2563eb 100%);
    }

    .student-progress-overview__stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
    }

    .student-progress-overview__stats--three {
        grid-template-columns: repeat(3, 1fr);
    }

    .student-progress-overview--section .student-progress-overview__hero {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.85rem;
    }

    .student-progress-overview--section .student-progress-overview__percent {
        margin-bottom: 0;
        flex-shrink: 0;
        min-width: 4.5rem;
    }

    .student-progress-overview--section .student-progress-overview__bar {
        margin-bottom: 0;
    }

    .student-progress-detail-stat {
        padding: 0.65rem 0.5rem 0.55rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .student-progress-detail-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07);
    }

    .student-progress-detail-stat__pct {
        display: block;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .student-progress-detail-stat__count {
        display: block;
        font-size: 0.68rem;
        color: var(--text-muted, #94a3b8);
        margin-top: 0.1rem;
        margin-bottom: 0.4rem;
    }

    .student-progress-detail-stat__bar {
        height: 5px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .student-progress-detail-stat__bar .progress-bar {
        border-radius: 999px;
    }

    .student-progress-card__stat--lessons .student-progress-detail-stat__pct { color: #059669; }
    .student-progress-card__stat--quizzes .student-progress-detail-stat__pct { color: #0284c7; }
    .student-progress-card__stat--questions .student-progress-detail-stat__pct { color: #d97706; }

    [data-theme-mode="dark"] .student-progress-detail-stat__count,
    [data-bs-theme="dark"] .student-progress-detail-stat__count {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-progress-detail-stat__bar,
    [data-bs-theme="dark"] .student-progress-detail-stat__bar {
        background: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-progress-card__stat--lessons .student-progress-detail-stat__pct,
    [data-bs-theme="dark"] .student-progress-card__stat--lessons .student-progress-detail-stat__pct { color: #6ee7b7; }
    [data-theme-mode="dark"] .student-progress-card__stat--quizzes .student-progress-detail-stat__pct,
    [data-bs-theme="dark"] .student-progress-card__stat--quizzes .student-progress-detail-stat__pct { color: #7dd3fc; }
    [data-theme-mode="dark"] .student-progress-card__stat--questions .student-progress-detail-stat__pct,
    [data-bs-theme="dark"] .student-progress-card__stat--questions .student-progress-detail-stat__pct { color: #fcd34d; }

    [data-theme-mode="dark"] .student-progress-detail-stat:hover,
    [data-bs-theme="dark"] .student-progress-detail-stat:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    .student-progress-overview__attendance {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--default-border);
        font-size: 0.82rem;
        color: var(--text-muted, #64748b);
    }

    .student-progress-sections-panel {
        border-radius: 14px;
    }

    .student-progress-section-row {
        display: block;
        padding: 0.95rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        margin-bottom: 0.65rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        animation: studentProgressCardReveal 0.4s ease backwards;
    }

    .student-progress-section-row:last-child {
        margin-bottom: 0;
    }

    .student-progress-section-row:hover {
        transform: translateX(-3px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.22);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .student-progress-section-row__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.55rem;
    }

    .student-progress-section-row__title {
        margin: 0 0 0.15rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-progress-section-row__desc {
        margin: 0;
        font-size: 0.76rem;
        color: var(--text-muted, #64748b);
    }

    .student-progress-section-row__percent {
        font-size: 1.15rem;
        font-weight: 800;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        line-height: 1;
    }

    .student-progress-section-row__percent-label {
        display: block;
        font-size: 0.68rem;
        color: var(--text-muted, #94a3b8);
        margin-top: 0.15rem;
    }

    .student-progress-section-row__bar {
        height: 7px;
        border-radius: 999px;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .student-progress-section-row__bar .progress-bar {
        border-radius: 999px;
        background: linear-gradient(90deg, #38bdf8 0%, #0284c7 100%);
    }

    .student-progress-section-row__footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .student-progress-section-row__metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem 1rem;
        font-size: 0.76rem;
        color: var(--text-muted, #64748b);
    }

    .student-progress-section-row__metrics strong {
        color: var(--default-text-color, #0f172a);
    }

    [data-theme-mode="dark"] .student-progress-overview,
    [data-bs-theme="dark"] .student-progress-overview,
    [data-theme-mode="dark"] .student-progress-section-row,
    [data-bs-theme="dark"] .student-progress-section-row {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-progress-overview__head,
    [data-bs-theme="dark"] .student-progress-overview__head {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.14) 0%, rgba(28, 31, 40, 0.95) 100%);
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-progress-overview__percent,
    [data-bs-theme="dark"] .student-progress-overview__percent,
    [data-theme-mode="dark"] .student-progress-section-row__percent,
    [data-bs-theme="dark"] .student-progress-section-row__percent {
        color: #93c5fd;
    }

    [data-theme-mode="dark"] .student-progress-section-row__title,
    [data-bs-theme="dark"] .student-progress-section-row__title,
    [data-theme-mode="dark"] .student-progress-section-row__metrics strong,
    [data-bs-theme="dark"] .student-progress-section-row__metrics strong {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-progress-section-row__desc,
    [data-bs-theme="dark"] .student-progress-section-row__desc,
    [data-theme-mode="dark"] .student-progress-overview__attendance,
    [data-bs-theme="dark"] .student-progress-overview__attendance,
    [data-theme-mode="dark"] .student-progress-section-row__metrics,
    [data-bs-theme="dark"] .student-progress-section-row__metrics {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-progress-overview__bar,
    [data-bs-theme="dark"] .student-progress-overview__bar,
    [data-theme-mode="dark"] .student-progress-section-row__bar,
    [data-bs-theme="dark"] .student-progress-section-row__bar {
        background: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-progress-section-row:hover,
    [data-bs-theme="dark"] .student-progress-section-row:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
    }

    @media (max-width: 767.98px) {
        .student-progress-overview__stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .student-progress-overview__percent {
            font-size: 1.65rem;
        }

        .student-progress-section-row__footer {
            flex-direction: column;
            align-items: stretch;
        }

        .student-progress-section-row__footer .btn {
            width: 100%;
        }

        .student-progress-type-grid {
            grid-template-columns: 1fr;
        }

        .student-progress-overview--section .student-progress-overview__hero {
            flex-direction: column;
            align-items: stretch;
            gap: 0.45rem;
        }

        .student-progress-overview--section .student-progress-overview__percent {
            font-size: 1.5rem;
            min-width: 0;
        }

        .student-progress-overview--section .student-progress-overview__body {
            padding: 0.75rem 0.85rem 0.85rem;
        }

        .student-progress-overview--section .student-progress-overview__head {
            padding: 0.75rem 0.85rem 0.65rem;
        }

        .student-progress-overview--section .student-progress-overview__head h5 {
            font-size: 0.88rem;
        }

        .student-progress-overview__stats--three {
            grid-template-columns: repeat(3, 1fr);
            gap: 0.35rem;
        }

        .student-progress-detail-stat {
            padding: 0.45rem 0.3rem 0.4rem;
        }

        .student-progress-detail-stat__pct {
            font-size: 0.85rem;
        }

        .student-progress-detail-stat__count {
            font-size: 0.58rem;
            margin-bottom: 0.3rem;
        }

        .student-progress-detail-stat .student-progress-card__stat-label {
            font-size: 0.62rem;
        }
    }

    .student-progress-type-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-progress-type-card {
        text-align: center;
        padding: 1rem 0.75rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .student-progress-type-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    .student-progress-type-card__label {
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .student-progress-type-card__percent {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 0.25rem;
    }

    .student-progress-type-card__count {
        font-size: 0.78rem;
        color: var(--text-muted, #64748b);
        margin: 0;
    }

    .student-progress-type-card--lessons .student-progress-type-card__label,
    .student-progress-type-card--lessons .student-progress-type-card__percent { color: #059669; }
    .student-progress-type-card--quizzes .student-progress-type-card__label,
    .student-progress-type-card--quizzes .student-progress-type-card__percent { color: #0284c7; }
    .student-progress-type-card--questions .student-progress-type-card__label,
    .student-progress-type-card--questions .student-progress-type-card__percent { color: #d97706; }

    .student-progress-type-card--lessons { border-color: rgba(5, 150, 105, 0.15); background: rgba(5, 150, 105, 0.04); }
    .student-progress-type-card--quizzes { border-color: rgba(2, 132, 199, 0.15); background: rgba(2, 132, 199, 0.04); }
    .student-progress-type-card--questions { border-color: rgba(217, 119, 6, 0.15); background: rgba(217, 119, 6, 0.04); }

    [data-theme-mode="dark"] .student-progress-type-card,
    [data-bs-theme="dark"] .student-progress-type-card {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }

    [data-theme-mode="dark"] .student-progress-type-card--lessons .student-progress-type-card__label,
    [data-bs-theme="dark"] .student-progress-type-card--lessons .student-progress-type-card__label,
    [data-theme-mode="dark"] .student-progress-type-card--lessons .student-progress-type-card__percent,
    [data-bs-theme="dark"] .student-progress-type-card--lessons .student-progress-type-card__percent { color: #6ee7b7; }

    [data-theme-mode="dark"] .student-progress-type-card--quizzes .student-progress-type-card__label,
    [data-bs-theme="dark"] .student-progress-type-card--quizzes .student-progress-type-card__label,
    [data-theme-mode="dark"] .student-progress-type-card--quizzes .student-progress-type-card__percent,
    [data-bs-theme="dark"] .student-progress-type-card--quizzes .student-progress-type-card__percent { color: #7dd3fc; }

    [data-theme-mode="dark"] .student-progress-type-card--questions .student-progress-type-card__label,
    [data-bs-theme="dark"] .student-progress-type-card--questions .student-progress-type-card__label,
    [data-theme-mode="dark"] .student-progress-type-card--questions .student-progress-type-card__percent,
    [data-bs-theme="dark"] .student-progress-type-card--questions .student-progress-type-card__percent { color: #fcd34d; }

    [data-theme-mode="dark"] .student-progress-type-card--lessons,
    [data-bs-theme="dark"] .student-progress-type-card--lessons { background: rgba(5, 150, 105, 0.1); border-color: rgba(110, 231, 183, 0.2); }
    [data-theme-mode="dark"] .student-progress-type-card--quizzes,
    [data-bs-theme="dark"] .student-progress-type-card--quizzes { background: rgba(2, 132, 199, 0.1); border-color: rgba(125, 211, 252, 0.2); }
    [data-theme-mode="dark"] .student-progress-type-card--questions,
    [data-bs-theme="dark"] .student-progress-type-card--questions { background: rgba(217, 119, 6, 0.1); border-color: rgba(252, 211, 77, 0.2); }

    [data-theme-mode="dark"] .student-progress-type-card__count,
    [data-bs-theme="dark"] .student-progress-type-card__count {
        color: #94a3b8;
    }
</style>
