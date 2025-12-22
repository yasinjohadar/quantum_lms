@extends('admin.layouts.master')

@section('page-title')
    تعديل عنصر
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">تعديل عنصر: {{ $item->title }}</h5>
            <a href="{{ route('admin.library.items.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
            </a>
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

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.library.items.update', $item->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="عنوان العنصر" value="{{ old('title', $item->title) }}" required>
                                <label>العنوان <span class="text-danger">*</span></label>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach(\App\Models\LibraryItem::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $item->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <label>نوع العنصر <span class="text-danger">*</span></label>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" placeholder="الوصف" style="height: 100px;">{{ old('description', $item->description) }}</textarea>
                                <label>الوصف</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label>التصنيف <span class="text-danger">*</span></label>
                                @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
                                    <option value="">عام (غير مرتبط بمادة)</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $item->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                <label>المادة (اختياري)</label>
                                @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <h6 class="text-primary mb-3">الملف أو الرابط</h6>
                            @if($item->file_path)
                                <div class="alert alert-info">
                                    <strong>الملف الحالي:</strong> {{ $item->file_name }} 
                                    <a href="{{ route('admin.library.items.download', $item->id) }}" class="btn btn-sm btn-primary ms-2">تحميل</a>
                                </div>
                            @endif
                            @if($item->external_url)
                                <div class="alert alert-info">
                                    <strong>الرابط الحالي:</strong> <a href="{{ $item->external_url }}" target="_blank">{{ $item->external_url }}</a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6" id="file-upload-section">
                            <label class="form-label">رفع ملف جديد (اختياري)</label>
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.webm">
                            <small class="form-text text-muted">اتركه فارغاً للاحتفاظ بالملف الحالي</small>
                            @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" id="external-url-section" style="display: none;">
                            <div class="form-floating">
                                <input type="url" name="external_url" class="form-control @error('external_url') is-invalid @enderror" placeholder="الرابط الخارجي" value="{{ old('external_url', $item->external_url) }}">
                                <label>الرابط الخارجي</label>
                                @error('external_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <h6 class="text-primary mb-3">إعدادات الوصول</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="access_level" class="form-select @error('access_level') is-invalid @enderror" required>
                                    @foreach(\App\Models\LibraryItem::ACCESS_LEVELS as $key => $label)
                                        <option value="{{ $key }}" {{ old('access_level', $item->access_level) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <label>مستوى الوصول <span class="text-danger">*</span></label>
                                @error('access_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public', $item->is_public) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">عام (يظهر في المكتبة العامة)</label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $item->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">مميز</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <h6 class="text-primary mb-3">الوسوم</h6>
                            <select name="tags[]" class="form-select" multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $item->tags->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">اضغط Ctrl (أو Cmd على Mac) لتحديد عدة وسوم</small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ التغييرات
                            </button>
                            <a href="{{ route('admin.library.items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const fileSection = document.getElementById('file-upload-section');
        const urlSection = document.getElementById('external-url-section');
        
        if (type === 'link') {
            fileSection.style.display = 'none';
            urlSection.style.display = 'block';
        } else {
            fileSection.style.display = 'block';
            urlSection.style.display = 'none';
        }
    });
    
    // Trigger on page load
    document.getElementById('type').dispatchEvent(new Event('change'));
</script>
@endpush
@stop

