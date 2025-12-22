@extends('admin.layouts.master')

@section('page-title')
    إحصائيات: {{ $item->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">إحصائيات: {{ $item->title }}</h5>
            <a href="{{ route('admin.library.items.show', $item->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h3 class="text-primary">{{ $stats['total_downloads'] ?? 0 }}</h3>
                        <p class="mb-0">إجمالي التحميلات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h3 class="text-info">{{ $stats['total_views'] ?? 0 }}</h3>
                        <p class="mb-0">إجمالي المشاهدات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h3 class="text-warning">{{ $stats['recent_downloads'] ?? 0 }}</h3>
                        <p class="mb-0">تحميلات آخر 30 يوم</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h3 class="text-success">{{ $stats['recent_views'] ?? 0 }}</h3>
                        <p class="mb-0">مشاهدات آخر 30 يوم</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">التقييمات</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="text-warning">★ {{ number_format($item->average_rating, 1) }}</h4>
                                <p class="text-muted">من {{ $item->total_ratings }} تقييم</p>
                            </div>
                            <div class="col-md-6">
                                <h6>آخر التقييمات:</h6>
                                @forelse($item->ratings()->latest()->limit(5)->get() as $rating)
                                    <div class="mb-2">
                                        <strong>{{ $rating->user->name ?? 'مستخدم' }}</strong>
                                        <span class="text-warning ms-1">★ {{ $rating->rating }}</span>
                                        @if($rating->comment)
                                            <p class="small text-muted mb-0">{{ Str::limit($rating->comment, 100) }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted">لا توجد تقييمات</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

