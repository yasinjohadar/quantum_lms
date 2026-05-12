@extends('admin.layouts.master')

@section('page-title')
    ملف المستخدم
@stop

@push('styles')
    <style>
        .user-profile-hero {
            border-radius: 1rem;
            border: 1px solid rgba(13, 110, 253, 0.15);
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.06) 0%, rgba(13, 110, 253, 0.02) 100%);
        }
        .user-profile-avatar {
            width: 112px;
            height: 112px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }
        .user-profile-stat {
            font-size: 0.8125rem;
            color: #6c757d;
        }
        .user-profile-card {
            border-radius: 0.875rem;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .user-profile-table thead th {
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: none;
            border-bottom-width: 1px;
        }
        .subject-select-loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
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
        $classStatusMap = [
            'pending' => ['label' => 'معلق', 'class' => 'bg-warning text-dark'],
            'approved' => ['label' => 'معتمد', 'class' => 'bg-success'],
            'rejected' => ['label' => 'مرفوض', 'class' => 'bg-danger'],
        ];
        $enrollmentStatusMap = [
            'active' => ['label' => 'نشط', 'class' => 'bg-success'],
            'pending' => ['label' => 'معلق', 'class' => 'bg-warning text-dark'],
            'suspended' => ['label' => 'معلّق', 'class' => 'bg-secondary'],
            'completed' => ['label' => 'مكتمل', 'class' => 'bg-info text-dark'],
        ];
    @endphp

    <div class="main-content app-content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (request()->query('notice') === 'class_detached')
                <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                    تم فصل الطالب عن الصف بنجاح.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h5 class="page-title fs-21 mb-1">ملف المستخدم</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <a href="{{ route('users.index') }}" class="btn btn-light border btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                    @can('user-edit')
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-pen me-1"></i> تعديل
                        </a>
                    @endcan
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card user-profile-card user-profile-hero h-100">
                        <div class="card-body text-center py-4">
                            <div class="mb-3">
                                <img src="{{ $user->photo ? media_public_url($user->photo) : asset('assets/images/faces/default-avatar.jpg') }}"
                                     alt="{{ $user->name }}"
                                     class="rounded-circle user-profile-avatar">
                            </div>
                            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                            @if ($user->email)
                                <p class="mb-1">
                                    <a href="mailto:{{ $user->email }}" class="text-primary text-decoration-none small">
                                        {{ $user->email }}
                                    </a>
                                </p>
                            @endif
                            @if ($user->phone)
                                <p class="mb-2 text-muted small">{{ $user->phone }}</p>
                            @endif

                            <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
                                @foreach ($user->getRoleNames() as $role)
                                    <span class="badge bg-primary">{{ $role }}</span>
                                @endforeach
                            </div>

                            <div class="mb-2">
                                @if ($user->is_active)
                                    <span class="badge bg-success">حساب نشط</span>
                                @else
                                    <span class="badge bg-danger">حساب غير نشط</span>
                                @endif
                            </div>

                            <p class="user-profile-stat mb-0">
                                آخر دخول:
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'لا يوجد' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card user-profile-card mb-4">
                        <div class="card-header border-bottom-0 pb-0">
                            <h6 class="mb-0 fw-semibold">معلومات الحساب</h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <span class="user-profile-stat d-block mb-1">الاسم الكامل</span>
                                    <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <span class="user-profile-stat d-block mb-1">تاريخ الإنشاء</span>
                                    <p class="mb-0 fw-semibold">{{ $user->created_at?->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <span class="user-profile-stat d-block mb-1">تاريخ آخر تحديث</span>
                                    <p class="mb-0 fw-semibold">{{ $user->updated_at?->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($isStudent)
                        <div class="card user-profile-card mb-4">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                                <div>
                                    <h6 class="mb-0 fw-semibold">الصفوف الدراسية</h6>
                                    <small class="text-muted">انضمامات على مستوى الصف (معتمد / معلق / مرفوض)</small>
                                </div>
                                @can('enrollment-create')
                                    @if ($classesForAssign->isNotEmpty())
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssignClass">
                                            <i class="fas fa-link me-1"></i> ربط بصف
                                        </button>
                                    @endif
                                @endcan
                            </div>
                            <div class="card-body p-0">
                                @if ($classRows->isEmpty())
                                    <p class="text-muted text-center py-4 mb-0 small">لا توجد صفوف مسجّلة لهذا الطالب.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover user-profile-table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">الصف</th>
                                                    <th>المرحلة</th>
                                                    <th>الحالة</th>
                                                    <th class="text-end pe-4">إجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($classRows as $ce)
                                                    @php
                                                        $st = $classStatusMap[$ce->status] ?? ['label' => $ce->status, 'class' => 'bg-secondary'];
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-4 fw-medium">{{ $ce->schoolClass?->name ?? '—' }}</td>
                                                        <td class="text-muted small">{{ $ce->schoolClass?->stage?->name ?? '—' }}</td>
                                                        <td><span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span></td>
                                                        <td class="text-end pe-4">
                                                            @can('user-edit')
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-danger btn-detach-class"
                                                                        data-class-id="{{ $ce->class_id }}"
                                                                        data-class-name="{{ $ce->schoolClass?->name ?? '' }}">
                                                                    فصل عن الصف
                                                                </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card user-profile-card">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                                <div>
                                    <h6 class="mb-0 fw-semibold">المواد الدراسية</h6>
                                    <small class="text-muted">انضمامات المواد المرتبطة بالطالب</small>
                                </div>
                                @can('enrollment-create')
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssignSubjects">
                                        <i class="fas fa-book me-1"></i> ربط بمواد
                                    </button>
                                @endcan
                            </div>
                            <div class="card-body p-0">
                                @if ($enrollmentRows->isEmpty())
                                    <p class="text-muted text-center py-4 mb-0 small">لا توجد مواد مسجّلة.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover user-profile-table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">المادة</th>
                                                    <th>الصف</th>
                                                    <th>الحالة</th>
                                                    <th>تاريخ التسجيل</th>
                                                    <th class="text-end pe-4">إجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($enrollmentRows as $enrollment)
                                                    @php
                                                        $st = $enrollmentStatusMap[$enrollment->status] ?? ['label' => $enrollment->status, 'class' => 'bg-secondary'];
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-4 fw-medium">{{ $enrollment->subject?->name ?? '—' }}</td>
                                                        <td class="text-muted small">{{ $enrollment->subject?->schoolClass?->name ?? '—' }}</td>
                                                        <td><span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span></td>
                                                        <td class="small text-muted">{{ $enrollment->enrolled_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                                        <td class="text-end pe-4">
                                                            @can('enrollment-delete')
                                                                <form action="{{ route('admin.enrollments.destroy', $enrollment) }}"
                                                                      method="post"
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('إزالة انضمام هذه المادة؟');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="redirect_to" value="{{ $profileRedirectPath }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">إزالة</button>
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
                                <p class="text-muted small mb-3">سيتم اعتماد انضمام الصف وإنشاء انضمامات للمواد النشطة في هذا الصف (مع تخطي المواد المسجّل فيها بنشاط مسبقاً).</p>
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
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000" placeholder="ملاحظات داخلية…"></textarea>
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
                                        <label class="form-label">تصفية حسب الصف (اختياري)</label>
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
                                        <small class="text-muted">استخدم Ctrl أو Shift لاختيار أكثر من مادة.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">ملاحظات (اختياري)</label>
                                        <textarea name="notes" class="form-control" rows="2" maxlength="1000" placeholder="ملاحظات…"></textarea>
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
                        const url = classId
                            ? subjectsUrl + '?class_id=' + encodeURIComponent(classId)
                            : subjectsUrl;
                        fetch(url, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(function (r) {
                                if (!r.ok) {
                                    throw new Error('HTTP_' + r.status);
                                }
                                return r.json();
                            })
                            .then(function (json) {
                                subjectSelect.innerHTML = '';
                                if (!json || json.success === false) {
                                    showSubjectsLoadError('تعذر تحميل قائمة المواد. تحقق من الصلاحيات أو حاول لاحقاً.');
                                    return;
                                }
                                const rows = json.data ? json.data : [];
                                rows.forEach(function (sub) {
                                    const opt = document.createElement('option');
                                    opt.value = sub.id;
                                    const rel = sub.school_class || sub.schoolClass;
                                    const className = rel && rel.name ? rel.name : '';
                                    opt.textContent = sub.name + (className ? ' — ' + className : '');
                                    subjectSelect.appendChild(opt);
                                });
                            })
                            .catch(function () {
                                subjectSelect.innerHTML = '';
                                showSubjectsLoadError('تعذر تحميل قائمة المواد (خطأ في الاتصال، رد غير JSON، أو رفض الخادم مثل 403). تحقق من الصلاحيات أو جرّب تحديث الصفحة.');
                            })
                            .finally(function () {
                                subjectSelect.classList.remove('subject-select-loading');
                            });
                    }

                    if (classFilter && subjectSelect) {
                        classFilter.addEventListener('change', function () {
                            loadSubjects(this.value || '');
                        });
                    }

                    if (modalSubjects) {
                        modalSubjects.addEventListener('show.bs.modal', function () {
                            if (classFilter) {
                                classFilter.value = '';
                            }
                            loadSubjects('');
                        });
                    }

                    document.querySelectorAll('.btn-detach-class').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const classId = this.getAttribute('data-class-id');
                            const className = this.getAttribute('data-class-name') || '';
                            if (!classId || !confirm('فصل الطالب عن الصف: ' + className + '؟ سيتم حذف انضمامات مواد هذا الصف أيضاً.')) {
                                return;
                            }
                            const token = document.querySelector('meta[name="csrf-token"]');
                            fetch(@json(route('users.detach-from-class')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    user_id: {{ (int) $user->id }},
                                    class_id: parseInt(classId, 10)
                                })
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (data.success) {
                                        var p = @json($profileRedirectPath);
                                        var sep = p.indexOf('?') === -1 ? '?' : '&';
                                        window.location.href = p + sep + 'notice=class_detached';
                                    } else {
                                        alert(data.message || 'تعذر تنفيذ العملية');
                                    }
                                })
                                .catch(function () {
                                    alert('حدث خطأ في الاتصال');
                                });
                        });
                    });
                })();
            </script>
        @endcan
    @endif
@stop
