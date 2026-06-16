@once('enrollment-page-styles')
    <style>
        .enrollments-page {
            --enrollment-card-radius: 0.9rem;
            --enrollment-shadow: 0 0.25rem 1rem rgba(15, 23, 42, 0.06);
            --enrollment-shadow-hover: 0 0.65rem 1.75rem rgba(15, 23, 42, 0.1);
        }

        .enrollments-page__header-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--primary-rgb), 0.12);
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .enrollment-stats-panel {
            border: 1px solid var(--default-border);
            border-radius: var(--enrollment-card-radius);
            background: var(--custom-white);
            box-shadow: var(--enrollment-shadow);
            overflow: hidden;
        }

        .enrollment-stats-panel__item {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .enrollment-stats-panel__item + .enrollment-stats-panel__item {
            border-inline-start: 1px solid var(--default-border);
        }

        @media (max-width: 575.98px) {
            .enrollment-stats-panel__item + .enrollment-stats-panel__item {
                border-inline-start: 0;
                border-top: 1px solid var(--default-border);
            }
        }

        .enrollment-stats-panel__icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .enrollment-stats-panel__icon--classes {
            background: rgba(var(--primary-rgb), 0.12);
            color: var(--primary-color);
        }

        .enrollment-stats-panel__icon--subjects {
            background: rgba(var(--success-rgb), 0.12);
            color: rgb(var(--success-rgb));
        }

        .enrollment-stats-panel__value {
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .enrollment-stage-section {
            border: 1px solid var(--default-border);
            border-radius: var(--enrollment-card-radius);
            background: var(--custom-white);
            box-shadow: var(--enrollment-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .enrollment-stage-section__header {
            padding: 0.9rem 1.25rem;
            background: linear-gradient(
                90deg,
                rgba(var(--primary-rgb), 0.1) 0%,
                rgba(var(--primary-rgb), 0.03) 100%
            );
            border-bottom: 1px solid var(--default-border);
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .enrollment-stage-section__header-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: #fff;
        }

        .enrollment-stage-section__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--default-text-color);
        }

        .enrollment-stage-section__body {
            padding: 1.25rem;
        }

        .enrollment-class-card {
            height: 100%;
            border: 1px solid var(--default-border);
            border-radius: var(--enrollment-card-radius);
            background: var(--custom-white);
            box-shadow: var(--enrollment-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .enrollment-class-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--enrollment-shadow-hover);
            border-color: rgba(var(--primary-rgb), 0.25);
        }

        .enrollment-class-card__media {
            position: relative;
            height: 168px;
            overflow: hidden;
        }

        .enrollment-class-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .enrollment-class-card:hover .enrollment-class-card__media img {
            transform: scale(1.04);
        }

        .enrollment-class-card__media-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, rgba(var(--primary-rgb), 0.75) 100%);
            color: #fff;
            font-size: 3.25rem;
        }

        .enrollment-class-card__badge {
            position: absolute;
            top: 0.65rem;
            inset-inline-start: 0.65rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.12);
        }

        .enrollment-class-card__badge--paid {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
        }

        .enrollment-class-card__badge--free {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
        }

        .enrollment-class-card__badge.position-static {
            position: static;
            box-shadow: none;
        }

        .enrollment-class-card__body {
            padding: 1rem 1rem 0.85rem;
            flex-grow: 1;
        }

        .enrollment-class-card__title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--default-text-color);
            margin-bottom: 0.35rem;
            text-decoration: none;
        }

        .enrollment-class-card__title:hover {
            color: var(--primary-color);
        }

        .enrollment-class-card__desc {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 0.75rem;
            min-height: 2.5rem;
        }

        .enrollment-class-card__meta {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.6rem;
            border-radius: 0.45rem;
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-muted);
            background: rgba(var(--primary-rgb), 0.06);
            border: 1px solid rgba(var(--primary-rgb), 0.1);
        }

        .enrollment-class-card__actions {
            padding: 0 1rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .enrollment-class-card__btn-join {
            border-radius: 0.55rem;
            font-weight: 600;
            padding: 0.45rem 0.85rem;
            box-shadow: 0 0.2rem 0.65rem rgba(var(--primary-rgb), 0.25);
        }

        .enrollment-class-card__btn-pending {
            border-radius: 0.55rem;
            font-weight: 600;
        }

        .enrollment-class-card__footer {
            padding: 0.7rem 1rem;
            border-top: 1px solid var(--default-border);
            background: rgba(var(--primary-rgb), 0.03);
        }

        .enrollment-class-card__footer-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .enrollment-class-card__footer-link:hover {
            color: rgba(var(--primary-rgb), 0.85);
        }

        .enrollment-subject-card {
            height: 100%;
            border: 1px solid var(--default-border);
            border-radius: var(--enrollment-card-radius);
            background: var(--custom-white);
            box-shadow: var(--enrollment-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .enrollment-subject-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--enrollment-shadow-hover);
        }

        .enrollment-subject-card__media {
            height: 140px;
            overflow: hidden;
        }

        .enrollment-subject-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .enrollment-subject-card__media-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.85) 0%, var(--primary-color) 100%);
            color: #fff;
            font-size: 2.5rem;
        }

        .enrollment-subject-card__body {
            padding: 1rem;
            flex-grow: 1;
        }

        .enrollment-subject-card__footer {
            padding: 0.65rem 1rem;
            border-top: 1px solid var(--default-border);
            background: rgba(var(--primary-rgb), 0.03);
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .enrollment-class-hero {
            border: 1px solid var(--default-border);
            border-radius: var(--enrollment-card-radius);
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.08) 0%, var(--custom-white) 60%);
            box-shadow: var(--enrollment-shadow);
            padding: 1.15rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .enrollment-empty-state {
            border: 1px dashed var(--default-border);
            border-radius: var(--enrollment-card-radius);
            background: var(--custom-white);
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .enrollment-empty-state__icon {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
            font-size: 1.75rem;
        }
    </style>
@endonce
