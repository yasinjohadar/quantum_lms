@extends('admin.layouts.master')

@section('page-title')
    الأسابيع الدراسية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الأسابيع الدراسية</h5>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $selectedYear = request('academic_year_id') ? $academicYears->firstWhere('id', request('academic_year_id')) : null;
                    @endphp
                    @if($selectedYear)
                        <form action="{{ route('admin.academic-years.weeks.generate', $selectedYear) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="number" name="weeks_count" value="36" min="1" max="52" class="form-control form-control-sm d-inline-block" style="width: 70px;" title="عدد الأسابيع">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-magic me-1"></i> توليد الأسابيع تلقائياً
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.academic-weeks.create', request()->only('academic_year_id')) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة أسبوع
                    </a>
                    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-outline-secondary btn-sm">السنوات الدراسية</a>
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

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('admin.academic-weeks.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                        <label class="form-label mb-0">السنة الدراسية:</label>
                        <select name="academic_year_id" class="form-select form-select-sm" style="width: auto;">
                            <option value="">-- الكل --</option>
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-secondary">عرض</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>السنة</th>
                                    <th>رقم الأسبوع</th>
                                    <th>العنوان</th>
                                    <th>من - إلى</th>
                                    <th>هدف الدروس</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($weeks as $week)
                                    <tr>
                                        <td>{{ $loop->iteration + ($weeks->currentPage() - 1) * $weeks->perPage() }}</td>
                                        <td>{{ $week->academicYear->name ?? '—' }}</td>
                                        <td>{{ $week->week_number }}</td>
                                        <td>{{ $week->title ?? '—' }}</td>
                                        <td>{{ $week->start_date->format('Y-m-d') }} → {{ $week->end_date->format('Y-m-d') }}</td>
                                        <td>{{ $week->required_lessons_target }}</td>
                                        <td>
                                            @if($week->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.academic-weeks.edit', $week) }}" class="btn btn-sm btn-primary">تعديل</a>
                                            <form action="{{ route('admin.academic-weeks.destroy', $week) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            لا توجد أسابيع. اختر سنة ثم استخدم «توليد الأسابيع تلقائياً» أو <a href="{{ route('admin.academic-weeks.create', request()->only('academic_year_id')) }}">إضافة أسبوع</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $weeks->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
