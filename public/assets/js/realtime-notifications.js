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
        const dropdown = document.querySelector('#header-notification-scroll');
        if (!dropdown) {
            return;
        }

        // Hide "no notifications" message
        const noNotificationsMsg = document.getElementById('no-notifications-message');
        if (noNotificationsMsg) {
            noNotificationsMsg.style.display = 'none';
        }

        // Create notification item
        const notificationItem = document.createElement('li');
        notificationItem.className = 'dropdown-item';
        notificationItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-${data.color || 'primary'}">
                        <i class="${data.icon || 'fe fe-bell'}"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0">${this.escapeHtml(data.title)}</h6>
                    <p class="mb-0 text-muted">${this.escapeHtml(data.message)}</p>
                    <small class="text-muted">${this.formatTime(data.timestamp)}</small>
                </div>
            </div>
        `;

        // Add to top of dropdown
        const firstItem = dropdown.querySelector('li.dropdown-item');
        if (firstItem && firstItem.id !== 'no-notifications-message') {
            dropdown.insertBefore(notificationItem, firstItem);
        } else {
            dropdown.appendChild(notificationItem);
        }

        // Limit to 10 notifications
        const items = dropdown.querySelectorAll('li.dropdown-item:not(#no-notifications-message)');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
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
                
                // Update count text
                const countTextElement = document.getElementById('notification-count-text');
                if (countTextElement) {
                    countTextElement.textContent = count;
                }

                // Update pulse badge
                const badgeElement = document.querySelector('.main-header-notification .pulse-success');
                if (badgeElement) {
                    badgeElement.style.display = count > 0 ? 'block' : 'none';
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

