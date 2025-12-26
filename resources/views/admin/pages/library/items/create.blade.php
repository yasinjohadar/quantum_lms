@extends('admin.layouts.master')

@section('page-title')
    إضافة عنصر جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">إضافة عنصر جديد</h5>
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
                <form method="POST" action="{{ route('admin.library.items.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="عنوان العنصر" value="{{ old('title') }}" required>
                                <label>العنوان <span class="text-danger">*</span></label>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">اختر النوع</option>
                                    @foreach(\App\Models\LibraryItem::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" placeholder="الوصف" style="height: 100px;">{{ old('description') }}</textarea>
                                <label>الوصف</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">اختر التصنيف</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
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
                        </div>
                        <div class="col-md-12" id="file-upload-section">
                            <label class="form-label">رفع ملف <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file-input" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.webm">
                            <small class="form-text text-muted">الحد الأقصى: 50 ميجابايت</small>
                            @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12" id="external-url-section" style="display: none;">
                            <div class="form-floating">
                                <input type="url" name="external_url" id="external-url-input" class="form-control @error('external_url') is-invalid @enderror" placeholder="https://example.com" value="{{ old('external_url') }}">
                                <label>الرابط الخارجي <span class="text-danger">*</span></label>
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
                                        <option value="{{ $key }}" {{ old('access_level', 'public') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">عام (يظهر في المكتبة العامة)</label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">مميز</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <h6 class="text-primary mb-3">الوسوم (اختياري)</h6>
                            <select name="tags[]" class="form-select" multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">اضغط Ctrl (أو Cmd على Mac) لتحديد عدة وسوم</small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ
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

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const fileSection = document.getElementById('file-upload-section');
        const urlSection = document.getElementById('external-url-section');
        const fileInput = document.getElementById('file-input');
        const urlInput = document.getElementById('external-url-input');
        
        if (!typeSelect || !fileSection || !urlSection) {
            console.error('Missing required elements for library item form toggle');
            return;
        }
        
        function toggleSections() {
            const type = typeSelect.value;
            
            console.log('Type changed to:', type); // للتحقق
            
            if (type === 'link') {
                // إظهار حقل الرابط وإخفاء حقل الملف
                fileSection.style.display = 'none';
                urlSection.style.display = 'block';
                
                // إزالة required من الملف وإضافته للرابط
                if (fileInput) {
                    fileInput.removeAttribute('required');
                    fileInput.value = ''; // مسح قيمة الملف
                }
                if (urlInput) {
                    urlInput.setAttribute('required', 'required');
                }
            } else if (type) {
                // إظهار حقل الملف وإخفاء حقل الرابط
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
                
                // إضافة required للملف وإزالته من الرابط
                if (fileInput) {
                    fileInput.setAttribute('required', 'required');
                }
                if (urlInput) {
                    urlInput.removeAttribute('required');
                    urlInput.value = ''; // مسح قيمة الرابط
                }
            } else {
                // إذا لم يتم اختيار نوع، إخفاء كلا الحقلين
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
                if (fileInput) fileInput.removeAttribute('required');
                if (urlInput) urlInput.removeAttribute('required');
            }
        }
        
        // إضافة event listener عند تغيير النوع
        typeSelect.addEventListener('change', toggleSections);
        
        // تنفيذ عند تحميل الصفحة
        toggleSections();
    });
</script>
@stop
@stop


                if (urlInput) {
                    urlInput.setAttribute('required', 'required');
                }
            } else if (type) {
                // إظهار حقل الملف وإخفاء حقل الرابط
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
                
                // إضافة required للملف وإزالته من الرابط
                if (fileInput) {
                    fileInput.setAttribute('required', 'required');
                }
                if (urlInput) {
                    urlInput.removeAttribute('required');
                    urlInput.value = ''; // مسح قيمة الرابط
                }
            } else {
                // إذا لم يتم اختيار نوع، إخفاء كلا الحقلين
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
                if (fileInput) fileInput.removeAttribute('required');
                if (urlInput) urlInput.removeAttribute('required');
            }
        }
        
        // إضافة event listener عند تغيير النوع
        typeSelect.addEventListener('change', toggleSections);
        
        // تنفيذ عند تحميل الصفحة
        toggleSections();
    });
</script>
@stop
@stop

