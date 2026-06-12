@extends('admin.layouts.master')

@section('page-title')
    الإعدادات العامة
@stop

@push('styles')
    @include('admin.pages.settings.partials.index-styles')
@endpush

@section('content')
@php
    $groupIcons = [
        'general' => 'bi-sliders',
        'reports' => 'bi-bar-chart',
        'analytics' => 'bi-graph-up',
        'dashboard' => 'bi-speedometer2',
        'notifications' => 'bi-bell',
        'export' => 'bi-download',
        'gamification' => 'bi-trophy',
        'ai' => 'bi-robot',
        'email' => 'bi-envelope',
        'sms' => 'bi-chat-dots',
        'whatsapp' => 'bi-whatsapp',
        'phone_verification' => 'bi-phone',
        'storage' => 'bi-hdd-stack',
        'payments' => 'bi-credit-card',
    ];
    $activeGroupName = $groups[$group] ?? $group;
    $settingsCount = $settings->count();
@endphp
<div class="main-content app-content settings-page">
    <div class="container-fluid">

        <div class="settings-hero my-4">
            <div class="settings-hero__icon">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <div class="settings-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإعدادات</li>
                    </ol>
                </nav>
                <h4 class="settings-hero__title">الإعدادات العامة</h4>
                <p class="settings-hero__subtitle">ضبط سلوك المنصة، التواصل، المدفوعات، والمحتوى من مكان واحد</p>
            </div>
            <div class="settings-hero__stat">
                <span class="settings-hero__stat-value">{{ number_format($settingsCount) }}</span>
                <span class="settings-hero__stat-label">إعداد في «{{ $activeGroupName }}»</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="settings-card mb-4">
            <div class="settings-card__header">
                <h5 class="settings-card__header-title">
                    <span class="settings-card__header-icon"><i class="bi bi-grid-3x3-gap"></i></span>
                    مجموعات الإعدادات
                </h5>
            </div>
            <div class="settings-card__body pb-2">
                <div class="settings-groups">
                    @foreach($groups as $groupKey => $groupName)
                        <a href="{{ route('admin.settings.index', ['group' => $groupKey]) }}"
                           class="settings-groups__pill {{ $group === $groupKey ? 'is-active' : '' }}">
                            <i class="bi {{ $groupIcons[$groupKey] ?? 'bi-dot' }}"></i>
                            {{ $groupName }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card__header">
                <h5 class="settings-card__header-title">
                    <span class="settings-card__header-icon"><i class="bi {{ $groupIcons[$group] ?? 'bi-sliders' }}"></i></span>
                    إعدادات: {{ $activeGroupName }}
                </h5>
                @if($settingsCount > 0)
                    <span class="badge rounded-pill text-bg-light border">{{ $settingsCount }} حقل</span>
                @endif
            </div>
            <div class="settings-card__body">
                @if($settingsCount > 0)
                    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="{{ $group }}">

                        <div class="row g-3">
                            @foreach($settings as $setting)
                                @include('admin.pages.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>

                        <div class="settings-form-footer">
                            <div class="small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                التغييرات تُطبَّق فور الحفظ على المجموعة الحالية «{{ $activeGroupName }}».
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> حفظ الإعدادات
                                </button>
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('admin.settings.reset', $group) }}" method="POST" class="mt-3"
                          onsubmit="return confirm('هل أنت متأكد من إعادة تعيين جميع إعدادات هذه المجموعة؟');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> إعادة تعيين مجموعة «{{ $activeGroupName }}»
                        </button>
                    </form>
                @else
                    <div class="settings-empty">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 fw-semibold">لا توجد إعدادات في هذه المجموعة حالياً.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
