<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * بيانات تجريبية لصفحة "سجل النشاطات" فقط (لمعاينة الشكل والفلترة).
 * لا تُضاف لـ DatabaseSeeder الرئيسي عمداً — تُشغَّل يدوياً:
 *   php artisan db:seed --class=ActivityLogDemoSeeder
 * ولا تُنشئ أي محتوى منهج حقيقي، فقط سطور في جدول audit_logs.
 */
class ActivityLogDemoSeeder extends Seeder
{
    /** @var array<string, array{0: class-string, 1: string}> */
    private const MODEL_MAP = [
        'stage' => [Stage::class, 'name'],
        'class' => [SchoolClass::class, 'name'],
        'subject' => [Subject::class, 'name'],
        'subject_section' => [SubjectSection::class, 'title'],
        'unit' => [Unit::class, 'title'],
        'lesson' => [Lesson::class, 'title'],
        'question' => [Question::class, 'title'],
        'quiz' => [Quiz::class, 'title'],
    ];

    private const TITLE_POOLS = [
        'stage' => ['المرحلة الابتدائية', 'المرحلة الإعدادية', 'المرحلة الثانوية', 'مرحلة التمهيدي'],
        'class' => ['الصف الأول الابتدائي', 'الصف الثالث الإعدادي', 'الصف الثاني الثانوي', 'الصف السادس الابتدائي'],
        'subject' => ['الرياضيات', 'اللغة العربية', 'العلوم', 'اللغة الإنجليزية', 'التربية الإسلامية', 'الفيزياء', 'الكيمياء'],
        'subject_section' => ['الفصل الأول: الأعداد والعمليات', 'الفصل الثاني: الهندسة', 'قسم النحو والصرف', 'قسم القراءة والفهم'],
        'unit' => ['الوحدة الأولى: مقدمة', 'الوحدة الثانية: المعادلات', 'الوحدة الثالثة: التفاعلات الكيميائية', 'الوحدة الرابعة: القواعد النحوية'],
        'lesson' => ['الدرس الأول: التعرف على الأعداد', 'الدرس الثاني: الجمع والطرح', 'درس الإعراب', 'درس قوانين نيوتن', 'درس التفاعلات الكيميائية'],
        'question' => ['سؤال اختيار من متعدد - الأعداد الأولية', 'سؤال صح أو خطأ - قواعد النحو', 'سؤال مقالي - التحليل الكيميائي'],
        'quiz' => ['اختبار الوحدة الأولى', 'اختبار قصير - الفصل الثاني', 'الاختبار النهائي - الفصل الدراسي'],
    ];

    /** أوزان تقريبية: الدروس/الأسئلة/الاختبارات أكثر شيوعاً من مراحل/صفوف */
    private const SUBJECT_WEIGHTS = [
        'stage' => 1, 'class' => 2, 'subject' => 2,
        'subject_section' => 3, 'unit' => 4,
        'lesson' => 8, 'question' => 6, 'quiz' => 4,
    ];

    /** إنشاء/تعديل أكثر شيوعاً من الحذف/الاستعادة */
    private const EVENT_WEIGHTS = ['created' => 4, 'updated' => 5, 'deleted' => 1, 'restored' => 1];

    public function run(AuditLogService $auditLog): void
    {
        $users = User::role(['admin', 'teacher', 'supervisor'])->get();

        if ($users->isEmpty()) {
            $this->command?->warn('لا يوجد مستخدمون بدور admin/teacher/supervisor — تم إلغاء توليد بيانات سجل النشاطات التجريبية.');

            return;
        }

        $subjectKeys = $this->expandWeights(self::SUBJECT_WEIGHTS);
        $events = $this->expandWeights(self::EVENT_WEIGHTS);

        for ($i = 0; $i < 80; $i++) {
            $subjectKey = $subjectKeys[array_rand($subjectKeys)];
            $event = $events[array_rand($events)];
            $user = $users->random();

            [$modelClass, $field] = self::MODEL_MAP[$subjectKey];
            $label = self::TITLE_POOLS[$subjectKey][array_rand(self::TITLE_POOLS[$subjectKey])];

            $model = new $modelClass([$field => $label]);
            $model->setAttribute('id', random_int(1, 500));

            [$old, $new] = $this->buildValues($event, $field, $label);

            $occurredAt = Carbon::now()
                ->subDays(random_int(0, 20))
                ->subMinutes(random_int(0, 1439));

            $log = $auditLog->logModelEvent($user, $event, $model, $subjectKey, $old, $new, $occurredAt);
            $log->update(['metadata' => ['seeded' => true]]);
        }

        $this->command?->info('تم توليد 80 نشاطاً تجريبياً في سجل النشاطات (يمكن حذفها لاحقاً عبر AuditLog::whereJsonContains(\'metadata->seeded\', true)->delete()).');
    }

    /**
     * @return array{0: array, 1: array}
     */
    private function buildValues(string $event, string $field, string $label): array
    {
        return match ($event) {
            'created' => [[], [$field => $label, 'is_active' => true]],
            'updated' => $this->randomUpdateDiff($field, $label),
            default => [[], [$field => $label, 'is_active' => false]], // deleted / restored: لقطة أخيرة
        };
    }

    private function randomUpdateDiff(string $field, string $label): array
    {
        return match (random_int(1, 3)) {
            1 => [[$field => $label . ' (نسخة قديمة)'], [$field => $label]],
            2 => [['is_active' => true], ['is_active' => false]],
            default => [
                ['description' => 'وصف مختصر قديم للعنصر.'],
                ['description' => 'تم تحديث الوصف ليكون أكثر تفصيلاً ووضوحاً للطلاب.'],
            ],
        };
    }

    /**
     * @param array<string, int> $weights
     * @return array<int, string>
     */
    private function expandWeights(array $weights): array
    {
        $expanded = [];
        foreach ($weights as $key => $weight) {
            $expanded = array_merge($expanded, array_fill(0, $weight, $key));
        }

        return $expanded;
    }
}
