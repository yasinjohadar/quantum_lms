@forelse($subjects as $subject)
    <tr>
        <td>{{ $loop->iteration + ($subjects->currentPage() - 1) * $subjects->perPage() }}</td>
        <td>
            <div class="d-flex justify-content-center">
                <img src="{{ $subject->image ? asset('storage/' . $subject->image) : asset('assets/images/media/media-22.jpg') }}"
                     alt="{{ $subject->name }}"
                     class="rounded"
                     style="width: 60px; height: 60px; object-fit: cover;">
            </div>
        </td>
        <td>
            <a href="{{ route('admin.subjects.show', $subject->id) }}" class="text-decoration-none fw-semibold">
                {{ $subject->name }}
            </a>
        </td>
        <td>
            {{ $subject->schoolClass?->name ?? '-' }}
            @if($subject->schoolClass && $subject->schoolClass->stage)
                <span class="text-muted small d-block">
                    ({{ $subject->schoolClass->stage->name }})
                </span>
            @endif
        </td>
        <td>{{ $subject->order }}</td>
        <td>
            @if ($subject->display_in_class)
                <span class="badge bg-info text-dark">نعم</span>
            @else
                <span class="badge bg-secondary">لا</span>
            @endif
        </td>
        <td>
            @if ($subject->is_active)
                <span class="badge bg-success">نشطة</span>
            @else
                <span class="badge bg-danger">غير نشطة</span>
            @endif
        </td>
        <td>{{ $subject->created_at?->format('Y-m-d H:i') }}</td>
        <td>
            <div class="d-flex gap-1 flex-wrap justify-content-center">
                <a href="{{ route('admin.subjects.show', $subject->id) }}"
                   class="btn btn-sm btn-info text-white"
                   title="عرض تفاصيل المادة">
                    <i class="fas fa-eye"></i> عرض
                </a>
                <a href="{{ route('admin.subjects.enrolled-students', $subject->id) }}"
                   class="btn btn-sm btn-primary text-white"
                   title="عرض الطلاب المنضمين">
                    <i class="fas fa-users"></i> الطلاب
                </a>
                <a href="{{ route('admin.subjects.edit', $subject->id) }}"
                   class="btn btn-sm btn-warning text-white"
                   title="تعديل المادة">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button"
                        class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteSubject{{ $subject->id }}"
                        title="حذف المادة">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
            </div>

            @include('admin.pages.subjects.force-delete', ['subject' => $subject])
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center text-danger fw-bold">
            لا توجد مواد مسجلة حالياً
        </td>
    </tr>
@endforelse
