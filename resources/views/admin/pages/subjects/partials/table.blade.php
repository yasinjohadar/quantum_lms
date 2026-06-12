@forelse($subjects as $subject)
    <tr data-id="{{ $subject->id }}">
        <td class="text-center" style="width: 32px;">
            <span class="sortable-handle sb-sort-handle" title="اسحب لإعادة الترتيب">
                <i class="bi bi-grip-vertical"></i>
            </span>
        </td>
        <td class="text-muted small">{{ $loop->iteration + ($subjects->currentPage() - 1) * $subjects->perPage() }}</td>
        <td>
            <div class="sb-subject-cell">
                <img src="{{ $subject->image ? media_public_url($subject->image) : asset('assets/images/media/media-22.jpg') }}"
                     alt="{{ $subject->name }}"
                     class="sb-subject-thumb">
                <div>
                    <a href="{{ route('admin.subjects.show', $subject->id) }}" class="sb-subject-name d-block">
                        {{ $subject->name }}
                    </a>
                </div>
            </div>
        </td>
        <td>
            <span class="fw-medium small">{{ $subject->schoolClass?->name ?? '—' }}</span>
            @if($subject->schoolClass && $subject->schoolClass->stage)
                <span class="sb-class-meta d-block">{{ $subject->schoolClass->stage->name }}</span>
            @endif
        </td>
        <td>
            @can('subject-toggle-status')
                <button type="button"
                        class="sb-status-badge {{ $subject->is_active ? 'sb-status-badge--active' : 'sb-status-badge--inactive' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#toggleSubjectStatus{{ $subject->id }}"
                        title="تغيير الحالة">
                    <i class="bi {{ $subject->is_active ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                    {{ $subject->is_active ? 'نشطة' : 'غير نشطة' }}
                </button>
            @else
                <span class="sb-status-badge {{ $subject->is_active ? 'sb-status-badge--active' : 'sb-status-badge--inactive' }}">
                    {{ $subject->is_active ? 'نشطة' : 'غير نشطة' }}
                </span>
            @endcan
        </td>
        <td>
            <div class="row-action-bar">
                @can('question-list')
                    <a href="{{ route('admin.subjects.questions.index', $subject->id) }}"
                       class="row-action-btn row-action-btn--secondary"
                       title="بنك الأسئلة">
                        <i class="bi bi-journal-text"></i>
                    </a>
                @endcan
                @can('subject-show')
                    <a href="{{ route('admin.subjects.show', $subject->id) }}"
                       class="row-action-btn row-action-btn--info"
                       title="عرض">
                        <i class="bi bi-eye"></i>
                    </a>
                @endcan
                @can('subject-enrolled-students')
                    <a href="{{ route('admin.subjects.enrolled-students', $subject->id) }}"
                       class="row-action-btn row-action-btn--primary"
                       title="الطلاب">
                        <i class="bi bi-people"></i>
                    </a>
                @endcan
                @can('subject-edit')
                    <a href="{{ route('admin.subjects.edit', $subject->id) }}"
                       class="row-action-btn row-action-btn--warning"
                       title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('subject-delete')
                    <span class="row-action-divider" aria-hidden="true"></span>
                    <button type="button"
                            class="row-action-btn row-action-btn--danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteSubject{{ $subject->id }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>

            @can('subject-delete')
                @include('admin.pages.subjects.force-delete', ['subject' => $subject])
            @endcan
            @can('subject-toggle-status')
                @include('admin.pages.subjects.partials.toggle-status', ['subject' => $subject])
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6">
            <div class="subjects-empty">
                <i class="bi bi-journal-bookmark"></i>
                <p class="mb-0 fw-semibold">لا توجد مواد مطابقة للفلاتر</p>
            </div>
        </td>
    </tr>
@endforelse
