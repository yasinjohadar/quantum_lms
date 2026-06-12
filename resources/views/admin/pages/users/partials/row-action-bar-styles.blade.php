    .row-action-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.2rem;
        min-width: max-content;
    }

    .row-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        padding: 0;
        border-radius: 7px;
        border: 1px solid transparent;
        background: transparent;
        font-size: 0.78rem;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, transform 0.1s ease;
        flex-shrink: 0;
    }

    .row-action-btn:hover {
        transform: translateY(-1px);
    }

    .row-action-btn:focus-visible {
        outline: 2px solid rgba(var(--primary-rgb, 13, 110, 253), 0.45);
        outline-offset: 1px;
    }

    .row-action-btn--info {
        color: #0ea5e9;
        background: rgba(14, 165, 233, 0.08);
        border-color: rgba(14, 165, 233, 0.15);
    }
    .row-action-btn--info:hover {
        background: rgba(14, 165, 233, 0.16);
        border-color: rgba(14, 165, 233, 0.3);
        color: #0284c7;
    }

    .row-action-btn--primary {
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.08);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.15);
    }
    .row-action-btn--primary:hover {
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.16);
        border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.3);
    }

    .row-action-btn--success {
        color: #059669;
        background: rgba(16, 185, 129, 0.08);
        border-color: rgba(16, 185, 129, 0.15);
    }
    .row-action-btn--success:hover {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(16, 185, 129, 0.3);
        color: #047857;
    }

    .row-action-btn--warning {
        color: #d97706;
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.2);
    }
    .row-action-btn--warning:hover {
        background: rgba(245, 158, 11, 0.18);
        border-color: rgba(245, 158, 11, 0.35);
        color: #b45309;
    }

    .row-action-btn--danger {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.08);
        border-color: rgba(220, 53, 69, 0.15);
    }
    .row-action-btn--danger:hover {
        background: rgba(220, 53, 69, 0.16);
        border-color: rgba(220, 53, 69, 0.3);
        color: #b02a37;
    }

    .row-action-btn--secondary {
        color: var(--text-muted, #6c757d);
        background: rgba(100, 116, 139, 0.08);
        border-color: rgba(100, 116, 139, 0.15);
    }
    .row-action-btn--secondary:hover {
        background: rgba(100, 116, 139, 0.16);
        border-color: rgba(100, 116, 139, 0.28);
        color: var(--default-text-color, #495057);
    }

    [data-theme-mode="dark"] .row-action-btn--info,
    [data-bs-theme="dark"] .row-action-btn--info { color: #7dd3fc; }
    [data-theme-mode="dark"] .row-action-btn--success,
    [data-bs-theme="dark"] .row-action-btn--success { color: #6ee7b7; }
    [data-theme-mode="dark"] .row-action-btn--warning,
    [data-bs-theme="dark"] .row-action-btn--warning { color: #fcd34d; }
    [data-theme-mode="dark"] .row-action-btn--danger,
    [data-bs-theme="dark"] .row-action-btn--danger { color: #fca5a5; }

    .row-action-divider {
        width: 1px;
        height: 18px;
        background: var(--default-border, #dee2e6);
        margin: 0 0.1rem;
        flex-shrink: 0;
        align-self: center;
    }

    [data-theme-mode="dark"] .row-action-divider,
    [data-bs-theme="dark"] .row-action-divider {
        background: rgba(255, 255, 255, 0.12);
    }

    .row-action-form {
        display: inline-flex;
        margin: 0;
        padding: 0;
    }
