<?php

if (! function_exists('media_public_url')) {
    /**
     * رابط عام للملف المخزّن تحت مسار public storage (يفضّل السحابة عند توفر الملف هناك).
     */
    function media_public_url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        $path = trim((string) $path);
        // قديم: حُفظ الرابط كاملاً مع مضيف خاطئ (localhost أو دومين قديم) — نستخرج المسار بعد /storage/
        if (preg_match('#^https?://[^/]+/storage/(.+)$#i', $path, $m)) {
            $path = $m[1];
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        try {
            return \App\Services\Storage\MediaStorageService::url($normalized);
        } catch (\Throwable $e) {
            // Fallback to plain local storage URL when service not yet available
            try {
                return \Illuminate\Support\Facades\Storage::disk(
                    config('storage.fallback_disk', 'public')
                )->url($normalized);
            } catch (\Throwable $inner) {
                $p = ltrim(str_replace('\\', '/', $normalized), '/');

                return '/storage/'.$p;
            }
        }
    }
}
