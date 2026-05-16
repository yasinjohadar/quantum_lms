<?php

namespace App\Http\Middleware;

use App\Models\Purchase;
use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareStudentPendingPurchasesForLayout
{
    /**
     * مشاركة مشتريات الطالب المعلّقة ورقم واتساب المشرفة مع الواجهات التي تضمّنها صراحةً
     * (مثل صفحة «صفوفي» عبر middleware على مسار student/classes).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            View::share([
                'pendingPurchases' => collect(),
                'supervisorWhatsappDigits' => '',
            ]);

            return $next($request);
        }

        $user = $request->user();

        $pendingPurchases = Purchase::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('purchasable')
            ->orderByDesc('created_at')
            ->get();

        $supervisorWhatsapp = SystemSetting::get('student_supervisor_whatsapp_number', '');
        $supervisorWhatsappDigits = preg_replace('/\D/', '', (string) $supervisorWhatsapp);

        View::share([
            'pendingPurchases' => $pendingPurchases,
            'supervisorWhatsappDigits' => $supervisorWhatsappDigits,
        ]);

        return $next($request);
    }
}
