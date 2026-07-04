<style>
    .student-gamification-hero {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 1rem;
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 45%, #4338ca 100%);
        box-shadow: 0 8px 28px rgba(37, 99, 235, 0.25);
    }

    .student-gamification-hero__inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.35rem;
    }

    .student-gamification-hero__pattern {
        position: absolute;
        inset: 0;
        opacity: 0.12;
        background-image: radial-gradient(circle at 20% 30%, #fff 0%, transparent 45%),
                          radial-gradient(circle at 80% 70%, #fff 0%, transparent 40%);
        pointer-events: none;
    }

    .student-gamification-hero__profile {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }

    .student-gamification-hero__avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.75rem;
    }

    .student-gamification-hero__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-gamification-hero__name {
        margin: 0 0 0.25rem;
        font-size: 1.15rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.3;
    }

    .student-gamification-hero__level {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #fef3c7;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .student-gamification-hero__metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        margin-top: 0.65rem;
    }

    .student-gamification-hero__metric-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
    }

    .student-gamification-hero__metric-label {
        display: block;
        font-size: 0.68rem;
        color: rgba(255, 255, 255, 0.78);
        margin-top: 0.1rem;
    }

    .student-gamification-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .student-gamification-hero__actions .btn {
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .student-gamification-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-gamification-stat {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 0.95rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .student-gamification-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    .student-gamification-stat__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .student-gamification-stat--points .student-gamification-stat__icon { color: rgb(var(--primary-rgb, 13, 110, 253)); background: rgba(var(--primary-rgb, 13, 110, 253), 0.12); }
    .student-gamification-stat--badges .student-gamification-stat__icon { color: #059669; background: rgba(5, 150, 105, 0.12); }
    .student-gamification-stat--achievements .student-gamification-stat__icon { color: #0284c7; background: rgba(2, 132, 199, 0.12); }
    .student-gamification-stat--level .student-gamification-stat__icon { color: #d97706; background: rgba(217, 119, 6, 0.12); }

    .student-gamification-stat__label {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.1rem;
    }

    .student-gamification-stat__value {
        font-size: 1.1rem;
        font-weight: 800;
        line-height: 1.2;
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-level {
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .student-gamification-level__head {
        padding: 0.9rem 1.1rem;
        border-bottom: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 0%, transparent 100%);
    }

    .student-gamification-level__head h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-level__body {
        padding: 1rem 1.1rem 1.05rem;
    }

    .student-gamification-level__ends {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .student-gamification-level__end-title {
        margin: 0 0 0.1rem;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-level__end-sub {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }

    .student-gamification-level__bar {
        height: 12px;
        border-radius: 999px;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        overflow: hidden;
        margin-bottom: 0.65rem;
    }

    .student-gamification-level__bar .progress-bar {
        border-radius: 999px;
        background: linear-gradient(90deg, #4a7cff 0%, #2563eb 100%);
        font-size: 0.68rem;
        font-weight: 800;
    }

    .student-gamification-level__footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.76rem;
        color: var(--text-muted, #64748b);
    }

    .student-gamification-level__chip {
        display: inline-flex;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
    }

    .student-gamification-panel {
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .student-gamification-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.9rem 1.05rem;
        border-bottom: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.05) 0%, transparent 100%);
    }

    .student-gamification-panel__title {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-panel__body {
        padding: 0.85rem 1.05rem 1rem;
        flex: 1;
    }

    .student-gamification-mini-badge {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);
        margin-bottom: 0.55rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        animation: studentGamificationReveal 0.4s ease backwards;
    }

    .student-gamification-mini-badge:last-child {
        margin-bottom: 0;
    }

    .student-gamification-mini-badge:hover {
        transform: translateX(-3px);
        border-color: color-mix(in srgb, var(--badge-color, #6366f1) 30%, transparent);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .student-gamification-mini-badge__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        color: var(--badge-color, #6366f1);
        background: color-mix(in srgb, var(--badge-color, #6366f1) 12%, transparent);
        border: 1px solid color-mix(in srgb, var(--badge-color, #6366f1) 20%, transparent);
    }

    .student-gamification-mini-badge__title {
        margin: 0 0 0.15rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-mini-badge__meta {
        font-size: 0.7rem;
        color: var(--text-muted, #64748b);
    }

    .student-gamification-achievement-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: rgba(5, 150, 105, 0.02);
        margin-bottom: 0.55rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        animation: studentGamificationReveal 0.4s ease backwards;
    }

    .student-gamification-achievement-row:last-child {
        margin-bottom: 0;
    }

    .student-gamification-achievement-row:hover {
        transform: translateX(-3px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .student-gamification-achievement-row__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        color: #059669;
        background: rgba(5, 150, 105, 0.12);
    }

    .student-gamification-achievement-row__main {
        flex: 1;
        min-width: 0;
    }

    .student-gamification-achievement-row__title {
        margin: 0 0 0.1rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-achievement-row__desc {
        margin: 0;
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }

    .student-gamification-achievement-row__time {
        font-size: 0.68rem;
        color: var(--text-muted, #94a3b8);
        white-space: nowrap;
    }

    .student-gamification-empty {
        text-align: center;
        padding: 1.75rem 1rem;
    }

    .student-gamification-empty__icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 0.75rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .student-gamification-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
    }

    .student-gamification-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 1rem 0.75rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        text-decoration: none;
        color: var(--default-text-color, #0f172a);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .student-gamification-action:hover {
        transform: translateY(-4px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.22);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        color: var(--default-text-color, #0f172a);
    }

    .student-gamification-action__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .student-gamification-action--challenges .student-gamification-action__icon { color: #dc2626; background: rgba(220, 38, 38, 0.1); }
    .student-gamification-action--rewards .student-gamification-action__icon { color: rgb(var(--primary-rgb, 13, 110, 253)); background: rgba(var(--primary-rgb, 13, 110, 253), 0.1); }
    .student-gamification-action--tasks .student-gamification-action__icon { color: #059669; background: rgba(5, 150, 105, 0.1); }

    .student-gamification-action__label {
        font-size: 0.82rem;
        font-weight: 700;
    }

    @keyframes studentGamificationReveal {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    [data-theme-mode="dark"] .student-gamification-stat,
    [data-bs-theme="dark"] .student-gamification-stat,
    [data-theme-mode="dark"] .student-gamification-level,
    [data-bs-theme="dark"] .student-gamification-level,
    [data-theme-mode="dark"] .student-gamification-panel,
    [data-bs-theme="dark"] .student-gamification-panel,
    [data-theme-mode="dark"] .student-gamification-mini-badge,
    [data-bs-theme="dark"] .student-gamification-mini-badge,
    [data-theme-mode="dark"] .student-gamification-achievement-row,
    [data-bs-theme="dark"] .student-gamification-achievement-row,
    [data-theme-mode="dark"] .student-gamification-action,
    [data-bs-theme="dark"] .student-gamification-action {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-gamification-stat__value,
    [data-bs-theme="dark"] .student-gamification-stat__value,
    [data-theme-mode="dark"] .student-gamification-level__head h5,
    [data-bs-theme="dark"] .student-gamification-level__head h5,
    [data-theme-mode="dark"] .student-gamification-level__end-title,
    [data-bs-theme="dark"] .student-gamification-level__end-title,
    [data-theme-mode="dark"] .student-gamification-panel__title,
    [data-bs-theme="dark"] .student-gamification-panel__title,
    [data-theme-mode="dark"] .student-gamification-mini-badge__title,
    [data-bs-theme="dark"] .student-gamification-mini-badge__title,
    [data-theme-mode="dark"] .student-gamification-achievement-row__title,
    [data-bs-theme="dark"] .student-gamification-achievement-row__title,
    [data-theme-mode="dark"] .student-gamification-action__label,
    [data-bs-theme="dark"] .student-gamification-action__label {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-gamification-stat__label,
    [data-bs-theme="dark"] .student-gamification-stat__label,
    [data-theme-mode="dark"] .student-gamification-level__end-sub,
    [data-bs-theme="dark"] .student-gamification-level__end-sub,
    [data-theme-mode="dark"] .student-gamification-level__footer,
    [data-bs-theme="dark"] .student-gamification-level__footer,
    [data-theme-mode="dark"] .student-gamification-mini-badge__meta,
    [data-bs-theme="dark"] .student-gamification-mini-badge__meta,
    [data-theme-mode="dark"] .student-gamification-achievement-row__desc,
    [data-bs-theme="dark"] .student-gamification-achievement-row__desc,
    [data-theme-mode="dark"] .student-gamification-achievement-row__time,
    [data-bs-theme="dark"] .student-gamification-achievement-row__time {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-gamification-level__head,
    [data-bs-theme="dark"] .student-gamification-level__head,
    [data-theme-mode="dark"] .student-gamification-panel__head,
    [data-bs-theme="dark"] .student-gamification-panel__head {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.14) 0%, rgba(28, 31, 40, 0.95) 100%);
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-gamification-stat--points .student-gamification-stat__icon,
    [data-bs-theme="dark"] .student-gamification-stat--points .student-gamification-stat__icon { color: #93c5fd; background: rgba(var(--primary-rgb, 13, 110, 253), 0.18); }
    [data-theme-mode="dark"] .student-gamification-stat--badges .student-gamification-stat__icon,
    [data-bs-theme="dark"] .student-gamification-stat--badges .student-gamification-stat__icon { color: #6ee7b7; background: rgba(5, 150, 105, 0.15); }
    [data-theme-mode="dark"] .student-gamification-stat--achievements .student-gamification-stat__icon,
    [data-bs-theme="dark"] .student-gamification-stat--achievements .student-gamification-stat__icon { color: #7dd3fc; background: rgba(14, 165, 233, 0.15); }
    [data-theme-mode="dark"] .student-gamification-stat--level .student-gamification-stat__icon,
    [data-bs-theme="dark"] .student-gamification-stat--level .student-gamification-stat__icon { color: #fcd34d; background: rgba(245, 158, 11, 0.15); }

    [data-theme-mode="dark"] .student-gamification-level__bar,
    [data-bs-theme="dark"] .student-gamification-level__bar {
        background: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-gamification-hero,
    [data-bs-theme="dark"] .student-gamification-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #312e81 50%, #3730a3 100%);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35);
    }

    @media (max-width: 991.98px) {
        .student-gamification-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 767.98px) {
        .student-gamification-hero__inner {
            padding: 1rem;
        }

        .student-gamification-hero__avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
        }

        .student-gamification-hero__name {
            font-size: 1rem;
        }

        .student-gamification-hero__metric-value {
            font-size: 1.1rem;
        }

        .student-gamification-hero__actions {
            width: 100%;
        }

        .student-gamification-hero__actions .btn {
            flex: 1;
        }

        .student-gamification-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .student-gamification-stat {
            padding: 0.7rem 0.75rem;
        }

        .student-gamification-stat__value {
            font-size: 0.95rem;
        }

        .student-gamification-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-gamification-stat,
        .student-gamification-stat:hover,
        .student-gamification-mini-badge,
        .student-gamification-mini-badge:hover,
        .student-gamification-achievement-row,
        .student-gamification-achievement-row:hover,
        .student-gamification-action,
        .student-gamification-action:hover {
            animation: none;
            transition: none;
            transform: none;
        }
    }
</style>
