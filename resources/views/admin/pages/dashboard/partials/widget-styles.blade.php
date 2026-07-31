<style>
    /* ── Stat cards ── */
    .dashboard-stat-card {
        position: relative;
        overflow: hidden;
        border-radius: 14px;
        border: none;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .dashboard-stat-card::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        inset-inline-start: -30px;
        inset-block-end: -40px;
        pointer-events: none;
    }
    .dashboard-stat-card:hover {
        transform: translateY(-3px);
    }
    .dashboard-stat-card__body {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1.1rem 1.15rem;
        position: relative;
        z-index: 1;
    }
    .dashboard-stat-card__content {
        flex: 1;
        min-width: 0;
    }
    .dashboard-stat-card__label {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.65rem;
        opacity: 0.92;
    }
    .dashboard-stat-card__value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.2rem;
    }
    .dashboard-stat-card__meta {
        font-size: 0.75rem;
        margin-bottom: 0;
        opacity: 0.85;
    }
    .dashboard-stat-card__icon {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    /* Light mode — vivid gradients */
    .dashboard-stat-card--students {
        background: linear-gradient(135deg, #4a7cff 0%, #2563eb 55%, #1d4ed8 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.28);
    }
    .dashboard-stat-card--classes {
        background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 55%, #7c3aed 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(139, 92, 246, 0.28);
    }
    .dashboard-stat-card--subjects {
        background: linear-gradient(135deg, #34d399 0%, #10b981 55%, #059669 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.28);
    }
    .dashboard-stat-card--quizzes {
        background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 55%, #0284c7 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.28);
    }
    .dashboard-stat-card--enrollments {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 55%, #d97706 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.28);
    }
    .dashboard-stat-card--students:hover { box-shadow: 0 12px 28px rgba(37, 99, 235, 0.38); }
    .dashboard-stat-card--classes:hover { box-shadow: 0 12px 28px rgba(139, 92, 246, 0.38); }
    .dashboard-stat-card--subjects:hover { box-shadow: 0 12px 28px rgba(16, 185, 129, 0.38); }
    .dashboard-stat-card--quizzes:hover { box-shadow: 0 12px 28px rgba(14, 165, 233, 0.38); }
    .dashboard-stat-card--enrollments:hover { box-shadow: 0 12px 28px rgba(245, 158, 11, 0.38); }

    /* Dark mode — tinted glass */
    [data-theme-mode="dark"] .dashboard-stat-card {
        background: var(--custom-card-bg, var(--default-background));
        color: var(--default-text-color);
        border: 1px solid var(--default-border);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
    }
    [data-theme-mode="dark"] .dashboard-stat-card::after {
        background: rgba(255, 255, 255, 0.04);
        inset-inline-start: auto;
        inset-inline-end: -20px;
        inset-block-end: -30px;
        width: 100px;
        height: 100px;
    }
    [data-theme-mode="dark"] .dashboard-stat-card--students {
        border-color: rgba(74, 124, 255, 0.35);
        background: linear-gradient(145deg, rgba(74, 124, 255, 0.18) 0%, rgba(37, 99, 235, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--classes {
        border-color: rgba(167, 139, 250, 0.35);
        background: linear-gradient(145deg, rgba(167, 139, 250, 0.18) 0%, rgba(139, 92, 246, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--subjects {
        border-color: rgba(52, 211, 153, 0.35);
        background: linear-gradient(145deg, rgba(52, 211, 153, 0.18) 0%, rgba(16, 185, 129, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--quizzes {
        border-color: rgba(56, 189, 248, 0.35);
        background: linear-gradient(145deg, rgba(56, 189, 248, 0.18) 0%, rgba(14, 165, 233, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--enrollments {
        border-color: rgba(251, 191, 36, 0.35);
        background: linear-gradient(145deg, rgba(251, 191, 36, 0.18) 0%, rgba(245, 158, 11, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--students .dashboard-stat-card__value { color: #7ba3ff; }
    [data-theme-mode="dark"] .dashboard-stat-card--classes .dashboard-stat-card__value { color: #c4b5fd; }
    [data-theme-mode="dark"] .dashboard-stat-card--subjects .dashboard-stat-card__value { color: #6ee7b7; }
    [data-theme-mode="dark"] .dashboard-stat-card--quizzes .dashboard-stat-card__value { color: #7dd3fc; }
    [data-theme-mode="dark"] .dashboard-stat-card--enrollments .dashboard-stat-card__value { color: #fcd34d; }
    [data-theme-mode="dark"] .dashboard-stat-card__icon {
        background: rgba(255, 255, 255, 0.08);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--students .dashboard-stat-card__icon { color: #7ba3ff; }
    [data-theme-mode="dark"] .dashboard-stat-card--classes .dashboard-stat-card__icon { color: #c4b5fd; }
    [data-theme-mode="dark"] .dashboard-stat-card--subjects .dashboard-stat-card__icon { color: #6ee7b7; }
    [data-theme-mode="dark"] .dashboard-stat-card--quizzes .dashboard-stat-card__icon { color: #7dd3fc; }
    [data-theme-mode="dark"] .dashboard-stat-card--enrollments .dashboard-stat-card__icon { color: #fcd34d; }
    [data-theme-mode="dark"] .dashboard-stat-card:hover {
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
    }

    /* ── Shortcut cards ── */
    .dashboard-shortcut {
        display: block;
        background: var(--custom-card-bg, var(--default-background));
        border: 1px solid var(--default-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .dashboard-shortcut__body {
        text-align: center;
        padding: 1rem 0.75rem;
    }
    .dashboard-shortcut__icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 0.65rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: transform 0.25s ease;
    }
    .dashboard-shortcut__title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        color: var(--default-text-color);
    }
    .dashboard-shortcut__subtitle {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted, #6c757d);
    }
    .dashboard-shortcut__extra {
        margin-top: 0.5rem;
    }
    .dashboard-shortcut:hover {
        transform: translateY(-4px);
        text-decoration: none;
    }
    .dashboard-shortcut:focus-visible {
        outline: 2px solid rgb(var(--primary-rgb, 13, 110, 253));
        outline-offset: 2px;
    }
    [data-theme-mode="dark"] .dashboard-shortcut {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }
    [data-theme-mode="dark"] .dashboard-shortcut:hover {
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
    }

    .dashboard-shortcut--primary .dashboard-shortcut__icon { background: rgba(var(--primary-rgb, 13, 110, 253), 0.12); color: rgb(var(--primary-rgb, 13, 110, 253)); }
    .dashboard-shortcut--success .dashboard-shortcut__icon { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .dashboard-shortcut--info .dashboard-shortcut__icon { background: rgba(13, 202, 240, 0.12); color: #0dcaf0; }
    .dashboard-shortcut--warning .dashboard-shortcut__icon { background: rgba(255, 193, 7, 0.15); color: #cc9a06; }
    .dashboard-shortcut--danger .dashboard-shortcut__icon { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .dashboard-shortcut--secondary .dashboard-shortcut__icon { background: rgba(108, 117, 125, 0.12); color: #6c757d; }
    .dashboard-shortcut--purple .dashboard-shortcut__icon { background: rgba(111, 66, 193, 0.12); color: #6f42c1; }
    .dashboard-shortcut--teal .dashboard-shortcut__icon { background: rgba(32, 201, 151, 0.12); color: #20c997; }
    .dashboard-shortcut--orange .dashboard-shortcut__icon { background: rgba(253, 126, 20, 0.12); color: #fd7e14; }
    .dashboard-shortcut--indigo .dashboard-shortcut__icon { background: rgba(102, 16, 242, 0.12); color: #6610f2; }
    .dashboard-shortcut--muted .dashboard-shortcut__icon { background: rgba(108, 117, 125, 0.1); color: var(--text-muted, #6c757d); }

    .dashboard-shortcut--primary:hover { border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.45); box-shadow: 0 8px 20px rgba(var(--primary-rgb, 13, 110, 253), 0.15); }
    .dashboard-shortcut--success:hover { border-color: rgba(25, 135, 84, 0.45); box-shadow: 0 8px 20px rgba(25, 135, 84, 0.15); }
    .dashboard-shortcut--info:hover { border-color: rgba(13, 202, 240, 0.45); box-shadow: 0 8px 20px rgba(13, 202, 240, 0.15); }
    .dashboard-shortcut--warning:hover { border-color: rgba(255, 193, 7, 0.5); box-shadow: 0 8px 20px rgba(255, 193, 7, 0.15); }
    .dashboard-shortcut--danger:hover { border-color: rgba(220, 53, 69, 0.45); box-shadow: 0 8px 20px rgba(220, 53, 69, 0.15); }
    .dashboard-shortcut--secondary:hover { border-color: rgba(108, 117, 125, 0.45); box-shadow: 0 8px 20px rgba(108, 117, 125, 0.12); }
    .dashboard-shortcut--purple:hover { border-color: rgba(111, 66, 193, 0.45); box-shadow: 0 8px 20px rgba(111, 66, 193, 0.15); }
    .dashboard-shortcut--teal:hover { border-color: rgba(32, 201, 151, 0.45); box-shadow: 0 8px 20px rgba(32, 201, 151, 0.15); }
    .dashboard-shortcut--orange:hover { border-color: rgba(253, 126, 20, 0.45); box-shadow: 0 8px 20px rgba(253, 126, 20, 0.15); }
    .dashboard-shortcut--indigo:hover { border-color: rgba(102, 16, 242, 0.45); box-shadow: 0 8px 20px rgba(102, 16, 242, 0.15); }
    .dashboard-shortcut--muted:hover { border-color: var(--default-border); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }

    .dashboard-shortcut:hover .dashboard-shortcut__icon {
        transform: scale(1.08);
    }

    [data-theme-mode="dark"] .dashboard-shortcut__extra .badge.bg-light {
        background: rgba(255, 255, 255, 0.1) !important;
        color: var(--default-text-color) !important;
        border: 1px solid var(--default-border);
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-stat-card,
        .dashboard-stat-card:hover,
        .dashboard-shortcut,
        .dashboard-shortcut:hover,
        .dashboard-shortcut__icon {
            transition: none;
            transform: none;
        }
    }
</style>
