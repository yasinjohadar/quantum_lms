@extends('admin.layouts.master')

@section('page-title')
    إضافة مهمة أسبوعية جديدة
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة مهمة أسبوعية جديدة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.weekly-tasks.index') }}">المهام الأسبوعية</a></li>
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
                        <div class="card-title">معلومات المهمة</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.weekly-tasks.store') }}" method="POST">
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
                                    <select name="type" class="form-select" required>
                                        <option value="attendance" {{ old('type') == 'attendance' ? 'selected' : '' }}>حضور</option>
                                        <option value="lesson_completion" {{ old('type') == 'lesson_completion' ? 'selected' : '' }}>إكمال درس</option>
                                        <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>اختبار</option>
                                        <option value="question" {{ old('type') == 'question' ? 'selected' : '' }}>سؤال</option>
                                    </select>
                                    @error('type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">مكافأة النقاط <span class="text-danger">*</span></label>
                                    <input type="number" name="points_reward" class="form-control" value="{{ old('points_reward', 0) }}" min="0" required>
                                    @error('points_reward')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">يوم البداية <span class="text-danger">*</span></label>
                                    <select name="start_day" class="form-select" required>
                                        <option value="1" {{ old('start_day') == '1' ? 'selected' : '' }}>الاثنين</option>
                                        <option value="2" {{ old('start_day') == '2' ? 'selected' : '' }}>الثلاثاء</option>
                                        <option value="3" {{ old('start_day') == '3' ? 'selected' : '' }}>الأربعاء</option>
                                        <option value="4" {{ old('start_day') == '4' ? 'selected' : '' }}>الخميس</option>
                                        <option value="5" {{ old('start_day') == '5' ? 'selected' : '' }}>الجمعة</option>
                                        <option value="6" {{ old('start_day') == '6' ? 'selected' : '' }}>السبت</option>
                                        <option value="7" {{ old('start_day') == '7' ? 'selected' : '' }}>الأحد</option>
                                    </select>
                                    @error('start_day')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">يوم النهاية <span class="text-danger">*</span></label>
                                    <select name="end_day" class="form-select" required>
                                        <option value="1" {{ old('end_day') == '1' ? 'selected' : '' }}>الاثنين</option>
                                        <option value="2" {{ old('end_day') == '2' ? 'selected' : '' }}>الثلاثاء</option>
                                        <option value="3" {{ old('end_day') == '3' ? 'selected' : '' }}>الأربعاء</option>
                                        <option value="4" {{ old('end_day') == '4' ? 'selected' : '' }}>الخميس</option>
                                        <option value="5" {{ old('end_day') == '5' ? 'selected' : '' }}>الجمعة</option>
                                        <option value="6" {{ old('end_day') == '6' ? 'selected' : '' }}>السبت</option>
                                        <option value="7" {{ old('end_day') == '7' ? 'selected' : '' }}>الأحد</option>
                                    </select>
                                    @error('end_day')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                                    @error('order')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.weekly-tasks.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@stop

