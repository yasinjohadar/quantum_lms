@php
    $fieldId = $fieldId ?? uniqid('lessonAttach');
@endphp

<hr class="my-3">
<h6 class="mb-3">
    <i class="bi bi-paperclip text-info me-1"></i>
    مرفقات الدرس (اختياري)
</h6>

<ul class="nav nav-tabs lesson-create-attachment-tabs" id="lessonCreateAttachmentTabs{{ $fieldId }}" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active lesson-create-attachment-tab"
                id="lessonCreateFilesTab{{ $fieldId }}"
                data-bs-toggle="tab"
                data-bs-target="#lessonCreateFilesPane{{ $fieldId }}"
                data-attachment-mode="files"
                data-field-id="{{ $fieldId }}"
                type="button"
                role="tab">
            <i class="bi bi-files me-1"></i> ملفات
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link lesson-create-attachment-tab"
                id="lessonCreateLinkTab{{ $fieldId }}"
                data-bs-toggle="tab"
                data-bs-target="#lessonCreateLinkPane{{ $fieldId }}"
                data-attachment-mode="link"
                data-field-id="{{ $fieldId }}"
                type="button"
                role="tab">
            <i class="bi bi-link-45deg me-1"></i> رابط
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-3 mb-3 bg-light bg-opacity-25">
    <div class="tab-pane fade show active" id="lessonCreateFilesPane{{ $fieldId }}" role="tabpanel">
        <div class="mb-3">
            <label class="form-label">الملفات</label>
            <input type="file"
                   name="attachment_files[]"
                   class="form-control lesson-create-attachments-input"
                   data-field-id="{{ $fieldId }}"
                   id="lessonCreateFilesInput{{ $fieldId }}"
                   multiple
                   accept="*/*">
            <small class="text-muted d-block mt-1">يمكنك اختيار عدة ملفات بأنواع مختلفة. الحد الأقصى: 20 ملف، 50 ميجابايت لكل ملف.</small>
            <div class="lesson-create-attachments-preview mt-2 text-muted small" id="lessonCreateFilesPreview{{ $fieldId }}" style="display:none;"></div>
        </div>
        <div class="mb-0">
            <label class="form-label">عنوان المرفق (اختياري)</label>
            <input type="text"
                   name="attachment_title"
                   class="form-control lesson-create-attachment-title-files"
                   data-field-id="{{ $fieldId }}"
                   id="lessonCreateTitleFiles{{ $fieldId }}"
                   placeholder="اختياري: يُستخدم عند رفع ملف واحد فقط">
        </div>
    </div>

    <div class="tab-pane fade" id="lessonCreateLinkPane{{ $fieldId }}" role="tabpanel">
        <div class="mb-3">
            <label class="form-label">عنوان المرفق (اختياري)</label>
            <input type="text"
                   name="attachment_title"
                   class="form-control lesson-create-attachment-title-link"
                   data-field-id="{{ $fieldId }}"
                   id="lessonCreateTitleLink{{ $fieldId }}"
                   placeholder="اختياري: سيُستخدم «رابط مرفق» تلقائيًا"
                   disabled>
        </div>
        <div class="mb-0">
            <label class="form-label">الرابط</label>
            <input type="url"
                   name="attachment_url"
                   class="form-control lesson-create-attachment-url"
                   data-field-id="{{ $fieldId }}"
                   id="lessonCreateUrl{{ $fieldId }}"
                   placeholder="https://example.com/resource"
                   disabled>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">وصف المرفقات (اختياري)</label>
    <textarea name="attachment_description" class="form-control" rows="2" placeholder="وصف مختصر للمرفقات..."></textarea>
</div>

<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" name="attachment_is_downloadable" id="lessonCreateAttachmentDownloadable{{ $fieldId }}" checked>
    <label class="form-check-label" for="lessonCreateAttachmentDownloadable{{ $fieldId }}">السماح بالتحميل</label>
</div>
