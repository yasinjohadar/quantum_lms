<style>
    .student-stats-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.15rem;
        border-radius: 14px;
        margin-bottom: 1rem;
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.1) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.02) 100%);
    }

    .student-stats-total__label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.15rem;
    }

    .student-stats-total__value {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
        color: rgb(var(--primary-rgb, 13, 110, 253));
    }

    .student-stats-total__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        flex-shrink: 0;
    }

    .student-stats-breakdown {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .student-stats-breakdown-card {
        text-align: center;
        padding: 0.85rem 0.5rem;
        border-radius: 14px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .student-stats-breakdown-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    .student-stats-breakdown-card__icon {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin: 0 auto 0.5rem;
    }

    .student-stats-breakdown-card__label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.25rem;
    }

    .student-stats-breakdown-card__value {
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--default-text-color, #0f172a);
    }

    .student-stats-breakdown-card--attendance .student-stats-breakdown-card__icon { color: rgb(var(--primary-rgb, 13, 110, 253)); background: rgba(var(--primary-rgb, 13, 110, 253), 0.12); }
    .student-stats-breakdown-card--lessons .student-stats-breakdown-card__icon { color: #059669; background: rgba(5, 150, 105, 0.12); }
    .student-stats-breakdown-card--quiz .student-stats-breakdown-card__icon { color: #0284c7; background: rgba(2, 132, 199, 0.12); }
    .student-stats-breakdown-card--question .student-stats-breakdown-card__icon { color: #d97706; background: rgba(217, 119, 6, 0.12); }
    .student-stats-breakdown-card--achievement .student-stats-breakdown-card__icon { color: #7c3aed; background: rgba(124, 58, 237, 0.12); }

    .student-stats-history {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .student-stats-history-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: var(--custom-card-bg, var(--default-background, #fff));
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        animation: studentGamificationReveal 0.4s ease backwards;
    }

    .student-stats-history-row:hover {
        transform: translateX(-3px);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.18);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .student-stats-history-row__icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .student-stats-history-row__main {
        flex: 1;
        min-width: 0;
    }

    .student-stats-history-row__type {
        margin: 0 0 0.1rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
    }

    .student-stats-history-row__date {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }

    .student-stats-history-row__points {
        font-size: 0.95rem;
        font-weight: 800;
        white-space: nowrap;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .student-stats-history-row__points--positive {
        color: #059669;
        background: rgba(5, 150, 105, 0.1);
    }

    .student-stats-history-row__points--negative {
        color: #dc2626;
        background: rgba(220, 38, 38, 0.08);
    }

    [data-theme-mode="dark"] .student-stats-total,
    [data-bs-theme="dark"] .student-stats-total {
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.18) 0%, rgba(28, 31, 40, 0.95) 100%);
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme-mode="dark"] .student-stats-total__value,
    [data-bs-theme="dark"] .student-stats-total__value,
    [data-theme-mode="dark"] .student-stats-total__icon,
    [data-bs-theme="dark"] .student-stats-total__icon {
        color: #93c5fd;
    }

    [data-theme-mode="dark"] .student-stats-breakdown-card,
    [data-bs-theme="dark"] .student-stats-breakdown-card,
    [data-theme-mode="dark"] .student-stats-history-row,
    [data-bs-theme="dark"] .student-stats-history-row {
        background: var(--custom-card-bg, #1c1f28);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }

    [data-theme-mode="dark"] .student-stats-breakdown-card__value,
    [data-bs-theme="dark"] .student-stats-breakdown-card__value,
    [data-theme-mode="dark"] .student-stats-history-row__type,
    [data-bs-theme="dark"] .student-stats-history-row__type {
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .student-stats-breakdown-card__label,
    [data-bs-theme="dark"] .student-stats-breakdown-card__label,
    [data-theme-mode="dark"] .student-stats-history-row__date,
    [data-bs-theme="dark"] .student-stats-history-row__date {
        color: #94a3b8;
    }

    [data-theme-mode="dark"] .student-stats-history-row__points--positive,
    [data-bs-theme="dark"] .student-stats-history-row__points--positive { color: #6ee7b7; background: rgba(5, 150, 105, 0.12); }
    [data-theme-mode="dark"] .student-stats-history-row__points--negative,
    [data-bs-theme="dark"] .student-stats-history-row__points--negative { color: #fca5a5; background: rgba(220, 38, 38, 0.12); }

    @media (max-width: 1199.98px) {
        .student-stats-breakdown {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 767.98px) {
        .student-stats-total__value {
            font-size: 1.45rem;
        }

        .student-stats-breakdown {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .student-stats-breakdown-card {
            padding: 0.65rem 0.4rem;
        }

        .student-stats-breakdown-card__value {
            font-size: 1rem;
        }

        .student-stats-history-row {
            flex-wrap: wrap;
        }

        .student-stats-history-row__points {
            width: 100%;
            text-align: center;
        }
    }
</style>
