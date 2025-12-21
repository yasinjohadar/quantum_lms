@extends('admin.layouts.master')

@section('page-title')
    الطلاب المنضمين - {{ $class->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الطلاب المنضمين - {{ $class->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">الصفوف الدراسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الطلاب المنضمين</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                    <a href="{{ route('admin.enrollments.create', ['class_id' => $class->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة طلاب
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h5 class="mb-0 fw-bold">معلومات الصف</h5>
                                    <div class="mt-2">
                                        @if($class->stage)
                                            <span class="badge bg-primary-transparent text-primary">
                                                {{ $class->stage->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-md-end">
                                    <div class="h4 mb-0 text-primary">{{ $enrollments->total() }}</div>
                                    <small class="text-muted">إجمالي الانضمامات</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة الطلاب المنضمين</h5>

                            <form method="GET" action="{{ route('admin.classes.enrolled-students', $class->id) }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="بحث بالاسم، البريد، أو الهاتف"
                                       value="{{ request('search') }}" style="min-width: 220px;">

                                @if($subjects->count() > 0)
                                <select name="subject_id" class="form-select form-select-sm" style="min-width: 180px;">
                                    <option value="">كل المواد</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @endif

                                <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>معلق</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <a href="{{ route('admin.classes.enrolled-students', $class->id) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> مسح
                                </a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 180px;">الطالب</th>
                                        <th style="min-width: 150px;">البريد الإلكتروني</th>
                                        <th style="min-width: 120px;">الهاتف</th>
                                        <th style="min-width: 180px;">المادة</th>
                                        <th style="min-width: 100px;">حالة الانضمام</th>
                                        <th style="min-width: 150px;">تاريخ الانضمام</th>
                                        <th style="min-width: 150px;">أضيف بواسطة</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($enrollments as $enrollment)
                                        <tr>
                                            <td>{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($enrollment->user->avatar)
                                                        <img src="{{ asset('storage/' . $enrollment->user->avatar) }}" 
                                                             alt="{{ $enrollment->user->name }}" 
                                                             class="rounded-circle" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            {{ substr($enrollment->user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                                                        <small class="text-muted">ID: {{ $enrollment->user->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $enrollment->user->email }}</div>
                                                @if($enrollment->user->is_active)
                                                    <small class="badge bg-success-transparent text-success">نشط</small>
                                                @else
                                                    <small class="badge bg-danger-transparent text-danger">غير نشط</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $enrollment->user->phone ?? '-' }}
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $enrollment->subject->name }}</div>
                                                @if($enrollment->subject->schoolClass)
                                                    <small class="text-muted">{{ $enrollment->subject->schoolClass->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->status === 'active')
                                                    <span class="badge bg-success-transparent text-success">نشط</span>
                                                @elseif($enrollment->status === 'suspended')
                                                    <span class="badge bg-warning-transparent text-warning">معلق</span>
                                                @else
                                                    <span class="badge bg-secondary-transparent text-secondary">مكتمل</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $enrollment->enrolled_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $enrollment->enrolled_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($enrollment->enrolledBy)
                                                    {{ $enrollment->enrolledBy->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    @if($enrollment->notes)
                                                        <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $enrollment->notes }}">
                                                            <i class="bi bi-info-circle"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteEnrollmentModal{{ $enrollment->id }}">
                                                        <i class="bi bi-trash"></i> إلغاء
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا يوجد طلاب منضمين لهذا الصف</p>
                                                <a href="{{ route('admin.enrollments.create', ['class_id' => $class->id]) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-plus-circle me-1"></i> إضافة طلاب
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($enrollments->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $enrollments->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modals for Delete Confirmation -->
    @foreach($enrollments as $enrollment)
    <div class="modal fade" id="deleteEnrollmentModal{{ $enrollment->id }}" tabindex="-1" aria-labelledby="deleteEnrollmentModalLabel{{ $enrollment->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-trash-fill text-danger display-1 mb-3"></i>
                    <h4 class="mb-3">تأكيد إلغاء الانضمام</h4>
                    <p class="mb-3">هل أنت متأكد من إلغاء انضمام الطالب <strong>{{ $enrollment->user->name }}</strong> لمادة <strong>{{ $enrollment->subject->name }}</strong>؟</p>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>هذه العملية لا يمكن التراجع عنها.</small>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <form action="{{ route('admin.enrollments.destroy', $enrollment->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="{{ route('admin.classes.enrolled-students', $class->id) }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@stop

@section('js')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@stop


