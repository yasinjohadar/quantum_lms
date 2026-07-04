<style>
    .student-badges-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-badges-stat {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 0.95rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .student-badges-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.07);
    }

    .student-badges-stat__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .student-badges-stat--earned .student-badges-stat__icon {
        color: #059669;
        background: rgba(5, 150, 105, 0.12);
    }

    .student-badges-stat--remaining .student-badges-stat__icon {
        color: #d97706;
        background: rgba(217, 119, 6, 0.12);
    }

    .student-badges-stat--total .student-badges-stat__icon {
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .student-badges-stat__label {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.1rem;
    }

    .student-badges-stat__value {
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--default-text-color, #0f172a);
    }

    .student-badges-section {
        margin-bottom: 1.25rem;
    }

    .student-badges-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }

    .student-badges-section__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
    }

    .student-badges-section__count {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
    }

    .student-badges-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
    }

    .student-badge-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.15rem 0.85rem 0.95rem;
        border-radius: 16px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        animation: studentBadgeReveal 0.45s ease backwards;
    }

    .student-badge-card__glow {
        position: absolute;
        inset: -40% auto auto 50%;
        width: 120px;
        height: 120px;
        transform: translateX(-50%);
        border-radius: 50%;
        background: radial-gradient(circle, color-mix(in srgb, var(--badge-color) 35%, transparent) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .student-badge-card--earned:hover {
        transform: translateY(-6px) scale(1.02);
        border-color: color-mix(in srgb, var(--badge-color) 40%, transparent);
        box-shadow: 0 16px 32px color-mix(in srgb, var(--badge-color) 18%, rgba(15, 23, 42, 0.08));
    }

    .student-badge-card--earned:hover .student-badge-card__glow {
        opacity: 1;
    }

    .student-badge-card--locked {
        opacity: 0.82;
    }

    .student-badge-card--locked:hover {
        transform: translateY(-3px);
        opacity: 0.95;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
    }

    .student-badge-card__icon-wrap {
        position: relative;
        width: 64px;
        height: 64px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
        background: color-mix(in srgb, var(--badge-color) 12%, transparent);
        border: 1px solid color-mix(in srgb, var(--badge-color) 22%, transparent);
        transition: transform 0.28s ease;
    }

    .student-badge-card--earned:hover .student-badge-card__icon-wrap {
        transform: scale(1.08) rotate(-3deg);
    }

    .student-badge-card--locked .student-badge-card__icon-wrap {
        background: rgba(100, 116, 139, 0.08);
        border-color: rgba(100, 116, 139, 0.15);
        filter: grayscale(0.85);
    }

    .student-badge-card__icon {
        font-size: 1.75rem;
        color: var(--badge-color);
        line-height: 1;
    }

    .student-badge-card--locked .student-badge-card__icon {
        color: #94a3b8;
    }

    .student-badge-card__lock {
        position: absolute;
        bottom: -4px;
        left: -4px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.62rem;
        color: #64748b;
        background: var(--custom-card-bg, #fff);
        border: 1px solid var(--default-border);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    }

    .student-badge-card__title {
        margin: 0 0 0.35rem;
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
        line-height: 1.35;
    }

    .student-badge-card__desc {
        margin: 0 0 0.65rem;
        font-size: 0.74rem;
        color: var(--text-muted, #64748b);
        line-height: 1.45;
        flex: 1;
    }

    .student-badge-card__footer {
        width: 100%;
        margin-top: auto;
    }

    .student-badge-card__meta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .student-badge-card__meta--earned {
        color: #059669;
        background: rgba(5, 150, 105, 0.1);
    }

    .student-badge-card__meta--points {
        color: #d97706;
        background: rgba(217, 119, 6, 0.1);
    }

    .student-badge-card__meta--locked {
        color: #64748b;
        background: rgba(100, 116, 139, 0.1);
    }

    .student-badge-card__ribbon {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        font-size: 0.6rem;
        font-weight: 800;
        color: #fff;
        background: var(--badge-color);
        box-shadow: 0 2px 8px color-mix(in srgb, var(--badge-color) 40%, transparent);
    }

    .student-badges-empty {
        text-align: center;
        padding: 2.5rem 1.25rem;
        border-radius: 14px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-badges-empty__icon {
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

    @keyframes studentBadgeReveal {
        from { opacity: 0; transform: translateY(14px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    [data-theme-mode="dark"] .student-badges-stat,
    [data-bs-theme="dark"] .student-badges-stat,
    [data-theme-mode="dark"] .student-badge-card,
    [data-bs-theme="dark"] .student-badge-card {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }

    [data-theme-mode="dark"] .student-badges-stat__value,
    [data-bs-theme="dark"] .student-badges-stat__value,
    [data-theme-mode="dark"] .student-badge-card__title,
    [data-bs-theme="dark"] .student-badge-card__title,
    [data-theme-mode="dark"] .student-badges-section__title,
    [data-bs-theme="dark"] .student-badges-section__title {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-badges-stat__label,
    [data-bs-theme="dark"] .student-badges-stat__label,
    [data-theme-mode="dark"] .student-badge-card__desc,
    [data-bs-theme="dark"] .student-badge-card__desc {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-badges-stat--earned .student-badges-stat__icon,
    [data-bs-theme="dark"] .student-badges-stat--earned .student-badges-stat__icon { color: #6ee7b7; background: rgba(5, 150, 105, 0.15); }
    [data-theme-mode="dark"] .student-badges-stat--remaining .student-badges-stat__icon,
    [data-bs-theme="dark"] .student-badges-stat--remaining .student-badges-stat__icon { color: #fcd34d; background: rgba(245, 158, 11, 0.15); }
    [data-theme-mode="dark"] .student-badges-stat--total .student-badges-stat__icon,
    [data-bs-theme="dark"] .student-badges-stat--total .student-badges-stat__icon { color: #93c5fd; background: rgba(var(--primary-rgb, 13, 110, 253), 0.18); }

    [data-theme-mode="dark"] .student-badge-card--earned:hover,
    [data-bs-theme="dark"] .student-badge-card--earned:hover {
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.4);
    }

    [data-theme-mode="dark"] .student-badge-card__lock,
    [data-bs-theme="dark"] .student-badge-card__lock {
        background: #1c1f28;
        border-color: rgba(255, 255, 255, 0.12);
        color: #cbd5e1;
    }

    [data-theme-mode="dark"] .student-badge-card__meta--earned,
    [data-bs-theme="dark"] .student-badge-card__meta--earned { color: #6ee7b7; background: rgba(5, 150, 105, 0.12); }
    [data-theme-mode="dark"] .student-badge-card__meta--points,
    [data-bs-theme="dark"] .student-badge-card__meta--points { color: #fcd34d; background: rgba(245, 158, 11, 0.12); }
    [data-theme-mode="dark"] .student-badge-card__meta--locked,
    [data-bs-theme="dark"] .student-badge-card__meta--locked { color: #cbd5e1; background: rgba(148, 163, 184, 0.12); }

    [data-theme-mode="dark"] .student-badges-empty,
    [data-bs-theme="dark"] .student-badges-empty {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, transparent 100%);
        border-color: rgba(255, 255, 255, 0.12);
    }

    @media (max-width: 1199.98px) {
        .student-badges-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 991.98px) {
        .student-badges-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 767.98px) {
        .student-badges-stats {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .student-badges-stat {
            padding: 0.7rem 0.8rem;
        }

        .student-badges-stat__value {
            font-size: 1.1rem;
        }

        .student-badges-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.55rem;
        }

        .student-badge-card {
            padding: 0.85rem 0.65rem 0.75rem;
            border-radius: 14px;
        }

        .student-badge-card__icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            margin-bottom: 0.55rem;
        }

        .student-badge-card__icon {
            font-size: 1.4rem;
        }

        .student-badge-card__title {
            font-size: 0.82rem;
        }

        .student-badge-card__desc {
            font-size: 0.68rem;
            margin-bottom: 0.5rem;
        }

        .student-badge-card__meta {
            font-size: 0.62rem;
        }

        .student-badge-card__ribbon {
            top: 8px;
            left: 8px;
            font-size: 0.55rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-badge-card,
        .student-badge-card:hover,
        .student-badge-card__icon-wrap,
        .student-badges-stat,
        .student-badges-stat:hover {
            animation: none;
            transition: none;
            transform: none;
        }
    }
</style>
