<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // السماح لمن لديهم لوحة تحكم admin أو teacher (المعلم يستخدم نفس الرابط admin مع صلاحيات مخصصة)
        $hasAllowedDashboard = $user->roles()
            ->whereIn('dashboard_type', ['admin', 'teacher'])
            ->exists();

        if (!$hasAllowedDashboard) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}

