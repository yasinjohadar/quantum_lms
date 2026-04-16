@forelse ($users as $user)
    <tr>
        <th scope="row">{{ $loop->iteration }}</th>

        <td>
            <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none">
                {{ $user->name }}
            </a>
        </td>

        <td>
            {{ $user->primary_role_label }}
        </td>

        <td>
            {{ $user->email ?: '—' }}
        </td>

        <td>
            @if ($user->phone)
                <div class="d-flex align-items-center gap-2">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank"
                       class="text-success text-decoration-none" title="فتح WhatsApp">
                        <i class="fab fa-whatsapp me-1"></i>
                        {{ $user->phone }}
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary copy-btn p-1"
                            data-copy-text="{{ $user->phone }}"
                            title="نسخ الرقم">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            @else
                —
            @endif
        </td>

        <td>
            @php
                $roleNames = $user->roles->pluck('name')->implode(', ');
            @endphp
            <span class="text-muted">{{ $roleNames ?: 'بدون أدوار' }}</span>
        </td>

        <td>
            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                {{ $user->is_active ? 'مفعل' : 'معطل' }}
            </span>
        </td>

        <td>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    العمليات
                </button>
                <ul class="dropdown-menu">
                    @can('user-impersonate')
                        <li>
                            <button type="button" class="dropdown-item"
                                    data-bs-toggle="modal"
                                    data-bs-target="#impersonateModal{{ $user->id }}">
                                <i class="fas fa-user-secret me-2 text-info"></i> تسجيل الدخول كمستخدم
                            </button>
                        </li>
                    @endcan

                    <li>
                        <a class="dropdown-item"
                           href="{{ route('users.edit', ['user' => $user->id, 'return_context' => 'manage']) }}">
                            <i class="fa-solid fa-pen-to-square me-2 text-info"></i> تعديل
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item"
                           href="{{ route('users.login-logs', $user->id) }}">
                            <i class="fa-solid fa-sign-in-alt me-2 text-secondary"></i> سجلات الدخول
                        </a>
                    </li>

                    @if (! $user->is_archived)
                        <li>
                            <button type="button" class="dropdown-item archive-user-btn"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                <i class="fas fa-archive me-2 text-warning"></i> أرشفة
                            </button>
                        </li>
                    @endif

                    <li>
                        <a class="dropdown-item" href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#change_password{{ $user->id }}">
                            <i class="fa-solid fa-key me-2 text-warning"></i> تعديل كلمة السر
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item text-danger" href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#delete{{ $user->id }}">
                            <i class="fa-solid fa-trash-can me-2"></i> حذف
                        </a>
                    </li>

                    @can('user-delete')
                        <li>
                            <form action="{{ route('admin.users.force-delete', $user->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('تحذير: هذا حذف نهائي ولا يمكن التراجع عنه. هل أنت متأكد؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fa-solid fa-trash-can me-2"></i> حذف نهائي
                                </button>
                            </form>
                        </li>
                    @endcan
                </ul>
            </div>
        </td>
    </tr>

    @include('admin.pages.users.delete', ['user' => $user])
    @include('admin.pages.users.change_password', ['user' => $user])
@empty
    <tr>
        <td colspan="8" class="text-center text-danger fw-bold">
            لا توجد بيانات متاحة
        </td>
    </tr>
@endforelse

