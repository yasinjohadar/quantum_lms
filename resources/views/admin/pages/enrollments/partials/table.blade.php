@forelse($enrollments as $enrollment)
    <tr>
        <td>{{ $enrollment->id }}</td>
        <td>
            <div class="d-flex align-items-center gap-2">
                @if($enrollment->user->photo)
                    <img src="{{ asset('storage/' . $enrollment->user->photo) }}" 
                         alt="{{ $enrollment->user->name }}" 
                         class="rounded-circle" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px;">
                        {{ substr($enrollment->user->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                    <small class="text-muted">{{ $enrollment->user->email }}</small>
                </div>
            </div>
        </td>
        <td>
            @if($enrollment->subject)
                <div class="fw-semibold">{{ $enrollment->subject->name }}</div>
                @if($enrollment->subject->schoolClass)
                    <small class="text-muted">{{ $enrollment->subject->schoolClass->name }}</small>
                @endif
            @else
                <span class="text-danger">تم حذف المادة</span>
            @endif
        </td>
        <td>
            @if($enrollment->subject && $enrollment->subject->schoolClass)
                <span class="badge bg-info-transparent text-info">
                    {{ $enrollment->subject->schoolClass->name }}
                </span>
                @if($enrollment->subject->schoolClass->stage)
                    <br>
                    <small class="text-muted">{{ $enrollment->subject->schoolClass->stage->name }}</small>
                @endif
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($enrollment->status === 'active')
                <span class="badge bg-success-transparent text-success">نشط</span>
            @elseif($enrollment->status === 'suspended')
                <span class="badge bg-warning-transparent text-warning">معلق</span>
            @elseif($enrollment->status === 'pending')
                <span class="badge bg-warning-transparent text-warning">معلق</span>
            @elseif($enrollment->status === 'completed')
                <span class="badge bg-info-transparent text-info">مكتمل</span>
            @else
                <span class="badge bg-secondary-transparent text-secondary">{{ $enrollment->status }}</span>
            @endif
        </td>
        <td>
            <div>{{ $enrollment->enrolled_at->format('Y-m-d') }}</div>
            <small class="text-muted">{{ $enrollment->enrolled_at->format('H:i') }}</small>
        </td>
        <td>
            @if($enrollment->enrolledBy)
                {{ $enrollment->enrolledBy->name }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                @if($enrollment->notes)
                    <button type="button" class="btn btn-sm btn-info" 
                            data-bs-toggle="tooltip" 
                            title="{{ $enrollment->notes }}">
                        <i class="bi bi-info-circle"></i>
                    </button>
                @endif
                <button type="button" 
                        class="btn btn-sm btn-danger" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteEnrollmentModal{{ $enrollment->id }}">
                    <i class="bi bi-trash"></i> إلغاء
                </button>
            </div>
        </td>
    </tr>
    
    <!-- Modal for Delete Confirmation -->
    <div class="modal fade" id="deleteEnrollmentModal{{ $enrollment->id }}" tabindex="-1" aria-labelledby="deleteEnrollmentModalLabel{{ $enrollment->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="deleteEnrollmentModalLabel{{ $enrollment->id }}">
                        تأكيد إلغاء الانضمام
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 80px;"></i>
                    </div>
                    <h6 class="mb-3">هل أنت متأكد من إلغاء هذا الانضمام؟</h6>
                    <p class="text-muted mb-3">
                        سيتم إلغاء انضمام الطالب <strong>{{ $enrollment->user->name }}</strong> 
                        @if($enrollment->subject)
                            لمادة <strong>{{ $enrollment->subject->name }}</strong>
                        @else
                            لمادة (تم حذف المادة)
                        @endif
                    </p>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>هذه العملية لا يمكن التراجع عنها.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <form action="{{ route('admin.enrollments.destroy', $enrollment->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@empty
    <tr>
        <td colspan="8" class="text-center text-danger fw-bold">
            لا توجد انضمامات مسجلة حالياً
        </td>
    </tr>
@endforelse
