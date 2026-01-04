# فحص جاهزية نظام WhatsApp للتجريب

## ✅ المكونات المطلوبة - حالة التحقق

### 1. الملفات الأساسية ✅
- [x] `config/whatsapp.php` - موجود
- [x] `app/Http/Controllers/API/WhatsAppWebhookController.php` - موجود
- [x] `app/Services/WhatsApp/WhatsAppClient.php` - موجود
- [x] `app/Services/WhatsApp/WebhookParser.php` - موجود
- [x] `app/Services/WhatsApp/SignatureVerifier.php` - موجود
- [x] `app/Services/WhatsApp/SendWhatsAppMessage.php` - موجود
- [x] `app/Jobs/ProcessWhatsAppWebhookEventJob.php` - موجود
- [x] `app/Jobs/SendWhatsAppMessageJob.php` - موجود
- [x] `routes/api.php` - Routes موجودة
- [x] `routes/admin.php` - Admin routes موجودة

### 2. Models ✅
- [x] `WhatsAppContact` - موجود
- [x] `WhatsAppMessage` - موجود
- [x] `WhatsAppWebhookEvent` - موجود

### 3. Migrations ⚠️
- [x] Migrations موجودة (3 ملفات)
- [ ] **يجب تشغيل:** `php artisan migrate`

### 4. Environment Variables ⚠️
- [ ] التحقق من `.env` يحتوي على:
  ```
  WHATSAPP_CLOUD_API_VERSION=v20.0
  WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
  WHATSAPP_WABA_ID=your_waba_id (optional)
  WHATSAPP_ACCESS_TOKEN=your_access_token
  WHATSAPP_VERIFY_TOKEN=your_verify_token
  WHATSAPP_APP_SECRET=your_app_secret
  WHATSAPP_WEBHOOK_PATH=/api/webhooks/whatsapp
  WHATSAPP_STRICT_SIGNATURE=true
  WHATSAPP_AUTO_REPLY=false
  ```

### 5. Queue Configuration ⚠️
- [ ] **مهم:** يجب أن يكون Queue Worker يعمل:
  ```bash
  php artisan queue:work
  ```
  أو استخدام Supervisor/Cron

### 6. مشكلة محتملة في الكود ⚠️
- ⚠️ `WhatsAppWebhookController` يستخدم `SignatureVerifier::verifyFromRequest` 
  لكن يجب التحقق إذا كانت method موجودة أو يجب تغييرها إلى instance method

---

## 🔍 خطوات التحقق قبل التجريب

### 1. تشغيل Migrations
```bash
php artisan migrate
```

### 2. التحقق من Routes
```bash
php artisan route:list | grep whatsapp
```

يجب أن ترى:
- `GET /api/webhooks/whatsapp` (webhooks.whatsapp.verify)
- `POST /api/webhooks/whatsapp` (webhooks.whatsapp.handle)
- `GET /admin/whatsapp-settings` (admin.whatsapp-settings.index)
- `POST /admin/whatsapp-settings` (admin.whatsapp-settings.update)
- `GET /admin/whatsapp-messages` (admin.whatsapp-messages.index)
- وغيرها...

### 3. إعداد .env
- أضف جميع المتغيرات المطلوبة
- احصل على Access Token من Meta Developer Console
- أنشئ Verify Token (يمكن أن يكون أي نص)

### 4. إعداد Meta Webhook
- Webhook URL: `https://yourdomain.com/api/webhooks/whatsapp`
- Verify Token: نفس القيمة في `.env`
- Subscribe to: `messages`, `message_status`

### 5. تشغيل Queue Worker
```bash
php artisan queue:work --queue=default
```

---

## ⚠️ المشاكل المحتملة

### 1. SignatureVerifier Method
في `WhatsAppWebhookController` السطر 52:
```php
SignatureVerifier::verifyFromRequest($signature, $rawBody, $appSecret)
```
يجب التحقق إذا كانت هذه method static موجودة في `SignatureVerifier` أو يجب تغييرها.

### 2. Queue Driver
- تأكد من أن `QUEUE_CONNECTION` في `.env` ليس `sync` في الإنتاج
- استخدم `database` أو `redis` للـ queues

### 3. Logging
- تأكد من وجود مجلد `storage/logs`
- ستجد logs في `storage/logs/whatsapp.log`

---

## ✅ جاهزية النظام

**الحالة الحالية:** 🟡 **جاهز تقريباً** (85%)

**ما يحتاج عمله قبل التجريب:**
1. ✅ الكود موجود ومكتمل
2. ⚠️ تشغيل migrations
3. ⚠️ إعداد .env
4. ⚠️ التحقق من SignatureVerifier method
5. ⚠️ تشغيل queue worker
6. ⚠️ إعداد Meta Webhook

---

## 📝 خطوات التجريب السريع

1. **تشغيل Migrations:**
   ```bash
   php artisan migrate
   ```

2. **إعداد .env:**
   - أضف جميع متغيرات WhatsApp

3. **الوصول لصفحة الإعدادات:**
   - `/admin/whatsapp-settings`
   - أدخل البيانات
   - اضغط "اختبار الاتصال"

4. **إرسال رسالة تجريبية:**
   - `/admin/whatsapp-messages/send`
   - اختر رقم هاتف
   - أرسل رسالة

5. **إعداد Webhook في Meta:**
   - افتح Meta Developer Console
   - أضف Webhook URL
   - استخدم Verify Token من .env

6. **تشغيل Queue Worker:**
   ```bash
   php artisan queue:work
   ```

---

**آخر تحديث:** 2026-01-01

