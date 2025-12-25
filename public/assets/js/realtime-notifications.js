/**
 * Real-time Notifications using Polling (بديل أخف من SSE)
 */
class RealTimeNotifications {
    constructor(options = {}) {
        this.userId = options.userId || null;
        this.pollUrl = '/student/notifications/latest';
        this.pollInterval = options.pollInterval || 30000; // 30 ثانية
        this.pollTimer = null;
        this.onNotificationCallback = options.onNotification || null;
        this.onErrorCallback = options.onError || null;
        this.notificationSound = options.sound || false;
        this.lastNotificationId = null;
        this.isEnabled = true;
        
        this.init();
    }

    /**
     * Initialize polling
     */
    init() {
        if (!this.isEnabled) {
            console.log('Notifications disabled');
            return;
        }
        
        // Load initial notifications
        this.fetchNotifications();
        
        // Start polling
        this.startPolling();
        
        // Update count periodically
        this.updateNotificationCount();
        
        console.log('Notifications initialized (polling mode)');
    }

    /**
     * Start polling for notifications
     */
    startPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
        }
        
        this.pollTimer = setInterval(() => {
            this.fetchNotifications();
        }, this.pollInterval);
    }

    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    /**
     * Fetch notifications from server
     */
    fetchNotifications() {
        const url = this.lastNotificationId 
            ? `${this.pollUrl}?after=${this.lastNotificationId}` 
            : this.pollUrl;
            
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.notifications && Array.isArray(data.notifications)) {
                data.notifications.forEach(notification => {
                    this.handleNotification(notification);
                    if (notification.id) {
                        this.lastNotificationId = notification.id;
                    }
                });
            }
        })
        .catch(error => {
            // Silent fail - don't spam console
            console.debug('Notification fetch error:', error.message);
        });
    }

    /**
     * Handle incoming notification
     */
    handleNotification(data) {
        if (!data || !data.type) {
            return;
        }

        // Call custom callback if provided
        if (this.onNotificationCallback && typeof this.onNotificationCallback === 'function') {
            this.onNotificationCallback(data);
        }

        // Default handling
        this.showNotification(data);
        this.updateNotificationCount();
        
        if (this.notificationSound) {
            this.playNotificationSound();
        }
    }

    /**
     * Show notification in UI
     */
    showNotification(data) {
        // Try to use toastr if available
        if (typeof toastr !== 'undefined') {
            const method = data.color === 'success' ? 'success' : 
                          data.color === 'danger' ? 'error' : 
                          data.color === 'warning' ? 'warning' : 'info';
            
            toastr[method](data.message, data.title, {
                timeOut: 5000,
                closeButton: true,
                progressBar: true,
            });
        }

        // Add to notification dropdown if exists
        this.addToNotificationDropdown(data);
    }

    /**
     * Add notification to dropdown
     */
    addToNotificationDropdown(data) {
        const dropdown = document.querySelector('#header-notification-scroll');
        if (!dropdown) {
            return;
        }

        // Hide "no notifications" message
        const noNotificationsMsg = document.getElementById('no-notifications-message');
        if (noNotificationsMsg) {
            noNotificationsMsg.style.display = 'none';
        }

        // Get icon and color
        const icon = data.icon || this.getNotificationIcon(data.type);
        const color = data.color || this.getNotificationColor(data.type);
        const colorClass = color === 'success' ? 'success' : 
                          color === 'danger' ? 'danger' : 
                          color === 'warning' ? 'warning' : 
                          color === 'info' ? 'info' : 'primary';

        // Create notification item
        const notificationItem = document.createElement('li');
        notificationItem.className = 'dropdown-item';
        notificationItem.style.cssText = 'padding: 0.75rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.1); cursor: pointer;';
        notificationItem.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="avatar avatar-sm me-3 flex-shrink-0">
                    <span class="avatar-initial rounded-circle bg-${colorClass}-transparent">
                        <i class="${icon} text-${colorClass}"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold">${this.escapeHtml(data.title || '')}</h6>
                    <p class="mb-1 text-muted small">${this.escapeHtml(data.message || '')}</p>
                    <small class="text-muted">${this.formatTime(data.timestamp || data.created_at)}</small>
                </div>
            </div>
        `;

        notificationItem.addEventListener('click', () => {
            window.location.href = '/student/notifications';
        });

        // Add to top of dropdown
        dropdown.insertBefore(notificationItem, dropdown.firstChild);

        // Limit to 10 notifications
        const items = dropdown.querySelectorAll('li.dropdown-item');
        if (items.length > 10) {
            for (let i = 10; i < items.length; i++) {
                items[i].remove();
            }
        }
    }
    
    /**
     * Get notification icon by type
     */
    getNotificationIcon(type) {
        const icons = {
            'badge_earned': 'fe fe-award',
            'achievement_unlocked': 'fe fe-star',
            'level_up': 'fe fe-trending-up',
            'points_earned': 'fe fe-plus-circle',
            'challenge_completed': 'fe fe-target',
            'reward_claimed': 'fe fe-gift',
            'certificate_earned': 'fe fe-file-text',
            'leaderboard_update': 'fe fe-bar-chart-2',
            'task_completed': 'fe fe-check-circle',
            'custom_notification': 'fe fe-bell',
            'lesson_attended': 'fe fe-book-open',
            'lesson_completed': 'fe fe-check-square',
            'quiz_completed': 'fe fe-edit-3',
            'question_answered': 'fe fe-help-circle',
        };
        return icons[type] || 'fe fe-bell';
    }
    
    /**
     * Get notification color by type
     */
    getNotificationColor(type) {
        const colors = {
            'badge_earned': 'warning',
            'achievement_unlocked': 'success',
            'level_up': 'primary',
            'points_earned': 'info',
            'challenge_completed': 'danger',
            'reward_claimed': 'purple',
            'certificate_earned': 'teal',
            'leaderboard_update': 'orange',
            'task_completed': 'success',
            'custom_notification': 'primary',
            'lesson_attended': 'info',
            'lesson_completed': 'success',
            'quiz_completed': 'warning',
            'question_answered': 'secondary',
        };
        return colors[type] || 'primary';
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Update notification count badge
     */
    updateNotificationCount() {
        fetch('/student/notifications/unread-count', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            
            // Update count text in dropdown
            const countTextElement = document.getElementById('notification-count-text');
            if (countTextElement) {
                countTextElement.textContent = count;
            }

            // Update badge count next to bell icon
            const badgeCountElement = document.getElementById('notification-badge-count');
            if (badgeCountElement) {
                if (count > 0) {
                    badgeCountElement.textContent = count > 99 ? '99+' : count;
                    badgeCountElement.style.display = 'block';
                } else {
                    badgeCountElement.style.display = 'none';
                }
            }

            // Update pulse badge
            const badgeElement = document.querySelector('.main-header-notification .pulse-success');
            if (badgeElement) {
                badgeElement.style.display = count > 0 ? 'block' : 'none';
            }
        })
        .catch(error => {
            // Silent fail
        });
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        try {
            const audio = new Audio('/assets/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(e => {});
        } catch (error) {}
    }

    /**
     * Format timestamp
     */
    formatTime(timestamp) {
        if (!timestamp) {
            return '';
        }

        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (seconds < 60) {
            return 'الآن';
        } else if (minutes < 60) {
            return `منذ ${minutes} دقيقة`;
        } else if (hours < 24) {
            return `منذ ${hours} ساعة`;
        } else if (days < 7) {
            return `منذ ${days} يوم`;
        } else {
            return date.toLocaleDateString('ar-SA');
        }
    }

    /**
     * Close/stop notifications
     */
    close() {
        this.stopPolling();
        this.isEnabled = false;
    }

    /**
     * Restart notifications
     */
    reconnect() {
        this.isEnabled = true;
        this.init();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // تأخير بسيط لضمان تحميل الصفحة أولاً
    setTimeout(() => {
        if (typeof window.currentUserId !== 'undefined') {
            window.realtimeNotifications = new RealTimeNotifications({
                userId: window.currentUserId,
                pollInterval: 60000, // كل دقيقة
                sound: false,
            });
        }
    }, 3000); // انتظار 3 ثواني بعد تحميل الصفحة
});
