@extends('admin.layouts.master')

@section('page-title')
    إضافة مكافأة جديدة
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إضافة مكافأة جديدة',
            'gamiIcon' => 'bi-gift',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'المكافآت', 'url' => route('admin.rewards.index')],
                ['label' => 'إضافة جديدة', 'active' => true],
            ],
        ])

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="gami-card gami-form-card">
                    <div class="gami-card__header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="gami-card__header-icon"><i class="bi bi-plus-circle"></i></span>
                            <span>معلومات المكافأة</span>
                        </div>
                    </div>
                    <div class="gami-card__body">
                        <form action="{{ route('admin.rewards.store') }}" method="POST">
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
                                        <option value="discount" {{ old('type') == 'discount' ? 'selected' : '' }}>خصم</option>
                                        <option value="badge" {{ old('type') == 'badge' ? 'selected' : '' }}>شارة</option>
                                        <option value="points" {{ old('type') == 'points' ? 'selected' : '' }}>نقاط</option>
                                        <option value="access" {{ old('type') == 'access' ? 'selected' : '' }}>وصول</option>
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
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تكلفة النقاط <span class="text-danger">*</span></label>
                                    <input type="number" name="points_cost" class="form-control" value="{{ old('points_cost') }}" min="0" required>
                                    @error('points_cost')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الكمية المتاحة</label>
                                    <input type="number" name="quantity_available" class="form-control" value="{{ old('quantity_available') }}" min="0" placeholder="اتركه فارغاً للكمية غير المحدودة">
                                    @error('quantity_available')
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
                            <div class="gami-form-actions">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.rewards.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
