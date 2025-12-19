<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;

// Route للصفحة الرئيسية - يوجه حسب الصلاحية
Route::get('/', function () {
    // إذا لم يكن المستخدم مسجل دخول، يوجه إلى login
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    $user = auth()->user();
    
    // التحقق من الصلاحيات
    if ($user->hasRole('admin')) {
        return view('admin.dashboard');
    } elseif ($user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }
    
    // إذا لم يكن لديه صلاحية محددة، يوجه إلى student dashboard كافتراضي
    return redirect()->route('student.dashboard');
})->middleware('auth')->name('admin.dashboard');

Route::get('/dashboard', function () {
    // إذا لم يكن المستخدم مسجل دخول، يوجه إلى login
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    $user = auth()->user();
    
    // التحقق من الصلاحيات
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }
    
    // إذا لم يكن لديه صلاحية محددة، يوجه إلى student dashboard كافتراضي
    return redirect()->route('student.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'check.user.active'])->group(function () {
    // Profile routes - متاحة لجميع المستخدمين
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Profile route للطالب - يمكن الوصول من routes/student.php أيضاً
Route::middleware(['auth', 'check.user.active'])->prefix('student')->as('student.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
});

// Admin routes - محمية بصلاحية admin فقط
Route::middleware(['auth', 'check.user.active', 'admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::get('users/{user}/login-logs', [UserController::class, 'loginLogs'])->name('users.login-logs');
    Route::resource('roles', RoleController::class);
    Route::put('users/{user}/change-password', [UserController::class, 'updatePassword'])->name('users.update-password');
});

// مسار toggle-status - محمي بصلاحية admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('toggle-user-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status-alt');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
