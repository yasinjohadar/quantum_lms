@forelse ($admins as $admin)
    @php
        $initial = mb_strtoupper(mb_substr(trim($admin->name), 0, 1));
    @endphp
    <tr>
        <th scope="row" class="text-muted small">{{ $loop->iteration + ($admins->currentPage() - 1) * $admins->perPage() }}</th>

        <td>
            <div class="ad-admin-cell">
                <span class="ad-admin-avatar">{{ $initial }}</span>
                <a href="{{ route('users.show', $admin->id) }}" class="ad-admin-name">
                    {{ $admin->name }}
                </a>
            </div>
        </td>

        <td>
            @if ($admin->email)
                <a href="mailto:{{ $admin->email }}" class="ad-email-link">{{ $admin->email }}</a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        <td>
            @if ($admin->phone)
                <div class="d-flex align-items-center gap-1">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $admin->phone) }}" target="_blank"
                       class="ad-phone-link text-success text-decoration-none" title="فتح WhatsApp">
                        <i class="fab fa-whatsapp me-1"></i>{{ $admin->phone }}
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-link p-0 copy-btn text-muted"
                            data-copy-text="{{ $admin->phone }}"
                            title="نسخ الرقم">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        <td>
            <span class="ad-status-badge {{ $admin->is_active ? 'ad-status-badge--active' : 'ad-status-badge--inactive' }}">
                <i class="bi {{ $admin->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                {{ $admin->is_active ? 'مفعل' : 'معطل' }}
            </span>
        </td>

        <td>
            <div class="row-action-bar">
                @can('user-impersonate')
                    <button type="button" class="row-action-btn row-action-btn--info"
                            data-bs-toggle="modal"
                            data-bs-target="#impersonateModal{{ $admin->id }}"
                            title="تسجيل الدخول كالمستخدم">
                        <i class="bi bi-incognito"></i>
                    </button>
                @endcan

                <a class="row-action-btn row-action-btn--primary"
                   href="{{ route('users.edit', ['user' => $admin->id, 'role' => 'admin']) }}"
                   title="تعديل المدير">
                    <i class="bi bi-pencil"></i>
                </a>

                <a class="row-action-btn row-action-btn--secondary"
                   href="{{ route('users.login-logs', $admin->id) }}"
                   title="سجلات الدخول">
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>

                @can('user-delete')
                    <span class="row-action-divider"></span>
                    <button type="button" class="row-action-btn row-action-btn--danger"
                            data-bs-toggle="modal"
                            data-bs-target="#delete{{ $admin->id }}"
                            title="حذف المستخدم">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>
        </td>
    </tr>

    @can('user-delete')
        @include('admin.pages.users.delete', ['user' => $admin])
    @endcan
@empty
    <tr>
        <td colspan="6">
            <div class="admins-empty">
                <i class="bi bi-shield-x"></i>
                <p class="mb-0 fw-semibold">لا توجد حسابات مدراء متاحة</p>
                <p class="small text-muted mt-1">جرّب تغيير معايير البحث أو أنشئ مديراً جديداً</p>
            </div>
        </td>
    </tr>
@endforelse
