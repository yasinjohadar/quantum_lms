<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // يمكنه فقط رؤية قائمة الصلاحيات (index)
        $this->middleware(['permission:role-list'])->only(['index', 'grantedPermissions']);

        // يمكنه فقط إنشاء صلاحية جديدة (create + store)
        $this->middleware(['permission:role-create'])->only(['create', 'store']);

        // يمكنه فقط تعديل الصلاحية (edit + update)
        $this->middleware(['permission:role-edit'])->only(['edit', 'update']);

        // يمكنه فقط حذف الصلاحية (destroy)
        $this->middleware(['permission:role-delete'])->only('destroy');
    }

    public function index()
    {
        $permissions = Permission::all();
        $roles = Role::withCount('permissions')->orderBy('name')->get();

        return view('admin.pages.roles.index', compact('roles', 'permissions'));
    }

    /**
     * تصنيف الصلاحيات حسب الفئات (كل تصنيف منفصل)
     */
    private function categorizePermissions($permissions)
    {
        $categories = [
            'مراجعة المحتوى والنشر' => ['review-queue-', 'review-comment-'],
            'إدارة الصفوف' => ['class-'],
            'إدارة المراحل' => ['stage-'],
            'إدارة المواد الدراسية' => ['subject-'],
            'إدارة أقسام المواد' => ['subject-section-'],
            'إدارة الدروس' => ['lesson-'],
            'إدارة مرفقات الدروس' => ['lesson-attachment-'],
            'إدارة الوحدات' => ['unit-'],
            'إدارة الأسئلة' => ['question-'],
            'إدارة الاختبارات' => ['quiz-'],
            'إدارة محاولات الاختبارات' => ['quiz-attempt-'],
            'إدارة التسجيلات' => ['enrollment-'],
            'إدارة المدفوعات' => ['payment-'],
            'إدارة وسائل الدفع المخصصة' => ['custom-payment-method-'],
            'إدارة المستخدمين' => ['user-'],
            'إدارة المستخدمين المؤرشفين' => ['archived-user-'],
            'إدارة الأدوار' => ['role-'],
            'إدارة المكتبة' => ['library-item-', 'library-'],
            'إدارة فئات المكتبة' => ['library-category-'],
            'التقارير والإحصائيات' => ['report-'],
            'الإعدادات' => ['settings-'],
            'لوحة التحكم' => ['dashboard-', 'admin-dashboard-'],
            'إدارة العملات' => ['currency-'],
            'إدارة أسعار الصرف' => ['exchange-rate-'],
            'إدارة التقويم' => ['calendar-', 'calendar-event-'],
            'إدارة WhatsApp' => ['whats-app-', 'whats-app-settings-', 'whats-app-message-'],
            'إدارة البريد الإلكتروني' => ['email-', 'email-settings-', 'email-template-', 'email-log-'],
            'إدارة SMS' => ['sms-', 'sms-settings-', 'sms-template-', 'sms-log-'],
            'إدارة النسخ الاحتياطي' => ['backup-', 'backup-storage-', 'backup-schedule-', 'backup-storage-analytics-'],
            'إدارة التخزين' => ['storage-', 'app-storage-', 'app-storage-analytics-', 'storage-disk-mapping-'],
            'إدارة الذكاء الاصطناعي' => ['ai-', 'ai-question-generation-', 'ai-content-', 'ai-settings-', 'ai-grading-settings-', 'ai-student-feedback-', 'ai-model-', 'ai-question-solving-'],
            'إدارة التحفيز' => ['gamification-'],
            'إدارة الإشعارات' => ['notification-', 'notification-preference-'],
            'إدارة سجلات تسجيل الدخول' => ['login-log-'],
            'إدارة الجلسات' => ['user-session-'],
            'إدارة تقدم الطلاب' => ['student-progress-', 'admin-student-progress-'],
            'إدارة المهام الأسبوعية' => ['weekly-task-'],
            'إدارة المهام اليومية' => ['daily-task-'],
            'إدارة التذكيرات' => ['reminder-'],
            'إدارة المستويات' => ['level-'],
            'إدارة الإنجازات' => ['achievement-'],
            'إدارة الشارات' => ['badge-'],
            'إدارة التحديات' => ['challenge-'],
            'إدارة لوحة المتصدرين' => ['leaderboard-'],
            'إدارة المكافآت' => ['reward-'],
            'إدارة تخصيصات المعلمين' => ['teacher-assignment-'],
            'إدارة تخصيصات المشرفين' => ['supervisor-assignment-'],
            'إدارة تقدم المعلمين' => ['teacher-progress-'],
            'إدارة السنوات الدراسية' => ['academic-year-'],
            'إدارة الأسابيع الدراسية' => ['academic-week-'],
            'لوحة التحليلات' => ['analytics-dashboard-'],
            'لوحة المكتبة' => ['library-dashboard-'],
            'تقارير المكتبة' => ['library-report-'],
        ];

        $categorized = [];
        foreach ($categories as $categoryName => $prefixes) {
            $categorized[$categoryName] = $permissions->filter(function ($permission) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($permission->name, $prefix)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $reviewWorkflowNames = [
            'lesson-submit-for-review',
            'lesson-approve-review',
            'lesson-reject-review',
            'quiz-submit-for-review',
            'quiz-approve-review',
            'quiz-reject-review',
        ];

        $reviewWorkflowPermissions = $permissions->filter(
            fn ($permission) => in_array($permission->name, $reviewWorkflowNames, true)
        );

        if ($reviewWorkflowPermissions->isNotEmpty()) {
            $existingReview = $categorized['مراجعة المحتوى والنشر'] ?? collect();
            $categorized['مراجعة المحتوى والنشر'] = $existingReview
                ->merge($reviewWorkflowPermissions)
                ->unique('id')
                ->values();

            foreach ($categorized as $categoryName => $categoryPermissions) {
                if ($categoryName === 'مراجعة المحتوى والنشر') {
                    continue;
                }

                $categorized[$categoryName] = $categoryPermissions
                    ->reject(fn ($permission) => in_array($permission->name, $reviewWorkflowNames, true))
                    ->values();
            }
        }

        // إضافة فئة "أخرى" للصلاحيات غير المصنفة
        $allCategorized = collect($categorized)->flatten();
        $uncategorized = $permissions->diff($allCategorized);
        if ($uncategorized->isNotEmpty()) {
            $categorized['أخرى'] = $uncategorized;
        }

        return $categorized;
    }

    /**
     * عناوين التبويبات العليا لتجميع فئات الصلاحيات في الواجهة.
     *
     * @return array<string, string>
     */
    private function permissionTabLabels(): array
    {
        return [
            'academic' => 'المحتوى الأكاديمي والتسجيل',
            'library_reports' => 'المكتبة والتقارير والتحليلات',
            'users_security' => 'المستخدمون والأدوار والأمان',
            'communication' => 'التواصل والتقويم',
            'system' => 'الإعدادات والبنية التحتية',
            'gamification_ai' => 'التحفيز والذكاء الاصطناعي',
            'dashboard_notifications' => 'لوحة التحكم والإشعارات',
            'other' => 'أخرى',
        ];
    }

    /**
     * ربط اسم فئة الصلاحية (كما في categorizePermissions) بمفتاح التبويب.
     *
     * @return array<string, string>
     */
    private function permissionCategoryTabMap(): array
    {
        return [
            'إدارة الصفوف' => 'academic',
            'مراجعة المحتوى والنشر' => 'academic',
            'إدارة المراحل' => 'academic',
            'إدارة المواد الدراسية' => 'academic',
            'إدارة أقسام المواد' => 'academic',
            'إدارة الدروس' => 'academic',
            'إدارة مرفقات الدروس' => 'academic',
            'إدارة الوحدات' => 'academic',
            'إدارة الأسئلة' => 'academic',
            'إدارة الاختبارات' => 'academic',
            'إدارة محاولات الاختبارات' => 'academic',
            'إدارة التسجيلات' => 'academic',
            'إدارة المدفوعات' => 'academic',
            'إدارة وسائل الدفع المخصصة' => 'academic',
            'إدارة تقدم الطلاب' => 'academic',
            'إدارة المهام الأسبوعية' => 'academic',
            'إدارة المهام اليومية' => 'academic',
            'إدارة التذكيرات' => 'academic',
            'إدارة تخصيصات المعلمين' => 'academic',
            'إدارة تخصيصات المشرفين' => 'academic',
            'إدارة تقدم المعلمين' => 'academic',
            'إدارة السنوات الدراسية' => 'academic',
            'إدارة الأسابيع الدراسية' => 'academic',
            'إدارة المكتبة' => 'library_reports',
            'إدارة فئات المكتبة' => 'library_reports',
            'التقارير والإحصائيات' => 'library_reports',
            'لوحة التحليلات' => 'library_reports',
            'لوحة المكتبة' => 'library_reports',
            'تقارير المكتبة' => 'library_reports',
            'إدارة المستخدمين' => 'users_security',
            'إدارة المستخدمين المؤرشفين' => 'users_security',
            'إدارة الأدوار' => 'users_security',
            'إدارة سجلات تسجيل الدخول' => 'users_security',
            'إدارة الجلسات' => 'users_security',
            'إدارة التقويم' => 'communication',
            'إدارة WhatsApp' => 'communication',
            'إدارة البريد الإلكتروني' => 'communication',
            'إدارة SMS' => 'communication',
            'الإعدادات' => 'system',
            'إدارة العملات' => 'system',
            'إدارة أسعار الصرف' => 'system',
            'إدارة النسخ الاحتياطي' => 'system',
            'إدارة التخزين' => 'system',
            'إدارة التحفيز' => 'gamification_ai',
            'إدارة الذكاء الاصطناعي' => 'gamification_ai',
            'إدارة المستويات' => 'gamification_ai',
            'إدارة الإنجازات' => 'gamification_ai',
            'إدارة الشارات' => 'gamification_ai',
            'إدارة التحديات' => 'gamification_ai',
            'إدارة لوحة المتصدرين' => 'gamification_ai',
            'إدارة المكافآت' => 'gamification_ai',
            'لوحة التحكم' => 'dashboard_notifications',
            'إدارة الإشعارات' => 'dashboard_notifications',
            'أخرى' => 'other',
        ];
    }

    /**
     * @param  array<string, \Illuminate\Support\Collection>  $categorized
     * @return list<array{key: string, pane_id: string, label: string, categories: array<string, \Illuminate\Support\Collection>}>
     */
    private function tabbedCategorizedPermissions(array $categorized): array
    {
        $labels = $this->permissionTabLabels();
        $map = $this->permissionCategoryTabMap();
        $bucket = [];
        foreach (array_keys($labels) as $key) {
            $bucket[$key] = ['categories' => []];
        }

        foreach ($categorized as $categoryName => $permissions) {
            if ($permissions->isEmpty()) {
                continue;
            }
            $tabKey = $map[$categoryName] ?? 'other';
            if (! isset($bucket[$tabKey])) {
                $tabKey = 'other';
            }
            $bucket[$tabKey]['categories'][$categoryName] = $permissions;
        }

        $tabs = [];
        foreach ($labels as $key => $label) {
            if (empty($bucket[$key]['categories'])) {
                continue;
            }
            $tabs[] = [
                'key' => $key,
                'pane_id' => 'role-perm-tab-'.$key,
                'label' => $label,
                'categories' => $bucket[$key]['categories'],
            ];
        }

        return $tabs;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $categorizedPermissions = $this->categorizePermissions($permissions);
        $permissionTabs = $this->tabbedCategorizedPermissions($categorizedPermissions);
        $roles = Role::all();

        return view('admin.pages.roles.create', compact('roles', 'permissions', 'permissionTabs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $guard = config('auth.defaults.guard', 'web');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $hasStaffProfile = Schema::hasColumn((new Role)->getTable(), 'staff_profile');

        $validated = $request->validate(
            $this->roleFormRules(null, $rolesTable, $permissionsTable, $guard, $hasStaffProfile),
            $this->roleValidationMessages(),
            $this->roleValidationAttributes()
        );

        $attributes = [
            'name' => $validated['name'],
            'dashboard_type' => $validated['dashboard_type'],
        ];
        if ($hasStaffProfile) {
            $attributes['staff_profile'] = $validated['staff_profile'];
        }

        $role = Role::create($attributes);

        $perms = $validated['permissions'] ?? null;
        $role->syncPermissions(is_array($perms) ? array_values($perms) : []);

        return redirect()->route('roles.index')->with('success', 'تم إضافة الدور بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * عرض الصلاحيات الممنوحة للدور فقط (قراءة).
     */
    public function grantedPermissions(string $role)
    {
        $role = Role::with('permissions')->findOrFail($role);
        $grantedPermissions = $role->permissions->sortBy('name')->values();
        $categorizedPermissions = $this->categorizePermissions($grantedPermissions);
        $permissionTabs = $this->tabbedCategorizedPermissions($categorizedPermissions);

        return view('admin.pages.roles.granted-permissions', compact(
            'role',
            'permissionTabs',
            'grantedPermissions'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $categorizedPermissions = $this->categorizePermissions($permissions);
        $permissionTabs = $this->tabbedCategorizedPermissions($categorizedPermissions);

        return view('admin.pages.roles.edit', compact('role', 'permissions', 'permissionTabs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($request->id);

        $guard = config('auth.defaults.guard', 'web');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $hasStaffProfile = Schema::hasColumn($role->getTable(), 'staff_profile');

        $validated = $request->validate(
            $this->roleFormRules($role, $rolesTable, $permissionsTable, $guard, $hasStaffProfile),
            $this->roleValidationMessages(),
            $this->roleValidationAttributes()
        );

        $attributes = [
            'name' => $validated['name'],
            'dashboard_type' => $validated['dashboard_type'],
        ];
        if ($hasStaffProfile) {
            $attributes['staff_profile'] = $validated['staff_profile'];
        }

        $role->update($attributes);

        $perms = $validated['permissions'] ?? null;
        $role->syncPermissions(is_array($perms) ? array_values($perms) : []);

        return redirect()->route('roles.index')->with('success', 'تم تعديل الدور بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $role = Role::findOrFail($request->id);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'تم حذف الدور بنجاح');
    }

    /**
     * البحث في الصلاحيات (AJAX)
     */
    public function searchPermissions(Request $request)
    {
        $search = $request->input('search');
        $permissions = Permission::query();

        if ($search) {
            $permissions->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return response()->json($permissions->get());
    }

    /**
     * قواعد التحقق المشتركة لنماذج إنشاء/تعديل الدور.
     */
    private function roleFormRules(?Role $role, string $rolesTable, string $permissionsTable, string $guard, bool $hasStaffProfile): array
    {
        $nameUnique = Rule::unique($rolesTable, 'name')->where('guard_name', $guard);
        if ($role !== null) {
            $nameUnique->ignore($role->getKey());
        }

        $rules = [
            'name' => ['required', 'string', 'max:255', $nameUnique],
            'dashboard_type' => ['required', Rule::in(['admin', 'student'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists($permissionsTable, 'name')],
        ];

        if ($hasStaffProfile) {
            $rules['staff_profile'] = ['required', Rule::in(['none', 'supervisor', 'teacher'])];
        }

        return $rules;
    }

    /**
     * رسائل التحقق بالعربية.
     */
    private function roleValidationMessages(): array
    {
        return [
            'name.required' => 'يرجى إدخال اسم الدور.',
            'name.string' => 'اسم الدور يجب أن يكون نصاً.',
            'name.max' => 'اسم الدور لا يجوز أن يتجاوز 255 حرفاً.',
            'name.unique' => 'هذا الاسم مستخدم مسبقاً لدور آخر بنفس نوع الحماية.',
            'dashboard_type.required' => 'يرجى اختيار نوع الواجهة.',
            'dashboard_type.in' => 'نوع الواجهة المختار غير صالح.',
            'staff_profile.required' => 'يرجى اختيار تصنيف المشرف / المعلم.',
            'staff_profile.in' => 'تصنيف المشرف / المعلم غير صالح.',
            'permissions.array' => 'تنسيق الصلاحيات المرسل غير صالح.',
            'permissions.*.exists' => 'إحدى الصلاحيات المحددة غير موجودة في النظام.',
        ];
    }

    /**
     * أسماء الحقول في رسائل التحقق.
     */
    private function roleValidationAttributes(): array
    {
        return [
            'name' => 'اسم الدور',
            'dashboard_type' => 'نوع الواجهة',
            'staff_profile' => 'تصنيف المشرف / المعلم',
            'permissions' => 'الصلاحيات',
        ];
    }
}
