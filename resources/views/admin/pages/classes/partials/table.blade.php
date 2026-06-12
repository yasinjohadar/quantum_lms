@forelse($classes as $class)
    <tr data-id="{{ $class->id }}">
        <td class="text-center" style="width: 32px;">
            <span class="sortable-handle cl-sort-handle" title="اسحب لإعادة الترتيب">
                <i class="bi bi-grip-vertical"></i>
            </span>
        </td>
        <td class="text-muted small">{{ $loop->iteration + ($classes->currentPage() - 1) * $classes->perPage() }}</td>
        <td>
            <div class="cl-class-cell">
                <img src="{{ $class->image ? media_public_url($class->image) : asset('assets/images/media/media-22.jpg') }}"
                     alt="{{ $class->name }}"
                     class="cl-class-thumb">
                <a href="{{ route('admin.classes.show', $class->id) }}" class="cl-class-name">
                    {{ $class->name }}
                </a>
            </div>
        </td>
        <td>
            @if($class->stage)
                <span class="cl-stage-pill">{{ $class->stage->name }}</span>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td>
            @can('class-toggle-status')
                <button type="button"
                        class="cl-status-badge {{ $class->is_active ? 'cl-status-badge--active' : 'cl-status-badge--inactive' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#toggleClassStatus{{ $class->id }}"
                        title="تغيير الحالة">
                    <i class="bi {{ $class->is_active ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                    {{ $class->is_active ? 'نشط' : 'غير نشط' }}
                </button>
            @else
                <span class="cl-status-badge {{ $class->is_active ? 'cl-status-badge--active' : 'cl-status-badge--inactive' }}">
                    {{ $class->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            @endcan
        </td>
        <td>
            <div class="row-action-bar">
                @can('class-show')
                    <a href="{{ route('admin.classes.show', $class->id) }}"
                       class="row-action-btn row-action-btn--info"
                       title="عرض">
                        <i class="bi bi-eye"></i>
                    </a>
                @endcan
                @can('class-enrolled-students')
                    <a href="{{ route('admin.classes.enrolled-students', $class->id) }}"
                       class="row-action-btn row-action-btn--primary"
                       title="الطلاب">
                        <i class="bi bi-people"></i>
                    </a>
                @endcan
                @can('class-edit')
                    <a href="{{ route('admin.classes.edit', $class->id) }}"
                       class="row-action-btn row-action-btn--warning"
                       title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('class-delete')
                    <span class="row-action-divider" aria-hidden="true"></span>
                    <button type="button"
                            class="row-action-btn row-action-btn--danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteClass{{ $class->id }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>

            @can('class-delete')
                @include('admin.pages.classes.delete', ['class' => $class])
            @endcan
            @can('class-toggle-status')
                @include('admin.pages.classes.partials.toggle-status', ['class' => $class])
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6">
            <div class="classes-empty">
                <i class="bi bi-building"></i>
                <p class="mb-0 fw-semibold">لا توجد صفوف مطابقة للفلاتر</p>
            </div>
        </td>
    </tr>
@endforelse
