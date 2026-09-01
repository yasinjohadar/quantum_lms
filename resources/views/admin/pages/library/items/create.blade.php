@extends('admin.layouts.master')

@section('page-title')
    إضافة عنصر مكتبة
@stop

@push('styles')
    @include('admin.pages.library.partials.library-styles')
@endpush

@section('content')
    <div class="main-content app-content library-form-page">
        <div class="container-fluid">

            <div class="library-form-hero my-4">
                <div class="library-form-hero__icon">
                    <i class="bi bi-file-earmark-plus-fill"></i>
                </div>
                <div class="library-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.library.items.index') }}">عناصر المكتبة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة عنصر</li>
                        </ol>
                    </nav>
                    <h4 class="library-form-hero__title">إضافة عنصر مكتبة جديد</h4>
                    <p class="library-form-hero__subtitle">ملف أو رابط، مرتبط اختيارياً بصف ومادة معيّنَين</p>
                </div>
                <div class="library-form-hero__actions">
                    <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.library.items.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="library-form-card">
                    <div class="library-form-card__header">
                        <span class="library-form-card__header-icon"><i class="bi bi-info-circle"></i></span>
                        <div>
                            <div class="library-form-card__title">البيانات الأساسية</div>
                            <p class="library-form-card__desc">العنوان، النوع، التصنيف، والوصف</p>
                        </div>
                    </div>
                    <div class="library-form-card__body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" name="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                                    <select name="type" id="libraryItemType" class="form-select @error('type') is-invalid @enderror" required>
                                        @foreach (\App\Models\LibraryItem::TYPES as $value => $label)
                                            <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="library-form-field">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">التصنيف <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                        <option value="">اختر التصنيف</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="library-form-card">
                    <div class="library-form-card__header">
                        <span class="library-form-card__header-icon"><i class="bi bi-mortarboard"></i></span>
                        <div>
                            <div class="library-form-card__title">الربط بصف/مادة (اختياري)</div>
                            <p class="library-form-card__desc">العنصر المرتبط بصف/مادة يظهر فقط للطلاب المسجَّلين فيهما</p>
                        </div>
                    </div>
                    <div class="library-form-card__body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">الصف</label>
                                    <select name="class_id" id="libraryItemClass" class="form-select @error('class_id') is-invalid @enderror">
                                        <option value="">بدون صف محدد</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}" {{ (string) old('class_id') === (string) $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">المادة</label>
                                    <select name="subject_id" id="libraryItemSubject" class="form-select @error('subject_id') is-invalid @enderror">
                                        <option value="">اختر الصف أولاً</option>
                                    </select>
                                    @error('subject_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="library-form-card">
                    <div class="library-form-card__header">
                        <span class="library-form-card__header-icon"><i class="bi bi-paperclip"></i></span>
                        <div>
                            <div class="library-form-card__title">المحتوى</div>
                            <p class="library-form-card__desc">ملف مرفوع أو رابط خارجي، ومستوى الوصول</p>
                        </div>
                    </div>
                    <div class="library-form-card__body">
                        <div class="row g-4">
                            <div class="col-md-6" id="libraryItemFileWrap">
                                <div class="library-form-field">
                                    <label class="form-label">الملف</label>
                                    <input type="file" name="file"
                                           class="form-control @error('file') is-invalid @enderror"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.webm">
                                    @error('file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="library-hint">
                                        <i class="bi bi-info-circle"></i>
                                        <span>الحد الأقصى 50 ميجابايت.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id="libraryItemUrlWrap" style="display:none;">
                                <div class="library-form-field">
                                    <label class="form-label">الرابط الخارجي</label>
                                    <input type="url" name="external_url"
                                           class="form-control @error('external_url') is-invalid @enderror"
                                           placeholder="https://" value="{{ old('external_url') }}">
                                    @error('external_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="library-form-field">
                                    <label class="form-label">مستوى الوصول <span class="text-danger">*</span></label>
                                    <select name="access_level" class="form-select @error('access_level') is-invalid @enderror" required>
                                        @foreach (\App\Models\LibraryItem::ACCESS_LEVELS as $value => $label)
                                            <option value="{{ $value }}" {{ old('access_level', 'public') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('access_level')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1"
                                           {{ old('is_public', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_public">عنصر عام</label>
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">عنصر مميز</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-secondary px-4 me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> حفظ العنصر
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@push('scripts')
<script>
(function () {
    const typeSelect = document.getElementById('libraryItemType');
    const fileWrap = document.getElementById('libraryItemFileWrap');
    const urlWrap = document.getElementById('libraryItemUrlWrap');
    const classSelect = document.getElementById('libraryItemClass');
    const subjectSelect = document.getElementById('libraryItemSubject');
    const subjectsByClassUrl = @json(route('admin.library.items.subjects-by-class'));
    const oldSubjectId = @json(old('subject_id'));

    function toggleFileOrUrl() {
        const isLink = typeSelect.value === 'link';
        fileWrap.style.display = isLink ? 'none' : '';
        urlWrap.style.display = isLink ? '' : 'none';
    }

    function loadSubjects(classId, selectedId) {
        if (!classId) {
            subjectSelect.innerHTML = '<option value="">اختر الصف أولاً</option>';
            return;
        }
        subjectSelect.disabled = true;
        subjectSelect.innerHTML = '<option value="">جاري التحميل…</option>';
        fetch(subjectsByClassUrl + '?class_id=' + encodeURIComponent(classId), {
            headers: { 'Accept': 'application/json' },
        })
            .then((res) => res.json())
            .then((data) => {
                const subjects = (data && data.subjects) || [];
                let html = '<option value="">بدون مادة محددة</option>';
                subjects.forEach((subject) => {
                    const sel = String(subject.id) === String(selectedId) ? 'selected' : '';
                    html += `<option value="${subject.id}" ${sel}>${subject.name}</option>`;
                });
                subjectSelect.innerHTML = html;
            })
            .finally(() => { subjectSelect.disabled = false; });
    }

    typeSelect.addEventListener('change', toggleFileOrUrl);
    classSelect.addEventListener('change', () => loadSubjects(classSelect.value, null));

    toggleFileOrUrl();
    if (classSelect.value) {
        loadSubjects(classSelect.value, oldSubjectId);
    }
})();
</script>
@endpush
