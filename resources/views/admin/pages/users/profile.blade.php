@extends('admin.layouts.master')

@section('page-title')
    ملف المستخدم
@stop

@push('styles')
    @include('admin.pages.users.partials.users-profile-styles')
    @include('admin.pages.users.partials.user-subscription-shared-styles')
@endpush

@section('content')
    @php
        $profileRedirectPath = parse_url(route('users.show', $user), PHP_URL_PATH) ?: '/users/' . $user->id;
        $isStudent = $user->hasRole('student');
        $classRows = $isStudent
            ? $user->classEnrollments->sortBy(fn ($ce) => $ce->schoolClass?->name ?? '')
            : collect();
        $enrollmentRows = $isStudent
            ? $user->enrollments->sortBy(fn ($e) => $e->subject?->name ?? '')
            : collect();
        $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
        $classSubscriptionMap = $classSubscriptionMap ?? [];
        $approvedClassCount = $classRows->where('status', 'approved')->count();
        $activeSubjectCount = $enrollmentRows->where('status', 'active')->count();
    @endphp

    <div class="main-content app-content user-profile-page">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3 py-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3 py-2" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-3 py-2" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (request()->query('notice') === 'class_detached')
                <div class="alert alert-success alert-dismissible fade show mt-3 py-2" role="alert">
                    تم فصل الطالب عن الصف بنجاح.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="user-profile-toolbar">
                <div>
                    <h1 class="user-profile-toolbar__title">ملف المستخدم</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">الطلاب</a></li>
                            <li class="breadcrumb-item active">{{ $user->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="user-profile-toolbar__actions">
                    <a href="{{ route('users.index') }}" class="btn btn-light border">
                        <i class="bi bi-arrow-right me-1"></i> رجوع
                    </a>
                    @can('user-edit')
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> تعديل
                        </a>
                    @endcan
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-xl-3">
                    <div class="user-profile-sidebar">
                        <div class="user-profile-sidebar__banner"></div>
                        <div class="user-profile-sidebar__body">
                            <div class="user-profile-sidebar__avatar">
                                @if ($user->photo)
                                    <img src="{{ media_public_url($user->photo) }}" alt="{{ $user->name }}">
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
                            <div class="user-profile-sidebar__name">{{ $user->name }}</div>
                            <div class="user-profile-sidebar__contact">
                                @if ($user->phone)
                                    <div dir="ltr"><i class="bi bi-telephone me-1"></i>{{ $user->phone }}</div>
                                @endif
                                @if ($user->email)
                                    <div class="text-truncate"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
                                @endif
                            </div>

                            <div class="user-profile-sidebar__badges">
                                @foreach ($user->getRoleNames() as $role)
                                    <span class="user-profile-badge user-profile-badge--role">{{ $role }}</span>
                                @endforeach
                                @if ($user->is_active)
                                    <span class="user-profile-badge user-profile-badge--active"><i class="bi bi-check-circle"></i> نشط</span>
                                @else
                                    <span class="user-profile-badge user-profile-badge--inactive"><i class="bi bi-x-circle"></i> معطّل</span>
                                @endif
                            </div>

                            @if ($isStudent)
                                <div class="user-profile-stats">
                                    <div class="user-profile-stat">
                                        <span class="user-profile-stat__value">{{ $approvedClassCount }}</span>
                                        <span class="user-profile-stat__label">صفوف</span>
                                    </div>
                                    <div class="user-profile-stat">
                                        <span class="user-profile-stat__value">{{ $activeSubjectCount }}</span>
                                        <span class="user-profile-stat__label">مواد نشطة</span>
                                    </div>
                                    <div class="user-profile-stat">
                                        <span class="user-profile-stat__value" style="font-size:0.75rem;line-height:1.4;padding-top:0.2rem">
                                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans(null, true) : '—' }}
                                        </span>
                                        <span class="user-profile-stat__label">آخر دخول</span>
                                    </div>
                                </div>
                            @else
                                <p class="small text-muted mb-0 pt-2 border-top">
                                    آخر دخول: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'لا يوجد' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-xl-9">
                    <div class="user-profile-panel">
                        <div class="user-profile-panel__head">
                            <div>
                                <h6><i class="bi bi-person-vcard me-2 text-primary"></i>معلومات الحساب</h6>
                            </div>
                        </div>
                        <div class="user-profile-info-grid">
                            <div class="user-profile-info-item">
                                <label>الاسم الكامل</label>
                                <span>{{ $user->name }}</span>
                            </div>
                            <div class="user-profile-info-item">
                                <label>تاريخ الإنشاء</label>
                                <span>{{ $user->created_at?->format('Y-m-d') }}</span>
                            </div>
                            <div class="user-profile-info-item">
                                <label>آخر تحديث</label>
                                <span>{{ $user->updated_at?->format('Y-m-d') }}</span>
                            </div>
                            @if ($user->phone)
                                <div class="user-profile-info-item">
                                    <label>الهاتف</label>
                                    <span dir="ltr">{{ $user->phone }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($isStudent)
                        <div class="user-profile-panel">
                            <div class="user-profile-panel__head">
                                <div>
                                    <h6><i class="bi bi-mortarboard me-2 text-primary"></i>الصفوف الدراسية</h6>
                                    <small>انضمامات الصف مع تاريخ نهاية الاشتراك</small>
                                </div>
                                @can('enrollment-create')
                                    @if ($classesForAssign->isNotEmpty())
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssignClass">
                                            <i class="bi bi-link-45deg me-1"></i> ربط بصف
                                        </button>
                                    @endif
                                @endcan
                            </div>

                            @if ($classRows->isEmpty())
                                <div class="user-profile-empty">
                                    <i class="bi bi-inbox"></i>
                                    لا توجد صفوف مسجّلة لهذا الطالب
                                </div>
                            @else
                                <div class="user-profile-table-wrap">
                                    <table class="user-profile-table">
                                        <thead>
                                            <tr>
                                                <th>الصف</th>
                                                <th>المرحلة</th>
                                                <th>الحالة</th>
                                                <th>نهاية الاشتراك</th>
                                                <th class="text-end">إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($classRows as $ce)
                                                @php
                                                    $statusClass = match ($ce->status) {
                                                        'approved' => 'user-profile-status--approved',
                                                        'pending' => 'user-profile-status--pending',
                                                        'rejected' => 'user-profile-status--rejected',
                                                        default => '',
                                                    };
                                                    $statusLabel = match ($ce->status) {
                                                        'approved' => 'معتمد',
                                                        'pending' => 'معلق',
                                                        'rejected' => 'مرفوض',
                                                        default => $ce->status,
                                                    };
                                                    $subscription = ($ce->status === 'approved' && isset($classSubscriptionMap[$ce->class_id]))
                                                        ? $classSubscriptionMap[$ce->class_id]
                                                        : null;
                                                @endphp
                                                <tr>
                                                    <td><span class="class-name">{{ $ce->schoolClass?->name ?? '—' }}</span></td>
                                                    <td><span class="stage-name">{{ $ce->schoolClass?->stage?->name ?? '—' }}</span></td>
                                                    <td>
                                                        <span class="user-profile-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($ce->status === 'approved' && $subscription)
                                                            @include('admin.pages.users.partials.profile-subscription-cell', [
                                                                'user' => $user,
                                                                'subscription' => $subscription,
                                                            ])
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @can('user-edit')
                                                            @if ($ce->status === 'approved')
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-danger btn-detach-class"
                                                                        data-class-id="{{ $ce->class_id }}"
                                                                        data-class-name="{{ $ce->schoolClass?->name ?? '' }}">
                                                                    <i class="bi bi-x-lg"></i> فصل
                                                                </button>
                                                            @endif
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="user-profile-panel">
                            <div class="user-profile-panel__head">
                                <div>
                                    <h6><i class="bi bi-book me-2 text-primary"></i>المواد الدراسية</h6>
                                    <small>انضمامات المواد المرتبطة بالطالب</small>
                                </div>
                                @can('enrollment-create')
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssignSubjects">
                                        <i class="bi bi-plus-lg me-1"></i> ربط بمواد
                                    </button>
                                @endcan
                            </div>

                            @if ($enrollmentRows->isEmpty())
                                <div class="user-profile-empty">
                                    <i class="bi bi-journal-x"></i>
                                    لا توجد مواد مسجّلة
                                </div>
                            @else
                                <div class="user-profile-table-wrap">
                                    <table class="user-profile-table">
                                        <thead>
                                            <tr>
                                                <th>المادة</th>
                                                <th>الصف</th>
                                                <th>الحالة</th>
                                                <th>تاريخ التسجيل</th>
                                                <th class="text-end">إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($enrollmentRows as $enrollment)
                                                @php
                                                    $enrStatusClass = match ($enrollment->status) {
                                                        'active' => 'user-profile-status--active',
                                                        'pending' => 'user-profile-status--pending',
                                                        'suspended' => 'user-profile-status--suspended',
                                                        'completed' => 'user-profile-status--completed',
                                                        default => '',
                                                    };
                                                    $enrStatusLabel = match ($enrollment->status) {
                                                        'active' => 'نشط',
                                                        'pending' => 'معلق',
                                                        'suspended' => 'معلّق',
                                                        'completed' => 'مكتمل',
                                                        default => $enrollment->status,
                                                    };
                                                @endphp
                                                <tr>
                                                    <td><span class="class-name">{{ $enrollment->subject?->name ?? '—' }}</span></td>
                                                    <td><span class="stage-name">{{ $enrollment->subject?->schoolClass?->name ?? '—' }}</span></td>
                                                    <td><span class="user-profile-status {{ $enrStatusClass }}">{{ $enrStatusLabel }}</span></td>
                                                    <td class="stage-name">{{ $enrollment->enrolled_at?->format('Y-m-d') ?? '—' }}</td>
                                                    <td class="text-end">
                                                        @can('enrollment-delete')
                                                            <form action="{{ route('admin.enrollments.destroy', $enrollment) }}"
                                                                  method="post"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('إزالة انضمام هذه المادة؟');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="redirect_to" value="{{ $profileRedirectPath }}">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($isStudent)
        @can('enrollment-create')
            @if ($classesForAssign->isNotEmpty())
            <div class="modal fade" id="modalAssignClass" tabindex="-1" aria-labelledby="modalAssignClassLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="{{ route('admin.enrollments.assign-class-to-user') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <input type="hidden" name="redirect_to" value="{{ $profileRedirectPath }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalAssignClassLabel">ربط الطالب بصف دراسي</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">سيتم اعتماد انضمام الصف وإنشاء انضمامات للمواد النشطة في هذا الصف.</p>
                                <div class="mb-3">
                                    <label class="form-label">اختر الصف</label>
                                    <select name="class_id" class="form-select" required>
                                        <option value="" disabled selected>— اختر الصف —</option>
                                        @foreach ($classesForAssign as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->name }}@if($sc->stage) — {{ $sc->stage->name }} @endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">حفظ الربط</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <div class="modal fade" id="modalAssignSubjects" tabindex="-1" aria-labelledby="modalAssignSubjectsLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <form method="post" action="{{ route('admin.enrollments.store') }}" id="formAssignSubjects">
                            @csrf
                            <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                            <input type="hidden" name="status" value="active">
                            <input type="hidden" name="redirect_to" value="{{ $profileRedirectPath }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalAssignSubjectsLabel">ربط الطالب بمواد</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <div id="assignSubjectsLoadError" class="alert alert-danger d-none small mb-3" role="alert"></div>
                                <div class="row g-3">
                                    @if ($classesForAssign->isNotEmpty())
                                    <div class="col-md-6">
                                        <label class="form-label">تصفية حسب الصف</label>
                                        <select class="form-select" id="assignSubjectsClassFilter">
                                            <option value="">جميع المواد</option>
                                            @foreach ($classesForAssign as $sc)
                                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-12">
                                        <label class="form-label">اختر المواد <span class="text-danger">*</span></label>
                                        <select name="subject_ids[]" id="assignSubjectsSelect" class="form-select" multiple size="10"></select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">ملاحظات (اختياري)</label>
                                        <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">إضافة الانضمامات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif
@stop

@section('js')
    @if ($isStudent)
        @can('user-edit')
            @include('admin.pages.users.partials.user-subscription-date-script')
        @endcan
        @can('enrollment-create')
            <script>
                (function () {
                    const subjectsUrl = @json(route('admin.enrollments.get-subjects-by-class'));
                    const classFilter = document.getElementById('assignSubjectsClassFilter');
                    const subjectSelect = document.getElementById('assignSubjectsSelect');
                    const modalSubjects = document.getElementById('modalAssignSubjects');
                    const loadErrorEl = document.getElementById('assignSubjectsLoadError');

                    function hideSubjectsLoadError() {
                        if (loadErrorEl) {
                            loadErrorEl.classList.add('d-none');
                            loadErrorEl.textContent = '';
                        }
                    }

                    function showSubjectsLoadError(msg) {
                        if (loadErrorEl) {
                            loadErrorEl.textContent = msg;
                            loadErrorEl.classList.remove('d-none');
                        }
                    }

                    function loadSubjects(classId) {
                        if (!subjectSelect) return;
                        hideSubjectsLoadError();
                        subjectSelect.classList.add('subject-select-loading');
                        const url = classId ? subjectsUrl + '?class_id=' + encodeURIComponent(classId) : subjectsUrl;
                        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function (r) {
                                if (!r.ok) throw new Error('HTTP_' + r.status);
                                return r.json();
                            })
                            .then(function (json) {
                                subjectSelect.innerHTML = '';
                                if (!json || json.success === false) {
                                    showSubjectsLoadError('تعذر تحميل قائمة المواد.');
                                    return;
                                }
                                (json.data || []).forEach(function (sub) {
                                    const opt = document.createElement('option');
                                    opt.value = sub.id;
                                    const rel = sub.school_class || sub.schoolClass;
                                    opt.textContent = sub.name + (rel && rel.name ? ' — ' + rel.name : '');
                                    subjectSelect.appendChild(opt);
                                });
                            })
                            .catch(function () {
                                subjectSelect.innerHTML = '';
                                showSubjectsLoadError('تعذر تحميل قائمة المواد.');
                            })
                            .finally(function () {
                                subjectSelect.classList.remove('subject-select-loading');
                            });
                    }

                    if (classFilter && subjectSelect) {
                        classFilter.addEventListener('change', function () { loadSubjects(this.value || ''); });
                    }
                    if (modalSubjects) {
                        modalSubjects.addEventListener('show.bs.modal', function () {
                            if (classFilter) classFilter.value = '';
                            loadSubjects('');
                        });
                    }

                    document.querySelectorAll('.btn-detach-class').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const classId = this.getAttribute('data-class-id');
                            const className = this.getAttribute('data-class-name') || '';
                            if (!classId || !confirm('فصل الطالب عن الصف: ' + className + '؟')) return;
                            const token = document.querySelector('meta[name="csrf-token"]');
                            fetch(@json(route('users.detach-from-class')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({ user_id: {{ (int) $user->id }}, class_id: parseInt(classId, 10) })
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (data.success) {
                                        var p = @json($profileRedirectPath);
                                        window.location.href = p + (p.indexOf('?') === -1 ? '?' : '&') + 'notice=class_detached';
                                    } else {
                                        alert(data.message || 'تعذر تنفيذ العملية');
                                    }
                                })
                                .catch(function () { alert('حدث خطأ في الاتصال'); });
                        });
                    });
                })();
            </script>
        @endcan
    @endif
@stop
