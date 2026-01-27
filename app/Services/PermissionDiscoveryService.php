<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class PermissionDiscoveryService
{
    /**
     * اكتشاف الصلاحيات تلقائياً من Controllers
     */
    public function discoverFromControllers(): array
    {
        $permissions = [];
        $controllersPath = app_path('Http/Controllers/Admin');
        
        if (!File::exists($controllersPath)) {
            return $permissions;
        }

        $files = File::allFiles($controllersPath);
        
        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file->getPathname());
            if (!$className) {
                continue;
            }

            $fullClassName = "App\\Http\\Controllers\\Admin\\{$className}";
            
            if (!class_exists($fullClassName)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($fullClassName);
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                foreach ($methods as $method) {
                    // تجاهل الدوال الموروثة من Controller
                    if ($method->getDeclaringClass()->getName() !== $fullClassName) {
                        continue;
                    }

                    // تجاهل الدوال الخاصة (__construct, __invoke, etc.)
                    if (strpos($method->getName(), '__') === 0) {
                        continue;
                    }

                    // استخراج اسم الصلاحية من اسم Controller والدالة
                    $permissionName = $this->generatePermissionName($className, $method->getName());
                    $description = $this->generateDescription($className, $method->getName());
                    
                    $permissions[] = [
                        'name' => $permissionName,
                        'description' => $description,
                    ];
                }
            } catch (\Exception $e) {
                // تجاهل الأخطاء في Reflection
                continue;
            }
        }

        return $permissions;
    }

    /**
     * استخراج اسم الـ Class من ملف
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);
        
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            $namespace = $namespaceMatch[1];
        } else {
            return null;
        }

        if (preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            $className = $classMatch[1];
            return $className;
        }

        return null;
    }

    /**
     * توليد اسم الصلاحية من اسم Controller والدالة
     */
    private function generatePermissionName(string $controllerName, string $methodName): string
    {
        // إزالة "Controller" من نهاية الاسم
        $resource = str_replace('Controller', '', $controllerName);
        $resource = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $resource));
        
        // تحويل اسم الدالة إلى snake_case
        $action = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $methodName));
        
        // معالجة بعض الأسماء الشائعة
        $actionMap = [
            'index' => 'list',
            'store' => 'create',
            'update' => 'edit',
            'destroy' => 'delete',
            'show' => 'show',
        ];

        if (isset($actionMap[$action])) {
            $action = $actionMap[$action];
        }

        return "{$resource}-{$action}";
    }

    /**
     * توليد وصف عربي للصلاحية
     */
    private function generateDescription(string $controllerName, string $methodName): string
    {
        // إزالة "Controller" من نهاية الاسم
        $resource = str_replace('Controller', '', $controllerName);
        
        // تحويل اسم الدالة إلى وصف عربي
        $actionDescriptions = [
            'index' => 'عرض قائمة',
            'create' => 'إنشاء',
            'store' => 'إنشاء',
            'edit' => 'تعديل',
            'update' => 'تعديل',
            'destroy' => 'حذف',
            'delete' => 'حذف',
            'show' => 'عرض تفاصيل',
            'enrolledStudents' => 'عرض الطلاب المنضمين',
            'enrolled-students' => 'عرض الطلاب المنضمين',
            'questions' => 'عرض الأسئلة',
            'attachQuestions' => 'ربط أسئلة',
            'attach-questions' => 'ربط أسئلة',
            'detachQuestion' => 'فك ربط سؤال',
            'detach-question' => 'فك ربط سؤال',
            'availableQuestions' => 'عرض الأسئلة المتاحة',
            'available-questions' => 'عرض الأسئلة المتاحة',
            'addQuestion' => 'إضافة سؤال',
            'add-question' => 'إضافة سؤال',
            'removeQuestion' => 'إزالة سؤال',
            'remove-question' => 'إزالة سؤال',
            'reorderQuestions' => 'إعادة ترتيب أسئلة',
            'reorder-questions' => 'إعادة ترتيب أسئلة',
            'updateQuestionPoints' => 'تحديث درجة سؤال',
            'update-question-points' => 'تحديث درجة سؤال',
            'duplicate' => 'نسخ',
            'togglePublish' => 'تبديل حالة نشر',
            'toggle-publish' => 'تبديل حالة نشر',
            'publish' => 'نشر',
            'unpublish' => 'إلغاء نشر',
            'toggleStatus' => 'تبديل الحالة',
            'toggle-status' => 'تبديل الحالة',
            'preview' => 'معاينة',
            'results' => 'عرض النتائج',
            'exportResults' => 'تصدير النتائج',
            'export-results' => 'تصدير النتائج',
            'export' => 'تصدير',
            'exportTemplate' => 'تصدير قالب',
            'export-template' => 'تصدير قالب',
            'import' => 'استيراد',
            'showImport' => 'عرض صفحة الاستيراد',
            'show-import' => 'عرض صفحة الاستيراد',
            'uploadImage' => 'رفع صورة',
            'upload-image' => 'رفع صورة',
            'approve' => 'قبول',
            'reject' => 'رفض',
            'approveMultiple' => 'قبول عدة طلبات دفعة واحدة',
            'approve-multiple' => 'قبول عدة طلبات دفعة واحدة',
            'rejectMultiple' => 'رفض عدة طلبات دفعة واحدة',
            'reject-multiple' => 'رفض عدة طلبات دفعة واحدة',
            'pendingRequests' => 'عرض الطلبات المعلقة',
            'pending-requests' => 'عرض الطلبات المعلقة',
            'searchStudents' => 'البحث عن الطلاب',
            'search-students' => 'البحث عن الطلاب',
            'getSubjectsByClass' => 'الحصول على المواد حسب الصف',
            'get-subjects-by-class' => 'الحصول على المواد حسب الصف',
            'getUnits' => 'الحصول على الوحدات',
            'get-units' => 'الحصول على الوحدات',
            'review' => 'مراجعة',
            'reviewPayment' => 'مراجعة الدفع',
            'review-payment' => 'مراجعة الدفع',
            'approvePayment' => 'الموافقة على الدفع',
            'approve-payment' => 'الموافقة على الدفع',
            'rejectPayment' => 'رفض الدفع',
            'reject-payment' => 'رفض الدفع',
            'downloadReceipt' => 'تحميل الوصل',
            'download-receipt' => 'تحميل الوصل',
            'updatePassword' => 'تحديث كلمة المرور',
            'update-password' => 'تحديث كلمة المرور',
            'loginLogs' => 'عرض سجلات تسجيل الدخول',
            'login-logs' => 'عرض سجلات تسجيل الدخول',
            'sendVerificationOTP' => 'إرسال رمز التحقق',
            'send-verification-otp' => 'إرسال رمز التحقق',
            'grade' => 'تصحيح',
            'saveGrade' => 'حفظ التصحيح',
            'save-grade' => 'حفظ التصحيح',
            'regrade' => 'إعادة تصحيح',
            'resetUserAttempts' => 'إعادة تعيين محاولات طالب',
            'reset-user-attempts' => 'إعادة تعيين محاولات طالب',
            'needsGrading' => 'عرض المحاولات التي تحتاج تصحيح',
            'needs-grading' => 'عرض المحاولات التي تحتاج تصحيح',
            'statistics' => 'عرض الإحصائيات',
            'gradeWithAI' => 'تصحيح باستخدام AI',
            'grade-with-ai' => 'تصحيح باستخدام AI',
            'gradeMultipleWithAI' => 'تصحيح عدة إجابات باستخدام AI',
            'grade-multiple-with-ai' => 'تصحيح عدة إجابات باستخدام AI',
            'classPendingRequests' => 'عرض طلبات الانضمام للصف المعلقة',
            'class-pending-requests' => 'عرض طلبات الانضمام للصف المعلقة',
            'approveClass' => 'قبول طلب انضمام للصف',
            'approve-class' => 'قبول طلب انضمام للصف',
            'approveClassEnrollment' => 'قبول طلب انضمام للصف',
            'approve-class-enrollment' => 'قبول طلب انضمام للصف',
            'rejectClass' => 'رفض طلب انضمام للصف',
            'reject-class' => 'رفض طلب انضمام للصف',
            'rejectClassEnrollment' => 'رفض طلب انضمام للصف',
            'reject-class-enrollment' => 'رفض طلب انضمام للصف',
            'approveMultipleClass' => 'قبول عدة طلبات صف دفعة واحدة',
            'approve-multiple-class' => 'قبول عدة طلبات صف دفعة واحدة',
            'approveMultipleClassEnrollments' => 'قبول عدة طلبات صف دفعة واحدة',
            'approve-multiple-class-enrollments' => 'قبول عدة طلبات صف دفعة واحدة',
            'rejectMultipleClass' => 'رفض عدة طلبات صف دفعة واحدة',
            'reject-multiple-class' => 'رفض عدة طلبات صف دفعة واحدة',
            'rejectMultipleClassEnrollments' => 'رفض عدة طلبات صف دفعة واحدة',
            'reject-multiple-class-enrollments' => 'رفض عدة طلبات صف دفعة واحدة',
            'approveReview' => 'الموافقة على المراجعة',
            'approve-review' => 'الموافقة على المراجعة',
            'rejectReview' => 'رفض المراجعة',
            'reject-review' => 'رفض المراجعة',
            'submitForReview' => 'إرسال للمراجعة',
            'submit-for-review' => 'إرسال للمراجعة',
            'getAssignableItems' => 'الحصول على العناصر القابلة للتعيين',
            'get-assignable-items' => 'الحصول على العناصر القابلة للتعيين',
            'getEvents' => 'الحصول على الأحداث',
            'get-events' => 'الحصول على الأحداث',
            'testConnection' => 'اختبار الاتصال',
            'test-connection' => 'اختبار الاتصال',
            'test' => 'اختبار',
            'process' => 'معالجة',
            'save' => 'حفظ',
            'saveSelected' => 'حفظ المحدد',
            'save-selected' => 'حفظ المحدد',
            'regenerate' => 'إعادة توليد',
            'summarize' => 'تلخيص',
            'lessonSummary' => 'تلخيص الدرس',
            'lesson-summary' => 'تلخيص الدرس',
            'improve' => 'تحسين',
            'grammarCheck' => 'فحص القواعد',
            'grammar-check' => 'فحص القواعد',
            'reset' => 'إعادة تعيين',
            'schedule' => 'جدولة',
            'widgets' => 'العناصر',
            'saveWidgets' => 'حفظ العناصر',
            'save-widgets' => 'حفظ العناصر',
            'showStudent' => 'عرض الطالب',
            'show-student' => 'عرض الطالب',
            'showStudentSubject' => 'عرض مادة الطالب',
            'show-student-subject' => 'عرض مادة الطالب',
            'assignments' => 'التخصيصات',
            'assignment' => 'التخصيص',
            'lessons' => 'الدروس',
            'quizzes' => 'الاختبارات',
            'assignments' => 'الواجبات',
            'reply' => 'الرد',
            'resolve' => 'حل',
            'unresolve' => 'إلغاء حل',
        ];

        $action = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $methodName));
        $actionDescription = $actionDescriptions[$action] ?? $action;

        // تحويل اسم الـ Resource إلى عربي
        $resourceDescriptions = [
            'subject' => 'المادة',
            'class' => 'الصف',
            'stage' => 'المرحلة',
            'lesson' => 'الدرس',
            'unit' => 'الوحدة',
            'quiz' => 'الاختبار',
            'question' => 'السؤال',
            'enrollment' => 'التسجيل',
            'payment' => 'الدفع',
            'custom-payment-method' => 'وسيلة الدفع المخصصة',
            'user' => 'المستخدم',
            'archived-user' => 'المستخدم المؤرشف',
            'role' => 'الدور',
            'library' => 'عنصر المكتبة',
            'library-item' => 'عنصر المكتبة',
            'library-category' => 'فئة المكتبة',
            'library-tag' => 'علامة المكتبة',
            'report' => 'التقرير',
            'settings' => 'الإعدادات',
            'dashboard' => 'لوحة التحكم',
            'admin-dashboard' => 'لوحة التحكم',
            'subject-section' => 'قسم المادة',
            'lesson-attachment' => 'مرفق الدرس',
            'quiz-attempt' => 'محاولة الاختبار',
            'assignment' => 'الواجب',
            'review-queue' => 'قائمة المراجعة',
            'review-comment' => 'ملاحظة المراجعة',
            'currency' => 'العملة',
            'exchange-rate' => 'سعر الصرف',
            'calendar' => 'التقويم',
            'calendar-event' => 'حدث التقويم',
            'attendance' => 'الحضور',
            'live-session' => 'الجلسة الحية',
            'zoom' => 'Zoom',
            'zoom-settings' => 'إعدادات Zoom',
            'zoom-meeting' => 'اجتماع Zoom',
            'whats-app' => 'WhatsApp',
            'whats-app-settings' => 'إعدادات WhatsApp',
            'whats-app-message' => 'رسالة WhatsApp',
            'email' => 'البريد الإلكتروني',
            'email-settings' => 'إعدادات البريد الإلكتروني',
            'email-template' => 'قالب البريد الإلكتروني',
            'email-log' => 'سجل البريد الإلكتروني',
            'sms' => 'SMS',
            'sms-settings' => 'إعدادات SMS',
            'sms-template' => 'قالب SMS',
            'sms-log' => 'سجل SMS',
            'backup' => 'النسخ الاحتياطي',
            'backup-storage' => 'تخزين النسخ الاحتياطي',
            'backup-schedule' => 'جدولة النسخ الاحتياطي',
            'backup-storage-analytics' => 'تحليلات تخزين النسخ الاحتياطي',
            'storage' => 'التخزين',
            'app-storage' => 'تخزين التطبيق',
            'app-storage-analytics' => 'تحليلات تخزين التطبيق',
            'storage-disk-mapping' => 'تعيين قرص التخزين',
            'ai' => 'الذكاء الاصطناعي',
            'ai-question-generation' => 'توليد الأسئلة بالذكاء الاصطناعي',
            'ai-content' => 'محتوى الذكاء الاصطناعي',
            'ai-settings' => 'إعدادات الذكاء الاصطناعي',
            'ai-grading-settings' => 'إعدادات تصحيح الذكاء الاصطناعي',
            'ai-student-feedback' => 'تغذية راجعة الطالب بالذكاء الاصطناعي',
            'ai-model' => 'نموذج الذكاء الاصطناعي',
            'ai-question-solving' => 'حل الأسئلة بالذكاء الاصطناعي',
            'gamification' => 'التحفيز',
            'notification' => 'الإشعار',
            'notification-preference' => 'تفضيلات الإشعار',
            'login-log' => 'سجل تسجيل الدخول',
            'user-session' => 'جلسة المستخدم',
            'student-progress' => 'تقدم الطالب',
            'admin-student-progress' => 'تقدم الطالب',
            'review' => 'التقييم',
            'group' => 'المجموعة',
            'weekly-task' => 'المهمة الأسبوعية',
            'daily-task' => 'المهمة اليومية',
            'reminder' => 'التذكير',
            'level' => 'المستوى',
            'achievement' => 'الإنجاز',
            'badge' => 'الشارة',
            'challenge' => 'التحدي',
            'leaderboard' => 'لوحة المتصدرين',
            'certificate' => 'الشهادة',
            'reward' => 'المكافأة',
            'teacher-assignment' => 'تخصيص المعلم',
            'supervisor-assignment' => 'تخصيص المشرف',
            'analytics-dashboard' => 'لوحة التحليلات',
            'library-dashboard' => 'لوحة المكتبة',
            'library-report' => 'تقرير المكتبة',
        ];

        $resource = str_replace('Controller', '', $controllerName);
        $resource = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $resource));
        $resourceDescription = $resourceDescriptions[$resource] ?? $resource;

        return "{$actionDescription} {$resourceDescription}";
    }

    /**
     * مزامنة الصلاحيات المكتشفة مع قاعدة البيانات
     */
    public function syncPermissions(): void
    {
        $discoveredPermissions = $this->discoverFromControllers();
        
        foreach ($discoveredPermissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description'] ?? null]
            );
        }
    }
}
