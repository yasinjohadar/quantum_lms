@foreach($teachers as $teacher)
    @php
        $initial = mb_strtoupper(mb_substr(trim($teacher->name), 0, 1));
        $assignedClasses = $teacher->assignedClasses;
        $assignedSubjects = $teacher->assignedSubjects;
        $lastLogin = $lastLogins[$teacher->id] ?? $teacher->last_login_at;
        $isOnline = $onlineUserIds->contains($teacher->id);
        $prog = $teachersProgress[$teacher->id] ?? null;
        $quizCount = (int) ($prog['quizzes_created'] ?? 0);
    @endphp
    <tr>
        <td class="text-muted small">{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
        <td data-tv-col="name">
            <div class="tv-user-cell">
                <span class="tv-user-avatar">
                    @if($teacher->photo)
                        <img src="{{ media_public_url($teacher->photo) }}" alt="">
                    @else
                        {{ $initial }}
                    @endif
                </span>
                <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="tv-user-name">
                    {{ $teacher->name }}
                </a>
            </div>
        </td>
        <td data-tv-col="email">
            <a href="mailto:{{ $teacher->email }}" class="text-decoration-none small">{{ $teacher->email }}</a>
        </td>
        <td data-tv-col="roles">
            @if($teacher->roles->count() > 0)
                @foreach($teacher->roles as $role)
                    <span class="tv-role-pill">{{ $role->name }}</span>
                @endforeach
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td data-tv-col="classes">
            @if($assignedClasses->count() > 0)
                <span class="tv-count-badge tv-count-badge--class">{{ $assignedClasses->count() }} صف</span>
                <div class="tv-meta-list">
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
        <td data-tv-col="subjects">
            @if($assignedSubjects->count() > 0)
                <span class="tv-count-badge tv-count-badge--subject">{{ $assignedSubjects->count() }} مادة</span>
                <div class="tv-meta-list">
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
        <td data-tv-col="status">
            @can('user-toggle-status')
                <button type="button"
                        class="tv-status-badge {{ $teacher->is_active ? 'tv-status-badge--active' : 'tv-status-badge--inactive' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#toggleStatus{{ $teacher->id }}"
                        title="تغيير حالة الحساب">
                    <i class="bi {{ $teacher->is_active ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                    {{ $teacher->is_active ? 'مفعل' : 'معطل' }}
                </button>
            @else
                <span class="tv-status-badge {{ $teacher->is_active ? 'tv-status-badge--active' : 'tv-status-badge--inactive' }}">
                    {{ $teacher->is_active ? 'مفعل' : 'معطل' }}
                </span>
            @endcan
        </td>
        <td data-tv-col="last_login">
            @if($lastLogin)
                <span class="small">{{ \Carbon\Carbon::parse($lastLogin)->format('Y-m-d H:i') }}</span>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td data-tv-col="online">
            <span class="tv-online-badge {{ $isOnline ? 'tv-online-badge--on' : 'tv-online-badge--off' }}">
                <i class="bi {{ $isOnline ? 'bi-circle-fill' : 'bi-circle' }} me-1" style="font-size: 0.5rem;"></i>
                {{ $isOnline ? 'متصل' : 'غير متصل' }}
            </span>
        </td>
        <td data-tv-col="quizzes" class="text-center">
            <div class="tv-quiz-count">
                <span class="tv-count-badge tv-count-badge--quiz">{{ number_format($quizCount) }}</span>
                <span class="tv-quiz-count__label">اختبار</span>
            </div>
        </td>
        <td data-tv-col="progress">
            @if($prog)
                <div class="tv-progress-box">
                    <div class="tv-progress-row">
                        <span class="tv-progress-label">الصفحات:</span>
                        @if($prog['pages_required'] > 0)
                            <span>{{ $prog['pages_completed'] }}/{{ $prog['pages_required'] }}</span>
                            @if($prog['pages_percentage'] !== null)
                                @php
                                    $pp = $prog['pages_percentage'];
                                    $ppClass = $pp >= 100 ? 'tv-progress-pill--high' : ($pp >= 50 ? 'tv-progress-pill--mid' : 'tv-progress-pill--low');
                                @endphp
                                <span class="tv-progress-pill {{ $ppClass }}">{{ number_format($pp, 1) }}%</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="tv-progress-row">
                        <span class="tv-progress-label">الأسبوع:</span>
                        @if($prog['weekly_target'] > 0)
                            <span>{{ $prog['weekly_completed'] }}/{{ $prog['weekly_target'] }}</span>
                            @if($prog['weekly_percentage'] !== null)
                                @php
                                    $wp = $prog['weekly_percentage'];
                                    $wpClass = $wp >= 100 ? 'tv-progress-pill--high' : ($wp >= 50 ? 'tv-progress-pill--mid' : 'tv-progress-pill--low');
                                @endphp
                                <span class="tv-progress-pill {{ $wpClass }}">{{ number_format($wp, 1) }}%</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="tv-progress-row">
                        <span class="tv-progress-label">دروس:</span>
                        <span>{{ $prog['total_approved_lessons'] }}</span>
                    </div>
                    <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="tv-progress-link">تفاصيل ←</a>
                </div>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td>
            @canany(['user-impersonate', 'teacher-assignment-show', 'teacher-progress-view', 'user-edit', 'user-delete', 'teacher-assignment-update'])
                <div class="row-action-bar">
                    @can('user-impersonate')
                        <button type="button" class="row-action-btn row-action-btn--info"
                                data-bs-toggle="modal"
                                data-bs-target="#impersonateModal{{ $teacher->id }}"
                                title="تسجيل الدخول كمستخدم">
                            <i class="bi bi-incognito"></i>
                        </button>
                    @endcan

                    @can('teacher-assignment-show')
                        <a class="row-action-btn row-action-btn--primary"
                           href="{{ route('admin.teachers.assignments', $teacher->id) }}"
                           title="تخصيص">
                            <i class="bi bi-sliders"></i>
                        </a>
                    @endcan

                    @can('teacher-progress-view')
                        <a class="row-action-btn row-action-btn--secondary"
                           href="{{ route('admin.teachers.progress.history', $teacher->id) }}"
                           title="إحصائيات سابقة">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        <a class="row-action-btn row-action-btn--success"
                           href="{{ route('admin.teachers.progress.show', $teacher->id) }}"
                           title="تقدم المعلم">
                            <i class="bi bi-graph-up"></i>
                        </a>
                    @endcan

                    @can('user-edit')
                        <a class="row-action-btn row-action-btn--primary"
                           href="{{ route('users.edit', ['user' => $teacher->id, 'role' => 'teacher']) }}"
                           title="تعديل">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endcan

                    @can('teacher-assignment-update')
                        <button type="button" class="row-action-btn row-action-btn--warning"
                                data-bs-toggle="modal"
                                data-bs-target="#resetTeacherPassword{{ $teacher->id }}"
                                title="إعادة تعيين كلمة المرور وإرسالها عبر واتساب">
                            <i class="bi bi-key"></i>
                        </button>
                    @endcan

                    @can('user-delete')
                        <span class="row-action-divider" aria-hidden="true"></span>
                        <button type="button" class="row-action-btn row-action-btn--danger"
                                data-bs-toggle="modal"
                                data-bs-target="#delete{{ $teacher->id }}"
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
    @can('user-toggle-status')
        @include('admin.pages.users.toggle_status', ['user' => $teacher])
    @endcan
    @can('user-delete')
        @include('admin.pages.users.delete', ['user' => $teacher])
    @endcan
    @can('teacher-assignment-update')
        @include('admin.pages.teachers.partials.reset-password-modal', ['teacher' => $teacher])
    @endcan
@endforeach
