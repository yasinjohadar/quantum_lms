@extends('student.layouts.master')

@section('page-title')
    تعديل التقييم
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل التقييم</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تعديل التقييم</li>
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

        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">تعديل تقييم: {{ $review->reviewable->name }}</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.reviews.update', $review) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">التقييم بالنجوم <span class="text-danger">*</span></label>
                                <div class="rating-input">
                                    <div class="d-flex align-items-center gap-2">
                                        @for($i = 5; $i >= 1; $i--)
                                            <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" class="d-none" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
                                            <label for="rating{{ $i }}" class="rating-star cursor-pointer">
                                                <i class="fe fe-star fs-32 {{ old('rating', $review->rating) >= $i ? 'text-warning fill' : 'text-muted' }}"></i>
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
                                <input type="text" name="title" class="form-control" value="{{ old('title', $review->title) }}" maxlength="255">
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص المراجعة (اختياري)</label>
                                <textarea name="comment" class="form-control" rows="5" maxlength="2000">{{ old('comment', $review->comment) }}</textarea>
                                <small class="text-muted">الحد الأقصى: 2000 حرف</small>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1" {{ old('is_anonymous', $review->is_anonymous) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_anonymous">
                                        تقييم مجهول
                                    </label>
                                </div>
                            </div>

                            @if($review->status === 'approved')
                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    سيتم إعادة التقييم إلى قيد المراجعة بعد التعديل.
                                </div>
                            @endif

                            <div class="d-flex justify-content-between gap-2">
                                <form action="{{ route('student.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف التقييم؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fe fe-trash-2 me-1"></i> حذف
                                    </button>
                                </form>
                                <div class="d-flex gap-2">
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary">إلغاء</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-save me-1"></i> حفظ التعديلات
                                    </button>
                                </div>
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
    // Rating stars interaction (same as create.blade.php)
    document.querySelectorAll('.rating-star').forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = 5 - index;
            document.querySelector(`#rating${rating}`).checked = true;
            
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
    });
</script>
@endpush
@endsection

