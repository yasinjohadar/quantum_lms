{{-- ملخص الصلاحيات المحددة — أكورديون مطوي (يُحدَّث عبر JavaScript) --}}
<div class="accordion mb-3" id="rolePermissionsSummaryAccordion">
    <div class="accordion-item" id="role-permissions-summary-panel">
        <div class="accordion-header role-permissions-summary-header d-flex align-items-stretch"
             id="headingRolePermissionsSummary">
            <button class="accordion-button collapsed py-2"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#rolePermissionsSummaryCollapse"
                    aria-expanded="false"
                    aria-controls="rolePermissionsSummaryCollapse">
                <i class="bi bi-list-check me-2 text-primary"></i>
                <span class="fw-semibold">ملخص الصلاحيات المحددة</span>
                <span class="badge bg-primary ms-2" id="role-permissions-summary-count">0</span>
            </button>
            <div class="role-permissions-summary-actions d-flex align-items-center flex-shrink-0 px-3 border-bottom">
                <button type="button"
                        class="btn btn-sm btn-link p-0 text-danger text-nowrap"
                        id="role-permissions-deselect-all">
                    <i class="bi bi-x-circle me-1"></i> إلغاء تحديد الكل
                </button>
            </div>
        </div>
        <div id="rolePermissionsSummaryCollapse"
             class="accordion-collapse collapse"
             aria-labelledby="headingRolePermissionsSummary">
            <div class="accordion-body py-3">
                <p class="text-muted small mb-2" id="role-permissions-summary-empty">لم يتم تحديد أي صلاحية بعد.</p>
                <div class="role-permissions-summary-list-wrap">
                    <ul class="list-unstyled small mb-0" id="role-permissions-summary-list"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
