@extends('admin.layouts.master')

@section('page-title')
    معاينة الدرس - {{ $lesson->title }}
@stop

@push('styles')
    @include('admin.pages.lessons.partials.show-styles')
@endpush

@section('content')
    @php
        $lessonSubject = $lesson->unit?->section?->subject ?? $lesson->section?->subject;
        $lessonSection = $lesson->unit?->section ?? $lesson->section;
        $user = auth()->user();
        $isTeacher = $user->shouldSubmitContentForReview();
        $statusChipClass = match ($lesson->review_status) {
            \App\Models\Lesson::REVIEW_STATUS_PENDING => 'ls-chip--status-pending',
            \App\Models\Lesson::REVIEW_STATUS_APPROVED => 'ls-chip--status-approved',
            \App\Models\Lesson::REVIEW_STATUS_REJECTED => 'ls-chip--status-rejected',
            default => 'ls-chip--status-draft',
        };
        $reviewBadgeClass = match ($lesson->review_status) {
            \App\Models\Lesson::REVIEW_STATUS_PENDING => 'ls-review-badge--warning',
            \App\Models\Lesson::REVIEW_STATUS_APPROVED => 'ls-review-badge--success',
            \App\Models\Lesson::REVIEW_STATUS_REJECTED => 'ls-review-badge--danger',
            default => 'ls-review-badge--secondary',
        };
    @endphp
    <div class="main-content app-content lesson-show-page">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if(!$lessonSubject)
                <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    تعذّر ربط هذا الدرس بمادة حالية (القسم أو المادة المرتبطة قد تكون محذوفة). يمكنك معاينته والموافقة عليه، لكن يُفضّل إعادة ربطه من صفحة المادة.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="ls-hero my-4">
                <div class="ls-hero__icon">
                    <i class="bi bi-play-circle-fill"></i>
                </div>
                <div class="ls-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد الدراسية</a></li>
                            @if($lessonSubject)
                                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.show', $lessonSubject->id) }}">{{ $lessonSubject->name }}</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">معاينة الدرس</li>
                        </ol>
                    </nav>
                    <h1 class="ls-hero__title">{{ $lesson->title }}</h1>
                    <div class="ls-hero__chips">
                        <span class="ls-chip {{ $statusChipClass }}">
                            <i class="bi bi-clipboard-check"></i> {{ $lesson->review_status_name }}
                        </span>
                        @if($lesson->formatted_duration)
                            <span class="ls-chip"><i class="bi bi-clock"></i> {{ $lesson->formatted_duration }}</span>
                        @endif
                        @if($lesson->book_page_from !== null || $lesson->book_page_to !== null)
                            <span class="ls-chip"><i class="bi bi-book"></i>
                                @if($lesson->book_page_from !== null && $lesson->book_page_to !== null)
                                    ص {{ $lesson->book_page_from }}–{{ $lesson->book_page_to }}
                                @elseif($lesson->book_page_from !== null)
                                    من ص {{ $lesson->book_page_from }}
                                @else
                                    إلى ص {{ $lesson->book_page_to }}
                                @endif
                            </span>
                        @endif
                        <span class="ls-chip">
                            <i class="bi bi-camera-video"></i>
                            {{ \App\Models\Lesson::VIDEO_TYPES[$lesson->video_type] ?? $lesson->video_type }}
                        </span>
                        @if($lesson->is_active)
                            <span class="ls-chip" style="color:#059669;border-color:rgba(5,150,105,.3);"><i class="bi bi-eye"></i> نشط</span>
                        @else
                            <span class="ls-chip"><i class="bi bi-eye-slash"></i> مخفي</span>
                        @endif
                    </div>
                </div>
                <div class="ls-hero__actions">
                    @can('lesson-edit')
                        <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> تعديل الدرس
                        </a>
                    @endcan
                    @if($lessonSubject)
                        <a href="{{ route('admin.subjects.show', $lessonSubject->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i> رجوع للمادة
                        </a>
                    @else
                        <a href="{{ route('admin.review-queue.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-clipboard2-check me-1"></i> قائمة المراجعة
                        </a>
                    @endif
                </div>
            </div>

            @if(!$isTeacher && $lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_PENDING)
                <div class="ls-review-bar">
                    <div class="ls-review-bar__text">
                        <i class="bi bi-hourglass-split me-1"></i>
                        هذا الدرس بانتظار مراجعتك — شاهد المحتوى ثم اتخذ قرار النشر
                    </div>
                    <div class="ls-review-bar__actions">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#lessonApproveModal">
                            <i class="bi bi-check-circle me-1"></i> الموافقة على النشر
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#lessonRejectModal">
                            <i class="bi bi-x-circle me-1"></i> رفض النشر
                        </button>
                    </div>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="ls-card">
                        <div class="ls-card__body">
                            @if ($lesson->embed_url)
                                @php
                                    $actualType = $lesson->actual_video_type;
                                @endphp
                                <div class="ls-player ratio ratio-16x9">
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
                                               poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}"
                                               controlsList="nodownload">
                                            <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                            <source src="{{ $lesson->embed_url }}" type="video/webm">
                                            <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                            المتصفح لا يدعم تشغيل الفيديو.
                                        </video>
                                    @else
                                        {{-- رابط خارجي - نحاول تشغيله كفيديو --}}
                                        <video controls class="w-100 h-100" 
                                               poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}">
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
                                <div class="ls-empty-video">
                                    <i class="bi bi-collection-play"></i>
                                    <p class="mb-1 fw-semibold">لم يتم ضبط فيديو لهذا الدرس بعد</p>
                                    <p class="small mb-0">يمكنك إضافة رابط أو رفع فيديو من نموذج تعديل الدرس.</p>
                                </div>
                            @endif

                            @if($lesson->description)
                                <div class="ls-desc mt-3">
                                    <div class="ls-desc__title">وصف الدرس</div>
                                    <p class="mb-0 text-muted">{{ $lesson->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 d-flex flex-column gap-4">
                    <div class="ls-card">
                        <div class="ls-card__header">
                            <span><span class="ls-card__header-icon"><i class="bi bi-info-circle"></i></span>معلومات الدرس</span>
                        </div>
                        <div class="ls-card__body">
                            <div class="ls-meta-list">
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-book"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">المادة</div>
                                        <div class="ls-meta-item__value">{{ $lessonSubject?->name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-layers"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">الوحدة</div>
                                        <div class="ls-meta-item__value text-muted">{{ $lesson->unit->title ?? '— (مباشر في القسم)' }}</div>
                                    </div>
                                </div>
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-folder2"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">القسم</div>
                                        <div class="ls-meta-item__value text-muted">{{ $lessonSection?->title ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-journal-text"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">صفحات الكتاب</div>
                                        <div class="ls-meta-item__value text-muted">
                                            @if($lesson->book_page_from !== null && $lesson->book_page_to !== null)
                                                من {{ $lesson->book_page_from }} إلى {{ $lesson->book_page_to }}
                                            @elseif($lesson->book_page_from !== null)
                                                من صفحة {{ $lesson->book_page_from }}
                                            @elseif($lesson->book_page_to !== null)
                                                إلى صفحة {{ $lesson->book_page_to }}
                                            @else
                                                لم يُحدد نطاق
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-calculator"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">صفحات الإحصائيات</div>
                                        <div class="ls-meta-item__value">{{ \App\Services\TeacherProgressService::lessonPageCount($lesson) }}</div>
                                    </div>
                                </div>
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-sort-numeric-down"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">ترتيب العرض · المعرّف</div>
                                        <div class="ls-meta-item__value text-muted">{{ (int) ($lesson->order ?? 0) }} · #{{ $lesson->id }}</div>
                                    </div>
                                </div>
                                @if($lesson->video_url)
                                    <div class="ls-meta-item">
                                        <span class="ls-meta-item__icon"><i class="bi bi-link-45deg"></i></span>
                                        <div>
                                            <div class="ls-meta-item__label">رابط الفيديو</div>
                                            <div class="ls-meta-item__value">
                                                <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener noreferrer" class="small">
                                                    {{ \Illuminate\Support\Str::limit($lesson->video_url, 80) }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="ls-meta-item">
                                    <span class="ls-meta-item__icon"><i class="bi bi-sliders"></i></span>
                                    <div>
                                        <div class="ls-meta-item__label">الظهور</div>
                                        <div class="ls-meta-item__value">
                                            @if($lesson->is_free)<span class="ls-chip me-1"><i class="bi bi-unlock"></i> مجاني</span>@endif
                                            @if($lesson->is_preview)<span class="ls-chip me-1"><i class="bi bi-eye"></i> معاينة</span>@endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ls-card">
                        <div class="ls-card__header">
                            <span><span class="ls-card__header-icon"><i class="bi bi-clipboard-check"></i></span>حالة المراجعة</span>
                        </div>
                        <div class="ls-card__body">
                            <div class="ls-review-status">
                                <span class="ls-review-badge {{ $reviewBadgeClass }}">
                                    <i class="bi bi-shield-check"></i>
                                    {{ $lesson->review_status_name }}
                                </span>
                            </div>
                            <div class="ls-timeline">
                                <div class="ls-timeline__item">
                                    <span class="ls-timeline__dot"></span>
                                    <span class="ls-timeline__label">الإرسال للمراجعة</span>
                                    <span class="ls-timeline__value">{{ $lesson->submitted_for_review_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                </div>
                                <div class="ls-timeline__item">
                                    <span class="ls-timeline__dot" style="background:var(--ls-accent-2);"></span>
                                    <span class="ls-timeline__label">تاريخ الاعتماد</span>
                                    <span class="ls-timeline__value">{{ $lesson->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                </div>
                                <div class="ls-timeline__item">
                                    <span class="ls-timeline__dot" style="background:#94a3b8;"></span>
                                    <span class="ls-timeline__label">مراجع من</span>
                                    <span class="ls-timeline__value">{{ $lesson->reviewed_at ? ($lesson->reviewer->name ?? 'غير معروف') : '—' }}</span>
                                </div>
                            </div>
                            @if($lesson->review_notes)
                                <div class="alert alert-{{ $lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_REJECTED ? 'danger' : 'info' }} mt-3 mb-0 py-2 small">
                                    <strong class="d-block mb-1">ملاحظات المراجعة</strong>
                                    {{ $lesson->review_notes }}
                                </div>
                            @endif
                            @if(!$isTeacher && $lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_PENDING)
                                <div class="ls-review-actions d-xl-none">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#lessonApproveModal">
                                        <i class="bi bi-check-circle me-1"></i> الموافقة على النشر
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#lessonRejectModal">
                                        <i class="bi bi-x-circle me-1"></i> رفض النشر
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="ls-card">
                        <div class="ls-card__header">
                            <span><span class="ls-card__header-icon"><i class="bi bi-paperclip"></i></span>مرفقات ({{ $lesson->attachments->count() }})</span>
                            @can('lesson-attachment-create')
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAttachmentModal">
                                    <i class="bi bi-plus-lg me-1"></i> إضافة
                                </button>
                            @endcan
                        </div>
                        <div class="ls-card__body">
                            @if($lesson->attachments->count() === 0)
                                <div class="ls-empty py-3">
                                    <i class="bi bi-folder2-open"></i>
                                    <p class="mb-0 small">لا توجد مرفقات حالياً</p>
                                </div>
                            @else
                                @foreach($lesson->attachments as $attachment)
                                    <div class="ls-attachment">
                                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                                            <span class="ls-attachment__icon {{ $attachment->type === 'link' ? 'ls-attachment__icon--link' : 'ls-attachment__icon--file' }}">
                                                <i class="bi {{ $attachment->type_icon }}"></i>
                                            </span>
                                            <div class="min-width-0">
                                                <div class="fw-semibold text-truncate">{{ $attachment->title }}</div>
                                                <div class="text-muted small">
                                                    {{ \App\Models\LessonAttachment::TYPES[$attachment->type] ?? $attachment->type }}
                                                    @if($attachment->formatted_file_size) · {{ $attachment->formatted_file_size }} @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            @if($attachment->access_url)
                                                <a href="{{ $attachment->access_url }}" target="_blank" class="btn btn-sm btn-outline-success" title="فتح">
                                                    <i class="bi bi-{{ $attachment->type === 'link' ? 'box-arrow-up-right' : 'download' }}"></i>
                                                </a>
                                            @endif
                                            @can('lesson-attachment-edit')
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAttachment{{ $attachment->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endcan
                                            @can('lesson-attachment-delete')
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAttachment{{ $attachment->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="ls-card mt-4">
                <div class="ls-card__header">
                    <span><span class="ls-card__header-icon"><i class="bi bi-people"></i></span>متابعة مشاهدات الطلاب</span>
                </div>
                <div class="ls-card__body p-0">
                    @if(isset($lessonCompletions) && $lessonCompletions->isNotEmpty())
                        <div class="ls-table-wrap border-0 rounded-0">
                            <div class="table-responsive">
                                <table class="table ls-table mb-0">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>الحالة</th>
                                        <th>نسبة المشاهدة</th>
                                        <th>وقت المشاهدة</th>
                                        <th>آخر موضع</th>
                                        <th>آخر تحديث</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lessonCompletions as $lc)
                                        <tr>
                                            <td>
                                                @if($lc->user)
                                                    <span class="fw-semibold">{{ $lc->user->name }}</span>
                                                    <br><small class="text-muted">{{ $lc->user->email }}</small>
                                                @else
                                                —
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $lc->status === 'completed' ? 'success' : 'info' }}">
                                                    {{ \App\Models\LessonCompletion::STATUSES[$lc->status] ?? $lc->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($lc->progress_percentage !== null)
                                                    {{ number_format((float) $lc->progress_percentage, 1) }}%
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($lc->time_spent !== null)
                                                    {{ \App\Models\LessonCompletion::formatDurationSeconds((int) $lc->time_spent) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($lc->last_position !== null)
                                                    {{ \App\Models\LessonCompletion::formatDurationSeconds((int) $lc->last_position) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($lc->updated_at)
                                                    {{ $lc->updated_at->format('Y-m-d H:i') }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="ls-empty py-4">
                            <i class="bi bi-person-video3"></i>
                            <p class="mb-0">لا توجد مشاهدات مسجلة لهذا الدرس بعد.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- مودال الموافقة على الدرس --}}
    @if(!$isTeacher && $lesson->review_status === \App\Models\Lesson::REVIEW_STATUS_PENDING)
        <div class="modal fade" id="lessonApproveModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.lessons.approve-review', $lesson->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">الموافقة على نشر الدرس</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ملاحظات (اختياري)</label>
                                <textarea name="review_notes" class="form-control" rows="3"
                                          placeholder="أضف ملاحظات للمعلم..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-success">الموافقة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- مودال رفض الدرس --}}
        <div class="modal fade" id="lessonRejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.lessons.reject-review', $lesson->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">رفض نشر الدرس</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ملاحظات <span class="text-danger">*</span></label>
                                <textarea name="review_notes" class="form-control" rows="3"
                                          placeholder="أضف ملاحظات توضح سبب الرفض للمعلم..." required></textarea>
                                <small class="text-muted">يجب إضافة ملاحظات توضح سبب الرفض</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger">رفض</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال إضافة مرفق --}}
    @can('lesson-attachment-create')
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
                    <input type="hidden" name="return_to" value="{{ url()->current() }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">عنوان المرفق (اختياري)</label>
                            <input type="text" name="title" class="form-control" placeholder="اختياري: سيُستخدم اسم الملف تلقائيًا">
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
    @endcan

    {{-- مودالات تعديل وحذف المرفقات --}}
    @foreach($lesson->attachments as $attachment)
        @can('lesson-attachment-edit')
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
                        <input type="hidden" name="return_to" value="{{ url()->current() }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">عنوان المرفق (اختياري)</label>
                                <input type="text" name="title" class="form-control" value="{{ $attachment->title }}" placeholder="اختياري: سيُستخدم اسم الملف تلقائيًا">
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
        @endcan

        @can('lesson-attachment-delete')
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
                        <input type="hidden" name="return_to" value="{{ url()->current() }}">
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


