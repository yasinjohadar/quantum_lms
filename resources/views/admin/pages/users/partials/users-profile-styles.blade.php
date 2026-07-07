<style>
    .user-profile-page {
        --up-primary: rgb(var(--primary-rgb, 13, 110, 253));
        --up-surface: var(--custom-card-bg, #fff);
        --up-border: var(--default-border, #e2e8f0);
        --up-muted: var(--text-muted, #64748b);
        --up-text: var(--default-text-color, #0f172a);
        --up-radius: 14px;
        padding-bottom: 2rem;
        background:
            radial-gradient(ellipse 70% 40% at 50% 0%, rgba(var(--primary-rgb, 13, 110, 253), 0.08), transparent 65%),
            radial-gradient(ellipse 45% 30% at 100% 80%, rgba(16, 185, 129, 0.06), transparent 55%);
    }

    [data-theme-mode="dark"] .user-profile-page,
    [data-bs-theme="dark"] .user-profile-page {
        --up-surface: var(--custom-card-bg, #111827);
        --up-border: rgba(255, 255, 255, 0.09);
        --up-text: #f1f5f9;
    }

    .user-profile-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin: 1.25rem 0 1.5rem;
    }

    .user-profile-toolbar__title {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0 0 0.25rem;
        color: var(--up-text);
    }

    .user-profile-toolbar .breadcrumb {
        margin: 0;
        font-size: 0.8rem;
    }

    .user-profile-toolbar__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .user-profile-toolbar__actions .btn {
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .user-profile-sidebar {
        background: var(--up-surface);
        border: 1px solid var(--up-border);
        border-radius: calc(var(--up-radius) + 2px);
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.07);
        overflow: hidden;
        height: 100%;
    }

    [data-theme-mode="dark"] .user-profile-sidebar,
    [data-bs-theme="dark"] .user-profile-sidebar {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
    }

    .user-profile-sidebar__banner {
        height: 100px;
        background: linear-gradient(125deg, #1e40af, #2563eb 40%, #0891b2 75%, #059669);
        position: relative;
    }

    .user-profile-sidebar__banner::after {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.07'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E");
    }

    .user-profile-sidebar__body {
        text-align: center;
        padding: 0 1.25rem 1.5rem;
        margin-top: -48px;
        position: relative;
    }

    .user-profile-sidebar__avatar {
        width: 96px;
        height: 96px;
        margin: 0 auto 0.85rem;
        border-radius: 22px;
        border: 4px solid var(--up-surface);
        box-shadow: 0 8px 24px rgba(29, 78, 216, 0.3);
        object-fit: cover;
        background: linear-gradient(145deg, #1d4ed8, #059669);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        overflow: hidden;
    }

    .user-profile-sidebar__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-profile-sidebar__name {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--up-text);
        margin-bottom: 0.35rem;
    }

    .user-profile-sidebar__contact {
        font-size: 0.84rem;
        color: var(--up-muted);
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .user-profile-sidebar__badges {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.35rem;
        margin-bottom: 1rem;
    }

    .user-profile-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .user-profile-badge--role {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
        color: var(--up-primary);
        border: 1px solid rgba(var(--primary-rgb, 13, 110, 253), 0.2);
    }

    .user-profile-badge--active {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.22);
    }

    .user-profile-badge--inactive {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .user-profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--up-border);
    }

    .user-profile-stat {
        text-align: center;
        padding: 0.5rem 0.25rem;
    }

    .user-profile-stat__value {
        display: block;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--up-primary);
        line-height: 1.2;
    }

    .user-profile-stat__label {
        font-size: 0.68rem;
        color: var(--up-muted);
        font-weight: 600;
    }

    .user-profile-panel {
        background: var(--up-surface);
        border: 1px solid var(--up-border);
        border-radius: var(--up-radius);
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    .user-profile-panel__head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--up-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.03);
    }

    .user-profile-panel__head h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--up-text);
    }

    .user-profile-panel__head small {
        display: block;
        font-size: 0.76rem;
        color: var(--up-muted);
        margin-top: 0.15rem;
    }

    .user-profile-panel__head .btn {
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .user-profile-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        padding: 1.25rem;
    }

    .user-profile-info-item label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--up-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .user-profile-info-item span {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--up-text);
    }

    .user-profile-table-wrap {
        overflow-x: auto;
    }

    .user-profile-table {
        width: 100%;
        margin: 0;
        font-size: 0.86rem;
    }

    .user-profile-table thead th {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.04);
        color: var(--up-muted);
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--up-border);
        white-space: nowrap;
    }

    .user-profile-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--up-border);
        color: var(--up-text);
    }

    .user-profile-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-profile-table tbody tr:hover {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);
    }

    .user-profile-table .class-name {
        font-weight: 700;
        font-size: 0.88rem;
    }

    .user-profile-table .stage-name {
        color: var(--up-muted);
        font-size: 0.8rem;
    }

    .user-profile-status {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .user-profile-status--approved { background: rgba(16, 185, 129, 0.12); color: #047857; }
    .user-profile-status--pending { background: rgba(245, 158, 11, 0.15); color: #b45309; }
    .user-profile-status--rejected { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .user-profile-status--active { background: rgba(16, 185, 129, 0.12); color: #047857; }
    .user-profile-status--suspended { background: rgba(100, 116, 139, 0.15); color: var(--up-muted); }
    .user-profile-status--completed { background: rgba(6, 182, 212, 0.15); color: #0e7490; }

    .user-profile-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--up-muted);
        font-size: 0.88rem;
    }

    .user-profile-empty i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    .user-profile-table .btn-detach-class {
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
    }

    @media (max-width: 991px) {
        .user-profile-stats { grid-template-columns: repeat(3, 1fr); }
    }
</style>
