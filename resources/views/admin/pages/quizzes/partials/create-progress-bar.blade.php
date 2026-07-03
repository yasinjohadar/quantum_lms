<div class="quiz-create-progress">
<style>
    .quiz-create-progress .import-steps-bar {
        width: 100%;
        border-bottom: 0;
    }
    .quiz-create-progress .import-steps-bar .nav-item {
        flex: 1 1 0;
        min-width: 0;
        margin-inline-end: 0.5rem;
    }
    .quiz-create-progress .import-steps-bar .nav-item:last-child {
        margin-inline-end: 0;
    }
    .quiz-create-progress .import-steps-bar .nav-link {
        justify-content: center;
        width: 100%;
        pointer-events: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    .quiz-create-progress .import-steps-bar .nav-item.completed .nav-link {
        color: rgb(var(--success-rgb));
        border-color: rgb(var(--success-rgb)) !important;
    }
    .quiz-create-progress .import-steps-bar .nav-item.completed .nav-link i {
        border-color: rgb(var(--success-rgb));
        color: rgb(var(--success-rgb));
    }
</style>
<div class="card custom-card mb-3">
    <div class="card-body py-3">
        <ul class="nav nav-tabs form-wizard-1 import-steps-bar d-flex mb-0" role="list">
            <li class="nav-item {{ $currentStep === 1 ? 'active' : 'completed' }}" role="listitem">
                <span class="nav-link {{ $currentStep === 1 ? 'active' : '' }}" @if($currentStep === 1) aria-current="step" @endif>
                    <i class="bi bi-pencil-square"></i>
                    <span class="ms-1 d-none d-sm-inline">بيانات الاختبار</span>
                </span>
            </li>
            <li class="nav-item {{ $currentStep === 2 ? 'active' : '' }}" role="listitem">
                <span class="nav-link {{ $currentStep === 2 ? 'active' : '' }}" @if($currentStep === 2) aria-current="step" @endif>
                    <i class="bi bi-cloud-upload"></i>
                    <span class="ms-1 d-none d-sm-inline">استيراد Excel</span>
                </span>
            </li>
        </ul>
    </div>
</div>
</div>
