@extends('admin.layouts.master')

@section('page-title')
    إدارة الصفوف - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الصفوف - {{ $group->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.index') }}">المجموعات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.show', $group->id) }}">{{ $group->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إدارة الصفوف</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.groups.show', $group->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
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
                <!-- قائمة الصفوف الحالية -->
                <div class="col-xl-6">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-school me-2"></i> الصفوف المرتبطة ({{ $group->classes->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($group->classes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسم الصف</th>
                                            <th>المرحلة</th>
                                            <th>تاريخ الإضافة</th>
                                            <th>العمليات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($group->classes as $class)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $class->name }}</td>
                                                <td>{{ $class->stage->name ?? '-' }}</td>
                                                <td>
                                                    @if($class->pivot->added_at)
                                                        {{ \Carbon\Carbon::parse($class->pivot->added_at)->format('Y-m-d') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    <form action="{{ route('admin.groups.remove-class', [$group->id, $class->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من إزالة هذا الصف؟')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">لا توجد صفوف مرتبطة بهذه المجموعة</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- إضافة صفوف جديدة -->
                <div class="col-xl-6">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-plus-circle me-2"></i> إضافة صفوف جديدة
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.groups.add-classes', $group->id) }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label">اختر الصفوف</label>
                                    <select name="class_ids[]" class="form-select" multiple size="10" required>
                                        @php
                                            $currentClassIds = $group->classes->pluck('id')->toArray();
                                        @endphp
                                        @foreach($classes as $class)
                                            @if(!in_array($class->id, $currentClassIds))
                                                <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->stage->name ?? '' }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <small class="text-muted">اضغط Ctrl (أو Cmd على Mac) لتحديد عدة صفوف</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات حول إضافة هذه الصفوف..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> إضافة الصفوف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

