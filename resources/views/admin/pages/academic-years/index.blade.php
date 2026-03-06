@extends('admin.layouts.master')

@section('page-title')
    السنوات الدراسية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">السنوات الدراسية</h5>
                </div>
                <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة سنة دراسية
                </a>
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

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>تاريخ البداية</th>
                                    <th>تاريخ النهاية</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($years as $year)
                                    <tr>
                                        <td>{{ $loop->iteration + ($years->currentPage() - 1) * $years->perPage() }}</td>
                                        <td>{{ $year->name }}</td>
                                        <td>{{ $year->start_date->format('Y-m-d') }}</td>
                                        <td>{{ $year->end_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($year->is_active)
                                                <span class="badge bg-success">نشطة</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشطة</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$year->is_active)
                                                <form action="{{ route('admin.academic-years.activate', $year) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">تفعيل</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.academic-weeks.index', ['academic_year_id' => $year->id]) }}" class="btn btn-sm btn-info">الأسابيع</a>
                                            <a href="{{ route('admin.academic-years.edit', $year) }}" class="btn btn-sm btn-primary">تعديل</a>
                                            <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">لا توجد سنوات دراسية. <a href="{{ route('admin.academic-years.create') }}">إضافة سنة</a></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $years->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
