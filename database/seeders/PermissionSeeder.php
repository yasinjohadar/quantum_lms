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
            ['name' => 'user-show', 'description' => 'عرض تفاصيل المستخدم'],
            ['name' => 'user-update-password', 'description' => 'تحديث كلمة مرور المستخدم'],
            ['name' => 'user-toggle-status', 'description' => 'تبديل حالة المستخدم'],
            ['name' => 'user-login-logs', 'description' => 'عرض سجلات تسجيل الدخول'],
            ['name' => 'user-send-verification-otp', 'description' => 'إرسال رمز التحقق للمستخدم'],

            // صلاحيات إدارة الصفوف
            ['name' => 'class-list', 'description' => 'عرض قائمة الصفوف'],
            ['name' => 'class-create', 'description' => 'إنشاء صف جديد'],
            ['name' => 'class-edit', 'description' => 'تعديل الصف'],
            ['name' => 'class-delete', 'description' => 'حذف الصف'],
            ['name' => 'class-show', 'description' => 'عرض تفاصيل الصف'],
            ['name' => 'class-enrolled-students', 'description' => 'عرض الطلاب المنضمين للصف'],

            // صلاحيات إدارة المواد
            ['name' => 'subject-list', 'description' => 'عرض قائمة المواد'],
            ['name' => 'subject-create', 'description' => 'إنشاء مادة جديدة'],
            ['name' => 'subject-edit', 'description' => 'تعديل المادة'],
            ['name' => 'subject-delete', 'description' => 'حذف المادة'],
            ['name' => 'subject-show', 'description' => 'عرض تفاصيل المادة'],
            ['name' => 'subject-enrolled-students', 'description' => 'عرض الطلاب المنضمين للمادة'],

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
            ['name' => 'lesson-create', 'description' => 'إنشاء درس جديد'],
            ['name' => 'lesson-edit', 'description' => 'تعديل الدرس'],
            ['name' => 'lesson-delete', 'description' => 'حذف الدرس'],
            ['name' => 'lesson-show', 'description' => 'عرض تفاصيل الدرس'],
            ['name' => 'lesson-approve-review', 'description' => 'الموافقة على تفعيل الدرس'],
            ['name' => 'lesson-reject-review', 'description' => 'رفض تفعيل الدرس'],

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
            ['name' => 'quiz-get-units', 'description' => 'الحصول على الوحدات'],

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

            // صلاحيات إدارة المكتبة
            ['name' => 'library-list', 'description' => 'عرض قائمة عناصر المكتبة'],
            ['name' => 'library-create', 'description' => 'إضافة عنصر للمكتبة'],
            ['name' => 'library-edit', 'description' => 'تعديل عنصر المكتبة'],
            ['name' => 'library-delete', 'description' => 'حذف عنصر المكتبة'],
            ['name' => 'library-show', 'description' => 'عرض تفاصيل عنصر المكتبة'],
            ['name' => 'library-preview', 'description' => 'معاينة عنصر المكتبة'],
            ['name' => 'library-download', 'description' => 'تحميل عنصر المكتبة'],
            ['name' => 'library-stats', 'description' => 'عرض إحصائيات عنصر المكتبة'],
            ['name' => 'library-get-subjects-by-class', 'description' => 'الحصول على المواد حسب الصف'],

            // صلاحيات إدارة التقارير
            ['name' => 'report-view', 'description' => 'عرض التقارير'],
            ['name' => 'report-export', 'description' => 'تصدير التقارير'],

            // صلاحيات إدارة الإعدادات
            ['name' => 'settings-manage', 'description' => 'إدارة إعدادات النظام'],

            // صلاحيات لوحة التحكم
            ['name' => 'dashboard-view', 'description' => 'عرض لوحة التحكم'],
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
