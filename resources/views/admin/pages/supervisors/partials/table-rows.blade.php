@foreach($supervisors as $supervisor)
    <tr>
        <td>{{ $loop->iteration + ($supervisors->currentPage() - 1) * $supervisors->perPage() }}</td>
        <td>
            <div class="d-flex align-items-center">
                @if($supervisor->photo)
                    <img src="{{ media_public_url($supervisor->photo) }}"
                         alt="{{ $supervisor->name }}"
                         class="rounded-circle me-2"
                         style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                         style="width: 40px; height: 40px;">
                        {{ substr($supervisor->name, 0, 1) }}
                    </div>
                @endif
                <span class="fw-semibold">{{ $supervisor->name }}</span>
            </div>
        </td>
        <td>{{ $supervisor->email }}</td>
        <td>
            @if($supervisor->roles->count() > 0)
                <div class="d-flex flex-wrap gap-1">
                    @foreach($supervisor->roles as $role)
                        <span class="badge bg-secondary">{{ $role->name }}</span>
                    @endforeach
                </div>
            @else
                <span class="text-muted">لا يوجد</span>
            @endif
        </td>
        <td>
            @php
                $assignedClasses = $supervisor->assignedClassesAsSupervisor;
            @endphp
            @if($assignedClasses->count() > 0)
                <span class="badge bg-primary">
                    {{ $assignedClasses->count() }} صف
                </span>
                <div class="mt-1">
                    @foreach($assignedClasses->take(2) as $class)
                        <small class="d-block text-muted">{{ $class->name }}</small>
                    @endforeach
                    @if($assignedClasses->count() > 2)
                        <small class="text-muted">+ {{ $assignedClasses->count() - 2 }} أخرى</small>
                    @endif
                </div>
            @else
                <span class="text-muted">لا يوجد</span>
            @endif
        </td>
        <td>
            @php
                $assignedSubjects = $supervisor->assignedSubjectsAsSupervisor;
            @endphp
            @if($assignedSubjects->count() > 0)
                <span class="badge bg-info">
                    {{ $assignedSubjects->count() }} مادة
                </span>
                <div class="mt-1">
                    @foreach($assignedSubjects->take(2) as $subject)
                        <small class="d-block text-muted">{{ $subject->name }}</small>
                    @endforeach
                    @if($assignedSubjects->count() > 2)
                        <small class="text-muted">+ {{ $assignedSubjects->count() - 2 }} أخرى</small>
                    @endif
                </div>
            @else
                <span class="text-muted">لا يوجد</span>
            @endif
        </td>
        <td>
            @php
                $lastLogin = $lastLogins[$supervisor->id] ?? $supervisor->last_login_at;
            @endphp
            @if($lastLogin)
                {{ \Carbon\Carbon::parse($lastLogin)->format('Y-m-d H:i') }}
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($onlineUserIds->contains($supervisor->id))
                <span class="badge bg-success"><i class="fa-solid fa-circle fa-fw" style="font-size: 0.5em; vertical-align: middle;"></i> متصل الآن</span>
            @else
                <span class="badge bg-secondary">غير متصل</span>
            @endif
        </td>
        <td class="text-center">
            @canany(['user-impersonate', 'supervisor-assignment-show', 'user-edit', 'user-delete'])
                <div class="dropdown">
                    <button class="btn btn-sm btn-icon btn-light"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="true"
                            aria-expanded="false"
                            title="الإجراءات"
                            aria-label="الإجراءات">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @can('user-impersonate')
                            <li>
                                <button type="button"
                                        class="dropdown-item"
                                        data-bs-toggle="modal"
                                        data-bs-target="#impersonateModal{{ $supervisor->id }}">
                                    <i class="fas fa-user-secret me-2 text-info"></i> تسجيل الدخول كالمستخدم
                                </button>
                            </li>
                        @endcan
                        @can('supervisor-assignment-show')
                            <li>
                                <a class="dropdown-item"
                                   href="{{ route('admin.supervisors.assignments', $supervisor->id) }}">
                                    <i class="fas fa-user-tie me-2 text-primary"></i> تخصيص
                                </a>
                            </li>
                        @endcan
                        @can('user-edit')
                            <li>
                                <a class="dropdown-item"
                                   href="{{ route('users.edit', ['user' => $supervisor->id, 'role' => 'supervisor']) }}">
                                    <i class="fa-solid fa-pen-to-square me-2 text-info"></i> تعديل
                                </a>
                            </li>
                        @endcan
                        @can('user-delete')
                            @if(auth()->user()->can('user-impersonate') || auth()->user()->can('supervisor-assignment-show') || auth()->user()->can('user-edit'))
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#delete{{ $supervisor->id }}">
                                    <i class="fa-solid fa-trash-can me-2"></i> حذف
                                </button>
                            </li>
                        @endcan
                    </ul>
                </div>
            @else
                <span class="text-muted">—</span>
            @endcanany
        </td>
    </tr>
@endforeach
