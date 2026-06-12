{{-- تبويبات تجميع فئات الصلاحيات (إنشاء/تعديل دور) — يتوقع $permissionTabs واختيارياً $role --}}
@if (!empty($permissionTabs))
    <ul class="nav nav-tabs role-perm-tabs flex-wrap mb-3" role="tablist">
        @foreach ($permissionTabs as $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link @if ($loop->first) active @endif"
                        id="link-{{ $tab['pane_id'] }}"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $tab['pane_id'] }}"
                        type="button"
                        role="tab"
                        aria-controls="{{ $tab['pane_id'] }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $tab['label'] }}
                </button>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        @foreach ($permissionTabs as $tab)
            <div class="tab-pane fade @if ($loop->first) show active @endif"
                 id="{{ $tab['pane_id'] }}"
                 role="tabpanel"
                 aria-labelledby="link-{{ $tab['pane_id'] }}"
                 tabindex="0">
                <div class="d-flex flex-wrap gap-2 mb-3 permission-tab-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-secondary expand-all-categories">
                        <i class="bi bi-arrows-expand me-1"></i> توسيع الكل
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary collapse-all-categories">
                        <i class="bi bi-arrows-collapse me-1"></i> طي الكل
                    </button>
                </div>

                <div class="accordion permission-categories-accordion" id="accordion-{{ $tab['pane_id'] }}">
                    @foreach ($tab['categories'] as $categoryName => $categoryPermissions)
                        @if ($categoryPermissions->isNotEmpty())
                            @php
                                $categorySlug = $tab['pane_id'].'-cat-'.$loop->index;
                                $collapseId = 'collapse-'.$categorySlug;
                                $headingId = 'heading-'.$categorySlug;
                                $totalInCategory = $categoryPermissions->count();

                                $selectedInCategory = 0;
                                if (isset($role)) {
                                    $selectedInCategory = $categoryPermissions->filter(
                                        fn ($permission) => $role->hasPermissionTo($permission->name)
                                    )->count();
                                } elseif (! empty(old('permissions'))) {
                                    $selectedInCategory = $categoryPermissions->filter(
                                        fn ($permission) => isset(old('permissions')[$permission->name])
                                    )->count();
                                }
                            @endphp
                            <div class="accordion-item permission-category-card"
                                 data-category-name="{{ e($categoryName) }}">
                                <div class="accordion-header permission-category-header d-flex align-items-stretch"
                                     id="{{ $headingId }}">
                                    <button class="accordion-button collapsed py-2"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $collapseId }}"
                                            aria-expanded="false"
                                            aria-controls="{{ $collapseId }}">
                                        <span class="fw-bold">{{ $categoryName }}</span>
                                        <span class="badge bg-primary-transparent text-primary ms-2 permission-category-badge"
                                              data-total="{{ $totalInCategory }}">{{ $selectedInCategory }} / {{ $totalInCategory }}</span>
                                    </button>
                                    <div class="permission-category-actions d-flex align-items-center flex-shrink-0 px-2 border-bottom">
                                        <button type="button"
                                                class="btn btn-sm btn-link p-0 text-nowrap select-all-category">
                                            تحديد الكل
                                        </button>
                                        <span class="mx-1 text-muted">|</span>
                                        <button type="button"
                                                class="btn btn-sm btn-link p-0 text-nowrap deselect-all-category">
                                            إلغاء الكل
                                        </button>
                                    </div>
                                </div>
                                <div id="{{ $collapseId }}"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="{{ $headingId }}">
                                    <div class="accordion-body permission-category-body">
                                        <div class="row">
                                            @foreach ($categoryPermissions as $permission)
                                                <div class="col-md-6 col-lg-4 mb-3 permission-item">
                                                    <div class="form-check role-perm-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="permissions[{{ $permission->name }}]"
                                                               value="{{ $permission->name }}"
                                                               id="perm_{{ $permission->id }}"
                                                               data-permission-description="{{ e($permission->description ?? '') }}"
                                                               @if (isset($role))
                                                                   @checked($role->hasPermissionTo($permission->name))
                                                               @else
                                                                   @checked(isset(old('permissions', [])[$permission->name]))
                                                               @endif>
                                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                            <span class="role-perm-check__name d-block">{{ $permission->name }}</span>
                                                            @if ($permission->description)
                                                                <small class="role-perm-check__desc text-muted d-block mt-1">{{ $permission->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
