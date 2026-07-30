<?php

namespace App\AiHtmlQuiz\Services;

use App\AiHtmlQuiz\Models\AiHtmlQuiz;

/**
 * Assembles a full HTML document for sandboxed iframe playback.
 */
class AiHtmlQuizBundleAssembler
{
    public function __construct(
        protected AiHtmlQuizBundleNormalizer $normalizer
    ) {}

    public function assembleDocument(AiHtmlQuiz $quiz): string
    {
        return $this->assembleFromParts(
            (string) $quiz->bundle_html,
            (string) $quiz->bundle_css,
            (string) $quiz->bundle_js,
            (string) $quiz->title
        );
    }

    public function assembleFromParts(string $html, string $css, string $js, string $title = 'اختبار'): string
    {
        $html = $this->normalizer->sanitizeHtml($html);
        $css = $this->normalizer->sanitizeCss($css);
        $js = $this->normalizer->sanitizeJs($js);

        if (! $this->normalizer->containsResultPostMessage($js) && ! $this->normalizer->containsResultPostMessage($html)) {
            $js = $this->normalizer->resultBridgeSnippet()."\n\n".$this->normalizer->safeTerminateJs($js);
        }

        $safeTitle = htmlspecialchars($title !== '' ? $title : 'اختبار', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $csp = "default-src 'none'; script-src 'unsafe-inline'; script-src-attr 'unsafe-inline'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src data: https://fonts.gstatic.com; img-src data: blob:; media-src data: blob: 'self'; connect-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'";

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Security-Policy" content="{$csp}">
<title>{$safeTitle}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
{$css}
</style>
</head>
<body>
{$html}
<script>
{$js}
</script>
</body>
</html>
HTML;
    }
}
