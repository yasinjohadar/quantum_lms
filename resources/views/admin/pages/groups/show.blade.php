@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المجموعة - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل المجموعة - {{ $group->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.index') }}">المجموعات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تفاصيل المجموعة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                    <a href="{{ route('admin.groups.edit', $group->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h5 class="mb-0 fw-bold">معلومات المجموعة</h5>
                                    @if($group->color)
                                        <div class="mt-2 d-flex align-items-center gap-2">
                                            <div style="width: 30px; height: 30px; background-color: {{ $group->color }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                            <small class="text-muted">{{ $group->color }}</small>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    @if ($group->is_active)
                                        <span class="badge bg-success">نشطة</span>
                                    @else
                                        <span class="badge bg-danger">غير نشطة</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>الاسم:</strong> {{ $group->name }}</p>
                                    <p><strong>الوصف:</strong> {{ $group->description ?? '-' }}</p>
                                    <p><strong>تاريخ الإنشاء:</strong> {{ $group->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>عدد الطلاب:</strong> <span class="badge bg-info">{{ $group->users_count }}</span></p>
                                    <p><strong>عدد الصفوف:</strong> <span class="badge bg-primary">{{ $group->classes_count }}</span></p>
                                    <p><strong>عدد المواد:</strong> <span class="badge bg-success">{{ $group->subjects_count }}</span></p>
                                    @if($group->createdBy)
                                        <p><strong>أنشئ بواسطة:</strong> {{ $group->createdBy->name }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Tabs -->
                            <ul class="nav nav-tabs" id="groupTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
                                        <i class="fas fa-users me-1"></i> الطلاب ({{ $group->users_count }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button" role="tab">
                                        <i class="fas fa-school me-1"></i> الصفوف ({{ $group->classes_count }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="subjects-tab" data-bs-toggle="tab" data-bs-target="#subjects" type="button" role="tab">
                                        <i class="fas fa-book me-1"></i> المواد ({{ $group->subjects_count }})
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-3" id="groupTabsContent">
                                <!-- Students Tab -->
                                <div class="tab-pane fade show active" id="students" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6>قائمة الطلاب</h6>
                                        <a href="{{ route('admin.groups.manage-students', $group->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> إدارة الطلاب
                                        </a>
                                    </div>
                                    @if($group->users->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>الاسم</th>
                                                    <th>البريد</th>
                                                    <th>تاريخ الإضافة</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($group->users as $user)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $user->name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>
                                                            @if($user->pivot->added_at)
                                                                {{ \Carbon\Carbon::parse($user->pivot->added_at)->format('Y-m-d') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">لا يوجد طلاب في هذه المجموعة</p>
                                    @endif
                                </div>

                                <!-- Classes Tab -->
                                <div class="tab-pane fade" id="classes" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6>قائمة الصفوف</h6>
                                        <a href="{{ route('admin.groups.manage-classes', $group->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> إدارة الصفوف
                                        </a>
                                    </div>
                                    @if($group->classes->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>اسم الصف</th>
                                                    <th>المرحلة</th>
                                                    <th>تاريخ الإضافة</th>
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
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">لا توجد صفوف مرتبطة بهذه المجموعة</p>
                                    @endif
                                </div>

                                <!-- Subjects Tab -->
                                <div class="tab-pane fade" id="subjects" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6>قائمة المواد</h6>
                                        <a href="{{ route('admin.groups.manage-subjects', $group->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> إدارة المواد
                                        </a>
                                    </div>
                                    @if($group->subjects->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>اسم المادة</th>
                                                    <th>الصف</th>
                                                    <th>تاريخ الإضافة</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($group->subjects as $subject)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $subject->name }}</td>
                                                        <td>{{ $subject->schoolClass->name ?? '-' }}</td>
                                                        <td>
                                                            @if($subject->pivot->added_at)
                                                                {{ \Carbon\Carbon::parse($subject->pivot->added_at)->format('Y-m-d') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">لا توجد مواد مرتبطة بهذه المجموعة</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

