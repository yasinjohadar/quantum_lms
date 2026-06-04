<?php

if (! function_exists('question_heading_text')) {
    /**
     * عنوان الصفحة (ترويسة) بدون معادلات LaTeX خام.
     */
    function question_heading_text(?string $text, int $limit = 100): string
    {
        return \App\Support\QuestionMarkupFormatter::plainHeading($text, $limit);
    }
}

if (! function_exists('format_question_markup')) {
    /**
     * تحويل النص بين backticks إلى كود مميز، مع دعم كتل ``` للنص متعدد الأسطر.
     */
    function format_question_markup(?string $text): string
    {
        return \App\Support\QuestionMarkupFormatter::format($text);
    }
}

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

if (! function_exists('tinymce_public_image_url')) {
    /**
     * رابط صورة للمحرر والعرض: يفضّل /storage/ على نفس الموقع إن وُجد الملف محلياً،
     * وإلا رابط السحابة (بما في ذلك presigned عند الحاجة).
     */
    function tinymce_public_image_url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        $path = trim((string) $path);
        if (preg_match('#^https?://[^/]+/storage/(.+)$#i', $path, $m)) {
            $path = $m[1];
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if ($normalized === '') {
            return '';
        }

        try {
            $localDisk = \Illuminate\Support\Facades\Storage::disk(
                config('storage.fallback_disk', 'public')
            );
            if ($localDisk->exists($normalized)) {
                return url('/storage/'.$normalized);
            }
        } catch (\Throwable) {
            //
        }

        return media_public_url($normalized);
    }
}

if (! function_exists('linkify_plain_text')) {
    /**
     * نص عادي آمن مع أسطر جديدة وروابط http/https قابلة للنقر.
     */
    function linkify_plain_text(?string $text): string
    {
        $escaped = e($text ?? '');
        $withBreaks = nl2br($escaped);

        return (string) preg_replace_callback(
            '~\bhttps?://[^\s<]+~iu',
            static function (array $matches): string {
                $url = $matches[0];

                return '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($url).'</a>';
            },
            $withBreaks
        );
    }
}
