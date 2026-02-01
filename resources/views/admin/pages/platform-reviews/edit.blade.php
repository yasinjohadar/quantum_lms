@extends('admin.layouts.master')

@section('page-title')
    تعديل رأي الطالب
@stop

@section('content')
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
                <h5 class="page-title mb-0">تعديل رأي الطالب: {{ $review->user->name ?? '—' }}</h5>
                <a href="{{ route('admin.platform-reviews.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.platform-reviews.update', $review) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <p class="text-muted small mb-0">الطالب: <strong>{{ $review->user->name ?? '—' }}</strong> | النجوم: {{ $review->stars }} | تاريخ الإرسال: {{ $review->created_at?->format('Y-m-d H:i') }}</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label">التعليق (النص المعروض بعد الاعتماد) <span class="text-danger">*</span></label>
                                <textarea name="comment" class="form-control @error('comment') is-invalid @enderror" rows="4" maxlength="2000" required>{{ old('comment', $review->comment) }}</textarea>
                                @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="pending" {{ old('status', $review->status) === 'pending' ? 'selected' : '' }}>معلق</option>
                                    <option value="approved" {{ old('status', $review->status) === 'approved' ? 'selected' : '' }}>معتمد</option>
                                    <option value="rejected" {{ old('status', $review->status) === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الصف المعروض بجانب الاسم</label>
                                <select name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                    <option value="">— لا صف —</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $review->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" min="0" value="{{ old('order', $review->order) }}">
                                @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">صورة الطالب (اختياري — يرفعها الأدمن)</label>
                                @if($review->photo)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $review->photo) }}" alt="صورة الطالب" class="rounded" style="max-height: 80px;">
                                        <label class="ms-2">
                                            <input type="checkbox" name="remove_photo" value="1"> إزالة الصورة
                                        </label>
                                    </div>
                                @endif
                                <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="text-muted">إن لم تُرفع صورة، تُعرض صورة المستخدم من حسابه.</small>
                                @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                                </button>
                                <a href="{{ route('admin.platform-reviews.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
