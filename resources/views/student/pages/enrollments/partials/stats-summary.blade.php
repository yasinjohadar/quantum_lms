@if(isset($assigned_classes_total, $assigned_subjects_total))
    <div class="card custom-card border mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-3 justify-content-sm-start justify-content-center">
                        <div class="avatar avatar-md bg-primary-transparent rounded-circle d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px;">
                            <i class="bi bi-building fs-4 text-primary"></i>
                        </div>
                        <div class="text-sm-start text-center">
                            <div class="text-muted small mb-0">الصفوف المخصّصة لك</div>
                            <div class="fs-4 fw-bold text-primary">{{ $assigned_classes_total }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-3 justify-content-sm-start justify-content-center">
                        <div class="avatar avatar-md bg-success-transparent rounded-circle d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px;">
                            <i class="bi bi-journal-bookmark fs-4 text-success"></i>
                        </div>
                        <div class="text-sm-start text-center">
                            <div class="text-muted small mb-0">المواد المخصّصة لك (إجمالي)</div>
                            <div class="fs-4 fw-bold text-success">{{ $assigned_subjects_total }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
