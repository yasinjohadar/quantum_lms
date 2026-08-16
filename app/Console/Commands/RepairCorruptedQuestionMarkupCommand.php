<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\Storage\MediaStorageService;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Console\Command;

/**
 * يُصلح حقول الأسئلة/الخيارات التي أفسدها كاشف "شبه-الرياضيات" القديم في
 * QuestionMarkupFormatter عندما كانت تحتوي وسم HTML (غالباً <img>) بقيمة خاصية
 * تبدو "حسابية" (رابط تخزين سحابي طويل)، فحوّل < و> الفعليين إلى نص \lt/\gt خامل
 * ودمّر الوسم قبل الحفظ.
 *
 * يتطلب أن يكون إصلاح normalizeForRender() (حماية وسوم HTML من كاشف الرياضيات)
 * منشوراً بالفعل قبل استخدام --apply، وإلا فستُفسِد إعادة التطبيع الصف من جديد فوراً.
 */
class RepairCorruptedQuestionMarkupCommand extends Command
{
    protected $signature = 'questions:repair-corrupted-markup
                            {--apply : تطبيق الإصلاح فعلياً (الوضع الافتراضي تقرير فقط بلا كتابة)}
                            {--chunk=200 : حجم الدفعة}
                            {--fields= : قائمة حقول مفصولة بفواصل لتقييد الفحص (مثال: title,content)}';

    protected $description = 'إصلاح حقول الأسئلة/الخيارات التي أفسدها كاشف شبه-الرياضيات القديم (وسوم <img> تحوّلت لنص \\lt/\\gt خامل)';

    /** أسماء وسوم HTML معروفة — وجود \lt/\gt ملاصقة لها هو توقيع الفساد (وليس متباينة رياضية حقيقية) */
    private const CORRUPTED_TAG_SIGNATURE = '/\\\\(?:lt|gt)\s*\/?\s*(?:img|p|div|span|a|table|thead|tbody|tr|td|th|ul|ol|li|br|strong|b|em|i|u|h[1-6]|figure|figcaption|source|video|audio|iframe|blockquote|pre|code|hr)\b/iu';

    private const TAG_NAMES = ['img', 'p', 'div', 'span', 'a', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'ul', 'ol', 'li', 'br', 'strong', 'b', 'em', 'i', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'figure', 'figcaption', 'source', 'video', 'audio', 'iframe', 'blockquote', 'pre', 'code', 'hr'];

    private int $scanned = 0;

    private int $repaired = 0;

    private int $needsReview = 0;

    private int $clean = 0;

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $chunk = max(50, (int) $this->option('chunk'));
        $fieldsOption = trim((string) $this->option('fields'));
        $onlyFields = $fieldsOption !== '' ? array_map('trim', explode(',', $fieldsOption)) : null;

        $this->info($apply
            ? 'تطبيق الإصلاح فعلياً — سيتم حفظ التغييرات.'
            : 'وضع تجريبي (--dry-run الافتراضي): لن يتم حفظ أي تغيير.');

        $questionFields = $onlyFields ? array_intersect(['title', 'content', 'explanation'], $onlyFields) : ['title', 'content', 'explanation'];
        $optionFields = $onlyFields ? array_intersect(['content', 'match_target', 'feedback'], $onlyFields) : ['content', 'match_target', 'feedback'];

        $needsReviewRows = [];

        Question::query()->orderBy('id')->chunkById($chunk, function ($questions) use ($apply, $questionFields, &$needsReviewRows) {
            foreach ($questions as $question) {
                $changes = [];

                foreach ($questionFields as $field) {
                    $result = $this->repairField((string) ($question->{$field} ?? ''));
                    if ($result === null) {
                        continue;
                    }
                    if ($result['status'] === 'repaired') {
                        $changes[$field] = $result['value'];
                    } else {
                        $needsReviewRows[] = ['Question', $question->id, $field, $result['reason']];
                    }
                }

                if (is_array($question->blank_answers)) {
                    $blanksChanged = false;
                    $newBlanks = $question->blank_answers;
                    foreach ($newBlanks as $i => $answer) {
                        if (! is_string($answer)) {
                            continue;
                        }
                        $result = $this->repairField($answer);
                        if ($result === null) {
                            continue;
                        }
                        if ($result['status'] === 'repaired') {
                            $newBlanks[$i] = $result['value'];
                            $blanksChanged = true;
                        } else {
                            $needsReviewRows[] = ['Question', $question->id, "blank_answers[{$i}]", $result['reason']];
                        }
                    }
                    if ($blanksChanged) {
                        $changes['blank_answers'] = $newBlanks;
                    }
                }

                if ($changes === []) {
                    continue;
                }

                $this->repaired++;
                $this->line("Question #{$question->id}: ".implode(', ', array_keys($changes)));

                if ($apply) {
                    $question->fill($changes);
                    $question->save();
                }
            }
        });

        QuestionOption::query()->orderBy('id')->chunkById($chunk, function ($options) use ($apply, $optionFields, &$needsReviewRows) {
            foreach ($options as $option) {
                $changes = [];

                foreach ($optionFields as $field) {
                    $result = $this->repairField((string) ($option->{$field} ?? ''));
                    if ($result === null) {
                        continue;
                    }
                    if ($result['status'] === 'repaired') {
                        $changes[$field] = $result['value'];
                    } else {
                        $needsReviewRows[] = ['QuestionOption', $option->id, $field, $result['reason']];
                    }
                }

                if ($changes === []) {
                    continue;
                }

                $this->repaired++;
                $this->line("Option #{$option->id}: ".implode(', ', array_keys($changes)));

                if ($apply) {
                    $option->fill($changes);
                    $option->save();
                }
            }
        });

        $this->newLine();
        $this->table(
            ['الحالة', 'العدد'],
            [
                ['تم فحصها', $this->scanned],
                ['نظيفة (لا فساد)', $this->clean],
                ['أُصلحت'.($apply ? '' : ' (ستُصلَح مع --apply)'), $this->repaired],
                ['تحتاج مراجعة يدوية', $this->needsReview],
            ]
        );

        if ($needsReviewRows !== []) {
            $this->newLine();
            $this->warn('صفوف تحتاج مراجعة يدوية (لم تُكتَب):');
            $this->table(['الكيان', 'المعرف', 'الحقل', 'السبب'], $needsReviewRows);
        }

        if (! $apply) {
            $this->comment('أعد التشغيل مع --apply لتطبيق الإصلاح فعلياً (بعد التأكد من نشر إصلاح QuestionMarkupFormatter).');
        } else {
            $this->info('اكتمل الإصلاح.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{status: 'repaired', value: string}|array{status: 'needs_review', reason: string}|null
     */
    private function repairField(string $original): ?array
    {
        if (trim($original) === '') {
            return null;
        }

        $this->scanned++;

        if (! preg_match(self::CORRUPTED_TAG_SIGNATURE, $original)) {
            $this->clean++;

            return null;
        }

        $stripped = QuestionMarkupFormatter::stripMathDelimiters($original);
        $reconstructed = preg_replace(['/\\\\lt\s*/i', '/\\\\gt\s*/i'], ['<', '>'], $stripped) ?? $stripped;

        if (! $this->tagCountIncreased($original, $reconstructed)) {
            $this->needsReview++;

            return ['status' => 'needs_review', 'reason' => 'توقيع الفساد مطابق لكن لا يوجد وسم HTML فعلي بعد إعادة البناء (تطابق زائف)'];
        }

        $reconstructed = $this->rebakeImageSources($reconstructed);

        $repaired = QuestionMarkupFormatter::deepNormalizeForStorage($reconstructed);

        if (preg_match(self::CORRUPTED_TAG_SIGNATURE, $repaired)) {
            $this->needsReview++;

            return ['status' => 'needs_review', 'reason' => 'النتيجة بعد إعادة التطبيع لا تزال تطابق توقيع الفساد (على الأرجح إصلاح QuestionMarkupFormatter لم يُنشَر بعد)'];
        }

        if (! $this->tagCountsMatch($reconstructed, $repaired)) {
            $this->needsReview++;

            return ['status' => 'needs_review', 'reason' => 'عدد وسوم HTML تغيّر بين إعادة البناء وإعادة التطبيع'];
        }

        // شبكة أمان أخيرة: لا نكتب أبداً نتيجة فارغة/بيضاء طالما القيمة الأصلية لم تكن كذلك،
        // حتى لو اجتازت الفحوصات السابقة بطريقة غير متوقعة — فقدان المحتوى أسوأ من تركه فاسداً.
        if (trim($repaired) === '') {
            $this->needsReview++;

            return ['status' => 'needs_review', 'reason' => 'النتيجة النهائية فارغة تماماً بعد إعادة التطبيع رغم أن الأصل لم يكن فارغاً — لن تُكتَب'];
        }

        return ['status' => 'repaired', 'value' => $repaired];
    }

    private function tagCountIncreased(string $before, string $after): bool
    {
        foreach (self::TAG_NAMES as $tag) {
            $beforeCount = substr_count(strtolower($before), '<'.$tag);
            $afterCount = substr_count(strtolower($after), '<'.$tag);
            if ($afterCount > $beforeCount) {
                return true;
            }
        }

        return false;
    }

    private function tagCountsMatch(string $a, string $b): bool
    {
        foreach (self::TAG_NAMES as $tag) {
            if (substr_count(strtolower($a), '<'.$tag) !== substr_count(strtolower($b), '<'.$tag)) {
                return false;
            }
        }

        return true;
    }

    /**
     * يستبدل src أي <img> يشير لملف موجود فعلاً على تخزيننا برابط تحويل ثابت
     * (/media/...) بدل الرابط السحابي المُوقَّع المنتهي صلاحيته، حتى تظهر الصورة
     * فوراً دون انتظار 7 أيام أخرى أو تعديل يدوي.
     */
    private function rebakeImageSources(string $html): string
    {
        return (string) preg_replace_callback(
            '#<img\b([^>]*?)\bsrc\s*=\s*("|\')([^"\']*)\2([^>]*)>#is',
            function (array $m): string {
                $before = $m[1];
                $q = $m[2];
                $src = html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $after = $m[4];

                $path = ltrim((string) parse_url($src, PHP_URL_PATH), '/');

                if ($path !== '' && MediaStorageService::exists($path)) {
                    $src = Question::absoluteImageUrlForDisplay($path);
                }

                return '<img'.$before.' src='.$q.$src.$q.$after.'>';
            },
            $html
        ) ?? $html;
    }
}
