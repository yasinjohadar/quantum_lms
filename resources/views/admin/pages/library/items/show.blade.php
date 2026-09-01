@extends('admin.layouts.master')

@section('page-title')
    {{ $item->title }}
@stop

@push('styles')
    @include('admin.pages.library.partials.library-styles')
@endpush

@section('content')
    <div class="main-content app-content library-form-page">
        <div class="container-fluid">

            <div class="library-form-hero my-4">
                <div class="library-form-hero__icon">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <div class="library-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.library.items.index') }}">عناصر المكتبة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $item->title }}</li>
                        </ol>
                    </nav>
                    <h4 class="library-form-hero__title">{{ $item->title }}</h4>
                    <p class="library-form-hero__subtitle">{{ $item->description ?: 'لا يوجد وصف' }}</p>
                </div>
                <div class="library-form-hero__actions">
                    @can('library-item-edit')
                        <a href="{{ route('admin.library.items.edit', $item->id) }}" class="btn btn-warning btn-sm text-white">
                            <i class="bi bi-pencil me-1"></i> تعديل
                        </a>
                    @endcan
                    <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            <div class="library-form-card">
                <div class="library-form-card__header">
                    <span class="library-form-card__header-icon"><i class="bi bi-info-circle"></i></span>
                    <div>
                        <div class="library-form-card__title">تفاصيل العنصر</div>
                    </div>
                </div>
                <div class="library-form-card__body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 180px;">النوع</th>
                            <td><span class="badge bg-info">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span></td>
                        </tr>
                        <tr>
                            <th>التصنيف</th>
                            <td>{{ $item->category?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>الصف</th>
                            <td>{{ $item->schoolClass?->name ?? 'كل الصفوف' }}</td>
                        </tr>
                        <tr>
                            <th>المادة</th>
                            <td>{{ $item->subject?->name ?? 'كل المواد' }}</td>
                        </tr>
                        <tr>
                            <th>مستوى الوصول</th>
                            <td>{{ \App\Models\LibraryItem::ACCESS_LEVELS[$item->access_level] ?? $item->access_level }}</td>
                        </tr>
                        <tr>
                            <th>الحالة</th>
                            <td>
                                @if ($item->is_public)
                                    <span class="badge bg-success">عام</span>
                                @else
                                    <span class="badge bg-secondary">خاص</span>
                                @endif
                                @if ($item->is_featured)
                                    <span class="badge bg-warning text-dark">مميز</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>رفعه</th>
                            <td>{{ $item->uploader?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الإضافة</th>
                            <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if ($item->file_path)
                            <tr>
                                <th>الملف</th>
                                <td>
                                    {{ $item->file_name }} ({{ $item->formatted_file_size }})
                                    <a href="{{ route('admin.library.items.download', $item->id) }}" class="btn btn-sm btn-primary ms-2">
                                        <i class="bi bi-download me-1"></i> تحميل
                                    </a>
                                </td>
                            </tr>
                        @elseif ($item->external_url)
                            <tr>
                                <th>الرابط</th>
                                <td><a href="{{ $item->external_url }}" target="_blank">{{ $item->external_url }}</a></td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
