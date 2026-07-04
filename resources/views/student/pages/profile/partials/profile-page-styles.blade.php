<style>
    .student-profile-hero {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 1rem;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
    }

    .student-profile-hero__banner {
        height: 88px;
        background: linear-gradient(135deg, #2563eb 0%, #4338ca 55%, #7c3aed 100%);
        position: relative;
    }

    .student-profile-hero__banner::after {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.15;
        background-image: radial-gradient(circle at 25% 40%, #fff 0%, transparent 50%),
                          radial-gradient(circle at 75% 60%, #fff 0%, transparent 45%);
    }

    .student-profile-hero__body {
        padding: 0 1.15rem 1.15rem;
        margin-top: -42px;
        position: relative;
        z-index: 1;
    }

    .student-profile-hero__avatar-wrap {
        width: 84px;
        height: 84px;
        border-radius: 20px;
        padding: 3px;
        background: linear-gradient(135deg, #60a5fa, #a78bfa, #f472b6);
        margin-bottom: 0.75rem;
        transition: transform 0.3s ease;
    }

    .student-profile-hero:hover .student-profile-hero__avatar-wrap {
        transform: scale(1.04);
    }

    .student-profile-hero__avatar {
        width: 100%;
        height: 100%;
        border-radius: 17px;
        overflow: hidden;
        background: var(--custom-card-bg, #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        color: rgb(var(--primary-rgb, 13, 110, 253));
    }

    .student-profile-hero__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-profile-hero__name {
        margin: 0 0 0.35rem;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
    }

    .student-profile-hero__contacts {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        margin-bottom: 0.85rem;
    }

    .student-profile-hero__contact {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        color: var(--text-muted, #64748b);
    }

    .student-profile-hero__contact i {
        color: rgb(var(--primary-rgb, 13, 110, 253));
        width: 1rem;
        text-align: center;
    }

    .student-profile-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .student-profile-stat {
        text-align: center;
        padding: 0.75rem 0.5rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        cursor: default;
    }

    .student-profile-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.07);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.18);
    }

    .student-profile-stat__value {
        display: block;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 0.15rem;
    }

    .student-profile-stat__label {
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--text-muted, #64748b);
    }

    .student-profile-stat--subjects .student-profile-stat__value { color: rgb(var(--primary-rgb, 13, 110, 253)); }
    .student-profile-stat--enrollments .student-profile-stat__value { color: #059669; }
    .student-profile-stat--attempts .student-profile-stat__value { color: #0284c7; }
    .student-profile-stat--passed .student-profile-stat__value { color: #d97706; }
    .student-profile-stat--average .student-profile-stat__value { color: #7c3aed; }

    .student-profile-panel {
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .student-profile-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.9rem 1.05rem;
        border-bottom: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.05) 0%, transparent 100%);
    }

    .student-profile-panel__title {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
    }

    .student-profile-panel__count {
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .student-profile-panel__body {
        padding: 0.85rem 1.05rem 1rem;
    }

    .student-profile-subject-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        margin-bottom: 0.55rem;
        text-decoration: none;
        color: inherit;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        animation: studentProfileReveal 0.4s ease backwards;
    }

    .student-profile-subject-row:last-child {
        margin-bottom: 0;
    }

    .student-profile-subject-row:hover {
        transform: translateX(-4px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.22);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        color: inherit;
    }

    .student-profile-subject-row__icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        transition: transform 0.22s ease;
    }

    .student-profile-subject-row:hover .student-profile-subject-row__icon {
        transform: scale(1.08);
    }

    .student-profile-subject-row__main {
        flex: 1;
        min-width: 0;
    }

    .student-profile-subject-row__title {
        margin: 0 0 0.15rem;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-profile-subject-row__meta {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }

    .student-profile-subject-row__status {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .student-profile-subject-row__status--active { color: #059669; background: rgba(5, 150, 105, 0.1); }
    .student-profile-subject-row__status--suspended { color: #d97706; background: rgba(217, 119, 6, 0.1); }
    .student-profile-subject-row__status--other { color: #64748b; background: rgba(100, 116, 139, 0.1); }

    .student-profile-attempt-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        margin-bottom: 0.55rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        animation: studentProfileReveal 0.4s ease backwards;
    }

    .student-profile-attempt-row:last-child { margin-bottom: 0; }

    .student-profile-attempt-row:hover {
        transform: translateX(-3px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .student-profile-attempt-row__score {
        width: 48px;
        text-align: center;
        flex-shrink: 0;
        padding: 0.4rem 0.25rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .student-profile-attempt-row__score--passed { color: #059669; background: rgba(5, 150, 105, 0.1); }
    .student-profile-attempt-row__score--failed { color: #dc2626; background: rgba(220, 38, 38, 0.08); }
    .student-profile-attempt-row__score--pending { color: #64748b; background: rgba(100, 116, 139, 0.1); }

    .student-profile-attempt-row__title {
        margin: 0 0 0.1rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-profile-attempt-row__meta {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }

    .student-profile-login-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.75rem;
        padding: 0.65rem 0.75rem;
        border-radius: 11px;
        border: 1px solid var(--default-border);
        margin-bottom: 0.45rem;
        font-size: 0.74rem;
        transition: background 0.2s ease;
        animation: studentProfileReveal 0.4s ease backwards;
    }

    .student-profile-login-row:last-child { margin-bottom: 0; }

    .student-profile-login-row:hover {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.03);
    }

    .student-profile-login-row__item {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: var(--text-muted, #64748b);
    }

    .student-profile-login-row__item strong {
        color: var(--default-text-color, #0f172a);
        font-weight: 700;
    }

    .student-profile-login-row__status {
        margin-right: auto;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
    }

    .student-profile-login-row__status--ok { color: #059669; background: rgba(5, 150, 105, 0.1); }
    .student-profile-login-row__status--fail { color: #dc2626; background: rgba(220, 38, 38, 0.08); }

    .student-profile-empty {
        text-align: center;
        padding: 2rem 1rem;
        border-radius: 12px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.22);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-profile-empty__icon {
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

    @keyframes studentProfileReveal {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    [data-theme-mode="dark"] .student-profile-hero,
    [data-bs-theme="dark"] .student-profile-hero,
    [data-theme-mode="dark"] .student-profile-panel,
    [data-bs-theme="dark"] .student-profile-panel,
    [data-theme-mode="dark"] .student-profile-stat,
    [data-bs-theme="dark"] .student-profile-stat,
    [data-theme-mode="dark"] .student-profile-subject-row,
    [data-bs-theme="dark"] .student-profile-subject-row,
    [data-theme-mode="dark"] .student-profile-attempt-row,
    [data-bs-theme="dark"] .student-profile-attempt-row,
    [data-theme-mode="dark"] .student-profile-login-row,
    [data-bs-theme="dark"] .student-profile-login-row {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }

    [data-theme-mode="dark"] .student-profile-hero__avatar,
    [data-bs-theme="dark"] .student-profile-hero__avatar {
        background: #1c1f28;
        color: #93c5fd;
    }

    [data-theme-mode="dark"] .student-profile-hero__name,
    [data-bs-theme="dark"] .student-profile-hero__name,
    [data-theme-mode="dark"] .student-profile-panel__title,
    [data-bs-theme="dark"] .student-profile-panel__title,
    [data-theme-mode="dark"] .student-profile-subject-row__title,
    [data-bs-theme="dark"] .student-profile-subject-row__title,
    [data-theme-mode="dark"] .student-profile-attempt-row__title,
    [data-bs-theme="dark"] .student-profile-attempt-row__title,
    [data-theme-mode="dark"] .student-profile-login-row__item strong,
    [data-bs-theme="dark"] .student-profile-login-row__item strong {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-profile-hero__contact,
    [data-bs-theme="dark"] .student-profile-hero__contact,
    [data-theme-mode="dark"] .student-profile-stat__label,
    [data-bs-theme="dark"] .student-profile-stat__label,
    [data-theme-mode="dark"] .student-profile-subject-row__meta,
    [data-bs-theme="dark"] .student-profile-subject-row__meta,
    [data-theme-mode="dark"] .student-profile-attempt-row__meta,
    [data-bs-theme="dark"] .student-profile-attempt-row__meta,
    [data-theme-mode="dark"] .student-profile-login-row__item,
    [data-bs-theme="dark"] .student-profile-login-row__item {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-profile-panel__head,
    [data-bs-theme="dark"] .student-profile-panel__head {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.14) 0%, rgba(28, 31, 40, 0.95) 100%);
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .student-profile-stat--subjects .student-profile-stat__value,
    [data-bs-theme="dark"] .student-profile-stat--subjects .student-profile-stat__value { color: #93c5fd; }
    [data-theme-mode="dark"] .student-profile-stat--enrollments .student-profile-stat__value,
    [data-bs-theme="dark"] .student-profile-stat--enrollments .student-profile-stat__value { color: #6ee7b7; }
    [data-theme-mode="dark"] .student-profile-stat--attempts .student-profile-stat__value,
    [data-bs-theme="dark"] .student-profile-stat--attempts .student-profile-stat__value { color: #7dd3fc; }
    [data-theme-mode="dark"] .student-profile-stat--passed .student-profile-stat__value,
    [data-bs-theme="dark"] .student-profile-stat--passed .student-profile-stat__value { color: #fcd34d; }

    [data-theme-mode="dark"] .student-profile-empty,
    [data-bs-theme="dark"] .student-profile-empty {
        border-color: rgba(255, 255, 255, 0.12);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, transparent 100%);
    }

    @media (max-width: 767.98px) {
        .student-profile-hero__body {
            padding: 0 0.9rem 0.9rem;
        }

        .student-profile-hero__avatar-wrap {
            width: 72px;
            height: 72px;
        }

        .student-profile-stat__value {
            font-size: 1.1rem;
        }

        .student-profile-attempt-row {
            flex-wrap: wrap;
        }

        .student-profile-login-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-profile-login-row__status {
            margin-right: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-profile-hero__avatar-wrap,
        .student-profile-stat,
        .student-profile-stat:hover,
        .student-profile-subject-row,
        .student-profile-subject-row:hover,
        .student-profile-attempt-row,
        .student-profile-attempt-row:hover {
            animation: none;
            transition: none;
            transform: none;
        }
    }
</style>
