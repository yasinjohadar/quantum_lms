<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentEnrollment
{
    /**
     * @var array<int, string>
     */
    protected array $allowedRoutePatterns = [
        'student.enrollments.*',
        'student.purchases.*',
        'student.wallet.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            View::share('studentNeedsEnrollment', false);

            return $next($request);
        }

        if ($this->isStaffUser($user)) {
            View::share('studentNeedsEnrollment', false);

            return $next($request);
        }

        $needsEnrollment = ! $user->hasActiveStudentEnrollment();
        View::share('studentNeedsEnrollment', $needsEnrollment);

        if (! $needsEnrollment) {
            return $next($request);
        }

        if ($request->routeIs($this->allowedRoutePatterns)) {
            return $next($request);
        }

        return redirect()
            ->route('student.enrollments.index')
            ->with('enrollment_required_warning', true);
    }

    protected function isStaffUser($user): bool
    {
        return $user->hasRole(['admin', 'supervisor', 'teacher']);
    }
}
