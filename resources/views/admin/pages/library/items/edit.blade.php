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
                                <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                    <option value="">اختر الصف (اختياري)</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $item->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                <label>الصف (اختياري)</label>
                                @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" {{ old('class_id', $item->class_id) ? '' : 'disabled' }}>
                                    <option value="">عام (غير مرتبط بمادة)</option>
                                    @if(old('class_id', $item->class_id))
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id', $item->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <label>المادة (اختياري)</label>
                                @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="subject-hint" class="text-muted small mt-1" style="{{ old('class_id', $item->class_id) ? 'display: none;' : '' }}">
                                <i class="fas fa-info-circle me-1"></i> يرجى اختيار الصف أولاً لعرض المواد المرتبطة به
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
                        <div class="col-md-12" id="file-upload-section">
                            <label class="form-label">رفع ملف جديد (اختياري)</label>
                            <input type="file" name="file" id="file-input" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.webm">
                            <small class="form-text text-muted">اتركه فارغاً للاحتفاظ بالملف الحالي</small>
                            @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12" id="external-url-section" style="display: none;">
                            <div class="form-floating">
                                <input type="url" name="external_url" id="external-url-input" class="form-control @error('external_url') is-invalid @enderror" placeholder="https://example.com" value="{{ old('external_url', $item->external_url) }}">
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
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const fileSection = document.getElementById('file-upload-section');
        const urlSection = document.getElementById('external-url-section');
        const fileInput = document.getElementById('file-input');
        const urlInput = document.getElementById('external-url-input');
        const classSelect = document.getElementById('class_id');
        const subjectSelect = document.getElementById('subject_id');
        const subjectHint = document.getElementById('subject-hint');
        
        if (!typeSelect || !fileSection || !urlSection) {
            console.error('Missing required elements for library item form toggle');
            return;
        }
        
        function toggleSections() {
            const type = typeSelect.value;
            
            if (type === 'link') {
                fileSection.style.display = 'none';
                urlSection.style.display = 'block';
                
                if (fileInput) {
                    fileInput.removeAttribute('required');
                }
            } else if (type) {
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
            } else {
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
            }
        }
        
        function loadSubjectsByClass(classId, selectedSubjectId = null) {
            if (!classId) {
                // إعادة تعيين حقل المادة
                subjectSelect.innerHTML = '<option value="">عام (غير مرتبط بمادة)</option>';
                subjectSelect.disabled = true;
                subjectHint.style.display = 'block';
                return;
            }
            
            // إظهار loading
            subjectSelect.disabled = true;
            subjectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            subjectHint.style.display = 'none';
            
            // جلب المواد عبر AJAX
            const url = '{{ route("admin.library.items.subjects-by-class") }}?class_id=' + classId;
            console.log('Fetching from URL:', url);
            
            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    if (data.success && data.subjects) {
                        // تحديث خيارات المادة
                        updateSubjectSelect(data.subjects, selectedSubjectId);
                        subjectSelect.disabled = false;
                    } else {
                        subjectSelect.innerHTML = '<option value="">لا توجد مواد متاحة</option>';
                        subjectSelect.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error loading subjects:', error);
                    subjectSelect.innerHTML = '<option value="">حدث خطأ في التحميل</option>';
                    subjectSelect.disabled = false;
                });
        }
        
        function updateSubjectSelect(subjects, selectedSubjectId = null) {
            let html = '<option value="">عام (غير مرتبط بمادة)</option>';
            
            subjects.forEach(subject => {
                const selected = selectedSubjectId && subject.id == selectedSubjectId ? 'selected' : '';
                html += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
            });
            
            subjectSelect.innerHTML = html;
            
            // إذا كان هناك old value، حدده
            const oldSubjectId = '{{ old("subject_id", $item->subject_id) }}';
            if (oldSubjectId && !selectedSubjectId) {
                subjectSelect.value = oldSubjectId;
            }
        }
        
        // عند تغيير الصف
        if (classSelect) {
            classSelect.addEventListener('change', function() {
                loadSubjectsByClass(this.value);
            });
            
            // إذا كان هناك صف محدد حالياً، حمّل المواد تلقائياً
            const currentClassId = {{ old('class_id', $item->class_id) ?: 'null' }};
            const currentSubjectId = {{ old('subject_id', $item->subject_id) ?: 'null' }};
            if (currentClassId) {
                loadSubjectsByClass(currentClassId, currentSubjectId);
            } else {
                // إذا لم يكن هناك صف محدد، اعرض جميع المواد (للتوافق مع البيانات القديمة)
                let html = '<option value="">عام (غير مرتبط بمادة)</option>';
                @if(!old('class_id', $item->class_id))
                    @foreach($subjects as $subject)
                        html += '<option value="{{ $subject->id }}" {{ old("subject_id", $item->subject_id) == $subject->id ? "selected" : "" }}>{{ $subject->name }}</option>';
                    @endforeach
                @endif
                subjectSelect.innerHTML = html;
                subjectSelect.disabled = false;
            }
        }
        
        typeSelect.addEventListener('change', toggleSections);
        toggleSections();
    });
</script>
@endpush

