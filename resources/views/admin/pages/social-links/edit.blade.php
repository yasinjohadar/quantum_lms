@extends('admin.layouts.master')

@section('page-title')
    تعديل رابط التواصل
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تعديل رابط التواصل</h5>
                <a href="{{ route('admin.social-links.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    @include('admin.pages.social-links.partials.form', [
                        'action' => route('admin.social-links.update', $socialLink),
                        'suggestedIcons' => $suggestedIcons,
                        'socialLink' => $socialLink,
                    ])
                </div>
            </div>
        </div>
    </div>
@stop
