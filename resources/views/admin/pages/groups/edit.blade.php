@extends('admin.layouts.master')

@section('page-title')
    تعديل المجموعة
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تعديل المجموعة</h5>
                <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.groups.update', $group->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="اسم المجموعة"
                                           value="{{ old('name', $group->name) }}" required>
                                    <label>اسم المجموعة <span class="text-danger">*</span></label>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="color"
                                           class="form-control @error('color') is-invalid @enderror"
                                           placeholder="#RRGGBB"
                                           value="{{ old('color', $group->color) }}"
                                           pattern="^#[0-9A-Fa-f]{6}$">
                                    <label>اللون (Hex Code)</label>
                                    <small class="text-muted">مثال: #FF5733</small>
                                    @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea name="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="وصف المجموعة"
                                              style="height: 100px">{{ old('description', $group->description) }}</textarea>
                                    <label>الوصف</label>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                           {{ old('is_active', $group->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        المجموعة نشطة
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> حفظ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop


