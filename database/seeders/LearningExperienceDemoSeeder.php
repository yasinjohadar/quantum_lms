<?php

namespace Database\Seeders;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Services\SchemaValidator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LearningExperienceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@admin.com')->first()
            ?? User::query()->orderBy('id')->first();

        $title = 'تجربة تجريبية — كل الأنواع الخمسة';

        LearningExperience::query()
            ->where('title', $title)
            ->delete();

        $validator = app(SchemaValidator::class);
        $schema = $validator->emptySchema($title);
        $schema['meta']['title'] = $title;
        $schema['rules'] = [
            'allowBack' => true,
            'shuffleQuestions' => false,
            'maxWrong' => null,
            'showExplanation' => true,
            'attemptsPerQuestion' => 1,
            'timerSeconds' => null,
        ];
        $schema['questions'] = [
            $this->trueFalse(),
            $this->singleChoice(),
            $this->multipleChoice(),
            $this->dragDrop(),
            $this->matching(),
        ];

        $result = $validator->validate($schema);
        if (! $result['valid']) {
            throw new \RuntimeException('Demo schema invalid: '.implode(' | ', $result['errors']));
        }

        $experience = LearningExperience::create([
            'title' => $title,
            'description' => 'تجربة جاهزة للمعاينة تشمل: صح/خطأ، اختيار واحد، اختيار متعدد، سحب وإفلات، ومطابقة.',
            'status' => LearningExperience::STATUS_PUBLISHED,
            'schema_json' => $schema,
            'schema_version' => SchemaValidator::SCHEMA_VERSION,
            'engine_version' => SchemaValidator::ENGINE_VERSION,
            'created_by' => $admin?->id,
        ]);

        $this->command?->info('Learning experience created: id='.$experience->id);
        $this->command?->info('Play: /learning-experiences/'.$experience->id);
        $this->command?->info('Admin: /admin/learning-experiences/'.$experience->id.'/edit');
    }

    /**
     * @return array<string, mixed>
     */
    protected function trueFalse(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'true_false',
            'stem' => 'الشمس نجم يشعّ الضوء والحرارة.',
            'points' => 1,
            'difficulty' => 'easy',
            'hints' => ['فكّر في طبيعة الشمس الفلكية'],
            'explanation' => 'الشمس نجم متوسط الحجم في مجرة درب التبانة.',
            'successMessage' => 'أحسنت! إجابة صحيحة.',
            'errorMessage' => 'راجع تعريف النجم ثم حاول مجدداً.',
            'estimatedSeconds' => 20,
            'tags' => ['علوم', 'فلك'],
            'learningObjectives' => ['تمييز النجم عن الكوكب'],
            'payload' => ['correct' => true],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function singleChoice(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'single_choice',
            'stem' => 'ما عاصمة المملكة العربية السعودية؟',
            'points' => 2,
            'difficulty' => 'easy',
            'hints' => ['ليست جدة ولا الدمام'],
            'explanation' => 'الرياض هي العاصمة السياسية للمملكة.',
            'successMessage' => 'ممتاز!',
            'errorMessage' => 'ليست الإجابة الصحيحة.',
            'estimatedSeconds' => 25,
            'tags' => ['جغرافيا'],
            'learningObjectives' => ['معرفة عواصم الدول'],
            'payload' => [
                'options' => [
                    ['id' => 'a', 'label' => 'جدة'],
                    ['id' => 'b', 'label' => 'الرياض'],
                    ['id' => 'c', 'label' => 'الدمام'],
                    ['id' => 'd', 'label' => 'مكة المكرمة'],
                ],
                'correctId' => 'b',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function multipleChoice(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'multiple_choice',
            'stem' => 'أيّ مما يلي من ألوان الطيف الأساسية؟ (يمكن اختيار أكثر من إجابة)',
            'points' => 3,
            'difficulty' => 'medium',
            'hints' => ['فكّر في أحمر وأخضر وأزرق'],
            'explanation' => 'الأحمر والأخضر والأزرق من المكوّنات الأساسية في نموذج RGB.',
            'successMessage' => 'رائع! اخترت الإجابات الصحيحة.',
            'errorMessage' => 'هناك خيارات ناقصة أو زائدة.',
            'estimatedSeconds' => 40,
            'tags' => ['فيزياء', 'ضوء'],
            'learningObjectives' => ['فهم مكوّنات الضوء'],
            'payload' => [
                'options' => [
                    ['id' => 'a', 'label' => 'أحمر'],
                    ['id' => 'b', 'label' => 'أخضر'],
                    ['id' => 'c', 'label' => 'أسود'],
                    ['id' => 'd', 'label' => 'أزرق'],
                ],
                'correctIds' => ['a', 'b', 'd'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dragDrop(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'drag_drop',
            'stem' => 'ضع كل عنصر في مجموعته الصحيحة.',
            'points' => 3,
            'difficulty' => 'medium',
            'hints' => ['التفاح والموز فواكه، والجزر خيار خضار'],
            'explanation' => 'الفواكه: تفاح وموز. الخضار: جزر وخيار.',
            'successMessage' => 'تصنيف ممتاز!',
            'errorMessage' => 'راجع تصنيف الفواكه والخضار.',
            'estimatedSeconds' => 45,
            'tags' => ['تصنيف'],
            'learningObjectives' => ['تصنيف عناصر يومية'],
            'payload' => [
                'items' => [
                    ['id' => 'i1', 'label' => 'تفاح'],
                    ['id' => 'i2', 'label' => 'جزر'],
                    ['id' => 'i3', 'label' => 'موز'],
                    ['id' => 'i4', 'label' => 'خيار'],
                ],
                'zones' => [
                    ['id' => 'z1', 'label' => 'فواكه'],
                    ['id' => 'z2', 'label' => 'خضار'],
                ],
                'assignments' => [
                    'i1' => 'z1',
                    'i2' => 'z2',
                    'i3' => 'z1',
                    'i4' => 'z2',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function matching(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'matching',
            'stem' => 'طابق كل حيوان بصوته.',
            'points' => 3,
            'difficulty' => 'easy',
            'hints' => ['القطة تموء والكلب ينبح'],
            'explanation' => 'قطة↔مواء، كلب↔نباح، بقرة↔خوار.',
            'successMessage' => 'مطابقة صحيحة!',
            'errorMessage' => 'أعد مطابقة الأصوات.',
            'estimatedSeconds' => 40,
            'tags' => ['لغة', 'حيوانات'],
            'learningObjectives' => ['ربط المفاهيم'],
            'payload' => [
                'left' => [
                    ['id' => 'l1', 'label' => 'قطة'],
                    ['id' => 'l2', 'label' => 'كلب'],
                    ['id' => 'l3', 'label' => 'بقرة'],
                ],
                'right' => [
                    ['id' => 'r1', 'label' => 'مواء'],
                    ['id' => 'r2', 'label' => 'نباح'],
                    ['id' => 'r3', 'label' => 'خوار'],
                ],
                'pairs' => [
                    'l1' => 'r1',
                    'l2' => 'r2',
                    'l3' => 'r3',
                ],
            ],
        ];
    }
}
