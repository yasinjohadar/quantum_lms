@foreach($teachers as $teacher)
    <tr>
        <td>{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
        <td>
            <div class="d-flex align-items-center">
                @if($teacher->photo)
                    <img src="{{ media_public_url($teacher->photo) }}"
                         alt="{{ $teacher->name }}"
                         class="rounded-circle me-2"
                         style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                         style="width: 40px; height: 40px;">
                        {{ substr($teacher->name, 0, 1) }}
                    </div>
                @endif
                <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="fw-semibold text-decoration-none">{{ $teacher->name }}</a>
            </div>
        </td>
        <td>{{ $teacher->email }}</td>
        <td>
            @if($teacher->roles->count() > 0)
                <div class="d-flex flex-wrap gap-1">
                    @foreach($teacher->roles as $role)
                        <span class="badge bg-secondary">{{ $role->name }}</span>
                    @endforeach
                </div>
            @else
                <span class="text-muted">لا يوجد</span>
            @endif
        </td>
        <td>
            @php $assignedClasses = $teacher->assignedClasses; @endphp
            @if($assignedClasses->count() > 0)
                <span class="badge bg-primary">{{ $assignedClasses->count() }} صف</span>
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
            @php $assignedSubjects = $teacher->assignedSubjects; @endphp
            @if($assignedSubjects->count() > 0)
                <span class="badge bg-info">{{ $assignedSubjects->count() }} مادة</span>
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
            @can('user-toggle-status')
            <button type="button"
                    class="btn btn-sm d-inline-flex align-items-center {{ $teacher->is_active ? 'btn-success' : 'btn-outline-danger' }}"
                    data-bs-toggle="modal"
                    data-bs-target="#toggleStatus{{ $teacher->id }}">
                @if($teacher->is_active)
                    <i class="fa-solid fa-check-circle me-1"></i><span>مفعل</span>
                @else
                    <i class="fa-solid fa-ban me-1"></i><span>معطل</span>
                @endif
            </button>
            @else
                <span class="badge {{ $teacher->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $teacher->is_active ? 'مفعل' : 'معطل' }}</span>
            @endcan
        </td>
        <td>
            @php $lastLogin = $lastLogins[$teacher->id] ?? $teacher->last_login_at; @endphp
            @if($lastLogin)
                {{ \Carbon\Carbon::parse($lastLogin)->format('Y-m-d H:i') }}
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($onlineUserIds->contains($teacher->id))
                <span class="badge bg-success"><i class="fa-solid fa-circle fa-fw" style="font-size: 0.5em; vertical-align: middle;"></i> متصل الآن</span>
            @else
                <span class="badge bg-secondary">غير متصل</span>
            @endif
        </td>
        <td class="text-nowrap">
            @php $prog = $teachersProgress[$teacher->id] ?? null; @endphp
            @if($prog)
                <div class="d-flex flex-column gap-1 small">
                    <div>
                        <span class="text-muted">الصفحات:</span>
                        @if($prog['pages_required'] > 0)
                            {{ $prog['pages_completed'] }} / {{ $prog['pages_required'] }}
                            @if($prog['pages_percentage'] !== null)
                                <span class="badge {{ $prog['pages_percentage'] >= 100 ? 'bg-success' : ($prog['pages_percentage'] >= 50 ? 'bg-info' : 'bg-warning text-dark') }} ms-1">{{ number_format($prog['pages_percentage'], 1) }}%</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-muted">الأسبوع:</span>
                        @if(isset($prog['current_week']) && $prog['current_week'])
                            <span class="small text-muted" title="{{ $prog['current_week']->start_date->format('Y-m-d') }} - {{ $prog['current_week']->end_date->format('Y-m-d') }}">({{ $prog['current_week']->title ?? 'أسبوع ' . $prog['current_week']->week_number }})</span>
                        @endif
                        @if($prog['weekly_target'] > 0)
                            {{ $prog['weekly_completed'] }}/{{ $prog['weekly_target'] }}
                            @if($prog['weekly_percentage'] !== null)
                                <span class="badge {{ $prog['weekly_percentage'] >= 100 ? 'bg-success' : ($prog['weekly_percentage'] >= 50 ? 'bg-info' : 'bg-warning text-dark') }} ms-1">{{ number_format($prog['weekly_percentage'], 1) }}%</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div><span class="text-muted">دروس مسجلة:</span> {{ $prog['total_approved_lessons'] }}</div>
                    <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="small">تفاصيل</a>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-center">
            @canany(['user-impersonate', 'teacher-assignment-show', 'teacher-progress-view', 'user-edit', 'user-delete'])
                <div class="dropdown">
                    <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false" title="الإجراءات" aria-label="الإجراءات">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @can('user-impersonate')
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#impersonateModal{{ $teacher->id }}"><i class="fas fa-user-secret me-2 text-info"></i> تسجيل الدخول كالمستخدم</button></li>
                        @endcan
                        @can('teacher-assignment-show')
                            <li><a class="dropdown-item" href="{{ route('admin.teachers.assignments', $teacher->id) }}"><i class="fas fa-user-tie me-2 text-primary"></i> تخصيص</a></li>
                        @endcan
                        @can('teacher-progress-view')
                            <li><a class="dropdown-item" href="{{ route('admin.teachers.progress.history', $teacher->id) }}"><i class="bi bi-clock-history me-2 text-secondary"></i> إحصائيات سابقة</a></li>
                        @endcan
                        @can('user-edit')
                            <li><a class="dropdown-item" href="{{ route('users.edit', ['user' => $teacher->id, 'role' => 'teacher']) }}"><i class="fa-solid fa-pen-to-square me-2 text-info"></i> تعديل</a></li>
                        @endcan
                        @can('user-delete')
                            @if(auth()->user()->can('user-impersonate') || auth()->user()->can('teacher-assignment-show') || auth()->user()->can('teacher-progress-view') || auth()->user()->can('user-edit'))
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $teacher->id }}"><i class="fa-solid fa-trash-can me-2"></i> حذف</button></li>
                        @endcan
                    </ul>
                </div>
            @else
                <span class="text-muted">—</span>
            @endcanany
        </td>
    </tr>
    @can('user-toggle-status')
    @include('admin.pages.users.toggle_status', ['user' => $teacher])
    @endcan
    @can('user-delete')
    @include('admin.pages.users.delete', ['user' => $teacher])
    @endcan
@endforeach
