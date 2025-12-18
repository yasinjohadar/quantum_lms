@extends('admin.layouts.master')

@section('page-title')
    المجموعات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">المجموعات</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.groups.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة مجموعة جديدة
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

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة المجموعات</h5>

                            <form method="GET" action="{{ route('admin.groups.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="query" class="form-control form-control-sm"
                                       placeholder="بحث باسم المجموعة أو الوصف"
                                       value="{{ request('query') }}" style="min-width: 220px;">

                                <select name="is_active" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    بحث
                                </button>
                                <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-danger btn-sm">
                                    مسح الفلاتر
                                </a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 200px;">اسم المجموعة</th>
                                        <th style="min-width: 150px;">الوصف</th>
                                        <th style="min-width: 100px;">اللون</th>
                                        <th style="min-width: 100px;">عدد الطلاب</th>
                                        <th style="min-width: 100px;">عدد الصفوف</th>
                                        <th style="min-width: 100px;">عدد المواد</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 160px;">تاريخ الإنشاء</th>
                                        <th style="min-width: 250px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($groups as $group)
                                        <tr>
                                            <td>{{ $loop->iteration + ($groups->currentPage() - 1) * $groups->perPage() }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $group->name }}</div>
                                            </td>
                                            <td>
                                                <div class="text-muted small">
                                                    {{ \Illuminate\Support\Str::limit($group->description ?? '-', 50) }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($group->color)
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <div style="width: 30px; height: 30px; background-color: {{ $group->color }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                                        <small class="text-muted">{{ $group->color }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $group->users_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $group->classes_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $group->subjects_count }}</span>
                                            </td>
                                            <td>
                                                @if ($group->is_active)
                                                    <span class="badge bg-success">نشطة</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشطة</span>
                                                @endif
                                            </td>
                                            <td>{{ $group->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                                    <a href="{{ route('admin.groups.show', $group->id) }}"
                                                       class="btn btn-sm btn-info text-white"
                                                       title="عرض تفاصيل المجموعة">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                    <a href="{{ route('admin.groups.edit', $group->id) }}"
                                                       class="btn btn-sm btn-warning text-white"
                                                       title="تعديل المجموعة">
                                                        <i class="fas fa-edit"></i> تعديل
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteGroup{{ $group->id }}"
                                                            title="حذف المجموعة">
                                                        <i class="fas fa-trash-alt"></i> حذف
                                                    </button>
                                                </div>

                                                <!-- Modal for Delete Confirmation -->
                                                <div class="modal fade" id="deleteGroup{{ $group->id }}" tabindex="-1" aria-labelledby="deleteGroupLabel{{ $group->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-4">
                                                                <i class="bi bi-trash-fill text-danger display-1 mb-3"></i>
                                                                <h4 class="mb-3">تأكيد حذف المجموعة</h4>
                                                                <p class="mb-3">هل أنت متأكد من حذف المجموعة <strong>{{ $group->name }}</strong>؟</p>
                                                                <div class="alert alert-warning mb-4">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    <small>هذه العملية لا يمكن التراجع عنها.</small>
                                                                </div>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                        <i class="bi bi-x-circle me-1"></i> إلغاء
                                                                    </button>
                                                                    <form action="{{ route('admin.groups.destroy', $group->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">
                                                                            <i class="bi bi-trash me-1"></i> حذف
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-danger fw-bold">
                                                لا توجد مجموعات مسجلة حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($groups instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <div class="mt-3">
                                    {{ $groups->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

