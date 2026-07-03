<style>
    .excel-import-wizard .upload-area {
        border: 2px dashed var(--default-border);
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: var(--default-background);
        color: var(--default-text-color);
        cursor: pointer;
    }
    .excel-import-wizard .upload-area h5 {
        color: var(--default-text-color);
    }
    .excel-import-wizard .upload-area:hover,
    .excel-import-wizard .upload-area.dragover {
        border-color: rgb(var(--primary-rgb));
        background: var(--primary005);
    }
    .excel-import-wizard .upload-area.has-file {
        border-color: rgb(var(--success-rgb));
        background: rgba(var(--success-rgb), 0.08);
    }
    .excel-import-wizard .preview-table {
        max-height: 400px;
        overflow-y: auto;
    }
    .excel-import-wizard .column-mapping {
        border: 1px solid var(--default-border);
        border-radius: 8px;
        padding: 16px;
        background: var(--default-background);
    }
    .excel-import-wizard .mapping-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--custom-white);
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid var(--default-border);
    }
    .excel-import-wizard .mapping-item:hover {
        border-color: rgb(var(--primary-rgb));
        box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.12);
    }
    .excel-import-wizard .file-info {
        background: linear-gradient(135deg, rgb(var(--primary-rgb)) 0%, rgba(var(--primary-rgb), 0.75) 100%);
        color: #fff;
        border-radius: 8px;
        padding: 16px;
    }
    .excel-import-wizard .required-field {
        color: rgb(var(--danger-rgb));
    }
    .excel-import-wizard .import-steps-bar {
        width: 100%;
        border-bottom: 0;
    }
    .excel-import-wizard .import-steps-bar .nav-item {
        flex: 1 1 0;
        min-width: 0;
        margin-inline-end: 0.5rem;
    }
    .excel-import-wizard .import-steps-bar .nav-item:last-child {
        margin-inline-end: 0;
    }
    .excel-import-wizard .import-steps-bar .nav-link {
        justify-content: center;
        width: 100%;
        pointer-events: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    .excel-import-wizard .import-steps-bar .nav-item.completed .nav-link {
        color: rgb(var(--success-rgb));
        border-color: rgb(var(--success-rgb)) !important;
    }
    .excel-import-wizard .import-steps-bar .nav-item.completed .nav-link i {
        border-color: rgb(var(--success-rgb));
        color: rgb(var(--success-rgb));
    }
    @media (max-width: 991.98px) {
        .excel-import-wizard .import-steps-bar {
            overflow-x: auto;
            flex-wrap: nowrap;
        }
        .excel-import-wizard .import-steps-bar .nav-item {
            flex: 0 0 auto;
            min-width: 130px;
        }
    }
</style>
