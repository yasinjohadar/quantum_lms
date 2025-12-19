<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // التحقق من أن المستخدم لديه صلاحية admin
        if (!$user->hasRole('admin')) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة. هذه الصفحة مخصصة للإدارة فقط.');
        }

        return $next($request);
    }
}

