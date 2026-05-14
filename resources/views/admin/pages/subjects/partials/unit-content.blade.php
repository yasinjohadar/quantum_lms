{{-- شريط أدوات الوحدة --}}
<div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @can('lesson-create')
        <button type="button" class="btn btn-sm btn-success"
                data-bs-toggle="modal"
                data-bs-target="#createLessonModal{{ $unit->id }}"
                title="إضافة درس">
            <i class="bi bi-play-circle me-1"></i> درس جديد
        </button>
        @endcan
        @can('quiz-create')
        <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id, 'unit_id' => $unit->id, 'scope' => 'unit']) }}" class="btn btn-sm btn-info" title="إضافة اختبار للوحدة">
            <i class="bi bi-clipboard-check me-1"></i> اختبار الوحدة
        </a>
        @endcan
    </div>
</div>

@php
    $unitQuizzesList = $unit->allUnitQuizzes();
@endphp
@if($unitQuizzesList->count() > 0)
<div class="unit-quizzes mb-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 text-info fw-semibold small">
            <i class="bi bi-clipboard-check me-1"></i>
            اختبارات الوحدة ({{ $unitQuizzesList->count() }})
        </h6>
    </div>
    <div class="list-group list-group-flush">
        @foreach($unitQuizzesList as $quiz)
        <div class="list-group-item d-flex align-items-center justify-content-between px-2 py-2 bg-info-transparent rounded mb-1">
            <div class="d-flex align-items-center flex-grow-1">
                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                    <i class="bi bi-clipboard-check text-white small"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-0 small fw-medium">{{ $quiz->title }}</p>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if($quiz->is_published)
                            <span class="badge bg-success-transparent text-success" style="font-size:0.6rem;">منشور</span>
                        @else
                            <span class="badge bg-warning-transparent text-warning" style="font-size:0.6rem;">غير منشور</span>
                        @endif
                        <span class="text-muted" style="font-size:0.65rem;">
                            <i class="bi bi-question-circle me-1"></i>{{ $quiz->questions_count ?? $quiz->questions->count() }} سؤال
                        </span>
                        @if($quiz->duration_minutes)
                        <span class="text-muted" style="font-size:0.65rem;">
                            <i class="bi bi-clock me-1"></i>{{ $quiz->duration_minutes }} دقيقة
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                @php
                    // جلب الوحدات المرتبطة مباشرة من قاعدة البيانات
                    $linkedUnitIds = \Illuminate\Support\Facades\DB::table('quiz_units')
                        ->where('quiz_id', $quiz->id)
                        ->pluck('unit_id');
                    $linkedUnitsForQuiz = \App\Models\Unit::with('section.subject.schoolClass.stage')
                        ->whereIn('id', $linkedUnitIds)
                        ->get();
                    $quizLinkedUnitsData = $linkedUnitsForQuiz->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'title' => $u->title ?? '',
                            'section_title' => optional($u->section)->title ?? '',
                            'subject_name' => optional(optional($u->section)->subject)->name ?? '',
                            'class_name' => optional(optional(optional($u->section)->subject)->schoolClass)->name ?? '',
                            'stage_name' => optional(optional(optional(optional($u->section)->subject)->schoolClass)->stage)->name ?? '',
                        ];
                    })->values()->toJson();
                @endphp
                @can('quiz-edit')
                    <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="ربط بوحدات أخرى"
                            data-bs-toggle="modal" data-bs-target="#linkQuizUnitsModal"
                            data-quiz-id="{{ $quiz->id }}" data-quiz-title="{{ $quiz->title }}" data-quiz-primary-unit-id="{{ $quiz->unit_id }}"
                            data-linked-units="{{ e($quizLinkedUnitsData) }}">
                        <i class="bi bi-link-45deg"></i>
                    </button>
                @endcan
                @can('quiz-show')
                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}" class="btn btn-sm btn-icon btn-info-transparent" title="عرض"><i class="bi bi-eye"></i></a>
                @endcan
                @can('quiz-edit')
                    <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-icon btn-warning-transparent" title="تعديل"><i class="bi bi-pencil"></i></a>
                @endcan
                @can('quiz-delete')
                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-icon btn-danger-transparent" title="حذف"><i class="bi bi-trash"></i></button>
                    </form>
                @endcan
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="unit-content" data-unit-content-id="{{ $unit->id }}">
    @if($unit->allLessons()->count() === 0)
        <div class="text-center py-4 text-muted bg-light rounded">
            <i class="bi bi-collection-play display-6 d-block mb-2"></i>
            <span class="small">لا توجد محتويات في هذه الوحدة بعد</span>
            @can('lesson-create')
                <p class="small text-muted mb-0 mt-1">اضغط على "درس جديد" لإضافة محتوى</p>
            @endcan
        </div>
    @else
        <div class="list-group list-group-flush" data-sortable="lessons" data-unit-id="{{ $unit->id }}" data-reorder-url="{{ route('admin.units.lessons.reorder', $unit) }}">
            @foreach($unit->allLessons() as $lesson)
            <div class="list-group-item d-flex flex-column px-0 py-2" data-id="{{ $lesson->id }}" data-lesson-id="{{ $lesson->id }}">
                <div class="d-flex align-items-center justify-content-between gap-2 w-100">
                    <span class="sortable-handle d-flex align-items-center px-2 cursor-grab text-muted me-1" title="اسحب لإعادة الترتيب"><i class="bi bi-grip-vertical"></i></span>
                    <div class="d-flex align-items-center min-w-0 flex-grow-1">
                        <div class="me-3 position-relative flex-shrink-0">
                            @if($lesson->thumbnail)
                                <img src="{{ media_public_url($lesson->thumbnail) }}" alt="{{ $lesson->title }}" class="rounded" style="width:60px;height:40px;object-fit:cover;">
                            @else
                                <div class="bg-danger-transparent text-danger rounded d-flex align-items-center justify-content-center" style="width:60px;height:40px;">
                                    <i class="bi bi-play-circle fs-4"></i>
                                </div>
                            @endif
                            @if($lesson->is_free)
                                <span class="badge bg-success position-absolute top-0 start-0" style="font-size:0.6rem;">مجاني</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h6 class="mb-0 fw-semibold small">
                                <span class="sortable-index">{{ $loop->iteration }}</span> - {{ $lesson->title }}
                                @if(!$lesson->is_active)
                                    <span class="badge bg-secondary-transparent text-secondary ms-1">مخفي</span>
                                @endif
                                @if($lesson->review_status === 'pending_review')
                                    <span class="badge bg-warning text-dark ms-1"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>
                                @elseif($lesson->review_status === 'rejected')
                                    <span class="badge bg-danger ms-1"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
                                @endif
                            </h6>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-{{ $lesson->video_type === 'youtube' ? 'danger' : ($lesson->video_type === 'vimeo' ? 'info' : 'primary') }}-transparent text-{{ $lesson->video_type === 'youtube' ? 'danger' : ($lesson->video_type === 'vimeo' ? 'info' : 'primary') }}" style="font-size:0.65rem;">
                                    <i class="bi bi-{{ $lesson->video_type === 'youtube' ? 'youtube' : ($lesson->video_type === 'vimeo' ? 'vimeo' : 'film') }} me-1"></i>
                                    {{ \App\Models\Lesson::VIDEO_TYPES[$lesson->video_type] ?? $lesson->video_type }}
                                </span>
                                @if($lesson->duration)
                                    <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-clock me-1"></i>{{ $lesson->formatted_duration }}</span>
                                @endif
                                @if($lesson->attachments->count() > 0)
                                    <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-paperclip me-1"></i>{{ $lesson->attachments->count() }}</span>
                                @endif
                                @if($lesson->book_page_from !== null || $lesson->book_page_to !== null)
                                    <span class="text-muted" style="font-size:0.7rem;">
                                        <i class="bi bi-journal-bookmark me-1"></i>
                                        @if($lesson->book_page_from !== null && $lesson->book_page_to !== null)
                                            صفحات من {{ $lesson->book_page_from }} إلى {{ $lesson->book_page_to }}
                                        @elseif($lesson->book_page_from !== null)
                                            من صفحة {{ $lesson->book_page_from }}
                                        @else
                                            إلى صفحة {{ $lesson->book_page_to }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        @can('lesson-show')
                            <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn btn-sm btn-icon btn-success-transparent" title="مشاهدة"><i class="bi bi-play-fill"></i></a>
                        @endcan
                        @can('lesson-show')
                            @if($lesson->embed_url || $lesson->video_url)
                            <button type="button" class="btn btn-sm btn-icon btn-warning-transparent" data-bs-toggle="modal" data-bs-target="#playVideoModal{{ $lesson->id }}" title="تشغيل الفيديو - معاينة سريعة"><i class="bi bi-play-circle"></i></button>
                            @endif
                        @endcan
                        @can('lesson-attachment-create')
                            <button type="button" class="btn btn-sm btn-icon btn-info-transparent" data-bs-toggle="modal" data-bs-target="#addLessonAttachment{{ $lesson->id }}" title="إضافة مرفقات"><i class="bi bi-paperclip"></i></button>
                        @endcan
                        @php
                            $isMirroredInThisUnit = $lesson->unit_id !== null && (int) $lesson->unit_id !== (int) $unit->id;
                            $hasExtraUnitLinks = $lesson->linkedUnits->isNotEmpty();
                            $showLessonLinkIndicator = $isMirroredInThisUnit || $hasExtraUnitLinks;
                            $lessonLinkTooltipParts = [];
                            if ($isMirroredInThisUnit) {
                                $pu = $lesson->unit;
                                $orig = $pu ? trim(implode(' — ', array_filter([optional(optional($pu->section)->subject)->name, optional($pu->section)->title, $pu->title]))) : '';
                                $lessonLinkTooltipParts[] = 'يظهر في هذه الوحدة عبر الربط الإضافي.' . ($orig !== '' ? ' المنشأ: ' . $orig : '');
                            }
                            if ($hasExtraUnitLinks) {
                                foreach ($lesson->linkedUnits as $lu) {
                                    $row = trim(implode(' — ', array_filter([optional(optional($lu->section)->subject)->name, optional($lu->section)->title, $lu->title])));
                                    if ($row !== '') {
                                        $lessonLinkTooltipParts[] = 'يظهر أيضاً في: ' . $row;
                                    }
                                }
                            }
                            $lessonLinkTooltip = implode(' ', array_filter($lessonLinkTooltipParts));
                        @endphp
                        @if($showLessonLinkIndicator)
                            <span class="btn btn-sm btn-icon btn-outline-secondary border-secondary-subtle text-secondary lesson-cross-links-indicator"
                                  role="img"
                                  tabindex="0"
                                  style="cursor: help;"
                                  title="{{ e($lessonLinkTooltip) }}">
                                <i class="bi bi-diagram-3"></i>
                            </span>
                        @endif
                        @can('lesson-edit')
                            <button type="button" class="btn btn-sm btn-icon btn-primary-transparent" data-bs-toggle="modal" data-bs-target="#editLesson{{ $lesson->id }}" title="تعديل"><i class="bi bi-pencil"></i></button>
                        @endcan
                        @can('lesson-delete')
                            <button type="button" class="btn btn-sm btn-icon btn-danger-transparent" data-bs-toggle="modal" data-bs-target="#deleteLesson{{ $lesson->id }}" title="حذف"><i class="bi bi-trash"></i></button>
                        @endcan
                        @if($lesson->review_status === 'pending_review')
                            @canany(['lesson-approve-review', 'lesson-reject-review'])
                            <div class="btn-group btn-group-sm ms-2">
                                @can('lesson-approve-review')
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveLesson{{ $lesson->id }}" title="موافقة"><i class="bi bi-check-circle"></i></button>
                                @endcan
                                @can('lesson-reject-review')
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectLesson{{ $lesson->id }}" title="رفض"><i class="bi bi-x-circle"></i></button>
                                @endcan
                            </div>
                            @endcanany
                        @endif
                        @can('quiz-create')
                            <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id, 'unit_id' => $unit->id, 'lesson_id' => $lesson->id, 'scope' => 'lesson']) }}" class="btn btn-sm btn-outline-info" title="اختبار لهذا الدرس"><i class="bi bi-clipboard-check me-1"></i> اختبار الدرس</a>
                        @endcan
                        @if($lesson->quizzes && $lesson->quizzes->count() > 0)
                            @php $firstQuiz = $lesson->quizzes->first(); @endphp
                            @can('quiz-show')
                                <a href="{{ route('admin.quizzes.show', $firstQuiz->id) }}" class="btn btn-sm btn-icon btn-info-transparent" title="{{ $firstQuiz->title }}"><i class="bi bi-question-circle"></i></a>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
