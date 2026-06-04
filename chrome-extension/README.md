# Quantum LMS — Chrome Extension (NotebookLM)

## التثبيت (وضع المطوّر)

1. افتح `chrome://extensions/`
2. فعّل **وضع المطوّر**
3. **Load unpacked** → اختر مجلد `chrome-extension`
4. حدّث `config/environments.json` أو صفحة الإعدادات بعنوان API الحقيقي

## الاستخدام

1. افتح Quiz في NotebookLM و**اضغط على بطاقة الاختبار** في Studio حتى تظهر الأسئلة.
2. من نفس التبويب: الإضافة → **استخراج من الصفحة** (يقرأ `data-app-data` من iframe داخل Google).
3. راجع المعاينة → احفظ في بنك الأسئلة.

راجع [`docs/guides/notebooklm-extension-teacher-guide.md`](../docs/guides/notebooklm-extension-teacher-guide.md).

**بعد التحديث:** Reload الإضافة في `chrome://extensions` (v1.2.0+).

## Backend

تشغيل Laravel محلياً ثم:

```bash
php artisan migrate
```

نقاط API: `/api/v1/extension/*` (Sanctum Bearer).
