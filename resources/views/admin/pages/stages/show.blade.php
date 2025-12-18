@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المرحلة الدراسية
@stop

@section('css')
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تفاصيل المرحلة: {{ $stage->name }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.stages.edit', $stage->id) }}" class="btn btn-warning btn-sm text-white">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="{{ route('admin.stages.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="{{ $stage->image ? asset('storage/' . $stage->image) : asset('assets/images/media/media-22.jpg') }}"
                                     alt="{{ $stage->name }}"
                                     class="rounded"
                                     style="width: 180px; height: 180px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold mb-1">{{ $stage->name }}</h5>
                            <p class="mb-1">
                                @if ($stage->is_active)
                                    <span class="badge bg-success">مرحلة نشطة</span>
                                @else
                                    <span class="badge bg-danger">غير نشطة</span>
                                @endif
                            </p>
                            <p class="text-muted mb-0">
                                ترتيب العرض: <span class="fw-semibold">{{ $stage->order }}</span>
                            </p>
                        </div>
                    </div>

                    @if ($stage->og_image || $stage->meta_title || $stage->meta_description || $stage->meta_keywords)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">بيانات الـ SEO</h6>
                            </div>
                            <div class="card-body">
                                @if ($stage->meta_title)
                                    <p class="mb-2"><span class="fw-semibold">Meta Title: </span>{{ $stage->meta_title }}</p>
                                @endif
                                @if ($stage->meta_description)
                                    <p class="mb-2"><span class="fw-semibold">Meta Description: </span>{{ $stage->meta_description }}</p>
                                @endif
                                @if ($stage->meta_keywords)
                                    <p class="mb-2"><span class="fw-semibold">Meta Keywords: </span>{{ $stage->meta_keywords }}</p>
                                @endif>
                                @if ($stage->og_image)
                                    <div class="mt-2">
                                        <span class="fw-semibold d-block mb-1">صورة Open Graph:</span>
                                        <img src="{{ asset('storage/' . $stage->og_image) }}" alt="{{ $stage->name }}"
                                             class="rounded" style="width: 160px; height: 160px; object-fit: cover;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-xl-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات المرحلة</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                <span class="fw-semibold">الوصف:</span>
                                <br>
                                {{ $stage->description ?: 'لا يوجد وصف متاح لهذه المرحلة حالياً.' }}
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">الرابط الدائم:</span>
                                {{ $stage->slug ?: 'لم يتم تعيين رابط دائم' }}
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">تاريخ الإنشاء:</span>
                                {{ $stage->created_at?->format('Y-m-d H:i') }}
                            </p>
                            <p class="mb-0">
                                <span class="fw-semibold">تاريخ آخر تحديث:</span>
                                {{ $stage->updated_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">الصفوف التابعة لهذه المرحلة</h6>
                            <a href="{{ route('admin.classes.create') }}?stage_id={{ $stage->id }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> إضافة صف جديد
                            </a>
                        </div>
                        <div class="card-body">
                            @if ($stage->classes && $stage->classes->count())
                                <div class="row g-3">
                                    @foreach ($stage->classes as $class)
                                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-12">
                                            <div class="card custom-card h-100">
                                                <div class="card-img-top-wrapper">
                                                    <img src="{{ $class->image ? asset('storage/' . $class->image) : asset('assets/images/media/media-22.jpg') }}"
                                                         class="card-img-top" alt="{{ $class->name }}">
                                                </div>
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title fw-semibold mb-1">{{ $class->name }}</h6>
                                                    <p class="text-muted small mb-2">
                                                        المرحلة: {{ $stage->name }}
                                                    </p>
                                                    <p class="card-text text-muted small mb-3">
                                                        {{ \Illuminate\Support\Str::limit($class->description, 80) ?: 'لا يوجد وصف لهذا الصف حالياً.' }}
                                                    </p>
                                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-light text-dark border">
                                                            ترتيب: {{ $class->order ?? 0 }}
                                                        </span>
                                                        @if ($class->is_active)
                                                            <span class="badge bg-success">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger">غير نشط</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="card-footer d-flex justify-content-between align-items-center">
                                                    <a href="{{ route('admin.classes.show', $class->id) }}"
                                                       class="btn btn-sm btn-primary">
                                                        عرض الصف
                                                    </a>
                                                    <a href="{{ route('admin.subjects.index', ['class_id' => $class->id]) }}"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        المواد
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center text-muted mb-0">
                                    لا توجد صفوف مرتبطة بهذه المرحلة حالياً.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


