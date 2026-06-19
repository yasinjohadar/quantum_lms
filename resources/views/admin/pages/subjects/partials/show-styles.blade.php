<style>
    .subject-show-page {
        --ss-radius: 14px;
        --ss-accent: #d97706;
        --ss-surface: var(--custom-card-bg, #fff);
        --ss-border: var(--default-border, #e9ecef);
        --ss-muted: var(--text-muted, #6c757d);
        --ss-soft: rgba(217, 119, 6, 0.06);
    }

    [data-theme-mode="dark"] .subject-show-page,
    [data-bs-theme="dark"] .subject-show-page {
        --ss-surface: var(--custom-card-bg, #111a2e);
        --ss-border: rgba(255, 255, 255, 0.1);
        --ss-soft: rgba(217, 119, 6, 0.14);
    }

    .subject-show-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: var(--ss-radius);
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.16) 0%, rgba(217, 119, 6, 0.04) 100%);
        border: 1px solid rgba(217, 119, 6, 0.22);
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.08);
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .subject-show-hero,
    [data-bs-theme="dark"] .subject-show-hero {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.22) 0%, rgba(0, 0, 0, 0.14) 100%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    }

    .subject-show-hero__cover {
        width: 88px;
        height: 88px;
        border-radius: 14px;
        object-fit: cover;
        border: 2px solid rgba(217, 119, 6, 0.25);
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .subject-show-hero__cover--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--ss-soft);
        border-style: dashed;
        color: var(--ss-accent);
        font-size: 2rem;
        opacity: 0.65;
    }

    .subject-show-hero__content { flex: 1; min-width: min(100%, 260px); }

    .subject-show-hero__title {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        line-height: 1.35;
    }

    .subject-show-hero__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem 0.65rem;
        margin-bottom: 0.85rem;
    }

    .subject-show-class-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        background: var(--ss-surface);
        border: 1px solid rgba(217, 119, 6, 0.28);
        color: var(--ss-accent);
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    a.subject-show-class-chip:hover {
        color: #b45309;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
    }

    [data-theme-mode="dark"] a.subject-show-class-chip:hover,
    [data-bs-theme="dark"] a.subject-show-class-chip:hover { color: #fcd34d; }

    .subject-show-class-chip__stage { opacity: 0.85; font-weight: 500; }
    .subject-show-class-chip__dot { opacity: 0.45; }

    .subject-show-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .subject-show-badge--active {
        background: rgba(22, 163, 74, 0.12);
        color: #16a34a;
        border: 1px solid rgba(22, 163, 74, 0.25);
    }

    .subject-show-badge--inactive {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .subject-show-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .subject-show-stat {
        text-align: center;
        padding: 0.55rem 0.85rem;
        border-radius: 10px;
        background: var(--ss-surface);
        border: 1px solid var(--ss-border);
        min-width: 82px;
    }

    .subject-show-stat__value {
        display: block;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--ss-accent);
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .subject-show-stat__value,
    [data-bs-theme="dark"] .subject-show-stat__value { color: #fcd34d; }

    .subject-show-stat__label {
        font-size: 0.68rem;
        color: var(--ss-muted);
    }

    .subject-show-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-self: center;
        margin-inline-start: auto;
    }

    .subject-show-hero__actions .btn {
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.84rem;
    }

    .subject-show-section {
        border-radius: var(--ss-radius);
        border: 1px solid var(--ss-border);
        background: var(--ss-surface);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    [data-theme-mode="dark"] .subject-show-section,
    [data-bs-theme="dark"] .subject-show-section {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
    }

    .subject-show-section__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ss-border);
        background: var(--ss-soft);
    }

    .subject-show-section__title-wrap {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .subject-show-section__icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(217, 119, 6, 0.14);
        color: var(--ss-accent);
        font-size: 1rem;
    }

    .subject-show-section__title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0; }
    .subject-show-section__count { font-size: 0.75rem; color: var(--ss-muted); font-weight: 500; }

    .subject-show-section__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .subject-show-section__actions .btn { border-radius: 10px; font-weight: 600; font-size: 0.82rem; }

    .subject-show-section__body { padding: 1.25rem; }

    .subject-show-empty {
        text-align: center;
        padding: 2.75rem 1.5rem;
    }

    .subject-show-empty__icon {
        width: 68px;
        height: 68px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.75rem;
        color: var(--ss-accent);
        background: var(--ss-soft);
        border: 1px solid var(--ss-border);
    }

    .subject-show-empty__title { font-weight: 700; font-size: 1rem; margin-bottom: 0.35rem; }
    .subject-show-empty__text { color: var(--ss-muted); font-size: 0.85rem; margin-bottom: 0; }

    .subject-show-page .accordion.accordion-primary,
    .subject-show-page .accordion.accordion-secondary {
        --bs-accordion-border-radius: 10px;
    }

    .subject-show-page .accordion.accordion-primary .accordion-item,
    .subject-show-page .accordion.accordion-secondary .accordion-item {
        border: 1px solid var(--ss-border) !important;
        border-radius: 10px !important;
        overflow: hidden;
        margin-bottom: 0.65rem;
        background: var(--ss-surface);
    }

    .subject-show-page .accordion.accordion-primary .accordion-item[class*="section-level-"] > .accordion-collapse .accordion-body,
    .subject-show-page .accordion.accordion-secondary .accordion-item.unit-item > .accordion-collapse .accordion-body {
        background-color: var(--ss-surface) !important;
    }

    @media (max-width: 767.98px) {
        .subject-show-hero { padding: 1rem; gap: 1rem; }
        .subject-show-hero__cover { width: 72px; height: 72px; }
        .subject-show-hero__title { font-size: 1.15rem; }
        .subject-show-hero__actions {
            width: 100%;
            margin-inline-start: 0;
            justify-content: flex-start;
        }
        .subject-show-section__header { padding: 0.85rem 1rem; }
        .subject-show-section__body { padding: 0.85rem; }
        .subject-show-stat { min-width: 72px; padding: 0.45rem 0.65rem; }
    }

    @media (max-width: 575.98px) {
        .subject-show-hero__actions .btn { flex: 1 1 auto; justify-content: center; }
        .subject-show-section__actions { width: 100%; }
        .subject-show-section__actions .btn { flex: 1 1 auto; }
    }

<style>
.btn-purple {
    background-color: #6259ca;
    border-color: #6259ca;
    color: #fff;
}
.btn-purple:hover {
    background-color: #524abb;
    border-color: #524abb;
    color: #fff;
}
.btn-purple:focus, .btn-purple:active {
    background-color: #4a42a7;
    border-color: #4a42a7;
    color: #fff;
}
.bg-purple-transparent {
    background-color: rgba(98, 89, 202, 0.1);
}
.text-purple {
    color: #6259ca !important;
}
.questions-list-container {
    max-height: 400px;
    overflow-y: auto;
}
.questions-list-container .list-group-item:hover {
    background-color: rgba(98, 89, 202, 0.05);
}
.questions-list-container .form-check-input:checked + .flex-grow-1 {
    background-color: rgba(98, 89, 202, 0.05);
}
/*
 * ØªÙ…ÙŠÙŠØ² Ø§Ù„Ù…Ø³ØªÙˆÙ‰: Ø­Ø¯ Ø¬Ø§Ù†Ø¨ÙŠ + Ù„ÙˆÙ† Ø´Ø±ÙŠØ· Ø§Ù„Ø¹Ù†ÙˆØ§Ù† ÙÙ‚Ø· (Ù„Ø§ Ø®Ù„ÙÙŠØ© Ù„Ù„Ø¨Ø·Ø§Ù‚Ø© Ø£Ùˆ Ø¬Ø³Ù… Ø§Ù„Ø£ÙƒÙˆØ±Ø¯ÙŠÙˆÙ†)
 * 0 Ø£Ø²Ø±Ù‚ØŒ 1 ØªØ±ÙƒÙˆØ§Ø²ØŒ 2 ÙˆØ±Ø¯ÙŠ/Ù…Ø±Ø¬Ø§Ù†ÙŠØŒ 3 Ø£Ø®Ø¶Ø±ØŒ 4 ÙƒÙ‡Ø±Ù…Ø§Ù†ÙŠØŒ 5 Ø¨Ù†ÙØ³Ø¬ÙŠ
 */
:root {
    --section-level-0-rgb: 37, 99, 235;
    --section-level-1-rgb: 8, 145, 178;
    --section-level-2-rgb: 219, 39, 119;
    --section-level-3-rgb: 22, 163, 74;
    --section-level-4-rgb: 217, 119, 6;
    --section-level-5-rgb: 124, 58, 237;
    --unit-accent-rgb: 98, 89, 202;
}
.section-level-0 { border-start: 3px solid rgb(var(--section-level-0-rgb)); background-color: transparent; }
.section-level-1 { border-start: 3px solid rgb(var(--section-level-1-rgb)); background-color: transparent; }
.section-level-2 { border-start: 3px solid rgb(var(--section-level-2-rgb)); background-color: transparent; }
.section-level-3 { border-start: 3px solid rgb(var(--section-level-3-rgb)); background-color: transparent; }
.section-level-4 { border-start: 3px solid rgb(var(--section-level-4-rgb)); background-color: transparent; }
.section-level-5 { border-start: 3px solid rgb(var(--section-level-5-rgb)); background-color: transparent; }

.section-level-0 .accordion-button,
.section-level-0 .accordion-button.collapsed,
.section-level-0 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-0-rgb), 0.14);
    box-shadow: none;
}
.section-level-0 .accordion-button:hover { background-color: rgba(var(--section-level-0-rgb), 0.2); }
.section-level-0 .accordion-button:focus { background-color: rgba(var(--section-level-0-rgb), 0.18); box-shadow: 0 0 0 0.2rem rgba(var(--section-level-0-rgb), 0.22); }

.section-level-1 .accordion-button,
.section-level-1 .accordion-button.collapsed,
.section-level-1 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-1-rgb), 0.14);
    box-shadow: none;
}
.section-level-1 .accordion-button:hover { background-color: rgba(var(--section-level-1-rgb), 0.2); }
.section-level-1 .accordion-button:focus { background-color: rgba(var(--section-level-1-rgb), 0.18); box-shadow: 0 0 0 0.2rem rgba(var(--section-level-1-rgb), 0.22); }

.section-level-2 .accordion-button,
.section-level-2 .accordion-button.collapsed,
.section-level-2 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-2-rgb), 0.12);
    box-shadow: none;
}
.section-level-2 .accordion-button:hover { background-color: rgba(var(--section-level-2-rgb), 0.18); }
.section-level-2 .accordion-button:focus { background-color: rgba(var(--section-level-2-rgb), 0.16); box-shadow: 0 0 0 0.2rem rgba(var(--section-level-2-rgb), 0.2); }

.section-level-3 .accordion-button,
.section-level-3 .accordion-button.collapsed,
.section-level-3 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-3-rgb), 0.12);
    box-shadow: none;
}
.section-level-3 .accordion-button:hover { background-color: rgba(var(--section-level-3-rgb), 0.18); }
.section-level-3 .accordion-button:focus { background-color: rgba(var(--section-level-3-rgb), 0.16); box-shadow: 0 0 0 0.2rem rgba(var(--section-level-3-rgb), 0.2); }

.section-level-4 .accordion-button,
.section-level-4 .accordion-button.collapsed,
.section-level-4 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-4-rgb), 0.14);
    box-shadow: none;
}
.section-level-4 .accordion-button:hover { background-color: rgba(var(--section-level-4-rgb), 0.2); }
.section-level-4 .accordion-button:focus { background-color: rgba(var(--section-level-4-rgb), 0.18); box-shadow: 0 0 0 0.2rem rgba(var(--section-level-4-rgb), 0.22); }

.section-level-5 .accordion-button,
.section-level-5 .accordion-button.collapsed,
.section-level-5 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-5-rgb), 0.14);
    box-shadow: none;
}
.section-level-5 .accordion-button:hover { background-color: rgba(var(--section-level-5-rgb), 0.2); }
.section-level-5 .accordion-button:focus { background-color: rgba(var(--section-level-5-rgb), 0.18); box-shadow: 0 0 0 0.2rem rgba(var(--section-level-5-rgb), 0.22); }

/* Ø§Ù„ÙˆØ­Ø¯Ø§Øª â€” Ù„ÙˆÙ† Ø¨Ù†ÙØ³Ø¬ÙŠ Ø§Ù„Ù…Ø´Ø±ÙˆØ¹ØŒ Ù…Ø®ØªÙ„Ù Ø¹Ù† Ø£Ù„ÙˆØ§Ù† Ø§Ù„Ø£Ù‚Ø³Ø§Ù… */
.unit-item {
    overflow: hidden;
}
.unit-item-root {
    border-start: 3px solid #6259ca !important;
    background-color: transparent;
}
.unit-item-root > .accordion-header .accordion-button,
.unit-item-root > .accordion-header .accordion-button.collapsed,
.unit-item-root > .accordion-header .accordion-button:not(.collapsed) {
    background-color: rgba(var(--unit-accent-rgb), 0.12);
    box-shadow: none;
}
.unit-item-root > .accordion-header .accordion-button:hover { background-color: rgba(var(--unit-accent-rgb), 0.18); }
.unit-item-root > .accordion-header .accordion-button:focus { background-color: rgba(var(--unit-accent-rgb), 0.16); box-shadow: 0 0 0 0.2rem rgba(var(--unit-accent-rgb), 0.22); }

.unit-item-child {
    border-start: 3px solid var(--bs-info) !important;
    background-color: transparent;
}
.unit-item-child > .accordion-header .accordion-button,
.unit-item-child > .accordion-header .accordion-button.collapsed,
.unit-item-child > .accordion-header .accordion-button:not(.collapsed) {
    background-color: rgba(var(--bs-info-rgb), 0.11);
    box-shadow: none;
}
.unit-item-child > .accordion-header .accordion-button:hover { background-color: rgba(var(--bs-info-rgb), 0.17); }
.unit-item-child > .accordion-header .accordion-button:focus { background-color: rgba(var(--bs-info-rgb), 0.15); box-shadow: 0 0 0 0.2rem rgba(var(--bs-info-rgb), 0.2); }

/* Ø¬Ø³Ù… Ø§Ù„Ø£ÙƒÙˆØ±Ø¯ÙŠÙˆÙ† Ø¨Ø¯ÙˆÙ† Ù„ÙˆÙ† â€” ÙÙ‚Ø· Ø²Ø± Ø§Ù„Ø¹Ù†ÙˆØ§Ù† */
/*
 * ØªØ¬Ø§ÙˆØ² styles.min.css: .accordion.accordion-primary .accordion-button (Ø£Ø¹Ù„Ù‰ Ø®ØµÙˆØµÙŠØ© Ù…Ù† .section-level-N ÙÙ‚Ø·)
 */
.accordion.accordion-primary .accordion-item.section-level-0 .accordion-button,
.accordion.accordion-primary .accordion-item.section-level-0 .accordion-button.collapsed,
.accordion.accordion-primary .accordion-item.section-level-0 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-0-rgb), 0.14) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-primary .accordion-item.section-level-0 .accordion-button:hover { background-color: rgba(var(--section-level-0-rgb), 0.2) !important; }
.accordion.accordion-primary .accordion-item.section-level-0 .accordion-button:focus { background-color: rgba(var(--section-level-0-rgb), 0.18) !important; box-shadow: 0 0 0 0.2rem rgba(var(--section-level-0-rgb), 0.22) !important; }

.accordion.accordion-primary .accordion-item.section-level-1 .accordion-button,
.accordion.accordion-primary .accordion-item.section-level-1 .accordion-button.collapsed,
.accordion.accordion-primary .accordion-item.section-level-1 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-1-rgb), 0.14) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-primary .accordion-item.section-level-1 .accordion-button:hover { background-color: rgba(var(--section-level-1-rgb), 0.2) !important; }
.accordion.accordion-primary .accordion-item.section-level-1 .accordion-button:focus { background-color: rgba(var(--section-level-1-rgb), 0.18) !important; box-shadow: 0 0 0 0.2rem rgba(var(--section-level-1-rgb), 0.22) !important; }

.accordion.accordion-primary .accordion-item.section-level-2 .accordion-button,
.accordion.accordion-primary .accordion-item.section-level-2 .accordion-button.collapsed,
.accordion.accordion-primary .accordion-item.section-level-2 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-2-rgb), 0.12) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-primary .accordion-item.section-level-2 .accordion-button:hover { background-color: rgba(var(--section-level-2-rgb), 0.18) !important; }
.accordion.accordion-primary .accordion-item.section-level-2 .accordion-button:focus { background-color: rgba(var(--section-level-2-rgb), 0.16) !important; box-shadow: 0 0 0 0.2rem rgba(var(--section-level-2-rgb), 0.2) !important; }

.accordion.accordion-primary .accordion-item.section-level-3 .accordion-button,
.accordion.accordion-primary .accordion-item.section-level-3 .accordion-button.collapsed,
.accordion.accordion-primary .accordion-item.section-level-3 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-3-rgb), 0.12) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-primary .accordion-item.section-level-3 .accordion-button:hover { background-color: rgba(var(--section-level-3-rgb), 0.18) !important; }
.accordion.accordion-primary .accordion-item.section-level-3 .accordion-button:focus { background-color: rgba(var(--section-level-3-rgb), 0.16) !important; box-shadow: 0 0 0 0.2rem rgba(var(--section-level-3-rgb), 0.2) !important; }

.accordion.accordion-primary .accordion-item.section-level-4 .accordion-button,
.accordion.accordion-primary .accordion-item.section-level-4 .accordion-button.collapsed,
.accordion.accordion-primary .accordion-item.section-level-4 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-4-rgb), 0.14) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-primary .accordion-item.section-level-4 .accordion-button:hover { background-color: rgba(var(--section-level-4-rgb), 0.2) !important; }
.accordion.accordion-primary .accordion-item.section-level-4 .accordion-button:focus { background-color: rgba(var(--section-level-4-rgb), 0.18) !important; box-shadow: 0 0 0 0.2rem rgba(var(--section-level-4-rgb), 0.22) !important; }

.accordion.accordion-primary .accordion-item.section-level-5 .accordion-button,
.accordion.accordion-primary .accordion-item.section-level-5 .accordion-button.collapsed,
.accordion.accordion-primary .accordion-item.section-level-5 .accordion-button:not(.collapsed) {
    background-color: rgba(var(--section-level-5-rgb), 0.14) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-primary .accordion-item.section-level-5 .accordion-button:hover { background-color: rgba(var(--section-level-5-rgb), 0.2) !important; }
.accordion.accordion-primary .accordion-item.section-level-5 .accordion-button:focus { background-color: rgba(var(--section-level-5-rgb), 0.18) !important; box-shadow: 0 0 0 0.2rem rgba(var(--section-level-5-rgb), 0.22) !important; }

.accordion.accordion-secondary .accordion-item.unit-item-root > .accordion-header .accordion-button,
.accordion.accordion-secondary .accordion-item.unit-item-root > .accordion-header .accordion-button.collapsed,
.accordion.accordion-secondary .accordion-item.unit-item-root > .accordion-header .accordion-button:not(.collapsed) {
    background-color: rgba(var(--unit-accent-rgb), 0.12) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-secondary .accordion-item.unit-item-root > .accordion-header .accordion-button:hover { background-color: rgba(var(--unit-accent-rgb), 0.18) !important; }
.accordion.accordion-secondary .accordion-item.unit-item-root > .accordion-header .accordion-button:focus { background-color: rgba(var(--unit-accent-rgb), 0.16) !important; box-shadow: 0 0 0 0.2rem rgba(var(--unit-accent-rgb), 0.22) !important; }

.accordion.accordion-secondary .accordion-item.unit-item-child > .accordion-header .accordion-button,
.accordion.accordion-secondary .accordion-item.unit-item-child > .accordion-header .accordion-button.collapsed,
.accordion.accordion-secondary .accordion-item.unit-item-child > .accordion-header .accordion-button:not(.collapsed) {
    background-color: rgba(var(--bs-info-rgb), 0.11) !important;
    color: inherit;
    box-shadow: none;
}
.accordion.accordion-secondary .accordion-item.unit-item-child > .accordion-header .accordion-button:hover { background-color: rgba(var(--bs-info-rgb), 0.17) !important; }
.accordion.accordion-secondary .accordion-item.unit-item-child > .accordion-header .accordion-button:focus { background-color: rgba(var(--bs-info-rgb), 0.15) !important; box-shadow: 0 0 0 0.2rem rgba(var(--bs-info-rgb), 0.2) !important; }
</style>
