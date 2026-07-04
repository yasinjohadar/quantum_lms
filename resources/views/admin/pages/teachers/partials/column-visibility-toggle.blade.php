<div class="tv-columns-toggle dropdown">
    <button type="button"
            class="btn btn-sm btn-outline-secondary dropdown-toggle"
            id="teachersColumnsToggleBtn"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false">
        <i class="bi bi-layout-three-columns me-1"></i>
        <span>الأعمدة</span>
        <span class="badge bg-secondary-transparent text-secondary ms-1" id="teachersColumnsVisibleCount"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end tv-columns-menu p-0" aria-labelledby="teachersColumnsToggleBtn">
        <div class="tv-columns-menu__header">
            <span class="fw-semibold small">إظهار الأعمدة</span>
            <span class="text-muted small">حسب الأهمية</span>
        </div>
        <div class="tv-columns-menu__presets">
            <button type="button" class="btn btn-sm btn-outline-primary" data-tv-columns-preset="minimal">أساسي</button>
            <button type="button" class="btn btn-sm btn-outline-primary active" data-tv-columns-preset="standard">قياسي</button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-tv-columns-preset="full">الكل</button>
        </div>
        <div class="tv-columns-menu__list" id="teachersColumnsChecklist"></div>
    </div>
</div>
