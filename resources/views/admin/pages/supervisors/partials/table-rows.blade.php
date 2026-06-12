@foreach($supervisors as $supervisor)
    @php
        $initial = mb_strtoupper(mb_substr(trim($supervisor->name), 0, 1));
        $assignedClasses = $supervisor->assignedClassesAsSupervisor;
        $assignedSubjects = $supervisor->assignedSubjectsAsSupervisor;
        $lastLogin = $lastLogins[$supervisor->id] ?? $supervisor->last_login_at;
        $isOnline = $onlineUserIds->contains($supervisor->id);
    @endphp
    <tr>
        <td class="text-muted small">{{ $loop->iteration + ($supervisors->currentPage() - 1) * $supervisors->perPage() }}</td>
        <td>
            <div class="sv-user-cell">
                <span class="sv-user-avatar">
                    @if($supervisor->photo)
                        <img src="{{ media_public_url($supervisor->photo) }}" alt="">
                    @else
                        {{ $initial }}
                    @endif
                </span>
                <a href="{{ route('admin.supervisors.overview', $supervisor) }}" class="sv-user-name">
                    {{ $supervisor->name }}
                </a>
            </div>
        </td>
        <td>
            <a href="mailto:{{ $supervisor->email }}" class="text-decoration-none small">{{ $supervisor->email }}</a>
        </td>
        <td>
            @if($supervisor->roles->count() > 0)
                @foreach($supervisor->roles as $role)
                    <span class="sv-role-pill">{{ $role->name }}</span>
                @endforeach
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td>
            @if($assignedClasses->count() > 0)
                <span class="sv-count-badge sv-count-badge--class">{{ $assignedClasses->count() }} صف</span>
                <div class="sv-meta-list">
                    @foreach($assignedClasses->take(2) as $class)
                        <span class="d-block">{{ $class->name }}</span>
                    @endforeach
                    @if($assignedClasses->count() > 2)
                        <span>+ {{ $assignedClasses->count() - 2 }} أخرى</span>
                    @endif
                </div>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td>
            @if($assignedSubjects->count() > 0)
                <span class="sv-count-badge sv-count-badge--subject">{{ $assignedSubjects->count() }} مادة</span>
                <div class="sv-meta-list">
                    @foreach($assignedSubjects->take(2) as $subject)
                        <span class="d-block">{{ $subject->name }}</span>
                    @endforeach
                    @if($assignedSubjects->count() > 2)
                        <span>+ {{ $assignedSubjects->count() - 2 }} أخرى</span>
                    @endif
                </div>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td>
            @if($lastLogin)
                <span class="small">{{ \Carbon\Carbon::parse($lastLogin)->format('Y-m-d H:i') }}</span>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td>
            <span class="sv-online-badge {{ $isOnline ? 'sv-online-badge--on' : 'sv-online-badge--off' }}">
                <i class="bi {{ $isOnline ? 'bi-circle-fill' : 'bi-circle' }} me-1" style="font-size: 0.5rem;"></i>
                {{ $isOnline ? 'متصل الآن' : 'غير متصل' }}
            </span>
        </td>
        <td>
            @canany(['user-impersonate', 'supervisor-assignment-show', 'user-edit', 'user-delete'])
                <div class="row-action-bar">
                    @can('user-impersonate')
                        <button type="button" class="row-action-btn row-action-btn--info"
                                data-bs-toggle="modal"
                                data-bs-target="#impersonateModal{{ $supervisor->id }}"
                                title="تسجيل الدخول كمستخدم">
                            <i class="bi bi-incognito"></i>
                        </button>
                    @endcan

                    <a class="row-action-btn row-action-btn--secondary"
                       href="{{ route('admin.supervisors.overview', $supervisor) }}"
                       title="نظرة عامة">
                        <i class="bi bi-person-lines-fill"></i>
                    </a>

                    @can('supervisor-assignment-show')
                        <a class="row-action-btn row-action-btn--primary"
                           href="{{ route('admin.supervisors.assignments', $supervisor->id) }}"
                           title="تخصيص">
                            <i class="bi bi-sliders"></i>
                        </a>
                    @endcan

                    @can('user-edit')
                        <a class="row-action-btn row-action-btn--primary"
                           href="{{ route('users.edit', ['user' => $supervisor->id, 'role' => 'supervisor']) }}"
                           title="تعديل">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endcan

                    @can('user-delete')
                        <span class="row-action-divider" aria-hidden="true"></span>
                        <button type="button" class="row-action-btn row-action-btn--danger"
                                data-bs-toggle="modal"
                                data-bs-target="#delete{{ $supervisor->id }}"
                                title="حذف">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endcan
                </div>
            @else
                <span class="text-muted small">—</span>
            @endcanany
        </td>
    </tr>
@endforeach
