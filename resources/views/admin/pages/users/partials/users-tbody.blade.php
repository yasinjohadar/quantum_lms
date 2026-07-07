@forelse ($users as $user)
    @php
        $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
        $rowNum = $loop->iteration + ($users->currentPage() - 1) * $users->perPage();
    @endphp
    <tr>
        <td>
            @if (!$user->is_archived)
                <input type="checkbox" name="selected_user_ids[]" value="{{ $user->id }}" class="form-check-input user-checkbox">
            @endif
        </td>
        <th scope="row" class="text-muted small">{{ $rowNum }}</th>

        <td>
            <div class="ui-user-cell">
                <span class="ui-user-avatar">{{ $initial }}</span>
                <a href="{{ route('users.show', $user->id) }}" class="ui-user-name">
                    {{ $user->name }}
                </a>
            </div>
        </td>

        <td>
            @if ($user->phone)
                <div class="d-flex align-items-center gap-1">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank"
                       class="ui-phone-link text-success text-decoration-none" title="فتح WhatsApp">
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
            <button type="button"
                    class="ui-status-badge {{ $user->is_active ? 'ui-status-badge--active' : 'ui-status-badge--inactive' }}"
                    data-bs-toggle="modal"
                    data-bs-target="#toggleStatus{{ $user->id }}"
                    title="تغيير حالة الحساب">
                <i class="bi {{ $user->is_active ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                {{ $user->is_active ? 'مفعل' : 'معطل' }}
            </button>
        </td>

        @include('admin.pages.users.partials.subscription-expires-cell', [
            'user' => $user,
            'selectedClassId' => $selectedClassId ?? null,
        ])

        <td class="users-classes-col">
            @if ($user->classEnrollments->isNotEmpty())
                <div class="d-flex flex-wrap">
                    @foreach ($user->classEnrollments as $enrollment)
                        @if ($enrollment->schoolClass)
                            @php $pendingClassEnrollment = $enrollment->status === 'pending'; @endphp
                            <span class="ui-class-pill {{ $pendingClassEnrollment ? 'ui-class-pill--pending' : 'ui-class-pill--approved' }}">
                                {{ $enrollment->schoolClass->name }}
                                @if ($pendingClassEnrollment)
                                    <span class="opacity-75">· قيد المراجعة</span>
                                @else
                                    <button type="button"
                                            class="detach-class-btn"
                                            data-user-id="{{ $user->id }}"
                                            data-class-id="{{ $enrollment->class_id }}"
                                            title="فصل عن الصف">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif
                            </span>
                        @endif
                    @endforeach
                </div>
            @else
                <span class="text-muted small">—</span>
            @endif
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

                @can('enrollment-create')
                    @if (isset($classesForAssign) && $classesForAssign->isNotEmpty())
                        <button type="button"
                                class="row-action-btn row-action-btn--primary quick-assign-class-trigger"
                                data-bs-toggle="modal"
                                data-bs-target="#quickAssignClassModal"
                                data-user-id="{{ $user->id }}"
                                title="ربط بصف دراسي">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    @endif
                    <button type="button"
                            class="row-action-btn row-action-btn--success quick-assign-subjects-trigger"
                            data-bs-toggle="modal"
                            data-bs-target="#quickAssignSubjectsModal"
                            data-user-id="{{ $user->id }}"
                            title="ربط بمواد">
                        <i class="bi bi-journal-bookmark"></i>
                    </button>
                @endcan

                <a class="row-action-btn row-action-btn--primary"
                   href="{{ route('users.edit', $user->id) }}"
                   title="تعديل">
                    <i class="bi bi-pencil"></i>
                </a>

                @if ($user->hasRole('teacher'))
                    <a class="row-action-btn row-action-btn--success"
                       href="{{ route('admin.teachers.assignments', $user->id) }}"
                       title="تخصيص الصفوف والمواد">
                        <i class="bi bi-person-badge"></i>
                    </a>
                @endif

                <a class="row-action-btn row-action-btn--secondary"
                   href="{{ route('users.login-logs', $user->id) }}"
                   title="سجلات الدخول">
                    <i class="bi bi-clock-history"></i>
                </a>

                @if ($user->phone && !$user->phone_verified_at)
                    <button type="button"
                            class="row-action-btn row-action-btn--success send-otp-btn"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            data-user-phone="{{ $user->phone }}"
                            title="إرسال كود التحقق">
                        <i class="bi bi-chat-dots"></i>
                    </button>
                @endif

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
            </div>
        </td>
    </tr>

    @include('admin.pages.users.toggle_status', ['user' => $user])
    @include('admin.pages.users.delete', ['user' => $user])
    @include('admin.pages.users.change_password', ['user' => $user])
@empty
    <tr>
        <td colspan="8">
            <div class="users-index-empty">
                <i class="bi bi-mortarboard"></i>
                <p class="mb-0 fw-semibold">لا يوجد طلاب مطابقين للفلاتر</p>
            </div>
        </td>
    </tr>
@endforelse
