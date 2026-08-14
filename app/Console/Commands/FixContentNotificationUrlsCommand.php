<?php

namespace App\Console\Commands;

use App\Models\GamificationNotification;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Services\StudentContentNotificationService;
use Illuminate\Console\Command;

/**
 * تصحيح روابط إشعارات «درس جديد متاح» و«اختبار جديد متاح» القديمة.
 *
 * كانت تشير إلى:
 *  - صفحة الدرس المفردة (student/lessons/{id})
 *  - ومسار بدء الاختبار (student/quizzes/{id}/start) الذي يُنشئ محاولة فوراً
 *
 * والمطلوب أن تفتح صفحة الوحدة داخل المادة:
 *   student/subjects/{subject}/folders/section/{section}/unit/{unit}
 */
class FixContentNotificationUrlsCommand extends Command
{
    protected $signature = 'notifications:fix-content-urls
                            {--dry-run : عرض التغييرات دون حفظ}
                            {--chunk=200 : حجم الدفعة}
                            {--type= : نوع واحد فقط (lesson أو quiz)}';

    protected $description = 'تحويل روابط إشعارات الدروس والاختبارات إلى صفحة الوحدة';

    /** نوع الإشعار ← [مفتاح المعرّف في data، الموديل] */
    private const TYPES = [
        'student_lesson_available' => ['lesson_id', Lesson::class],
        'student_quiz_available' => ['quiz_id', Quiz::class],
    ];

    public function handle(StudentContentNotificationService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(50, (int) $this->option('chunk'));

        $only = (string) ($this->option('type') ?? '');
        $types = match ($only) {
            'lesson' => ['student_lesson_available'],
            'quiz' => ['student_quiz_available'],
            '' => array_keys(self::TYPES),
            default => null,
        };

        if ($types === null) {
            $this->error('--type يقبل lesson أو quiz فقط.');

            return self::FAILURE;
        }

        $this->info($dryRun
            ? 'وضع تجريبي (--dry-run): لن يتم حفظ أي تغيير.'
            : 'بدء تصحيح روابط إشعارات المحتوى...');

        $rows = [];

        foreach ($types as $type) {
            [$idKey, $modelClass] = self::TYPES[$type];
            $scanned = 0;
            $updated = 0;
            $skipped = 0;
            $urlCache = [];

            GamificationNotification::query()
                ->where('type', $type)
                ->orderBy('id')
                ->chunkById($chunk, function ($notifications) use (
                    $service, $dryRun, $idKey, $modelClass, $type,
                    &$scanned, &$updated, &$skipped, &$urlCache
                ) {
                    foreach ($notifications as $notification) {
                        $scanned++;

                        $data = is_array($notification->data) ? $notification->data : [];
                        $modelId = (int) ($data[$idKey] ?? 0);

                        if ($modelId <= 0) {
                            $skipped++;

                            continue;
                        }

                        // نفس الدرس/الاختبار يتكرّر عبر مئات الطلاب — نحسب رابطه مرة واحدة
                        if (! array_key_exists($modelId, $urlCache)) {
                            $urlCache[$modelId] = $this->resolveUrl($service, $modelClass, $type, $modelId);
                        }

                        $newUrl = $urlCache[$modelId];
                        if ($newUrl === null || $newUrl === $notification->action_url) {
                            $skipped++;

                            continue;
                        }

                        $updated++;

                        if (! $dryRun) {
                            $data['url'] = $newUrl;
                            $notification->action_url = $newUrl;
                            $notification->data = $data;
                            $notification->save();
                        }
                    }
                });

            $rows[] = [
                $type === 'student_lesson_available' ? 'دروس' : 'اختبارات',
                $scanned,
                $updated,
                $skipped,
                count($urlCache),
            ];
        }

        $this->newLine();
        $this->table(['النوع', 'مفحوصة', 'محدَّثة', 'متجاوَزة', 'عناصر مميّزة'], $rows);

        $totalUpdated = array_sum(array_column($rows, 2));

        if ($dryRun && $totalUpdated > 0) {
            $this->warn('أعد التشغيل بدون --dry-run لحفظ التغييرات.');
        } elseif ($totalUpdated === 0) {
            $this->info('لا شيء يحتاج تصحيحاً.');
        } else {
            $this->info('تم الحفظ بنجاح.');
        }

        return self::SUCCESS;
    }

    private function resolveUrl(
        StudentContentNotificationService $service,
        string $modelClass,
        string $type,
        int $id
    ): ?string {
        if ($type === 'student_lesson_available') {
            $lesson = $modelClass::with('unit.section.subject', 'section.subject')->find($id);

            return $lesson ? $service->lessonBrowseUrl($lesson) : null;
        }

        $quiz = $modelClass::with('unit.section', 'section', 'subject')->find($id);

        return $quiz ? $service->quizBrowseUrl($quiz) : null;
    }
}
