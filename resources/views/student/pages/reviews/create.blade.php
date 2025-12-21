@extends('student.layouts.master')

@section('page-title')
    إنشاء تقييم
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء تقييم</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إنشاء تقييم</li>
                    </ol>
                </nav>
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

        @if($existingReview)
            <div class="alert alert-info">
                <i class="fe fe-info me-2"></i>
                لديك تقييم سابق. يمكنك <a href="{{ route('student.reviews.edit', $existingReview) }}">تعديله</a> بدلاً من إنشاء تقييم جديد.
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">تقييم: {{ $reviewable->name }}</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.reviews.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="id" value="{{ $reviewable->id }}">

                            <div class="mb-3">
                                <label class="form-label">التقييم بالنجوم <span class="text-danger">*</span></label>
                                <div class="rating-input">
                                    <div class="d-flex align-items-center gap-2">
                                        @for($i = 5; $i >= 1; $i--)
                                            <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" class="d-none" {{ old('rating', $existingReview->rating ?? '') == $i ? 'checked' : '' }} required>
                                            <label for="rating{{ $i }}" class="rating-star cursor-pointer">
                                                <i class="fe fe-star fs-32 {{ old('rating', $existingReview->rating ?? 0) >= $i ? 'text-warning fill' : 'text-muted' }}"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                @error('rating')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عنوان المراجعة (اختياري)</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $existingReview->title ?? '') }}" maxlength="255">
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص المراجعة (اختياري)</label>
                                <textarea name="comment" class="form-control" rows="5" maxlength="2000">{{ old('comment', $existingReview->comment ?? '') }}</textarea>
                                <small class="text-muted">الحد الأقصى: 2000 حرف</small>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1" {{ old('is_anonymous', $existingReview->is_anonymous ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_anonymous">
                                        تقييم مجهول
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-send me-1"></i> إرسال التقييم
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Rating stars interaction
    document.querySelectorAll('.rating-star').forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = 5 - index;
            document.querySelector(`#rating${rating}`).checked = true;
            
            // Update visual stars
            document.querySelectorAll('.rating-star i').forEach((icon, i) => {
                if (i <= index) {
                    icon.classList.remove('text-muted');
                    icon.classList.add('text-warning', 'fill');
                } else {
                    icon.classList.remove('text-warning', 'fill');
                    icon.classList.add('text-muted');
                }
            });
        });

        star.addEventListener('mouseenter', function() {
            const rating = 5 - index;
            document.querySelectorAll('.rating-star i').forEach((icon, i) => {
                if (i <= index) {
                    icon.classList.add('text-warning');
                }
            });
        });
    });

    document.querySelector('.rating-input').addEventListener('mouseleave', function() {
        const checkedRating = document.querySelector('input[name="rating"]:checked')?.value || 0;
        document.querySelectorAll('.rating-star i').forEach((icon, i) => {
            if (i < checkedRating) {
                icon.classList.remove('text-muted');
                icon.classList.add('text-warning', 'fill');
            } else {
                icon.classList.remove('text-warning', 'fill');
                icon.classList.add('text-muted');
            }
        });
    });
</script>
@endpush
@endsection

