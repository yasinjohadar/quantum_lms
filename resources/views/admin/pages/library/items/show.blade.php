@extends('admin.layouts.master')

@section('page-title')
    عرض عنصر: {{ $item->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">عرض عنصر: {{ $item->title }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.library.items.edit', $item->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> تعديل
                </a>
                <a href="{{ route('admin.library.items.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">معلومات العنصر</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">العنوان</th>
                                <td>{{ $item->title }}</td>
                            </tr>
                            <tr>
                                <th>الوصف</th>
                                <td>{{ $item->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>النوع</th>
                                <td>
                                    <span class="badge bg-info">
                                        {{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>التصنيف</th>
                                <td>{{ $item->category->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>المادة</th>
                                <td>{{ $item->subject->name ?? 'عام' }}</td>
                            </tr>
                            <tr>
                                <th>مستوى الوصول</th>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ \App\Models\LibraryItem::ACCESS_LEVELS[$item->access_level] ?? $item->access_level }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>الحالة</th>
                                <td>
                                    <span class="badge bg-{{ $item->is_public ? 'success' : 'warning' }}">
                                        {{ $item->is_public ? 'عام' : 'خاص' }}
                                    </span>
                                    @if($item->is_featured)
                                        <span class="badge bg-warning ms-1">مميز</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>من رفع</th>
                                <td>{{ $item->uploader->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء</th>
                                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @if($item->file_path)
                            <tr>
                                <th>الملف</th>
                                <td>
                                    <a href="{{ route('admin.library.items.download', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download me-1"></i> تحميل
                                    </a>
                                    <span class="ms-2">{{ $item->file_name }} ({{ $item->formatted_file_size }})</span>
                                </td>
                            </tr>
                            @endif
                            @if($item->external_url)
                            <tr>
                                <th>الرابط الخارجي</th>
                                <td>
                                    <a href="{{ $item->external_url }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-external-link-alt me-1"></i> فتح الرابط
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($item->tags->count() > 0)
                            <tr>
                                <th>الوسوم</th>
                                <td>
                                    @foreach($item->tags as $tag)
                                        <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">الإحصائيات</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>عدد التحميلات:</strong> {{ $stats['total_downloads'] ?? $item->download_count }}
                        </div>
                        <div class="mb-3">
                            <strong>عدد المشاهدات:</strong> {{ $stats['total_views'] ?? $item->view_count }}
                        </div>
                        <div class="mb-3">
                            <strong>التقييم المتوسط:</strong> 
                            @if($item->total_ratings > 0)
                                <span class="text-warning">★</span> {{ number_format($item->average_rating, 1) }} ({{ $item->total_ratings }} تقييم)
                            @else
                                <span class="text-muted">لا يوجد تقييمات</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.library.items.stats', $item->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-chart-bar me-1"></i> عرض الإحصائيات التفصيلية
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

