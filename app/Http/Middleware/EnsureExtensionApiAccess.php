<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExtensionApiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return response()->json(['message' => 'الحساب غير نشط أو غير مصرح.'], 403);
        }

        if (! $user->can('question-import')) {
            return response()->json(['message' => 'ليس لديك صلاحية استيراد الأسئلة.'], 403);
        }

        $allowedRoles = config('extension.allowed_roles', []);
        if ($allowedRoles !== [] && ! $user->hasAnyRole($allowedRoles)) {
            return response()->json(['message' => 'هذه الإضافة مخصصة لفريق الإدارة والتدريس.'], 403);
        }

        return $next($request);
    }
}
