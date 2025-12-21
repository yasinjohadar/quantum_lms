@extends('admin.layouts.master')

@section('page-title')
    إضافة لوحة متصدرين جديدة
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة لوحة متصدرين جديدة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.leaderboards.index') }}">لوحة المتصدرين</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إضافة جديدة</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">معلومات اللوحة</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.leaderboards.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required id="leaderboard_type">
                                        <option value="global" {{ old('type') == 'global' ? 'selected' : '' }}>عامة</option>
                                        <option value="course" {{ old('type') == 'course' ? 'selected' : '' }}>كورس</option>
                                        <option value="weekly" {{ old('type') == 'weekly' ? 'selected' : '' }}>أسبوعية</option>
                                        <option value="monthly" {{ old('type') == 'monthly' ? 'selected' : '' }}>شهرية</option>
                                    </select>
                                    @error('type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3" id="subject_field" style="display: none;">
                                    <label class="form-label">المادة</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">اختر المادة</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3" id="period_start_field" style="display: none;">
                                    <label class="form-label">تاريخ البداية</label>
                                    <input type="datetime-local" name="period_start" class="form-control" value="{{ old('period_start') }}">
                                    @error('period_start')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3" id="period_end_field" style="display: none;">
                                    <label class="form-label">تاريخ النهاية</label>
                                    <input type="datetime-local" name="period_end" class="form-control" value="{{ old('period_end') }}">
                                    @error('period_end')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.leaderboards.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
    document.getElementById('leaderboard_type').addEventListener('change', function() {
        const type = this.value;
        const subjectField = document.getElementById('subject_field');
        const periodStartField = document.getElementById('period_start_field');
        const periodEndField = document.getElementById('period_end_field');
        
        if (type === 'course') {
            subjectField.style.display = 'block';
            periodStartField.style.display = 'none';
            periodEndField.style.display = 'none';
        } else if (type === 'weekly' || type === 'monthly') {
            subjectField.style.display = 'none';
            periodStartField.style.display = 'block';
            periodEndField.style.display = 'block';
        } else {
            subjectField.style.display = 'none';
            periodStartField.style.display = 'none';
            periodEndField.style.display = 'none';
        }
    });
    
    // Trigger on page load
    document.getElementById('leaderboard_type').dispatchEvent(new Event('change'));
</script>
@endpush
@stop

