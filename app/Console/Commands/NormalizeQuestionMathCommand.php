<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeQuestionMathCommand extends Command
{
    protected $signature = 'questions:normalize-math
                            {--dry-run : عرض التغييرات دون حفظ}
                            {--chunk=200 : حجم الدفعة}';

    protected $description = 'ترحيل نصوص الأسئلة والخيارات إلى LaTeX موحّد ($...$) عبر QuestionMarkupFormatter';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(50, (int) $this->option('chunk'));

        $this->info($dryRun
            ? 'وضع تجريبي (--dry-run): لن يتم حفظ أي تغيير.'
            : 'بدء ترحيل المعادلات وحفظ التغييرات...');

        $questionUpdated = 0;
        $questionScanned = 0;
        $optionUpdated = 0;
        $optionScanned = 0;

        Question::query()
            ->orderBy('id')
            ->chunkById($chunk, function ($questions) use ($dryRun, &$questionUpdated, &$questionScanned) {
                foreach ($questions as $question) {
                    $questionScanned++;
                    $changes = [];

                    foreach (['title', 'content', 'explanation'] as $field) {
                        $original = $question->{$field};
                        if (! is_string($original) || trim($original) === '') {
                            continue;
                        }
                        $normalized = QuestionMarkupFormatter::deepNormalizeForStorage($original);
                        if ($normalized !== $original) {
                            $changes[$field] = $normalized;
                        }
                    }

                    if (is_array($question->blank_answers) && $question->blank_answers !== []) {
                        $normalizedBlanks = array_map(
                            fn ($answer) => is_string($answer)
                                ? QuestionMarkupFormatter::deepNormalizeForStorage($answer)
                                : $answer,
                            $question->blank_answers
                        );
                        if ($normalizedBlanks !== $question->blank_answers) {
                            $changes['blank_answers'] = $normalizedBlanks;
                        }
                    }

                    if ($changes === []) {
                        continue;
                    }

                    $questionUpdated++;
                    if ($dryRun) {
                        $this->line("Question #{$question->id}: ".implode(', ', array_keys($changes)));
                    } else {
                        $question->fill($changes);
                        $question->save();
                    }
                }
            });

        QuestionOption::query()
            ->orderBy('id')
            ->chunkById($chunk, function ($options) use ($dryRun, &$optionUpdated, &$optionScanned) {
                foreach ($options as $option) {
                    $optionScanned++;
                    $changes = [];

                    foreach (['content', 'match_target', 'feedback'] as $field) {
                        $original = $option->{$field};
                        if (! is_string($original) || trim($original) === '') {
                            continue;
                        }
                        $normalized = QuestionMarkupFormatter::deepNormalizeForStorage($original);
                        if ($normalized !== $original) {
                            $changes[$field] = $normalized;
                        }
                    }

                    if ($changes === []) {
                        continue;
                    }

                    $optionUpdated++;
                    if ($dryRun) {
                        $this->line("Option #{$option->id}: ".implode(', ', array_keys($changes)));
                    } else {
                        $option->fill($changes);
                        $option->save();
                    }
                }
            });

        $this->newLine();
        $this->table(
            ['الكيان', 'تم الفحص', 'يحتاج تحديث'],
            [
                ['questions', $questionScanned, $questionUpdated],
                ['question_options', $optionScanned, $optionUpdated],
            ]
        );

        if ($dryRun) {
            $this->comment('أعد التشغيل بدون --dry-run لتطبيق التغييرات.');
        } else {
            $this->info('اكتمل الترحيل.');
            DB::connection()->getPdo(); // touch connection for any delayed writes
        }

        return self::SUCCESS;
    }
}
