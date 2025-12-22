/**
 * Real-time Notifications using Server-Sent Events (SSE)
 */
class RealTimeNotifications {
    constructor(options = {}) {
        this.userId = options.userId || null;
        this.streamUrl = options.streamUrl || '/student/notifications/stream';
        this.eventSource = null;
        this.reconnectInterval = options.reconnectInterval || 5000;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
        this.onNotificationCallback = options.onNotification || null;
        this.onErrorCallback = options.onError || null;
        this.notificationSound = options.sound || false;
        
        this.init();
    }

    /**
     * Initialize SSE connection
     */
    init() {
        if (this.eventSource && this.eventSource.readyState !== EventSource.CLOSED) {
            return; // Already connected
        }

        try {
            this.eventSource = new EventSource(this.streamUrl);
            
            // Handle incoming notifications
            this.eventSource.addEventListener('notification', (event) => {
                const data = JSON.parse(event.data);
                this.handleNotification(data);
            });

            // Handle ping messages
            this.eventSource.addEventListener('ping', (event) => {
                // Just acknowledge the ping
                console.debug('SSE Ping received');
            });

            // Handle connection open
            this.eventSource.onopen = () => {
                console.log('SSE Connection opened');
                this.reconnectAttempts = 0;
            };

            // Handle errors
            this.eventSource.onerror = (error) => {
                console.error('SSE Error:', error);
                this.handleError(error);
            };

        } catch (error) {
            console.error('Failed to initialize SSE:', error);
            this.handleError(error);
        }
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
        } else {
            // Fallback to browser notification
            this.showBrowserNotification(data);
        }

        // Add to notification dropdown if exists
        this.addToNotificationDropdown(data);
    }

    /**
     * Show browser notification (if permission granted)
     */
    showBrowserNotification(data) {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'granted') {
            new Notification(data.title, {
                body: data.message,
                icon: '/assets/images/logo.png',
                badge: '/assets/images/logo.png',
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification(data.title, {
                        body: data.message,
                        icon: '/assets/images/logo.png',
                    });
                }
            });
        }
    }

    /**
     * Add notification to dropdown
     */
    addToNotificationDropdown(data) {
        console.log('Adding notification to dropdown:', data);
        
        const dropdown = document.querySelector('#header-notification-scroll');
        if (!dropdown) {
            console.warn('Notification dropdown not found');
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

        // Create notification item with proper styling
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

        // Add click handler to navigate to notifications page
        notificationItem.addEventListener('click', () => {
            window.location.href = '/student/notifications';
        });

        // Remove "no notifications" message if exists
        if (noNotificationsMsg && noNotificationsMsg.parentNode) {
            noNotificationsMsg.parentNode.removeChild(noNotificationsMsg);
        }

        // Add to top of dropdown
        dropdown.insertBefore(notificationItem, dropdown.firstChild);

        // Limit to 10 notifications
        const items = dropdown.querySelectorAll('li.dropdown-item');
        if (items.length > 10) {
            for (let i = 10; i < items.length; i++) {
                items[i].remove();
            }
        }

        // Update notification count immediately
        this.updateNotificationCount();
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
        fetch('/student/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                
                // Update count text in dropdown
                const countTextElement = document.getElementById('notification-count-text');
                if (countTextElement) {
                    countTextElement.textContent = count;
                    
                    // Update the text in header
                    const headerText = countTextElement.closest('.menu-header-content');
                    if (headerText) {
                        const subtext = headerText.querySelector('.subtext');
                        if (subtext) {
                            subtext.innerHTML = `لديك <span id="notification-count-text">${count}</span> إشعارات جديدة`;
                        }
                    }
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

                // Update "Mark all as read" badge text
                const markAllBadge = document.querySelector('.main-header-notification .badge.rounded-pill.bg-warning');
                if (markAllBadge && count > 0) {
                    markAllBadge.textContent = `تحديد الكل كمقروء (${count})`;
                }
            })
            .catch(error => {
                console.error('Error updating notification count:', error);
            });
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        try {
            const audio = new Audio('/assets/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(e => {
                // Ignore errors (user might not have interacted with page)
            });
        } catch (error) {
            // Sound file might not exist, ignore
        }
    }

    /**
     * Handle errors and reconnect
     */
    handleError(error) {
        if (this.onErrorCallback && typeof this.onErrorCallback === 'function') {
            this.onErrorCallback(error);
        }

        // Close current connection
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }

        // Attempt to reconnect
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
            
            setTimeout(() => {
                this.init();
            }, this.reconnectInterval);
        } else {
            console.error('Max reconnection attempts reached. Please refresh the page.');
        }
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
     * Close connection
     */
    close() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }

    /**
     * Reconnect manually
     */
    reconnect() {
        this.close();
        this.reconnectAttempts = 0;
        this.init();
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.currentUserId !== 'undefined') {
            window.realtimeNotifications = new RealTimeNotifications({
                userId: window.currentUserId,
                streamUrl: '/student/notifications/stream',
                sound: false, // Enable if you have sound file
            });
        }
    });
} else {
    if (typeof window.currentUserId !== 'undefined') {
        window.realtimeNotifications = new RealTimeNotifications({
            userId: window.currentUserId,
            streamUrl: '/student/notifications/stream',
            sound: false,
        });
    }
}

