@extends('admin.layouts.master')

@section('page-title')
    لوحة إحصائيات المكتبة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">لوحة إحصائيات المكتبة</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-book me-1"></i> إدارة العناصر
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $totalItems }}</h3>
                            <span class="text-muted">إجمالي العناصر</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $totalDownloads }}</h3>
                            <span class="text-muted">إجمالي التحميلات</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $totalViews }}</h3>
                            <span class="text-muted">إجمالي المشاهدات</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <h3 class="mb-1">
                                {{ $averageRating ? number_format($averageRating, 1) : '0.0' }}
                            </h3>
                            <span class="text-muted">متوسط التقييم</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">أكثر العناصر تحميلاً</h5>
                        </div>
                        <div class="card-body">
                            <div id="library-popular-downloads-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">أكثر العناصر مشاهدة</h5>
                        </div>
                            <div class="card-body">
                                <div id="library-most-viewed-chart"></div>
                            </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">أعلى العناصر تقييماً</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered text-center mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>العنوان</th>
                                        <th>التصنيف</th>
                                        <th>المادة</th>
                                        <th>التقييم</th>
                                        <th>عدد التقييمات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($highestRatedItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['title'] ?? '-' }}</td>
                                            <td>{{ $item['category']['name'] ?? '-' }}</td>
                                            <td>{{ $item['subject']['name'] ?? 'عام' }}</td>
                                            <td>{{ $item['average_rating'] ?? 0 }}</td>
                                            <td>{{ $item['total_ratings'] ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">لا توجد بيانات متاحة.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script src="{{ asset('assets/js/apexcharts-bar.js') }}"></script>
    <script>
        (function () {
            "use strict";

            const popularDownloads = @json($popularItems);
            const mostViewed = @json($mostViewedItems);

            const downloadsOptions = {
                series: [{
                    name: 'التحميلات',
                    data: popularDownloads.map(i => i.download_count ?? 0),
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: popularDownloads.map(i => i.title ?? ''),
                }
            };

            const viewsOptions = {
                series: [{
                    name: 'المشاهدات',
                    data: mostViewed.map(i => i.view_count ?? 0),
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: mostViewed.map(i => i.title ?? ''),
                }
            };

            if (document.querySelector('#library-popular-downloads-chart')) {
                new ApexCharts(document.querySelector('#library-popular-downloads-chart'), downloadsOptions).render();
            }

            if (document.querySelector('#library-most-viewed-chart')) {
                new ApexCharts(document.querySelector('#library-most-viewed-chart'), viewsOptions).render();
            }
        })();
    </script>
@endpush


