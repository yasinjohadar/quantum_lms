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
        'certificate_earned' => 'file-text',
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
        'certificate_earned' => 'teal',
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
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الإشعارات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإشعارات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Statistics Cards -->
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

        <!-- Filters and Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">الإشعارات</h3>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($stats['unread'] > 0)
                            <form action="{{ route('student.notifications.read-all') }}" method="POST" class="d-inline" id="mark-all-read-form">
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
                        <!-- Filters -->
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

                        <!-- Notifications List -->
                        @if($notifications->count() > 0)
                            <div class="notifications-list">
                                @foreach($notifications as $notification)
                                <div class="notification-item card mb-3 {{ !$notification->is_read ? 'border-primary border-start border-3' : '' }}" 
                                     data-notification-id="{{ $notification->id }}"
                                     data-is-read="{{ $notification->is_read ? 'true' : 'false' }}">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <!-- Icon -->
                                            <div class="avatar avatar-md me-3 flex-shrink-0">
                                                <span class="avatar-initial rounded-circle bg-{{ getNotificationColor($notification->type) }}-transparent">
                                                    <i class="fe fe-{{ getNotificationIcon($notification->type) }} text-{{ getNotificationColor($notification->type) }}"></i>
                                                </span>
                                            </div>
                                            
                                            <!-- Content -->
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
                                                
                                                <!-- Meta Info -->
                                                <div class="d-flex align-items-center gap-3 text-muted small">
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
                                                </div>
                                                
                                                <!-- Additional Data -->
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

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $notifications->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
                                    <i class="fe fe-bell-off fs-40 text-muted"></i>
                                </div>
                                <h5 class="text-muted">لا توجد إشعارات</h5>
                                <p class="text-muted">لا توجد إشعارات تطابق الفلتر المحدد</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

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
<style>
    /* إصلاح مشكلة ظهور الإشعارات خارج القائمة */
    .notifications-list {
        position: relative;
        overflow: visible !important;
        max-height: none !important;
        min-height: auto;
        display: block;
        width: 100%;
    }
    
    .notification-item {
        position: relative;
        z-index: 1;
        margin-bottom: 1rem !important;
        display: block;
        width: 100%;
        overflow: visible !important;
    }
    
    .notification-item .card {
        overflow: visible !important;
        position: relative;
    }
    
    .notification-item .card-body {
        position: relative;
        overflow: visible !important;
        width: 100%;
        padding: 1rem;
    }
    
    /* إصلاح الـ card الرئيسية */
    .card:not(.notification-item) {
        overflow: visible !important;
    }
    
    .card-body:not(.notification-item .card-body) {
        overflow: visible !important;
    }
    
    /* التأكد من أن المحتوى لا يخرج من الـ container */
    .main-content.app-content {
        overflow: visible !important;
        position: relative;
        min-height: auto;
    }
    
    .container-fluid {
        overflow: visible !important;
        position: relative;
        padding-bottom: 2rem;
    }
    
    /* إصلاح أي مشاكل في الـ row */
    .row {
        overflow: visible !important;
    }
    
    .col-12 {
        overflow: visible !important;
    }
    
    /* التأكد من أن الـ page container لا يقطع المحتوى */
    .page {
        overflow: visible !important;
        position: relative;
    }
    
    /* إصلاح الـ pagination */
    .mt-4 {
        overflow: visible !important;
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
                item.classList.remove('border-primary', 'border-start', 'border-3');
                item.dataset.isRead = 'true';
                
                // Update badge
                const badge = item.querySelector('.badge.bg-primary');
                if (badge) badge.remove();
                
                // Update button
                btn.outerHTML = `
                    <button type="button" 
                            class="btn btn-sm btn-outline-secondary mark-unread-btn" 
                            data-notification-id="${notificationId}"
                            title="تحديد كغير مقروء">
                        <i class="fe fe-mail"></i>
                    </button>
                `;
                
                // Update notification count
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
                item.classList.add('border-primary', 'border-start', 'border-3');
                item.dataset.isRead = 'false';
                
                // Add badge
                const title = item.querySelector('h6');
                title.innerHTML = `<span class="badge bg-primary me-2">جديد</span>${title.textContent.trim()}`;
                
                // Update button
                btn.outerHTML = `
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary mark-read-btn" 
                            data-notification-id="${notificationId}"
                            title="تحديد كمقروء">
                        <i class="fe fe-check"></i>
                    </button>
                `;
                
                // Update notification count
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
                item.style.transition = 'opacity 0.3s';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    // Check if list is empty
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
