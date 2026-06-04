<style>
    .question-card {
        transition: all 0.3s ease;
        border-right: 4px solid transparent;
        color: var(--default-text-color);
    }
    .question-card:hover {
        box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.12);
    }
    .question-card.question-card-selected {
        box-shadow: 0 0 0 2px rgb(var(--primary-rgb));
        background-color: rgba(var(--primary-rgb), 0.06);
    }
    [data-theme-mode=dark] .question-card.question-card-selected {
        background-color: rgba(var(--primary-rgb), 0.12);
    }
    .question-bulk-checkbox {
        cursor: pointer;
    }
    .question-bulk-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.45;
    }
    [data-theme-mode=dark] .question-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    .question-card h6,
    .question-card .question-text-body,
    .question-card a.question-text-body {
        color: var(--default-text-color) !important;
    }
    .question-card a.question-text-body:hover {
        color: rgb(var(--primary-rgb)) !important;
    }
    .question-card .text-muted {
        color: var(--text-muted) !important;
    }
    .question-card .question-points-badge {
        background-color: var(--default-background) !important;
        color: var(--default-text-color) !important;
        border: 1px solid var(--default-border);
    }
    .question-card-curriculum thead th {
        color: var(--text-muted);
        border-bottom-color: var(--default-border);
    }
    .question-card-curriculum tbody .fw-semibold {
        color: var(--default-text-color);
    }
    .question-type-single_choice { border-right-color: var(--primary-color) !important; }
    .question-type-multiple_choice { border-right-color: #17a2b8 !important; }
    .question-type-true_false { border-right-color: #28a745 !important; }
    .question-type-short_answer { border-right-color: #ffc107 !important; }
    .question-type-essay { border-right-color: #6c757d !important; }
    .question-type-matching { border-right-color: #dc3545 !important; }
    .question-type-ordering { border-right-color: #343a40 !important; }
    .question-type-fill_blanks { border-right-color: #007bff !important; }
    .question-type-numerical { border-right-color: #17a2b8 !important; }
    .question-type-drag_drop { border-right-color: #fd7e14 !important; }
    .question-card-preview {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .question-card .question-text-body {
        font-size: 0.95rem;
        line-height: 1.65;
    }
    .question-card .question-text-body .katex {
        font-size: 1em;
        padding: 0.06em 0.3em;
        margin: 0 0.08em;
    }
    [data-theme-mode=dark] .question-card .question-text-body .question-inline-code,
    [data-theme-mode=dark] .question-card .question-text-body .katex {
        color: #90caf9;
        background: rgba(144, 202, 249, 0.12);
        border-color: rgba(144, 202, 249, 0.28);
    }
</style>
