@if(isset($assigned_classes_total, $assigned_subjects_total))
    @include('student.pages.enrollments.partials.enrollment-page-styles')

    <div class="enrollment-stats-panel mb-4">
        <div class="row g-0">
            <div class="col-sm-6">
                <div class="enrollment-stats-panel__item justify-content-sm-start justify-content-center">
                    <div class="enrollment-stats-panel__icon enrollment-stats-panel__icon--classes">
                        <i class="bi bi-building fs-5" aria-hidden="true"></i>
                    </div>
                    <div class="text-sm-start text-center">
                        <div class="text-muted small mb-1">الصفوف المخصّصة لك</div>
                        <div class="enrollment-stats-panel__value text-primary">{{ $assigned_classes_total }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="enrollment-stats-panel__item justify-content-sm-start justify-content-center">
                    <div class="enrollment-stats-panel__icon enrollment-stats-panel__icon--subjects">
                        <i class="bi bi-journal-bookmark fs-5" aria-hidden="true"></i>
                    </div>
                    <div class="text-sm-start text-center">
                        <div class="text-muted small mb-1">المواد المخصّصة لك (إجمالي)</div>
                        <div class="enrollment-stats-panel__value text-success">{{ $assigned_subjects_total }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
