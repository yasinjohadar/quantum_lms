@extends('admin.layouts.master')

@section('page-title')
    الواجبات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الواجبات</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة واجب جديد
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
                            <h5 class="mb-0 fw-bold">قائمة الواجبات</h5>

                            <form method="GET" action="{{ route('admin.assignments.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="بحث بالعنوان أو الوصف"
                                       value="{{ request('search') }}" style="min-width: 220px;">

                                <select name="assignable_type" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الأنواع</option>
                                    <option value="App\Models\Subject" {{ request('assignable_type') == 'App\Models\Subject' ? 'selected' : '' }}>مادة</option>
                                    <option value="App\Models\Unit" {{ request('assignable_type') == 'App\Models\Unit' ? 'selected' : '' }}>وحدة</option>
                                    <option value="App\Models\Lesson" {{ request('assignable_type') == 'App\Models\Lesson' ? 'selected' : '' }}>درس</option>
                                </select>

                                <select name="is_published" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>غير منشور</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    بحث
                                </button>
                                <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-danger btn-sm">
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
                                        <th style="min-width: 200px;">العنوان</th>
                                        <th style="min-width: 150px;">المرتبط بـ</th>
                                        <th style="min-width: 120px;">الدرجة الكاملة</th>
                                        <th style="min-width: 150px;">موعد التسليم</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 120px;">المعلم</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($assignments as $assignment)
                                        <tr>
                                            <td>{{ $assignment->id }}</td>
                                            <td>
                                                <a href="{{ route('admin.assignments.show', $assignment) }}" class="text-primary">
                                                    {{ $assignment->title }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($assignment->assignable_type === 'App\Models\Subject')
                                                    <span class="badge bg-info">{{ $assignment->assignable->name ?? 'N/A' }}</span>
                                                @elseif($assignment->assignable_type === 'App\Models\Unit')
                                                    <span class="badge bg-warning">{{ $assignment->assignable->title ?? 'N/A' }}</span>
                                                @elseif($assignment->assignable_type === 'App\Models\Lesson')
                                                    <span class="badge bg-success">{{ $assignment->assignable->title ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->max_score }}</td>
                                            <td>
                                                @if($assignment->due_date)
                                                    <span class="{{ $assignment->isOverdue() ? 'text-danger' : '' }}">
                                                        {{ $assignment->due_date->format('Y-m-d H:i') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">لا يوجد</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($assignment->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-secondary">غير منشور</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->creator->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.assignments.edit', $assignment) }}" 
                                                       class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($assignment->is_published)
                                                        <form action="{{ route('admin.assignments.unpublish', $assignment) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-secondary" title="إلغاء النشر">
                                                                <i class="fas fa-eye-slash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.assignments.publish', $assignment) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" title="نشر">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form action="{{ route('admin.assignments.duplicate', $assignment) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary" title="نسخ">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST" class="d-inline" 
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الواجب؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <p class="text-muted mb-0">لا توجد واجبات</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $assignments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

