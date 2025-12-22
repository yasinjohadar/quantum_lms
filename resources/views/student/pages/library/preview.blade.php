@extends('student.layouts.master')

@section('page-title')
    معاينة: {{ $item->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">معاينة: {{ $item->title }}</h5>
            <div class="d-flex gap-2">
                @if($item->file_path)
                    <form action="{{ route('student.library.download', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-download me-1"></i> تحميل
                        </button>
                    </form>
                @endif
                <a href="{{ route('student.library.show', $item->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @if($item->file_path)
                            @php
                                $extension = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION));
                                $fileUrl = Storage::disk('public')->url($item->file_path);
                            @endphp

                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ $fileUrl }}" alt="{{ $item->title }}" class="img-fluid">
                            @elseif(in_array($extension, ['pdf']))
                                <iframe src="{{ $fileUrl }}" width="100%" height="800px" style="border: none;"></iframe>
                            @elseif(in_array($extension, ['mp4', 'webm']))
                                <video controls width="100%" style="max-height: 600px;">
                                    <source src="{{ $fileUrl }}" type="video/{{ $extension }}">
                                    متصفحك لا يدعم تشغيل الفيديو.
                                </video>
                            @else
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    لا يمكن معاينة هذا النوع من الملفات. يرجى تحميله.
                                    <br>
                                    <a href="{{ route('student.library.download', $item->id) }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-download me-1"></i> تحميل الملف
                                    </a>
                                </div>
                            @endif
                        @elseif($item->external_url)
                            <div class="alert alert-info text-center">
                                <i class="fas fa-external-link-alt me-2"></i>
                                هذا عنصر رابط خارجي.
                                <br>
                                <a href="{{ $item->external_url }}" target="_blank" class="btn btn-primary mt-2">
                                    <i class="fas fa-external-link-alt me-1"></i> فتح الرابط
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                لا يوجد ملف أو رابط للمعاينة.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

