<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\frontend\HomeController;
use App\Http\Controllers\NotificationInboxController;

// Route للصفحة الرئيسية - يوجه إلى صفحة Frontend (متاح للجميع بدون middleware auth)
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    // إذا لم يكن المستخدم مسجل دخول، يوجه إلى login
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    $user = auth()->user();
    
    // 1. التحقق من وجود أي دور بـ dashboard_type = 'admin' أو 'teacher' (المعلم يستخدم نفس لوحة admin بصلاحيات مخصصة)
    $hasAdminOrTeacherDashboard = false;
    try {
        $hasAdminOrTeacherDashboard = $user->roles()
            ->whereIn('dashboard_type', ['admin', 'teacher'])
            ->exists();
    } catch (\Exception $e) {
        $hasAdminOrTeacherDashboard = false;
    }

    if ($hasAdminOrTeacherDashboard) {
        // التحقق إذا كان المستخدم مشرف
        if ($user->hasRole('supervisor')) {
            return redirect()->route('admin.my-classes');
        }
        return redirect()->route('admin.dashboard');
    }

    // 2. Fallback: التحقق من أسماء الأدوار (للتوافق مع البيانات القديمة أو عند عدم وجود العمود)
    if ($user->hasRole(['admin', 'supervisor', 'teacher'])) {
        // التحقق إذا كان المستخدم مشرف
        if ($user->hasRole('supervisor')) {
            return redirect()->route('admin.my-classes');
        }
        return redirect()->route('admin.dashboard');
    }
    
    // افتراضي: student dashboard
    return redirect()->route('student.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'check.user.active'])->group(function () {
    Route::get('/notifications/inbox/recent', [NotificationInboxController::class, 'recent'])->name('notifications.inbox.recent');
    Route::get('/notifications/inbox/unread-count', [NotificationInboxController::class, 'unreadCount'])->name('notifications.inbox.unread-count');
    Route::post('/notifications/inbox/read-all', [NotificationInboxController::class, 'markAllRead'])->name('notifications.inbox.read-all');

    // Profile routes - متاحة لجميع المستخدمين
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Profile route للطالب - يمكن الوصول من routes/student.php أيضاً
Route::middleware(['auth', 'check.user.active'])->prefix('student')->as('student.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
});

// Route للـ impersonation بدون auth middleware (يستخدم signed URL)
Route::get('users/{user}/impersonate', [UserController::class, 'impersonate'])
    ->middleware('signed')
    ->name('users.impersonate.link');

// Admin routes - محمية بصلاحية admin فقط
Route::middleware(['auth', 'check.user.active', 'admin'])->group(function () {
    Route::post('users/quick-student', [UserController::class, 'storeQuickStudent'])->name('users.store-quick-student');
    Route::resource('users', UserController::class);
    Route::get('users/{user}/login-logs', [UserController::class, 'loginLogs'])->name('users.login-logs');
    Route::get('roles/search-permissions', [RoleController::class, 'searchPermissions'])->name('roles.search-permissions');
    Route::get('roles/{role}/granted-permissions', [RoleController::class, 'grantedPermissions'])->name('roles.granted-permissions');
    Route::resource('roles', RoleController::class);
    Route::put('users/{user}/change-password', [UserController::class, 'updatePassword'])->name('users.update-password');
    Route::patch('users/{user}/subscription-expires', [UserController::class, 'updateSubscriptionExpires'])->name('users.update-subscription-expires');
    Route::post('users/detach-from-class', [UserController::class, 'detachFromClass'])->name('users.detach-from-class');
    Route::post('users/detach-multiple-from-class', [UserController::class, 'detachMultipleFromClass'])->name('users.detach-multiple-from-class');
    Route::post('users/detach-all-from-class', [UserController::class, 'detachAllFromClass'])->name('users.detach-all-from-class');
    Route::post('users/detach-all-from-subject', [UserController::class, 'detachAllFromSubject'])->name('users.detach-all-from-subject');
    Route::post('users/{id}/send-verification-otp', [UserController::class, 'sendVerificationOTP'])->name('users.send-verification-otp');
    
    // تسجيل الدخول كالمستخدم (POST فقط من form)
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::match(['get', 'post'], 'stop-impersonate', [UserController::class, 'stopImpersonate'])->name('stop-impersonate');
});

// مسار toggle-status - محمي بصلاحية admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('toggle-user-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status-alt');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
require __DIR__.'/teacher.php';
require __DIR__.'/frontend.php';