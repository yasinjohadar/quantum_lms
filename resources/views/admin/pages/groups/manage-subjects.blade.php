@extends('admin.layouts.master')

@section('page-title')
    إدارة المواد - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة المواد - {{ $group->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.index') }}">المجموعات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.groups.show', $group->id) }}">{{ $group->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إدارة المواد</li>
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
                <!-- قائمة المواد الحالية -->
                <div class="col-xl-6">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-book me-2"></i> المواد المرتبطة ({{ $group->subjects->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($group->subjects->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسم المادة</th>
                                            <th>الصف</th>
                                            <th>تاريخ الإضافة</th>
                                            <th>العمليات</th>
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
                                                <td>
                                                    <form action="{{ route('admin.groups.remove-subject', [$group->id, $subject->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من إزالة هذه المادة؟')">
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
                                <p class="text-muted text-center">لا توجد مواد مرتبطة بهذه المجموعة</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- إضافة مواد جديدة -->
                <div class="col-xl-6">
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-plus-circle me-2"></i> إضافة مواد جديدة
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.groups.add-subjects', $group->id) }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label">فلترة حسب الصف (اختياري)</label>
                                    <select id="classFilter" class="form-select">
                                        <option value="">كل الصفوف</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->stage->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">اختر المواد</label>
                                    <select name="subject_ids[]" id="subjectsSelect" class="form-select" multiple size="10" required>
                                        @php
                                            $currentSubjectIds = $group->subjects->pluck('id')->toArray();
                                        @endphp
                                        @foreach($subjects as $subject)
                                            @if(!in_array($subject->id, $currentSubjectIds))
                                                <option value="{{ $subject->id }}" data-class-id="{{ $subject->class_id }}">
                                                    {{ $subject->name }} - {{ $subject->schoolClass->name ?? '' }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <small class="text-muted">اضغط Ctrl (أو Cmd على Mac) لتحديد عدة مواد</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات حول إضافة هذه المواد..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> إضافة المواد
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
<script>
    document.getElementById('classFilter').addEventListener('change', function() {
        const classId = this.value;
        const subjectsSelect = document.getElementById('subjectsSelect');
        const options = subjectsSelect.getElementsByTagName('option');

        for (let option of options) {
            if (!classId || option.getAttribute('data-class-id') === classId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
</script>
@stop

