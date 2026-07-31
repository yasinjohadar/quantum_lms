<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Services\PermissionDiscoveryService;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الصلاحيات الأساسية (يدوياً مع أوصاف عربية)
        $basePermissions = [
            // صلاحيات الأدوار
            ['name' => 'role-list', 'description' => 'عرض قائمة الأدوار'],
            ['name' => 'role-create', 'description' => 'إنشاء دور جديد'],
            ['name' => 'role-edit', 'description' => 'تعديل الدور'],
            ['name' => 'role-delete', 'description' => 'حذف الدور'],

            // صلاحيات المستخدمين
            ['name' => 'user-list', 'description' => 'عرض قائمة المستخدمين'],
            ['name' => 'user-create', 'description' => 'إنشاء مستخدم جديد'],
            ['name' => 'user-edit', 'description' => 'تعديل المستخدم'],
            ['name' => 'user-delete', 'description' => 'حذف المستخدم'],
            ['name' => 'user-impersonate', 'description' => 'تسجيل الدخول كالمستخدم'],
            ['name' => 'user-show', 'description' => 'عرض تفاصيل المستخدم'],
            ['name' => 'user-update-password', 'description' => 'تحديث كلمة مرور المستخدم'],
            ['name' => 'user-toggle-status', 'description' => 'تبديل حالة المستخدم'],
            ['name' => 'user-login-logs', 'description' => 'عرض سجلات تسجيل الدخول'],
            ['name' => 'user-send-verification-otp', 'description' => 'إرسال رمز التحقق للمستخدم'],

            // صلاحيات إدارة تخصيصات المشرفين
            ['name' => 'supervisor-assignment-list', 'description' => 'عرض قائمة تخصيصات المشرفين'],
            ['name' => 'supervisor-assignment-show', 'description' => 'عرض صفحة تخصيص المشرف'],
            ['name' => 'supervisor-assignment-update', 'description' => 'تحديث تخصيصات المشرف'],
            ['name' => 'supervisor-assignment-manage-classes', 'description' => 'إدارة تخصيص صفوف المشرف'],
            ['name' => 'supervisor-assignment-manage-subjects', 'description' => 'إدارة تخصيص مواد المشرف'],

            // صلاحيات إدارة الصفوف
            ['name' => 'class-list', 'description' => 'عرض قائمة الصفوف'],
            ['name' => 'class-create', 'description' => 'إنشاء صف جديد'],
            ['name' => 'class-edit', 'description' => 'تعديل الصف'],
            ['name' => 'class-delete', 'description' => 'حذف الصف'],
            ['name' => 'class-show', 'description' => 'عرض تفاصيل الصف'],
            ['name' => 'class-enrolled-students', 'description' => 'عرض الطلاب المنضمين للصف'],
            ['name' => 'class-toggle-status', 'description' => 'تبديل حالة الصف (تفعيل/تعطيل)'],

            // صلاحيات إدارة المراحل
            ['name' => 'stage-list', 'description' => 'عرض قائمة المراحل'],
            ['name' => 'stage-create', 'description' => 'إنشاء مرحلة جديدة'],
            ['name' => 'stage-edit', 'description' => 'تعديل المرحلة'],
            ['name' => 'stage-delete', 'description' => 'حذف المرحلة'],
            ['name' => 'stage-show', 'description' => 'عرض تفاصيل المرحلة'],

            // صلاحيات الطلاب المتميزين (الصفحة الرئيسية)
            ['name' => 'distinguished-students-list', 'description' => 'عرض قائمة الطلاب المتميزين'],
            ['name' => 'distinguished-students-create', 'description' => 'إضافة طالب متميز'],
            ['name' => 'distinguished-students-edit', 'description' => 'تعديل الطالب المتميز'],
            ['name' => 'distinguished-students-delete', 'description' => 'حذف الطالب المتميز'],

            // صلاحيات روابط التواصل الاجتماعي (ديناميكية)
            ['name' => 'social-links-list', 'description' => 'عرض روابط التواصل الاجتماعي'],
            ['name' => 'social-links-create', 'description' => 'إضافة رابط تواصل'],
            ['name' => 'social-links-edit', 'description' => 'تعديل رابط التواصل'],
            ['name' => 'social-links-delete', 'description' => 'حذف رابط التواصل'],

            // صلاحيات إدارة المواد
            ['name' => 'subject-list', 'description' => 'عرض قائمة المواد'],
            ['name' => 'subject-create', 'description' => 'إنشاء مادة جديدة'],
            ['name' => 'subject-edit', 'description' => 'تعديل المادة'],
            ['name' => 'subject-delete', 'description' => 'حذف المادة'],
            ['name' => 'subject-show', 'description' => 'عرض تفاصيل المادة'],
            ['name' => 'subject-enrolled-students', 'description' => 'عرض الطلاب المنضمين للمادة'],
            ['name' => 'subject-toggle-status', 'description' => 'تبديل حالة المادة (تفعيل/تعطيل)'],

            // صلاحيات إدارة أقسام المواد
            ['name' => 'subject-section-create', 'description' => 'إنشاء قسم للمادة'],
            ['name' => 'subject-section-edit', 'description' => 'تعديل قسم المادة'],
            ['name' => 'subject-section-delete', 'description' => 'حذف قسم المادة'],

            // صلاحيات إدارة الوحدات
            ['name' => 'unit-create', 'description' => 'إنشاء وحدة جديدة'],
            ['name' => 'unit-edit', 'description' => 'تعديل الوحدة'],
            ['name' => 'unit-delete', 'description' => 'حذف الوحدة'],
            ['name' => 'unit-questions', 'description' => 'عرض أسئلة الوحدة'],
            ['name' => 'unit-attach-questions', 'description' => 'ربط أسئلة بالوحدة'],
            ['name' => 'unit-detach-question', 'description' => 'فك ربط سؤال من الوحدة'],
            ['name' => 'unit-available-questions', 'description' => 'عرض الأسئلة المتاحة للربط'],

            // صلاحيات إدارة الدروس
            ['name' => 'lesson-list', 'description' => 'عرض قائمة الدروس'],
            ['name' => 'lesson-create', 'description' => 'إنشاء درس جديد'],
            ['name' => 'lesson-edit', 'description' => 'تعديل الدرس'],
            ['name' => 'lesson-delete', 'description' => 'حذف الدرس'],
            ['name' => 'lesson-show', 'description' => 'عرض تفاصيل الدرس'],
            ['name' => 'lesson-approve-review', 'description' => 'الموافقة على تفعيل الدرس'],
            ['name' => 'lesson-reject-review', 'description' => 'رفض تفعيل الدرس'],
            ['name' => 'lesson-submit-for-review', 'description' => 'إرسال الدرس للمراجعة'],

            // صلاحيات إدارة مرفقات الدروس
            ['name' => 'lesson-attachment-create', 'description' => 'إضافة مرفق للدرس'],
            ['name' => 'lesson-attachment-edit', 'description' => 'تعديل مرفق الدرس'],
            ['name' => 'lesson-attachment-delete', 'description' => 'حذف مرفق الدرس'],

            // صلاحيات إدارة الأسئلة
            ['name' => 'question-list', 'description' => 'عرض قائمة الأسئلة'],
            ['name' => 'question-create', 'description' => 'إنشاء سؤال جديد'],
            ['name' => 'question-edit', 'description' => 'تعديل السؤال'],
            ['name' => 'question-delete', 'description' => 'حذف السؤال'],
            ['name' => 'question-show', 'description' => 'عرض تفاصيل السؤال'],
            ['name' => 'question-duplicate', 'description' => 'نسخ سؤال'],
            ['name' => 'question-toggle-status', 'description' => 'تبديل حالة السؤال'],
            ['name' => 'question-upload-image', 'description' => 'رفع صورة للسؤال'],
            ['name' => 'question-export', 'description' => 'تصدير الأسئلة'],
            ['name' => 'question-export-template', 'description' => 'تصدير قالب الأسئلة'],
            ['name' => 'question-import', 'description' => 'استيراد الأسئلة'],
            ['name' => 'question-show-import', 'description' => 'عرض صفحة الاستيراد'],

            // صلاحيات إدارة الاختبارات
            ['name' => 'quiz-list', 'description' => 'عرض قائمة الاختبارات'],
            ['name' => 'quiz-create', 'description' => 'إنشاء اختبار جديد'],
            ['name' => 'quiz-edit', 'description' => 'تعديل الاختبار'],
            ['name' => 'quiz-delete', 'description' => 'حذف الاختبار'],
            ['name' => 'quiz-show', 'description' => 'عرض تفاصيل الاختبار'],
            ['name' => 'quiz-questions', 'description' => 'إدارة أسئلة الاختبار'],
            ['name' => 'quiz-add-question', 'description' => 'إضافة سؤال للاختبار'],
            ['name' => 'quiz-remove-question', 'description' => 'إزالة سؤال من الاختبار'],
            ['name' => 'quiz-reorder-questions', 'description' => 'إعادة ترتيب أسئلة الاختبار'],
            ['name' => 'quiz-update-question-points', 'description' => 'تحديث درجة سؤال في الاختبار'],
            ['name' => 'quiz-duplicate', 'description' => 'نسخ اختبار'],
            ['name' => 'quiz-toggle-publish', 'description' => 'تبديل حالة نشر الاختبار'],
            ['name' => 'quiz-preview', 'description' => 'معاينة الاختبار'],
            ['name' => 'quiz-results', 'description' => 'عرض نتائج الاختبار'],
            ['name' => 'quiz-export-results', 'description' => 'تصدير نتائج الاختبار'],
            ['name' => 'quiz-get-subjects-by-class', 'description' => 'الحصول على المواد حسب الصف'],
            ['name' => 'quiz-get-classes-by-stage', 'description' => 'الحصول على الصفوف حسب المرحلة'],
            ['name' => 'quiz-get-units', 'description' => 'الحصول على الوحدات'],
            ['name' => 'quiz-approve-review', 'description' => 'الموافقة على نشر الاختبار'],
            ['name' => 'quiz-reject-review', 'description' => 'رفض نشر الاختبار'],
            ['name' => 'quiz-submit-for-review', 'description' => 'إرسال الاختبار للمراجعة'],

            // صلاحيات قائمة المراجعة
            ['name' => 'review-queue-list', 'description' => 'عرض قائمة المراجعة'],
            ['name' => 'review-queue-lessons', 'description' => 'عرض الدروس قيد المراجعة'],
            ['name' => 'review-queue-quizzes', 'description' => 'عرض الاختبارات قيد المراجعة'],

            // صلاحيات ملاحظات المراجعة
            ['name' => 'review-comment-create', 'description' => 'إنشاء ملاحظة مراجعة'],
            ['name' => 'review-comment-edit', 'description' => 'تعديل ملاحظة مراجعة'],
            ['name' => 'review-comment-delete', 'description' => 'حذف ملاحظة مراجعة'],
            ['name' => 'review-comment-reply', 'description' => 'الرد على ملاحظة مراجعة'],
            ['name' => 'review-comment-resolve', 'description' => 'حل/إلغاء حل ملاحظة مراجعة'],

            // صلاحيات إدارة محاولات الاختبارات
            ['name' => 'quiz-attempt-list', 'description' => 'عرض محاولات الاختبار'],
            ['name' => 'quiz-attempt-show', 'description' => 'عرض تفاصيل المحاولة'],
            ['name' => 'quiz-attempt-grade', 'description' => 'تصحيح المحاولة'],
            ['name' => 'quiz-attempt-save-grade', 'description' => 'حفظ التصحيح اليدوي'],
            ['name' => 'quiz-attempt-regrade', 'description' => 'إعادة تصحيح المحاولة'],
            ['name' => 'quiz-attempt-delete', 'description' => 'حذف المحاولة'],
            ['name' => 'quiz-attempt-reset-user', 'description' => 'إعادة تعيين محاولات طالب'],
            ['name' => 'quiz-attempt-needs-grading', 'description' => 'عرض المحاولات التي تحتاج تصحيح'],
            ['name' => 'quiz-attempt-statistics', 'description' => 'عرض إحصائيات المحاولات'],
            ['name' => 'quiz-attempt-grade-with-ai', 'description' => 'تصحيح إجابة مقالية باستخدام AI'],
            ['name' => 'quiz-attempt-grade-multiple-with-ai', 'description' => 'تصحيح عدة إجابات مقالية باستخدام AI'],

            // صلاحيات إدارة التسجيلات
            ['name' => 'enrollment-list', 'description' => 'عرض قائمة التسجيلات'],
            ['name' => 'enrollment-show', 'description' => 'عرض تفاصيل التسجيل'],
            ['name' => 'enrollment-create', 'description' => 'إنشاء تسجيل جديد'],
            ['name' => 'enrollment-delete', 'description' => 'حذف التسجيل'],
            ['name' => 'enrollment-pending-requests', 'description' => 'عرض طلبات الانضمام المعلقة'],
            ['name' => 'enrollment-approve', 'description' => 'قبول طلب انضمام'],
            ['name' => 'enrollment-reject', 'description' => 'رفض طلب انضمام'],
            ['name' => 'enrollment-approve-multiple', 'description' => 'قبول عدة طلبات دفعة واحدة'],
            ['name' => 'enrollment-reject-multiple', 'description' => 'رفض عدة طلبات دفعة واحدة'],
            ['name' => 'enrollment-search-students', 'description' => 'البحث عن الطلاب'],
            ['name' => 'enrollment-get-subjects-by-class', 'description' => 'الحصول على المواد حسب الصف'],
            ['name' => 'enrollment-class-pending-requests', 'description' => 'عرض طلبات الانضمام للصف المعلقة'],
            ['name' => 'enrollment-approve-class', 'description' => 'قبول طلب انضمام للصف'],
            ['name' => 'enrollment-reject-class', 'description' => 'رفض طلب انضمام للصف'],
            ['name' => 'enrollment-approve-multiple-class', 'description' => 'قبول عدة طلبات صف دفعة واحدة'],
            ['name' => 'enrollment-reject-multiple-class', 'description' => 'رفض عدة طلبات صف دفعة واحدة'],

            // صلاحيات إدارة المدفوعات
            ['name' => 'payment-list', 'description' => 'عرض قائمة المدفوعات'],
            ['name' => 'payment-show', 'description' => 'عرض تفاصيل الدفع'],
            ['name' => 'payment-review', 'description' => 'مراجعة الدفع'],
            ['name' => 'payment-approve', 'description' => 'الموافقة على الدفع'],
            ['name' => 'payment-reject', 'description' => 'رفض الدفع'],
            ['name' => 'payment-download-receipt', 'description' => 'تحميل وصل الدفع'],

            // صلاحيات إدارة وسائل الدفع المخصصة
            ['name' => 'custom-payment-method-list', 'description' => 'عرض قائمة وسائل الدفع المخصصة'],
            ['name' => 'custom-payment-method-create', 'description' => 'إنشاء وسيلة دفع مخصصة'],
            ['name' => 'custom-payment-method-edit', 'description' => 'تعديل وسيلة الدفع المخصصة'],
            ['name' => 'custom-payment-method-delete', 'description' => 'حذف وسيلة الدفع المخصصة'],
            ['name' => 'custom-payment-method-show', 'description' => 'عرض تفاصيل وسيلة الدفع المخصصة'],

            // صلاحيات إدارة التقارير
            ['name' => 'report-view', 'description' => 'عرض التقارير'],
            ['name' => 'report-export', 'description' => 'تصدير التقارير'],

            // صلاحيات إدارة الإعدادات
            ['name' => 'settings-manage', 'description' => 'إدارة إعدادات النظام'],

            // صلاحيات لوحة التحكم
            ['name' => 'dashboard-view', 'description' => 'عرض لوحة التحكم'],
            ['name' => 'dashboard-widgets', 'description' => 'إدارة عناصر لوحة التحكم'],
            ['name' => 'dashboard-save-widgets', 'description' => 'حفظ عناصر لوحة التحكم'],

            // صلاحيات إدارة العملات
            ['name' => 'currency-list', 'description' => 'عرض قائمة العملات'],
            ['name' => 'currency-create', 'description' => 'إنشاء عملة جديدة'],
            ['name' => 'currency-edit', 'description' => 'تعديل العملة'],
            ['name' => 'currency-delete', 'description' => 'حذف العملة'],
            ['name' => 'currency-show', 'description' => 'عرض تفاصيل العملة'],

            // صلاحيات إدارة أسعار الصرف
            ['name' => 'exchange-rate-list', 'description' => 'عرض قائمة أسعار الصرف'],
            ['name' => 'exchange-rate-create', 'description' => 'إنشاء سعر صرف جديد'],
            ['name' => 'exchange-rate-edit', 'description' => 'تعديل سعر الصرف'],
            ['name' => 'exchange-rate-delete', 'description' => 'حذف سعر الصرف'],
            ['name' => 'exchange-rate-show', 'description' => 'عرض تفاصيل سعر الصرف'],

            // صلاحيات إدارة WhatsApp
            ['name' => 'whats-app-settings-list', 'description' => 'عرض إعدادات WhatsApp'],
            ['name' => 'whats-app-settings-update', 'description' => 'تحديث إعدادات WhatsApp'],
            ['name' => 'whats-app-settings-test-connection', 'description' => 'اختبار اتصال WhatsApp'],
            ['name' => 'whats-app-message-list', 'description' => 'عرض قائمة رسائل WhatsApp'],
            ['name' => 'whats-app-message-send', 'description' => 'إرسال رسالة WhatsApp'],
            ['name' => 'whats-app-message-get-students-count', 'description' => 'الحصول على عدد الطلاب'],
            ['name' => 'whats-app-template-list', 'description' => 'عرض قائمة قوالب WhatsApp'],
            ['name' => 'whats-app-template-create', 'description' => 'إنشاء قالب WhatsApp'],
            ['name' => 'whats-app-template-edit', 'description' => 'تعديل قالب WhatsApp'],
            ['name' => 'whats-app-template-delete', 'description' => 'حذف قالب WhatsApp'],

            // صلاحيات إدارة البريد الإلكتروني
            ['name' => 'email-settings-list', 'description' => 'عرض إعدادات البريد الإلكتروني'],
            ['name' => 'email-settings-update', 'description' => 'تحديث إعدادات البريد الإلكتروني'],
            ['name' => 'email-settings-test-connection', 'description' => 'اختبار اتصال البريد الإلكتروني'],
            ['name' => 'email-template-list', 'description' => 'عرض قائمة قوالب البريد الإلكتروني'],
            ['name' => 'email-template-create', 'description' => 'إنشاء قالب بريد إلكتروني'],
            ['name' => 'email-template-edit', 'description' => 'تعديل قالب البريد الإلكتروني'],
            ['name' => 'email-template-delete', 'description' => 'حذف قالب البريد الإلكتروني'],
            ['name' => 'email-log-list', 'description' => 'عرض سجلات البريد الإلكتروني'],
            ['name' => 'email-log-show', 'description' => 'عرض تفاصيل سجل البريد الإلكتروني'],
            ['name' => 'email-log-delete', 'description' => 'حذف سجل البريد الإلكتروني'],

            // صلاحيات إدارة SMS
            ['name' => 'sms-settings-list', 'description' => 'عرض إعدادات SMS'],
            ['name' => 'sms-settings-update', 'description' => 'تحديث إعدادات SMS'],
            ['name' => 'sms-template-list', 'description' => 'عرض قائمة قوالب SMS'],
            ['name' => 'sms-template-create', 'description' => 'إنشاء قالب SMS'],
            ['name' => 'sms-template-edit', 'description' => 'تعديل قالب SMS'],
            ['name' => 'sms-template-delete', 'description' => 'حذف قالب SMS'],
            ['name' => 'sms-log-list', 'description' => 'عرض سجلات SMS'],

            // صلاحيات إدارة النسخ الاحتياطي
            ['name' => 'backup-list', 'description' => 'عرض قائمة النسخ الاحتياطي'],
            ['name' => 'backup-create', 'description' => 'إنشاء نسخة احتياطية'],
            ['name' => 'backup-restore', 'description' => 'استعادة نسخة احتياطية'],
            ['name' => 'backup-delete', 'description' => 'حذف نسخة احتياطية'],
            ['name' => 'backup-storage-list', 'description' => 'عرض قائمة تخزين النسخ الاحتياطي'],
            ['name' => 'backup-storage-create', 'description' => 'إنشاء تخزين نسخ احتياطي'],
            ['name' => 'backup-storage-edit', 'description' => 'تعديل تخزين النسخ الاحتياطي'],
            ['name' => 'backup-storage-delete', 'description' => 'حذف تخزين النسخ الاحتياطي'],
            ['name' => 'backup-storage-test', 'description' => 'اختبار تخزين النسخ الاحتياطي'],
            ['name' => 'backup-storage-test-connection', 'description' => 'اختبار اتصال تخزين النسخ الاحتياطي'],
            ['name' => 'backup-schedule-list', 'description' => 'عرض قائمة جدولة النسخ الاحتياطي'],
            ['name' => 'backup-schedule-create', 'description' => 'إنشاء جدولة نسخ احتياطي'],
            ['name' => 'backup-schedule-edit', 'description' => 'تعديل جدولة النسخ الاحتياطي'],
            ['name' => 'backup-schedule-delete', 'description' => 'حذف جدولة النسخ الاحتياطي'],

            // صلاحيات إدارة التخزين
            ['name' => 'app-storage-list', 'description' => 'عرض قائمة تخزين التطبيق'],
            ['name' => 'app-storage-create', 'description' => 'إنشاء تخزين تطبيق'],
            ['name' => 'app-storage-edit', 'description' => 'تعديل تخزين التطبيق'],
            ['name' => 'app-storage-delete', 'description' => 'حذف تخزين التطبيق'],
            ['name' => 'storage-disk-mapping-list', 'description' => 'عرض قائمة تعيينات أقراص التخزين'],
            ['name' => 'storage-disk-mapping-create', 'description' => 'إنشاء تعيين قرص تخزين'],
            ['name' => 'storage-disk-mapping-edit', 'description' => 'تعديل تعيين قرص التخزين'],
            ['name' => 'storage-disk-mapping-delete', 'description' => 'حذف تعيين قرص التخزين'],

            // صلاحيات إدارة الذكاء الاصطناعي
            ['name' => 'ai-question-generation-list', 'description' => 'عرض قائمة توليد الأسئلة بالذكاء الاصطناعي'],
            ['name' => 'ai-question-generation-create', 'description' => 'إنشاء توليد أسئلة بالذكاء الاصطناعي'],
            ['name' => 'ai-question-generation-create-advanced', 'description' => 'إنشاء توليد أسئلة متقدم بالذكاء الاصطناعي'],
            ['name' => 'ai-question-generation-show', 'description' => 'عرض تفاصيل توليد الأسئلة'],
            ['name' => 'ai-question-generation-process', 'description' => 'معالجة توليد الأسئلة'],
            ['name' => 'ai-question-generation-save', 'description' => 'حفظ توليد الأسئلة'],
            ['name' => 'ai-question-generation-save-selected', 'description' => 'حفظ الأسئلة المحددة'],
            ['name' => 'ai-question-generation-regenerate', 'description' => 'إعادة توليد الأسئلة'],
            ['name' => 'ai-content-summarize', 'description' => 'تلخيص المحتوى بالذكاء الاصطناعي'],
            ['name' => 'ai-content-lesson-summary', 'description' => 'تلخيص الدرس بالذكاء الاصطناعي'],
            ['name' => 'ai-content-improve', 'description' => 'تحسين المحتوى بالذكاء الاصطناعي'],
            ['name' => 'ai-content-grammar-check', 'description' => 'فحص القواعد بالذكاء الاصطناعي'],
            ['name' => 'ai-settings-list', 'description' => 'عرض إعدادات الذكاء الاصطناعي'],
            ['name' => 'ai-settings-update', 'description' => 'تحديث إعدادات الذكاء الاصطناعي'],
            ['name' => 'ai-model-list', 'description' => 'عرض قائمة نماذج الذكاء الاصطناعي'],
            ['name' => 'ai-model-create', 'description' => 'إنشاء نموذج ذكاء اصطناعي'],
            ['name' => 'ai-model-edit', 'description' => 'تعديل نموذج الذكاء الاصطناعي'],
            ['name' => 'ai-model-delete', 'description' => 'حذف نموذج الذكاء الاصطناعي'],

            // صلاحيات إدارة التحفيز
            ['name' => 'gamification-list', 'description' => 'عرض إعدادات التحفيز'],
            ['name' => 'gamification-update', 'description' => 'تحديث إعدادات التحفيز'],
            ['name' => 'achievement-list', 'description' => 'عرض قائمة الإنجازات'],
            ['name' => 'achievement-create', 'description' => 'إنشاء إنجاز جديد'],
            ['name' => 'achievement-edit', 'description' => 'تعديل الإنجاز'],
            ['name' => 'achievement-delete', 'description' => 'حذف الإنجاز'],
            ['name' => 'badge-list', 'description' => 'عرض قائمة الشارات'],
            ['name' => 'badge-create', 'description' => 'إنشاء شارة جديدة'],
            ['name' => 'badge-edit', 'description' => 'تعديل الشارة'],
            ['name' => 'badge-delete', 'description' => 'حذف الشارة'],
            ['name' => 'challenge-list', 'description' => 'عرض قائمة التحديات'],
            ['name' => 'challenge-create', 'description' => 'إنشاء تحدي جديد'],
            ['name' => 'challenge-edit', 'description' => 'تعديل التحدي'],
            ['name' => 'challenge-delete', 'description' => 'حذف التحدي'],
            ['name' => 'leaderboard-list', 'description' => 'عرض لوحة المتصدرين'],
            ['name' => 'reward-list', 'description' => 'عرض قائمة المكافآت'],
            ['name' => 'reward-create', 'description' => 'إنشاء مكافأة جديدة'],
            ['name' => 'reward-edit', 'description' => 'تعديل المكافأة'],
            ['name' => 'reward-delete', 'description' => 'حذف المكافأة'],
            ['name' => 'level-list', 'description' => 'عرض قائمة المستويات'],
            ['name' => 'level-create', 'description' => 'إنشاء مستوى جديد'],
            ['name' => 'level-edit', 'description' => 'تعديل المستوى'],
            ['name' => 'level-delete', 'description' => 'حذف المستوى'],

            // صلاحيات إدارة الإشعارات
            ['name' => 'notification-create', 'description' => 'إرسال إشعار'],
            ['name' => 'notification-preference-list', 'description' => 'عرض تفضيلات الإشعارات'],
            ['name' => 'notification-preference-update', 'description' => 'تحديث تفضيلات الإشعارات'],

            // صلاحيات إدارة سجلات تسجيل الدخول
            ['name' => 'login-log-list', 'description' => 'عرض سجلات تسجيل الدخول'],
            ['name' => 'login-log-show', 'description' => 'عرض تفاصيل سجل تسجيل الدخول'],
            ['name' => 'login-log-delete', 'description' => 'حذف سجل تسجيل الدخول'],

            // صلاحيات إدارة الجلسات
            ['name' => 'user-session-list', 'description' => 'عرض قائمة جلسات المستخدمين'],
            ['name' => 'user-session-delete', 'description' => 'حذف جلسة مستخدم'],

            // صلاحيات إدارة تقدم الطلاب
            ['name' => 'student-progress-list', 'description' => 'عرض قائمة تقدم الطلاب'],
            ['name' => 'student-progress-show-student', 'description' => 'عرض تقدم طالب'],
            ['name' => 'student-progress-show-student-subject', 'description' => 'عرض تقدم طالب في مادة'],
            ['name' => 'student-progress-get-subjects-by-class', 'description' => 'الحصول على المواد حسب الصف'],

            // صلاحيات إدارة المهام الأسبوعية
            ['name' => 'weekly-task-list', 'description' => 'عرض قائمة المهام الأسبوعية'],
            ['name' => 'weekly-task-create', 'description' => 'إنشاء مهمة أسبوعية جديدة'],
            ['name' => 'weekly-task-edit', 'description' => 'تعديل المهمة الأسبوعية'],
            ['name' => 'weekly-task-delete', 'description' => 'حذف المهمة الأسبوعية'],

            // صلاحيات إدارة المهام اليومية
            ['name' => 'daily-task-list', 'description' => 'عرض قائمة المهام اليومية'],
            ['name' => 'daily-task-create', 'description' => 'إنشاء مهمة يومية جديدة'],
            ['name' => 'daily-task-edit', 'description' => 'تعديل المهمة اليومية'],
            ['name' => 'daily-task-delete', 'description' => 'حذف المهمة اليومية'],

            // صلاحيات إدارة تخصيصات المعلمين
            ['name' => 'teacher-assignment-list', 'description' => 'عرض قائمة تخصيصات المعلمين'],
            ['name' => 'teacher-assignment-show', 'description' => 'عرض تفاصيل تخصيص المعلم'],
            ['name' => 'teacher-assignment-update', 'description' => 'تحديث تخصيص المعلم'],
            ['name' => 'teacher-assignment-manage-classes', 'description' => 'إدارة تخصيص صفوف المعلم'],
            ['name' => 'teacher-assignment-manage-subjects', 'description' => 'إدارة تخصيص مواد المعلم'],
            ['name' => 'teacher-progress-view', 'description' => 'عرض تقدم المعلمين'],

            // صلاحيات إدارة السنوات والأسابيع الدراسية
            ['name' => 'academic-year-list', 'description' => 'عرض السنوات الدراسية'],
            ['name' => 'academic-year-create', 'description' => 'إنشاء سنة دراسية'],
            ['name' => 'academic-year-edit', 'description' => 'تعديل سنة دراسية'],
            ['name' => 'academic-year-delete', 'description' => 'حذف سنة دراسية'],
            ['name' => 'academic-year-activate', 'description' => 'تفعيل سنة دراسية'],
            ['name' => 'academic-week-list', 'description' => 'عرض الأسابيع الدراسية'],
            ['name' => 'academic-week-create', 'description' => 'إنشاء أسبوع دراسي'],
            ['name' => 'academic-week-edit', 'description' => 'تعديل أسبوع دراسي'],
            ['name' => 'academic-week-delete', 'description' => 'حذف أسبوع دراسي'],
            ['name' => 'academic-week-generate', 'description' => 'توليد أسابيع السنة الدراسية'],

            // صلاحيات لوحة التحليلات
            ['name' => 'analytics-dashboard-view', 'description' => 'عرض لوحة التحليلات'],
        ];

        // حفظ الصلاحيات الأساسية
        foreach ($basePermissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['description' => $permission['description'] ?? null]
            );
        }

        // اكتشاف الصلاحيات تلقائياً من Controllers
        try {
            $discoveryService = app(PermissionDiscoveryService::class);
            $discoveredPermissions = $discoveryService->discoverFromControllers();
            
            // دمج الصلاحيات المكتشفة (تجاهل المكررة)
            $existingNames = array_column($basePermissions, 'name');
            foreach ($discoveredPermissions as $permission) {
                if (!in_array($permission['name'], $existingNames)) {
                    Permission::updateOrCreate(
                        ['name' => $permission['name'], 'guard_name' => 'web'],
                        ['description' => $permission['description'] ?? null]
                    );
                }
            }
        } catch (\Exception $e) {
            // في حالة فشل الاكتشاف، نكمل مع الصلاحيات الأساسية فقط
            \Log::warning('Failed to discover permissions from controllers: ' . $e->getMessage());
        }
    }
}
