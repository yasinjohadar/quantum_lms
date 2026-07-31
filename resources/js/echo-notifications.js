import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

function normalizeReverbHost(h) {
    if (h == null || h === '') {
        return null;
    }
    return String(h)
        .replace(/^https?:\/\//i, '')
        .replace(/\/$/, '');
}

/** يُحقَن من Laravel (config/echo-client.php) — يتجاوز قيم Vite المخزّنة في build */
const runtimeCfg =
    typeof window !== 'undefined' &&
    window.__echoReverbConfig &&
    typeof window.__echoReverbConfig === 'object'
        ? window.__echoReverbConfig
        : null;

const echoNotificationsEnabled =
    runtimeCfg == null ||
    runtimeCfg.enabled === undefined ||
    runtimeCfg.enabled === true ||
    runtimeCfg.enabled === 'true' ||
    runtimeCfg.enabled === 1;

const keyFromEnv = runtimeCfg?.app_key || import.meta.env.VITE_REVERB_APP_KEY;
const key = echoNotificationsEnabled ? keyFromEnv : null;
const wsHost =
    normalizeReverbHost(runtimeCfg?.host ?? import.meta.env.VITE_REVERB_HOST) ||
    window.location.hostname;
const port = (() => {
    const p = runtimeCfg?.port ?? import.meta.env.VITE_REVERB_PORT;
    if (p !== undefined && p !== null && p !== '') {
        const n = parseInt(String(p), 10);
        return Number.isFinite(n) ? n : 8080;
    }
    return 8080;
})();
const scheme = runtimeCfg?.scheme ?? import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const forceTLS = scheme === 'https';

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

const TOAST_TTL_MS = 7000;
const TOAST_MAX_STACK = 4;

function injectEchoToastStylesOnce() {
    if (document.getElementById('echo-toast-styles')) {
        return;
    }
    const style = document.createElement('style');
    style.id = 'echo-toast-styles';
    style.textContent = `
        #echo-toast-stack {
            position: fixed;
            z-index: 10850;
            top: 1rem;
            inset-inline-end: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            max-width: min(92vw, 400px);
            pointer-events: none;
        }
        #echo-toast-stack .echo-toast-card {
            pointer-events: auto;
            position: relative;
            border-radius: 0.5rem;
            box-shadow: 0 0.4rem 1.35rem rgba(0, 0, 0, 0.28);
            overflow: hidden;
            color: #fff;
            animation: echoToastIn 0.42s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            transform-origin: top center;
        }
        #echo-toast-stack .echo-toast-card.echo-toast-leave {
            animation: echoToastOut 0.32s ease forwards;
        }
        #echo-toast-stack .echo-toast-close {
            position: absolute;
            top: 0.65rem;
            inset-inline-end: 0.65rem;
            z-index: 2;
            width: 1.75rem;
            height: 1.75rem;
            padding: 0;
            margin: 0;
            border: none;
            border-radius: 0.25rem;
            background: transparent;
            color: #fff;
            opacity: 0.92;
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #echo-toast-stack .echo-toast-close:hover {
            opacity: 1;
            background: rgba(255,255,255,0.12);
        }
        #echo-toast-stack .echo-toast-inner {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem 2.6rem 1rem 1rem;
        }
        [dir="rtl"] #echo-toast-stack .echo-toast-inner {
            padding: 1rem 1rem 1rem 2.6rem;
        }
        #echo-toast-stack .echo-toast-icon-wrap {
            flex-shrink: 0;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        #echo-toast-stack .echo-toast-icon-wrap i {
            font-size: 1.35rem;
            line-height: 1;
        }
        #echo-toast-stack .echo-toast-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.92;
            margin-bottom: 0.35rem;
        }
        #echo-toast-stack .echo-toast-title {
            font-size: 0.98rem;
            font-weight: 700;
            line-height: 1.35;
            margin-bottom: 0.35rem;
        }
        #echo-toast-stack .echo-toast-body {
            font-size: 0.84rem;
            font-weight: 400;
            line-height: 1.45;
            opacity: 0.95;
            margin: 0;
        }
        #echo-toast-stack .echo-toast-meta {
            font-size: 0.75rem;
            opacity: 0.85;
            margin-top: 0.4rem;
        }
        #echo-toast-stack .echo-toast-hint {
            font-size: 0.72rem;
            opacity: 0.8;
            margin-top: 0.35rem;
        }
        @keyframes echoToastIn {
            from {
                opacity: 0;
                transform: translateY(-14px) scale(0.94) rotate(-1.2deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1) rotate(0deg);
            }
        }
        @keyframes echoToastOut {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to { opacity: 0; transform: translateY(-10px) scale(0.96); }
        }
    `;
    document.head.appendChild(style);
}

function getOrCreateToastStack() {
    injectEchoToastStylesOnce();
    let el = document.getElementById('echo-toast-stack');
    if (!el) {
        el = document.createElement('div');
        el.id = 'echo-toast-stack';
        el.setAttribute('aria-live', 'polite');
        el.setAttribute('role', 'region');
        el.setAttribute('aria-label', 'إشعارات منبثقة');
        document.body.appendChild(el);
    }
    return el;
}

/**
 * ألوان خلفية التوست (نص أبيض) + لون الأيقونة داخل الدائرة البيضاء
 */
function getToastTheme(color) {
    const c = (color || 'success').toLowerCase();
    const themes = {
        success: { bg: '#2e7d32', iconColor: '#2e7d32' },
        primary: { bg: '#3949ab', iconColor: '#3949ab' },
        danger: { bg: '#c62828', iconColor: '#c62828' },
        warning: { bg: '#ef6c00', iconColor: '#ef6c00' },
        info: { bg: '#0277bd', iconColor: '#0277bd' },
        secondary: { bg: '#546e7a', iconColor: '#546e7a' },
        orange: { bg: '#ef6c00', iconColor: '#ef6c00' },
        purple: { bg: '#6a1b9a', iconColor: '#6a1b9a' },
    };
    return themes[c] || themes.success;
}

/**
 * أيقونة مميزة حسب نوع الإشعار، مع احترام الأيقونة القادمة من الخادم عند الحاجة
 */
function distinctiveIconForNotification(data) {
    const fallback = data.icon || 'fe fe-bell';
    const t = data.type || '';
    const byType = {
        lesson_review_submitted: 'fe fe-book-open',
        lesson_review_approved: 'fe fe-check-circle',
        lesson_review_rejected: 'fe fe-x-circle',
        lesson_review_submit_ack: 'fe fe-send',
        quiz_review_submitted: 'fe fe-edit-3',
        quiz_review_approved: 'fe fe-check-circle',
        quiz_review_rejected: 'fe fe-x-circle',
        quiz_review_submit_ack: 'fe fe-send',
        student_lesson_available: 'fe fe-play-circle',
        student_quiz_available: 'fe fe-edit-2',
        class_enrollment_decision: 'fe fe-users',
        custom_notification: 'fe fe-message-square',
        badge_earned: 'fe fe-award',
        achievement_unlocked: 'fe fe-star',
        level_up: 'fe fe-trending-up',
        staff_review: 'fe fe-bell',
    };
    return byType[t] || fallback;
}

function showToastNotification(data) {
    const stack = getOrCreateToastStack();
    while (stack.children.length >= TOAST_MAX_STACK) {
        stack.firstElementChild?.remove();
    }

    const href = data.action_url || data.data?.url || '';
    const theme = getToastTheme(data.color);
    const iconClass = distinctiveIconForNotification(data);

    const wrap = document.createElement('div');
    wrap.className = 'echo-toast-card mb-0';
    wrap.setAttribute('role', 'alert');
    wrap.style.background = theme.bg;

    const actorLine =
        data.actor_name ?
            `<div class="echo-toast-meta">من: ${escapeHtml(data.actor_name)}${data.actor_role ? ' — ' + escapeHtml(data.actor_role) : ''}</div>`
        :   '';

    const hint = href ? `<div class="echo-toast-hint">اضغط للانتقال إلى التفاصيل</div>` : '';

    wrap.innerHTML = `
        <button type="button" class="echo-toast-close echo-toast-dismiss" aria-label="إغلاق">×</button>
        <div class="echo-toast-inner">
            <div class="echo-toast-icon-wrap" aria-hidden="true">
                <i class="${escapeHtml(iconClass)}" style="color:${theme.iconColor}"></i>
            </div>
            <div class="echo-toast-text flex-grow-1 min-w-0">
                <div class="echo-toast-label">إشعار جديد</div>
                <div class="echo-toast-title">${escapeHtml(data.title)}</div>
                <p class="echo-toast-body">${escapeHtml(data.message)}</p>
                ${actorLine}
                ${hint}
            </div>
        </div>
    `;

    const dismiss = () => {
        if (wrap.classList.contains('echo-toast-leave')) {
            return;
        }
        wrap.classList.add('echo-toast-leave');
        window.clearTimeout(wrap._echoToastTimer);
        setTimeout(() => wrap.remove(), 320);
    };

    wrap.querySelector('.echo-toast-dismiss')?.addEventListener('click', (e) => {
        e.stopPropagation();
        dismiss();
    });

    wrap.addEventListener('click', (e) => {
        if (e.target.closest('.echo-toast-dismiss')) {
            return;
        }
        if (href) {
            window.location.href = href;
        }
    });

    wrap._echoToastTimer = window.setTimeout(dismiss, TOAST_TTL_MS);
    stack.appendChild(wrap);
}

function updateBadge(count) {
    const countText = document.getElementById('notification-count-text');
    if (countText) {
        countText.textContent = count;
    }
    const badge = document.getElementById('notification-badge-count');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
    const pulse = document.querySelector('.main-header-notification .pulse-success');
    if (pulse) {
        pulse.style.display = count > 0 ? 'block' : 'none';
    }
}

function prefetchUnreadCount() {
    try {
        fetch('/notifications/inbox/unread-count', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then((d) => updateBadge(d.count ?? 0))
            .catch(() => {});
    } catch (_) {}
}

function addNotificationToDropdown(data) {
    const dropdown = document.getElementById('header-notification-scroll');
    if (!dropdown) {
        return;
    }
    const noMsg = document.getElementById('no-notifications-message');
    if (noMsg) {
        noMsg.style.display = 'none';
    }
    const item = document.createElement('li');
    item.className = 'dropdown-item';
    item.style.cssText =
        'padding: 0.75rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.1); cursor: pointer;';
    const href = data.action_url || data.data?.url || '';
    item.onclick = () => {
        if (href) {
            window.location.href = href;
        }
    };
    const icon = data.icon || 'fe fe-bell';
    const color = data.color || 'primary';
    item.innerHTML = `
        <div class="d-flex align-items-start">
            <div class="avatar avatar-sm me-3 flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-${color}-transparent">
                    <i class="${icon} text-${color}"></i>
                </span>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-semibold">${escapeHtml(data.title)}</h6>
                <p class="mb-1 text-muted small">${escapeHtml(data.message)}</p>
                ${data.actor_name ? `<small class="text-muted d-block">من: ${escapeHtml(data.actor_name)}${data.actor_role ? ' (' + escapeHtml(data.actor_role) + ')' : ''}</small>` : ''}
            </div>
        </div>
    `;
    dropdown.insertBefore(item, dropdown.firstChild);
    const items = dropdown.querySelectorAll('li.dropdown-item');
    if (items.length > 10) {
        for (let i = 10; i < items.length; i++) {
            items[i].remove();
        }
    }
}

function handleNotification(data) {
    if (!data || !data.type) {
        return;
    }
    window.EchoNotificationsConnected = true;
    addNotificationToDropdown(data);
    prefetchUnreadCount();
    try {
        showToastNotification(data);
    } catch (e) {
        console.warn('echo toast:', e);
    }
}

function whenDomReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
        fn();
    }
}

function getEchoPusher() {
    return window.Echo?.connector?.pusher ?? null;
}

const ECHO_REALTIME_STORAGE_KEY = 'lms_echo_realtime';
const WS_GIVE_UP_STORAGE_KEY = 'lms_echo_ws_give_up_until';
const WS_GIVE_UP_COOLDOWN_MS = 5 * 60 * 1000;
const WS_MAX_FAILS = 2;

let pollingIntervalId = null;

function isWsTemporarilyDisabled() {
    try {
        const until = parseInt(sessionStorage.getItem(WS_GIVE_UP_STORAGE_KEY) || '0', 10);
        return Number.isFinite(until) && Date.now() < until;
    } catch (_) {
        return false;
    }
}

function markWsGiveUp() {
    try {
        sessionStorage.setItem(WS_GIVE_UP_STORAGE_KEY, String(Date.now() + WS_GIVE_UP_COOLDOWN_MS));
    } catch (_) {}
}

function startPollingFallback(silent = true) {
    prefetchUnreadCount();
    if (!pollingIntervalId) {
        pollingIntervalId = window.setInterval(prefetchUnreadCount, 120000);
    }
    if (!silent) {
        whenDomReady(() => updateRealtimeStatusUI('polling', { mode: 'polling' }));
    }
}

function scheduleIdleTask(fn, fallbackMs = 400) {
    if (typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(fn, { timeout: 2500 });
    } else {
        window.setTimeout(fn, fallbackMs);
    }
}

function isEchoRealtimePausedByUser() {
    try {
        return localStorage.getItem(ECHO_REALTIME_STORAGE_KEY) === '0';
    } catch (_) {
        return false;
    }
}

function updateRealtimeStatusUI(state, extra = {}) {
    const el = document.getElementById('echo-realtime-status');
    const btnOn = document.getElementById('echo-realtime-on');
    const btnOff = document.getElementById('echo-realtime-off');
    const actions = document.getElementById('echo-realtime-actions');
    if (!el) {
        return;
    }

    const mode = extra.mode || 'web';

    if (mode === 'polling') {
        el.textContent =
            extra.reason === 'disabled'
                ? 'الفوري معطّل من الإعدادات'
                : 'وضع احتياطي (تحديث دوري)';
        el.className = 'badge rounded-pill bg-warning-transparent text-warning border';
        if (actions) {
            actions.classList.add('d-none');
        }
        return;
    }

    if (actions) {
        actions.classList.remove('d-none');
    }

    const paused = state === 'paused' || isEchoRealtimePausedByUser();

    if (paused) {
        el.textContent = 'الفوري: موقوف';
        el.className = 'badge rounded-pill bg-warning-transparent text-warning border';
        if (btnOn) {
            btnOn.classList.remove('d-none');
            btnOn.disabled = false;
        }
        if (btnOff) {
            btnOff.classList.add('d-none');
        }
        return;
    }

    const s = state || '';
    let label = '—';
    let badgeClass = 'badge rounded-pill border';

    switch (s) {
        case 'initialized':
        case 'connecting':
            label = 'جاري الاتصال…';
            badgeClass += ' bg-info-transparent text-info';
            break;
        case 'connected':
            label = 'متصل (فوري)';
            badgeClass += ' bg-success-transparent text-success';
            break;
        case 'disconnected':
            label = 'غير متصل';
            badgeClass += ' bg-secondary-transparent text-secondary';
            break;
        case 'failed':
        case 'unavailable':
            label = 'فشل الاتصال';
            badgeClass += ' bg-danger-transparent text-danger';
            break;
        default:
            label = s || '—';
            badgeClass += ' bg-secondary-transparent text-secondary';
    }

    el.textContent = label;
    el.className = badgeClass;

    const connecting = s === 'connecting' || s === 'initialized';

    if (btnOn) {
        if (connecting) {
            btnOn.classList.add('d-none');
            btnOn.disabled = true;
        } else if (s === 'connected') {
            btnOn.classList.add('d-none');
            btnOn.disabled = false;
        } else {
            btnOn.classList.remove('d-none');
            btnOn.disabled = false;
        }
    }
    if (btnOff) {
        if (connecting) {
            btnOff.classList.add('d-none');
        } else if (s === 'connected') {
            btnOff.classList.remove('d-none');
            btnOff.disabled = false;
        } else {
            btnOff.classList.add('d-none');
        }
    }
}

function wireEchoConnectionUi() {
    const pusher = getEchoPusher();
    const conn = pusher?.connection;
    if (!conn) {
        return;
    }

    let wsFailCount = 0;

    const giveUpWebSocket = () => {
        markWsGiveUp();
        try {
            pusher.disconnect();
        } catch (_) {}
        startPollingFallback(false);
    };

    const applyFromConn = () => {
        if (isEchoRealtimePausedByUser()) {
            updateRealtimeStatusUI('paused', { mode: 'web' });
        } else {
            updateRealtimeStatusUI(conn.state, { mode: 'web' });
        }
    };

    conn.bind('state_change', (states) => {
        if (states.current === 'connected') {
            wsFailCount = 0;
        }
        if (states.current === 'failed' || states.current === 'unavailable') {
            wsFailCount += 1;
            if (wsFailCount >= WS_MAX_FAILS) {
                giveUpWebSocket();
                return;
            }
        }
        if (isEchoRealtimePausedByUser()) {
            updateRealtimeStatusUI('paused', { mode: 'web' });
        } else {
            updateRealtimeStatusUI(states.current, { mode: 'web' });
        }
    });
    conn.bind('error', () => {
        wsFailCount += 1;
        if (wsFailCount >= WS_MAX_FAILS) {
            giveUpWebSocket();
            return;
        }
        if (isEchoRealtimePausedByUser()) {
            updateRealtimeStatusUI('paused', { mode: 'web' });
        } else {
            updateRealtimeStatusUI(conn.state || 'failed', { mode: 'web' });
        }
    });

    whenDomReady(() => {
        if (isEchoRealtimePausedByUser()) {
            try {
                pusher.disconnect();
            } catch (_) {}
            updateRealtimeStatusUI('paused', { mode: 'web' });
        } else {
            applyFromConn();
        }
    });
}

/** إعادة محاولة الاتصال (مثلاً بعد خطأ) — يفعّل الفوري ويستأنف الاتصال */
window.reconnectEchoNotifications = function () {
    try {
        localStorage.setItem(ECHO_REALTIME_STORAGE_KEY, '1');
        sessionStorage.removeItem(WS_GIVE_UP_STORAGE_KEY);
    } catch (_) {}
    const pusher = getEchoPusher();
    if (!pusher) {
        return;
    }
    try {
        updateRealtimeStatusUI('connecting', { mode: 'web' });
        pusher.connect();
    } catch (e) {
        console.warn('echo reconnect:', e);
    }
};

whenDomReady(() => {
    const btnOn = document.getElementById('echo-realtime-on');
    const btnOff = document.getElementById('echo-realtime-off');
    if (btnOn && !btnOn.dataset.echoBound) {
        btnOn.dataset.echoBound = '1';
        btnOn.addEventListener('click', () => {
            try {
                localStorage.setItem(ECHO_REALTIME_STORAGE_KEY, '1');
                sessionStorage.removeItem(WS_GIVE_UP_STORAGE_KEY);
            } catch (_) {}
            updateRealtimeStatusUI('connecting', { mode: 'web' });
            const p = getEchoPusher();
            if (p) {
                try {
                    p.connect();
                } catch (e) {
                    console.warn('echo connect:', e);
                }
            }
        });
    }
    if (btnOff && !btnOff.dataset.echoBound) {
        btnOff.dataset.echoBound = '1';
        btnOff.addEventListener('click', () => {
            try {
                localStorage.setItem(ECHO_REALTIME_STORAGE_KEY, '0');
            } catch (_) {}
            const p = getEchoPusher();
            if (p) {
                try {
                    p.disconnect();
                } catch (e) {
                    console.warn('echo disconnect:', e);
                }
            }
            updateRealtimeStatusUI('paused', { mode: 'web' });
        });
    }
});

if (!echoNotificationsEnabled) {
    console.warn('ECHO_NOTIFICATIONS_ENABLED=false — تم إيقاف WebSocket (لا إعادة محاولة اتصال).');
    startPollingFallback();
    whenDomReady(() => updateRealtimeStatusUI('polling', { mode: 'polling', reason: 'disabled' }));
} else if (!key) {
    console.warn('VITE_REVERB_APP_KEY غير مضبوط — الإشعارات الفورية معطّلة (استخدم polling الافتراضي).');
    startPollingFallback();
    whenDomReady(() => updateRealtimeStatusUI('polling', { mode: 'polling' }));
} else if (typeof window.currentUserId !== 'undefined') {
    if (isWsTemporarilyDisabled()) {
        startPollingFallback();
    } else {
        scheduleIdleTask(() => {
            if (isEchoRealtimePausedByUser()) {
                startPollingFallback();
                whenDomReady(() => updateRealtimeStatusUI('paused', { mode: 'web' }));
                return;
            }

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key,
                wsHost,
                wsPort: port,
                wssPort: port,
                forceTLS,
                enabledTransports: forceTLS ? ['wss'] : ['ws'],
                disableStats: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN':
                            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                },
            });

            window.Echo.private(`user.${window.currentUserId}`).listen('.notification', (e) => {
                handleNotification(e);
            });

            prefetchUnreadCount();
            wireEchoConnectionUi();
        });
    }
} else {
    startPollingFallback();
    whenDomReady(() => updateRealtimeStatusUI('polling', { mode: 'polling' }));
}
