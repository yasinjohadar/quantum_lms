<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.user.active');
    }

    /**
     * عرض لوحة تحكم الطالب
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // التحقق من أن المستخدم لديه صلاحية student
        // إذا لم يكن لديه صلاحية، نعطيه صلاحية student تلقائياً (للمستخدمين القدامى)
        if (!$user->hasRole('student')) {
            // محاولة إعطاء صلاحية student تلقائياً
            try {
                $user->assignRole('student');
            } catch (\Exception $e) {
                // إذا فشل تعيين الصلاحية، نعرض رسالة خطأ
                abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة. يرجى التواصل مع الإدارة.');
            }
        }

        return view('student.dashboard', compact('user'));
    }
}

