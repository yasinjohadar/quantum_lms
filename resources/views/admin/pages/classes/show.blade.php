@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الصف الدراسي
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            {{-- شريط علوي: صورة صغيرة + اسم الصف + أزرار (مثل صفحة تفاصيل المادة) --}}
            <div class="d-flex align-items-center justify-content-between gap-3 py-3 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $class->image ? media_public_url($class->image) : asset('assets/images/media/media-22.jpg') }}"
                         alt="{{ $class->name }}"
                         class="rounded flex-shrink-0"
                         style="width: 56px; height: 56px; object-fit: cover;">
                    <div>
                        <h5 class="page-title mb-0">تفاصيل الصف: {{ $class->name }}</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                            @if ($class->stage)
                                <span class="text-muted small">{{ $class->stage->name }}</span>
                            @endif
                            @if ($class->is_active)
                                <span class="badge bg-success">صف نشط</span>
                            @else
                                <span class="badge bg-danger">غير نشط</span>
                            @endif
                            <span class="text-muted small">ترتيب العرض: {{ $class->order }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-warning btn-sm text-white">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            {{-- قسم المواد بعرض كامل --}}
            <div class="row">
                <div class="col-12">
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
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <div class="ratio ratio-4x3 mb-3 rounded overflow-hidden bg-light mx-auto" style="max-width: 100%;">
                                                        <img src="{{ $subject->image ? media_public_url($subject->image) : asset('assets/images/media/media-22.jpg') }}"
                                                             alt="{{ $subject->name }}"
                                                             class="rounded"
                                                             style="object-fit: contain; width: 100%; height: 100%;">
                                                    </div>
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
                                                    <a href="{{ route('admin.subjects.show', $subject->id) }}?return_to_class_id={{ $class->id }}"
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
