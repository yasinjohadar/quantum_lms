@forelse ($users as $user)
    <tr>
        <td>
            @if (!$user->is_archived)
                <input type="checkbox" name="selected_user_ids[]" value="{{ $user->id }}" class="form-check-input user-checkbox">
            @endif
        </td>
        <th scope="row">{{ $loop->iteration }}</th>

        <td>
            <div class="d-flex align-items-center">
                <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none">
                    {{ $user->name }}
                </a>
            </div>
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
                -
            @endif
        </td>

        <td>
            <button type="button"
                    class="btn btn-sm d-inline-flex align-items-center {{ $user->is_active ? 'btn-success' : 'btn-outline-danger' }}"
                    data-bs-toggle="modal"
                    data-bs-target="#toggleStatus{{ $user->id }}">
                @if ($user->is_active)
                    <i class="fa-solid fa-check-circle me-1"></i>
                    <span>مفعل</span>
                @else
                    <i class="fa-solid fa-ban me-1"></i>
                    <span>معطل</span>
                @endif
            </button>
        </td>

        <td class="users-classes-col">
            @if ($user->classEnrollments->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @foreach ($user->classEnrollments as $enrollment)
                        @if ($enrollment->schoolClass)
                            @php
                                $pendingClassEnrollment = $enrollment->status === 'pending';
                            @endphp
                            <span class="badge {{ $pendingClassEnrollment ? 'bg-warning text-dark' : 'bg-info text-dark' }} d-inline-flex align-items-center gap-2">
                                {{ $enrollment->schoolClass->name }}
                                @if ($pendingClassEnrollment)
                                    <span class="small">قيد المراجعة</span>
                                @else
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning detach-class-btn px-2 py-0"
                                            data-user-id="{{ $user->id }}"
                                            data-class-id="{{ $enrollment->class_id }}"
                                            title="فصل الطالب عن هذا الصف">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                @endif
                            </span>
                        @endif
                    @endforeach
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        <td>
            <div class="d-flex gap-1 flex-wrap">
                @can('user-impersonate')
                    <button type="button" class="btn btn-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#impersonateModal{{ $user->id }}"
                            title="تسجيل الدخول كالمستخدم">
                        <i class="fas fa-user-secret"></i>
                    </button>
                @endcan

                @can('enrollment-create')
                    @if (isset($classesForAssign) && $classesForAssign->isNotEmpty())
                        <button type="button"
                                class="btn btn-primary btn-sm quick-assign-class-trigger"
                                data-bs-toggle="modal"
                                data-bs-target="#quickAssignClassModal"
                                data-user-id="{{ $user->id }}"
                                title="ربط بصف دراسي">
                            <i class="fas fa-link"></i>
                        </button>
                    @endif
                    <button type="button"
                            class="btn btn-success btn-sm quick-assign-subjects-trigger"
                            data-bs-toggle="modal"
                            data-bs-target="#quickAssignSubjectsModal"
                            data-user-id="{{ $user->id }}"
                            title="ربط بمواد">
                        <i class="fas fa-book"></i>
                    </button>
                @endcan

                <a class="btn btn-info btn-sm" href="{{ route('users.edit', $user->id) }}" title="تعديل المستخدم">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>

                @if ($user->hasRole('teacher'))
                    <a class="btn btn-success btn-sm" href="{{ route('admin.teachers.assignments', $user->id) }}"
                       title="تخصيص الصفوف والمواد">
                        <i class="fa-solid fa-user-tie"></i>
                    </a>
                @endif

                <a class="btn btn-secondary btn-sm" href="{{ route('users.login-logs', $user->id) }}" title="سجلات الدخول">
                    <i class="fa-solid fa-sign-in-alt"></i>
                </a>

                @if ($user->phone && !$user->phone_verified_at)
                    <button type="button"
                            class="btn btn-success btn-sm send-otp-btn"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            data-user-phone="{{ $user->phone }}"
                            title="إرسال كود التحقق">
                        <i class="fa-solid fa-message"></i>
                    </button>
                @endif

                @if (! $user->is_archived)
                    <button type="button" class="btn btn-warning btn-sm archive-user-btn"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            title="أرشفة المستخدم">
                        <i class="fas fa-archive"></i>
                    </button>
                @endif

                <a class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete{{ $user->id }}"
                   title="حذف المستخدم">
                    <i class="fa-solid fa-trash-can"></i>
                </a>

                <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#change_password{{ $user->id }}"
                   title="تعديل كلمة السر">
                    <i class="fa-solid fa-key"></i>
                </a>
            </div>
        </td>
    </tr>

    {{-- Modals for each user --}}
    @include('admin.pages.users.toggle_status', ['user' => $user])
    @include('admin.pages.users.delete', ['user' => $user])
    @include('admin.pages.users.change_password', ['user' => $user])
@empty
    <tr>
        <td colspan="7" class="text-center text-danger fw-bold">
            لا توجد بيانات متاحة
        </td>
    </tr>
@endforelse

