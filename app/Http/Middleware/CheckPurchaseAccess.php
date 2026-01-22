<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PurchaseService;
use App\Models\Subject;
use App\Models\SchoolClass;

class CheckPurchaseAccess
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // التحقق من الوصول للمادة
        if ($request->route('subject')) {
            $subject = $request->route('subject');
            if (is_numeric($subject)) {
                $subject = Subject::findOrFail($subject);
            }

            if (!$this->purchaseService->checkAccess($user, $subject)) {
                return redirect()->route('student.purchases.subject.show', $subject->id)
                    ->with('error', 'يجب شراء هذه المادة للوصول إليها');
            }
        }

        // التحقق من الوصول للصف
        if ($request->route('class')) {
            $class = $request->route('class');
            if (is_numeric($class)) {
                $class = SchoolClass::findOrFail($class);
            }

            if (!$this->purchaseService->checkAccess($user, $class)) {
                return redirect()->route('student.purchases.class.show', $class->id)
                    ->with('error', 'يجب شراء هذا الصف للوصول إليه');
            }
        }

        return $next($request);
    }
}
