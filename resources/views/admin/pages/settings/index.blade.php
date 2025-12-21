@extends('admin.layouts.master')

@section('page-title')
    الإعدادات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الإعدادات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإعدادات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Group Tabs -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    @foreach($groups as $key => $name)
                        <li class="nav-item">
                            <a class="nav-link {{ $group == $key ? 'active' : '' }}" 
                               href="{{ route('admin.settings.index', ['group' => $key]) }}">
                                {{ $name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">إعدادات {{ $groups[$group] ?? $group }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update', $group) }}">
                    @csrf
                    @method('PUT')

                    @if($settings->count() > 0)
                        <div class="row">
                            @foreach($settings as $setting)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ $setting->key }}</label>
                                    @if($setting->description)
                                        <small class="text-muted d-block mb-1">{{ $setting->description }}</small>
                                    @endif
                                    
                                    @if($setting->type == 'boolean')
                                        <select name="{{ $setting->key }}" class="form-select">
                                            <option value="1" {{ $setting->value ? 'selected' : '' }}>نعم</option>
                                            <option value="0" {{ !$setting->value ? 'selected' : '' }}>لا</option>
                                        </select>
                                    @elseif($setting->type == 'text')
                                        <textarea name="{{ $setting->key }}" class="form-control" rows="3">{{ $setting->value }}</textarea>
                                    @else
                                        <input type="{{ $setting->type == 'integer' ? 'number' : 'text' }}" 
                                               name="{{ $setting->key }}" 
                                               class="form-control" 
                                               value="{{ $setting->value }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                حفظ الإعدادات
                            </button>
                            <a href="{{ route('admin.settings.index', ['group' => $group]) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-right me-1"></i>
                                إلغاء
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">لا توجد إعدادات في هذه المجموعة</p>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@stop

