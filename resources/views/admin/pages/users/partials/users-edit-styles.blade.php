<style>
    .user-edit-page {
        --ue-primary: rgb(var(--primary-rgb, 13, 110, 253));
        --ue-surface: var(--custom-card-bg, #fff);
        --ue-border: var(--default-border, #e2e8f0);
        --ue-muted: var(--text-muted, #64748b);
        --ue-text: var(--default-text-color, #0f172a);
        --ue-radius: 16px;
        min-height: calc(100vh - 80px);
        background:
            radial-gradient(ellipse 70% 45% at 50% 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.1), transparent 70%),
            radial-gradient(ellipse 50% 35% at 0% 100%, rgba(16, 185, 129, 0.07), transparent 60%);
    }

    [data-theme-mode="dark"] .user-edit-page,
    [data-bs-theme="dark"] .user-edit-page {
        --ue-surface: var(--custom-card-bg, #111827);
        --ue-border: rgba(255, 255, 255, 0.09);
        --ue-text: #f1f5f9;
    }

    .user-edit-layout {
        width: 100%;
        max-width: 480px;
        margin-inline: auto;
        padding: 1.25rem 0 2.5rem;
    }

    .user-edit-layout--wide { max-width: 680px; }

    .user-edit-breadcrumb {
        margin-bottom: 1.25rem;
    }

    .user-edit-breadcrumb .breadcrumb {
        margin-bottom: 0;
        font-size: 0.8rem;
    }

    .user-edit-card {
        width: 100%;
        background: var(--ue-surface);
        border: 1px solid var(--ue-border);
        border-radius: calc(var(--ue-radius) + 4px);
        box-shadow:
            0 1px 3px rgba(15, 23, 42, 0.05),
            0 20px 50px rgba(15, 23, 42, 0.1);
        overflow: hidden;
    }

    [data-theme-mode="dark"] .user-edit-card,
    [data-bs-theme="dark"] .user-edit-card {
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }

    .user-edit-card__banner {
        position: relative;
        height: 128px;
        background: linear-gradient(125deg, #1e40af 0%, #2563eb 35%, #0891b2 70%, #059669 100%);
        overflow: hidden;
    }

    .user-edit-card__banner::before,
    .user-edit-card__banner::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .user-edit-card__banner::before {
        width: 180px;
        height: 180px;
        top: -60px;
        inset-inline-end: -40px;
    }

    .user-edit-card__banner::after {
        width: 120px;
        height: 120px;
        bottom: -50px;
        inset-inline-start: 20px;
    }

    .user-edit-card__profile {
        text-align: center;
        padding: 0 1.5rem;
        margin-top: -52px;
        position: relative;
        z-index: 1;
    }

    .user-edit-card__avatar {
        width: 96px;
        height: 96px;
        margin: 0 auto 1rem;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(145deg, #1d4ed8, #0d9488);
        border: 4px solid var(--ue-surface);
        box-shadow: 0 10px 28px rgba(29, 78, 216, 0.4);
    }

    .user-edit-card__title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--ue-text);
        margin: 0 0 0.25rem;
    }

    .user-edit-card__name {
        font-size: 0.9rem;
        color: var(--ue-muted);
        margin: 0 0 1rem;
    }

    .user-edit-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .user-edit-status--student {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.22);
    }

    .user-edit-status--locked {
        background: rgba(100, 116, 139, 0.1);
        color: var(--ue-muted);
        border: 1px solid var(--ue-border);
    }

    .user-edit-card__form {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .user-edit-info {
        display: flex;
        gap: 0.65rem;
        align-items: flex-start;
        padding: 0.85rem 1rem;
        margin-bottom: 1.25rem;
        border-radius: 12px;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        font-size: 0.82rem;
        line-height: 1.55;
        color: var(--ue-muted);
    }

    .user-edit-info__icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.12);
        color: var(--ue-primary);
        font-size: 0.95rem;
    }

    .user-edit-field {
        margin-bottom: 1rem;
    }

    .user-edit-field label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--ue-muted);
        margin-bottom: 0.4rem;
    }

    .user-edit-field .form-control {
        border-radius: 11px;
        border: 1.5px solid var(--ue-border);
        padding: 0.7rem 0.95rem;
        font-size: 0.95rem;
        min-height: 48px;
        background: var(--ue-surface);
        color: var(--ue-text);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .user-edit-field .form-control:focus {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .user-edit-field .input-group {
        border-radius: 11px;
        overflow: hidden;
        border: 1.5px solid var(--ue-border);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .user-edit-field .input-group:focus-within {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 13, 110, 253), 0.12);
    }

    .user-edit-field .input-group .form-control {
        border: none;
        box-shadow: none;
        min-height: 46px;
    }

    .user-edit-field .input-group .form-control:focus {
        box-shadow: none;
    }

    .user-edit-field .input-group-text {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.05);
        border: none;
        color: var(--ue-muted);
        padding-inline: 0.9rem;
    }

    .user-edit-field .input-group.is-invalid {
        border-color: #dc3545;
    }

    .user-edit-actions {
        display: flex;
        gap: 0.65rem;
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--ue-border);
    }

    .user-edit-actions .btn {
        flex: 1;
        border-radius: 11px;
        font-weight: 700;
        padding: 0.72rem 1rem;
        font-size: 0.9rem;
    }

    .user-edit-actions .btn-save {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        color: #fff;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
    }

    .user-edit-actions .btn-save:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: #fff;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
    }

    .user-edit-actions .btn-cancel {
        background: transparent;
        border: 1.5px solid var(--ue-border);
        color: var(--ue-muted);
    }

    .user-edit-actions .btn-cancel:hover {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.04);
        color: var(--ue-text);
    }

    .user-edit-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        gap: 0.5rem;
    }

    .user-edit-toolbar a {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--ue-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.65rem;
        border-radius: 8px;
        transition: background 0.15s, color 0.15s;
    }

    .user-edit-toolbar a:hover {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        color: var(--ue-primary);
    }

    .user-edit-roles {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .user-edit-role-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.75rem;
        border-radius: 9px;
        border: 1.5px solid var(--ue-border);
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        margin: 0;
        transition: all 0.15s;
    }

    .user-edit-role-chip:has(input:checked) {
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.45);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        color: var(--ue-primary);
    }

    .user-edit-role-chip input { display: none; }

    .user-edit-switch {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.8rem 1rem;
        border-radius: 11px;
        border: 1.5px solid var(--ue-border);
        margin-bottom: 1rem;
    }

    .user-edit-subscriptions {
        margin: 1.25rem 0 0.5rem;
        border: 1.5px solid var(--ue-border);
        border-radius: 14px;
        overflow: hidden;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);
    }

    .user-edit-subscriptions__head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.06);
        border-bottom: 1px solid var(--ue-border);
    }

    .user-edit-subscriptions__head > i {
        font-size: 1.2rem;
        color: var(--ue-primary);
        margin-top: 0.1rem;
    }

    .user-edit-subscriptions__head strong {
        display: block;
        font-size: 0.88rem;
        color: var(--ue-text);
        margin-bottom: 0.15rem;
    }

    .user-edit-subscriptions__head span {
        font-size: 0.76rem;
        color: var(--ue-muted);
    }

    .user-edit-sub-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--ue-border);
        transition: background 0.15s;
    }

    .user-edit-sub-row:last-child { border-bottom: none; }

    .user-edit-sub-row:hover {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.03);
    }

    .user-edit-sub-row--expired {
        background: rgba(220, 53, 69, 0.04);
    }

    .user-edit-sub-row__class {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        min-width: 0;
        flex: 1;
    }

    .user-edit-sub-row__name {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--ue-text);
    }

    .user-edit-sub-row__badge {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: rgba(100, 116, 139, 0.12);
        color: var(--ue-muted);
    }

    .user-edit-sub-row__badge--danger {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }

    .user-edit-sub-row__date {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-shrink: 0;
    }

    .user-edit-sub-date {
        border: 1.5px solid var(--ue-border);
        border-radius: 10px;
        padding: 0.4rem 0.6rem;
        font-size: 0.82rem;
        min-width: 148px;
        background: var(--ue-surface);
        color: var(--ue-text);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .user-edit-sub-date:focus {
        outline: none;
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .user-edit-sub-row__readonly {
        font-size: 0.84rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .user-edit-sub-row__status {
        width: 1.25rem;
        text-align: center;
        font-size: 0.9rem;
    }

    .user-edit-sub-row__status--ok { color: #059669; }
    .user-edit-sub-row__status--err { color: #dc3545; }
    .user-edit-sub-row__status--loading { color: var(--ue-muted); }

    .user-edit-subscriptions__empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1.5rem;
        color: var(--ue-muted);
        font-size: 0.85rem;
    }

    @media (max-width: 576px) {
        .user-edit-sub-row {
            flex-direction: column;
            align-items: stretch;
        }

        .user-edit-sub-row__date {
            justify-content: space-between;
        }

        .user-edit-sub-date { flex: 1; }
    }

    @media (max-width: 576px) {
        .user-edit-layout { padding-inline: 0.25rem; }
        .user-edit-card__form { padding-inline: 1.15rem; }
        .user-edit-actions { flex-direction: column-reverse; }
    }
</style>
