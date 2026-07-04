<style>
    .pack-import-module .pack-import-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .pack-import-module .pack-import-step {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: var(--default-background);
        border: 1px solid var(--default-border);
        color: var(--text-muted);
    }

    .pack-import-module .pack-import-step.is-active {
        border-color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.08);
        color: rgb(var(--primary-rgb));
    }

    .pack-import-module .pack-import-step.is-done {
        border-color: rgb(var(--success-rgb));
        background: rgba(var(--success-rgb), 0.08);
        color: rgb(var(--success-rgb));
    }

    .pack-import-module .format-toggle .btn-check:checked + .btn {
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.2);
    }

    .pack-import-upload-area {
        border: 2px dashed var(--default-border);
        border-radius: 12px;
        padding: 2rem 1.25rem;
        text-align: center;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        background: var(--default-background);
        cursor: pointer;
        outline: none;
    }

    .pack-import-upload-area:hover,
    .pack-import-upload-area:focus-visible {
        border-color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.05);
        box-shadow: 0 0 0 0.15rem rgba(var(--primary-rgb), 0.12);
    }

    .pack-import-upload-area.dragover {
        border-color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.1);
        transform: scale(1.01);
    }

    .pack-import-upload-area.has-file {
        border-color: rgb(var(--success-rgb));
        background: rgba(var(--success-rgb), 0.06);
        cursor: default;
    }

    .pack-import-upload-area.has-file:hover {
        box-shadow: none;
    }

    .pack-import-upload-area__format-badge {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        background: rgba(var(--primary-rgb), 0.12);
        color: rgb(var(--primary-rgb));
    }

    .pack-import-module .parse-action-row .btn-parse-file:not(:disabled) {
        background: rgb(var(--primary-rgb));
        border-color: rgb(var(--primary-rgb));
        color: #fff;
    }

    .pack-import-module .parse-action-row .btn-parse-file:not(:disabled):hover {
        filter: brightness(1.05);
    }

    .pack-import-module .preview-table {
        max-height: 360px;
        overflow: auto;
    }
</style>
