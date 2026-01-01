@extends('admin.layouts.master')

@section('page-title')
    الإعدادات العامة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الإعدادات العامة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإعدادات</li>
                    </ol>
                </nav>
            </div>
        </div>

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

        <div class="row">
            <div class="col-lg-12">
                <!-- Tabs للتنقل بين مجموعات الإعدادات -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach($groups as $groupKey => $groupName)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $group === $groupKey ? 'active' : '' }}" 
                                       href="{{ route('admin.settings.index', ['group' => $groupKey]) }}">
                                        {{ $groupName }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        @if($settings->count() > 0)
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="group" value="{{ $group }}">
                                
                                <div class="row g-3">
                                    @foreach($settings as $setting)
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                {{ $setting->key }}
                                                @if($setting->description)
                                                    <small class="text-muted">({{ $setting->description }})</small>
                                                @endif
                                            </label>
                                            
                                            @if($setting->type === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="settings[{{ $setting->key }}]" 
                                                           value="1" 
                                                           id="setting_{{ $setting->id }}"
                                                           {{ $setting->value ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="setting_{{ $setting->id }}">
                                                        مفعّل
                                                    </label>
                                                </div>
                                            @elseif($setting->type === 'integer')
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="settings[{{ $setting->key }}]" 
                                                       id="setting_{{ $setting->id }}" 
                                                       value="{{ $setting->value }}">
                                            @elseif($setting->type === 'json')
                                                <textarea class="form-control" 
                                                         name="settings[{{ $setting->key }}]" 
                                                         id="setting_{{ $setting->id }}" 
                                                         rows="3">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $setting->value }}</textarea>
                                            @else
                                                <input type="text" 
                                                      class="form-control" 
                                                      name="settings[{{ $setting->key }}]" 
                                                      id="setting_{{ $setting->id }}" 
                                                      value="{{ $setting->value }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> حفظ الإعدادات
                                    </button>
                                    <form action="{{ route('admin.settings.reset', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إعادة تعيين جميع إعدادات هذه المجموعة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-redo me-1"></i> إعادة تعيين
                                        </button>
                                    </form>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد إعدادات في هذه المجموعة.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




