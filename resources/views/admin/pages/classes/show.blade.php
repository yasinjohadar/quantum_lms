@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الصف الدراسي
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
                <h5 class="page-title mb-0">تفاصيل الصف: {{ $class->name }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-warning btn-sm text-white">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="{{ $class->image ? asset('storage/'.$class->image) : asset('assets/images/media/media-22.jpg') }}"
                                     alt="{{ $class->name }}"
                                     class="rounded"
                                     style="width: 180px; height: 180px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold mb-1">{{ $class->name }}</h5>
                            <p class="mb-1 text-muted">
                                المرحلة: {{ $class->stage?->name ?? '-' }}
                            </p>
                            <p class="mb-1">
                                @if ($class->is_active)
                                    <span class="badge bg-success">صف نشط</span>
                                @else
                                    <span class="badge bg-danger">غير نشط</span>
                                @endif
                            </p>
                            <p class="text-muted mb-0">
                                ترتيب العرض: <span class="fw-semibold">{{ $class->order }}</span>
                            </p>
                        </div>
                    </div>

                    @if ($class->meta_title || $class->meta_description || $class->meta_keywords || $class->og_image)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">بيانات الـ SEO</h6>
                            </div>
                            <div class="card-body">
                                @if ($class->meta_title)
                                    <p class="mb-2"><span class="fw-semibold">Meta Title: </span>{{ $class->meta_title }}</p>
                                @endif
                                @if ($class->meta_description)
                                    <p class="mb-2"><span class="fw-semibold">Meta Description: </span>{{ $class->meta_description }}</p>
                                @endif
                                @if ($class->meta_keywords)
                                    <p class="mb-2"><span class="fw-semibold">Meta Keywords: </span>{{ $class->meta_keywords }}</p>
                                @endif
                                @if ($class->og_image)
                                    <div class="mt-2">
                                        <span class="fw-semibold d-block mb-1">صورة Open Graph:</span>
                                        <img src="{{ asset('storage/'.$class->og_image) }}" alt="{{ $class->name }}"
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
                            <h6 class="mb-0">معلومات الصف</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                <span class="fw-semibold">الوصف:</span>
                                <br>
                                {{ $class->description ?: 'لا يوجد وصف متاح لهذا الصف حالياً.' }}
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">الرابط الدائم:</span>
                                {{ $class->slug ?: 'لم يتم تعيين رابط دائم' }}
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">تاريخ الإنشاء:</span>
                                {{ $class->created_at?->format('Y-m-d H:i') }}
                            </p>
                            <p class="mb-0">
                                <span class="fw-semibold">تاريخ آخر تحديث:</span>
                                {{ $class->updated_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">المواد المرتبطة بهذا الصف</h6>
                            <a href="{{ route('admin.subjects.create') }}?class_id={{ $class->id }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> إضافة مادة جديدة
                            </a>
                        </div>
                        <div class="card-body">
                            @if ($class->subjects && $class->subjects->count())
                                <div class="row g-3">
                                    @foreach ($class->subjects as $subject)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <h6 class="fw-semibold mb-1">{{ $subject->name }}</h6>
                                                    <p class="mb-1 text-muted small">
                                                        ترتيب: {{ $subject->order ?? 0 }}
                                                    </p>
                                                    @if ($subject->is_active)
                                                        <span class="badge bg-success">نشطة</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشطة</span>
                                                    @endif
                                                </div>
                                                <div class="card-footer text-center">
                                                    <a href="{{ route('admin.subjects.show', $subject->id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        عرض المادة
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center text-muted mb-0">
                                    لا توجد مواد مرتبطة بهذا الصف حالياً.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

