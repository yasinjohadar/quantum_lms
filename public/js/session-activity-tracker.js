/**
 * Session Activity Tracker
 * تتبع أنشطة الجلسة تلقائياً
 */

class SessionActivityTracker {
    constructor(sessionId, apiUrl) {
        this.sessionId = sessionId;
        this.apiUrl = apiUrl;
        this.idleTimeout = null;
        this.idleStartTime = null;
        this.isIdle = false;
        this.lastActivityTime = Date.now();
        this.idleThreshold = 5 * 60 * 1000; // 5 دقائق
        this.pageViewDebounce = null;
        
        this.init();
    }

    init() {
        // تتبع عرض الصفحة
        this.trackPageView();

        // تتبع فقدان/استعادة التركيز
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackFocusLost();
            } else {
                this.trackFocusGained();
            }
        });

        // تتبع الخمول
        this.setupIdleTracking();

        // تتبع الإجراءات
        this.setupActionTracking();

        // تتبع إغلاق الصفحة
        window.addEventListener('beforeunload', () => {
            this.trackSessionEnd();
        });

        // تتبع إعادة الاتصال
        window.addEventListener('online', () => {
            this.trackReconnect();
        });

        // تتبع الانقطاع
        window.addEventListener('offline', () => {
            this.trackDisconnect();
        });
    }

    /**
     * تتبع عرض الصفحة
     */
    trackPageView() {
        clearTimeout(this.pageViewDebounce);
        this.pageViewDebounce = setTimeout(() => {
            this.logActivity('page_view', {
                title: document.title,
                path: window.location.pathname,
            }, window.location.href);
        }, 1000);
    }

    /**
     * تتبع إجراء
     */
    trackAction(actionName, details = {}) {
        this.logActivity('action', {
            action_name: actionName,
            ...details,
        });
    }

    /**
     * تتبع فقدان التركيز
     */
    trackFocusLost() {
        if (!this.isIdle) {
            this.logActivity('focus_lost');
        }
    }

    /**
     * تتبع استعادة التركيز
     */
    trackFocusGained() {
        if (!this.isIdle) {
            this.logActivity('focus_gained');
        }
    }

    /**
     * تتبع بداية الخمول
     */
    trackIdleStart() {
        if (!this.isIdle) {
            this.isIdle = true;
            this.idleStartTime = Date.now();
            this.logActivity('idle_start');
        }
    }

    /**
     * تتبع نهاية الخمول
     */
    trackIdleEnd() {
        if (this.isIdle) {
            const idleDuration = Date.now() - this.idleStartTime;
            this.isIdle = false;
            this.logActivity('idle_end', {
                duration_seconds: Math.floor(idleDuration / 1000),
            });
        }
    }

    /**
     * تتبع الانقطاع
     */
    trackDisconnect() {
        this.logActivity('disconnect');
    }

    /**
     * تتبع إعادة الاتصال
     */
    trackReconnect() {
        this.logActivity('reconnect');
    }

    /**
     * تتبع نهاية الجلسة
     */
    trackSessionEnd() {
        // استخدام sendBeacon لإرسال البيانات حتى عند إغلاق الصفحة
        if (navigator.sendBeacon) {
            const data = JSON.stringify({
                session_id: this.sessionId,
                activity_type: 'session_end',
            });
            navigator.sendBeacon(this.apiUrl, data);
        } else {
            this.logActivity('session_end');
        }
    }

    /**
     * إعداد تتبع الخمول
     */
    setupIdleTracking() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.lastActivityTime = Date.now();
                
                if (this.isIdle) {
                    this.trackIdleEnd();
                }
                
                clearTimeout(this.idleTimeout);
                this.idleTimeout = setTimeout(() => {
                    this.trackIdleStart();
                }, this.idleThreshold);
            }, { passive: true });
        });
    }

    /**
     * إعداد تتبع الإجراءات
     */
    setupActionTracking() {
        // تتبع النقرات على الأزرار
        document.addEventListener('click', (e) => {
            if (e.target.matches('button, a.btn, input[type="submit"]')) {
                this.trackAction('button_click', {
                    element: e.target.tagName,
                    text: e.target.textContent?.trim(),
                    id: e.target.id,
                    class: e.target.className,
                });
            }
        });

        // تتبع إرسال النماذج
        document.addEventListener('submit', (e) => {
            this.trackAction('form_submit', {
                form_id: e.target.id,
                form_action: e.target.action,
            });
        });
    }

    /**
     * تسجيل نشاط
     */
    async logActivity(activityType, details = null, pageUrl = null) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    activity_type: activityType,
                    activity_details: details,
                    page_url: pageUrl || window.location.href,
                }),
            });

            if (!response.ok) {
                console.error('Failed to log activity:', response.statusText);
            }
        } catch (error) {
            console.error('Error logging activity:', error);
        }
    }
}

// تهيئة التتبع عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    const sessionId = document.querySelector('meta[name="session-id"]')?.content;
    const apiUrl = document.querySelector('meta[name="session-activity-api"]')?.content;
    
    if (sessionId && apiUrl) {
        window.sessionTracker = new SessionActivityTracker(sessionId, apiUrl);
    }
});

