@forelse ($logs as $log)
    @php
        $eventKey = \Illuminate\Support\Str::afterLast($log->event_type, '.');
        $eventColors = ['created' => 'active', 'updated' => 'pending', 'deleted' => 'inactive', 'force_deleted' => 'inactive', 'restored' => 'pending'];
        $rowNum = $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage();
    @endphp
    <tr>
        <th scope="row" class="text-muted small">{{ $rowNum }}</th>
        <td>
            @if($log->user)
                <div class="ui-user-cell">
                    <div class="ui-user-avatar">{{ mb_strtoupper(mb_substr(trim($log->user->name), 0, 1)) }}</div>
                    <div>
                        <div class="ui-user-name">{{ $log->user->name }}</div>
                        <small class="text-muted">{{ $log->causer_role ?? '-' }}</small>
                    </div>
                </div>
            @else
                <span class="text-muted">غير معروف</span>
            @endif
        </td>
        <td><span class="ui-class-pill ui-class-pill--approved">{{ $subjectLabels[$log->subject_type] ?? $log->subject_type }}</span></td>
        <td class="fw-semibold">{{ $log->subject_label }}</td>
        <td>
            <span class="ui-status-badge ui-status-badge--{{ $eventColors[$eventKey] ?? 'inactive' }}">
                {{ $eventLabels[$eventKey] ?? $eventKey }}
            </span>
        </td>
        <td class="text-muted small">{{ $log->occurred_at->format('Y-m-d H:i:s') }}</td>
        <td>
            <a href="{{ route('admin.activity-log.show', $log->id) }}" class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                <i class="bi bi-eye"></i>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7">
            <div class="activity-log-empty">
                <i class="bi bi-inboxes"></i>
                لا توجد نشاطات مطابقة
            </div>
        </td>
    </tr>
@endforelse
