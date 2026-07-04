<style>
    .student-rewards-points {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1.1rem 1.2rem;
        border-radius: 16px;
        margin-bottom: 1rem;
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #4338ca 100%);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.22);
        color: #fff;
    }

    .student-rewards-points__label {
        font-size: 0.82rem;
        font-weight: 600;
        opacity: 0.85;
        margin-bottom: 0.15rem;
    }

    .student-rewards-points__value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .student-rewards-points__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
    }

    .student-rewards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .student-reward-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        animation: studentGamificationReveal 0.45s ease backwards;
    }

    .student-reward-card--available:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
    }

    .student-reward-card--locked {
        opacity: 0.88;
    }

    .student-reward-card--locked:hover {
        transform: translateY(-2px);
        opacity: 0.95;
    }

    .student-reward-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.65rem;
    }

    .student-reward-card__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .student-reward-card--locked .student-reward-card__icon {
        color: #94a3b8;
        background: rgba(100, 116, 139, 0.1);
    }

    .student-reward-card__type {
        font-size: 0.68rem;
        font-weight: 800;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .student-reward-card__title {
        margin: 0 0 0.35rem;
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
        line-height: 1.35;
    }

    .student-reward-card__desc {
        margin: 0 0 0.75rem;
        font-size: 0.76rem;
        color: var(--text-muted, #64748b);
        line-height: 1.45;
        flex: 1;
    }

    .student-reward-card__footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.45rem;
        margin-top: auto;
    }

    .student-reward-card__cost {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #d97706;
        background: rgba(217, 119, 6, 0.1);
        border: 1px solid rgba(217, 119, 6, 0.15);
    }

    .student-reward-card__footer .btn {
        border-radius: 10px;
        font-weight: 700;
    }

    .student-reward-card__locked {
        font-size: 0.72rem;
        font-weight: 700;
        color: #dc2626;
    }

    .student-user-reward-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 0.75rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);
        margin-bottom: 0.55rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        animation: studentGamificationReveal 0.4s ease backwards;
    }

    .student-user-reward-row:last-child {
        margin-bottom: 0;
    }

    .student-user-reward-row:hover {
        transform: translateX(-3px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .student-user-reward-row__icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #7c3aed;
        background: rgba(124, 58, 237, 0.1);
        flex-shrink: 0;
    }

    .student-user-reward-row__main {
        flex: 1;
        min-width: 0;
    }

    .student-user-reward-row__title {
        margin: 0 0 0.1rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-user-reward-row__date {
        font-size: 0.68rem;
        color: var(--text-muted, #64748b);
    }

    .student-user-reward-row__status {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .student-user-reward-row__status--success { color: #059669; background: rgba(5, 150, 105, 0.1); }
    .student-user-reward-row__status--warning { color: #d97706; background: rgba(217, 119, 6, 0.1); }
    .student-user-reward-row__status--danger { color: #dc2626; background: rgba(220, 38, 38, 0.08); }

    [data-theme-mode="dark"] .student-rewards-points,
    [data-bs-theme="dark"] .student-rewards-points {
        background: linear-gradient(135deg, #1e3a8a 0%, #312e81 50%, #3730a3 100%);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    }

    [data-theme-mode="dark"] .student-reward-card,
    [data-bs-theme="dark"] .student-reward-card,
    [data-theme-mode="dark"] .student-user-reward-row,
    [data-bs-theme="dark"] .student-user-reward-row {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }

    [data-theme-mode="dark"] .student-reward-card__title,
    [data-bs-theme="dark"] .student-reward-card__title,
    [data-theme-mode="dark"] .student-user-reward-row__title,
    [data-bs-theme="dark"] .student-user-reward-row__title {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-reward-card__desc,
    [data-bs-theme="dark"] .student-reward-card__desc,
    [data-theme-mode="dark"] .student-user-reward-row__date,
    [data-bs-theme="dark"] .student-user-reward-row__date {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-reward-card__icon,
    [data-bs-theme="dark"] .student-reward-card__icon {
        color: #93c5fd;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.18);
    }

    [data-theme-mode="dark"] .student-reward-card__type,
    [data-bs-theme="dark"] .student-reward-card__type {
        color: #93c5fd;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.2);
    }

    [data-theme-mode="dark"] .student-reward-card__cost,
    [data-bs-theme="dark"] .student-reward-card__cost {
        color: #fcd34d;
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(252, 211, 77, 0.2);
    }

    [data-theme-mode="dark"] .student-reward-card__locked,
    [data-bs-theme="dark"] .student-reward-card__locked { color: #fca5a5; }

    [data-theme-mode="dark"] .student-user-reward-row__icon,
    [data-bs-theme="dark"] .student-user-reward-row__icon {
        color: #c4b5fd;
        background: rgba(124, 58, 237, 0.15);
    }

    [data-theme-mode="dark"] .student-user-reward-row__status--success,
    [data-bs-theme="dark"] .student-user-reward-row__status--success { color: #6ee7b7; background: rgba(5, 150, 105, 0.12); }
    [data-theme-mode="dark"] .student-user-reward-row__status--warning,
    [data-bs-theme="dark"] .student-user-reward-row__status--warning { color: #fcd34d; background: rgba(245, 158, 11, 0.12); }
    [data-theme-mode="dark"] .student-user-reward-row__status--danger,
    [data-bs-theme="dark"] .student-user-reward-row__status--danger { color: #fca5a5; background: rgba(220, 38, 38, 0.12); }

    @media (max-width: 991.98px) {
        .student-rewards-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .student-rewards-points__value {
            font-size: 1.5rem;
        }

        .student-reward-card {
            padding: 0.85rem;
        }

        .student-reward-card__footer {
            flex-direction: column;
            align-items: stretch;
        }

        .student-reward-card__footer .btn {
            width: 100%;
        }
    }
</style>
