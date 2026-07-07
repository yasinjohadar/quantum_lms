<style>
    .user-sub-date {
        border: 1.5px solid var(--up-border, #e2e8f0);
        border-radius: 9px;
        padding: 0.35rem 0.55rem;
        font-size: 0.8rem;
        min-width: 140px;
        max-width: 100%;
        background: var(--up-surface, #fff);
        color: var(--up-text, #0f172a);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .user-sub-date:focus {
        outline: none;
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .user-sub-readonly {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.82rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .user-sub-readonly--expired { color: #dc3545; }
    .user-sub-readonly--class { color: var(--up-muted, #64748b); }

    .user-sub-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.12rem 0.4rem;
        border-radius: 999px;
        background: rgba(100, 116, 139, 0.12);
        color: var(--up-muted, #64748b);
        margin-inline-start: 0.25rem;
    }

    .user-sub-pill--danger {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }

    .user-sub-status {
        display: inline-block;
        width: 1.1rem;
        text-align: center;
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .user-sub-status--ok { color: #059669; }
    .user-sub-status--err { color: #dc3545; }

    .user-sub-cell-wrap {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }
</style>
