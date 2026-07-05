@forelse ($users as $user)
    @php
        $typeLabel = $user->primary_role_label;
        $avatarClass = match ($typeLabel) {
            'أدمن' => 'um-user-avatar--admin',
            'معلم' => 'um-user-avatar--teacher',
            'مشرف' => 'um-user-avatar--supervisor',
            'طالب' => 'um-user-avatar--student',
            default => 'um-user-avatar--other',
        };
        $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
    @endphp
    <tr>
        @can('user-edit')
        <td>
            <input type="checkbox" class="form-check-input user-manage-checkbox" value="{{ $user->id }}" aria-label="تحديد {{ $user->name }}">
        </td>
        @endcan
        <th scope="row" class="text-muted small">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</th>

        <td>
            <div class="um-user-cell">
                <span class="um-user-avatar {{ $avatarClass }}">{{ $initial }}</span>
                <a href="{{ route('users.show', $user->id) }}" class="um-user-name">
                    {{ $user->name }}
                </a>
            </div>
        </td>

        <td>
            <span class="um-type-badge um-type-badge--{{ $typeLabel }}">{{ $typeLabel }}</span>
        </td>

        <td>
            @if ($user->email)
                <a href="mailto:{{ $user->email }}" class="text-decoration-none small">{{ $user->email }}</a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        <td>
            @if ($user->phone)
                <div class="d-flex align-items-center gap-1">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank"
                       class="um-phone-link text-success text-decoration-none" title="فتح WhatsApp">
                        <i class="fab fa-whatsapp me-1"></i>{{ $user->phone }}
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-link p-0 copy-btn text-muted"
                            data-copy-text="{{ $user->phone }}"
                            title="نسخ الرقم">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        <td>
            @if ($user->roles->isNotEmpty())
                @foreach ($user->roles as $role)
                    <span class="um-role-pill">{{ $role->name }}</span>
                @endforeach
            @else
                <span class="text-muted small">بدون أدوار</span>
            @endif
        </td>

        <td>
            <span class="um-status-badge {{ $user->is_active ? 'um-status-badge--active' : 'um-status-badge--inactive' }}">
                <i class="bi {{ $user->is_active ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                {{ $user->is_active ? 'مفعل' : 'معطل' }}
            </span>
        </td>

        <td>
            <div class="row-action-bar">
                @can('user-impersonate')
                    <button type="button" class="row-action-btn row-action-btn--info"
                            data-bs-toggle="modal"
                            data-bs-target="#impersonateModal{{ $user->id }}"
                            title="تسجيل الدخول كمستخدم">
                        <i class="bi bi-incognito"></i>
                    </button>
                @endcan

                <a class="row-action-btn row-action-btn--primary"
                   href="{{ route('users.edit', ['user' => $user->id, 'return_context' => 'manage']) }}"
                   title="تعديل">
                    <i class="bi bi-pencil"></i>
                </a>

                <a class="row-action-btn row-action-btn--secondary"
                   href="{{ route('users.login-logs', $user->id) }}"
                   title="سجلات الدخول">
                    <i class="bi bi-clock-history"></i>
                </a>

                @if (! $user->is_archived)
                    <button type="button" class="row-action-btn row-action-btn--warning archive-user-btn"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            title="أرشفة">
                        <i class="bi bi-archive"></i>
                    </button>
                @endif

                <button type="button" class="row-action-btn row-action-btn--warning"
                        data-bs-toggle="modal"
                        data-bs-target="#change_password{{ $user->id }}"
                        title="تعديل كلمة السر">
                    <i class="bi bi-key"></i>
                </button>

                <span class="row-action-divider" aria-hidden="true"></span>

                <button type="button" class="row-action-btn row-action-btn--danger"
                        data-bs-toggle="modal"
                        data-bs-target="#delete{{ $user->id }}"
                        title="حذف">
                    <i class="bi bi-trash"></i>
                </button>

                @can('user-delete')
                    <form action="{{ route('admin.users.force-delete', $user->id) }}"
                          method="POST"
                          class="row-action-form"
                          onsubmit="return confirm('تحذير: هذا حذف نهائي ولا يمكن التراجع عنه. هل أنت متأكد؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="row-action-btn row-action-btn--danger" title="حذف نهائي">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </td>
    </tr>

    @include('admin.pages.users.delete', ['user' => $user])
    @include('admin.pages.users.change_password', ['user' => $user])
@empty
    <tr>
        <td colspan="{{ auth()->user()->can('user-edit') ? 9 : 8 }}">
            <div class="users-manage-empty">
                <i class="bi bi-people"></i>
                <p class="mb-0 fw-semibold">لا توجد مستخدمين مطابقين للفلاتر</p>
            </div>
        </td>
    </tr>
@endforelse
