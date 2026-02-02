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
            @can('subject-toggle-status')
                <button type="button"
                        class="btn btn-sm d-inline-flex align-items-center {{ $subject->is_active ? 'btn-success' : 'btn-outline-danger' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#toggleSubjectStatus{{ $subject->id }}">
                    @if($subject->is_active)
                        <i class="fas fa-check-circle me-1"></i>
                        <span>نشطة</span>
                    @else
                        <i class="fas fa-ban me-1"></i>
                        <span>غير نشطة</span>
                    @endif
                </button>
            @else
                @if ($subject->is_active)
                    <span class="badge bg-success">نشطة</span>
                @else
                    <span class="badge bg-danger">غير نشطة</span>
                @endif
            @endcan
        </td>
        <td>{{ $subject->created_at?->format('Y-m-d H:i') }}</td>
        <td>
            <div class="d-flex gap-1 flex-wrap justify-content-center">
                @can('subject-show')
                    <a href="{{ route('admin.subjects.show', $subject->id) }}"
                       class="btn btn-sm btn-info text-white"
                       title="عرض تفاصيل المادة">
                        <i class="fas fa-eye"></i> عرض
                    </a>
                @endcan
                @can('subject-enrolled-students')
                    <a href="{{ route('admin.subjects.enrolled-students', $subject->id) }}"
                       class="btn btn-sm btn-primary text-white"
                       title="عرض الطلاب المنضمين">
                        <i class="fas fa-users"></i> الطلاب
                    </a>
                @endcan
                @can('subject-edit')
                    <a href="{{ route('admin.subjects.edit', $subject->id) }}"
                       class="btn btn-sm btn-warning text-white"
                       title="تعديل المادة">
                        <i class="fas fa-edit"></i> تعديل
                    </a>
                @endcan
                @can('subject-delete')
                    <button type="button"
                            class="btn btn-sm btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteSubject{{ $subject->id }}"
                            title="حذف المادة">
                        <i class="fas fa-trash-alt"></i> حذف
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
        <td colspan="9" class="text-center text-danger fw-bold">
            لا توجد مواد مسجلة حالياً
        </td>
    </tr>
@endforelse
