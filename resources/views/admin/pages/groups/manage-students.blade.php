@extends('admin.layouts.master')

@section('page-title')
    إدارة الطلاب - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الطلاب - {{ $group->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.index') }}">المجموعات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.show', $group->id) }}">{{ $group->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إدارة الطلاب</li>
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
                <!-- قائمة الطلاب الحاليين -->
                <div class="col-xl-6">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i> الطلاب الحاليين ({{ $group->users->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($group->users->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>الاسم</th>
                                            <th>البريد</th>
                                            <th>تاريخ الإضافة</th>
                                            <th>العمليات</th>
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
                                                <td>
                                                    <form action="{{ route('admin.groups.remove-student', [$group->id, $user->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من إزالة هذا الطالب؟')">
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
                                <p class="text-muted text-center">لا يوجد طلاب في هذه المجموعة</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- إضافة طلاب جدد -->
                <div class="col-xl-6">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-plus me-2"></i> إضافة طلاب جدد
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.groups.add-students', $group->id) }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label">اختر الطلاب</label>
                                    <select name="user_ids[]" class="form-select" multiple size="10" required>
                                        @php
                                            $currentUserIds = $group->users->pluck('id')->toArray();
                                            $allStudents = \App\Models\User::students()->orderBy('name')->get();
                                        @endphp
                                        @foreach($allStudents as $student)
                                            @if(!in_array($student->id, $currentUserIds))
                                                <option value="{{ $student->id }}">{{ $student->name }} - {{ $student->email }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <small class="text-muted">اضغط Ctrl (أو Cmd على Mac) لتحديد عدة طلاب</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات حول إضافة هؤلاء الطلاب..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> إضافة الطلاب
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

