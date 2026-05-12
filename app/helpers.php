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
                return \Illuminate\Support\Facades\URL::to('/storage/' . $normalized);
            }
        }
    }
}
