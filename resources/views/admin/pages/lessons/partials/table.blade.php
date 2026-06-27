@php
    if (! function_exists('liLinkPathLines')) {
        function liLinkPathLines(?\App\Models\Unit $unit): array
        {
            if (! $unit) {
                return [];
            }

            $subject = $unit->relationLoaded('section')
                ? $unit->section?->subject
                : $unit->section()->with('subject.schoolClass')->first()?->subject;

            return array_values(array_filter([
                (string) (data_get($subject, 'schoolClass.name') ?? ''),
                (string) ($subject->name ?? ''),
                (string) (data_get($unit, 'section.path_title') ?? data_get($unit, 'section.title') ?? ''),
                (string) ($unit->title ?? ''),
            ], fn ($line) => $line !== ''));
        }
    }
@endphp

@forelse($lessons as $lesson)
    @php
        $lessonSubject = $lesson->unit?->section?->subject ?? $lesson->section?->subject;
        $lessonSection = $lesson->unit?->section ?? $lesson->section;
        $reviewBadgeClass = match ($lesson->review_status) {
            \App\Models\Lesson::REVIEW_STATUS_APPROVED => 'li-badge--review-approved',
            \App\Models\Lesson::REVIEW_STATUS_PENDING => 'li-badge--review-pending',
            \App\Models\Lesson::REVIEW_STATUS_REJECTED => 'li-badge--review-rejected',
            default => 'li-badge--review-draft',
        };
        $legacyUnits = $lesson->linkedUnits->filter(function ($u) use ($lesson) {
            return $lesson->unit_id === null || (int) $u->id !== (int) $lesson->unit_id;
        });
    @endphp
    <tr>
        <td class="text-muted small">{{ $lesson->id }}</td>
        <td class="lessons-col-title">
            <div class="li-lesson-cell">
                <div class="li-lesson-title-row">
                    @can('lesson-show')
                        <a href="{{ route('admin.lessons.show', $lesson) }}" class="li-lesson-title" title="{{ e($lesson->title) }}">{{ $lesson->title }}</a>
                    @else
                        <span class="fw-semibold" title="{{ e($lesson->title) }}">{{ $lesson->title }}</span>
                    @endcan
                    @if($lesson->isSyncMirror())
                        <span class="li-badge li-badge--mirror flex-shrink-0" title="نسخة متزامنة"><i class="bi bi-copy"></i></span>
                    @endif
                </div>
                @if($lesson->formatted_duration)
                    <span class="text-muted small"><i class="bi bi-clock"></i> {{ $lesson->formatted_duration }}</span>
                @endif
            </div>
        </td>
        <td class="lessons-col-subject">
            @if($lessonSubject)
                <span class="fw-semibold small d-block text-truncate" title="{{ e($lessonSubject->name) }}">{{ $lessonSubject->name }}</span>
                @if($lessonSubject->schoolClass)
                    <span class="text-muted small text-truncate d-block" title="{{ e($lessonSubject->schoolClass->name) }}">{{ $lessonSubject->schoolClass->name }}</span>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="small text-muted lessons-col-section">
            <div title="{{ e($lessonSection?->path_title ?? $lessonSection?->title ?? '') }}">{{ $lessonSection?->path_title ?? $lessonSection?->title ?? '—' }}</div>
            <div title="{{ e($lesson->unit?->title ?? '') }}">{{ $lesson->unit?->title ?? '—' }}</div>
        </td>
        <td class="small lessons-col-video">
            {{ \App\Models\Lesson::VIDEO_TYPES[$lesson->video_type] ?? ($lesson->video_type ?: '—') }}
        </td>
        <td class="lessons-col-review">
            <span class="li-badge {{ $reviewBadgeClass }}">{{ $lesson->review_status_name }}</span>
            @if(! $lesson->is_active)
                <span class="li-badge li-badge--review-draft mt-1"><i class="bi bi-pause-circle"></i> معطّل</span>
            @endif
        </td>
        <td class="lessons-col-links">
            @if($lesson->isSyncMirror() && $lesson->clonedFromLesson)
                <div class="li-links-cell">
                    <span class="li-badge li-badge--mirror">
                        <i class="bi bi-link-45deg"></i>
                        أصل:
                        @can('lesson-show')
                            <a href="{{ route('admin.lessons.show', $lesson->clonedFromLesson) }}" class="text-decoration-none">#{{ $lesson->clonedFromLesson->id }}</a>
                        @else
                            #{{ $lesson->clonedFromLesson->id }}
                        @endcan
                    </span>
                    @if($lines = liLinkPathLines($lesson->unit))
                        <div class="li-link-stack">
                            @foreach($lines as $line)
                                <span class="li-link-stack__line" title="{{ e($line) }}">{{ $line }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="li-links-cell">
                    @if(($lesson->sync_mirrors_count ?? 0) > 0)
                        <span class="li-badge li-badge--sync">
                            <i class="bi bi-box-arrow-up-right"></i> sync: {{ $lesson->sync_mirrors_count }}
                        </span>
                        @if($lesson->relationLoaded('syncMirrors') && $lesson->syncMirrors->isNotEmpty())
                            @foreach($lesson->syncMirrors->take(4) as $mirror)
                                @if($lines = liLinkPathLines($mirror->unit))
                                    <div class="li-link-stack li-link-stack--item">
                                        @foreach($lines as $line)
                                            <span class="li-link-stack__line" title="{{ e($line) }}">{{ $line }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                            @if($lesson->sync_mirrors_count > 4)
                                <span class="li-link-stack__more">+ {{ $lesson->sync_mirrors_count - 4 }} أخرى</span>
                            @endif
                        @endif
                    @endif
                    @if($legacyUnits->isNotEmpty())
                        <span class="li-badge li-badge--legacy">
                            <i class="bi bi-diagram-3"></i> legacy: {{ $legacyUnits->count() }}
                        </span>
                        @foreach($legacyUnits->take(3) as $lu)
                            @if($lines = liLinkPathLines($lu))
                                <div class="li-link-stack li-link-stack--item">
                                    @foreach($lines as $line)
                                        <span class="li-link-stack__line" title="{{ e($line) }}">{{ $line }}</span>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif
                    @if(($lesson->sync_mirrors_count ?? 0) === 0 && $legacyUnits->isEmpty())
                        <span class="text-muted small">—</span>
                    @endif
                </div>
            @endif
        </td>
        <td class="lessons-col-actions">
            <div class="d-flex flex-wrap gap-1">
                @can('lesson-show')
                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-sm btn-outline-primary" title="معاينة"><i class="bi bi-eye"></i></a>
                @endcan
                @can('lesson-edit')
                    @if(! $lesson->isSyncMirror())
                        <button type="button"
                                class="btn btn-sm btn-outline-info link-lesson-units-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#linkLessonUnitsModal"
                                data-lesson-id="{{ $lesson->id }}"
                                data-lesson-title="{{ e($lesson->title) }}"
                                data-lesson-primary-unit-id="{{ $lesson->unit_id ?? '' }}"
                                title="نسخ الدرس في وحدات أخرى (متزامن)">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    @endif
                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bi bi-pencil"></i></a>
                @endcan
                @if($lessonSubject)
                    @can('subject-show')
                        <a href="{{ route('admin.subjects.show', $lessonSubject) }}" class="btn btn-sm btn-outline-info" title="صفحة المادة"><i class="bi bi-journal-bookmark"></i></a>
                    @endcan
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted py-5">
            <i class="bi bi-journal-x d-block fs-3 mb-2"></i>
            لا توجد دروس مطابقة للتصفية.
        </td>
    </tr>
@endforelse
