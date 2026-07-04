<style>
    .student-quizzes-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-quizzes-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
    }

    .student-quizzes-filters .form-select {
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        min-width: 9rem;
    }

    .student-quizzes-grid > [class*="col"] {
        animation: studentQuizCardReveal 0.45s ease backwards;
    }

    .student-quizzes-grid > [class*="col"]:nth-child(1) { animation-delay: 0.03s; }
    .student-quizzes-grid > [class*="col"]:nth-child(2) { animation-delay: 0.06s; }
    .student-quizzes-grid > [class*="col"]:nth-child(3) { animation-delay: 0.09s; }
    .student-quizzes-grid > [class*="col"]:nth-child(4) { animation-delay: 0.12s; }
    .student-quizzes-grid > [class*="col"]:nth-child(5) { animation-delay: 0.15s; }
    .student-quizzes-grid > [class*="col"]:nth-child(n+6) { animation-delay: 0.18s; }

    .student-quiz-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        color: var(--default-text-color, #0f172a);
    }

    .student-quiz-card:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
    }

    .student-quiz-card__head {
        position: relative;
        padding: 0.95rem 1rem 0.85rem;
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.02) 100%);
        border-bottom: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.08);
    }

    .student-quiz-card__head-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.45rem;
    }

    .student-quiz-card__icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }

    .student-quiz-card:hover .student-quiz-card__icon {
        transform: scale(1.06);
    }

    .student-quiz-card__status {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .student-quiz-card__status--available {
        color: #059669;
        background: rgba(5, 150, 105, 0.12);
        border: 1px solid rgba(5, 150, 105, 0.2);
    }

    .student-quiz-card__status--locked {
        color: #64748b;
        background: rgba(100, 116, 139, 0.1);
        border: 1px solid rgba(100, 116, 139, 0.18);
    }

    .student-quiz-card__status--attempts {
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
    }

    .student-quiz-card__title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.4;
        color: var(--default-text-color, #0f172a);
    }

    .student-quiz-card__title a {
        color: inherit;
        text-decoration: none;
    }

    .student-quiz-card__title a:hover {
        color: rgb(var(--primary-rgb, 13, 110, 253));
    }

    .student-quiz-card__desc {
        margin: 0;
        font-size: 0.78rem;
        color: var(--text-muted, #64748b);
        line-height: 1.45;
    }

    .student-quiz-card__body {
        padding: 0.9rem 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .student-quiz-card__meta {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        font-size: 0.76rem;
        color: var(--text-muted, #64748b);
    }

    .student-quiz-card__meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .student-quiz-card__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .student-quiz-card__chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.22rem 0.5rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.03);
    }

    .student-quiz-card__chip--time { color: #0284c7; border-color: rgba(2, 132, 199, 0.15); background: rgba(2, 132, 199, 0.05); }
    .student-quiz-card__chip--points { color: #d97706; border-color: rgba(217, 119, 6, 0.15); background: rgba(217, 119, 6, 0.05); }
    .student-quiz-card__chip--attempts { color: #7c3aed; border-color: rgba(124, 58, 237, 0.15); background: rgba(124, 58, 237, 0.05); }

    .student-quiz-card__dates {
        font-size: 0.72rem;
        color: var(--text-muted, #94a3b8);
    }

    .student-quiz-card__last {
        padding: 0.55rem 0.65rem;
        border-radius: 10px;
        font-size: 0.74rem;
        color: #0369a1;
        background: rgba(14, 165, 233, 0.08);
        border: 1px solid rgba(14, 165, 233, 0.15);
    }

    .student-quiz-card__footer {
        display: flex;
        gap: 0.45rem;
        margin-top: auto;
        padding-top: 0.15rem;
    }

    .student-quiz-card__footer .btn {
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .student-quizzes-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        border-radius: 14px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-quizzes-empty__icon {
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

    @keyframes studentQuizCardReveal {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    [data-theme-mode="dark"] .student-quiz-card,
    [data-bs-theme="dark"] .student-quiz-card {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-quiz-card:hover,
    [data-bs-theme="dark"] .student-quiz-card:hover {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.35);
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.42);
    }

    [data-theme-mode="dark"] .student-quiz-card__head,
    [data-bs-theme="dark"] .student-quiz-card__head {
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.18) 0%, rgba(28, 31, 40, 0.95) 100%);
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-quiz-card__title,
    [data-bs-theme="dark"] .student-quiz-card__title,
    [data-theme-mode="dark"] .student-quiz-card__title a,
    [data-bs-theme="dark"] .student-quiz-card__title a {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-quiz-card__title a:hover,
    [data-bs-theme="dark"] .student-quiz-card__title a:hover {
        color: #93c5fd;
    }

    [data-theme-mode="dark"] .student-quiz-card__icon,
    [data-bs-theme="dark"] .student-quiz-card__icon {
        color: #93c5fd;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.2);
    }

    [data-theme-mode="dark"] .student-quiz-card__meta,
    [data-bs-theme="dark"] .student-quiz-card__meta,
    [data-theme-mode="dark"] .student-quiz-card__desc,
    [data-bs-theme="dark"] .student-quiz-card__desc,
    [data-theme-mode="dark"] .student-quiz-card__dates,
    [data-bs-theme="dark"] .student-quiz-card__dates {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-quiz-card__status--available,
    [data-bs-theme="dark"] .student-quiz-card__status--available {
        color: #6ee7b7;
        background: rgba(5, 150, 105, 0.18);
        border-color: rgba(5, 150, 105, 0.3);
    }

    [data-theme-mode="dark"] .student-quiz-card__status--locked,
    [data-bs-theme="dark"] .student-quiz-card__status--locked {
        color: #cbd5e1;
        background: rgba(148, 163, 184, 0.12);
        border-color: rgba(148, 163, 184, 0.22);
    }

    [data-theme-mode="dark"] .student-quiz-card__status--attempts,
    [data-bs-theme="dark"] .student-quiz-card__status--attempts {
        color: #93c5fd;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.18);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.28);
    }

    [data-theme-mode="dark"] .student-quiz-card__chip,
    [data-bs-theme="dark"] .student-quiz-card__chip {
        border-color: rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
    }

    [data-theme-mode="dark"] .student-quiz-card__chip--time,
    [data-bs-theme="dark"] .student-quiz-card__chip--time {
        color: #7dd3fc;
        border-color: rgba(125, 211, 252, 0.22);
        background: rgba(14, 165, 233, 0.12);
    }

    [data-theme-mode="dark"] .student-quiz-card__chip--points,
    [data-bs-theme="dark"] .student-quiz-card__chip--points {
        color: #fcd34d;
        border-color: rgba(252, 211, 77, 0.22);
        background: rgba(245, 158, 11, 0.12);
    }

    [data-theme-mode="dark"] .student-quiz-card__chip--attempts,
    [data-bs-theme="dark"] .student-quiz-card__chip--attempts {
        color: #c4b5fd;
        border-color: rgba(196, 181, 253, 0.22);
        background: rgba(124, 58, 237, 0.12);
    }

    [data-theme-mode="dark"] .student-quiz-card__last,
    [data-bs-theme="dark"] .student-quiz-card__last {
        color: #7dd3fc;
        background: rgba(14, 165, 233, 0.12);
        border-color: rgba(14, 165, 233, 0.22);
    }

    [data-theme-mode="dark"] .student-quizzes-empty,
    [data-bs-theme="dark"] .student-quizzes-empty {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, transparent 100%);
        border-color: rgba(255, 255, 255, 0.12);
    }

    @media (max-width: 767.98px) {
        .student-quizzes-filters {
            width: 100%;
        }

        .student-quizzes-filters .form-select {
            flex: 1;
            min-width: 0;
        }

        .student-quiz-card__title {
            font-size: 0.88rem;
        }

        .student-quiz-card__footer {
            flex-direction: column;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-quiz-card,
        .student-quiz-card:hover,
        .student-quiz-card__icon,
        .student-quizzes-grid > [class*="col"],
        .student-quiz-result-row,
        .student-quiz-result-row:hover {
            animation: none;
            transition: none;
            transform: none;
        }
    }

    /* ── Quiz results list ── */
    .student-quizzes-filters--results {
        width: 100%;
    }

    .student-quizzes-filter-field {
        flex: 1;
        min-width: 8rem;
    }

    .student-quizzes-filter-field__label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.25rem;
    }

    .student-quiz-results-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .student-quiz-result-row {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        animation: studentQuizCardReveal 0.4s ease backwards;
    }

    .student-quiz-result-row:hover {
        transform: translateX(-3px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.22);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .student-quiz-result-row__score {
        flex-shrink: 0;
        width: 4.5rem;
        text-align: center;
        padding: 0.55rem 0.35rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
    }

    .student-quiz-result-row__score--passed {
        color: #059669;
        background: rgba(5, 150, 105, 0.08);
        border-color: rgba(5, 150, 105, 0.18);
    }

    .student-quiz-result-row__score--failed {
        color: #dc2626;
        background: rgba(220, 38, 38, 0.06);
        border-color: rgba(220, 38, 38, 0.15);
    }

    .student-quiz-result-row__score--pending {
        color: #64748b;
        background: rgba(100, 116, 139, 0.08);
        border-color: rgba(100, 116, 139, 0.15);
    }

    .student-quiz-result-row__score-value {
        display: block;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .student-quiz-result-row__score-sub {
        display: block;
        font-size: 0.62rem;
        font-weight: 600;
        opacity: 0.85;
        margin-top: 0.1rem;
    }

    .student-quiz-result-row__main {
        flex: 1;
        min-width: 0;
    }

    .student-quiz-result-row__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.35rem 0.65rem;
        margin-bottom: 0.35rem;
    }

    .student-quiz-result-row__title {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
        line-height: 1.35;
    }

    .student-quiz-result-row__attempt {
        display: inline-block;
        margin-right: 0.35rem;
        padding: 0.1rem 0.4rem;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        background: rgba(100, 116, 139, 0.1);
        vertical-align: middle;
    }

    .student-quiz-result-row__badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }

    .student-quiz-result-row__badge {
        display: inline-flex;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
    }

    .student-quiz-result-row__badge--info { color: #0284c7; background: rgba(2, 132, 199, 0.1); }
    .student-quiz-result-row__badge--success { color: #059669; background: rgba(5, 150, 105, 0.1); }
    .student-quiz-result-row__badge--warning { color: #d97706; background: rgba(217, 119, 6, 0.1); }
    .student-quiz-result-row__badge--passed { color: #059669; background: rgba(5, 150, 105, 0.12); border: 1px solid rgba(5, 150, 105, 0.2); }
    .student-quiz-result-row__badge--failed { color: #dc2626; background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.15); }

    .student-quiz-result-row__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.85rem;
        font-size: 0.74rem;
        color: var(--text-muted, #64748b);
    }

    .student-quiz-result-row__action {
        flex-shrink: 0;
    }

    .student-quiz-result-row__action .btn {
        border-radius: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    [data-theme-mode="dark"] .student-quiz-result-row,
    [data-bs-theme="dark"] .student-quiz-result-row {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-quiz-result-row:hover,
    [data-bs-theme="dark"] .student-quiz-result-row:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
    }

    [data-theme-mode="dark"] .student-quiz-result-row__title,
    [data-bs-theme="dark"] .student-quiz-result-row__title {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-quiz-result-row__meta,
    [data-bs-theme="dark"] .student-quiz-result-row__meta {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-quiz-result-row__attempt,
    [data-bs-theme="dark"] .student-quiz-result-row__attempt {
        color: #cbd5e1;
        background: rgba(148, 163, 184, 0.12);
    }

    [data-theme-mode="dark"] .student-quiz-result-row__score--passed,
    [data-bs-theme="dark"] .student-quiz-result-row__score--passed {
        color: #6ee7b7;
        background: rgba(5, 150, 105, 0.12);
        border-color: rgba(110, 231, 183, 0.2);
    }

    [data-theme-mode="dark"] .student-quiz-result-row__score--failed,
    [data-bs-theme="dark"] .student-quiz-result-row__score--failed {
        color: #fca5a5;
        background: rgba(220, 38, 38, 0.12);
        border-color: rgba(252, 165, 165, 0.2);
    }

    [data-theme-mode="dark"] .student-quiz-result-row__score--pending,
    [data-bs-theme="dark"] .student-quiz-result-row__score--pending {
        color: #cbd5e1;
        background: rgba(148, 163, 184, 0.1);
        border-color: rgba(148, 163, 184, 0.2);
    }

    [data-theme-mode="dark"] .student-quiz-result-row__badge--info,
    [data-bs-theme="dark"] .student-quiz-result-row__badge--info { color: #7dd3fc; background: rgba(14, 165, 233, 0.12); }
    [data-theme-mode="dark"] .student-quiz-result-row__badge--success,
    [data-bs-theme="dark"] .student-quiz-result-row__badge--success { color: #6ee7b7; background: rgba(5, 150, 105, 0.12); }
    [data-theme-mode="dark"] .student-quiz-result-row__badge--warning,
    [data-bs-theme="dark"] .student-quiz-result-row__badge--warning { color: #fcd34d; background: rgba(245, 158, 11, 0.12); }
    [data-theme-mode="dark"] .student-quiz-result-row__badge--passed,
    [data-bs-theme="dark"] .student-quiz-result-row__badge--passed { color: #6ee7b7; border-color: rgba(110, 231, 183, 0.25); }
    [data-theme-mode="dark"] .student-quiz-result-row__badge--failed,
    [data-bs-theme="dark"] .student-quiz-result-row__badge--failed { color: #fca5a5; border-color: rgba(252, 165, 165, 0.25); }

    [data-theme-mode="dark"] .student-quizzes-filter-field__label,
    [data-bs-theme="dark"] .student-quizzes-filter-field__label {
        color: #94a3b8;
    }

    @media (max-width: 767.98px) {
        .student-quizzes-filters--results {
            flex-direction: column;
        }

        .student-quizzes-filter-field {
            width: 100%;
            min-width: 0;
        }

        .student-quiz-result-row {
            flex-wrap: wrap;
            padding: 0.75rem 0.85rem;
            gap: 0.65rem;
        }

        .student-quiz-result-row__score {
            width: 3.75rem;
            padding: 0.4rem 0.25rem;
        }

        .student-quiz-result-row__score-value {
            font-size: 0.85rem;
        }

        .student-quiz-result-row__title {
            font-size: 0.85rem;
        }

        .student-quiz-result-row__meta {
            font-size: 0.68rem;
            gap: 0.25rem 0.55rem;
        }

        .student-quiz-result-row__action {
            width: 100%;
        }

        .student-quiz-result-row__action .btn {
            width: 100%;
        }
    }
</style>
