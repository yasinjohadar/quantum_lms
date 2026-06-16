@extends('admin.layouts.master')

@section('page-title')
    تعديل إنجاز
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'تعديل إنجاز',
            'gamiIcon' => 'bi-star',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'الإنجازات', 'url' => route('admin.achievements.index')],
                ['label' => 'تعديل', 'active' => true],
            ],
        ])

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="gami-card gami-form-card">
                    <div class="gami-card__header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="gami-card__header-icon"><i class="bi bi-pencil-square"></i></span>
                            <span>معلومات الإنجاز</span>
                        </div>
                    </div>
                    <div class="gami-card__body">
                        <form action="{{ route('admin.achievements.update', $achievement) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $achievement->name) }}" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="attendance" {{ old('type', $achievement->type) == 'attendance' ? 'selected' : '' }}>حضور</option>
                                        <option value="quiz" {{ old('type', $achievement->type) == 'quiz' ? 'selected' : '' }}>اختبار</option>
                                        <option value="course" {{ old('type', $achievement->type) == 'course' ? 'selected' : '' }}>كورس</option>
                                        <option value="special" {{ old('type', $achievement->type) == 'special' ? 'selected' : '' }}>خاص</option>
                                        <option value="streak" {{ old('type', $achievement->type) == 'streak' ? 'selected' : '' }}>سلسلة</option>
                                    </select>
                                    @error('type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $achievement->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">مكافأة النقاط</label>
                                    <input type="number" name="points_reward" class="form-control" value="{{ old('points_reward', $achievement->points_reward) }}" min="0">
                                    @error('points_reward')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">الشارة المرتبطة</label>
                                    <select name="badge_id" class="form-select">
                                        <option value="">لا يوجد</option>
                                        @foreach($badges as $badge)
                                            <option value="{{ $badge->id }}" {{ old('badge_id', $achievement->badge_id) == $badge->id ? 'selected' : '' }}>
                                                {{ $badge->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('badge_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="order" class="form-control" value="{{ old('order', $achievement->order) }}" min="0">
                                    @error('order')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $achievement->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                            </div>
                            <div class="gami-form-actions">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.achievements.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
