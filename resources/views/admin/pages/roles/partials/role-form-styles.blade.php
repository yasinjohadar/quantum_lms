<style>
    .role-form-page {
        --role-form-radius: 14px;
        --role-form-accent: rgb(var(--primary-rgb, 13, 110, 253));
        --role-form-surface: var(--custom-card-bg, #fff);
        --role-form-border: var(--default-border, #e9ecef);
        --role-form-muted: var(--text-muted, #6c757d);
        --role-form-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
    }

    [data-theme-mode="dark"] .role-form-page,
    [data-bs-theme="dark"] .role-form-page {
        --role-form-surface: var(--custom-card-bg, #111a2e);
        --role-form-border: rgba(255, 255, 255, 0.1);
        --role-form-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .role-form-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--role-form-radius);
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.12) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 100%);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.18);
        box-shadow: 0 8px 24px rgba(var(--primary-rgb, 13, 110, 253), 0.08);
    }

    [data-theme-mode="dark"] .role-form-hero,
    [data-bs-theme="dark"] .role-form-hero {
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 13, 110, 253), 0.18) 0%, rgba(0, 0, 0, 0.15) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    }

    .role-form-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--role-form-accent);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.14);
        flex-shrink: 0;
    }

    .role-form-hero__content {
        flex: 1;
        min-width: 200px;
    }

    .role-form-hero__title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .role-form-hero__subtitle {
        color: var(--role-form-muted);
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    .role-form-hero__stat {
        text-align: center;
        padding: 0.65rem 1.1rem;
        border-radius: 12px;
        background: var(--role-form-surface);
        border: 1px solid var(--role-form-border);
        min-width: 120px;
    }

    .role-form-hero__stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--role-form-accent);
        line-height: 1.2;
    }

    .role-form-hero__stat-label {
        font-size: 0.75rem;
        color: var(--role-form-muted);
    }

    .role-form-card {
        border-radius: var(--role-form-radius);
        border: 1px solid var(--role-form-border);
        background: var(--role-form-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .role-form-card,
    [data-bs-theme="dark"] .role-form-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .role-form-card__header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--role-form-border);
        background: var(--role-form-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .role-form-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        color: var(--role-form-accent);
        font-size: 0.95rem;
    }

    .role-form-card__body {
        padding: 1.25rem;
    }

    .role-form-field .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }

    .role-form-field .form-control,
    .role-form-field .form-select {
        border-radius: 10px;
        border-color: var(--role-form-border);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .role-form-field .form-control:focus,
    .role-form-field .form-select:focus {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.55);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .role-perm-search-wrap {
        position: relative;
    }

    .role-perm-search-wrap .bi-search {
        position: absolute;
        inset-inline-start: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--role-form-muted);
        pointer-events: none;
    }

    .role-perm-search-wrap .form-control {
        padding-inline-start: 2.35rem;
        border-radius: 10px;
        border-color: var(--role-form-border);
    }

    /* Tabs */
    .role-perm-tabs {
        border-bottom: none;
        gap: 0.35rem;
        padding: 0.35rem;
        border-radius: 12px;
        background: var(--role-form-soft);
        border: 1px solid var(--role-form-border);
    }

    .role-perm-tabs .nav-link {
        border: none;
        border-radius: 8px;
        color: var(--role-form-muted);
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.45rem 0.75rem;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .role-perm-tabs .nav-link:hover {
        color: var(--role-form-accent);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
    }

    .role-perm-tabs .nav-link.active {
        color: #fff;
        background: var(--role-form-accent);
        box-shadow: 0 4px 12px rgba(var(--primary-rgb, 13, 110, 253), 0.3);
    }

    [data-theme-mode="dark"] .role-perm-tabs .nav-link.active,
    [data-bs-theme="dark"] .role-perm-tabs .nav-link.active {
        color: #fff;
    }

    .permission-tab-toolbar .btn {
        border-radius: 8px;
        font-size: 0.8125rem;
    }

    /* Accordions */
    .permission-categories-accordion,
    #rolePermissionsSummaryAccordion {
        --bs-accordion-border-radius: 12px;
        --bs-accordion-inner-border-radius: 12px;
    }

    .permission-categories-accordion .accordion-item,
    #role-permissions-summary-panel {
        border: 1px solid var(--role-form-border);
        border-radius: 12px !important;
        margin-bottom: 0.65rem;
        overflow: hidden;
        background: var(--role-form-surface);
        transition: box-shadow 0.2s ease;
    }

    .permission-categories-accordion .accordion-item:hover,
    #role-permissions-summary-panel:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }

    [data-theme-mode="dark"] .permission-categories-accordion .accordion-item:hover,
    [data-theme-mode="dark"] #role-permissions-summary-panel:hover,
    [data-bs-theme="dark"] .permission-categories-accordion .accordion-item:hover,
    [data-bs-theme="dark"] #role-permissions-summary-panel:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    }

    .permission-category-header .accordion-button,
    .role-permissions-summary-header .accordion-button {
        box-shadow: none;
        font-size: 0.9rem;
        background: transparent;
    }

    .permission-category-header .accordion-button:not(.collapsed),
    .role-permissions-summary-header .accordion-button:not(.collapsed) {
        background: var(--role-form-soft);
        color: var(--default-text-color, inherit);
    }

    .permission-category-actions,
    .role-permissions-summary-actions {
        background: var(--role-form-soft);
        border-inline-start: 1px solid var(--role-form-border);
    }

    .permission-category-actions .btn-link,
    .role-permissions-summary-actions .btn-link {
        font-size: 0.8rem;
        text-decoration: none;
    }

    .permission-category-body {
        max-height: 420px;
        overflow-y: auto;
    }

    .permission-categories-accordion .permission-category-badge {
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 6px;
    }

    /* Permission check items */
    .role-perm-check {
        height: 100%;
        padding: 0.65rem 0.75rem;
        border-radius: 10px;
        border: 1px solid var(--role-form-border);
        background: var(--role-form-surface);
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }

    .role-perm-check:hover {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.35);
        background: var(--role-form-soft);
    }

    .role-perm-check:has(.form-check-input:checked) {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        box-shadow: 0 2px 8px rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .role-perm-check .form-check-input {
        margin-top: 0.2rem;
    }

    .role-perm-check .form-check-label {
        cursor: pointer;
        width: 100%;
    }

    .role-perm-check__name {
        font-size: 0.8125rem;
        font-weight: 600;
        word-break: break-word;
        color: var(--role-form-accent);
    }

    .role-perm-check__desc {
        font-size: 0.75rem;
        line-height: 1.45;
    }

    /* Summary list */
    #role-permissions-summary-panel .role-permissions-summary-list-wrap {
        max-height: min(50vh, 480px);
        overflow-y: auto;
    }

    #role-permissions-summary-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 220px), 1fr));
        gap: 0.65rem;
        align-items: stretch;
    }

    #role-permissions-summary-list > li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        border: 1px solid var(--role-form-border);
        border-radius: 10px;
        padding: 0.5rem 0.65rem;
        background: var(--role-form-soft);
    }

    #role-permissions-summary-list .role-permissions-summary-index {
        flex-shrink: 0;
        min-width: 1.6rem;
        font-weight: 700;
        font-size: 0.8rem;
        line-height: 1.4;
        color: var(--role-form-accent);
    }

    #role-permissions-summary-list .role-permissions-summary-item-text {
        min-width: 0;
        flex: 1 1 auto;
    }

    @media (max-width: 767.98px) {
        .permission-category-header {
            flex-direction: column;
        }
        .permission-category-actions,
        .role-permissions-summary-actions {
            border-inline-start: none;
            border-top: 1px solid var(--role-form-border);
            justify-content: center;
            width: 100%;
            padding-block: 0.5rem !important;
        }
        .role-form-hero__stat {
            width: 100%;
        }
    }
</style>
