# متغيرات البيئة المطلوبة لـ WhatsApp Cloud API

## 📝 المتغيرات المطلوبة في ملف `.env`

أضف هذه المتغيرات إلى ملف `.env` الخاص بك:

```env
# ============================================
# WhatsApp Cloud API Configuration
# ============================================

# إصدار API (افتراضي: v20.0)
WHATSAPP_CLOUD_API_VERSION=v20.0

# Phone Number ID - يمكنك الحصول عليه من Meta Developer Console
# مثال: 123456789012345
WHATSAPP_PHONE_NUMBER_ID=

# WABA ID (WhatsApp Business Account ID) - اختياري
# مثال: 987654321098765
WHATSAPP_WABA_ID=

# Access Token - Token الدائم للحصول على الرسائل وإرسالها
# يمكنك الحصول عليه من Meta Developer Console
WHATSAPP_ACCESS_TOKEN=

# Verify Token - Token للتحقق من Webhook (يمكن أن يكون أي نص تختاره)
# مثال: my-secret-verify-token-12345
WHATSAPP_VERIFY_TOKEN=

# App Secret - يستخدم للتحقق من توقيع Webhook
# يمكنك الحصول عليه من Meta App Settings
WHATSAPP_APP_SECRET=

# Webhook Path - مسار Webhook (افتراضي: /api/webhooks/whatsapp)
WHATSAPP_WEBHOOK_PATH=/api/webhooks/whatsapp

# Strict Signature Verification - التحقق الصارم من التوقيع
# true في الإنتاج، false للتطوير المحلي
WHATSAPP_STRICT_SIGNATURE=true

# Auto Reply - تفعيل الرد التلقائي على الرسائل الواردة
# false = معطل، true = مفعّل
WHATSAPP_AUTO_REPLY=false

# API Timeout - مهلة انتظار API (بالثواني)
WHATSAPP_API_TIMEOUT=30
```

---

## 🔑 كيفية الحصول على القيم

### 1. Phone Number ID و Access Token

1. اذهب إلى [Meta for Developers](https://developers.facebook.com/)
2. افتح تطبيقك (App)
3. اذهب إلى **WhatsApp** → **API Setup**
4. ستجد:
   - **Phone number ID** - انسخه وأضفه في `WHATSAPP_PHONE_NUMBER_ID`
   - **Temporary access token** - للاختبار فقط (ينتهي بعد 24 ساعة)
   - للحصول على **Permanent access token**:
     - اذهب إلى **WhatsApp** → **Configuration** → **Access Tokens**
     - أو استخدم **System User Access Token**

### 2. WABA ID (اختياري)

1. في نفس صفحة **API Setup**
2. أو من **WhatsApp Manager** في Business Settings

### 3. Verify Token

- **يمكن أن يكون أي نص تختاره**
- مثال: `my-secret-verify-token-12345`
- يجب أن يكون نفس القيمة التي تدخلها في Meta Webhook Configuration
- يجب أن يكون آمنًا (لا تشاركه)

### 4. App Secret

1. اذهب إلى **App Settings** → **Basic**
2. ابحث عن **App Secret**
3. انقر على **Show** لإظهاره
4. انسخه وأضفه في `WHATSAPP_APP_SECRET`

---

## ⚠️ ملاحظات أمنية مهمة

1. **لا تشارك Access Token أو App Secret أبداً**
2. **استخدم Permanent Access Token في الإنتاج** (ليس Temporary)
3. **Verify Token يجب أن يكون عشوائياً وآمناً**
4. **في الإنتاج، ضع `WHATSAPP_STRICT_SIGNATURE=true`**
5. **في التطوير المحلي، يمكن وضع `WHATSAPP_STRICT_SIGNATURE=false`**

---

## 📋 مثال كامل لملف .env

```env
# WhatsApp Cloud API
WHATSAPP_CLOUD_API_VERSION=v20.0
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_WABA_ID=987654321098765
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WHATSAPP_VERIFY_TOKEN=my-secret-verify-token-abc123xyz
WHATSAPP_APP_SECRET=abc123def456ghi789jkl012mno345pqr678stu901vwx234
WHATSAPP_WEBHOOK_PATH=/api/webhooks/whatsapp
WHATSAPP_STRICT_SIGNATURE=true
WHATSAPP_AUTO_REPLY=false
WHATSAPP_API_TIMEOUT=30
```

---

## 🔍 التحقق من الإعدادات

بعد إضافة المتغيرات، يمكنك التحقق:

```bash
# عرض إعدادات WhatsApp
php artisan config:show whatsapp

# أو اختبار الاتصال من صفحة الإعدادات
# /admin/whatsapp-settings
```

---

## 📝 ملاحظات إضافية

- **Access Token**: إذا كان Temporary، يجب تحديثه كل 24 ساعة
- **Verify Token**: يمكنك اختيار أي نص تريده، فقط تأكد من استخدام نفس القيمة في Meta
- **Strict Signature**: في التطوير المحلي، يمكن تعطيله (`false`) لتسهيل الاختبار
- **Webhook URL**: سيكون `https://yourdomain.com/api/webhooks/whatsapp` (استخدم نفس القيمة في Meta)

---

**آخر تحديث:** 2026-01-01

