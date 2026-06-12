@extends('admin.layouts.master')

@section('page-title')
    كافة الطلاب
@stop



@push('styles')
    @include('admin.pages.users.partials.users-index-styles')
@endpush

@section('content')
    <div class="main-content app-content users-index-page">
        <div class="container-fluid">

            <div class="users-index-hero my-4">
                <div class="users-index-hero__icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="users-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">كافة الطلاب</li>
                        </ol>
                    </nav>
                    <h4 class="users-index-hero__title">كافة الطلاب</h4>
                    <p class="users-index-hero__subtitle">إدارة حسابات الطلاب، الصفوف، والانضمامات</p>
                </div>
                <div class="users-index-stat-mini">
                    <span class="users-index-stat-mini__value">{{ number_format($users->total()) }}</span>
                    <span class="users-index-stat-mini__label">طالب مطابق</span>
                </div>
                <div class="users-index-hero__actions">
                    @can('user-create')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#quickAddStudentModal">
                            <i class="bi bi-person-plus me-1"></i> إضافة طالب
                        </button>
                    @endcan
                    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus-fill me-1"></i> مستخدم جديد
                    </a>
                    <a href="{{ route('admin.archived-users.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-archive me-1"></i> الأرشيف
                    </a>
                    <a href="{{ route('admin.users.manage') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-people me-1"></i> كل المستخدمين
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="users-index-card">
                <div class="users-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="users-index-card__header-icon"><i class="bi bi-funnel"></i></span>
                        تصفية وبحث
                    </div>
                </div>
                <div class="users-index-card__body">
                    <form id="usersFiltersForm" action="{{ route('users.index') }}" method="GET" class="users-index-filters">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="query" class="form-control border-start-0"
                                           placeholder="الاسم أو الهاتف" value="{{ request('query') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">حالة الحساب</label>
                                <select name="is_active" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active', '1') == '1' ? 'selected' : '' }}>مفعل</option>
                                    <option value="0" {{ request('is_active', '1') == '0' ? 'selected' : '' }}>معطل</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">الصف الدراسي</label>
                                <select name="class_id" id="classFilter" class="form-select">
                                    <option value="">كل الصفوف</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == (string)$class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                @if(request()->hasAny(['query','class_id','is_active']) && (request('query') || request('class_id') || request('is_active') !== null))
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-x-lg me-1"></i> مسح
                                    </a>
                                @endif
                                @include('admin.partials.per-page-toolbar', ['paginator' => $users])
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="users-index-card">
                <div class="users-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="users-index-card__header-icon"><i class="bi bi-table"></i></span>
                        قائمة الطلاب
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button"
                                class="btn btn-outline-danger btn-sm"
                                id="detachAllByScopeBtn">
                            <i class="bi bi-person-x me-1"></i> فصل الكل حسب الصف/المادة
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                id="toggleUsersClassesColBtn"
                                title="إظهار أو إخفاء عمود الصفوف">
                            <i class="bi bi-building me-1"></i>
                            <span id="toggleUsersClassesColLabel">إظهار الصفوف</span>
                        </button>
                        <span class="badge bg-primary-transparent text-primary">
                            صفحة {{ $users->currentPage() }} من {{ $users->lastPage() }}
                        </span>
                    </div>
                </div>
                <div class="users-index-card__body p-0">
                    <form id="bulk-archive-form" action="{{ route('admin.users.bulk-archive') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_ids" id="user_ids_input">
                        <input type="hidden" name="reason" id="archive_reason_input">

                        <div class="px-3 pt-3">
                            <div class="users-bulk-toolbar" id="usersBulkToolbar" style="display: none;">
                                <span class="small text-muted me-2"><i class="bi bi-check2-square me-1"></i> إجراءات جماعية:</span>
                                <button type="button" class="btn btn-warning btn-sm" id="bulk-archive-btn">
                                    <i class="bi bi-archive me-1"></i> أرشفة المحدد
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="bulk-detach-class-btn">
                                    <i class="bi bi-person-x me-1"></i> فصل المحدد عن الصف
                                    <span class="badge bg-light text-dark ms-1" id="bulkDetachSelectedCount">0</span>
                                </button>
                            </div>
                        </div>

                        <div class="users-index-table-wrap mx-3 mb-0">
                            <div class="table-responsive">
                                <table class="table users-index-table align-middle mb-0 users-table hide-classes-col" id="usersTable">
                                    <thead>
                                    <tr>
                                        <th scope="col" style="width: 40px;">
                                            <input type="checkbox" id="select-all-users" class="form-check-input">
                                        </th>
                                        <th scope="col" style="width: 48px;">#</th>
                                        <th scope="col">اسم الطالب</th>
                                        <th scope="col">الهاتف</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col" class="users-classes-col">الصفوف</th>
                                        <th scope="col" style="min-width: 220px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                    @include('admin.pages.users.partials.users-tbody', ['users' => $users, 'classesForAssign' => $classesForAssign])
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="px-3 pb-3 users-index-pagination" id="usersPaginationContainer">
                            {{ $users->withQueryString()->links() }}
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

<!-- Modal أرشفة المستخدم -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="archiveModalLabel">
                    <i class="bi bi-archive-fill me-2"></i> أرشفة المستخدم
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <i class="bi bi-archive-fill text-warning" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-center mb-3">هل أنت متأكد من أرشفة هذا المستخدم؟</h5>
                <p class="text-muted text-center mb-3">
                    <strong id="archiveUserName"></strong>
                </p>
                <div class="mb-3">
                    <label for="archiveReason" class="form-label">سبب الأرشفة (اختياري)</label>
                    <textarea class="form-control" id="archiveReason" name="reason" rows="3" 
                              placeholder="أدخل سبب الأرشفة (اختياري)"></textarea>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    سيتم نقل المستخدم إلى الأرشيف ويمكن استعادته لاحقاً.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="archiveForm" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="reason" id="archiveReasonInput">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-archive me-1"></i> نعم، أرشفة المستخدم
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal فصل جماعي عن الصف -->
<div class="modal fade" id="bulkDetachClassModal" tabindex="-1" aria-labelledby="bulkDetachClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDetachClassModalLabel">
                    <i class="fas fa-user-slash me-2"></i> فصل جماعي عن الصف
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-info">
                    سيتم فصل الطلاب المحددين من الصف الذي تختاره.
                </div>

                <div class="mb-3">
                    <label for="bulkDetachClassId" class="form-label">اختر الصف</label>
                    <select id="bulkDetachClassId" class="form-select">
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == (string)$class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-danger" id="bulkDetachConfirmBtn">
                    <i class="fas fa-user-slash me-1"></i> تأكيد الفصل
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal فصل الكل حسب الصف/المادة -->
<div class="modal fade" id="detachAllByScopeModal" tabindex="-1" aria-labelledby="detachAllByScopeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="detachAllByScopeModalLabel">
                    <i class="fas fa-user-slash me-2"></i> فصل الكل حسب الصف/المادة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-3">
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="detachAllScope" id="detachAllScopeClass" value="class" checked>
                            <label class="form-check-label" for="detachAllScopeClass">حسب الصف</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="detachAllScope" id="detachAllScopeSubject" value="subject">
                            <label class="form-check-label" for="detachAllScopeSubject">حسب مادة ضمن صف</label>
                        </div>
                    </div>
                </div>

                <div id="detachAllClassSection" class="mb-3">
                    <label for="detachAllClassId" class="form-label">اختر الصف</label>
                    <select id="detachAllClassId" class="form-select">
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == (string)$class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="detachAllSubjectSection" class="mb-3" style="display: none;">
                    <label class="form-label">اختر الصف</label>
                    <select id="detachAllSubjectClassId" class="form-select mb-2">
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == (string)$class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="detachAllSubjectId" class="form-label">اختر المادة</label>
                    <select id="detachAllSubjectId" class="form-select" disabled>
                        <option value="">اختر المادة</option>
                    </select>
                    <div class="form-text mt-2">
                        سيتم فصل انضمامات المادة فقط (لا يتم حذف انضمام الصف).
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-danger" id="detachAllConfirmBtn">
                    <i class="fas fa-user-slash me-1"></i> تأكيد الفصل
                </button>
            </div>
        </div>
    </div>
</div>

@can('user-create')
    <div class="modal fade" id="quickAddStudentModal" tabindex="-1" aria-labelledby="quickAddStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg quick-add-student-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('users.store-quick-student') }}" id="formQuickAddStudent" autocomplete="off">
                    @csrf
                    <input type="hidden" name="_quick_student" value="1">
                    @if (request()->filled('query'))
                        <input type="hidden" name="list_query" value="{{ request('query') }}">
                    @endif
                    @if (request()->has('is_active'))
                        <input type="hidden" name="list_is_active" value="{{ request('is_active') }}">
                    @endif
                    @if (request()->filled('class_id'))
                        <input type="hidden" name="list_class_id" value="{{ request('class_id') }}">
                    @endif
                    @if (request()->filled('per_page'))
                        <input type="hidden" name="list_per_page" value="{{ request('per_page') }}">
                    @endif
                    @if (request()->filled('page'))
                        <input type="hidden" name="list_page" value="{{ request('page') }}">
                    @endif

                    <div class="modal-header">
                        <h5 class="modal-title" id="quickAddStudentModalLabel">إضافة طالب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">يُنشأ حساب بدور <strong>طالب</strong> مباشرة. يمكنك ربطه بصف (مع مواد الصف) أو بمواد محددة.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required maxlength="255"
                                    value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required maxlength="255"
                                    value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الهاتف (اختياري)</label>
                                <input type="text" name="phone" class="form-control" maxlength="20"
                                    placeholder="+9665xxxxxxxx" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="quickAddStudentActive"
                                        value="1" @checked((string) old('is_active', '1') === '1')>
                                    <label class="form-check-label" for="quickAddStudentActive">حساب مفعّل</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="8"
                                    autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required
                                    minlength="8" autocomplete="new-password">
                            </div>
                        </div>

                        @can('enrollment-create')
                            <hr class="my-4">
                            <div class="mb-2 fw-semibold">ربط بعد الإنشاء</div>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input quick-add-attach-mode" type="radio" name="attach_mode"
                                        id="quickAttachNone" value="none"
                                        {{ old('attach_mode', 'none') === 'none' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="quickAttachNone">بدون ربط</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input quick-add-attach-mode" type="radio" name="attach_mode"
                                        id="quickAttachClass" value="class"
                                        {{ old('attach_mode') === 'class' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="quickAttachClass">ربط بصف</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input quick-add-attach-mode" type="radio" name="attach_mode"
                                        id="quickAttachSubjects" value="subjects"
                                        {{ old('attach_mode') === 'subjects' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="quickAttachSubjects">ربط بمواد</label>
                                </div>
                            </div>

                            <div id="quickAddAttachClassBlock" class="mb-3" style="display: none;">
                                @if (isset($classesForAssign) && $classesForAssign->isNotEmpty())
                                    <label class="form-label">الصف الدراسي <span class="text-danger">*</span></label>
                                    <select name="assign_class_id" id="quickAddAssignClassId" class="form-select">
                                        <option value="">— اختر الصف —</option>
                                        @foreach ($classesForAssign as $sc)
                                            <option value="{{ $sc->id }}"
                                                {{ (string) old('assign_class_id') === (string) $sc->id ? 'selected' : '' }}>
                                                {{ $sc->name }}@if ($sc->stage) — {{ $sc->stage->name }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="alert alert-warning small mb-0">لا توجد صفوف متاحة للربط.</div>
                                @endif
                            </div>

                            <div id="quickAddAttachSubjectsBlock" class="mb-3" style="display: none;">
                                <div id="quickAddStudentSubjectsLoadError" class="alert alert-danger d-none small mb-3"
                                    role="alert"></div>
                                @if (isset($classesForAssign) && $classesForAssign->isNotEmpty())
                                    <div class="mb-2">
                                        <label class="form-label">تصفية حسب الصف</label>
                                        <select class="form-select" id="quickAddStudentSubjectsClassFilter">
                                            <option value="">جميع المواد</option>
                                            @foreach ($classesForAssign as $sc)
                                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <label class="form-label">المواد <span class="text-danger">*</span></label>
                                <select name="subject_ids[]" id="quickAddStudentSubjectsSelect"
                                    class="form-select quick-add-subjects-select" multiple size="5"></select>
                                <small class="text-muted d-block mt-1">استخدم Ctrl أو Shift لاختيار أكثر من مادة.</small>
                            </div>
                        @else
                            <input type="hidden" name="attach_mode" value="none">
                        @endcan

                        <div class="mt-3">
                            <label class="form-label">ملاحظات الربط (اختياري)</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="1000"
                                placeholder="ملاحظات داخلية للانضمام…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success">حفظ وإنشاء الطالب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('enrollment-create')
    @if (isset($classesForAssign) && $classesForAssign->isNotEmpty())
        <div class="modal fade" id="quickAssignClassModal" tabindex="-1" aria-labelledby="quickAssignClassModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.enrollments.assign-class-to-user') }}">
                        @csrf
                        <input type="hidden" name="user_id" id="quickAssignClassUserId" value="">
                        <input type="hidden" name="redirect_to" id="quickAssignClassRedirectTo" value="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="quickAssignClassModalLabel">ربط الطالب بصف دراسي</h5>
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

    <div class="modal fade" id="quickAssignSubjectsModal" tabindex="-1" aria-labelledby="quickAssignSubjectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.enrollments.store') }}" id="formQuickAssignSubjects">
                    @csrf
                    <input type="hidden" name="user_ids[]" id="quickAssignSubjectsUserId" value="">
                    <input type="hidden" name="status" value="active">
                    <input type="hidden" name="redirect_to" id="quickAssignSubjectsRedirectTo" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quickAssignSubjectsModalLabel">ربط الطالب بمواد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div id="quickAssignSubjectsLoadError" class="alert alert-danger d-none small mb-3" role="alert"></div>
                        <div class="row g-3">
                            @if (isset($classesForAssign) && $classesForAssign->isNotEmpty())
                                <div class="col-md-6">
                                    <label class="form-label">تصفية حسب الصف (اختياري)</label>
                                    <select class="form-select" id="quickAssignSubjectsClassFilter">
                                        <option value="">جميع المواد</option>
                                        @foreach ($classesForAssign as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <label class="form-label">اختر المواد <span class="text-danger">*</span></label>
                                <select name="subject_ids[]" id="quickAssignSubjectsSelect" class="form-select" multiple size="10"></select>
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

@stop

@section('js')
<script>
    @if ($errors->any() && old('_quick_student'))
        document.addEventListener('DOMContentLoaded', function () {
            var _qModal = document.getElementById('quickAddStudentModal');
            if (_qModal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(_qModal).show();
            }
        });
    @endif
    // إظهار الرسائل تلقائياً
    document.addEventListener('DOMContentLoaded', function() {
        // Bulk archive functionality
        const selectAllUsers = document.getElementById('select-all-users');
        const usersTableBody = document.getElementById('usersTableBody');
        const getUserCheckboxes = () => document.querySelectorAll('.user-checkbox');
        const bulkArchiveBtn = document.getElementById('bulk-archive-btn');
            const bulkDetachBtn = document.getElementById('bulk-detach-class-btn');
        const bulkArchiveForm = document.getElementById('bulk-archive-form');
        const userIdsInput = document.getElementById('user_ids_input');
        const archiveReasonInput = document.getElementById('archive_reason_input');
            const bulkDetachModalEl = document.getElementById('bulkDetachClassModal');
            const bulkDetachClassIdEl = document.getElementById('bulkDetachClassId');
            const bulkDetachConfirmBtn = document.getElementById('bulkDetachConfirmBtn');
            const bulkDetachSelectedCountEl = document.getElementById('bulkDetachSelectedCount');
            const classFilterEl = document.getElementById('classFilter');

        // Detach-all UI
        const detachAllByScopeBtn = document.getElementById('detachAllByScopeBtn');
        const detachAllByScopeModalEl = document.getElementById('detachAllByScopeModal');
        const detachAllConfirmBtn = document.getElementById('detachAllConfirmBtn');
        const detachAllScopeClassRadio = document.getElementById('detachAllScopeClass');
        const detachAllScopeSubjectRadio = document.getElementById('detachAllScopeSubject');
        const detachAllClassSectionEl = document.getElementById('detachAllClassSection');
        const detachAllSubjectSectionEl = document.getElementById('detachAllSubjectSection');
        const detachAllClassIdEl = document.getElementById('detachAllClassId');
        const detachAllSubjectClassIdEl = document.getElementById('detachAllSubjectClassId');
        const detachAllSubjectIdEl = document.getElementById('detachAllSubjectId');
        const getSubjectsUrl = '{{ route("admin.enrollments.get-subjects-by-class") }}';

        const quickAssignClassModalEl = document.getElementById('quickAssignClassModal');
        const quickAssignSubjectsModalEl = document.getElementById('quickAssignSubjectsModal');
        const quickAssignSubjectsClassFilter = document.getElementById('quickAssignSubjectsClassFilter');
        const quickAssignSubjectsSelect = document.getElementById('quickAssignSubjectsSelect');
        const quickAssignSubjectsLoadError = document.getElementById('quickAssignSubjectsLoadError');

        function currentUsersListRedirectTo() {
            return window.location.pathname + (window.location.search || '');
        }

        function hideQuickSubjectsLoadError() {
            if (quickAssignSubjectsLoadError) {
                quickAssignSubjectsLoadError.classList.add('d-none');
                quickAssignSubjectsLoadError.textContent = '';
            }
        }

        function showQuickSubjectsLoadError(msg) {
            if (quickAssignSubjectsLoadError) {
                quickAssignSubjectsLoadError.textContent = msg;
                quickAssignSubjectsLoadError.classList.remove('d-none');
            }
        }

        function loadQuickSubjects(classId) {
            if (!quickAssignSubjectsSelect) return;
            hideQuickSubjectsLoadError();
            quickAssignSubjectsSelect.classList.add('opacity-50');
            const url = classId
                ? getSubjectsUrl + '?class_id=' + encodeURIComponent(classId)
                : getSubjectsUrl;
            fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP_' + r.status);
                    return r.json();
                })
                .then(function (json) {
                    quickAssignSubjectsSelect.innerHTML = '';
                    if (!json || json.success === false) {
                        showQuickSubjectsLoadError('تعذر تحميل قائمة المواد.');
                        return;
                    }
                    const rows = json.data ? json.data : [];
                    rows.forEach(function (sub) {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        const rel = sub.school_class || sub.schoolClass;
                        const className = rel && rel.name ? rel.name : '';
                        opt.textContent = sub.name + (className ? ' — ' + className : '');
                        quickAssignSubjectsSelect.appendChild(opt);
                    });
                })
                .catch(function () {
                    quickAssignSubjectsSelect.innerHTML = '';
                    showQuickSubjectsLoadError('تعذر تحميل المواد. تحقق من الصلاحيات أو الاتصال.');
                })
                .finally(function () {
                    quickAssignSubjectsSelect.classList.remove('opacity-50');
                });
        }

        if (quickAssignClassModalEl) {
            quickAssignClassModalEl.addEventListener('show.bs.modal', function (event) {
                const raw = event.relatedTarget;
                const btn = raw && raw.closest ? raw.closest('.quick-assign-class-trigger') : null;
                if (!btn) return;
                const uid = btn.getAttribute('data-user-id');
                const uEl = document.getElementById('quickAssignClassUserId');
                const rEl = document.getElementById('quickAssignClassRedirectTo');
                if (uEl) uEl.value = uid || '';
                if (rEl) rEl.value = currentUsersListRedirectTo();
            });
        }

        if (quickAssignSubjectsModalEl) {
            quickAssignSubjectsModalEl.addEventListener('show.bs.modal', function (event) {
                const raw = event.relatedTarget;
                const btn = raw && raw.closest ? raw.closest('.quick-assign-subjects-trigger') : null;
                if (!btn) return;
                const uid = btn.getAttribute('data-user-id');
                const uEl = document.getElementById('quickAssignSubjectsUserId');
                const rEl = document.getElementById('quickAssignSubjectsRedirectTo');
                if (uEl) uEl.value = uid || '';
                if (rEl) rEl.value = currentUsersListRedirectTo();
                if (quickAssignSubjectsClassFilter) quickAssignSubjectsClassFilter.value = '';
                loadQuickSubjects('');
            });
        }

        if (quickAssignSubjectsClassFilter && quickAssignSubjectsSelect) {
            quickAssignSubjectsClassFilter.addEventListener('change', function () {
                loadQuickSubjects(this.value || '');
            });
        }

        // ——— إضافة طالب سريع (مودال) ———
        const quickAddStudentModalEl = document.getElementById('quickAddStudentModal');
        const formQuickAddStudent = document.getElementById('formQuickAddStudent');
        const quickAddAttachClassBlock = document.getElementById('quickAddAttachClassBlock');
        const quickAddAttachSubjectsBlock = document.getElementById('quickAddAttachSubjectsBlock');
        const quickAddAssignClassId = document.getElementById('quickAddAssignClassId');
        const quickAddStudentSubjectsSelect = document.getElementById('quickAddStudentSubjectsSelect');
        const quickAddStudentSubjectsClassFilter = document.getElementById('quickAddStudentSubjectsClassFilter');
        const quickAddStudentSubjectsLoadError = document.getElementById('quickAddStudentSubjectsLoadError');

        function hideQuickAddSubjectsLoadError() {
            if (quickAddStudentSubjectsLoadError) {
                quickAddStudentSubjectsLoadError.classList.add('d-none');
                quickAddStudentSubjectsLoadError.textContent = '';
            }
        }

        function showQuickAddSubjectsLoadError(msg) {
            if (quickAddStudentSubjectsLoadError) {
                quickAddStudentSubjectsLoadError.textContent = msg;
                quickAddStudentSubjectsLoadError.classList.remove('d-none');
            }
        }

        function loadQuickAddStudentSubjects(classId) {
            if (!quickAddStudentSubjectsSelect) return;
            hideQuickAddSubjectsLoadError();
            quickAddStudentSubjectsSelect.classList.add('opacity-50');
            const url = classId
                ? getSubjectsUrl + '?class_id=' + encodeURIComponent(classId)
                : getSubjectsUrl;
            fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP_' + r.status);
                    return r.json();
                })
                .then(function (json) {
                    quickAddStudentSubjectsSelect.innerHTML = '';
                    if (!json || json.success === false) {
                        showQuickAddSubjectsLoadError('تعذر تحميل قائمة المواد.');
                        return;
                    }
                    const rows = json.data ? json.data : [];
                    rows.forEach(function (sub) {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        const rel = sub.school_class || sub.schoolClass;
                        const className = rel && rel.name ? rel.name : '';
                        opt.textContent = sub.name + (className ? ' — ' + className : '');
                        quickAddStudentSubjectsSelect.appendChild(opt);
                    });
                })
                .catch(function () {
                    quickAddStudentSubjectsSelect.innerHTML = '';
                    showQuickAddSubjectsLoadError('تعذر تحميل المواد. تحقق من الصلاحيات أو الاتصال.');
                })
                .finally(function () {
                    quickAddStudentSubjectsSelect.classList.remove('opacity-50');
                });
        }

        function getQuickAddAttachMode() {
            const r = document.querySelector('.quick-add-attach-mode:checked');
            return r ? r.value : 'none';
        }

        function syncQuickAddAttachUi() {
            const mode = getQuickAddAttachMode();
            if (quickAddAttachClassBlock) {
                quickAddAttachClassBlock.style.display = mode === 'class' ? '' : 'none';
            }
            if (quickAddAttachSubjectsBlock) {
                quickAddAttachSubjectsBlock.style.display = mode === 'subjects' ? '' : 'none';
            }
            if (quickAddAssignClassId) {
                quickAddAssignClassId.required = mode === 'class';
            }
            if (mode === 'subjects' && quickAddStudentSubjectsSelect && quickAddStudentSubjectsSelect.options.length === 0) {
                const cid = quickAddStudentSubjectsClassFilter ? quickAddStudentSubjectsClassFilter.value : '';
                loadQuickAddStudentSubjects(cid || '');
            }
        }

        document.querySelectorAll('.quick-add-attach-mode').forEach(function (el) {
            el.addEventListener('change', syncQuickAddAttachUi);
        });

        if (quickAddStudentModalEl) {
            quickAddStudentModalEl.addEventListener('show.bs.modal', function () {
                syncQuickAddAttachUi();
            });
        }

        if (quickAddStudentSubjectsClassFilter && quickAddStudentSubjectsSelect) {
            quickAddStudentSubjectsClassFilter.addEventListener('change', function () {
                loadQuickAddStudentSubjects(this.value || '');
            });
        }

        if (formQuickAddStudent) {
            formQuickAddStudent.addEventListener('submit', function (e) {
                const mode = getQuickAddAttachMode();
                if (mode === 'class') {
                    if (!quickAddAssignClassId || !quickAddAssignClassId.value) {
                        e.preventDefault();
                        alert('يرجى اختيار الصف الدراسي.');
                        return false;
                    }
                }
                if (mode === 'subjects') {
                    if (!quickAddStudentSubjectsSelect || quickAddStudentSubjectsSelect.selectedOptions.length === 0) {
                        e.preventDefault();
                        alert('يرجى اختيار مادة واحدة على الأقل.');
                        return false;
                    }
                }
            });
        }

        // Select all functionality
        if (selectAllUsers) {
            selectAllUsers.addEventListener('change', function() {
                getUserCheckboxes().forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleBulkArchiveBtn();
            });
        }

        // Individual checkbox change
        getUserCheckboxes().forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                toggleBulkArchiveBtn();
                updateSelectAllUsers();
            });
        });

        const usersBulkToolbar = document.getElementById('usersBulkToolbar');

        function toggleBulkArchiveBtn() {
            const checked = document.querySelectorAll('.user-checkbox:checked');
            const hasChecked = checked.length > 0;
            if (usersBulkToolbar) {
                usersBulkToolbar.style.display = hasChecked ? 'flex' : 'none';
            }
            if (bulkDetachSelectedCountEl) {
                bulkDetachSelectedCountEl.textContent = checked.length;
            }
        }

        function updateSelectAllUsers() {
            if (selectAllUsers) {
                const checkboxes = getUserCheckboxes();
                const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
                selectAllUsers.checked = allChecked;
            }
        }

        // Bulk archive
        if (bulkArchiveBtn) {
            bulkArchiveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const checked = document.querySelectorAll('.user-checkbox:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                
                if (ids.length === 0) {
                    alert('يرجى اختيار مستخدمين للأرشفة');
                    return;
                }

                const reason = prompt('أدخل سبب الأرشفة (اختياري):');
                if (reason === null) return; // User cancelled

                if (confirm('هل أنت متأكد من أرشفة ' + ids.length + ' مستخدم محدد؟')) {
                    // التأكد من أن الـ form يستخدم POST
                    bulkArchiveForm.method = 'POST';
                    
                    // إزالة أي input fields سابقة لـ user_ids
                    bulkArchiveForm.querySelectorAll('input[name^="user_ids"]').forEach(input => {
                        if (input.id !== 'user_ids_input') {
                            input.remove();
                        }
                    });
                    
                    // إرسال user_ids كـ JSON string (سيتم تحويله في Request)
                    userIdsInput.value = JSON.stringify(ids);
                    archiveReasonInput.value = reason || '';
                    
                    // التأكد من وجود CSRF token
                    if (!bulkArchiveForm.querySelector('input[name="_token"]')) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        bulkArchiveForm.appendChild(csrfInput);
                    }
                    
                    bulkArchiveForm.submit();
                }
            });
        }

        // Bulk detach class
        if (bulkDetachBtn && bulkDetachModalEl && bulkDetachConfirmBtn) {
            bulkDetachBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const checkedIds = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
                if (checkedIds.length === 0) {
                    alert('يرجى اختيار طلاب للفصل');
                    return;
                }

                if (bulkDetachSelectedCountEl) {
                    bulkDetachSelectedCountEl.textContent = checkedIds.length;
                }

                // افتراضي: نفس قيمة فلتر الصف
                if (classFilterEl && bulkDetachClassIdEl) {
                    bulkDetachClassIdEl.value = classFilterEl.value;
                }

                const modal = bootstrap.Modal.getOrCreateInstance(bulkDetachModalEl);
                modal.show();
            });

            bulkDetachConfirmBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const checkedIds = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
                const classId = bulkDetachClassIdEl ? bulkDetachClassIdEl.value : '';

                if (checkedIds.length === 0) {
                    alert('لا يوجد طلاب محددين');
                    return;
                }

                if (!classId) {
                    alert('يرجى اختيار الصف');
                    return;
                }

                this.disabled = true;

                fetch('{{ route("users.detach-multiple-from-class") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ user_ids: checkedIds, class_id: classId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        const modal = bootstrap.Modal.getOrCreateInstance(bulkDetachModalEl);
                        modal.hide();
                        const deleted = data.deleted || {};
                        const classEnrollmentsDeleted = deleted.class_enrollments ?? 0;
                        const enrollmentsDeleted = deleted.enrollments ?? 0;
                        alert(`تم فصل الطلاب بنجاح.\nتم حذف ClassEnrollment: ${classEnrollmentsDeleted}\nتم حذف Enrollment: ${enrollmentsDeleted}`);
                        fetchUsers(1);
                    } else {
                        alert((data && data.message) ? data.message : 'حدث خطأ أثناء الفصل');
                    }
                })
                .catch(err => {
                    console.error('Bulk detach error:', err);
                    alert('حدث خطأ أثناء الفصل');
                })
                .finally(() => {
                    bulkDetachConfirmBtn.disabled = false;
                });
            });
        }

        // Detach all (by class or by subject) without selecting checkboxes
        if (detachAllByScopeBtn && detachAllByScopeModalEl && detachAllConfirmBtn) {
            const detachAllScopeSelector = () => document.querySelector('input[name="detachAllScope"]:checked');

            function showDetachAllSections() {
                const scopeVal = detachAllScopeSelector() ? detachAllScopeSelector().value : 'class';
                const isClass = scopeVal === 'class';

                if (detachAllClassSectionEl) {
                    detachAllClassSectionEl.style.display = isClass ? '' : 'none';
                }
                if (detachAllSubjectSectionEl) {
                    detachAllSubjectSectionEl.style.display = isClass ? 'none' : '';
                }
            }

            function loadSubjectsForDetachAllByClass(classId) {
                if (!detachAllSubjectIdEl || !detachAllSubjectClassIdEl) return;

                if (!classId) {
                    detachAllSubjectIdEl.disabled = true;
                    detachAllSubjectIdEl.innerHTML = '<option value="">اختر المادة</option>';
                    return;
                }

                detachAllSubjectIdEl.disabled = true;
                detachAllSubjectIdEl.innerHTML = '<option value="">جاري التحميل...</option>';

                fetch(`${getSubjectsUrl}?class_id=${encodeURIComponent(classId)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && Array.isArray(data.data)) {
                        if (data.data.length === 0) {
                            detachAllSubjectIdEl.innerHTML = '<option value="">لا توجد مواد</option>';
                        } else {
                            detachAllSubjectIdEl.innerHTML = '';
                            const emptyOpt = document.createElement('option');
                            emptyOpt.value = '';
                            emptyOpt.textContent = 'اختر المادة';
                            detachAllSubjectIdEl.appendChild(emptyOpt);

                            data.data.forEach(subject => {
                                const opt = document.createElement('option');
                                opt.value = subject.id;
                                opt.textContent = subject.name;
                                detachAllSubjectIdEl.appendChild(opt);
                            });
                        }
                        detachAllSubjectIdEl.disabled = false;
                    } else {
                        detachAllSubjectIdEl.innerHTML = '<option value="">لا توجد مواد</option>';
                        detachAllSubjectIdEl.disabled = true;
                    }
                })
                .catch(() => {
                    detachAllSubjectIdEl.innerHTML = '<option value="">خطأ في التحميل</option>';
                    detachAllSubjectIdEl.disabled = true;
                });
            }

            if (detachAllScopeClassRadio) {
                detachAllScopeClassRadio.addEventListener('change', function() {
                    showDetachAllSections();
                });
            }
            if (detachAllScopeSubjectRadio) {
                detachAllScopeSubjectRadio.addEventListener('change', function() {
                    showDetachAllSections();
                    if (detachAllSubjectClassIdEl) {
                        loadSubjectsForDetachAllByClass(detachAllSubjectClassIdEl.value);
                    }
                });
            }

            if (detachAllSubjectClassIdEl) {
                detachAllSubjectClassIdEl.addEventListener('change', function() {
                    loadSubjectsForDetachAllByClass(this.value);
                });
            }

            detachAllByScopeBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // set default values
                if (detachAllScopeClassRadio) detachAllScopeClassRadio.checked = true;
                if (detachAllScopeSubjectRadio) detachAllScopeSubjectRadio.checked = false;

                if (detachAllSubjectIdEl) {
                    detachAllSubjectIdEl.disabled = true;
                    detachAllSubjectIdEl.innerHTML = '<option value="">اختر المادة</option>';
                }

                showDetachAllSections();

                const modal = bootstrap.Modal.getOrCreateInstance(detachAllByScopeModalEl);
                modal.show();
            });

            detachAllConfirmBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const scopeVal = detachAllScopeSelector() ? detachAllScopeSelector().value : 'class';

                const payload = {};

                if (scopeVal === 'class') {
                    const classId = detachAllClassIdEl ? detachAllClassIdEl.value : '';
                    if (!classId) {
                        alert('يرجى اختيار الصف');
                        return;
                    }
                    payload.class_id = classId;
                    detachAllConfirmBtn.disabled = true;
                    fetch('{{ route("users.detach-all-from-class") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            const modal = bootstrap.Modal.getOrCreateInstance(detachAllByScopeModalEl);
                            modal.hide();
                            const deleted = data.deleted || {};
                            const classEnrollmentsDeleted = deleted.class_enrollments ?? 0;
                            const enrollmentsDeleted = deleted.enrollments ?? 0;
                            alert(`تم فصل جميع الطلاب بنجاح.\nتم حذف ClassEnrollment: ${classEnrollmentsDeleted}\nتم حذف Enrollment: ${enrollmentsDeleted}`);
                            fetchUsers(1);
                        } else {
                            alert((data && data.message) ? data.message : 'حدث خطأ');
                        }
                    })
                    .catch(err => {
                        console.error('Detach all (class) error:', err);
                        alert('حدث خطأ أثناء الفصل');
                    })
                    .finally(() => {
                        detachAllConfirmBtn.disabled = false;
                    });
                } else {
                    const classId = detachAllSubjectClassIdEl ? detachAllSubjectClassIdEl.value : '';
                    const subjectId = detachAllSubjectIdEl ? detachAllSubjectIdEl.value : '';

                    if (!classId || !subjectId) {
                        alert('يرجى اختيار الصف والمادة');
                        return;
                    }

                    payload.class_id = classId;
                    payload.subject_id = subjectId;

                    detachAllConfirmBtn.disabled = true;
                    fetch('{{ route("users.detach-all-from-subject") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            const modal = bootstrap.Modal.getOrCreateInstance(detachAllByScopeModalEl);
                            modal.hide();
                            const deleted = data.deleted || {};
                            const enrollmentsDeleted = deleted.enrollments ?? 0;
                            alert(`تم فصل جميع الطلاب عن المادة بنجاح.\nتم حذف Enrollment: ${enrollmentsDeleted}`);
                            fetchUsers(1);
                        } else {
                            alert((data && data.message) ? data.message : 'حدث خطأ');
                        }
                    })
                    .catch(err => {
                        console.error('Detach all (subject) error:', err);
                        alert('حدث خطأ أثناء الفصل');
                    })
                    .finally(() => {
                        detachAllConfirmBtn.disabled = false;
                    });
                }
            });
        }

        // Individual archive
        document.querySelectorAll('.archive-user-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                
                if (!userId || !userName) {
                    console.error('Missing data attributes');
                    return;
                }
                
                const archiveUserNameEl = document.getElementById('archiveUserName');
                const archiveFormEl = document.getElementById('archiveForm');
                const archiveReasonEl = document.getElementById('archiveReason');
                const archiveReasonInputEl = document.getElementById('archiveReasonInput');
                const archiveModalEl = document.getElementById('archiveModal');
                
                if (!archiveUserNameEl || !archiveFormEl || !archiveModalEl) {
                    console.error('Modal elements not found');
                    return;
                }
                
                archiveUserNameEl.textContent = userName;
                archiveFormEl.action = '{{ route("admin.users.archive", ":id") }}'.replace(':id', userId);
                if (archiveReasonEl) archiveReasonEl.value = '';
                if (archiveReasonInputEl) archiveReasonInputEl.value = '';
                
                const archiveModal = new bootstrap.Modal(archiveModalEl);
                archiveModal.show();
            });
        });

        // Archive form submission
        const archiveForm = document.getElementById('archiveForm');
        if (archiveForm) {
            archiveForm.addEventListener('submit', function(e) {
                const reason = document.getElementById('archiveReason').value;
                document.getElementById('archiveReasonInput').value = reason;
            });
        }

        // إظهار جميع الرسائل
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.display = 'block';
            alert.style.visibility = 'visible';
            alert.style.opacity = '1';
        });
        
        // إخفاء الرسائل تلقائياً بعد 5 ثواني
        setTimeout(function() {
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // إرسال OTP يدوياً
        document.querySelectorAll('.send-otp-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const button = this;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                const userPhone = button.getAttribute('data-user-phone');
                
                // تعطيل الزر وإظهار loading
                const originalHTML = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                
                // إرسال الطلب
                fetch(`/users/${userId}/send-verification-otp`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // إظهار رسالة نجاح
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>نجح!</strong> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        `;
                        document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
                        
                        // إزالة الرسالة بعد 5 ثواني
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    } else {
                        // إظهار رسالة خطأ
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>خطأ!</strong> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        `;
                        document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
                        
                        // إزالة الرسالة بعد 5 ثواني
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>خطأ!</strong> حدث خطأ أثناء إرسال كود التحقق
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                    `;
                    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
                    
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                })
                .finally(() => {
                    // إعادة تفعيل الزر
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                });
            });
        });

        // وظيفة نسخ النص للواتساب والإيميل
        document.querySelectorAll('.copy-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const textToCopy = this.getAttribute('data-copy-text');
                
                // نسخ النص إلى الحافظة
                navigator.clipboard.writeText(textToCopy).then(function() {
                    // تغيير الأيقونة مؤقتاً لإظهار النجاح
                    const icon = btn.querySelector('i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-check text-success';
                    
                    // إظهار رسالة نجاح
                    const originalTitle = btn.getAttribute('title');
                    btn.setAttribute('title', 'تم النسخ!');
                    
                    // إعادة الأيقونة بعد ثانيتين
                    setTimeout(function() {
                        icon.className = originalClass;
                        btn.setAttribute('title', originalTitle);
                    }, 2000);
                }).catch(function(err) {
                    console.error('فشل النسخ:', err);
                    alert('فشل نسخ النص');
                });
            });
        });

        // Delegation (works for AJAX-updated rows too)
        if (usersTableBody) {
            // Bulk selection checkbox (new rows)
            usersTableBody.addEventListener('change', function(e) {
                const target = e.target;
                if (!target || !target.matches('.user-checkbox')) return;
                toggleBulkArchiveBtn();
                updateSelectAllUsers();
            });

            // Individual actions for AJAX-updated rows
            usersTableBody.addEventListener('click', function(e) {
                const detachBtn = e.target.closest('.detach-class-btn');
                if (detachBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    const userId = detachBtn.getAttribute('data-user-id');
                    const classId = detachBtn.getAttribute('data-class-id');

                    if (!userId || !classId) return;

                    if (!confirm('متأكد من فصل الطالب عن هذا الصف؟')) return;

                    fetch('{{ route("users.detach-from-class") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ user_id: userId, class_id: classId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            const deleted = data.deleted || {};
                            const classEnrollmentsDeleted = deleted.class_enrollments ?? 0;
                            const enrollmentsDeleted = deleted.enrollments ?? 0;
                            alert(`تم فصل الطالب بنجاح.\nتم حذف ClassEnrollment: ${classEnrollmentsDeleted}\nتم حذف Enrollment: ${enrollmentsDeleted}`);
                            fetchUsers(1);
                        } else {
                            alert((data && data.message) ? data.message : 'حدث خطأ أثناء الفصل');
                        }
                    })
                    .catch(err => {
                        console.error('Detach error:', err);
                        alert('حدث خطأ أثناء الفصل');
                    });

                    return;
                }

                const archiveBtn = e.target.closest('.archive-user-btn');
                if (archiveBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    const userId = archiveBtn.getAttribute('data-user-id');
                    const userName = archiveBtn.getAttribute('data-user-name');

                    if (!userId || !userName) return;

                    const archiveUserNameEl = document.getElementById('archiveUserName');
                    const archiveFormEl = document.getElementById('archiveForm');
                    const archiveModalEl = document.getElementById('archiveModal');
                    if (!archiveUserNameEl || !archiveFormEl || !archiveModalEl) return;

                    archiveUserNameEl.textContent = userName;
                    archiveFormEl.action = '{{ route("admin.users.archive", ":id") }}'.replace(':id', userId);
                    const archiveReasonEl = document.getElementById('archiveReason');
                    if (archiveReasonEl) archiveReasonEl.value = '';

                    const archiveModal = new bootstrap.Modal(archiveModalEl);
                    archiveModal.show();
                    return;
                }

                const otpBtn = e.target.closest('.send-otp-btn');
                if (otpBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    const button = otpBtn;
                    const userId = button.getAttribute('data-user-id');
                    const userName = button.getAttribute('data-user-name');
                    const userPhone = button.getAttribute('data-user-phone');

                    // تعطيل الزر وإظهار loading
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                    fetch(`/users/${userId}/send-verification-otp`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const container = document.querySelector('.container-fluid');
                        const alertDiv = document.createElement('div');
                        if (data.success) {
                            alertDiv.className = 'alert alert-success alert-dismissible fade show';
                            alertDiv.innerHTML = `
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>نجح!</strong> ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                            `;
                        } else {
                            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                            alertDiv.innerHTML = `
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>خطأ!</strong> ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                            `;
                        }
                        if (container) {
                            container.insertBefore(alertDiv, container.firstChild);
                        }

                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const container = document.querySelector('.container-fluid');
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>خطأ!</strong> حدث خطأ أثناء إرسال كود التحقق
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        `;
                        if (container) {
                            container.insertBefore(alertDiv, container.firstChild);
                        }
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    });
                    return;
                }

                const copyBtn = e.target.closest('.copy-btn');
                if (copyBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    const textToCopy = copyBtn.getAttribute('data-copy-text');
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        const icon = copyBtn.querySelector('i');
                        const originalClass = icon ? icon.className : '';
                        if (icon) icon.className = 'fas fa-check text-success';

                        const originalTitle = copyBtn.getAttribute('title');
                        copyBtn.setAttribute('title', 'تم النسخ!');

                        setTimeout(function() {
                            if (icon) icon.className = originalClass;
                            copyBtn.setAttribute('title', originalTitle);
                        }, 2000);
                    }).catch(function(err) {
                        console.error('فشل النسخ:', err);
                        alert('فشل نسخ النص');
                    });
                }
            });
        }

        // AJAX class filter + pagination (no full page reload)
        const classFilter = document.getElementById('classFilter');
        const usersPaginationContainer = document.getElementById('usersPaginationContainer');
        const impersonateModalsWrapper = document.getElementById('impersonateModalsWrapper');
        const queryInput = document.querySelector('input[name="query"]');
        const isActiveSelect = document.querySelector('select[name="is_active"]');
        const fetchUrl = '{{ route("users.index") }}';
        const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');

        function getPerPageSelect() {
            return document.getElementById('perPageSelect');
        }
        function getPerPageCustomWrap() {
            return document.getElementById('perPageCustomWrap');
        }
        function getCurrentPerPage() {
            const sel = getPerPageSelect();
            if (!sel) {
                return 25;
            }
            if (sel.value === 'custom') {
                const input = document.getElementById('perPageCustom');
                const n = input ? parseInt(input.value, 10) : NaN;
                if (!Number.isFinite(n)) {
                    return 25;
                }
                return Math.min(100, Math.max(1, n));
            }
            const n = parseInt(sel.value, 10);
            if (!Number.isFinite(n)) {
                return 25;
            }
            return Math.min(100, Math.max(1, n));
        }
        function syncCustomPerPageUi() {
            const sel = getPerPageSelect();
            const wrap = getPerPageCustomWrap();
            if (!sel || !wrap) {
                return;
            }
            if (sel.value === 'custom') {
                wrap.classList.remove('d-none');
                wrap.classList.add('d-flex');
            } else {
                wrap.classList.add('d-none');
                wrap.classList.remove('d-flex');
            }
        }

        function buildFetchParams(page) {
            const params = new URLSearchParams();
            const query = queryInput ? queryInput.value.trim() : '';
            const isActive = isActiveSelect ? isActiveSelect.value : '';
            const classId = classFilter ? classFilter.value : '';

            if (query) params.set('query', query);
            // أرسل is_active دائماً (حتى لو فارغ) لتمييز "كل الحالات" عن القيمة الافتراضية
            params.set('is_active', isActive);
            if (classId) params.set('class_id', classId);
            params.set('page', page || 1);
            params.set('per_page', String(getCurrentPerPage()));

            return params.toString();
        }

        function fetchUsers(page) {
            const url = `${fetchUrl}?${buildFetchParams(page)}`;
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) return;

                if (usersTableBody && data.html !== undefined) {
                    usersTableBody.innerHTML = data.html;
                }
                if (usersPaginationContainer && data.pagination !== undefined) {
                    usersPaginationContainer.innerHTML = data.pagination;
                }
                if (impersonateModalsWrapper && data.impersonate_modals !== undefined) {
                    impersonateModalsWrapper.innerHTML = data.impersonate_modals;
                }
                syncCustomPerPageUi();

                // Reset bulk selection UI after table change
                if (selectAllUsers) selectAllUsers.checked = false;
                toggleBulkArchiveBtn();
                updateSelectAllUsers();
            })
            .catch(err => console.error('AJAX fetchUsers error:', err));
        }

        if (classFilter) {
            classFilter.addEventListener('change', function() {
                fetchUsers(1);
            });
        }

        if (isActiveSelect) {
            isActiveSelect.addEventListener('change', function() {
                fetchUsers(1);
            });
        }

        if (usersPaginationContainer) {
            usersPaginationContainer.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || !href.includes('page=')) return;
                e.preventDefault();

                const url = new URL(href, window.location.origin);
                const page = url.searchParams.get('page') || 1;
                fetchUsers(page);
            });
        }

        if (perPageToolbarContainer) {
            perPageToolbarContainer.addEventListener('change', function(e) {
                if (!e.target || e.target.id !== 'perPageSelect') {
                    return;
                }
                syncCustomPerPageUi();
                if (e.target.value !== 'custom') {
                    fetchUsers(1);
                }
            });
            perPageToolbarContainer.addEventListener('click', function(e) {
                const btn = e.target && e.target.closest ? e.target.closest('#applyCustomPerPage') : null;
                if (!btn) {
                    return;
                }
                e.preventDefault();
                const sel = getPerPageSelect();
                const input = document.getElementById('perPageCustom');
                if (sel && sel.value === 'custom' && input) {
                    const raw = parseInt(input.value, 10);
                    if (!Number.isFinite(raw) || raw < 1 || raw > 100) {
                        alert('أدخل عدداً بين 1 و 100');
                        return;
                    }
                }
                fetchUsers(1);
            });
        }

        syncCustomPerPageUi();

        // إظهار/إخفاء عمود الصفوف
        const usersTableEl = document.getElementById('usersTable');
        const toggleUsersClassesColBtn = document.getElementById('toggleUsersClassesColBtn');
        const toggleUsersClassesColLabel = document.getElementById('toggleUsersClassesColLabel');
        const usersClassesColStorageKey = 'usersListShowClassesColumn';

        function syncUsersClassesColUi(show) {
            if (!usersTableEl) {
                return;
            }
            usersTableEl.classList.toggle('hide-classes-col', !show);
            if (toggleUsersClassesColLabel) {
                toggleUsersClassesColLabel.textContent = show ? 'إخفاء الصفوف' : 'إظهار الصفوف';
            }
            if (toggleUsersClassesColBtn) {
                toggleUsersClassesColBtn.classList.toggle('btn-outline-secondary', !show);
                toggleUsersClassesColBtn.classList.toggle('btn-secondary', show);
            }
        }

        const savedShowClassesCol = localStorage.getItem(usersClassesColStorageKey) === '1';
        syncUsersClassesColUi(savedShowClassesCol);

        if (toggleUsersClassesColBtn) {
            toggleUsersClassesColBtn.addEventListener('click', function () {
                const willShow = usersTableEl && usersTableEl.classList.contains('hide-classes-col');
                syncUsersClassesColUi(willShow);
                localStorage.setItem(usersClassesColStorageKey, willShow ? '1' : '0');
            });
        }
    });
</script>

<div id="impersonateModalsWrapper">
    @include('admin.pages.users.partials.impersonate-modals', ['users' => $users])
</div>

@push('scripts')
<script>
function copyLink(userId) {
    const linkInput = document.getElementById('impersonateLink' + userId);
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // إظهار رسالة نجاح
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check me-1"></i> تم النسخ';
    button.classList.remove('btn-secondary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-secondary');
    }, 2000);
}
</script>
@endpush
@stop
