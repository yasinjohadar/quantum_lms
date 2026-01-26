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
     * تصنيف الصلاحيات حسب الفئات
     */
    private function categorizePermissions($permissions)
    {
        $categories = [
            'إدارة الصفوف والمراحل' => ['class-', 'stage-'],
            'إدارة المواد الدراسية' => ['subject-'],
            'إدارة الدروس' => ['lesson-'],
            'إدارة الوحدات' => ['unit-'],
            'إدارة الأسئلة' => ['question-'],
            'إدارة الاختبارات' => ['quiz-'],
            'إدارة محاولات الاختبارات' => ['quiz-attempt-'],
            'إدارة التسجيلات' => ['enrollment-'],
            'إدارة المدفوعات' => ['payment-'],
            'إدارة المستخدمين' => ['user-'],
            'إدارة الأدوار' => ['role-'],
            'إدارة المكتبة' => ['library-'],
            'التقارير والإحصائيات' => ['report-'],
            'الإعدادات' => ['settings-'],
            'لوحة التحكم' => ['dashboard-'],
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
            "name" => $request->name
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

            "name" => $request->name
            ]
        );
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
}
