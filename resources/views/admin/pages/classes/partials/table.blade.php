@forelse($classes as $class)
    <tr data-id="{{ $class->id }}">
        <td class="sortable-handle d-flex align-items-center justify-content-center cursor-grab text-muted py-2" style="width: 36px; min-width: 36px;" title="اسحب لإعادة الترتيب">
            <i class="bi bi-grip-vertical"></i>
        </td>
        <td>{{ $loop->iteration + ($classes->currentPage() - 1) * $classes->perPage() }}</td>
        <td>
            <div class="d-flex justify-content-center">
                <img src="{{ $class->image ? asset('storage/' . $class->image) : asset('assets/images/media/media-22.jpg') }}"
                     alt="{{ $class->name }}"
                     class="rounded"
                     style="width: 60px; height: 60px; object-fit: cover;">
            </div>
        </td>
        <td>
            <a href="{{ route('admin.classes.show', $class->id) }}" class="text-decoration-none fw-semibold">
                {{ $class->name }}
            </a>
        </td>
        <td>{{ $class->stage?->name ?? '-' }}</td>
        <td>
            @can('class-toggle-status')
                <button type="button"
                        class="btn btn-sm d-inline-flex align-items-center {{ $class->is_active ? 'btn-success' : 'btn-outline-danger' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#toggleClassStatus{{ $class->id }}">
                    @if($class->is_active)
                        <i class="fas fa-check-circle me-1"></i>
                        <span>نشط</span>
                    @else
                        <i class="fas fa-ban me-1"></i>
                        <span>غير نشط</span>
                    @endif
                </button>
            @else
                @if ($class->is_active)
                    <span class="badge bg-success">نشط</span>
                @else
                    <span class="badge bg-danger">غير نشط</span>
                @endif
            @endcan
        </td>
        <td>
            <div class="d-flex gap-1 flex-wrap justify-content-center">
                @can('class-show')
                    <a href="{{ route('admin.classes.show', $class->id) }}"
                       class="btn btn-sm btn-info text-white"
                       title="عرض تفاصيل الصف">
                        <i class="fas fa-eye"></i> عرض
                    </a>
                @endcan
                @can('class-enrolled-students')
                    <a href="{{ route('admin.classes.enrolled-students', $class->id) }}"
                       class="btn btn-sm btn-primary text-white"
                       title="عرض الطلاب المنضمين">
                        <i class="fas fa-users"></i> الطلاب
                    </a>
                @endcan
                @can('class-edit')
                    <a href="{{ route('admin.classes.edit', $class->id) }}"
                       class="btn btn-sm btn-warning text-white"
                       title="تعديل الصف">
                        <i class="fas fa-edit"></i> تعديل
                    </a>
                @endcan
                @can('class-delete')
                    <button type="button"
                            class="btn btn-sm btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteClass{{ $class->id }}"
                            title="حذف الصف">
                        <i class="fas fa-trash-alt"></i> حذف
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
        <td colspan="7" class="text-center text-danger fw-bold">
            لا توجد صفوف مسجلة حالياً
        </td>
    </tr>
@endforelse
