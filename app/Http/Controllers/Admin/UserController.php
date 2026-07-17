<?php

namespace App\Http\Controllers\Admin;

use HashContext;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\SystemSetting;
use App\Models\SchoolClass;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Purchase;
use App\Services\SMS\OTPService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PhoneHelper;
use App\Helpers\StorageHelper;
use App\Services\Storage\MediaStorageService;
use App\Services\AdminStudentEnrollmentService;
use App\Services\PurchaseService;
use App\Support\StudentSubscriptionExpiryResolver;
use App\Http\Requests\Admin\BulkUpdateRolesRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     // يمكنه فقط رؤية قائمة المستخدمين (index)
    //     $this->middleware(['permission:user-list'])->only('index');

    //     // يمكنه فقط إنشاء مستخدم جديد (create + store)
    //     $this->middleware(['permission:user-create'])->only(['create', 'store']);

    //     // يمكنه فقط تعديل المستخدم (edit + update)
    //     $this->middleware(['permission:user-edit'])->only(['edit', 'update']);

    //     // يمكنه فقط حذف المستخدم (destroy)
    //     $this->middleware(['permission:user-delete'])->only('destroy');

    //     // يمكنه فقط رؤية ملف المستخدم (show)
    //     $this->middleware(['permission:user-show'])->only('show');
    // }

    public function __construct(
        private OTPService $otpService,
        private AdminStudentEnrollmentService $adminStudentEnrollmentService
    ) {
        // تأكد أن المستخدم مصادق أولًا ثم تحقق من الصلاحيات
        // استثناء impersonate من auth middleware للسماح بـ signed URL (GET requests)
        // الـ POST requests محمية بـ auth middleware في route definition
        $this->middleware('auth')->except(['impersonate']);

        $this->middleware('permission:user-list')->only(['index', 'trashedIndex']);
        $this->middleware('permission:user-create')->only(['create', 'store', 'storeQuickStudent']);
        $this->middleware('permission:user-edit')->only([
            'edit',
            'update',
            'bulkUpdateRoles',
            'updateSubscriptionExpires',
        ]);
        $this->middleware('permission:user-edit')->only([
            'detachFromClass',
            'detachMultipleFromClass',
            'detachAllFromClass',
            'detachAllFromSubject',
        ]);
        $this->middleware('permission:user-delete')->only(['destroy', 'forceDestroy', 'forceDestroyDirect']);
        $this->middleware('permission:user-show')->only('show');
        $this->middleware('permission:user-update-password')->only('updatePassword');
        $this->middleware('permission:user-toggle-status')->only('toggleStatus');
        $this->middleware('permission:user-login-logs')->only('loginLogs');
        $this->middleware('permission:user-send-verification-otp')->only('sendVerificationOTP');
        // permission middleware للـ impersonate سيتم التحقق منه داخل الـ method نفسه
        // للـ POST requests: يتم التحقق في الـ method
        // للـ GET requests: الـ signed URL يوفر الأمان
        $this->middleware('permission:user-impersonate')->only(['stopImpersonate']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = Role::all();

        $classes = SchoolClass::active()->ordered()->get(['id', 'name']);
        $classesForAssign = SchoolClass::with('stage')->active()->ordered()->get();

        // بدء استعلام المستخدمين — الطلاب فقط (غير المؤرشفين)
        $usersQuery = User::query()->students();
        $usersQuery->with(['classEnrollments' => function ($q) {
            $q->whereIn('status', ['pending', 'approved'])->with('schoolClass');
        }, 'purchases' => function ($q) {
            $q->where('purchasable_type', SchoolClass::class)
                ->completed()
                ->whereNull('access_revoked_at')
                ->with('purchasable');
        }]);

        $selectedClassId = $request->filled('class_id') ? (int) $request->input('class_id') : null;

        // فلترة حسب البحث (name, email, phone)
        if ($request->filled('query')) {
            $search = $request->input('query');
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // افتراضياً: عرض الحسابات المفعلة فقط
        $isActiveFilter = $request->has('is_active') ? $request->input('is_active') : '1';
        if ($isActiveFilter !== '' && $isActiveFilter !== null) {
            $usersQuery->where('is_active', $isActiveFilter);
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $classId = (int) $request->input('class_id');
            $usersQuery->whereHas('classEnrollments', function ($q) use ($classId) {
                $q->whereIn('status', ['pending', 'approved'])->where('class_id', $classId);
            });
        }

        // افتراضياً: الأحدث تسجيلاً أولاً (يمكن عكسه عبر sort=oldest)
        $sort = $request->input('sort') === 'oldest' ? 'oldest' : 'newest';
        $usersQuery->orderBy('created_at', $sort === 'newest' ? 'desc' : 'asc')
            ->orderBy('id', $sort === 'newest' ? 'desc' : 'asc');

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $users = $usersQuery->paginate($perPage)->withQueryString();

        // AJAX response for class filter + pagination
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.users.partials.users-tbody', compact('users', 'classesForAssign', 'selectedClassId'))->render(),
                'pagination' => view('admin.pages.users.partials.pagination-links', compact('users'))->render(),
                'impersonate_modals' => view('admin.pages.users.partials.impersonate-modals', compact('users'))->render(),
                'sort' => $sort,
            ]);
        }

        return view('admin.pages.users.index', compact('users', 'roles', 'classes', 'classesForAssign', 'selectedClassId', 'sort'));
    }

    public function updateSubscriptionExpires(Request $request, User $user, PurchaseService $purchaseService): JsonResponse
    {
        $validated = $request->validate([
            'purchase_id' => ['nullable', 'integer', 'exists:purchases,id', 'required_without:class_id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id', 'required_without:purchase_id'],
            'expires_at' => ['required', 'date'],
        ], [
            'purchase_id.exists' => 'سجل الشراء غير موجود',
            'class_id.exists' => 'الصف غير موجود',
            'expires_at.required' => 'يجب تحديد تاريخ نهاية الاشتراك',
            'expires_at.date' => 'تاريخ نهاية الاشتراك غير صالح',
        ]);

        $newExpiresAt = \Carbon\Carbon::parse($validated['expires_at'])->endOfDay();

        try {
            if (! empty($validated['class_id'])) {
                $class = SchoolClass::query()->findOrFail($validated['class_id']);
                $purchase = $purchaseService->assignClassSubscriptionExpiry($user, $class, $newExpiresAt);
            } else {
                $purchase = Purchase::query()
                    ->where('user_id', $user->id)
                    ->where('id', $validated['purchase_id'])
                    ->where('purchasable_type', SchoolClass::class)
                    ->completed()
                    ->whereNull('access_revoked_at')
                    ->firstOrFail();

                $purchase->loadMissing('purchasable');

                if ($purchase->purchasable instanceof SchoolClass && $purchase->purchasable->subscription_ends_at) {
                    if ($newExpiresAt->gt($purchase->purchasable->subscription_ends_at)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'لا يمكن أن يتجاوز تاريخ الطالب نهاية اشتراك الصف ('.$purchase->purchasable->subscription_ends_at->format('Y-m-d').')',
                        ], 422);
                    }
                }

                $purchase->update([
                    'expires_at' => $newExpiresAt,
                    'access_revoked_at' => $newExpiresAt->isFuture() ? null : $purchase->access_revoked_at,
                ]);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث تاريخ نهاية الاشتراك',
            'purchase_id' => $purchase->id,
            'expires_at' => $purchase->expires_at->format('Y-m-d'),
            'is_expired' => $purchase->expires_at->isPast(),
        ]);
    }

    /**
     * Soft Deleted Users listing (deleted_at is not null)
     */
    public function trashedIndex(Request $request)
    {
        $roles = Role::all();

        $usersQuery = User::onlyTrashed()->with(['roles']);

        // بحث عام بالاسم / البريد / الهاتف
        if ($request->filled('query')) {
            $search = $request->input('query');
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        // فلتر نوع المستخدم (student/teacher/supervisor/admin/other)
        $userType = $request->input('user_type');
        if ($userType) {
            if (in_array($userType, ['student', 'teacher', 'supervisor', 'admin'], true)) {
                $rolesTable = config('permission.table_names.roles', 'roles');
                $usersQuery->whereHas('roles', function ($q) use ($userType, $rolesTable) {
                    // البعض يعتمد على staff_profile والبعض يعتمد على name
                    if (Schema::hasColumn($rolesTable, 'staff_profile')) {
                        $q->where('staff_profile', $userType);
                    } else {
                        $q->where('name', $userType);
                    }
                });
            } elseif ($userType === 'other') {
                $usersQuery->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['student', 'teacher', 'supervisor', 'admin']);
                });
            }
        }

        // فلتر حسب الدور (role dropdown)
        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $usersQuery->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // فلتر حالة الحساب (اختياري)
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $usersQuery->where('is_active', $request->input('is_active'));
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $users = $usersQuery->orderBy('name')->paginate($perPage);

        return view('admin.pages.users.trashed.index', compact('users', 'roles'));
    }

    /**
     * Force delete a soft deleted user.
     */
    public function forceDestroy(Request $request, $user)
    {
        try {
            $userModel = User::withTrashed()->findOrFail($user);

            if (! $userModel->trashed()) {
                return redirect()->route('admin.users.trashed.index')
                    ->with('error', "❌ هذا المستخدم ليس محذوفاً سوفت.");
            }

            // حذف الصورة إذا كانت موجودة
            if ($userModel->photo) {
                try {
                    StorageHelper::delete('avatars', $userModel->photo);
                } catch (\Exception $e) {
                    // لا نوقف العملية إذا فشل حذف الصورة
                }
            }

            $userName = $userModel->name;

            $userModel->forceDelete();

            return redirect()->route('admin.users.trashed.index')
                ->with('success', "✅ تم حذف المستخدم نهائياً ({$userName})");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.users.trashed.index')
                ->with('error', '❌ المستخدم المطلوب غير موجود');
        } catch (QueryException $e) {
            return redirect()->route('admin.users.trashed.index')
                ->with('error', '❌ فشل الحذف النهائي بسبب قيود قاعدة البيانات.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.trashed.index')
                ->with('error', '❌ حدث خطأ أثناء الحذف النهائي: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a user مباشرة من القائمة (حتى لو لم يكن soft deleted).
     */
    public function forceDestroyDirect(Request $request, $user)
    {
        try {
            $userModel = User::withTrashed()->findOrFail($user);

            // حذف الصورة إذا كانت موجودة
            if ($userModel->photo) {
                try {
                    StorageHelper::delete('avatars', $userModel->photo);
                } catch (\Exception $e) {
                    // لا نوقف العملية إذا فشل حذف الصورة
                }
            }

            $userName = $userModel->name;

            $userModel->forceDelete();

            return redirect()->back()
                ->with('success', "✅ تم حذف المستخدم نهائياً ({$userName})");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', '❌ المستخدم المطلوب غير موجود');
        } catch (QueryException $e) {
            return redirect()->back()
                ->with('error', '❌ فشل الحذف النهائي بسبب قيود قاعدة البيانات.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '❌ حدث خطأ أثناء الحذف النهائي: ' . $e->getMessage());
        }
    }

    /**
     * قائمة المدراء (users with role=admin فقط)
     */
    public function adminsIndex(Request $request)
    {
        // المدراء: كل مستخدم لديه role=admin
        $adminsQuery = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            });

        // بحث بالاسم / الإيميل / الهاتف
        if ($request->filled('query')) {
            $search = $request->input('query');
            $adminsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // افتراضياً: عرض الحسابات المفعلة فقط
        $isActiveFilter = $request->has('is_active') ? $request->input('is_active') : '1';
        if ($isActiveFilter !== '' && $isActiveFilter !== null) {
            $adminsQuery->where('is_active', $isActiveFilter);
        }

        $admins = $adminsQuery->orderBy('name')->paginate(10);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.admins.partials.table-rows', ['admins' => $admins])->render(),
                'pagination' => view('admin.pages.admins.partials.pagination', ['admins' => $admins])->render(),
                'impersonate_modals' => view('admin.pages.users.partials.impersonate-modals', ['users' => $admins])->render(),
            ]);
        }

        return view('admin.pages.admins.index', compact('admins'));
    }

    /**
     * صفحة إدارة جميع المستخدمين (غير المؤرشفين)
     */
    public function manageIndex(Request $request)
    {
        $roles = Role::all();

        $usersQuery = User::query()
            ->with('roles');

        // استبعاد المؤرشفين دائماً
        $usersQuery->notArchived();

        // بحث عام بالاسم / البريد / الهاتف
        if ($request->filled('query')) {
            $search = $request->input('query');
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // فلتر نوع المستخدم
        $userType = $request->input('user_type');
        if ($userType === 'student') {
            $usersQuery->students();
        } elseif ($userType === 'teacher') {
            $usersQuery->teachers();
        } elseif ($userType === 'supervisor') {
            $usersQuery->supervisors();
        } elseif ($userType === 'admin') {
            $usersQuery->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            });
        } elseif ($userType === 'other') {
            // مستخدمون لا يملكون أي من الأدوار الأساسية المعروفة
            $usersQuery->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['student', 'teacher', 'supervisor', 'admin']);
            });
        }

        // فلتر حسب الدور
        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $usersQuery->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // فلتر حالة الحساب (افتراضياً مفعل فقط)
        $isActiveFilter = $request->has('is_active') ? $request->input('is_active') : '1';
        if ($isActiveFilter !== '' && $isActiveFilter !== null) {
            $usersQuery->where('is_active', $isActiveFilter);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $users = $usersQuery->orderBy('name')->paginate($perPage);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.users.partials.manage-tbody', compact('users'))->render(),
                'pagination' => view('admin.pages.users.partials.manage-pagination', compact('users'))->render(),
                'impersonate_modals' => view('admin.pages.users.partials.impersonate-modals', ['users' => $users])->render(),
            ]);
        }

        return view('admin.pages.users.manage', compact('users', 'roles'));
    }

    /**
     * تحديث أدوار مجموعة من المستخدمين دفعة واحدة.
     */
    public function bulkUpdateRoles(BulkUpdateRolesRequest $request): RedirectResponse
    {
        $userIds = $request->input('user_ids', []);
        $roles = array_values(array_unique($request->input('roles', [])));

        if (! in_array('admin', $roles, true)) {
            $adminsOutsideSelection = User::whereNotIn('id', $userIds)
                ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
                ->count();

            $adminsInSelection = User::whereIn('id', $userIds)
                ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
                ->count();

            if ($adminsOutsideSelection === 0 && $adminsInSelection > 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '❌ لا يمكن إزالة دور admin من جميع المدراء في النظام.');
            }
        }

        $updated = 0;

        DB::transaction(function () use ($userIds, $roles, &$updated) {
            User::query()
                ->notArchived()
                ->whereIn('id', $userIds)
                ->each(function (User $user) use ($roles, &$updated) {
                    $user->syncRoles($roles);
                    $updated++;
                });
        });

        return redirect()->back()
            ->with('success', "✅ تم تحديث أدوار {$updated} مستخدم بنجاح.");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $roles = Role::all();
        $defaultRole = $request->input('role'); // للحصول على role من query parameter
        
        return view("admin.pages.users.create", compact("roles", "defaultRole"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // تطبيع رقم الهاتف تلقائياً إن وُجد
            if ($request->filled('phone')) {
                $normalized = PhoneHelper::normalize($request->phone, config('app.phone_default_country_code', '966'));
                if ($normalized !== null) {
                    $request->merge(['phone' => $normalized]);
                }
            }
            // التحقق من صحة البيانات
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone' => 'nullable|string|max:20|regex:/^\+[1-9]\d{1,14}$/|unique:users,phone',
                'password' => 'required|string|min:8|confirmed',
                'is_active' => 'boolean',
                'roles' => 'array',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'الاسم مطلوب',
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'البريد الإلكتروني غير صحيح',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
                'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
                'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
                'password.required' => 'كلمة المرور مطلوبة',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
                'photo.image' => 'يجب أن يكون الملف صورة',
                'photo.mimes' => 'نوع الصورة غير مدعوم',
                'photo.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
            ]);

            // معالجة الصورة
            $photoPath = null;
            if ($request->hasFile('photo')) {
                try {
                    $photo = $request->file('photo');
                    $photoName = time() . '_' . $photo->getClientOriginalName();
                    $uploadResult = MediaStorageService::uploadImage($photo, 'users/photos', $photoName);
                    $photoPath = $uploadResult['path'];
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'فشل رفع الصورة: ' . $e->getMessage());
                }
            }

            // إنشاء المستخدم
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active'),
                'photo' => $photoPath,
                'created_by' => auth()->id(), // المستخدم الذي أنشأ هذا الحساب
            ]);

            // تعيين الأدوار
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            if ($request->input('return_context') === 'manage') {
                return redirect()->route('admin.users.manage')
                    ->with("success", "✅ تم إضافة المستخدم ({$user->name}) بنجاح");
            }

            if ($request->input('return_context') === 'admin' || $user->hasRole('admin')) {
                return redirect()->route('admin.admins.index')
                    ->with("success", "✅ تم إضافة المستخدم ({$user->name}) كمدير بنجاح");
            }

            if ($request->input('return_context') === 'supervisor' || $user->hasSupervisorStaffIdentity()) {
                return redirect()->route('admin.supervisors.assignments.index')
                    ->with("success", "✅ تم إضافة المستخدم ({$user->name}) بنجاح");
            }
            if ($request->input('return_context') === 'teacher' || $user->hasTeacherStaffIdentity()) {
                return redirect()->route('admin.teachers.assignments.index')
                    ->with("success", "✅ تم إضافة المستخدم ({$user->name}) بنجاح");
            }

            return redirect()->route("users.index")
                ->with("success", "✅ تم إضافة المستخدم ({$user->name}) بنجاح");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', '❌ فشل إنشاء المستخدم. يرجى التحقق من البيانات المدخلة.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ حدث خطأ أثناء إنشاء المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء طالب بدور student مع اختيار ربط صف أو مواد في نفس الطلب.
     */
    public function storeQuickStudent(Request $request)
    {
        if ($request->filled('phone')) {
            $normalized = PhoneHelper::normalize($request->phone, config('app.phone_default_country_code', '966'));
            if ($normalized !== null) {
                $request->merge(['phone' => $normalized]);
            }
        }

        $attachMode = $request->input('attach_mode', 'none');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|regex:/^\+[1-9]\d{1,14}$/|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|in:0,1',
            'attach_mode' => 'required|in:none,class,subjects',
            'assign_class_id' => 'exclude_unless:attach_mode,class|required|integer|exists:classes,id',
            'subject_ids' => 'exclude_unless:attach_mode,subjects|required|array|min:1',
            'subject_ids.*' => 'integer|exists:subjects,id',
            'notes' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'attach_mode.required' => 'نوع الربط مطلوب',
            'assign_class_id.required' => 'يجب اختيار صف دراسي.',
            'subject_ids.required' => 'يجب اختيار مادة واحدة على الأقل.',
            'subject_ids.min' => 'يجب اختيار مادة واحدة على الأقل.',
        ]);

        if ($attachMode !== 'none' && ! auth()->user()->can('enrollment-create')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'ليس لديك صلاحية ربط الطلاب بالصفوف أو المواد. اختر «بدون ربط» أو اطلب صلاحية إنشاء الانضمامات.');
        }

        $indexParams = [];
        if ($request->filled('list_query')) {
            $indexParams['query'] = $request->input('list_query');
        }
        if ($request->filled('list_is_active')) {
            $indexParams['is_active'] = $request->input('list_is_active');
        }
        if ($request->filled('list_class_id')) {
            $indexParams['class_id'] = $request->input('list_class_id');
        }
        if ($request->filled('list_per_page')) {
            $indexParams['per_page'] = $request->input('list_per_page');
        }
        if ($request->filled('list_page')) {
            $indexParams['page'] = $request->input('list_page');
        }

        try {
            return DB::transaction(function () use ($request, $attachMode, $indexParams) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'is_active' => (bool) $request->input('is_active'),
                    'photo' => null,
                    'created_by' => auth()->id(),
                ]);

                $user->syncRoles(['student']);

                $linkMessage = '';
                if ($attachMode === 'class') {
                    $result = $this->adminStudentEnrollmentService->assignApprovedClassWithProvisioning(
                        $user->id,
                        (int) $request->input('assign_class_id'),
                        $request->input('notes'),
                        auth()->id()
                    );
                    $linkMessage = " تم ربطه بالصف ومزامنة {$result['created']} مادة.";
                    if ($result['skipped'] > 0) {
                        $linkMessage .= " (تم تخطي {$result['skipped']} مادة مسجل فيها مسبقاً)";
                    }
                } elseif ($attachMode === 'subjects') {
                    $counts = $this->adminStudentEnrollmentService->bulkAttachSubjects(
                        [$user->id],
                        array_values(array_unique($request->input('subject_ids', []))),
                        'active',
                        $request->input('notes'),
                        auth()->id()
                    );
                    if ($counts['insert_count'] === 0 && $counts['reactivated'] === 0) {
                        throw new \RuntimeException('لم يُضف أي انضمام للمواد.');
                    }
                    $parts = [];
                    if ($counts['insert_count'] > 0) {
                        $parts[] = "تمت إضافة {$counts['insert_count']} انضماماً للمواد";
                    }
                    if ($counts['reactivated'] > 0) {
                        $parts[] = "تمت إعادة تفعيل {$counts['reactivated']} انضماماً";
                    }
                    if ($counts['skipped'] > 0) {
                        $parts[] = "تم تخطي {$counts['skipped']} مكرراً";
                    }
                    $linkMessage = ' '.implode('، ', $parts).'.';
                }

                return redirect()->route('users.index', $indexParams)
                    ->with('success', 'تم إنشاء الطالب «'.$user->name.'» بنجاح.'.$linkMessage);
            });
        } catch (QueryException $e) {
            Log::error('storeQuickStudent database error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', $this->friendlyQuickStudentDbError($e));
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('storeQuickStudent: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'تعذر إتمام العملية: '.$e->getMessage());
        }
    }

    private function friendlyQuickStudentDbError(QueryException $e): string
    {
        $msg = $e->getMessage();
        if (str_contains($msg, '1062') && str_contains($msg, 'class_enrollments')) {
            return 'تعذر حفظ ربط الصف: يوجد تعارض مع سجل سابق. أعد تحميل الصفحة وحاول مرة أخرى.';
        }
        if (str_contains($msg, '1062') && str_contains($msg, 'enrollments')) {
            return 'تعذر حفظ انضمام المواد: تعارض مع سجل موجود مسبقاً.';
        }
        if (str_contains($msg, 'Integrity constraint violation')) {
            return 'تعذر إتمام العملية بسبب قيد في قاعدة البيانات.';
        }

        return 'تعذر إتمام إنشاء الطالب أو الربط. حاول لاحقاً أو راجع سجلات النظام.';
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with([
            'classEnrollments.schoolClass.stage',
            'enrollments.subject.schoolClass.stage',
            'purchases' => function ($q) {
                $q->where('purchasable_type', SchoolClass::class)
                    ->completed()
                    ->whereNull('access_revoked_at')
                    ->with('purchasable');
            },
        ])->findOrFail($id);

        $classesForAssign = collect();
        $classSubscriptionMap = [];

        if ($user->hasRole('student')) {
            $classesForAssign = SchoolClass::with('stage')->active()->ordered()->get();

            foreach (StudentSubscriptionExpiryResolver::resolveAllForUser($user) as $subscription) {
                if (! empty($subscription['class_id'])) {
                    $classSubscriptionMap[$subscription['class_id']] = $subscription;
                }
            }
        }

        return view('admin.pages.users.profile', compact('user', 'classesForAssign', 'classSubscriptionMap'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::query()
            ->with([
                'classEnrollments' => function ($q) {
                    $q->where('status', 'approved')->with('schoolClass');
                },
                'purchases' => function ($q) {
                    $q->where('purchasable_type', SchoolClass::class)
                        ->completed()
                        ->whereNull('access_revoked_at')
                        ->with('purchasable');
                },
            ])
            ->findOrFail($id);

        $roles = Role::all();
        $classSubscriptions = $user->hasRole('student')
            ? StudentSubscriptionExpiryResolver::resolveAllForUser($user)
            : [];

        return view('admin.pages.users.edit', compact('roles', 'user', 'classSubscriptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($request->filled('phone')) {
                $normalized = PhoneHelper::normalize($request->phone, config('app.phone_default_country_code', '966'));
                if ($normalized !== null) {
                    $request->merge(['phone' => $normalized]);
                }
            }

            if ($user->hasRole('student')) {
                $request->validate([
                    'name' => 'required|string|max:255',
                    'phone' => 'nullable|string|max:20|regex:/^\+[1-9]\d{1,14}$/|unique:users,phone,'.$id,
                ], [
                    'name.required' => 'الاسم مطلوب',
                    'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
                ]);

                $user->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                ]);

                return redirect()->route('users.index')
                    ->with('success', "✅ تم تحديث بيانات الطالب ({$user->name}) بنجاح");
            }

            $roles = $request->input('roles', $user->roles->pluck('name')->toArray());
            if (! is_array($roles)) {
                $roles = [];
            }

            $emailRequired = $this->rolesRequireEmail($roles);

            // التحقق من صحة البيانات
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => ($emailRequired ? 'required' : 'nullable').'|string|email|max:255|unique:users,email,'.$id,
                'phone' => 'nullable|string|max:20|regex:/^\+[1-9]\d{1,14}$/|unique:users,phone,'.$id,
                'is_active' => 'boolean',
                'roles' => 'nullable|array',
                'roles.*' => 'string|exists:roles,name',
            ], [
                'name.required' => 'الاسم مطلوب',
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'البريد الإلكتروني غير صحيح',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
                'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
                'roles.array' => 'صيغة الأدوار غير صحيحة',
                'roles.*.exists' => 'أحد الأدوار المختارة غير موجود',
            ]);

            // تجهيز البيانات للتحديث (الاسم، البريد، الهاتف، تفعيل الحساب فقط)
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active'),
            ];

            if ($emailRequired) {
                $updateData['email'] = $request->email;
            } elseif ($request->filled('email')) {
                $updateData['email'] = $request->email;
            }

            $user->update($updateData);

            // حماية: منع إسقاط آخر Admin من النظام
            $hadAdminRole = $user->hasRole('admin');
            $willKeepAdminRole = in_array('admin', $roles, true);
            if ($hadAdminRole && !$willKeepAdminRole) {
                $otherAdminsCount = User::where('id', '!=', $user->id)
                    ->whereHas('roles', function ($q) {
                        $q->where('name', 'admin');
                    })
                    ->count();

                if ($otherAdminsCount === 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', '❌ لا يمكن إزالة دور admin من آخر مدير في النظام.');
                }
            }

            $user->syncRoles($roles);

            if ($request->input('return_context') === 'manage') {
                return redirect()->route('admin.users.manage')
                    ->with('success', "✅ تم تحديث بيانات المستخدم ({$user->name}) بنجاح");
            }

            if ($request->input('return_context') === 'admin' || $user->hasRole('admin')) {
                return redirect()->route('admin.admins.index')
                    ->with('success', "✅ تم تحديث بيانات المدير ({$user->name}) بنجاح");
            }

            if ($request->input('return_context') === 'supervisor' || $user->hasSupervisorStaffIdentity()) {
                return redirect()->route('admin.supervisors.assignments.index')
                    ->with('success', "✅ تم تحديث بيانات المستخدم ({$user->name}) بنجاح");
            }
            if ($request->input('return_context') === 'teacher' || $user->hasTeacherStaffIdentity()) {
                return redirect()->route('admin.teachers.assignments.index')
                    ->with('success', "✅ تم تحديث بيانات المستخدم ({$user->name}) بنجاح");
            }

            return redirect()->route('users.index')
                ->with('success', "✅ تم تحديث بيانات المستخدم ({$user->name}) بنجاح");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', '❌ المستخدم المطلوب غير موجود');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', '❌ فشل تحديث المستخدم. يرجى التحقق من البيانات المدخلة.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ حدث خطأ أثناء تحديث المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $userName = $user->name;

            // حذف الصورة إذا كانت موجودة
            if ($user->photo) {
                try {
                    StorageHelper::delete('avatars', $user->photo);
                } catch (\Exception $e) {
                    // لا نوقف العملية إذا فشل حذف الصورة
                }
            }

            // حذف المستخدم
            $user->delete();

            // الرجوع لنفس الصفحة التي تم منها الحذف (users-management أو users.index)
            return redirect()->back()
                ->with("success", "✅ تم حذف المستخدم ({$userName}) بنجاح");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', '❌ المستخدم المطلوب غير موجود');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '❌ حدث خطأ أثناء حذف المستخدم: ' . $e->getMessage());
        }
    }



    public function updatePassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ], [
                'password.required' => 'كلمة المرور مطلوبة',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('users.index')
                ->with('success', "✅ تم تحديث كلمة مرور المستخدم ({$user->name}) بنجاح");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', '❌ فشل تحديث كلمة المرور. يرجى التحقق من البيانات المدخلة.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '❌ حدث خطأ أثناء تحديث كلمة المرور: ' . $e->getMessage());
        }
    }

    /**
     * حذف نهائي لانضمامات الصف والمواد المرتبطة به (تجاوز SoftDeletes حتى يُعاد الطالب كجديد عند إعادة التسجيل).
     *
     * @return array{class_enrollments: int, enrollments: int}
     */
    protected function permanentlyDetachUsersFromClass(array $userIds, int $classId): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return ['class_enrollments' => 0, 'enrollments' => 0];
        }

        $subjectIds = Subject::withTrashed()->where('class_id', $classId)->pluck('id');

        $classRows = ClassEnrollment::withTrashed()
            ->whereIn('user_id', $userIds)
            ->where('class_id', $classId)
            ->get();
        $deletedClassEnrollments = $classRows->count();
        foreach ($classRows as $row) {
            $row->forceDelete();
        }

        if ($subjectIds->isEmpty()) {
            return ['class_enrollments' => $deletedClassEnrollments, 'enrollments' => 0];
        }

        $enrollmentRows = Enrollment::withTrashed()
            ->whereIn('user_id', $userIds)
            ->whereIn('subject_id', $subjectIds)
            ->get();
        $deletedEnrollments = $enrollmentRows->count();
        foreach ($enrollmentRows as $row) {
            $row->forceDelete();
        }

        return [
            'class_enrollments' => $deletedClassEnrollments,
            'enrollments' => $deletedEnrollments,
        ];
    }

    /**
     * تبديل حالة المستخدم (تفعيل / إلغاء تفعيل) عبر فورم عادي بدون Ajax
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $oldStatus = (bool) $user->is_active;

            // تبديل حالة الحساب فقط من خلال الحقل is_active
            $user->is_active = ! $oldStatus;
            $user->save();

            $statusText = $user->is_active ? 'مفعل' : 'غير مفعل';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة المستخدم (ID: {$user->id}) إلى: {$statusText}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'فشل تحديث حالة المستخدم (ID: ' . $id . '): ' . $e->getMessage());
        }
    }

    /**
     * فصل الطالب عن صف: حذف نهائي لـ ClassEnrollment وسجلات Enrollment لمواد الصف (بدون Soft Delete).
     */
    public function detachFromClass(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'class_id' => 'required|integer|exists:classes,id',
            ]);

            $userId = (int) $validated['user_id'];
            $classId = (int) $validated['class_id'];

            $deleted = $this->permanentlyDetachUsersFromClass([$userId], $classId);

            $this->revokeClassPurchaseAccessForUsers([$userId], $classId);

            return response()->json([
                'success' => true,
                'message' => 'تم فصل الطالب عن الصف نهائياً.',
                'deleted' => [
                    'class_enrollments' => $deleted['class_enrollments'],
                    'enrollments' => $deleted['enrollments'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الفصل غير صحيحة.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error detaching user from class', [
                'user_id' => $request->input('user_id'),
                'class_id' => $request->input('class_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء فصل الطالب عن الصف.',
            ], 500);
        }
    }

    /**
     * فصل جماعي للطلاب المحددين عن صف محدد (حذف نهائي).
     */
    public function detachMultipleFromClass(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'required|integer|exists:users,id',
                'class_id' => 'required|integer|exists:classes,id',
            ]);

            $userIds = array_map('intval', $validated['user_ids']);
            $classId = (int) $validated['class_id'];

            $deleted = $this->permanentlyDetachUsersFromClass($userIds, $classId);

            $this->revokeClassPurchaseAccessForUsers($userIds, $classId);

            return response()->json([
                'success' => true,
                'message' => 'تم فصل الطلاب عن الصف نهائياً.',
                'deleted' => [
                    'class_enrollments' => $deleted['class_enrollments'],
                    'enrollments' => $deleted['enrollments'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الفصل غير صحيحة.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error detaching multiple users from class', [
                'user_ids' => $request->input('user_ids'),
                'class_id' => $request->input('class_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء فصل الطلاب عن الصف.',
            ], 500);
        }
    }

    /**
     * فصل جميع الطلاب عن صف محدد (حذف نهائي لـ ClassEnrollment و Enrollment ذات الصلة).
     */
    public function detachAllFromClass(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|integer|exists:classes,id',
            ]);

            $classId = (int) $validated['class_id'];

            $subjectIds = Subject::withTrashed()->where('class_id', $classId)->pluck('id');

            $cacheUserIds = ClassEnrollment::withTrashed()->where('class_id', $classId)->pluck('user_id')
                ->merge(Enrollment::withTrashed()->whereIn('subject_id', $subjectIds)->pluck('user_id'))
                ->merge(Purchase::where('purchasable_type', SchoolClass::class)->where('purchasable_id', $classId)->pluck('user_id'))
                ->unique()
                ->filter()
                ->values()
                ->all();

            $detachUserIds = ClassEnrollment::withTrashed()->where('class_id', $classId)->pluck('user_id')
                ->merge(Enrollment::withTrashed()->whereIn('subject_id', $subjectIds)->pluck('user_id'))
                ->unique()
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $deleted = $this->permanentlyDetachUsersFromClass($detachUserIds, $classId);

            Purchase::where('purchasable_type', SchoolClass::class)
                ->where('purchasable_id', $classId)
                ->whereIn('status', ['completed', 'pending'])
                ->update([
                    'status' => 'cancelled',
                    'cancelled_by' => 'admin',
                    'cancelled_at' => now(),
                ]);

            $this->flushPricingAccessCacheForClassUsers($classId, $cacheUserIds);

            return response()->json([
                'success' => true,
                'message' => 'تم فصل جميع الطلاب عن الصف نهائياً.',
                'deleted' => [
                    'class_enrollments' => $deleted['class_enrollments'],
                    'enrollments' => $deleted['enrollments'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الفصل غير صحيحة.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error detaching all users from class', [
                'class_id' => $request->input('class_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء فصل جميع الطلاب عن الصف.',
            ], 500);
        }
    }

    /**
     * فصل جميع الطلاب عن مادة ضمن صف محدد (Enrollment فقط):
     * - لا يتم حذف ClassEnrollment
     * - يتم حذف Enrollment لهذه المادة فقط (مع التحقق أن المادة ضمن class_id)
     */
    public function detachAllFromSubject(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|integer|exists:classes,id',
                'subject_id' => 'required|integer|exists:subjects,id',
            ]);

            $classId = (int) $validated['class_id'];
            $subjectId = (int) $validated['subject_id'];

            $subject = Subject::findOrFail($subjectId);
            if ((int) $subject->class_id !== $classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'المادة لا تنتمي إلى الصف المحدد.',
                ], 422);
            }

            $enrollmentRows = Enrollment::withTrashed()
                ->where('subject_id', $subjectId)
                ->get();
            $deletedEnrollments = $enrollmentRows->count();
            foreach ($enrollmentRows as $row) {
                $row->forceDelete();
            }

            return response()->json([
                'success' => true,
                'message' => 'تم فصل جميع الطلاب عن المادة نهائياً.',
                'deleted' => [
                    'enrollments' => $deletedEnrollments,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الفصل غير صحيحة.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error detaching all users from subject', [
                'class_id' => $request->input('class_id'),
                'subject_id' => $request->input('subject_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء فصل جميع الطلاب عن المادة.',
            ], 500);
        }
    }

    /**
     * عرض سجلات الدخول للمستخدم
     */
    public function loginLogs(string $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $logs = LoginLog::where('user_id', $user->id)
                ->latest('login_at')
                ->paginate(20);

            // إحصائيات
            $stats = [
                'total' => LoginLog::where('user_id', $user->id)->count(),
                'successful' => LoginLog::where('user_id', $user->id)->successful()->count(),
                'failed' => LoginLog::where('user_id', $user->id)->failed()->count(),
                'total_duration' => LoginLog::where('user_id', $user->id)
                    ->whereNotNull('session_duration_seconds')
                    ->sum('session_duration_seconds'),
            ];

            return view('admin.pages.users.login-logs', compact('user', 'logs', 'stats'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', 'المستخدم المطلوب غير موجود');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'حدث خطأ أثناء عرض سجلات الدخول: ' . $e->getMessage());
        }
    }

    /**
     * إرسال كود التحقق للمستخدم يدوياً
     */
    public function sendVerificationOTP(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود أو تم حذفه.',
            ], 404);
        }

        try {
            // التحقق من وجود رقم هاتف
            if (!$user->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم لا يملك رقم هاتف مسجل',
                ], 400);
            }

            // تطبيع رقم الهاتف تلقائياً (إضافة + ورمز الدولة إن لزم) وتحديثه في قاعدة البيانات
            $phone = PhoneHelper::normalize($user->phone, config('app.phone_default_country_code', '966'));
            if ($phone === null || !preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف غير صحيح. أدخل رقماً يبدأ برمز الدولة (مثال: 966501234567 أو 0501234567)',
                ], 400);
            }
            if ($phone !== $user->phone) {
                $user->update(['phone' => $phone]);
            } else {
                $user->refresh();
            }
            $phoneForSend = $phone;

            Log::info('Admin sending verification OTP manually', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'phone' => $phoneForSend,
            ]);

            // إنشاء OTP جديد (باستخدام الرقم المُطبّع)
            $otp = $this->otpService->generateOTP($user, $phoneForSend, 'verification');

            Log::info('OTP generated for manual send', [
                'otp_id' => $otp->id,
                'phone' => $otp->phone,
                'expires_at' => $otp->expires_at,
            ]);

            // إرسال OTP عبر SMS (افتراضي) أو WhatsApp حسب الإعداد
            $provider = SystemSetting::get('otp_provider', 'sms');
            
            Log::info('Attempting to send OTP manually', [
                'provider' => $provider,
                'phone' => $phoneForSend,
            ]);

            $sent = $this->otpService->sendOTP($otp, $provider);

            if (!$sent) {
                Log::warning('Manual OTP send failed', [
                    'user_id' => $user->id,
                    'phone' => $phoneForSend,
                    'provider' => $provider,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'فشل إرسال كود التحقق. يرجى التحقق من إعدادات SMS/WhatsApp',
                ], 500);
            }

            Log::info('Manual OTP sent successfully', [
                'user_id' => $user->id,
                'phone' => $phoneForSend,
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال كود التحقق بنجاح إلى ' . substr($phoneForSend, 0, 4) . '****' . substr($phoneForSend, -4),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending manual verification OTP', [
                'user_id' => $user->id,
                'phone' => $user->phone ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال كود التحقق: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تسجيل الدخول كالمستخدم المحدد
     * يدعم GET (signed URL) و POST (form) للسماح بفتح الرابط مباشرة
     */
    public function impersonate(User $user)
    {
        $isSignedUrl = request()->isMethod('get');

        if ($isSignedUrl) {
            // الرابط الموقّع يُتحقق منه عبر signed middleware
            if (auth()->check()) {
                if (! auth()->user()->can('user-impersonate')) {
                    abort(403, 'غير مصرح لك بتسجيل الدخول كالمستخدم');
                }

                if ($user->id === auth()->id()) {
                    return redirect()->back()->with('error', 'لا يمكنك تسجيل الدخول كحسابك الخاص');
                }

                $impersonatorId = auth()->id();
                $impersonatorName = auth()->user()->name;
            } else {
                $impersonatorId = null;
                $impersonatorName = 'System (Signed URL)';
            }
        } else {
            if (! auth()->check() || ! auth()->user()->can('user-impersonate')) {
                abort(403, 'غير مصرح لك بتسجيل الدخول كالمستخدم');
            }

            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'لا يمكنك تسجيل الدخول كحسابك الخاص');
            }

            $impersonatorId = auth()->id();
            $impersonatorName = auth()->user()->name;
        }

        // التحقق من أن المستخدم نشط
        if (!$user->is_active) {
            if (auth()->check()) {
                return redirect()->back()->with('error', 'لا يمكن تسجيل الدخول كحساب غير نشط');
            } else {
                return redirect()->route('login')->with('error', 'لا يمكن تسجيل الدخول كحساب غير نشط');
            }
        }

        // حفظ المستخدم الأصلي في session (إذا كان موجود)
        if ($impersonatorId) {
            session()->put('impersonator_id', $impersonatorId);
            session()->put('impersonator_name', $impersonatorName);
        }

        // تسجيل الدخول كالمستخدم الجديد
        Auth::login($user);

        // تسجيل الحدث
        Log::info('Admin ' . ($impersonatorName ?? 'System') . ' logged in as user ' . $user->name . ' (ID: ' . $user->id . ') via ' . (request()->isMethod('get') ? 'signed URL' : 'form'));

        // توجيه حسب صلاحية المستخدم
        $hasAdminDashboard = false;
        
        // 1. التحقق من وجود أي دور بـ dashboard_type = 'admin' (إذا كان العمود موجوداً)
        try {
            $hasAdminDashboard = $user->roles()
                ->where('dashboard_type', 'admin')
                ->exists();
        } catch (\Exception $e) {
            // إذا كان العمود غير موجود، نستخدم fallback
            $hasAdminDashboard = false;
        }

        if ($hasAdminDashboard) {
            // التحقق إذا كان المستخدم مشرف
            if ($user->hasRole('supervisor')) {
                return redirect()->route('admin.my-classes')->with('success', 'تم تسجيل الدخول كالمستخدم ' . $user->name);
            }
            return redirect()->route('admin.dashboard')->with('success', 'تم تسجيل الدخول كالمستخدم ' . $user->name);
        }

        // 2. Fallback: التحقق من أسماء الأدوار (للتوافق مع البيانات القديمة أو عند عدم وجود العمود)
        if ($user->hasRole(['admin', 'supervisor', 'teacher'])) {
            // التحقق إذا كان المستخدم مشرف
            if ($user->hasRole('supervisor')) {
                return redirect()->route('admin.my-classes')->with('success', 'تم تسجيل الدخول كالمستخدم ' . $user->name);
            }
            return redirect()->route('admin.dashboard')->with('success', 'تم تسجيل الدخول كالمستخدم ' . $user->name);
        }

        return redirect()->route('student.dashboard')->with('success', 'تم تسجيل الدخول كالمستخدم ' . $user->name);
    }

    /**
     * العودة للحساب الأصلي
     * يدعم GET و POST للسماح بفتح الرابط مباشرة
     */
    public function stopImpersonate()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('admin.dashboard');
        }

        $impersonatorId = session('impersonator_id');
        $impersonatorName = session('impersonator_name');
        $impersonator = User::find($impersonatorId);

        if (!$impersonator) {
            session()->forget(['impersonator_id', 'impersonator_name']);
            Auth::logout();
            return redirect()->route('login')->with('error', 'المستخدم الأصلي غير موجود');
        }

        $currentUserName = Auth::user()->name;

        // تسجيل الخروج من الحساب الحالي
        Auth::logout();

        // تسجيل الدخول كالمستخدم الأصلي
        Auth::login($impersonator);

        // حذف بيانات الـ impersonation
        session()->forget(['impersonator_id', 'impersonator_name']);

        // تسجيل الحدث
        Log::info('Admin ' . $impersonatorName . ' stopped impersonating user ' . $currentUserName);

        return redirect()->route('admin.dashboard')->with('success', 'تم العودة لحسابك الأصلي');
    }

    /**
     * إلغاء شراء الصف (مكتمل أو معلق) بعد فصل الطالب عن الصف حتى لا يبقى وصول عبر Purchase في AccessResolver.
     */
    protected function revokeClassPurchaseAccessForUsers(array $userIds, int $classId): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return;
        }

        Purchase::whereIn('user_id', $userIds)
            ->where('purchasable_type', SchoolClass::class)
            ->where('purchasable_id', $classId)
            ->whereIn('status', ['completed', 'pending'])
            ->update([
                'status' => 'cancelled',
                'cancelled_by' => 'admin',
                'cancelled_at' => now(),
            ]);

        $this->flushPricingAccessCacheForClassUsers($classId, $userIds);
    }

    protected function flushPricingAccessCacheForClassUsers(int $classId, array $userIds): void
    {
        $cache = app(\App\Services\Pricing\PricingCacheManager::class);
        $class = SchoolClass::find($classId);
        if ($class) {
            $cache->invalidateClass($class);
        }
        foreach ($userIds as $uid) {
            $user = User::find($uid);
            if ($user) {
                $cache->invalidateUserAccess($user);
            }
        }
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function rolesRequireEmail(array $roles): bool
    {
        $staffRoles = ['admin', 'teacher', 'supervisor'];

        return (bool) array_intersect($roles, $staffRoles);
    }

}
