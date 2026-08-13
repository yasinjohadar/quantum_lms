@php
    $icon = getNotificationIcon($notification->type);
    $color = getNotificationColor($notification->type);
@endphp

<div class="notification-item student-notif-card {{ !$notification->is_read ? 'student-notif-card--unread' : '' }} {{ $notification->action_url ? 'student-notif-card--clickable' : '' }}"
     data-notification-id="{{ $notification->id }}"
     data-is-read="{{ $notification->is_read ? 'true' : 'false' }}"
     @if($notification->action_url) data-action-url="{{ $notification->action_url }}" @endif>
    <span class="student-notif-card__accent" aria-hidden="true"></span>

    <div class="student-notif-card__icon bg-{{ $color }}-transparent">
        <i class="fe fe-{{ $icon }} text-{{ $color }}"></i>
    </div>

    <div class="student-notif-card__body">
        <div class="student-notif-card__head">
            <div class="student-notif-card__title-row">
                @if(!$notification->is_read)
                    <span class="student-notif-card__badge-new">جديد</span>
                @endif
                <h6 class="student-notif-card__title">{{ $notification->title }}</h6>
            </div>
            <div class="student-notif-card__actions">
                @if(!$notification->is_read)
                    <button type="button"
                            class="btn btn-outline-primary mark-read-btn"
                            data-notification-id="{{ $notification->id }}"
                            title="تحديد كمقروء">
                        <i class="fe fe-check"></i>
                    </button>
                @else
                    <button type="button"
                            class="btn btn-outline-secondary mark-unread-btn"
                            data-notification-id="{{ $notification->id }}"
                            title="تحديد كغير مقروء">
                        <i class="fe fe-mail"></i>
                    </button>
                @endif
                <button type="button"
                        class="btn btn-outline-danger delete-notification-btn"
                        data-notification-id="{{ $notification->id }}"
                        title="حذف">
                    <i class="fe fe-trash-2"></i>
                </button>
            </div>
        </div>

        <p class="student-notif-card__message">{{ $notification->message }}</p>

        <div class="student-notif-card__meta">
            <span><i class="fe fe-tag"></i>{{ $types[$notification->type] ?? $notification->type }}</span>
            <span><i class="fe fe-clock"></i>{{ $notification->created_at->diffForHumans() }}</span>
            @if($notification->is_read && $notification->read_at)
                <span><i class="fe fe-check-circle"></i>قرأت {{ $notification->read_at->diffForHumans() }}</span>
            @endif
        </div>

        @if(!empty($notification->data))
            <div class="student-notif-card__tags">
                @if(isset($notification->data['points']))
                    <span class="badge bg-warning-transparent text-warning">
                        <i class="fe fe-star me-1"></i>{{ $notification->data['points'] }} نقطة
                    </span>
                @endif
                @if(isset($notification->data['percentage']))
                    <span class="badge bg-info-transparent text-info">
                        <i class="fe fe-percent me-1"></i>{{ $notification->data['percentage'] }}%
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>
