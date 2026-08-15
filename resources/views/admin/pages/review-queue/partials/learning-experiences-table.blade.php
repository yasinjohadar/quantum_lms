@php
    $indexOffset = isset($experiences) ? ($experiences->currentPage() - 1) * $experiences->perPage() : 0;
@endphp

<div class="rq-table-wrap">
    <div class="table-responsive">
        <table class="table rq-table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان الاختبار التفاعلي</th>
                    <th>المادة / الصف</th>
                    <th>المعلم</th>
                    <th>تاريخ الإرسال</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($experiences as $experience)
                    @php
                        $subject = $experience->subject;
                        $class = $subject?->schoolClass;
                    @endphp
                    <tr>
                        <td data-label="#">{{ $loop->iteration + $indexOffset }}</td>
                        <td data-label="عنوان الاختبار التفاعلي">
                            <div class="rq-item-title">{{ $experience->title }}</div>
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
                            <span class="small">{{ $experience->creator->name ?? '—' }}</span>
                        </td>
                        <td data-label="تاريخ الإرسال">
                            @if($experience->submitted_for_review_at)
                                <span class="small">{{ \Carbon\Carbon::parse($experience->submitted_for_review_at)->format('Y-m-d H:i') }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td data-label="إجراءات" class="text-center">
                            <div class="row-action-bar justify-content-center">
                                <a href="{{ route('admin.learning-experiences.edit', $experience->id) }}"
                                   class="row-action-btn row-action-btn--primary" title="عرض ومراجعة">
                                    <i class="bi bi-eye"></i>
                                </a>
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
