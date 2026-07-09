@forelse ($roles as $role)
    @php
        $isAdminPanel = ($role->dashboard_type ?? 'student') === 'admin';
        $initial = mb_strtoupper(mb_substr(trim($role->name), 0, 1));
    @endphp
    <tr>
        <th scope="row" class="text-muted small">{{ $loop->iteration }}</th>
        <td>
            <div class="rl-role-cell">
                <span class="rl-role-avatar">{{ $initial }}</span>
                <div>
                    <div class="rl-role-name">{{ $role->name }}</div>
                    @if(isset($role->permissions_count))
                        <span class="rl-perm-count mt-1 d-inline-block">
                            <i class="bi bi-key me-1"></i>{{ number_format($role->permissions_count) }} صلاحية
                        </span>
                    @endif
                </div>
            </div>
        </td>
        <td>
            <span class="rl-panel-badge {{ $isAdminPanel ? 'rl-panel-badge--admin' : 'rl-panel-badge--student' }}">
                <i class="bi {{ $isAdminPanel ? 'bi-shield-lock' : 'bi-mortarboard' }}"></i>
                {{ $isAdminPanel ? 'لوحة الأدمن' : 'لوحة الطالب' }}
            </span>
        </td>
        <td>
            <div class="row-action-bar">
                @can('role-list')
                    <a href="{{ route('roles.granted-permissions', $role->id) }}" class="row-action-btn" title="عرض الصلاحيات الممنوحة">
                        <i class="bi bi-eye"></i>
                    </a>
                @endcan
                @can('role-edit')
                    <a href="{{ route('roles.edit', $role->id) }}" class="row-action-btn row-action-btn--primary" title="تعديل الدور">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('role-delete')
                    <span class="row-action-divider"></span>
                    <button type="button" class="row-action-btn row-action-btn--danger"
                            data-bs-toggle="modal"
                            data-bs-target="#delete{{ $role->id }}"
                            title="حذف الدور">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>
        </td>
    </tr>

    @can('role-delete')
        @include('admin.pages.roles.delete', ['role' => $role])
    @endcan
@empty
    <tr>
        <td colspan="4">
            <div class="roles-empty">
                <i class="bi bi-shield-x"></i>
                <p class="mb-0 fw-semibold">لا توجد أدوار متاحة</p>
                @can('role-create')
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm mt-3">
                        <i class="bi bi-plus-lg me-1"></i> إضافة أول دور
                    </a>
                @endcan
            </div>
        </td>
    </tr>
@endforelse
