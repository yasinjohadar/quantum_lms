<?php

namespace App\AiHtmlQuiz\Services;

/**
 * Normalizes and lightly sanitizes AI-generated HTML/CSS/JS quiz bundles.
 */
class AiHtmlQuizBundleNormalizer
{
    public const RESULT_MESSAGE_TYPE = 'ile-html-quiz-result';

    /**
     * @param  array{title?: mixed, html?: mixed, css?: mixed, js?: mixed, summary?: mixed, answer_key?: mixed}  $raw
     * @return array{title: string, html: string, css: string, js: string, summary: string, answer_key: array|null}
     */
    public function normalize(array $raw): array
    {
        $html = $this->sanitizeHtml((string) ($raw['html'] ?? ''));
        $css = $this->sanitizeCss((string) ($raw['css'] ?? ''));
        $js = $this->sanitizeJs((string) ($raw['js'] ?? ''));

        if (trim($html) === '' && trim($css) === '' && trim($js) === '') {
            throw new \InvalidArgumentException('الحزمة فارغة: يلزم html أو css أو js.');
        }

        if ($this->looksTruncatedJs($js)) {
            throw new \InvalidArgumentException(
                'يبدو أن سكربت الحزمة مقطوع (رد AI غير مكتمل). أعد التوليد أو التحسين بنموذج أعلى tokens / عدد أسئلة أقل.'
            );
        }

        if (! $this->containsResultPostMessage($js) && ! $this->containsResultPostMessage($html)) {
            // Prepend bridge so a broken/incomplete user script cannot glue onto it.
            $js = $this->resultBridgeSnippet()."\n\n".$this->safeTerminateJs($js);
        } else {
            $js = $this->safeTerminateJs($js);
        }

        // Wire common inline onclick handlers that AI emits but may miss in JS.
        $js = $this->ensureGlobalClickFallbacks($html, $js);

        $title = trim((string) ($raw['title'] ?? ''));
        if ($title === '') {
            $title = 'اختبار تفاعلي';
        }

        $answerKey = $raw['answer_key'] ?? null;
        if (! is_array($answerKey)) {
            $answerKey = null;
        }

        return [
            'title' => mb_substr($title, 0, 255),
            'html' => $html,
            'css' => $css,
            'js' => $js,
            'summary' => trim((string) ($raw['summary'] ?? '')),
            'answer_key' => $answerKey,
        ];
    }

    public function sanitizeHtml(string $html): string
    {
        $html = preg_replace(
            '/<script\b[^>]*\bsrc\s*=\s*([\'"])\s*(?:https?:)?\/\/[^\'"]*\1[^>]*>\s*<\/script>/iu',
            '',
            $html
        ) ?? $html;

        $html = preg_replace(
            '/<link\b[^>]*\bhref\s*=\s*([\'"])\s*(?:https?:)?\/\/[^\'"]*\1[^>]*>/iu',
            '',
            $html
        ) ?? $html;

        $html = preg_replace('/<(iframe|object|embed)\b[^>]*>.*?<\/\1>/isu', '', $html) ?? $html;
        $html = preg_replace('/<(iframe|object|embed)\b[^>]*\/?>/iu', '', $html) ?? $html;

        return trim($html);
    }

    public function sanitizeCss(string $css): string
    {
        $css = preg_replace(
            '/@import\s+(?:url\s*\(\s*)?[\'"]?\s*(?:https?:)?\/\/[^;]+;?/iu',
            '',
            $css
        ) ?? $css;

        return trim($css);
    }

    public function sanitizeJs(string $js): string
    {
        return trim($js);
    }

    public function containsResultPostMessage(string $code): bool
    {
        return str_contains($code, self::RESULT_MESSAGE_TYPE)
            && (str_contains($code, 'postMessage') || str_contains($code, 'parent.postMessage'));
    }

    /**
     * Heuristic: AI often cuts mid-expression when max_tokens is hit.
     */
    public function looksTruncatedJs(string $js): bool
    {
        $js = trim($js);
        if ($js === '') {
            return false;
        }

        // Ends with binary/unary operator or open punctuation that can't finish a program.
        if (preg_match('/(?:[+\-*\/%=<>!&|,?:.]|\b(?:function|return|const|let|var|if|else|for|while|new|typeof|await|class)\b)\s*$/u', $js)) {
            return true;
        }

        $opens = substr_count($js, '{') + substr_count($js, '(') + substr_count($js, '[');
        $closes = substr_count($js, '}') + substr_count($js, ')') + substr_count($js, ']');
        if ($opens > $closes + 1) {
            return true;
        }

        return false;
    }

    public function safeTerminateJs(string $js): string
    {
        $js = rtrim($js);
        if ($js === '') {
            return $js;
        }

        // Avoid gluing next statements onto an unfinished expression.
        $last = substr($js, -1);
        if (! in_array($last, [';', '}', ')', ']', '`', '"', "'", '/'], true) && ! preg_match('/\n\s*\/\/.*$/', $js)) {
            // leave as-is; truncation detector should catch bad cases
        }

        return $js."\n";
    }

    /**
     * If HTML uses onclick="startQuiz()" etc. but functions are missing, attach DOMContentLoaded binders
     * only when those globals are absent — runtime check inside injected snippet.
     */
    public function ensureGlobalClickFallbacks(string $html, string $js): string
    {
        $needed = [];
        if (preg_match('/onclick\s*=\s*([\'"])\s*startQuiz\s*\(/i', $html) && ! preg_match('/function\s+startQuiz\b|startQuiz\s*=\s*function|window\.startQuiz\s*=/', $js)) {
            $needed[] = 'startQuiz';
        }
        if (preg_match('/onclick\s*=\s*([\'"])\s*checkAnswer\s*\(/i', $html) && ! preg_match('/function\s+checkAnswer\b|checkAnswer\s*=\s*function|window\.checkAnswer\s*=/', $js)) {
            $needed[] = 'checkAnswer';
        }
        if (preg_match('/onclick\s*=\s*([\'"])\s*nextQuestion\s*\(/i', $html) && ! preg_match('/function\s+nextQuestion\b|nextQuestion\s*=\s*function|window\.nextQuestion\s*=/', $js)) {
            $needed[] = 'nextQuestion';
        }
        if (preg_match('/onclick\s*=\s*([\'"])\s*restartQuiz\s*\(/i', $html) && ! preg_match('/function\s+restartQuiz\b|restartQuiz\s*=\s*function|window\.restartQuiz\s*=/', $js)) {
            $needed[] = 'restartQuiz';
        }

        if ($needed === []) {
            return $js;
        }

        // Missing critical handlers usually means truncated AI output — fail loudly.
        throw new \InvalidArgumentException(
            'الحزمة تستدعي دوالاً غير موجودة في JS ('.implode(', ', $needed).'). غالباً السكربت مقطوع — أعد التوليد.'
        );
    }

    public function resultBridgeSnippet(): string
    {
        return <<<'JS'
/* ile-html-quiz result bridge (injected if missing) */
(function () {
  if (window.__ileHtmlQuizBridgeInstalled) return;
  window.__ileHtmlQuizBridgeInstalled = true;
  window.ileHtmlQuizSubmit = function (payload) {
    var data = payload && typeof payload === 'object' ? payload : {};
    window.parent.postMessage({
      type: 'ile-html-quiz-result',
      payload: {
        score: Number(data.score) || 0,
        total: Number(data.total) || 0,
        percentage: Number(data.percentage) || 0,
        answers: Array.isArray(data.answers) ? data.answers : [],
        durationSeconds: Number(data.durationSeconds || data.duration) || 0
      }
    }, '*');
  };
})();
JS;
    }

    public function hasDisallowedExternalScripts(string $html, string $js = ''): bool
    {
        $combined = $html."\n".$js;
        if (preg_match('/<script\b[^>]*\bsrc\s*=\s*[\'"]\s*(?:https?:)?\/\//iu', $combined)) {
            return true;
        }
        if (preg_match('/import\s*\(\s*[\'"]https?:\/\//iu', $combined)) {
            return true;
        }

        return false;
    }
}
