<?php

namespace App\InteractiveLearning\Services;

class ContentLogicChecker
{
    /**
     * @param  array<string, mixed>  $question
     * @return array{ok: bool, errors: list<string>, question: array<string, mixed>}
     */
    public function checkAndFix(array $question, string $mode = 'classic'): array
    {
        $errors = [];
        $fixed = $mode === 'dynamic'
            ? $this->fixDynamic($question, $errors)
            : $this->fixClassic($question, $errors);

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'question' => $fixed,
        ];
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  list<string>  $errors
     * @return array<string, mixed>
     */
    protected function fixClassic(array $question, array &$errors): array
    {
        $payload = is_array($question['payload'] ?? null) ? $question['payload'] : [];
        if (isset($payload['options']) && is_array($payload['options'])) {
            foreach ($payload['options'] as $i => $opt) {
                if (! is_array($opt)) {
                    continue;
                }
                $payload['options'][$i] = $this->sanitizeIconFields($opt, $errors, 'خيار');
            }
        }
        $question['payload'] = $payload;

        $stem = (string) ($question['stem'] ?? '');
        if ($this->looksLikeCounting($stem)) {
            $errors[] = 'سؤال عدّ كلاسيك بدون مشهد scene — فضّل الوضع الديناميك أو أضف صورة واضحة.';
        }

        return $question;
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  list<string>  $errors
     * @return array<string, mixed>
     */
    protected function fixDynamic(array $question, array &$errors): array
    {
        $blocks = is_array($question['stemBlocks'] ?? null) ? $question['stemBlocks'] : [];
        $normalizedBlocks = [];
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            if (! in_array($type, SchemaValidator::ALLOWED_BLOCKS, true)) {
                $errors[] = "كتلة غير معتمدة تم تجاهلها: {$type}";
                continue;
            }
            if ($type === 'icon' || $type === 'sticker') {
                $name = trim((string) ($block['name'] ?? ''));
                if ($this->isEmojiPile($name)) {
                    $block['name'] = $this->firstEmojiOrToken($name);
                    $errors[] = 'تم تقليص أيقونة متعددة إلى واحدة.';
                }
            }
            if ($type === 'scene') {
                $block['count'] = max(0, (int) ($block['count'] ?? 0));
                $block['item'] = $this->firstEmojiOrToken((string) ($block['item'] ?? 'apple'));
            }
            $normalizedBlocks[] = $block;
        }
        $question['stemBlocks'] = $normalizedBlocks;

        if ($normalizedBlocks === []) {
            $errors[] = 'stemBlocks فارغة.';
        }

        $interaction = is_array($question['interaction'] ?? null) ? $question['interaction'] : [];
        $payload = is_array($interaction['payload'] ?? null) ? $interaction['payload'] : [];
        if (isset($payload['options']) && is_array($payload['options'])) {
            foreach ($payload['options'] as $i => $opt) {
                if (! is_array($opt)) {
                    continue;
                }
                $payload['options'][$i] = $this->sanitizeIconFields($opt, $errors, 'خيار');
            }
        }
        $interaction['payload'] = $payload;
        $question['interaction'] = $interaction;

        $stemText = $this->blocksToText($normalizedBlocks).' '.(string) ($question['stem'] ?? '');
        $hasScene = $this->hasSceneOrImage($normalizedBlocks);
        if ($this->looksLikeCounting($stemText) && ! $hasScene) {
            // Auto-insert scene from correct numeric option when possible
            $count = $this->correctNumericValue($interaction);
            if ($count !== null) {
                array_unshift($question['stemBlocks'], [
                    'type' => 'scene',
                    'item' => 'apple',
                    'count' => $count,
                    'layout' => 'row',
                ]);
                $errors[] = 'أُضيف مشهد عدّ تلقائياً ليتوافق مع الإجابة.';
            } else {
                $errors[] = 'سؤال عدّ بلا مشهد scene أو صورة.';
            }
        }

        // Align scene count with correct numeric label
        $correctNum = $this->correctNumericValue($interaction);
        if ($correctNum !== null) {
            foreach ($question['stemBlocks'] as $i => $block) {
                if (($block['type'] ?? '') === 'scene' && (int) ($block['count'] ?? -1) !== $correctNum) {
                    $question['stemBlocks'][$i]['count'] = $correctNum;
                    $errors[] = 'تم ضبط scene.count ليطابق الإجابة الصحيحة.';
                }
            }
        }

        // Strip unknown libraries
        if (isset($question['assets']['libraries']) && is_array($question['assets']['libraries'])) {
            $question['assets']['libraries'] = array_values(array_filter(
                $question['assets']['libraries'],
                fn ($lib) => is_string($lib) && in_array($lib, SchemaValidator::ALLOWED_LIBRARIES, true)
            ));
        }

        // Soft errors that were auto-fixed should not fail the question if structure is ok
        $hard = array_values(array_filter($errors, function (string $e) {
            return str_contains($e, 'فارغة')
                || str_contains($e, 'بلا مشهد')
                || str_contains($e, 'غير معتمدة تم');
        }));

        // Recompute: if we auto-added scene, remove the hard counting error
        if ($this->hasSceneOrImage($question['stemBlocks'] ?? [])) {
            $errors = array_values(array_filter($errors, fn (string $e) => ! str_contains($e, 'بلا مشهد')));
        }

        // Only treat remaining hard structural issues as failures for ok=false
        // Auto-fix messages stay as warnings but ok stays true if stemBlocks non-empty and interaction present
        $fail = ($question['stemBlocks'] ?? []) === []
            || ! is_array($question['interaction'] ?? null)
            || ($this->looksLikeCounting($stemText) && ! $this->hasSceneOrImage($question['stemBlocks'] ?? []));

        if (! $fail) {
            // Keep informational messages but mark ok
            return $question;
        }

        return $question;
    }

    /**
     * Recalculate ok after fix — public helper for callers that only want hard failures.
     *
     * @param  array{ok: bool, errors: list<string>, question: array<string, mixed>}  $result
     * @return array{ok: bool, errors: list<string>, question: array<string, mixed>, warnings: list<string>}
     */
    public function partition(array $result): array
    {
        $warnings = [];
        $errors = [];
        foreach ($result['errors'] as $msg) {
            if (
                str_contains($msg, 'تقليص')
                || str_contains($msg, 'قُلّصت')
                || str_contains($msg, 'أُضيف')
                || str_contains($msg, 'تم ضبط')
                || str_contains($msg, 'تم تجاهلها')
                || str_contains($msg, 'فضّل الوضع')
            ) {
                $warnings[] = $msg;
            } else {
                $errors[] = $msg;
            }
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
            'question' => $result['question'],
        ];
    }

    /**
     * @param  array<string, mixed>  $opt
     * @param  list<string>  $errors
     * @return array<string, mixed>
     */
    protected function sanitizeIconFields(array $opt, array &$errors, string $label): array
    {
        if (isset($opt['icon']) && is_string($opt['icon']) && $this->isEmojiPile($opt['icon'])) {
            $opt['icon'] = $this->firstEmojiOrToken($opt['icon']);
            $errors[] = "{$label}: أيقونة متعددة قُلّصت إلى واحدة.";
        }
        if (isset($opt['sticker']) && is_string($opt['sticker']) && $this->isEmojiPile($opt['sticker'])) {
            $opt['sticker'] = $this->firstEmojiOrToken($opt['sticker']);
        }

        return $opt;
    }

    public function looksLikeCounting(string $text): bool
    {
        $t = mb_strtolower($text);

        return (bool) preg_match('/كم\s*عدد|عد[ّد]|يراها|يراه|احسب|عدد\s+ال/u', $t);
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    protected function hasSceneOrImage(array $blocks): bool
    {
        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'scene' || $type === 'image') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    protected function blocksToText(array $blocks): string
    {
        $parts = [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'text') {
                $parts[] = (string) ($block['text'] ?? '');
            }
        }

        return implode(' ', $parts);
    }

    /**
     * @param  array<string, mixed>  $interaction
     */
    protected function correctNumericValue(array $interaction): ?int
    {
        $type = (string) ($interaction['type'] ?? '');
        $payload = is_array($interaction['payload'] ?? null) ? $interaction['payload'] : [];

        if ($type === 'numerical' && is_numeric($payload['correct'] ?? null)) {
            return (int) $payload['correct'];
        }

        if (in_array($type, ['single_choice', 'listen_choose'], true)) {
            $correctId = (string) ($payload['correctId'] ?? '');
            foreach ($payload['options'] ?? [] as $opt) {
                if (! is_array($opt) || (string) ($opt['id'] ?? '') !== $correctId) {
                    continue;
                }
                $label = trim((string) ($opt['label'] ?? ''));
                if (is_numeric($label)) {
                    return (int) $label;
                }
            }
        }

        return null;
    }

    public function isEmojiPile(string $value): bool
    {
        $value = trim($value);
        if ($value === '' || ! is_string($value)) {
            return false;
        }

        if (preg_match_all('/\p{Extended_Pictographic}/u', $value, $m) && count($m[0]) >= 2) {
            return true;
        }

        // Fallback: same character repeated (UTF-8 safe via grapheme if available)
        if (function_exists('grapheme_strlen') && grapheme_strlen($value) >= 2) {
            $first = grapheme_extract($value, 1);
            if ($first !== '' && preg_match('/^(?:'.preg_quote($first, '/').'){2,}$/u', $value)) {
                return true;
            }
        }

        return false;
    }

    public function firstEmojiOrToken(string $value): string
    {
        $value = trim($value);
        if (preg_match('/\p{Extended_Pictographic}/u', $value, $m)) {
            return $m[0];
        }
        if (preg_match('/[a-zA-Z0-9_\-]+/u', $value, $mm)) {
            return $mm[0];
        }
        if (function_exists('grapheme_extract')) {
            $g = grapheme_extract($value, 1);

            return $g !== false ? $g : 'star';
        }

        return mb_substr($value, 0, 1) ?: 'star';
    }
}
