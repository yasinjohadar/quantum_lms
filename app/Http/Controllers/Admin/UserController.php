<?php

namespace App\Http\Controllers\Admin;

use HashContext;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\SystemSetting;
use App\Services\SMS\OTPService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Helpers\StorageHelper;

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
        private OTPService $otpService
    ) {
        // تأكد أن المستخدم مصادق أولًا ثم تحقق من الصلاحيات
        $this->middleware('auth');

        $this->middleware('permission:user-list')->only('index');
        $this->middleware('permission:user-create')->only(['create', 'store']);
        $this->middleware('permission:user-edit')->only(['edit', 'update']);
        $this->middleware('permission:user-delete')->only('destroy');
        $this->middleware('permission:user-show')->only('show');
    }

    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
    {
        $roles = Role::all();

        // جلب آخر جلسات المستخدمين
        $sessions = DB::table('sessions')
            ->orderByDesc('last_activity')
            ->get()
            ->groupBy('user_id');

        // بدء استعلام المستخدمين (استبعاد المؤرشفين)
        $usersQuery = User::query()->notArchived();

        // فلترة حسب البحث (name, email, phone)
        if ($request->filled('query')) {
            $search = $request->input('query');
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // فلترة حسب الحالة النشطة
        if ($request->filled('is_active')) {
            $usersQuery->where('is_active', $request->input('is_active'));
        }

        // تنفيذ الاستعلام
        $users = $usersQuery->paginate(10);

        return view("admin.pages.users.index", compact("users", "roles", "sessions"));
    }





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view("admin.pages.users.create" ,compact("roles"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // التحقق من صحة البيانات
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone' => 'nullable|string|max:20|unique:users,phone',
                'password' => 'required|string|min:8|confirmed',
                'is_active' => 'boolean',
                'roles' => 'array',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'الاسم مطلوب',
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'البريد الإلكتروني غير صحيح',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
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
                    $photoPath = $photo->storeAs('users/photos', $photoName, 'public');
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view("admin.pages.users.profile" , compact("user"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view("admin.pages.users.edit" ,compact("roles" , "user"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // التحقق من صحة البيانات
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
                'password' => 'nullable|string|min:8|confirmed',
                'is_active' => 'boolean',
                'roles' => 'array',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'الاسم مطلوب',
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'البريد الإلكتروني غير صحيح',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
                'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
                'photo.image' => 'يجب أن يكون الملف صورة',
                'photo.mimes' => 'نوع الصورة غير مدعوم',
                'photo.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
            ]);

            // تجهيز البيانات للتحديث
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active'),
            ];

            // تحديث كلمة المرور فقط إذا تم إدخالها
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // معالجة الصورة
            if ($request->hasFile('photo')) {
                try {
                    // حذف الصورة القديمة إذا كانت موجودة
                    if ($user->photo) {
                        StorageHelper::delete('avatars', $user->photo);
                    }

                    $photo = $request->file('photo');
                    $photoName = time() . '_' . $photo->getClientOriginalName();
                    $photoPath = 'users/photos/' . $photoName;
                    $photoPath = StorageHelper::store('avatars', $photoPath, file_get_contents($photo->getRealPath()), 'image') ? $photoPath : $photo->storeAs('users/photos', $photoName, 'public');
                    $updateData['photo'] = $photoPath;
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'فشل رفع الصورة: ' . $e->getMessage());
                }
            }

            // تحديث المستخدم
            $user->update($updateData);

            // تحديث الأدوار
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
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

            return redirect()->route("users.index")
                ->with("success", "✅ تم حذف المستخدم ({$userName}) بنجاح");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', '❌ المستخدم المطلوب غير موجود');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
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
    public function sendVerificationOTP(User $user): JsonResponse
    {
        try {
            // التحقق من وجود رقم هاتف
            if (!$user->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم لا يملك رقم هاتف مسجل',
                ], 400);
            }

            // التحقق من أن رقم الهاتف بصيغة صحيحة
            if (!preg_match('/^\+[1-9]\d{1,14}$/', $user->phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف غير صحيح. يجب أن يبدأ بـ + متبوعاً برمز الدولة',
                ], 400);
            }

            Log::info('Admin sending verification OTP manually', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]);

            // إنشاء OTP جديد
            $otp = $this->otpService->generateOTP($user, $user->phone, 'verification');

            Log::info('OTP generated for manual send', [
                'otp_id' => $otp->id,
                'phone' => $otp->phone,
                'expires_at' => $otp->expires_at,
            ]);

            // إرسال OTP عبر SMS (افتراضي) أو WhatsApp حسب الإعداد
            $provider = SystemSetting::get('otp_provider', 'sms');
            
            Log::info('Attempting to send OTP manually', [
                'provider' => $provider,
                'phone' => $user->phone,
            ]);

            $sent = $this->otpService->sendOTP($otp, $provider);

            if (!$sent) {
                Log::warning('Manual OTP send failed', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'provider' => $provider,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'فشل إرسال كود التحقق. يرجى التحقق من إعدادات SMS/WhatsApp',
                ], 500);
            }

            Log::info('Manual OTP sent successfully', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال كود التحقق بنجاح إلى ' . substr($user->phone, 0, 4) . '****' . substr($user->phone, -4),
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

}
