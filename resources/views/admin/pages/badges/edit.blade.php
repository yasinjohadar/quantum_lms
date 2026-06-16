@extends('admin.layouts.master')

@section('page-title')
    تعديل شارة
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'تعديل شارة',
            'gamiIcon' => 'bi-award',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'الشارات', 'url' => route('admin.badges.index')],
                ['label' => 'تعديل', 'active' => true],
            ],
        ])

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="gami-card gami-form-card">
                    <div class="gami-card__header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="gami-card__header-icon"><i class="bi bi-pencil-square"></i></span>
                            <span>معلومات الشارة</span>
                        </div>
                    </div>
                    <div class="gami-card__body">
                        <form action="{{ route('admin.badges.update', $badge) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $badge->name) }}" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الأيقونة</label>
                                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $badge->icon) }}" placeholder="bi bi-award">
                                    @error('icon')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $badge->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">اللون</label>
                                    <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', $badge->color) }}">
                                    @error('color')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">النقاط المطلوبة</label>
                                    <input type="number" name="points_required" class="form-control" value="{{ old('points_required', $badge->points_required) }}" min="0">
                                    @error('points_required')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="order" class="form-control" value="{{ old('order', $badge->order) }}" min="0">
                                    @error('order')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $badge->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_automatic" id="is_automatic" value="1" {{ old('is_automatic', $badge->is_automatic) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_automatic">منح تلقائي</label>
                                    </div>
                                </div>
                            </div>
                            <div class="gami-form-actions">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.badges.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
