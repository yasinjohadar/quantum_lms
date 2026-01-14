@forelse($classes as $class)
    <tr>
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
        <td>{{ $class->order }}</td>
        <td>
            @if ($class->is_active)
                <span class="badge bg-success">نشط</span>
            @else
                <span class="badge bg-danger">غير نشط</span>
            @endif
        </td>
        <td>{{ $class->created_at?->format('Y-m-d H:i') }}</td>
        <td>
            <div class="d-flex gap-1 flex-wrap justify-content-center">
                <a href="{{ route('admin.classes.show', $class->id) }}"
                   class="btn btn-sm btn-info text-white"
                   title="عرض تفاصيل الصف">
                    <i class="fas fa-eye"></i> عرض
                </a>
                <a href="{{ route('admin.classes.enrolled-students', $class->id) }}"
                   class="btn btn-sm btn-primary text-white"
                   title="عرض الطلاب المنضمين">
                    <i class="fas fa-users"></i> الطلاب
                </a>
                <a href="{{ route('admin.classes.edit', $class->id) }}"
                   class="btn btn-sm btn-warning text-white"
                   title="تعديل الصف">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button"
                        class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteClass{{ $class->id }}"
                        title="حذف الصف">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
            </div>

            @include('admin.pages.classes.delete', ['class' => $class])
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-danger fw-bold">
            لا توجد صفوف مسجلة حالياً
        </td>
    </tr>
@endforelse
