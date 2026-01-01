# دليل إعداد تكامل Zoom

## نظرة عامة

هذا الدليل يشرح كيفية إعداد وتكوين وحدة تكامل Zoom في منصة Quantum LMS. الوحدة تسمح للطلاب بالانضمام إلى جلسات Zoom مباشرة من خلال المنصة مع حماية كاملة لروابط الانضمام.

## المتطلبات الأساسية

- Laravel 10/11
- PHP 8.2+
- MySQL
- حساب Zoom مع Server-to-Server OAuth App
- Zoom Meeting SDK credentials

## متغيرات البيئة المطلوبة

أضف المتغيرات التالية إلى ملف `.env`:

```env
# Zoom Account Credentials (Server-to-Server OAuth)
ZOOM_ACCOUNT_ID=your_account_id
ZOOM_CLIENT_ID=your_client_id
ZOOM_CLIENT_SECRET=your_client_secret

# Zoom Meeting SDK Credentials
ZOOM_MEETING_SDK_KEY=your_sdk_key
ZOOM_MEETING_SDK_SECRET=your_sdk_secret

# Optional: API Base URL (default: https://api.zoom.us/v2)
ZOOM_API_BASE_URL=https://api.zoom.us/v2

# Optional: Token Cache TTL in seconds (default: 3600)
ZOOM_TOKEN_CACHE_TTL=3600

# Optional: Join Window Configuration
ZOOM_JOIN_WINDOW_BEFORE_MINUTES=10
ZOOM_JOIN_WINDOW_AFTER_MINUTES=15

# Optional: Token Configuration
ZOOM_TOKEN_TTL_MINUTES=5
ZOOM_TOKEN_MAX_USES=1

# Optional: Rate Limiting
ZOOM_RATE_LIMIT_PER_USER=10
ZOOM_RATE_LIMIT_PER_SESSION=50
ZOOM_RATE_LIMIT_PER_IP=20

# Optional: Security Settings
ZOOM_ENABLE_DEVICE_BINDING=true
ZOOM_ENABLE_IP_BINDING=true
ZOOM_IP_PREFIX_LENGTH=3
```

## كيفية إنشاء Zoom App Credentials

### 1. إنشاء Server-to-Server OAuth App

1. سجل الدخول إلى [Zoom Marketplace](https://marketplace.zoom.us/)
2. انتقل إلى **Develop** > **Build App**
3. اختر **Server-to-Server OAuth**
4. املأ معلومات التطبيق:
   - **App Name**: Quantum LMS Integration
   - **Company Name**: اسم شركتك
   - **Developer Contact Information**: معلومات الاتصال
5. بعد الإنشاء، ستحصل على:
   - **Account ID**
   - **Client ID**
   - **Client Secret**
6. احفظ هذه المعلومات في ملف `.env`

### 2. إنشاء Meeting SDK App

1. في Zoom Marketplace، أنشئ تطبيق جديد
2. اختر **Meeting SDK**
3. املأ معلومات التطبيق
4. بعد الإنشاء، ستحصل على:
   - **SDK Key**
   - **SDK Secret**
5. احفظ هذه المعلومات في ملف `.env`

### 3. إعدادات Zoom App

في إعدادات التطبيق:

1. **Scopes**: تأكد من تفعيل الصلاحيات التالية:
   - `meeting:write:admin`
   - `meeting:read:admin`
   - `meeting:write`
   - `meeting:read`

2. **OAuth Redirect URL**: لا حاجة لـ redirect URL في Server-to-Server OAuth

3. **Activation**: قم بتفعيل التطبيق

## التثبيت والإعداد

### 1. تشغيل Migrations

```bash
php artisan migrate
```

سيتم إنشاء الجداول التالية:
- `live_sessions`
- `zoom_meetings`
- `zoom_join_tokens`
- `attendance_logs`

### 2. تثبيت الحزم المطلوبة

تأكد من تثبيت الحزم التالية:

```bash
composer require guzzlehttp/guzzle
composer require maatwebsite/excel  # للتصدير
```

### 3. تسجيل Middleware (اختياري)

إذا كنت تريد استخدام `EnsureZoomJoinWindow` middleware، أضفه إلى `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'zoom.join.window' => \App\Http\Middleware\EnsureZoomJoinWindow::class,
    ]);
})
```

### 4. تسجيل Policy

تأكد من تسجيل `LiveSessionPolicy` في `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    LiveSession::class => LiveSessionPolicy::class,
];
```

## الاستخدام

### للمعلمين/المديرين

#### إنشاء جلسة حية مع Zoom

1. انتقل إلى صفحة إدارة الجلسات الحية
2. انقر على "إنشاء اجتماع Zoom"
3. املأ المعلومات:
   - عنوان الجلسة
   - الوصف
   - وقت الجلسة
   - المدة (بالدقائق)
   - المنطقة الزمنية
4. انقر على "إنشاء اجتماع Zoom"

#### إدارة الاجتماع

- **مزامنة الحالة**: مزامنة حالة الاجتماع من Zoom
- **تحديث الاجتماع**: تحديث وقت أو مدة الاجتماع
- **إلغاء الاجتماع**: إلغاء الاجتماع

#### عرض الحضور

1. انتقل إلى صفحة الحضور للجلسة
2. عرض قائمة الطلاب مع حالة الحضور
3. تصدير قائمة الحضور (Excel)
4. عرض إحصائيات الحضور

### للطلاب

#### الانضمام إلى جلسة

1. انتقل إلى صفحة الجلسة الحية
2. انقر على "الانضمام إلى الجلسة"
3. سيتم تحميل Zoom Meeting SDK تلقائياً
4. سيتم الانضمام إلى الجلسة تلقائياً

#### عرض سجل الحضور

1. انتقل إلى "سجل الحضور"
2. عرض جميع الجلسات التي حضرتها
3. عرض إحصائيات الحضور
4. عرض تفاصيل جلسة محددة

## الأمان

### حماية الروابط

- **لا يتم عرض `join_url`** في أي مكان في الواجهة
- يتم استخدام Zoom Meeting SDK مع توقيع (signature) يتم إنشاؤه من الخادم فقط
- Tokens قصيرة العمر (5 دقائق افتراضياً)
- Tokens للاستخدام الواحد (قابلة للتكوين)

### التحقق من الصلاحيات

- التحقق من التسجيل في المادة/الدرس
- التحقق من نافذة الوقت (10 دقائق قبل، 15 دقيقة بعد)
- التحقق من حالة المستخدم (نشط/غير نشط)
- Rate limiting (10 طلبات/دقيقة لكل مستخدم)

### منع المشاركة

- **Waiting Room**: مفعل تلقائياً
- **Join Before Host**: معطل
- **Device Binding**: ربط Token بـ User-Agent hash (اختياري)
- **IP Binding**: ربط Token بـ IP prefix (اختياري)
- **Duplicate Join Detection**: منع الانضمام المتعدد من نفس المستخدم

## API Endpoints

### للمعلمين/المديرين

```
POST   /admin/live-sessions/{liveSession}/zoom/create
POST   /admin/live-sessions/{liveSession}/zoom/update
POST   /admin/live-sessions/{liveSession}/zoom/cancel
GET    /admin/live-sessions/{liveSession}/zoom/sync
GET    /admin/live-sessions/{liveSession}/attendance
GET    /admin/live-sessions/{liveSession}/attendance/users/{user}
GET    /admin/live-sessions/{liveSession}/attendance/export/{format}
GET    /admin/live-sessions/{liveSession}/attendance/stats
```

### للطلاب

```
POST   /student/live-sessions/{liveSession}/zoom/join-token
GET    /student/live-sessions/{liveSession}/zoom/join
POST   /student/live-sessions/{liveSession}/zoom/on-join
POST   /student/live-sessions/{liveSession}/zoom/on-leave
GET    /student/attendance
GET    /student/attendance/sessions/{liveSession}
GET    /student/attendance/stats
GET    /student/attendance/stats/subject/{subject}
```

## الحدود والقيود

### Zoom API Rate Limits

- **Meeting API**: 30 طلب/ثانية
- **Token Cache**: يتم تخزين tokens مؤقتاً لتقليل الطلبات

### Meeting Settings

- **Waiting Room**: مفعل دائماً (لا يمكن تعطيله)
- **Join Before Host**: معطل دائماً
- **Registration**: غير مطلوب (يمكن تفعيله لاحقاً)

### Security Limitations

- Zoom Meeting SDK لا يدعم بعض إعدادات الأمان المتقدمة
- Device/IP binding قد لا يعمل بشكل مثالي مع VPN
- Token validation يعتمد على hash comparison (قد يكون بطيئاً مع عدد كبير من tokens)

## استكشاف الأخطاء

### خطأ: "Failed to authenticate with Zoom API"

- تحقق من صحة `ZOOM_ACCOUNT_ID`, `ZOOM_CLIENT_ID`, `ZOOM_CLIENT_SECRET`
- تأكد من تفعيل التطبيق في Zoom Marketplace
- تحقق من الصلاحيات (Scopes) المطلوبة

### خطأ: "Failed to generate join signature"

- تحقق من صحة `ZOOM_MEETING_SDK_KEY` و `ZOOM_MEETING_SDK_SECRET`
- تأكد من تفعيل Meeting SDK App

### خطأ: "Session is not available for joining"

- تحقق من نافذة الوقت (10 دقائق قبل، 15 دقيقة بعد)
- تحقق من حالة الجلسة (يجب أن تكون `scheduled` أو `live`)
- تحقق من أن المستخدم مسجل في المادة/الدرس

### خطأ: "You are already in this session"

- يتم منع الانضمام المتعدد من نفس المستخدم
- يجب إنهاء الجلسة الحالية أولاً

## الصيانة

### تنظيف Tokens المنتهية

يمكن إضافة scheduled task لتنظيف tokens المنتهية:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app(\App\Services\Zoom\JoinTokenService::class)->cleanupExpiredTokens();
    })->daily();
}
```

### مراقبة الأداء

- راقب عدد tokens المنشأة
- راقب معدل استخدام Zoom API
- راقب سجلات الأخطاء في `storage/logs/laravel.log`

## التطوير المستقبلي

### Webhooks (اختياري)

يمكن إضافة webhooks للمزامنة التلقائية:
- `meeting.started`
- `meeting.ended`
- `participant.joined`
- `participant.left`

### Registration per Student (اختياري)

يمكن تفعيل التسجيل لكل طالب:
- إنشاء registrant لكل طالب
- استخدام registrant join_url (لا يتم عرضه للطالب)
- استخدام Meeting SDK مع registrant credentials

## الدعم

للمساعدة أو الإبلاغ عن مشاكل:
- راجع سجلات Laravel: `storage/logs/laravel.log`
- راجع Zoom API logs في Zoom Dashboard
- تحقق من Zoom API documentation: https://marketplace.zoom.us/docs/api-reference/zoom-api


