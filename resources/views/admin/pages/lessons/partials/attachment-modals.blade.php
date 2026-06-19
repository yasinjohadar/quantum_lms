@php
    $modalId = $modalId ?? 'addLessonAttachment' . $lesson->id;
    $returnTo = $returnTo ?? url()->current();
    $headerClass = $headerClass ?? 'bg-info-transparent';
    $submitClass = $submitClass ?? 'btn-info';
    $attachments = $lesson->relationLoaded('attachments')
        ? $lesson->attachments
        : $lesson->attachments()->orderBy('order')->get();
@endphp

@can('lesson-attachment-create')
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 {{ $headerClass }}">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-paperclip text-info me-2"></i>
                    إدارة مرفقات الدرس
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            @if($attachments->isNotEmpty())
                <div class="px-4 pt-3 pb-0">
                    <h6 class="fw-semibold mb-2">
                        <i class="bi bi-list-ul me-1"></i>
                        المرفقات الحالية ({{ $attachments->count() }})
                    </h6>
                    <div class="list-group list-group-flush border rounded mb-3" style="max-height: 200px; overflow-y: auto;">
                        @foreach($attachments as $attachment)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <i class="bi {{ $attachment->type_icon }} text-muted"></i>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $attachment->title }}</div>
                                        <span class="badge bg-{{ $attachment->type === 'link' ? 'info' : 'primary' }}-transparent text-{{ $attachment->type === 'link' ? 'info' : 'primary' }}" style="font-size:0.7rem;">
                                            {{ \App\Models\LessonAttachment::TYPES[$attachment->type] ?? $attachment->type }}
                                        </span>
                                        @if($attachment->formatted_file_size)
                                            <span class="text-muted" style="font-size:0.7rem;">· {{ $attachment->formatted_file_size }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0 ms-2">
                                    @can('lesson-attachment-edit')
                                        <button type="button" class="btn btn-sm btn-icon btn-primary-transparent" data-bs-toggle="modal" data-bs-target="#editAttachment{{ $attachment->id }}" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan
                                    @can('lesson-attachment-delete')
                                        <button type="button" class="btn btn-sm btn-icon btn-danger-transparent" data-bs-toggle="modal" data-bs-target="#deleteAttachment{{ $attachment->id }}" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <ul class="nav nav-tabs px-4 attachment-tabs" id="attachmentTabs{{ $lesson->id }}" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="files-tab-{{ $lesson->id }}" data-bs-toggle="tab" data-bs-target="#files-pane-{{ $lesson->id }}" type="button" role="tab">
                        <i class="bi bi-files me-1"></i> ملفات
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="link-tab-{{ $lesson->id }}" data-bs-toggle="tab" data-bs-target="#link-pane-{{ $lesson->id }}" type="button" role="tab">
                        <i class="bi bi-link-45deg me-1"></i> رابط
                    </button>
                </li>
            </ul>

            <div class="tab-content px-4 pb-2">
                <div class="tab-pane fade show active" id="files-pane-{{ $lesson->id }}" role="tabpanel">
                    <form action="{{ route('admin.lessons.attachments.store', $lesson->id) }}" method="POST" enctype="multipart/form-data" class="pt-3">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ $returnTo }}">

                        <div class="alert alert-light border mb-3 py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>الدرس:</strong> {{ $lesson->title }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الملفات <span class="text-danger">*</span></label>
                            <input type="file" name="files[]" class="form-control attachment-files-input" data-lesson="{{ $lesson->id }}" multiple required>
                            <small class="text-muted d-block mt-1">يمكنك اختيار عدة ملفات بأنواع مختلفة. الحد الأقصى: 20 ملف، 50 ميجابايت لكل ملف.</small>
                            <div class="attachment-files-preview mt-2 text-muted small" id="filesPreview{{ $lesson->id }}" style="display:none;"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">عنوان المرفق (اختياري)</label>
                            <input type="text" name="title" class="form-control" placeholder="اختياري: يُستخدم عند رفع ملف واحد فقط">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">وصف المرفق (اختياري)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للمرفقات..."></textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_downloadable" checked>
                            <label class="form-check-label">السماح بالتحميل</label>
                        </div>

                        <div class="modal-footer border-0 px-0 pb-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn {{ $submitClass }}">
                                <i class="bi bi-upload me-1"></i> رفع الملفات
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="link-pane-{{ $lesson->id }}" role="tabpanel">
                    <form action="{{ route('admin.lessons.attachments.store', $lesson->id) }}" method="POST" class="pt-3">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                        <input type="hidden" name="type" value="link">

                        <div class="alert alert-light border mb-3 py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>الدرس:</strong> {{ $lesson->title }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">عنوان المرفق (اختياري)</label>
                            <input type="text" name="title" class="form-control" placeholder="اختياري: سيُستخدم «رابط مرفق» تلقائيًا">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الرابط <span class="text-danger">*</span></label>
                            <input type="url" name="url" class="form-control" placeholder="https://example.com/resource" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">وصف المرفق (اختياري)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للرابط..."></textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_downloadable" checked>
                            <label class="form-check-label">السماح بالتحميل</label>
                        </div>

                        <div class="modal-footer border-0 px-0 pb-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn {{ $submitClass }}">
                                <i class="bi bi-check-lg me-1"></i> حفظ الرابط
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

@foreach($attachments as $attachment)
    @can('lesson-attachment-edit')
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
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">عنوان المرفق (اختياري)</label>
                            <input type="text" name="title" class="form-control" value="{{ $attachment->title }}" placeholder="اختياري: سيُستخدم اسم الملف تلقائيًا">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">نوع المرفق</label>
                            <select class="form-select" disabled>
                                @foreach(\App\Models\LessonAttachment::TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ $attachment->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
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
    @endcan

    @can('lesson-attachment-delete')
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
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
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
    @endcan
@endforeach
