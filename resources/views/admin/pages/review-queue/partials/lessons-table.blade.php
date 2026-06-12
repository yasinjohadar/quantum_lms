@php
    $showUnit = $showUnit ?? false;
    $showStatus = $showStatus ?? false;
    $indexOffset = isset($lessons) ? ($lessons->currentPage() - 1) * $lessons->perPage() : 0;
@endphp
<div class="rq-table-wrap">
    <div class="table-responsive">
        <table class="table rq-table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان الدرس</th>
                    <th>المادة / الصف</th>
                    <th>المعلم</th>
                    @if($showUnit)<th>الوحدة</th>@endif
                    <th>تاريخ الإرسال</th>
                    @if($showStatus)<th>الحالة</th>@endif
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lessons as $lesson)
                    @php
                        $subject = $lesson->unit?->section?->subject ?? $lesson->section?->subject;
                        $class = $subject?->schoolClass;
                        $teachers = $subject?->assignedTeachers ?? collect();
                        $unitTitle = $lesson->unit?->title;
                        $sectionTitle = $lesson->section?->title ?? $lesson->unit?->section?->title;
                    @endphp
                    <tr>
                        <td data-label="#">{{ $loop->iteration + $indexOffset }}</td>
                        <td data-label="عنوان الدرس">
                            <div class="rq-item-title">{{ $lesson->title }}</div>
                            @if($sectionTitle)
                                <div class="rq-item-meta"><i class="bi bi-folder2-open me-1"></i>{{ $sectionTitle }}</div>
                            @endif
                        </td>
                        <td data-label="المادة / الصف">
                            @if($subject)
                                <span class="rq-chip rq-chip--subject"><i class="bi bi-journal-bookmark"></i> {{ $subject->name }}</span>
                                @if($class)
                                    <span class="rq-chip rq-chip--class ms-1"><i class="bi bi-building"></i> {{ $class->name }}</span>
                                @endif
                            @else
                                <span class="text-muted small">غير محدد</span>
                            @endif
                        </td>
                        <td data-label="المعلم">
                            @if($teachers->isNotEmpty())
                                <span class="small">{{ $teachers->pluck('name')->join('، ') }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        @if($showUnit)
                            <td data-label="الوحدة">{{ $unitTitle ?? '—' }}</td>
                        @endif
                        <td data-label="تاريخ الإرسال">
                            @if($lesson->submitted_for_review_at)
                                <span class="small">{{ \Carbon\Carbon::parse($lesson->submitted_for_review_at)->format('Y-m-d H:i') }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        @if($showStatus)
                            <td data-label="الحالة"><span class="rq-status">قيد المراجعة</span></td>
                        @endif
                        <td data-label="إجراءات" class="text-center">
                            <div class="row-action-bar justify-content-center">
                                @can('lesson-show')
                                    <a href="{{ route('admin.lessons.show', $lesson->id) }}"
                                       class="row-action-btn row-action-btn--primary" title="عرض ومراجعة">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @if($subject)
                                    <a href="{{ route('admin.subjects.show', $subject->id) }}"
                                       class="row-action-btn row-action-btn--info" title="صفحة المادة">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
