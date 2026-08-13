<?php

namespace App\Console\Commands;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Support\FeedbackPhrases;
use Illuminate\Console\Command;

class NormalizeIleFeedbackPhrasesCommand extends Command
{
    protected $signature = 'ile:normalize-feedback-phrases
                            {--dry-run : عرض التغييرات دون حفظ}
                            {--chunk=100 : حجم الدفعة}
                            {--check-files : التحقق من وجود ملفات الصوت العشرين فقط}';

    protected $description = 'ضبط رسائل النجاح/الخطأ في التجارب التفاعلية على العبارات التي لها تسجيل صوتي مطابق';

    public function handle(): int
    {
        if ($this->option('check-files')) {
            return $this->checkFiles();
        }

        if ($this->checkFiles() !== self::SUCCESS) {
            $this->error('توقف الترحيل: ملفات الصوت غير مكتملة.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(10, (int) $this->option('chunk'));

        $this->newLine();
        $this->info($dryRun
            ? 'وضع تجريبي (--dry-run): لن يتم حفظ أي تغيير.'
            : 'بدء ضبط رسائل التغذية الراجعة وحفظ التغييرات...');

        $scanned = 0;
        $experiencesUpdated = 0;
        $messagesChanged = 0;

        LearningExperience::query()
            ->orderBy('id')
            ->chunkById($chunk, function ($experiences) use ($dryRun, &$scanned, &$experiencesUpdated, &$messagesChanged) {
                foreach ($experiences as $experience) {
                    $scanned++;

                    $schema = $experience->schema_json;
                    if (! is_array($schema)) {
                        continue;
                    }

                    $result = FeedbackPhrases::snapSchema($schema);
                    if ($result['changed'] === 0) {
                        continue;
                    }

                    $experiencesUpdated++;
                    $messagesChanged += $result['changed'];

                    $this->line(sprintf(
                        '  #%d %s — %d رسالة',
                        $experience->id,
                        mb_strimwidth((string) $experience->title, 0, 40, '…'),
                        $result['changed']
                    ));

                    if (! $dryRun) {
                        $experience->schema_json = $result['schema'];
                        $experience->save();
                    }
                }
            });

        $this->newLine();
        $this->table(
            ['تجارب مفحوصة', 'تجارب معدّلة', 'رسائل مضبوطة'],
            [[$scanned, $experiencesUpdated, $messagesChanged]]
        );

        if ($dryRun && $experiencesUpdated > 0) {
            $this->warn('أعد التشغيل بدون --dry-run لحفظ التغييرات.');
        } elseif ($experiencesUpdated === 0) {
            $this->info('كل الرسائل مضبوطة أصلاً — لا حاجة لأي تغيير.');
        } else {
            $this->info('تم الحفظ بنجاح.');
        }

        return self::SUCCESS;
    }

    /**
     * التأكد من أن كل عبارة في FeedbackPhrases لها ملف صوتي موجود فعلاً على القرص.
     */
    protected function checkFiles(): int
    {
        $missing = [];
        $total = 0;

        foreach (FeedbackPhrases::all() as $kind => $rows) {
            foreach ($rows as $row) {
                $total++;
                if (! is_file(public_path('sounds/'.$kind.'/'.$row['file']))) {
                    $missing[] = $kind.' :: '.$row['text'].' → '.$row['file'];
                }
            }
        }

        if ($missing !== []) {
            $this->error('ملفات صوت مفقودة ('.count($missing).' من '.$total.'):');
            foreach ($missing as $line) {
                $this->line('  - '.$line);
            }

            return self::FAILURE;
        }

        $this->info("ملفات الصوت مكتملة: {$total}/{$total}");

        return self::SUCCESS;
    }
}
