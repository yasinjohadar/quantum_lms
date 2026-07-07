@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .users-index-page {
        --ui-radius: 14px;
        --ui-accent: rgb(var(--primary-rgb, 13, 110, 253));
        --ui-surface: var(--custom-card-bg, #fff);
        --ui-border: var(--default-border, #e9ecef);
        --ui-muted: var(--text-muted, #6c757d);
        --ui-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
    }

    [data-theme-mode="dark"] .users-index-page,
    [data-bs-theme="dark"] .users-index-page {
        --ui-surface: var(--custom-card-bg, #111a2e);
        --ui-border: rgba(255, 255, 255, 0.1);
        --ui-soft: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .users-index-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--ui-radius);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.16) 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.06) 100%);
        border: 1px solid rgba(16, 185, 129, 0.25);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .users-index-hero,
    [data-bs-theme="dark"] .users-index-hero {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.18) 0%, rgba(0, 0, 0, 0.12) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .users-index-hero__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #059669;
        background: rgba(16, 185, 129, 0.14);
        flex-shrink: 0;
    }

    [data-theme-mode="dark"] .users-index-hero__icon,
    [data-bs-theme="dark"] .users-index-hero__icon {
        color: #6ee7b7;
    }

    .users-index-hero__content { flex: 1; min-width: 200px; }
    .users-index-hero__title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem; }
    .users-index-hero__subtitle { color: var(--ui-muted); font-size: 0.875rem; margin-bottom: 0; }

    .users-index-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .users-index-hero__actions .btn { border-radius: 10px; font-weight: 600; }

    .users-index-stat-mini {
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        min-width: 110px;
    }

    .users-index-stat-mini__value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #059669;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .users-index-stat-mini__value,
    [data-bs-theme="dark"] .users-index-stat-mini__value { color: #6ee7b7; }

    .users-index-stat-mini__label { font-size: 0.72rem; color: var(--ui-muted); }

    .users-index-card {
        border-radius: var(--ui-radius);
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .users-index-card,
    [data-bs-theme="dark"] .users-index-card {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .users-index-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--ui-border);
        background: var(--ui-soft);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .users-index-card__header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        color: var(--ui-accent);
    }

    .users-index-card__body { padding: 1.25rem; }

    .users-index-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--ui-muted);
        margin-bottom: 0.3rem;
    }

    .users-index-filters .form-control,
    .users-index-filters .form-select {
        border-radius: 10px;
        border-color: var(--ui-border);
        font-size: 0.875rem;
    }

    .users-index-filters .form-control:focus,
    .users-index-filters .form-select:focus {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .users-bulk-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        background: rgba(245, 158, 11, 0.08);
        border: 1px dashed rgba(245, 158, 11, 0.35);
        margin-bottom: 1rem;
    }

    [data-theme-mode="dark"] .users-bulk-toolbar,
    [data-bs-theme="dark"] .users-bulk-toolbar {
        background: rgba(245, 158, 11, 0.1);
    }

    .users-index-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--ui-border);
        overflow: hidden;
    }

    .users-index-table { margin-bottom: 0; }

    .users-index-table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--ui-muted);
        background: var(--ui-soft);
        border-bottom: 1px solid var(--ui-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .users-index-table tbody td,
    .users-index-table tbody th {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--ui-border);
    }

    .users-index-table tbody tr { transition: background 0.15s ease; }
    .users-index-table tbody tr:hover { background: var(--ui-soft); }
    .users-index-table tbody tr:last-child td,
    .users-index-table tbody tr:last-child th { border-bottom: none; }

    .users-table.hide-classes-col .users-classes-col { display: none; }

    .ui-user-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .ui-user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
        color: #fff;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
    }

    .ui-user-name {
        font-weight: 600;
        color: var(--default-text-color, inherit);
        text-decoration: none;
    }

    .ui-user-name:hover { color: var(--ui-accent); }

    .ui-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: opacity 0.15s ease;
    }

    .ui-status-badge:hover { opacity: 0.85; }

    .ui-status-badge--active {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .ui-status-badge--inactive {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }

    [data-theme-mode="dark"] .ui-status-badge--active,
    [data-bs-theme="dark"] .ui-status-badge--active { color: #6ee7b7; }
    [data-theme-mode="dark"] .ui-status-badge--inactive,
    [data-bs-theme="dark"] .ui-status-badge--inactive { color: #fca5a5; }

    .subscription-expires-inline {
        border: 1px dashed rgba(13, 110, 253, 0.35);
        background: rgba(13, 110, 253, 0.06);
        color: #0d6efd;
        border-radius: 8px;
        padding: 0.3rem 0.55rem;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .subscription-expires-inline:hover {
        background: rgba(13, 110, 253, 0.12);
        border-color: rgba(13, 110, 253, 0.55);
    }

    .subscription-expires-inline--expired {
        border-color: rgba(220, 53, 69, 0.35);
        background: rgba(220, 53, 69, 0.08);
        color: #dc3545;
    }

    .subscription-expires-inline--unset {
        border-style: dashed;
        color: #6c757d;
        background: rgba(108, 117, 125, 0.06);
    }

    .subscription-expires-inline--unset:hover {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.08);
    }

    .subscription-expires-inline__hint {
        font-size: 0.65rem;
        opacity: 0.8;
    }

    .subscription-expires-input {
        width: 9.5rem;
        font-size: 0.78rem;
        padding: 0.2rem 0.45rem;
        border-radius: 8px;
    }

    .subscription-expires-readonly {
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
    }

    .ui-class-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.55rem;
        border-radius: 8px;
        margin: 0.15rem 0.1rem;
    }

    .ui-class-pill--approved {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
    }

    .ui-class-pill--pending {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }

    [data-theme-mode="dark"] .ui-class-pill--approved,
    [data-bs-theme="dark"] .ui-class-pill--approved { color: #7dd3fc; }
    [data-theme-mode="dark"] .ui-class-pill--pending,
    [data-bs-theme="dark"] .ui-class-pill--pending { color: #fcd34d; }

    .ui-class-pill .detach-class-btn {
        padding: 0 0.25rem;
        font-size: 0.7rem;
        line-height: 1;
        border: none;
        background: transparent;
        color: inherit;
        opacity: 0.7;
    }

    .ui-class-pill .detach-class-btn:hover { opacity: 1; }

    .ui-phone-link { font-size: 0.85rem; font-weight: 500; }

    @include('admin.pages.users.partials.row-action-bar-styles')

    .users-index-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--ui-muted);
    }

    .users-index-empty i {
        font-size: 2.5rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 0.75rem;
    }

    .users-index-pagination { padding-top: 1rem; }

    /* مودال إضافة طالب */
    #quickAddStudentModal .modal-dialog.quick-add-student-dialog {
        max-height: calc(100vh - 2rem);
        margin: 1rem auto;
    }
    #quickAddStudentModal .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #quickAddStudentModal .modal-content > form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        max-height: 100%;
        overflow: hidden;
    }
    #quickAddStudentModal.modal { overflow-y: auto; }
    #quickAddStudentModal .modal-header,
    #quickAddStudentModal .modal-footer { flex-shrink: 0; }
    #quickAddStudentModal .modal-body {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        flex: 1 1 auto;
        min-height: 0;
    }
    #quickAddStudentModal .quick-add-subjects-select { max-height: 11rem; }

    @media (max-width: 767.98px) {
        .users-index-hero__actions { width: 100%; }
        .users-index-hero__actions .btn { flex: 1; }
    }
</style>
