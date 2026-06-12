<style>
    .mcq-options-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .mcq-option-card {
        display: flex;
        align-items: stretch;
        gap: 0.875rem;
        padding: 1rem 1.125rem;
        border: 2px solid var(--default-border, #e9edf4);
        border-radius: 0.75rem;
        background: var(--custom-white, #fff);
        transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        cursor: default;
    }

    .mcq-option-card.is-interactive {
        cursor: pointer;
    }

    .mcq-option-card.is-interactive:hover:not(.is-selected):not(.is-correct):not(.is-wrong) {
        border-color: rgba(98, 89, 202, 0.45);
        box-shadow: 0 2px 8px rgba(98, 89, 202, 0.08);
    }

    .mcq-option-card.is-selected {
        border-color: var(--primary-color, #6259ca);
        background: rgba(98, 89, 202, 0.08);
        box-shadow: 0 0 0 1px rgba(98, 89, 202, 0.15);
    }

    .mcq-option-card.is-correct {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.08);
    }

    .mcq-option-card.is-wrong {
        border-color: #dc3545;
        background: rgba(220, 53, 69, 0.06);
    }

    .mcq-option-card.is-correct-missed {
        border-color: rgba(40, 167, 69, 0.55);
        background: rgba(40, 167, 69, 0.04);
    }

    .mcq-option-card__letter {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.95rem;
        background: var(--gray-2, #f3f4f7);
        color: var(--default-text-color, #333);
        align-self: center;
    }

    .mcq-option-card.is-selected .mcq-option-card__letter {
        background: var(--primary-color, #6259ca);
        color: #fff;
    }

    .mcq-option-card.is-correct .mcq-option-card__letter {
        background: #28a745;
        color: #fff;
    }

    .mcq-option-card.is-wrong .mcq-option-card__letter {
        background: #dc3545;
        color: #fff;
    }

    .mcq-option-card__body {
        flex: 1;
        min-width: 0;
        font-size: 1.05rem;
        line-height: 1.65;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .mcq-option-card__body .katex {
        font-size: 1.15em;
    }

    .mcq-option-card__feedback {
        font-size: 0.85rem;
        color: var(--text-muted, #6c757d);
        margin-top: 0.5rem;
    }

    .mcq-option-card__status {
        flex-shrink: 0;
        align-self: center;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .mcq-option-card input[type="radio"],
    .mcq-option-card input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
    }

    [data-theme-mode="dark"] .mcq-option-card {
        background: var(--custom-white, #1a1d2e);
        border-color: rgba(255, 255, 255, 0.12);
    }

    [data-theme-mode="dark"] .mcq-option-card__letter {
        background: rgba(255, 255, 255, 0.08);
    }

    [data-theme-mode="dark"] .mcq-option-card.is-selected {
        background: rgba(98, 89, 202, 0.18);
    }
</style>
