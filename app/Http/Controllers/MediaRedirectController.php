<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * تحويل ثابت (302) لروابط الوسائط: HTML الأسئلة يُخبِّز رابط "/media/{path}" الثابت
 * بدل الرابط المطلق النهائي، لأن روابط التخزين السحابي الخاص (presigned) تنتهي صلاحيتها
 * (7 أيام افتراضياً، media_use_presigned_urls). هذا المسار يُعيد توليد رابط صالح حديثاً
 * عبر media_public_url() في كل طلب، فلا تنتهي صلاحية الصورة المضمَّنة أبداً.
 */
class MediaRedirectController extends Controller
{
    public function show(string $path): RedirectResponse
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $url = media_public_url($path);

        if ($url === '') {
            abort(404);
        }

        return redirect()->away($url);
    }
}
