<style>
    .student-notif-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-notif-stat {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        color: var(--default-text-color, #0f172a);
    }

    .student-notif-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .student-notif-stat__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .student-notif-stat--total .student-notif-stat__icon {
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .student-notif-stat--unread .student-notif-stat__icon {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.12);
    }

    .student-notif-stat--read .student-notif-stat__icon {
        color: #059669;
        background: rgba(5, 150, 105, 0.12);
    }

    .student-notif-stat__label {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.1rem;
    }

    .student-notif-stat__value {
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--default-text-color, #0f172a);
    }

    .student-notif-realtime {
        border-radius: 12px;
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.05) 0%, rgba(255, 255, 255, 0.98) 100%);
        margin-bottom: 1rem;
    }

    .student-notif-panel {
        border-radius: 14px;
    }

    .student-notif-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-notif-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
    }

    .student-notif-filters .form-select {
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        min-width: 9rem;
        max-width: 100%;
    }

    .student-notif-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .student-notif-card {
        position: relative;
        display: flex;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease;
        animation: studentNotifReveal 0.4s ease backwards;
        color: var(--default-text-color, #0f172a);
    }

    .student-notif-card:hover {
        transform: translateX(-3px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.2);
    }

    .student-notif-card--unread {
        background: linear-gradient(90deg, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 0%, var(--custom-card-bg, var(--default-background, #fff)) 28%);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.22);
    }

    .student-notif-card__accent {
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
        border-radius: 0 4px 4px 0;
        transition: background 0.25s ease;
    }

    .student-notif-card--unread .student-notif-card__accent {
        background: linear-gradient(180deg, rgb(var(--primary-rgb, 13, 110, 253)) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.35) 100%);
    }

    .student-notif-card__icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        margin-top: 0.1rem;
        transition: transform 0.25s ease;
    }

    .student-notif-card:hover .student-notif-card__icon {
        transform: scale(1.06);
    }

    .student-notif-card__body {
        flex: 1;
        min-width: 0;
    }

    .student-notif-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .student-notif-card__title-row {
        min-width: 0;
    }

    .student-notif-card__badge-new {
        display: inline-flex;
        align-items: center;
        padding: 0.12rem 0.45rem;
        margin-bottom: 0.25rem;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(90deg, #4a7cff 0%, #2563eb 100%);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.28);
    }

    .student-notif-card__title {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.35;
        color: var(--default-text-color, #0f172a);
    }

    .student-notif-card__message {
        margin: 0 0 0.5rem;
        font-size: 0.82rem;
        color: var(--text-muted, #64748b);
        line-height: 1.45;
    }

    .student-notif-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.75rem;
        font-size: 0.72rem;
        color: var(--text-muted, #94a3b8);
    }

    .student-notif-card__meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .student-notif-card__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.45rem;
    }

    .student-notif-card__actions {
        display: flex;
        gap: 0.3rem;
        flex-shrink: 0;
    }

    .student-notif-card__actions .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .student-notif-card__actions .btn:hover {
        transform: scale(1.08);
    }

    .student-notif-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        border-radius: 14px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-notif-empty__icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--text-muted, #94a3b8);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
    }

    .notifications-list > .notification-item:nth-child(1) { animation-delay: 0.03s; }
    .notifications-list > .notification-item:nth-child(2) { animation-delay: 0.06s; }
    .notifications-list > .notification-item:nth-child(3) { animation-delay: 0.09s; }
    .notifications-list > .notification-item:nth-child(4) { animation-delay: 0.12s; }
    .notifications-list > .notification-item:nth-child(5) { animation-delay: 0.15s; }
    .notifications-list > .notification-item:nth-child(n+6) { animation-delay: 0.18s; }

    @keyframes studentNotifReveal {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    [data-theme-mode="dark"] .student-notif-stat,
    [data-bs-theme="dark"] .student-notif-stat {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-notif-stat__label,
    [data-bs-theme="dark"] .student-notif-stat__label {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-notif-stat__value,
    [data-bs-theme="dark"] .student-notif-stat__value {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-notif-stat--total .student-notif-stat__icon,
    [data-bs-theme="dark"] .student-notif-stat--total .student-notif-stat__icon {
        color: #93c5fd;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.2);
    }

    [data-theme-mode="dark"] .student-notif-stat--unread .student-notif-stat__icon,
    [data-bs-theme="dark"] .student-notif-stat--unread .student-notif-stat__icon {
        color: #fca5a5;
        background: rgba(220, 53, 69, 0.18);
    }

    [data-theme-mode="dark"] .student-notif-stat--read .student-notif-stat__icon,
    [data-bs-theme="dark"] .student-notif-stat--read .student-notif-stat__icon {
        color: #6ee7b7;
        background: rgba(5, 150, 105, 0.18);
    }

    [data-theme-mode="dark"] .student-notif-realtime,
    [data-bs-theme="dark"] .student-notif-realtime {
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.12) 0%, rgba(28, 31, 40, 0.98) 100%);
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme-mode="dark"] .student-notif-realtime .card-body,
    [data-bs-theme="dark"] .student-notif-realtime .card-body,
    [data-theme-mode="dark"] .student-notif-realtime h6,
    [data-bs-theme="dark"] .student-notif-realtime h6 {
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-notif-realtime p,
    [data-bs-theme="dark"] .student-notif-realtime p {
        color: #94a3b8 !important;
    }

    [data-theme-mode="dark"] .student-notif-card,
    [data-bs-theme="dark"] .student-notif-card {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
    }

    [data-theme-mode="dark"] .student-notif-card:hover,
    [data-bs-theme="dark"] .student-notif-card:hover {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.35);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
    }

    [data-theme-mode="dark"] .student-notif-card--unread,
    [data-bs-theme="dark"] .student-notif-card--unread {
        background: linear-gradient(90deg, rgba(var(--primary-rgb, 13, 110, 253), 0.14) 0%, var(--custom-card-bg, #1c1f28) 28%);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.28);
    }

    [data-theme-mode="dark"] .student-notif-card__title,
    [data-bs-theme="dark"] .student-notif-card__title {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-notif-card__message,
    [data-bs-theme="dark"] .student-notif-card__message,
    [data-theme-mode="dark"] .student-notif-card__meta,
    [data-bs-theme="dark"] .student-notif-card__meta {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-notif-empty,
    [data-bs-theme="dark"] .student-notif-empty {
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, transparent 100%);
        border-color: rgba(255, 255, 255, 0.12);
    }

    @media (max-width: 767.98px) {
        .student-notif-stats {
            gap: 0.45rem;
        }

        .student-notif-stat {
            padding: 0.55rem 0.6rem;
            gap: 0.45rem;
        }

        .student-notif-stat__icon {
            width: 32px;
            height: 32px;
            font-size: 0.88rem;
        }

        .student-notif-stat__label {
            font-size: 0.65rem;
        }

        .student-notif-stat__value {
            font-size: 0.95rem;
        }

        .student-notif-card {
            padding: 0.75rem 0.8rem;
            gap: 0.65rem;
        }

        .student-notif-card__icon {
            width: 38px;
            height: 38px;
            font-size: 1rem;
        }

        .student-notif-card__title {
            font-size: 0.85rem;
        }

        .student-notif-card__message {
            font-size: 0.78rem;
        }

        .student-notif-filters {
            width: 100%;
        }

        .student-notif-filters .form-select {
            flex: 1;
            min-width: 0;
        }

        .student-notif-actions {
            width: 100%;
        }

        .student-notif-actions .btn {
            flex: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-notif-card,
        .student-notif-card:hover,
        .student-notif-card__icon,
        .student-notif-stat,
        .notification-item {
            animation: none;
            transition: none;
            transform: none;
        }
    }
</style>
