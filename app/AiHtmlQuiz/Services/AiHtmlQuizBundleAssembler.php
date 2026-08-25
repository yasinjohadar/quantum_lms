<?php

namespace App\AiHtmlQuiz\Services;

use App\AiHtmlQuiz\Models\AiHtmlQuiz;

/**
 * Assembles a full HTML document for sandboxed iframe playback.
 */
class AiHtmlQuizBundleAssembler
{
    /**
     * مسارات محلية ثابتة (بلا أي CDN) — التطابق فقط بمفتاح من
     * AiHtmlQuizBundleNormalizer::AVAILABLE_LIBS يحدد ما يُحمَّل؛ لا صلة لهذه
     * الخريطة بأي مدخل من الذكاء الاصطناعي نفسه.
     */
    protected const LIB_ASSETS = [
        'chart' => ['script' => '/vendor/ai-html-quiz-libs/chart.min.js'],
        'confetti' => ['script' => '/vendor/ai-html-quiz-libs/confetti.min.js'],
        'katex' => [
            'script' => '/vendor/ai-html-quiz-libs/katex/katex.min.js',
            'style' => '/vendor/ai-html-quiz-libs/katex/katex.min.css',
        ],
        'mermaid' => ['script' => '/vendor/ai-html-quiz-libs/mermaid.min.js'],
    ];

    public function __construct(
        protected AiHtmlQuizBundleNormalizer $normalizer
    ) {}

    public function assembleDocument(AiHtmlQuiz $quiz): string
    {
        $libs = $quiz->prompt_meta['libs'] ?? [];

        return $this->assembleFromParts(
            (string) $quiz->bundle_html,
            (string) $quiz->bundle_css,
            (string) $quiz->bundle_js,
            (string) $quiz->title,
            is_array($libs) ? $libs : []
        );
    }

    /**
     * @param  list<string>  $libs
     */
    public function assembleFromParts(string $html, string $css, string $js, string $title = 'اختبار', array $libs = []): string
    {
        $html = $this->normalizer->sanitizeHtml($html);
        $css = $this->normalizer->sanitizeCss($css);
        $js = $this->normalizer->sanitizeJs($js);

        if (! $this->normalizer->containsResultPostMessage($js) && ! $this->normalizer->containsResultPostMessage($html)) {
            $js = $this->normalizer->resultBridgeSnippet()."\n\n".$this->normalizer->safeTerminateJs($js);
        }

        $safeTitle = htmlspecialchars($title !== '' ? $title : 'اختبار', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $csp = "default-src 'none'; script-src 'self' 'unsafe-inline'; script-src-attr 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; img-src data: blob:; media-src data: blob: 'self'; connect-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'";
        $libTags = $this->libraryTags($libs);

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
{$libTags}
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

    /**
     * يبني وسوم <link>/<script> محلية فقط لمكتبات مطابقة لخريطة LIB_ASSETS
     * الثابتة — أي مفتاح غير معروف يُتجاهل بصمت (دفاع مضاعف فوق الفلترة
     * التي يقوم بها AiHtmlQuizBundleNormalizer::filterLibs()).
     *
     * @param  list<string>  $libs
     */
    protected function libraryTags(array $libs): string
    {
        $libs = $this->normalizer->filterLibs($libs);
        if ($libs === []) {
            return '';
        }

        $tags = [];
        foreach ($libs as $lib) {
            $asset = self::LIB_ASSETS[$lib] ?? null;
            if ($asset === null) {
                continue;
            }
            if (isset($asset['style'])) {
                $tags[] = '<link rel="stylesheet" href="'.htmlspecialchars($asset['style'], ENT_QUOTES, 'UTF-8').'">';
            }
            if (isset($asset['script'])) {
                $tags[] = '<script src="'.htmlspecialchars($asset['script'], ENT_QUOTES, 'UTF-8').'"></script>';
            }
        }

        return implode("\n", $tags);
    }
}
