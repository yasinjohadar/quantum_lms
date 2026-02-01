<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{

public function __construct()
{
    // يمكنه فقط رؤية قائمة الصلاحيات (index)
    $this->middleware(['permission:role-list'])->only('index');

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
            $roles = Role::all();
            return view("admin.pages.roles.index" , compact("roles" , "permissions"));
        }

    /**
     * تصنيف الصلاحيات حسب الفئات (كل تصنيف منفصل)
     */
    private function categorizePermissions($permissions)
    {
        $categories = [
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
            'لوحة التحليلات' => ['analytics-dashboard-'],
            'لوحة المكتبة' => ['library-dashboard-'],
            'تقارير المكتبة' => ['library-report-'],
        ];
        
        $categorized = [];
        foreach ($categories as $categoryName => $prefixes) {
            $categorized[$categoryName] = $permissions->filter(function($permission) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($permission->name, $prefix)) {
                        return true;
                    }
                }
                return false;
            });
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $categorizedPermissions = $this->categorizePermissions($permissions);
        $roles = Role::all();

       return view("admin.pages.roles.create" , compact("roles" , "permissions", "categorizedPermissions"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role = Role::create([
            "name" => $request->name,
            "dashboard_type" => $request->dashboard_type ?? 'student'
        ]);

        $role->syncPermissions($request->permissions);

        return back()->with("success" , "تم اضافة الروول بنجاح");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $categorizedPermissions = $this->categorizePermissions($permissions);

       return view("admin.pages.roles.edit" , compact("role" , "permissions", "categorizedPermissions"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($request->id);

        $role->update([
            "name" => $request->name,
            "dashboard_type" => $request->dashboard_type ?? 'student'
        ]);
        $role->syncPermissions($request->permissions);
        return redirect()->route("roles.index")->with("success" , "تم تعديل الروول بنجاح");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request )
    {
        $role = Role::findOrFail($request->id);
        $role->delete();
        return redirect()->route("roles.index")->with("success" , "تم حذف الدور بنجاح");
    }

    /**
     * البحث في الصلاحيات (AJAX)
     */
    public function searchPermissions(Request $request)
    {
        $search = $request->input('search');
        $permissions = Permission::query();
        
        if ($search) {
            $permissions->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        return response()->json($permissions->get());
    }
}
