@extends('admin.layouts.master')

@section('page-title')
    تعديل الدرس — {{ $lesson->title }}
@stop

@section('content')
    @php
        $user = auth()->user();
        $isTeacherReviewer = $user->shouldSubmitContentForReview();
        $lessonMandatoryReview = \App\Models\SystemSetting::lessonMandatoryReviewEnabled();
        $lessonUpdateButtonLabel = (! $user->canReviewContent() && $lessonMandatoryReview)
            ? 'حفظ وإرسال للمراجعة'
            : 'حفظ التعديلات';
    @endphp
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2 my-4">
                <div>
                    <h5 class="page-title mb-1">
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        تعديل الدرس
                    </h5>
                    <p class="text-muted small mb-0">{{ $lesson->title }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i> معاينة الدرس
                    </a>
                    @if($subject)
                        <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-light btn-sm border">
                            <i class="bi bi-journal-bookmark me-1"></i> صفحة المادة
                        </a>
                    @else
                        <a href="{{ route('admin.review-queue.index') }}" class="btn btn-light btn-sm border">
                            <i class="bi bi-clipboard-check me-1"></i> قائمة المراجعة
                        </a>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('admin.lessons.update', $lesson) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="preserve_linked_units" value="1">
                        <input type="hidden" name="redirect_to_lesson" value="1">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $lesson->title) }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">نوع الفيديو <span class="text-danger">*</span></label>
                                    <select name="video_type" class="form-select @error('video_type') is-invalid @enderror" required>
                                        @foreach(\App\Models\Lesson::VIDEO_TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('video_type', $lesson->video_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('video_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">رابط الفيديو</label>
                            <input type="text" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $lesson->video_url) }}">
                            @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(old('video_type', $lesson->video_type) === 'upload')
                            <div class="mb-3">
                                <label class="form-label">استبدال ملف الفيديو (اختياري)</label>
                                <input type="file" name="video_file" class="form-control @error('video_file') is-invalid @enderror" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                                <small class="text-muted">يظهر عندما يكون نوع الفيديو «رفع مباشر». اتركه فارغاً للاحتفاظ بالملف الحالي.</small>
                                @error('video_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">وصف الدرس</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $lesson->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">الصورة المصغرة</label>
                                    <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                                    @if($lesson->thumbnail)
                                        <small class="text-muted">الصورة الحالية محفوظة؛ اختر ملفاً لاستبدالها.</small>
                                    @endif
                                    @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">مدة الفيديو (ثانية)</label>
                                    <input type="number" name="duration" class="form-control @error('duration') is-invalid @enderror" min="0" value="{{ old('duration', $lesson->duration) }}">
                                    @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">ترتيب العرض</label>
                                    <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" min="0" value="{{ old('order', $lesson->order) }}">
                                    @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">من الصفحة</label>
                                    <input type="number" name="book_page_from" class="form-control @error('book_page_from') is-invalid @enderror" min="1" value="{{ old('book_page_from', $lesson->book_page_from) }}" placeholder="مثال: 10">
                                    @error('book_page_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">إلى الصفحة</label>
                                    <input type="number" name="book_page_to" class="form-control @error('book_page_to') is-invalid @enderror" min="1" value="{{ old('book_page_to', $lesson->book_page_to) }}" placeholder="مثال: 25">
                                    @error('book_page_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-end">
                            @if($user->canReviewContent() || $isTeacherReviewer)
                                <div class="col-md-4 mb-3">
                                    @include('admin.pages.subjects.partials.lesson-review-teacher-fields', [
                                        'mandatoryReview' => $lessonMandatoryReview,
                                        'fieldId' => 'lessonActiveEdit',
                                        'isEdit' => true,
                                        'lesson' => $lesson,
                                    ])
                                </div>
                            @else
                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="lessonActiveEditDefault" {{ old('is_active', $lesson->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lessonActiveEditDefault">الدرس نشط</label>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_free" id="isFreeEdit" {{ old('is_free', $lesson->is_free) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isFreeEdit">درس مجاني</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_preview" id="isPreviewEdit" {{ old('is_preview', $lesson->is_preview) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPreviewEdit">متاح للمعاينة</label>
                                </div>
                            </div>
                        </div>

                        @if($lesson->linkedUnits->isNotEmpty())
                            <p class="small text-muted mb-3">
                                <i class="bi bi-link-45deg me-1"></i>
                                يوجد {{ $lesson->linkedUnits->count() }} ربط إضافي بوحدات أخرى؛ لم يُغيّر من هنا.
                                @if($subject)
                                    لإدارة الربط استخدم <a href="{{ route('admin.subjects.show', $subject) }}">صفحة المادة</a>.
                                @endif
                            </p>
                        @endif

                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> {{ $lessonUpdateButtonLabel }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
