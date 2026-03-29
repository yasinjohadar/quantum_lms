{{-- تبويبات تجميع فئات الصلاحيات (إنشاء/تعديل دور) — يتوقع $permissionTabs واختيارياً $role --}}
@if (!empty($permissionTabs))
    <ul class="nav nav-tabs flex-wrap gap-1 mb-3 border-bottom" role="tablist">
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
                @foreach ($tab['categories'] as $categoryName => $categoryPermissions)
                    @if ($categoryPermissions->isNotEmpty())
                        <div class="card mb-3 permission-category-card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">{{ $categoryName }}</h6>
                                <div>
                                    <button type="button" class="btn btn-sm btn-link p-0 select-all-category">
                                        تحديد الكل
                                    </button>
                                    <span class="mx-2">|</span>
                                    <button type="button" class="btn btn-sm btn-link p-0 deselect-all-category">
                                        إلغاء تحديد الكل
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($categoryPermissions as $permission)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="form-check">
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
                                                    <span class="fw-semibold">{{ $permission->name }}</span>
                                                    @if ($permission->description)
                                                        <br>
                                                        <small class="text-muted">{{ $permission->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
@endif
