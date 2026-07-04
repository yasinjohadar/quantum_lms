<style>
    .student-content-breadcrumb {
        --scb-accent: rgb(var(--primary-rgb, 13, 110, 253));
        --scb-accent-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        --scb-border: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        position: relative;
        padding: 1rem 1.15rem 1.05rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 250, 255, 0.98) 100%);
        border: 1px solid var(--scb-border);
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        overflow: hidden;
    }

    .student-content-breadcrumb::before {
        content: '';
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--scb-accent) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.35) 100%);
        border-radius: 0 4px 4px 0;
    }

    .student-content-breadcrumb__trail {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.15rem;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 0.82rem;
    }

    .student-content-breadcrumb__item {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        max-width: 100%;
    }

    .student-content-breadcrumb__link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        color: var(--default-text-color, #334155);
        text-decoration: none;
        font-weight: 600;
        transition: background 0.18s ease, color 0.18s ease, transform 0.15s ease;
        max-width: 14rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .student-content-breadcrumb__link:hover {
        background: var(--scb-accent-soft);
        color: var(--scb-accent);
        transform: translateY(-1px);
    }

    .student-content-breadcrumb__link i {
        font-size: 0.95rem;
        opacity: 0.85;
        flex-shrink: 0;
    }

    .student-content-breadcrumb__sep {
        color: rgba(var(--primary-rgb, 13, 110, 253), 0.35);
        font-size: 0.7rem;
        user-select: none;
        flex-shrink: 0;
    }

    .student-content-breadcrumb__current {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.32rem 0.65rem;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--scb-accent-soft) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 100%);
        color: var(--scb-accent);
        font-weight: 700;
        max-width: min(100%, 18rem);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .student-content-breadcrumb__current i {
        flex-shrink: 0;
    }

    .student-content-breadcrumb__heading {
        margin: 0.75rem 0 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--default-text-color, #0f172a);
        letter-spacing: 0.01em;
    }

    .student-content-breadcrumb__meta {
        margin: 0.25rem 0 0;
        font-size: 0.78rem;
        color: var(--text-muted, #64748b);
    }

    [data-theme-mode="dark"] .student-content-breadcrumb,
    [data-bs-theme="dark"] .student-content-breadcrumb {
        background: linear-gradient(135deg, rgba(28, 31, 40, 0.95) 0%, rgba(18, 20, 26, 0.98) 100%);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35);
    }

    [data-theme-mode="dark"] .student-content-breadcrumb__link,
    [data-bs-theme="dark"] .student-content-breadcrumb__link {
        color: #cbd5e1;
    }

    [data-theme-mode="dark"] .student-content-breadcrumb__heading,
    [data-bs-theme="dark"] .student-content-breadcrumb__heading {
        color: #f1f5f9;
    }

    @media (max-width: 767.98px) {
        .student-content-breadcrumb {
            padding: 0.55rem 0.65rem 0.5rem;
            margin-bottom: 0.75rem !important;
            border-radius: 0.65rem;
        }

        .student-content-breadcrumb::before {
            width: 3px;
        }

        .student-content-breadcrumb__trail {
            gap: 0.2rem 0.08rem;
            font-size: 0.7rem;
        }

        .student-content-breadcrumb__item {
            gap: 0.2rem;
        }

        .student-content-breadcrumb__link {
            gap: 0.22rem;
            padding: 0.16rem 0.38rem;
            max-width: 7.5rem;
            font-weight: 600;
        }

        .student-content-breadcrumb__link i {
            font-size: 0.78rem;
        }

        .student-content-breadcrumb__sep {
            font-size: 0.55rem;
        }

        .student-content-breadcrumb__current {
            gap: 0.22rem;
            padding: 0.18rem 0.42rem;
            max-width: 9rem;
            font-weight: 700;
        }

        .student-content-breadcrumb__current i {
            font-size: 0.78rem;
        }

        .student-content-breadcrumb__heading {
            margin-top: 0.45rem;
            font-size: 0.92rem;
        }

        .student-content-breadcrumb__heading i {
            font-size: 0.88rem;
        }

        .student-content-breadcrumb__meta {
            margin-top: 0.15rem;
            font-size: 0.68rem;
        }
    }

    @media (max-width: 575.98px) {
        .student-content-breadcrumb {
            padding: 0.45rem 0.55rem 0.42rem;
            margin-bottom: 0.6rem !important;
            border-radius: 0.55rem;
        }

        .student-content-breadcrumb__trail {
            font-size: 0.65rem;
        }

        .student-content-breadcrumb__link {
            max-width: 6.5rem;
            padding: 0.14rem 0.32rem;
        }

        .student-content-breadcrumb__link i,
        .student-content-breadcrumb__current i {
            font-size: 0.72rem;
        }

        .student-content-breadcrumb__current {
            max-width: 7.5rem;
            padding: 0.16rem 0.36rem;
        }

        .student-content-breadcrumb__heading {
            margin-top: 0.35rem;
            font-size: 0.85rem;
        }

        .student-content-breadcrumb__heading i {
            font-size: 0.82rem;
        }
    }
</style>
