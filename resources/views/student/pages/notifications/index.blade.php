@extends('student.layouts.master')

@section('page-title')
    الإشعارات
@stop

@php
// Helper functions for notification display
function getNotificationIcon($type) {
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
    ];
    return $icons[$type] ?? 'bell';
}

function getNotificationColor($type) {
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
    ];
    return $colors[$type] ?? 'primary';
}
@endphp

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">
        <nav class="student-content-breadcrumb mb-3" aria-label="مسار التنقل">
            <ol class="student-content-breadcrumb__trail">
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-bell-fill"></i>
                        <span>الإشعارات</span>
                    </span>
                </li>
            </ol>
            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-bell me-2 text-warning"></i>الإشعارات
            </h1>
            <p class="student-content-breadcrumb__meta mb-0">تابع آخر التحديثات والتنبيهات الخاصة بك</p>
        </nav>

        <div class="student-notif-stats">
            <div class="student-notif-stat student-notif-stat--total">
                <div class="student-notif-stat__icon"><i class="fe fe-bell"></i></div>
                <div>
                    <div class="student-notif-stat__label">إجمالي الإشعارات</div>
                    <div class="student-notif-stat__value" id="notif-stat-total">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="student-notif-stat student-notif-stat--unread">
                <div class="student-notif-stat__icon"><i class="fe fe-mail"></i></div>
                <div>
                    <div class="student-notif-stat__label">غير المقروءة</div>
                    <div class="student-notif-stat__value" id="notif-stat-unread">{{ $stats['unread'] }}</div>
                </div>
            </div>
            <div class="student-notif-stat student-notif-stat--read">
                <div class="student-notif-stat__icon"><i class="fe fe-check-circle"></i></div>
                <div>
                    <div class="student-notif-stat__label">المقروءة</div>
                    <div class="student-notif-stat__value" id="notif-stat-read">{{ $stats['read'] }}</div>
                </div>
            </div>
        </div>

        <div class="student-notif-realtime">
            @include('partials.echo-realtime-settings')
        </div>

        <div class="card dashboard-panel student-notif-panel">
            <div class="card-header border-bottom-0 pb-0">
                <div class="student-notif-toolbar">
                    <div class="student-notif-filters">
                        <select class="form-select form-select-sm" id="type-filter" onchange="filterNotifications()">
                            <option value="all" {{ $currentType === 'all' ? 'selected' : '' }}>جميع الأنواع</option>
                            @foreach($typeStats as $typeKey => $typeStat)
                                @if($typeStat['total'] > 0)
                                    <option value="{{ $typeKey }}" {{ $currentType === $typeKey ? 'selected' : '' }}>
                                        {{ $typeStat['name'] }} ({{ $typeStat['total'] }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm" id="status-filter" onchange="filterNotifications()">
                            <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>الكل</option>
                            <option value="unread" {{ $currentStatus === 'unread' ? 'selected' : '' }}>غير المقروءة ({{ $stats['unread'] }})</option>
                            <option value="read" {{ $currentStatus === 'read' ? 'selected' : '' }}>المقروءة ({{ $stats['read'] }})</option>
                        </select>
                    </div>
                    <div class="student-notif-actions">
                        @if($stats['unread'] > 0)
                            <form action="{{ route('student.notifications.read-all') }}" method="POST" class="d-inline" id="mark-all-read-form">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fe fe-check"></i>
                                    <span class="d-none d-sm-inline">تحديد الكل كمقروء</span>
                                    ({{ $stats['unread'] }})
                                </button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                            <i class="fe fe-refresh-cw"></i>
                            <span class="d-none d-sm-inline">تحديث</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                @if($notifications->count() > 0)
                    <div class="notifications-list">
                        @foreach($notifications as $notification)
                            @include('student.pages.notifications.partials.notification-item', [
                                'notification' => $notification,
                                'types' => $types,
                            ])
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $notifications->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="student-notif-empty">
                        <div class="student-notif-empty__icon">
                            <i class="fe fe-bell-off"></i>
                        </div>
                        <h5 class="mb-2 text-muted">لا توجد إشعارات</h5>
                        <p class="text-muted mb-0">لا توجد إشعارات تطابق الفلتر المحدد</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal لتأكيد حذف الإشعار -->
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

<!-- Modal لتأكيد تحديد جميع الإشعارات كمقروءة -->
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
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.notifications.partials.notifications-page-styles')
    <style>
        .student-notif-realtime .card {
            border: none;
            box-shadow: none;
            margin-bottom: 0 !important;
            background: transparent;
        }
        .student-notif-realtime .card-body {
            padding: 0.85rem 1rem;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Helper functions
    function getNotificationColor(type) {
        const colorMap = {
            'badge_earned': 'primary',
            'achievement_unlocked': 'warning',
            'level_up': 'success',
            'challenge_completed': 'info',
            'reward_claimed': 'secondary',
            'lesson_attended': 'success',
            'lesson_completed': 'success',
            'quiz_started': 'info',
            'quiz_completed': 'primary',
            'question_answered': 'primary',
            'task_completed': 'success',
            'points_awarded': 'warning',
        };
        return colorMap[type] || 'primary';
    }

    function getNotificationIcon(type) {
        const iconMap = {
            'badge_earned': 'award',
            'achievement_unlocked': 'trophy',
            'level_up': 'trending-up',
            'challenge_completed': 'flag',
            'reward_claimed': 'gift',
            'lesson_attended': 'calendar',
            'lesson_completed': 'check-circle',
            'quiz_started': 'play-circle',
            'quiz_completed': 'check-circle',
            'question_answered': 'help-circle',
            'task_completed': 'check-square',
            'points_awarded': 'star',
        };
        return iconMap[type] || 'bell';
    }

    // Filter notifications
    function filterNotifications() {
        const type = document.getElementById('type-filter').value;
        const status = document.getElementById('status-filter').value;
        const url = new URL(window.location.href);
        url.searchParams.set('type', type);
        url.searchParams.set('status', status);
        window.location.href = url.toString();
    }

    // Mark as read
    document.addEventListener('click', function(e) {
        if (e.target.closest('.mark-read-btn')) {
            const btn = e.target.closest('.mark-read-btn');
            const notificationId = btn.dataset.notificationId;
            markAsRead(notificationId, btn);
        }
        
        if (e.target.closest('.mark-unread-btn')) {
            const btn = e.target.closest('.mark-unread-btn');
            const notificationId = btn.dataset.notificationId;
            markAsUnread(notificationId, btn);
        }
        
        if (e.target.closest('.delete-notification-btn')) {
            const btn = e.target.closest('.delete-notification-btn');
            const notificationId = btn.dataset.notificationId;
            deleteNotification(notificationId, btn);
        }
    });

    function updateStatsCounters() {
        const unread = document.querySelectorAll('.notification-item[data-is-read="false"]').length;
        const read = document.querySelectorAll('.notification-item[data-is-read="true"]').length;
        const total = unread + read;

        const unreadEl = document.getElementById('notif-stat-unread');
        const readEl = document.getElementById('notif-stat-read');
        const totalEl = document.getElementById('notif-stat-total');

        if (unreadEl) unreadEl.textContent = unread;
        if (readEl) readEl.textContent = read;
        if (totalEl) totalEl.textContent = total;
    }

    function markAsRead(notificationId, btn) {
        fetch(`/student/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = btn.closest('.notification-item');
                item.classList.remove('student-notif-card--unread');
                item.dataset.isRead = 'true';

                const badge = item.querySelector('.student-notif-card__badge-new');
                if (badge) badge.remove();

                btn.outerHTML = `
                    <button type="button"
                            class="btn btn-outline-secondary mark-unread-btn"
                            data-notification-id="${notificationId}"
                            title="تحديد كغير مقروء">
                        <i class="fe fe-mail"></i>
                    </button>
                `;

                updateStatsCounters();
                updateNotificationCount();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تحديث الإشعار');
        });
    }

    function markAsUnread(notificationId, btn) {
        fetch(`/student/notifications/${notificationId}/unread`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = btn.closest('.notification-item');
                item.classList.add('student-notif-card--unread');
                item.dataset.isRead = 'false';

                const titleRow = item.querySelector('.student-notif-card__title-row');
                if (titleRow && !titleRow.querySelector('.student-notif-card__badge-new')) {
                    const badge = document.createElement('span');
                    badge.className = 'student-notif-card__badge-new';
                    badge.textContent = 'جديد';
                    titleRow.insertBefore(badge, titleRow.firstChild);
                }

                btn.outerHTML = `
                    <button type="button"
                            class="btn btn-outline-primary mark-read-btn"
                            data-notification-id="${notificationId}"
                            title="تحديد كمقروء">
                        <i class="fe fe-check"></i>
                    </button>
                `;

                updateStatsCounters();
                updateNotificationCount();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تحديث الإشعار');
        });
    }

    let pendingDeleteNotificationId = null;
    let pendingDeleteBtn = null;

    function deleteNotification(notificationId, btn) {
        pendingDeleteNotificationId = notificationId;
        pendingDeleteBtn = btn;
        
        // إظهار المودال
        const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    }
    
    function processDeleteNotification() {
        if (!pendingDeleteNotificationId || !pendingDeleteBtn) {
            return;
        }
        
        const notificationId = pendingDeleteNotificationId;
        const btn = pendingDeleteBtn;
        
        // إغلاق المودال
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
        if (modal) modal.hide();
        
        fetch(`/student/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = btn.closest('.notification-item');
                item.style.transition = 'opacity 0.3s, transform 0.3s';
                item.style.opacity = '0';
                item.style.transform = 'translateX(12px)';
                setTimeout(() => {
                    item.remove();
                    updateStatsCounters();
                    if (document.querySelectorAll('.notification-item').length === 0) {
                        location.reload();
                    }
                }, 300);

                updateNotificationCount();
            } else {
                alert('حدث خطأ أثناء حذف الإشعار');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حذف الإشعار');
        })
        .finally(() => {
            pendingDeleteNotificationId = null;
            pendingDeleteBtn = null;
        });
    }

    function updateNotificationCount() {
        fetch('/student/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                // Update header count if exists
                const countElement = document.getElementById('notification-count-text');
                if (countElement) {
                    countElement.textContent = data.count;
                }
            });
    }

    function refreshNotifications() {
        window.location.reload();
    }

    // Mark all as read form
    document.getElementById('mark-all-read-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // إظهار المودال
        const modal = new bootstrap.Modal(document.getElementById('confirmMarkAllReadModal'));
        modal.show();
    });
    
    // تأكيد تحديد جميع الإشعارات كمقروءة
    document.getElementById('confirmMarkAllReadBtn')?.addEventListener('click', function() {
        // إغلاق المودال
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmMarkAllReadModal'));
        if (modal) modal.hide();
        
        // إرسال النموذج
        document.getElementById('mark-all-read-form').submit();
    });
    
    // تأكيد حذف الإشعار
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        processDeleteNotification();
    });
</script>
@endpush
