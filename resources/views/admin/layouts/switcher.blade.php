    <!-- Start Switcher -->
    <div class="offcanvas offcanvas-end switcher-panel" tabindex="-1" id="switcher-canvas" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header border-bottom switcher-panel__header">
            <div>
                <h5 class="offcanvas-title text-default mb-0" id="offcanvasRightLabel">إعدادات العرض</h5>
                <p class="switcher-panel__subtitle mb-0">خصّص المظهر بسرعة</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body switcher-panel__body">

            {{-- خيارات مخفية لإبقاء سكربت الثيم يعمل بدون كسر --}}
            <div class="d-none" aria-hidden="true">
                <input class="form-check-input" type="radio" name="direction" id="switcher-ltr">
                <input class="form-check-input" type="radio" name="direction" id="switcher-rtl" checked>
                <input class="form-check-input" type="radio" name="navigation-style" id="switcher-vertical" checked>
                <input class="form-check-input" type="radio" name="navigation-style" id="switcher-horizontal">
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-menu-click">
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-menu-hover">
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-icon-click">
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-icon-hover">
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-icontext-menu">
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-icon-overlay">
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-detached">
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-double-menu">
                <input class="form-check-input" type="radio" name="page-styles" id="switcher-regular" checked>
                <input class="form-check-input" type="radio" name="page-styles" id="switcher-classic">
                <input class="form-check-input" type="radio" name="page-styles" id="switcher-modern">
                <input class="form-check-input" type="radio" name="layout-width" id="switcher-full-width" checked>
                <input class="form-check-input" type="radio" name="layout-width" id="switcher-boxed">
                <input class="form-check-input" type="radio" name="menu-positions" id="switcher-menu-fixed" checked>
                <input class="form-check-input" type="radio" name="menu-positions" id="switcher-menu-scroll">
                <input class="form-check-input" type="radio" name="header-positions" id="switcher-header-fixed" checked>
                <input class="form-check-input" type="radio" name="header-positions" id="switcher-header-scroll">
                <input class="form-check-input" type="radio" name="page-loader" id="switcher-loader-enable">
                <input class="form-check-input" type="radio" name="page-loader" id="switcher-loader-disable" checked>
                <input class="form-check-input color-input color-white" type="radio" name="menu-colors" id="switcher-menu-light" checked>
                <input class="form-check-input color-input color-dark" type="radio" name="menu-colors" id="switcher-menu-dark">
                <input class="form-check-input color-input color-primary" type="radio" name="menu-colors" id="switcher-menu-primary">
                <input class="form-check-input color-input color-gradient" type="radio" name="menu-colors" id="switcher-menu-gradient">
                <input class="form-check-input color-input color-transparent" type="radio" name="menu-colors" id="switcher-menu-transparent">
                <input class="form-check-input color-input color-white" type="radio" name="header-colors" id="switcher-header-light" checked>
                <input class="form-check-input color-input color-dark" type="radio" name="header-colors" id="switcher-header-dark">
                <input class="form-check-input color-input color-primary" type="radio" name="header-colors" id="switcher-header-primary">
                <input class="form-check-input color-input color-gradient" type="radio" name="header-colors" id="switcher-header-gradient">
                <input class="form-check-input color-input color-transparent" type="radio" name="header-colors" id="switcher-header-transparent">
                <input class="form-check-input color-input color-bg-1" type="radio" name="theme-background" id="switcher-background">
                <input class="form-check-input color-input color-bg-2" type="radio" name="theme-background" id="switcher-background1">
                <input class="form-check-input color-input color-bg-3" type="radio" name="theme-background" id="switcher-background2">
                <input class="form-check-input color-input color-bg-4" type="radio" name="theme-background" id="switcher-background3">
                <input class="form-check-input color-input color-bg-5" type="radio" name="theme-background" id="switcher-background4">
                <input class="form-check-input bgimage-input bg-img1" type="radio" name="theme-background" id="switcher-bg-img">
                <input class="form-check-input bgimage-input bg-img2" type="radio" name="theme-background" id="switcher-bg-img1">
                <input class="form-check-input bgimage-input bg-img3" type="radio" name="theme-background" id="switcher-bg-img2">
                <input class="form-check-input bgimage-input bg-img4" type="radio" name="theme-background" id="switcher-bg-img3">
                <input class="form-check-input bgimage-input bg-img5" type="radio" name="theme-background" id="switcher-bg-img4">
                <div class="theme-container-background"></div>
                <div class="pickr-container-background"></div>
            </div>

            <div class="switcher-card">
                <div class="switcher-card__head">
                    <span class="switcher-card__icon"><i class="fe fe-sun"></i></span>
                    <div>
                        <p class="switcher-card__title mb-0">المظهر</p>
                        <p class="switcher-card__hint mb-0">فاتح أو داكن</p>
                    </div>
                </div>
                <div class="switcher-choice-grid">
                    <label class="switcher-choice" for="switcher-light-theme">
                        <input class="form-check-input" type="radio" name="theme-style" id="switcher-light-theme" checked>
                        <span class="switcher-choice__box">
                            <i class="fe fe-sun"></i>
                            <span>فاتح</span>
                        </span>
                    </label>
                    <label class="switcher-choice" for="switcher-dark-theme">
                        <input class="form-check-input" type="radio" name="theme-style" id="switcher-dark-theme">
                        <span class="switcher-choice__box">
                            <i class="fe fe-moon"></i>
                            <span>داكن</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="switcher-card sidemenu-layout-styles">
                <div class="switcher-card__head">
                    <span class="switcher-card__icon"><i class="fe fe-sidebar"></i></span>
                    <div>
                        <p class="switcher-card__title mb-0">القائمة الجانبية</p>
                        <p class="switcher-card__hint mb-0">شكل الشريط الجانبي</p>
                    </div>
                </div>
                <div class="switcher-choice-grid">
                    <label class="switcher-choice" for="switcher-default-menu">
                        <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-default-menu" checked>
                        <span class="switcher-choice__box">
                            <i class="fe fe-menu"></i>
                            <span>موسّعة</span>
                        </span>
                    </label>
                    <label class="switcher-choice" for="switcher-closed-menu">
                        <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-closed-menu">
                        <span class="switcher-choice__box">
                            <i class="fe fe-minimize-2"></i>
                            <span>مصغّرة</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="switcher-card theme-colors">
                <div class="switcher-card__head">
                    <span class="switcher-card__icon"><i class="fe fe-droplet"></i></span>
                    <div>
                        <p class="switcher-card__title mb-0">اللون الأساسي</p>
                        <p class="switcher-card__hint mb-0">اختر لون الواجهة</p>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center switcher-style switcher-colors-row">
                    <div class="form-check switch-select me-3">
                        <input class="form-check-input color-input color-primary-1" type="radio" name="theme-primary" id="switcher-primary">
                    </div>
                    <div class="form-check switch-select me-3">
                        <input class="form-check-input color-input color-primary-2" type="radio" name="theme-primary" id="switcher-primary1">
                    </div>
                    <div class="form-check switch-select me-3">
                        <input class="form-check-input color-input color-primary-3" type="radio" name="theme-primary" id="switcher-primary2">
                    </div>
                    <div class="form-check switch-select me-3">
                        <input class="form-check-input color-input color-primary-4" type="radio" name="theme-primary" id="switcher-primary3">
                    </div>
                    <div class="form-check switch-select me-3">
                        <input class="form-check-input color-input color-primary-5" type="radio" name="theme-primary" id="switcher-primary4">
                    </div>
                    <div class="form-check switch-select ps-0 mt-1 color-primary-light">
                        <div class="theme-container-primary"></div>
                        <div class="pickr-container-primary"></div>
                    </div>
                </div>
            </div>

            <div class="switcher-panel__footer">
                <a href="javascript:void(0);" id="reset-all" class="btn btn-outline-danger w-100">
                    <i class="fe fe-refresh-cw me-1"></i>
                    إعادة الضبط الافتراضي
                </a>
            </div>
        </div>
    </div>
    <!-- End Switcher -->
