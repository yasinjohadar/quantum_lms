@forelse ($admins as $admin)
    <tr>
        <th scope="row">{{ $loop->iteration }}</th>

        <td>
            <a href="{{ route('users.show', $admin->id) }}" class="text-decoration-none">
                {{ $admin->name }}
            </a>
        </td>

        <td>
            {{ $admin->email ?: '—' }}
        </td>

        <td>
            @if ($admin->phone)
                <div class="d-flex align-items-center gap-2">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $admin->phone) }}" target="_blank"
                       class="text-success text-decoration-none" title="فتح WhatsApp">
                        <i class="fab fa-whatsapp me-1"></i>
                        {{ $admin->phone }}
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary copy-btn p-1"
                            data-copy-text="{{ $admin->phone }}"
                            title="نسخ الرقم">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            @else
                —
            @endif
        </td>

        <td>
            <span class="badge {{ $admin->is_active ? 'bg-success' : 'bg-danger' }}">
                {{ $admin->is_active ? 'مفعل' : 'معطل' }}
            </span>
        </td>

        <td>
            <div class="d-flex gap-1 flex-wrap">
                @can('user-impersonate')
                    <button type="button" class="btn btn-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#impersonateModal{{ $admin->id }}"
                            title="تسجيل الدخول كالمستخدم">
                        <i class="fas fa-user-secret"></i>
                    </button>
                @endcan

                <a class="btn btn-info btn-sm"
                   href="{{ route('users.edit', ['user' => $admin->id, 'role' => 'admin']) }}"
                   title="تعديل المدير">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>

                <a class="btn btn-secondary btn-sm"
                   href="{{ route('users.login-logs', $admin->id) }}"
                   title="سجلات الدخول">
                    <i class="fa-solid fa-sign-in-alt"></i>
                </a>

                <a class="btn btn-danger btn-sm"
                   data-bs-toggle="modal"
                   data-bs-target="#delete{{ $admin->id }}"
                   title="حذف المستخدم">
                    <i class="fa-solid fa-trash-can"></i>
                </a>
            </div>
        </td>
    </tr>

    @include('admin.pages.users.delete', ['user' => $admin])
@empty
    <tr>
        <td colspan="6" class="text-center text-danger fw-bold">
            لا توجد حسابات مدراء متاحة
        </td>
    </tr>
@endforelse

