@extends('admin.layouts.master')

@section('page-title')
    معاينة الدرس - {{ $lesson->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            {{-- رسائل النجاح --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            {{-- رسائل الأخطاء العامة --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <div>
                    <h5 class="page-title mb-1">
                        <i class="bi bi-play-circle text-success me-2"></i>
                        {{ $lesson->title }}
                    </h5>
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.subjects.index') }}">المواد الدراسية</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.subjects.show', $lesson->unit->section->subject_id) }}">
                                {{ $lesson->unit->section->subject->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">معاينة الدرس</li>
                    </ol>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.subjects.show', $lesson->unit->section->subject_id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للمادة
                    </a>
                </div>
            </div>

            <div class="row g-3">
                {{-- مشغل الفيديو --}}
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-body">
                            @if ($lesson->embed_url)
                                @php
                                    $actualType = $lesson->actual_video_type;
                                @endphp
                                <div class="ratio ratio-16x9 mb-3 bg-dark rounded overflow-hidden">
                                    @if($actualType === 'youtube')
                                        <iframe
                                            src="{{ $lesson->embed_url }}?rel=0&modestbranding=1"
                                            title="{{ $lesson->title }}"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen
                                            loading="lazy"
                                        ></iframe>
                                    @elseif($actualType === 'vimeo')
                                        <iframe
                                            src="{{ $lesson->embed_url }}?title=0&byline=0&portrait=0"
                                            title="{{ $lesson->title }}"
                                            frameborder="0"
                                            allow="autoplay; fullscreen; picture-in-picture"
                                            allowfullscreen
                                            loading="lazy"
                                        ></iframe>
                                    @elseif($actualType === 'upload')
                                        <video controls class="w-100 h-100" 
                                               poster="{{ $lesson->thumbnail ? asset('storage/'.$lesson->thumbnail) : '' }}"
                                               controlsList="nodownload">
                                            <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                            <source src="{{ $lesson->embed_url }}" type="video/webm">
                                            <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                            المتصفح لا يدعم تشغيل الفيديو.
                                        </video>
                                    @else
                                        {{-- رابط خارجي - نحاول تشغيله كفيديو --}}
                                        <video controls class="w-100 h-100" 
                                               poster="{{ $lesson->thumbnail ? asset('storage/'.$lesson->thumbnail) : '' }}">
                                            <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                            المتصفح لا يدعم تشغيل الفيديو.
                                        </video>
                                    @endif
                                </div>
                            @elseif($lesson->video_url)
                                {{-- لو الرابط موجود لكن embed_url فارغ --}}
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>تحذير:</strong> تعذر تشغيل الفيديو. تأكد من صحة الرابط.
                                    <br><small class="text-muted">الرابط المدخل: {{ $lesson->video_url }}</small>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted bg-light rounded">
                                    <i class="bi bi-collection-play display-5 d-block mb-2"></i>
                                    <p class="mb-1">لم يتم ضبط فيديو لهذا الدرس بعد.</p>
                                    <p class="small mb-0">يمكنك إضافة رابط أو رفع فيديو من نموذج تعديل الدرس.</p>
                                </div>
                            @endif

                            @if($lesson->description)
                                <h6 class="mt-3 mb-2">وصف الدرس</h6>
                                <p class="text-muted mb-0">{{ $lesson->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- معلومات الدرس والمرفقات --}}
                <div class="col-xl-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الدرس</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <span class="fw-semibold">المادة:</span>
                                <span class="text-muted">
                                    {{ $lesson->unit->section->subject->name }}
                                </span>
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">الوحدة:</span>
                                <span class="text-muted">
                                    {{ $lesson->unit->title }}
                                </span>
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">القسم:</span>
                                <span class="text-muted">
                                    {{ $lesson->unit->section->title }}
                                </span>
                            </p>
                            <p class="mb-2">
                                <span class="fw-semibold">نوع الفيديو:</span>
                                <span class="badge bg-primary-transparent text-primary">
                                    {{ \App\Models\Lesson::VIDEO_TYPES[$lesson->video_type] ?? $lesson->video_type }}
                                </span>
                            </p>
                            @if($lesson->formatted_duration)
                                <p class="mb-2">
                                    <span class="fw-semibold">المدة:</span>
                                    <span class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $lesson->formatted_duration }}
                                    </span>
                                </p>
                            @endif
                            <p class="mb-2">
                                <span class="fw-semibold">الحالة:</span>
                                @if($lesson->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">مخفي</span>
                                @endif
                            </p>
                            <p class="mb-0">
                                @if($lesson->is_free)
                                    <span class="badge bg-success-transparent text-success me-1">
                                        <i class="bi bi-unlock me-1"></i>درس مجاني
                                    </span>
                                @endif
                                @if($lesson->is_preview)
                                    <span class="badge bg-info-transparent text-info">
                                        <i class="bi bi-eye me-1"></i>متاح للمعاينة
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-paperclip me-2"></i>
                                مرفقات الدرس ({{ $lesson->attachments->count() }})
                            </h6>
                            <button type="button" class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addAttachmentModal">
                                <i class="bi bi-plus-lg me-1"></i> إضافة مرفق
                            </button>
                        </div>
                        <div class="card-body p-0">
                            @if($lesson->attachments->count() === 0)
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-folder2-open display-6 d-block mb-2"></i>
                                    <p class="mb-1">لا توجد مرفقات لهذا الدرس حالياً</p>
                                    <small>اضغط على "إضافة مرفق" لإضافة ملفات أو روابط</small>
                                </div>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($lesson->attachments as $attachment)
                                        <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-md bg-{{ $attachment->type === 'link' ? 'info' : 'primary' }}-transparent text-{{ $attachment->type === 'link' ? 'info' : 'primary' }} rounded me-3">
                                                    <i class="bi {{ $attachment->type_icon }} fs-5"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-semibold">{{ $attachment->title }}</div>
                                                    <div class="text-muted small">
                                                        <span class="badge bg-light text-muted border me-1">
                                                            {{ \App\Models\LessonAttachment::TYPES[$attachment->type] ?? $attachment->type }}
                                                        </span>
                                                        @if($attachment->formatted_file_size)
                                                            <span class="me-1">{{ $attachment->formatted_file_size }}</span>
                                                        @endif
                                                        @if($attachment->is_downloadable)
                                                            <i class="bi bi-download text-success" title="قابل للتحميل"></i>
                                                        @endif
                                                    </div>
                                                    @if($attachment->description)
                                                        <small class="text-muted d-block mt-1">{{ Str::limit($attachment->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                @if($attachment->access_url)
                                                    <a href="{{ $attachment->access_url }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-success"
                                                       title="{{ $attachment->type === 'link' ? 'فتح الرابط' : 'تحميل الملف' }}">
                                                        <i class="bi bi-{{ $attachment->type === 'link' ? 'box-arrow-up-right' : 'download' }}"></i>
                                                    </a>
                                                @endif
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editAttachment{{ $attachment->id }}"
                                                        title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteAttachment{{ $attachment->id }}"
                                                        title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال إضافة مرفق --}}
    <div class="modal fade" id="addAttachmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 bg-primary-transparent">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-paperclip text-primary me-2"></i>
                        إضافة مرفق جديد
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form action="{{ route('admin.lessons.attachments.store', $lesson->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">عنوان المرفق <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="مثال: ملف PDF للشرح" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">نوع المرفق <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="attachmentType" required>
                                <option value="file">ملف (PDF, Word, ZIP...)</option>
                                <option value="document">مستند</option>
                                <option value="image">صورة</option>
                                <option value="audio">ملف صوتي</option>
                                <option value="link">رابط خارجي</option>
                            </select>
                        </div>

                        <div class="mb-3" id="fileUploadField">
                            <label class="form-label">الملف</label>
                            <input type="file" name="file" class="form-control" id="attachmentFile">
                            <small class="text-muted">الحد الأقصى: 50 ميجابايت</small>
                        </div>

                        <div class="mb-3" id="urlField" style="display: none;">
                            <label class="form-label">الرابط <span class="text-danger">*</span></label>
                            <input type="url" name="url" class="form-control" id="attachmentUrl" placeholder="https://example.com/resource">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">وصف المرفق (اختياري)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للمرفق..."></textarea>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_downloadable" id="isDownloadable" checked>
                            <label class="form-check-label" for="isDownloadable">
                                السماح بالتحميل
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ المرفق
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودالات تعديل وحذف المرفقات --}}
    @foreach($lesson->attachments as $attachment)
        {{-- مودال تعديل المرفق --}}
        <div class="modal fade" id="editAttachment{{ $attachment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0 bg-primary-transparent">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil text-primary me-2"></i>
                            تعديل المرفق
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form action="{{ route('admin.attachments.update', $attachment->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">عنوان المرفق <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $attachment->title }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نوع المرفق</label>
                                <select name="type" class="form-select" disabled>
                                    @foreach(\App\Models\LessonAttachment::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ $attachment->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="type" value="{{ $attachment->type }}">
                            </div>

                            @if($attachment->type === 'link')
                                <div class="mb-3">
                                    <label class="form-label">الرابط</label>
                                    <input type="url" name="url" class="form-control" value="{{ $attachment->url }}">
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label">استبدال الملف (اختياري)</label>
                                    <input type="file" name="file" class="form-control">
                                    <small class="text-muted">اترك فارغاً للاحتفاظ بالملف الحالي</small>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">وصف المرفق</label>
                                <textarea name="description" class="form-control" rows="2">{{ $attachment->description }}</textarea>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_downloadable" {{ $attachment->is_downloadable ? 'checked' : '' }}>
                                <label class="form-check-label">السماح بالتحميل</label>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- مودال حذف المرفق --}}
        <div class="modal fade" id="deleteAttachment{{ $attachment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="border-0 text-center pt-4 px-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <span class="me-2 fs-4 text-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </span>
                            <h5 class="modal-title mb-0 fw-bold">حذف المرفق</h5>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="text-center mt-2">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm" style="width:80px;height:80px;">
                            <i class="bi bi-trash fs-2"></i>
                        </div>
                    </div>
                    <form action="{{ route('admin.attachments.destroy', $attachment->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-center pt-0 pb-3 px-4">
                            <p class="mb-1 text-muted">هل أنت متأكد من حذف المرفق:</p>
                            <p class="fw-bold mb-1" style="font-size:1.05rem;">{{ $attachment->title }}</p>
                            <p class="text-muted small mb-0">
                                <span class="badge bg-{{ $attachment->type === 'link' ? 'info' : 'primary' }}-transparent text-{{ $attachment->type === 'link' ? 'info' : 'primary' }}">
                                    {{ \App\Models\LessonAttachment::TYPES[$attachment->type] ?? $attachment->type }}
                                </span>
                            </p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-trash me-1"></i> حذف المرفق
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // التبديل بين حقل الملف وحقل الرابط
    var selectType = document.getElementById('attachmentType');
    var divFile = document.getElementById('fileUploadField');
    var divUrl = document.getElementById('urlField');

    if (selectType && divFile && divUrl) {
        selectType.onchange = function() {
            if (this.value === 'link') {
                divFile.style.display = 'none';
                divUrl.style.display = 'block';
            } else {
                divFile.style.display = 'block';
                divUrl.style.display = 'none';
            }
        };
    }
});
</script>
@stop


