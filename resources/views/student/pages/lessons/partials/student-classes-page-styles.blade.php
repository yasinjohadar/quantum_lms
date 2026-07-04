<style>
    .student-classes-empty {
        border-radius: 14px;
        border: 1px dashed rgba(var(--primary-rgb, 13, 110, 253), 0.25);
        background: linear-gradient(180deg, rgba(var(--primary-rgb, 13, 110, 253), 0.04) 0%, transparent 100%);
    }

    .student-classes-empty__icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: rgb(var(--primary-rgb, 13, 110, 253));
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .student-subject-empty {
        border-radius: 14px;
        border: 1px dashed var(--default-border);
        background: rgba(var(--primary-rgb, 13, 110, 253), 0.03);
    }

    .student-classes-panel .student-class-tabs {
        border-bottom: 2px solid rgba(var(--primary-rgb, 13, 110, 253), 0.1);
    }

    .student-classes-panel .student-class-tabs .nav-link {
        font-weight: 700;
    }

    .student-subject-card {
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .student-subject-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
    }

    [data-theme-mode="dark"] .student-subject-card:hover,
    [data-bs-theme="dark"] .student-subject-card:hover {
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.35);
    }

    @media (prefers-reduced-motion: reduce) {
        .student-subject-card,
        .student-subject-card:hover {
            transition: none;
            transform: none;
        }
    }
</style>
