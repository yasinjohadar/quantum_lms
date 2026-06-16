@forelse($enrollments as $enrollment)
    @php
        $rowNum = $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage();
        $initial = $enrollment->user ? mb_strtoupper(mb_substr(trim($enrollment->user->name), 0, 1)) : '—';
        $statusClass = match ($enrollment->status) {
            'active' => 'ui-enrollment-status--active',
            'pending', 'suspended' => 'ui-enrollment-status--pending',
            'completed' => 'ui-enrollment-status--completed',
            default => 'ui-enrollment-status--other',
        };
        $statusLabel = match ($enrollment->status) {
            'active' => 'نشط',
            'suspended' => 'معلق',
            'pending' => 'قيد الانتظار',
            'completed' => 'مكتمل',
            default => $enrollment->status,
        };
    @endphp
    <tr>
        <td class="text-center">
            <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}" class="form-check-input enrollment-row-checkbox" aria-label="تحديد">
        </td>
        <th scope="row" class="text-muted small">{{ $rowNum }}</th>
        <td>
            @if($enrollment->user)
                <div class="ui-user-cell">
                    @if($enrollment->user->photo)
                        <img src="{{ media_public_url($enrollment->user->photo) }}"
                             alt="{{ $enrollment->user->name }}"
                             class="ui-user-avatar"
                             style="object-fit: cover; padding: 0;">
                    @else
                        <span class="ui-user-avatar" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">{{ $initial }}</span>
                    @endif
                    <div class="min-width-0">
                        <div class="ui-user-name text-truncate">{{ $enrollment->user->name }}</div>
                        <small class="text-muted text-truncate d-block">{{ $enrollment->user->email }}</small>
                    </div>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($enrollment->subject)
                <div class="ui-enrollment-subject">{{ $enrollment->subject->name }}</div>
                @if($enrollment->subject->schoolClass)
                    <div class="ui-enrollment-subject-meta d-md-none">{{ $enrollment->subject->schoolClass->name }}</div>
                @endif
            @else
                <span class="text-danger small fw-semibold">تم حذف المادة</span>
            @endif
        </td>
        <td class="enrollments-col-class">
            @if($enrollment->subject && $enrollment->subject->schoolClass)
                <span class="ui-class-pill">
                    <i class="bi bi-building"></i>
                    {{ $enrollment->subject->schoolClass->name }}
                </span>
                @if($enrollment->subject->schoolClass->stage)
                    <div class="ui-enrollment-subject-meta mt-1">{{ $enrollment->subject->schoolClass->stage->name }}</div>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            <span class="ui-enrollment-status {{ $statusClass }}">
                <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                {{ $statusLabel }}
            </span>
        </td>
        <td class="enrollments-col-date">
            <div class="ui-date-cell">
                {{ $enrollment->enrolled_at->format('Y-m-d') }}
                <small>{{ $enrollment->enrolled_at->format('H:i') }}</small>
            </div>
        </td>
        <td class="enrollments-col-added-by">
            @if($enrollment->enrolledBy)
                <span class="small fw-medium">{{ $enrollment->enrolledBy->name }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            <div class="row-action-bar">
                @if($enrollment->notes)
                    <button type="button"
                            class="row-action-btn row-action-btn--info"
                            data-bs-toggle="tooltip"
                            title="{{ $enrollment->notes }}">
                        <i class="bi bi-chat-left-text"></i>
                    </button>
                @endif
                <button type="button"
                        class="row-action-btn row-action-btn--danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteEnrollmentModal{{ $enrollment->id }}"
                        title="إلغاء الانضمام">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>

    <div class="modal fade" id="deleteEnrollmentModal{{ $enrollment->id }}" tabindex="-1" aria-labelledby="deleteEnrollmentModalLabel{{ $enrollment->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="deleteEnrollmentModalLabel{{ $enrollment->id }}">تأكيد إلغاء الانضمام</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h6 class="mb-3">هل أنت متأكد من إلغاء هذا الانضمام؟</h6>
                    <p class="text-muted mb-3">
                        سيتم إلغاء انضمام الطالب <strong>{{ $enrollment->user?->name ?? '—' }}</strong>
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
        <td colspan="9">
            <div class="enrollments-index-empty">
                <i class="bi bi-journal-x"></i>
                <p class="mb-0 fw-semibold">لا توجد انضمامات مطابقة للبحث</p>
                <p class="small mb-0 mt-1">جرّب تغيير الفلاتر أو أضف انضمامات جديدة</p>
            </div>
        </td>
    </tr>
@endforelse
