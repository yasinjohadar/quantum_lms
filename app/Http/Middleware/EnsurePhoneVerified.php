<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من تفعيل الميزة
        $phoneVerificationEnabled = SystemSetting::get('phone_verification_enabled', false);
        
        if (!$phoneVerificationEnabled) {
            // إذا كانت الميزة معطلة، السماح بالدخول
            return $next($request);
        }

        // إذا كان المستخدم غير مسجل دخول، السماح بالدخول (سيتم التحقق في middleware آخر)
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // إذا كان المستخدم مفعلاً وله phone_verified_at، السماح بالدخول
        if ($user->is_active && $user->phone_verified_at) {
            return $next($request);
        }

        // إذا كان المستخدم غير مفعّل أو لم يتم التحقق من رقم الهاتف
        // Redirect إلى صفحة التحقق
        if (!$user->phone_verified_at) {
            return redirect()->route('phone.verify')
                ->with('error', 'يجب التحقق من رقم الهاتف أولاً');
        }

        // إذا كان الحساب غير مفعّل
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'حسابك غير مفعّل. يرجى التواصل مع الإدارة.');
        }

        return $next($request);
    }
}

