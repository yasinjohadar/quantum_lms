@extends('admin.layouts.master')

@section('page-title')
    تخصيص المعلمين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تخصيص المعلمين للصفوف والمواد</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'teacher']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> إضافة معلم جديد
                        </a>
                    @endcan
                    <a href="{{ route('users.index', ['role' => 'teacher']) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-users me-1"></i> عرض جميع المعلمين
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- بطاقات الإحصائيات -->
            @if(isset($totalTeachers))
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">إجمالي المعلمين</h6>
                            <h3 class="mb-0">{{ $totalTeachers }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">معلمون مخصصون</h6>
                            <h3 class="mb-0">{{ $assignedTeachers }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">معلمون غير مخصصين</h6>
                            <h3 class="mb-0">{{ $unassignedTeachers }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column justify-content-between gap-3">
                            <h5 class="mb-0 fw-bold">قائمة المعلمين</h5>

                            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                                <div class="w-100 mb-2 small text-muted">
                                    @if(isset($currentWeek) && $currentWeek)
                                        <strong>الأسبوع المعتمد في الإحصائيات:</strong> {{ $currentWeek->title ?? 'الأسبوع ' . $currentWeek->week_number }} ({{ $currentWeek->start_date->format('Y-m-d') }} - {{ $currentWeek->end_date->format('Y-m-d') }})
                                    @else
                                        <strong>الأسبوع:</strong> أسبوع النظام (حسب التاريخ الحالي)
                                    @endif
                                </div>
                            @endif
                            <form method="GET" action="{{ route('admin.teachers.assignments.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-end">
                                @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                                    <div class="d-flex align-items-center gap-1">
                                        <label class="form-label mb-0 small text-muted">الأسبوع</label>
                                        <select name="week_id" class="form-select form-select-sm" style="min-width: 180px;">
                                            <option value="">الأسبوع الحالي</option>
                                            @foreach($activeWeeks as $w)
                                                <option value="{{ $w->id }}" {{ request('week_id') == $w->id ? 'selected' : '' }}>{{ $w->title ?? 'الأسبوع ' . $w->week_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">بحث</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="الاسم أو البريد"
                                           value="{{ request('search') }}" style="min-width: 180px;">
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">التخصيص</label>
                                    <select name="assignment" class="form-select form-select-sm" style="min-width: 120px;">
                                        <option value="all" {{ request('assignment', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="assigned" {{ request('assignment') === 'assigned' ? 'selected' : '' }}>مخصصون فقط</option>
                                        <option value="unassigned" {{ request('assignment') === 'unassigned' ? 'selected' : '' }}>غير مخصصين</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">نسبة الصفحات</label>
                                    <select name="pages_progress" class="form-select form-select-sm" style="min-width: 130px;">
                                        <option value="all" {{ request('pages_progress', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="below_50" {{ request('pages_progress') === 'below_50' ? 'selected' : '' }}>أقل من 50%</option>
                                        <option value="below_100" {{ request('pages_progress') === 'below_100' ? 'selected' : '' }}>أقل من 100%</option>
                                        <option value="completed" {{ request('pages_progress') === 'completed' ? 'selected' : '' }}>منجز 100%</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">النسبة الأسبوعية</label>
                                    <select name="weekly_progress" class="form-select form-select-sm" style="min-width: 130px;">
                                        <option value="all" {{ request('weekly_progress', 'all') === 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="below_50" {{ request('weekly_progress') === 'below_50' ? 'selected' : '' }}>أقل من 50%</option>
                                        <option value="below_100" {{ request('weekly_progress') === 'below_100' ? 'selected' : '' }}>أقل من 100%</option>
                                        <option value="completed" {{ request('weekly_progress') === 'completed' ? 'selected' : '' }}>منجز 100%</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="form-label mb-0 small text-muted">الترتيب</label>
                                    <select name="sort" class="form-select form-select-sm" style="min-width: 160px;">
                                        <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>الاسم (أ→ي)</option>
                                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>الاسم (ي→أ)</option>
                                        <option value="pages_asc" {{ request('sort') === 'pages_asc' ? 'selected' : '' }}>نسبة الصفحات (تصاعدي)</option>
                                        <option value="pages_desc" {{ request('sort') === 'pages_desc' ? 'selected' : '' }}>نسبة الصفحات (تنازلي)</option>
                                        <option value="weekly_asc" {{ request('sort') === 'weekly_asc' ? 'selected' : '' }}>النسبة الأسبوعية (تصاعدي)</option>
                                        <option value="weekly_desc" {{ request('sort') === 'weekly_desc' ? 'selected' : '' }}>النسبة الأسبوعية (تنازلي)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                                @if(request()->hasAny(['search', 'assignment', 'pages_progress', 'weekly_progress', 'sort', 'week_id']) && (request('search') || request('assignment', 'all') !== 'all' || request('pages_progress', 'all') !== 'all' || request('weekly_progress', 'all') !== 'all' || request('sort', 'name_asc') !== 'name_asc' || request('week_id')))
                                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i> إلغاء الفلاتر
                                    </a>
                                @endif
                            </form>
                        </div>

                        <div class="card-body">
                            @if($teachers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الصفوف المخصصة</th>
                                                <th>المواد المخصصة</th>
                                                <th>حالة الحساب</th>
                                                <th>آخر دخول</th>
                                                <th>متصل الآن</th>
                                                <th>مؤشرات التقدم</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($teachers as $teacher)
                                                <tr>
                                                    <td>{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($teacher->photo)
                                                                <img src="{{ asset('storage/' . $teacher->photo) }}" 
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
                                                        @php
                                                            $assignedClasses = $teacher->assignedClasses;
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
                                                            $assignedSubjects = $teacher->assignedSubjects;
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
                                                        @can('user-toggle-status')
                                                        <button type="button"
                                                                class="btn btn-sm d-inline-flex align-items-center {{ $teacher->is_active ? 'btn-success' : 'btn-outline-danger' }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#toggleStatus{{ $teacher->id }}">
                                                            @if($teacher->is_active)
                                                                <i class="fa-solid fa-check-circle me-1"></i>
                                                                <span>الحساب مفعل</span>
                                                            @else
                                                                <i class="fa-solid fa-ban me-1"></i>
                                                                <span>الحساب معطل</span>
                                                            @endif
                                                        </button>
                                                        @else
                                                            @if($teacher->is_active)
                                                                <span class="badge bg-success">مفعل</span>
                                                            @else
                                                                <span class="badge bg-secondary">معطل</span>
                                                            @endif
                                                        @endcan
                                                    </td>
                                                    <td>
                                                        @php
                                                            $lastLogin = $lastLogins[$teacher->id] ?? $teacher->last_login_at;
                                                        @endphp
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
                                                        @php
                                                            $prog = $teachersProgress[$teacher->id] ?? null;
                                                        @endphp
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
                                                                <div>
                                                                    <span class="text-muted">دروس مسجلة:</span> {{ $prog['total_approved_lessons'] }}
                                                                </div>
                                                                <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="small">تفاصيل</a>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-user-tie me-1"></i> تخصيص
                                                        </a>
                                                        <a href="{{ route('admin.teachers.progress.history', $teacher->id) }}"
                                                           class="btn btn-outline-secondary btn-sm mt-1">
                                                            <i class="bi bi-clock-history me-1"></i> إحصائيات سابقة
                                                        </a>
                                                    </td>
                                                </tr>
                                                @can('user-toggle-status')
                                                @include('admin.pages.users.toggle_status', ['user' => $teacher])
                                                @endcan
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center mt-3">
                                    {{ $teachers->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-3">لا يوجد معلمين</p>
                                    @can('user-create')
                                        <a href="{{ route('users.create', ['role' => 'teacher']) }}" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-1"></i> إضافة معلم جديد
                                        </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
