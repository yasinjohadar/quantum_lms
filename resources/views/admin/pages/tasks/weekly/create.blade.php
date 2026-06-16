@extends('admin.layouts.master')

@section('page-title')
    إضافة مهمة أسبوعية جديدة
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إضافة مهمة أسبوعية جديدة',
            'gamiIcon' => 'bi-calendar-week',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'المهام الأسبوعية', 'url' => route('admin.weekly-tasks.index')],
                ['label' => 'إضافة جديدة', 'active' => true],
            ],
        ])

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="gami-card gami-form-card">
                    <div class="gami-card__header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="gami-card__header-icon"><i class="bi bi-plus-circle"></i></span>
                            <span>معلومات المهمة</span>
                        </div>
                    </div>
                    <div class="gami-card__body">
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
                            <div class="gami-form-actions">
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
@stop
