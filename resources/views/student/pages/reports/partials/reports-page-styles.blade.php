<style>
    .student-reports-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
    }

    .student-reports-toolbar__period {
        min-width: 9.5rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .student-reports-hero {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 14px;
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 0%, rgba(255, 255, 255, 0.98) 55%);
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
    }

    .student-reports-hero__avatar {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid rgba(var(--primary-rgb, 13, 110, 253), 0.15);
        box-shadow: 0 4px 12px rgba(var(--primary-rgb, 13, 110, 253), 0.15);
    }

    .student-reports-hero__avatar img,
    .student-reports-hero__avatar-fallback {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-reports-hero__avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4a7cff 0%, #2563eb 100%);
        color: #fff;
        font-size: 1.6rem;
    }

    .student-reports-hero__body {
        flex: 1;
        min-width: 0;
    }

    .student-reports-hero__name {
        margin: 0 0 0.45rem;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--default-text-color, #0f172a);
    }

    .student-reports-hero__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .student-reports-hero__chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 600;
        color: var(--default-text-color, #334155);
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid var(--default-border);
    }

    .student-reports-hero__chip i {
        color: rgb(var(--primary-rgb, 13, 110, 253));
        font-size: 0.82rem;
    }

    .student-reports-stats .dashboard-stat-card__body {
        padding: 0.85rem 0.95rem;
    }

    .student-reports-stats .dashboard-stat-card__value {
        font-size: 1.15rem;
    }

    .student-reports-stats .dashboard-stat-card__icon {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }

    .student-reports-panel {
        border-radius: 14px;
    }

    .student-reports-panel .card-header {
        padding-bottom: 0.65rem;
    }

    .student-reports-analytics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.65rem;
    }

    .student-reports-analytics__item {
        text-align: center;
        padding: 0.75rem 0.5rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.03);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .student-reports-analytics__item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
    }

    .student-reports-analytics__item i {
        font-size: 1.35rem;
        margin-bottom: 0.35rem;
    }

    .student-reports-analytics__value {
        display: block;
        font-size: 1.1rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .student-reports-analytics__label {
        display: block;
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
        margin-top: 0.15rem;
    }

    .student-reports-attendance {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
    }

    .student-reports-attendance__item {
        text-align: center;
        padding: 0.85rem 0.5rem;
        border-radius: 12px;
        border: 1px solid var(--default-border);
    }

    .student-reports-attendance__value {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 0.15rem;
    }

    .student-reports-attendance__label {
        font-size: 0.75rem;
        color: var(--text-muted, #64748b);
    }

    .student-reports-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .student-reports-table tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
    }

    .student-reports-empty {
        border-radius: 14px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    [data-theme-mode="dark"] .student-reports-hero,
    [data-bs-theme="dark"] .student-reports-hero {
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.12) 0%, rgba(28, 31, 40, 0.98) 55%);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.28);
    }

    [data-theme-mode="dark"] .student-reports-hero__chip,
    [data-bs-theme="dark"] .student-reports-hero__chip {
        background: rgba(255, 255, 255, 0.06);
    }

    @media (max-width: 991.98px) {
        .student-reports-analytics {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 767.98px) {
        .student-reports-hero {
            padding: 0.75rem 0.85rem;
            gap: 0.75rem;
        }

        .student-reports-hero__avatar {
            width: 52px;
            height: 52px;
            border-radius: 12px;
        }

        .student-reports-hero__name {
            font-size: 0.92rem;
        }

        .student-reports-hero__chip {
            font-size: 0.68rem;
            padding: 0.22rem 0.48rem;
        }

        .student-reports-stats .dashboard-stat-card__body {
            padding: 0.7rem 0.75rem;
        }

        .student-reports-stats .dashboard-stat-card__label {
            font-size: 0.68rem;
            margin-bottom: 0.4rem;
        }

        .student-reports-stats .dashboard-stat-card__value {
            font-size: 1rem;
        }

        .student-reports-stats .dashboard-stat-card__meta {
            font-size: 0.68rem;
        }

        .student-reports-stats .dashboard-stat-card__icon {
            width: 34px;
            height: 34px;
            font-size: 0.95rem;
        }

        .reports-chart-wrap {
            min-height: 240px;
        }

        .student-reports-attendance {
            grid-template-columns: 1fr;
        }
    }
</style>
