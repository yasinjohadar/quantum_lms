@forelse($students as $student)
    @php
        $stats = $studentsStats[$student->id] ?? ['total_subjects' => 0, 'avg_progress' => 0];
    @endphp
    <tr>
        <td>
            <div class="d-flex align-items-center">
                @if($student->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" 
                         alt="{{ $student->name }}" 
                         class="rounded-circle me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                         style="width: 40px; height: 40px;">
                        <i class="bi bi-person"></i>
                    </div>
                @endif
                <div>
                    <h6 class="mb-0">{{ $student->name }}</h6>
                    @if($student->phone)
                        <small class="text-muted">{{ $student->phone }}</small>
                    @endif
                </div>
            </div>
        </td>
        <td>{{ $student->email }}</td>
        <td>
            <span class="badge bg-info">
                {{ $stats['total_subjects'] }} كورس
            </span>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1 me-2">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $stats['avg_progress'] >= 75 ? 'success' : ($stats['avg_progress'] >= 50 ? 'warning' : 'danger') }}" 
                             role="progressbar" 
                             style="width: {{ $stats['avg_progress'] }}%"
                             aria-valuenow="{{ $stats['avg_progress'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <span class="fw-semibold">{{ number_format($stats['avg_progress'], 1) }}%</span>
            </div>
        </td>
        <td>
            <a href="{{ route('admin.student-progress.show', $student->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-eye me-1"></i>
                عرض التفاصيل
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-5">
            <i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>
            <h5 class="mb-2">لا يوجد طلاب</h5>
            <p class="text-muted">لم يتم العثور على أي طلاب</p>
        </td>
    </tr>
@endforelse
