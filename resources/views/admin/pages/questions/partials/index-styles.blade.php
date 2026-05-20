<style>
    .question-card {
        transition: all 0.3s ease;
        border-right: 4px solid transparent;
    }
    .question-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
</style>
