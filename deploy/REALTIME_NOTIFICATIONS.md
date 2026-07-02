# دليل تشغيل الإشعارات الفورية (Laravel Reverb + Echo)

هذا الدليل يشرح إعداد وتشغيل الإشعارات الفورية في **أكاديمية كوانتم** — محلياً وعلى السيرفر (`quantum-academy.online`).

---

## 1. كيف يعمل النظام؟

```
[Laravel]  --HTTP-->  [Reverb :8080]  --WebSocket-->  [المتصفح / Echo]
     ↑                        ↑
  بث الأحداث            يستمع داخلياً
  (ShouldBroadcastNow)   على 0.0.0.0:8080
```

| المكوّن | الدور |
|---------|--------|
| **Laravel** | يرسل الأحداث (إشعارات، تحفيز، إلخ) إلى Reverb |
| **Reverb** | خادم WebSocket — عملية مستقلة يجب إبقاؤها شغّالة |
| **Echo (المتصفح)** | يستمع على قناة `private-user.{id}` ويعرض Toast + تحديث العدد |

**مهم:** Reverb **لا يعمل تلقائياً** مع PHP أو Nginx. يُشغَّل مثل الطابور (`queue:work`) — عملية منفصلة يجب إبقاؤها قيد التشغيل (يفضّل عبر Supervisor).

**لا حاجة لـ Queue** للبث الفوري — الأحداث تستخدم `ShouldBroadcastNow` وتُبث مباشرة.

---

## 2. ملفات المشروع ذات الصلة

| الملف | الوصف |
|-------|--------|
| [`config/echo-client.php`](../config/echo-client.php) | إعدادات Echo المحقونة في الصفحة |
| [`config/broadcasting.php`](../config/broadcasting.php) | اتصال PHP بـ Reverb (`REVERB_BROADCAST_*`) |
| [`config/reverb.php`](../config/reverb.php) | إعدادات خادم Reverb |
| [`resources/js/echo-notifications.js`](../resources/js/echo-notifications.js) | عميل Echo في المتصفح |
| [`routes/channels.php`](../routes/channels.php) | تفويض القنوات الخاصة |
| [`deploy/supervisor/reverb.conf`](supervisor/reverb.conf) | Supervisor للإنتاج |
| [`deploy/nginx/reverb-websocket.conf.example`](nginx/reverb-websocket.conf.example) | Nginx WebSocket proxy |

---

## 3. إعداد `.env`

### 3.1 التطوير المحلي

```env
BROADCAST_CONNECTION=reverb
ECHO_NOTIFICATIONS_ENABLED=true

REVERB_APP_ID=quantum-lms
REVERB_APP_KEY=local-reverb-key
REVERB_APP_SECRET=local-reverb-secret

REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3.2 الإنتاج (`quantum-academy.online`)

```env
BROADCAST_CONNECTION=reverb
ECHO_NOTIFICATIONS_ENABLED=true

REVERB_APP_ID=649369
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret

# ما يستمع عليه artisan reverb:start (داخلي — لا يفتحه للإنترنت)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# ما يراه المتصفح (عبر Nginx/Apache على 443)
REVERB_HOST=quantum-academy.online
REVERB_PORT=443
REVERB_SCHEME=https

# اتصال PHP → Reverb على نفس السيرفر (يمنع timeout)
REVERB_BROADCAST_HOST=127.0.0.1
REVERB_BROADCAST_PORT=8080
REVERB_BROADCAST_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3.3 أخطاء شائعة في `.env`

| الخطأ | النتيجة | التصحيح |
|-------|---------|---------|
| `ECHO_NOTIFICATIONS_ENABLED=false` | شارة «الفوري معطّل من الإعدادات» | `true` |
| `BROADCAST_CONNECTION=null` | لا يصل بث من PHP | `reverb` |
| `REVERB_PORT=8080` + `http` للمتصفح على الإنتاج | فشل الاتصال | `443` + `https` |
| `VITE_REVERB_*` بقيم مختلفة عن `REVERB_*` | تعارض | استخدم `${REVERB_*}` |
| نسيان `REVERB_BROADCAST_*` | PHP لا يصل لـ Reverb (timeout) | `127.0.0.1:8080` + `http` |

بعد أي تعديل:

```bash
php artisan config:clear
```

> **ملاحظة:** تغيير `ECHO_NOTIFICATIONS_ENABLED` و `REVERB_HOST` يعمل بعد `config:clear` **دون** إعادة `npm run build` — لأن [`config/echo-client.php`](../config/echo-client.php) يُحقَن في الصفحة مباشرة.

---

## 4. بناء أصول الواجهة

يجب وجود `public/build/manifest.json` أو `public/hot` لتحميل سكربت Echo.

```bash
# تطوير
npm run dev

# إنتاج
npm run build
```

ارفع مجلد `public/build` كاملاً إلى السيرفر بعد البناء.

---

## 5. تشغيل Reverb

### 5.1 تشغيل يدوي (تطوير أو اختبار)

```bash
cd /path/to/quantum_lms
php artisan reverb:start
```

الناتج المتوقع:

```
INFO  Starting server on 0.0.0.0:8080 (quantum-academy.online).
```

**تحذير:** التشغيل اليدوي في SSH يتوقف عند إغلاق الجلسة. لا تستخدمه في الإنتاج.

### 5.2 تشغيل دائم عبر Supervisor (إنتاج — موصى به)

1. عدّل المسارات في [`deploy/supervisor/reverb.conf`](supervisor/reverb.conf):

```ini
command=php /path/to/public_html/artisan reverb:start
stdout_logfile=/path/to/public_html/storage/logs/reverb.log
user=www-data
```

مثال لاستضافة cPanel:

```ini
command=php /home/rootquantum1/public_html/artisan reverb:start
stdout_logfile=/home/rootquantum1/public_html/storage/logs/reverb.log
user=rootquantum1
```

2. انسخ الملف وفعّله:

```bash
sudo cp deploy/supervisor/reverb.conf /etc/supervisor/conf.d/quantum-lms-reverb.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start quantum-lms-reverb:*
sudo supervisorctl status
```

3. أوامر مفيدة:

```bash
sudo supervisorctl restart quantum-lms-reverb:*
sudo supervisorctl stop quantum-lms-reverb:*
tail -f storage/logs/reverb.log
```

### 5.3 تشغيل محلي متكامل (كل الخدمات)

```bash
composer dev
```

يشغّل: `serve` + `queue` + `vite` + `reverb` + `pail` معاً.

---

## 6. إعداد Proxy للمتصفح (إلزامي على الإنتاج)

المتصفح يتصل عبر:

```
wss://quantum-academy.online/app/{REVERB_APP_KEY}
```

Reverb يعمل داخلياً على `127.0.0.1:8080`. **يجب** توجيه مسار `/app` من الويب سيرفر إلى Reverb — وإلا تظهر شارة **«فشل الاتصال»**.

### 6.1 Nginx

أضف داخل `server { ... }` للدومين (انظر [`deploy/nginx/reverb-websocket.conf.example`](nginx/reverb-websocket.conf.example)):

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Host $http_host;
    proxy_set_header Scheme $scheme;
    proxy_set_header SERVER_PORT $server_port;
    proxy_set_header SERVER_NAME $host;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection $connection_upgrade;
    proxy_read_timeout 86400;
}
```

ثم:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 6.2 Apache / cPanel

في **Apache Includes** أو **vhost** للدومين (يتطلب `mod_proxy` و `mod_proxy_wstunnel`):

```apache
<IfModule mod_proxy.c>
    ProxyPreserveHost On
    ProxyPass /app ws://127.0.0.1:8080/app
    ProxyPassReverse /app ws://127.0.0.1:8080/app
</IfModule>
```

> على الاستضافة المشتركة قد لا يُسمح بـ `ProxyPass` من `.htaccess`. اطلب من الدعم الفني تفعيله في vhost الدومين.

### 6.3 Cloudflare

إذا كان الدومين خلف Cloudflare:

1. من لوحة Cloudflare → **Network** → فعّل **WebSockets**
2. تأكد أن SSL mode مناسب (عادة **Full** أو **Full (strict)**)
3. لا تحجب مسار `/app`

---

## 7. التحقق من التشغيل

### 7.1 على السيرفر

```bash
# Reverb يعمل؟
sudo supervisorctl status quantum-lms-reverb:*

# المنفذ 8080 مستمع؟
ss -tlnp | grep 8080
# أو
netstat -tlnp | grep 8080

# الإعدادات صحيحة؟
php artisan tinker --execute="echo json_encode(config('echo-client'));"
php artisan tinker --execute="echo config('broadcasting.default');"
```

### 7.2 في المتصفح

1. سجّل دخولاً (طالب أو أدمن)
2. افتح لوحة التحكم
3. راقب الشارة في الهيدر:

| الشارة | المعنى |
|--------|--------|
| **متصل (فوري)** | يعمل بشكل صحيح |
| **جاري الاتصال…** | محاولة اتصال |
| **فشل الاتصال** | غالباً: لا proxy أو Reverb متوقف |
| **الفوري معطّل من الإعدادات** | `ECHO_NOTIFICATIONS_ENABLED=false` |
| **وضع احتياطي (تحديث دوري)** | لا مفتاح Reverb أو لا `currentUserId` |
| **الفوري: موقوف** | المستخدم أوقفه من زر «إيقاف» |

4. **F12 → Network → WS** — يجب اتصال ناجح إلى:
   `wss://quantum-academy.online/app/...`

5. إذا أوقفت الفوري يدوياً، امسح من Console:
   ```js
   localStorage.removeItem('lms_echo_realtime');
   location.reload();
   ```

### 7.3 إرسال إشعار تجريبي

من لوحة الإدارة: **الإشعارات → إرسال إشعار مخصص** لمستخدم مسجّل الدخول في تبويب آخر.

أو من السيرفر:

```bash
php artisan tinker
```

```php
$user = App\Models\User::find(1);
event(new App\Events\CustomNotificationSent($user, 'اختبار', 'إشعار تجريبي', []));
```

---

## 8. استكشاف الأخطاء

### «فشل الاتصال»

| السبب المحتمل | الحل |
|---------------|------|
| لا يوجد Nginx/Apache proxy لـ `/app` | أضف الإعداد من القسم 6 |
| Reverb متوقف | `supervisorctl start` أو `php artisan reverb:start` |
| `ECHO_NOTIFICATIONS_ENABLED=false` | غيّره إلى `true` + `config:clear` |
| Cloudflare يحجب WebSocket | فعّل WebSockets |
| منفذ 8080 مغلق خارجياً | طبيعي — المتصفح يتصل عبر 443 وليس 8080 مباشرة |
| شهادة SSL خاطئة | راجع إعدادات SSL و Cloudflare |

### «الفوري معطّل من الإعدادات»

```env
ECHO_NOTIFICATIONS_ENABLED=true
```

```bash
php artisan config:clear
```

ثم حدّث الصفحة (`Ctrl+F5`).

### الإشعارات تُحفظ لكن لا تظهر فوراً

- العدد يتحدث كل **120 ثانية** في الوضع الاحتياطي
- تحقق من شارة «متصل (فوري)»
- تحقق من `BROADCAST_CONNECTION=reverb`
- راجع `storage/logs/laravel.log` لرسائل: *«تأكد من تشغيل Reverb»*

### Reverb يتوقف بعد إغلاق SSH

استخدم **Supervisor** (القسم 5.2) — لا تشغّل `reverb:start` يدوياً في الإنتاج.

### تنبيه أحمر أعلى الصفحة

يظهر إذا `BROADCAST_CONNECTION` ليس `reverb`. عيّن:

```env
BROADCAST_CONNECTION=reverb
```

---

## 9. قائمة تحقق سريعة للإنتاج

- [ ] `BROADCAST_CONNECTION=reverb`
- [ ] `ECHO_NOTIFICATIONS_ENABLED=true`
- [ ] `REVERB_HOST=quantum-academy.online` + `PORT=443` + `SCHEME=https`
- [ ] `REVERB_BROADCAST_HOST=127.0.0.1` + `PORT=8080` + `SCHEME=http`
- [ ] `php artisan config:clear`
- [ ] `public/build` موجود على السيرفر
- [ ] Reverb شغّال عبر Supervisor
- [ ] Nginx/Apache يوجّه `/app` → `127.0.0.1:8080`
- [ ] Cloudflare WebSockets مفعّل (إن وُجد)
- [ ] الشارة: **«متصل (فوري)»**
- [ ] إشعار تجريبي يصل فوراً

---

## 10. ملخص التشغيل

| البيئة | Reverb | Proxy | الأمر |
|--------|--------|-------|-------|
| محلي | طرفية منفصلة أو `composer dev` | غير مطلوب | `php artisan reverb:start` |
| إنتاج | Supervisor دائم | Nginx/Apache `/app` | `supervisorctl status` |

**الخلاصة:** الإشعارات الفورية جاهزة برمجياً. على الإنتاج تحتاج ثلاثة أشياء: **`.env` صحيح** + **Reverb شغّال دائماً** + **Proxy لمسار `/app`**.
