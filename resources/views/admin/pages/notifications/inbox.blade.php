@extends('admin.layouts.master')

@section('page-title')
    صندوق الإشعارات
@stop

@php
$inboxBase = url('/admin/notifications/inbox');
function adminInboxNotificationIcon($type) {
    $icons = [
        'badge_earned' => 'award',
        'achievement_unlocked' => 'star',
        'level_up' => 'trending-up',
        'points_earned' => 'plus-circle',
        'challenge_completed' => 'target',
        'reward_claimed' => 'gift',
        'leaderboard_update' => 'bar-chart-2',
        'task_completed' => 'check-circle',
        'custom_notification' => 'bell',
        'lesson_attended' => 'book-open',
        'lesson_completed' => 'check-square',
        'quiz_completed' => 'edit-3',
        'question_answered' => 'help-circle',
        'lesson_review_submitted' => 'book-open',
        'lesson_review_approved' => 'check-circle',
        'lesson_review_rejected' => 'x-circle',
        'lesson_review_submit_ack' => 'send',
        'quiz_review_submitted' => 'edit-3',
        'quiz_review_approved' => 'check-circle',
        'quiz_review_rejected' => 'x-circle',
        'quiz_review_submit_ack' => 'send',
        'student_lesson_available' => 'play-circle',
        'student_quiz_available' => 'edit-2',
        'class_enrollment_decision' => 'users',
    ];
    return $icons[$type] ?? 'bell';
}

function adminInboxNotificationColor($type) {
    $colors = [
        'badge_earned' => 'warning',
        'achievement_unlocked' => 'success',
        'level_up' => 'primary',
        'points_earned' => 'info',
        'challenge_completed' => 'danger',
        'reward_claimed' => 'purple',
        'leaderboard_update' => 'orange',
        'task_completed' => 'success',
        'custom_notification' => 'primary',
        'lesson_attended' => 'info',
        'lesson_completed' => 'success',
        'quiz_completed' => 'warning',
        'question_answered' => 'secondary',
        'lesson_review_submitted' => 'warning',
        'lesson_review_approved' => 'success',
        'lesson_review_rejected' => 'danger',
        'lesson_review_submit_ack' => 'info',
        'quiz_review_submitted' => 'warning',
        'quiz_review_approved' => 'success',
        'quiz_review_rejected' => 'danger',
        'quiz_review_submit_ack' => 'info',
        'student_lesson_available' => 'primary',
        'student_quiz_available' => 'success',
        'class_enrollment_decision' => 'primary',
    ];
    return $colors[$type] ?? 'primary';
}
@endphp

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">صندوق الإشعارات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">صندوق الإشعارات</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-primary-transparent rounded-circle me-3">
                                <i class="fe fe-bell fs-20"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted">إجمالي الإشعارات</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['total'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-danger-transparent rounded-circle me-3">
                                <i class="fe fe-mail fs-20"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted">غير المقروءة</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['unread'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-success-transparent rounded-circle me-3">
                                <i class="fe fe-check-circle fs-20"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted">المقروءة</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['read'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">قائمة الإشعارات</h3>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($stats['unread'] > 0)
                            <form action="{{ route('admin.notifications.inbox.read-all') }}" method="POST" class="d-inline" id="mark-all-read-form">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fe fe-check"></i> تحديد الكل كمقروء ({{ $stats['unread'] }})
                                </button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                                <i class="fe fe-refresh-cw"></i> تحديث
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">فلترة حسب النوع</label>
                                <select class="form-select" id="type-filter" onchange="filterNotifications()">
                                    <option value="all" {{ $currentType === 'all' ? 'selected' : '' }}>جميع الأنواع</option>
                                    @foreach($typeStats as $typeKey => $typeStat)
                                        @if($typeStat['total'] > 0)
                                        <option value="{{ $typeKey }}" {{ $currentType === $typeKey ? 'selected' : '' }}>
                                            {{ $typeStat['name'] }} ({{ $typeStat['total'] }})
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">فلترة حسب الحالة</label>
                                <select class="form-select" id="status-filter" onchange="filterNotifications()">
                                    <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="unread" {{ $currentStatus === 'unread' ? 'selected' : '' }}>غير المقروءة ({{ $stats['unread'] }})</option>
                                    <option value="read" {{ $currentStatus === 'read' ? 'selected' : '' }}>المقروءة ({{ $stats['read'] }})</option>
                                </select>
                            </div>
                        </div>

                        @if($notifications->count() > 0)
                            <div class="notifications-list">
                                @foreach($notifications as $notification)
                                <div class="notification-item card mb-3 {{ !$notification->is_read ? 'border-primary border-start border-3' : '' }}"
                                     data-notification-id="{{ $notification->id }}"
                                     data-is-read="{{ $notification->is_read ? 'true' : 'false' }}">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="avatar avatar-md me-3 flex-shrink-0">
                                                <span class="avatar-initial rounded-circle bg-{{ adminInboxNotificationColor($notification->type) }}-transparent">
                                                    <i class="fe fe-{{ adminInboxNotificationIcon($notification->type) }} text-{{ adminInboxNotificationColor($notification->type) }}"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">
                                                            @if(!$notification->is_read)
                                                                <span class="badge bg-primary me-2">جديد</span>
                                                            @endif
                                                            {{ $notification->title }}
                                                        </h6>
                                                        <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                                        @if($notification->actor_name)
                                                            <p class="mb-0 small text-muted">
                                                                <i class="fe fe-user me-1"></i>من: {{ $notification->actor_name }}
                                                                @if($notification->actor_role)
                                                                    <span class="opacity-75">({{ $notification->actor_role }})</span>
                                                                @endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        @if(!$notification->is_read)
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary mark-read-btn"
                                                                data-notification-id="{{ $notification->id }}"
                                                                title="تحديد كمقروء">
                                                            <i class="fe fe-check"></i>
                                                        </button>
                                                        @else
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary mark-unread-btn"
                                                                data-notification-id="{{ $notification->id }}"
                                                                title="تحديد كغير مقروء">
                                                            <i class="fe fe-mail"></i>
                                                        </button>
                                                        @endif
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger delete-notification-btn"
                                                                data-notification-id="{{ $notification->id }}"
                                                                title="حذف">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center flex-wrap gap-3 text-muted small">
                                                    <span>
                                                        <i class="fe fe-tag me-1"></i>
                                                        {{ $types[$notification->type] ?? $notification->type }}
                                                    </span>
                                                    <span>
                                                        <i class="fe fe-clock me-1"></i>
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </span>
                                                    @if($notification->is_read && $notification->read_at)
                                                    <span>
                                                        <i class="fe fe-check-circle me-1"></i>
                                                        قرأت {{ $notification->read_at->diffForHumans() }}
                                                    </span>
                                                    @endif
                                                    @if($notification->action_url)
                                                    <a href="{{ $notification->action_url }}" class="text-primary">فتح الرابط</a>
                                                    @endif
                                                </div>
                                                @if(!empty($notification->data))
                                                <div class="mt-2">
                                                    @if(isset($notification->data['points']))
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fe fe-star me-1"></i>
                                                        {{ $notification->data['points'] }} نقطة
                                                    </span>
                                                    @endif
                                                    @if(isset($notification->data['percentage']))
                                                    <span class="badge bg-info">
                                                        <i class="fe fe-percent me-1"></i>
                                                        {{ $notification->data['percentage'] }}%
                                                    </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $notifications->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
                                    <i class="fe fe-bell-off fs-40 text-muted"></i>
                                </div>
                                <h5 class="text-muted">لا توجد إشعارات</h5>
                                <p class="text-muted mb-0">لا توجد إشعارات تطابق الفلتر المحدد</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="avatar avatar-xl bg-danger-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                        <i class="fe fe-trash-2 fs-1 text-danger"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3" id="confirmDeleteModalLabel">تأكيد الحذف</h5>
                <p class="text-muted mb-4">هل أنت متأكد من حذف هذا الإشعار؟ لا يمكن التراجع عن هذا الإجراء.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fe fe-trash-2 me-1"></i> حذف
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmMarkAllReadModal" tabindex="-1" aria-labelledby="confirmMarkAllReadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="avatar avatar-xl bg-primary-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                        <i class="fe fe-check-circle fs-1 text-primary"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3" id="confirmMarkAllReadModalLabel">تأكيد التحديد</h5>
                <p class="text-muted mb-4">هل أنت متأكد من تحديد جميع الإشعارات كمقروءة؟</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmMarkAllReadBtn">
                        <i class="fe fe-check-circle me-1"></i> تأكيد
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@push('styles')
<style>
    .notifications-list { position: relative; overflow: visible !important; max-height: none !important; display: block; width: 100%; }
    .notification-item { position: relative; z-index: 1; margin-bottom: 1rem !important; display: block; width: 100%; overflow: visible !important; }
    .notification-item .card { overflow: visible !important; position: relative; }
    .notification-item .card-body { position: relative; overflow: visible !important; width: 100%; padding: 1rem; }
    .main-content.app-content { overflow: visible !important; position: relative; min-height: auto; }
    .container-fluid { overflow: visible !important; position: relative; padding-bottom: 2rem; }
    .row { overflow: visible !important; }
    .col-12 { overflow: visible !important; }
    .page { overflow: visible !important; position: relative; }
    .mt-4 { overflow: visible !important; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const inboxBase = @json($inboxBase);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function filterNotifications() {
        const type = document.getElementById('type-filter').value;
        const status = document.getElementById('status-filter').value;
        const url = new URL(window.location.href);
        url.searchParams.set('type', type);
        url.searchParams.set('status', status);
        window.location.href = url.toString();
    }
    window.filterNotifications = filterNotifications;

    document.addEventListener('click', function(e) {
        if (e.target.closest('.mark-read-btn')) {
            markAsRead(e.target.closest('.mark-read-btn'));
        }
        if (e.target.closest('.mark-unread-btn')) {
            markAsUnread(e.target.closest('.mark-unread-btn'));
        }
        if (e.target.closest('.delete-notification-btn')) {
            deleteNotification(e.target.closest('.delete-notification-btn'));
        }
    });

    function markAsRead(btn) {
        const id = btn.dataset.notificationId;
        fetch(`${inboxBase}/${id}/read`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const item = btn.closest('.notification-item');
            item.classList.remove('border-primary', 'border-start', 'border-3');
            const badge = item.querySelector('.badge.bg-primary');
            if (badge) badge.remove();
            btn.outerHTML = `<button type="button" class="btn btn-sm btn-outline-secondary mark-unread-btn" data-notification-id="${id}" title="تحديد كغير مقروء"><i class="fe fe-mail"></i></button>`;
            updateHeaderUnread();
        })
        .catch(() => alert('حدث خطأ أثناء تحديث الإشعار'));
    }

    function markAsUnread(btn) {
        const id = btn.dataset.notificationId;
        fetch(`${inboxBase}/${id}/unread`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const item = btn.closest('.notification-item');
            item.classList.add('border-primary', 'border-start', 'border-3');
            const h6 = item.querySelector('h6.mb-1');
            if (h6 && !h6.querySelector('.badge.bg-primary')) {
                h6.insertAdjacentHTML('afterbegin', '<span class="badge bg-primary me-2">جديد</span>');
            }
            btn.outerHTML = `<button type="button" class="btn btn-sm btn-outline-primary mark-read-btn" data-notification-id="${id}" title="تحديد كمقروء"><i class="fe fe-check"></i></button>`;
            updateHeaderUnread();
        })
        .catch(() => alert('حدث خطأ أثناء تحديث الإشعار'));
    }

    let pendingDelete = null;
    function deleteNotification(btn) {
        pendingDelete = btn;
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    }

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (!pendingDelete) return;
        const id = pendingDelete.dataset.notificationId;
        const btn = pendingDelete;
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'))?.hide();
        fetch(`${inboxBase}/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const item = btn.closest('.notification-item');
            item.style.opacity = '0';
            setTimeout(() => {
                item.remove();
                if (!document.querySelectorAll('.notification-item').length) location.reload();
            }, 300);
            updateHeaderUnread();
        })
        .catch(() => alert('حدث خطأ أثناء حذف الإشعار'))
        .finally(() => { pendingDelete = null; });
    });

    function updateHeaderUnread() {
        fetch(`${inboxBase}/unread-count`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const cnt = data.count ?? 0;
                const txt = document.getElementById('notification-count-text');
                if (txt) txt.textContent = cnt;
                const badge = document.getElementById('notification-badge-count');
                if (badge) {
                    if (cnt > 0) { badge.textContent = cnt > 99 ? '99+' : cnt; badge.style.display = 'block'; }
                    else { badge.style.display = 'none'; }
                }
                const pulse = document.querySelector('.main-header-notification .pulse-success');
                if (pulse) pulse.style.display = cnt > 0 ? 'block' : 'none';
            })
            .catch(() => {});
    }

    window.refreshNotifications = function() { window.location.reload(); };

    document.getElementById('mark-all-read-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        new bootstrap.Modal(document.getElementById('confirmMarkAllReadModal')).show();
    });
    document.getElementById('confirmMarkAllReadBtn')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmMarkAllReadModal'))?.hide();
        document.getElementById('mark-all-read-form')?.submit();
    });
})();
</script>
@endpush
