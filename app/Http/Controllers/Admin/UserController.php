<?php

namespace App\Http\Controllers\Admin;

use HashContext;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\SystemSetting;
use App\Services\SMS\OTPService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PhoneHelper;
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
        // استثناء impersonate من auth middleware للسماح بـ signed URL (GET requests)
        // الـ POST requests محمية بـ auth middleware في route definition
        $this->middleware('auth')->except(['impersonate']);

        $this->middleware('permission:user-list')->only('index');
        $this->middleware('permission:user-create')->only(['create', 'store']);
        $this->middleware('permission:user-edit')->only(['edit', 'update']);
        $this->middleware('permission:user-delete')->only('destroy');
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

        // بدء استعلام المستخدمين — الطلاب فقط (غير المؤرشفين)
        $usersQuery = User::query()->students();

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

        return view("admin.pages.users.index", compact("users", "roles"));
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
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20|regex:/^\+[1-9]\d{1,14}$/|unique:users,phone,' . $id,
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
        // إذا كان الطلب من signed URL (GET بدون auth)
        if (request()->isMethod('get') && !auth()->check()) {
            // التحقق من أن الرابط موقّع بشكل صحيح (يتم تلقائياً بواسطة signed middleware)
            // لا حاجة لتحقق إضافي لأن الأدمن فقط يمكنه إنشاء الرابط الموقّع
            // لا يوجد مستخدم أصلي في هذه الحالة، لذلك سنستخدم null
            $impersonatorId = null;
            $impersonatorName = 'System (Signed URL)';
        } else {
            // إذا كان الطلب من form (POST مع auth)
            if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                abort(403, 'غير مصرح لك بتسجيل الدخول كالمستخدم');
            }

            // التحقق من أن المستخدم لا يمكنه impersonate نفسه
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'لا يمكنك تسجيل الدخول كحسابك الخاص');
            }

            // حفظ المستخدم الأصلي في session
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

}
