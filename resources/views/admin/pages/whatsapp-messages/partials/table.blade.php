@forelse($messages as $message)
    <tr>
        <td>
            @if($message->direction === 'outbound')
                <input type="checkbox" class="form-check-input message-checkbox" value="{{ $message->id }}">
            @endif
        </td>
        <td>{{ $message->id }}</td>
        <td>
            @if($message->direction === 'inbound')
                <span class="badge bg-info">واردة</span>
            @else
                <span class="badge bg-primary">صادرة</span>
            @endif
        </td>
        <td>{{ $message->contact->wa_id ?? '-' }}</td>
        <td>{{ \Illuminate\Support\Str::limit($message->body ?? '-', 50) }}</td>
        <td>
            @if($message->status === 'sent')
                <span class="badge bg-success">مرسل</span>
            @elseif($message->status === 'delivered')
                <span class="badge bg-info">مستلم</span>
            @elseif($message->status === 'read')
                <span class="badge bg-primary">مقروء</span>
            @elseif($message->status === 'failed')
                <span class="badge bg-danger">فشل</span>
            @else
                <span class="badge bg-warning">في الانتظار</span>
            @endif
        </td>
        <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
        <td>
            <a href="{{ route('admin.whatsapp-messages.show', $message) }}" class="btn btn-sm btn-info" title="عرض">
                <i class="fas fa-eye"></i>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">لا توجد رسائل</td>
    </tr>
@endforelse
