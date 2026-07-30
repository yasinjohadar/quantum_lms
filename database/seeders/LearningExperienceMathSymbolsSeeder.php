<?php

namespace Database\Seeders;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Services\SchemaValidator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds a dynamic (Schema 2.0) experience packed with KaTeX symbols
 * to visually verify math rendering in stems and option grids.
 *
 * Run: php artisan db:seed --class=LearningExperienceMathSymbolsSeeder
 */
class LearningExperienceMathSymbolsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@admin.com')->first()
            ?? User::query()->orderBy('id')->first();

        $title = 'اختبار رموز الرياضيات — ديناميك';

        LearningExperience::query()
            ->where('title', $title)
            ->delete();

        $validator = app(SchemaValidator::class);
        $schema = $validator->emptySchema($title, 'dynamic');
        $schema['meta']['title'] = $title;
        $schema['assets'] = [
            'libraries' => ['katex', 'icons', 'stickers', 'lottie', 'tts'],
        ];
        $schema['rules'] = [
            'allowBack' => true,
            'shuffleQuestions' => false,
            'maxWrong' => null,
            'showExplanation' => true,
            'attemptsPerQuestion' => 2,
            'timerSeconds' => null,
        ];
        $schema['questions'] = [
            $this->qGalleryBasics(),
            $this->qNewtonChoice(),
            $this->qFractionsRoots(),
            $this->qGreekTrig(),
            $this->qSumIntegral(),
            $this->qInequalities(),
            $this->qTrueFalseEnergy(),
            $this->qNumericalPythagoras(),
            $this->qMultiCorrectIdentities(),
            $this->qListenEquation(),
            $this->qShowcaseAllInStem(),
        ];

        $result = $validator->validate($schema);
        if (! $result['valid']) {
            throw new \RuntimeException('Math symbols schema invalid: '.implode(' | ', $result['errors']));
        }

        $experience = LearningExperience::create([
            'title' => $title,
            'description' => 'تجربة ديناميكية لاختبار عرض KaTeX: كسور، جذور، يوناني، مثلثات، مجاميع، تكامل، متباينات، رموز فيزيائية.',
            'status' => LearningExperience::STATUS_PUBLISHED,
            'schema_json' => $schema,
            'schema_version' => SchemaValidator::SCHEMA_VERSION_DYNAMIC,
            'engine_version' => SchemaValidator::ENGINE_VERSION,
            'created_by' => $admin?->id,
        ]);

        $this->command?->info('Math symbols experience created: id='.$experience->id);
        $this->command?->info('Play: /learning-experiences/'.$experience->id);
        $this->command?->info('Admin: /admin/learning-experiences/'.$experience->id.'/edit');
    }

    /**
     * @param  list<array<string, mixed>>  $stemBlocks
     * @param  array<string, mixed>  $interaction
     * @return array<string, mixed>
     */
    protected function dyn(
        string $stem,
        array $stemBlocks,
        array $interaction,
        string $explanation,
        int $points = 2,
        ?string $layout = null
    ): array {
        if ($layout) {
            $interaction['layout'] = $layout;
        }

        return [
            'id' => (string) Str::uuid(),
            'stem' => $stem,
            'stemBlocks' => $stemBlocks,
            'interaction' => $interaction,
            'optionBlocks' => [],
            'assets' => ['libraries' => ['katex', 'tts', 'stickers']],
            'points' => $points,
            'difficulty' => 'medium',
            'hints' => ['لاحظ الرموز الرياضية جيداً'],
            'explanation' => $explanation,
            'successMessage' => 'يا بطل! المعادلة صحيحة!',
            'errorMessage' => 'جرّب مرة ثانية وراجع الرموز.',
            'estimatedSeconds' => 40,
            'tags' => ['رياضيات', 'KaTeX'],
            'learningObjectives' => ['قراءة الرموز الرياضية'],
        ];
    }

    /** Q1: gallery of basic operators in stem */
    protected function qGalleryBasics(): array
    {
        return $this->dyn(
            'تعرّف على الرموز الأساسية',
            [
                ['type' => 'text', 'text' => 'هذه رموز العمليات الأساسية — اختر ناتج:'],
                ['type' => 'math', 'latex' => '2 + 3 = ?'],
                ['type' => 'math', 'latex' => '7 - 4 = ?'],
                ['type' => 'math', 'latex' => '3 \\times 4 = ?'],
                ['type' => 'math', 'latex' => '12 \\div 3 = ?'],
                ['type' => 'math', 'latex' => '5 \\cdot 2 = ?'],
                ['type' => 'math', 'latex' => '10 \\pm 2'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => '3 \\times 4 = 12', 'icon' => 'number-3'],
                        ['id' => 'b', 'label' => '3 \\times 4 = 7', 'icon' => 'number-2'],
                        ['id' => 'c', 'label' => '3 \\times 4 = 34', 'icon' => 'star'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'الضرب: 3×4=12. الرموز: + − × ÷ · ±',
            2,
            'equation_grid'
        );
    }

    /** Q2: Newton — classic frac / times */
    protected function qNewtonChoice(): array
    {
        return $this->dyn(
            'قانون نيوتن الثاني',
            [
                ['type' => 'text', 'text' => 'حسب قانون نيوتن: القوة تساوي الكتلة مضروبة في التسارع. اختر المعادلة الصحيحة:'],
                ['type' => 'math', 'latex' => 'F = \\,?'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => 'F = m \\times a'],
                        ['id' => 'b', 'label' => 'F = m + a'],
                        ['id' => 'c', 'label' => 'F = \\dfrac{m}{a}'],
                        ['id' => 'd', 'label' => 'F = \\dfrac{a}{m}'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'F = m × a',
            2,
            'equation_grid'
        );
    }

    /** Q3: fractions and roots as options */
    protected function qFractionsRoots(): array
    {
        return $this->dyn(
            'كسور وجذور',
            [
                ['type' => 'text', 'text' => 'ما قيمة التعبير التالي؟'],
                ['type' => 'math', 'latex' => '\\sqrt{16} + \\frac{1}{2}'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => '4.5'],
                        ['id' => 'b', 'label' => '\\dfrac{9}{2}'],
                        ['id' => 'c', 'label' => '\\sqrt[3]{8}'],
                        ['id' => 'd', 'label' => '4 + \\dfrac{1}{4}'],
                    ],
                    'correctId' => 'b',
                ],
            ],
            '√16=4 و 4+1/2=9/2',
            3,
            'equation_grid'
        );
    }

    /** Q4: Greek + trig */
    protected function qGreekTrig(): array
    {
        return $this->dyn(
            'رموز يونانية ومثلثات',
            [
                ['type' => 'text', 'text' => 'أيّ صيغة صحيحة لمحيط دائرة نصف قطرها r؟'],
                ['type' => 'math', 'latex' => '\\alpha,\\; \\beta,\\; \\gamma,\\; \\theta,\\; \\pi,\\; \\Delta'],
                ['type' => 'math', 'latex' => '\\sin\\theta,\\; \\cos\\theta,\\; \\tan\\theta'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => 'C = 2\\pi r', 'icon' => 'ball'],
                        ['id' => 'b', 'label' => 'C = \\pi r^{2}', 'icon' => 'sun'],
                        ['id' => 'c', 'label' => 'C = \\frac{1}{2}\\pi r', 'icon' => 'star'],
                        ['id' => 'd', 'label' => 'A = 2\\pi r', 'icon' => 'flower'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'محيط الدائرة C = 2πr ، والمساحة A = πr²',
            2,
            'equation_grid'
        );
    }

    /** Q5: sum / product / integral display */
    protected function qSumIntegral(): array
    {
        return $this->dyn(
            'مجاميع وتكامل',
            [
                ['type' => 'text', 'text' => 'أيّ تعبير يمثل مجموع الأعداد من 1 إلى n؟'],
                ['type' => 'math', 'latex' => '\\sum_{k=1}^{n} k \\quad , \\quad \\prod_{k=1}^{n} k \\quad , \\quad \\int_{0}^{1} x\\,dx'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => '\\sum_{k=1}^{n} k', 'icon' => 'number-1'],
                        ['id' => 'b', 'label' => '\\prod_{k=1}^{n} k', 'icon' => 'number-2'],
                        ['id' => 'c', 'label' => '\\int_{0}^{1} x\\,dx', 'icon' => 'number-3'],
                        ['id' => 'd', 'label' => '\\lim_{x \\to \\infty} \\frac{1}{x}', 'icon' => 'number-4'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'رمز المجموع ∑ ، بينما ∏ للضرب المتتابع و ∫ للتكامل',
            3,
            'equation_grid'
        );
    }

    /** Q6: inequalities & relations */
    protected function qInequalities(): array
    {
        return $this->dyn(
            'متباينات وعلاقات',
            [
                ['type' => 'text', 'text' => 'أيّ عبارة صحيحة دائماً للأعداد الحقيقية؟'],
                ['type' => 'math', 'latex' => 'a \\le b'],
                ['type' => 'math', 'latex' => 'a \\ge b'],
                ['type' => 'math', 'latex' => 'a \\ne b'],
                ['type' => 'math', 'latex' => 'a \\approx b'],
                ['type' => 'math', 'latex' => 'x \\in \\mathbb{R}'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => 'x^{2} \\ge 0'],
                        ['id' => 'b', 'label' => 'x^{2} < 0'],
                        ['id' => 'c', 'label' => 'x^{2} \\ne x^{2}'],
                        ['id' => 'd', 'label' => '\\lvert x \\rvert < 0'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'مربع أي عدد حقيقي ≥ 0',
            2,
            'equation_grid'
        );
    }

    /** Q7: true/false with Einstein */
    protected function qTrueFalseEnergy(): array
    {
        return $this->dyn(
            'طاقة وكتلة',
            [
                ['type' => 'text', 'text' => 'هل المعادلة التالية صحيحة؟'],
                ['type' => 'math', 'latex' => 'E = mc^{2}'],
            ],
            [
                'type' => 'true_false',
                'payload' => ['correct' => true],
            ],
            'معادلة أينشتاين الشهيرة للطاقة والكتلة',
            1,
            'truth_banners'
        );
    }

    /** Q8: numerical with Pythagoras stem */
    protected function qNumericalPythagoras(): array
    {
        return $this->dyn(
            'فيثاغورس',
            [
                ['type' => 'text', 'text' => 'في مثلث قائم: a=3 و b=4. ما قيمة c؟'],
                ['type' => 'math', 'latex' => 'a^{2} + b^{2} = c^{2}'],
                ['type' => 'math', 'latex' => 'c = \\sqrt{a^{2}+b^{2}} = \\sqrt{3^{2}+4^{2}}'],
            ],
            [
                'type' => 'numerical',
                'payload' => [
                    'correct' => 5,
                    'tolerance' => 0,
                    'unit' => '',
                    'hint' => 'الجذر التربيعي لـ 9+16',
                ],
            ],
            '√(9+16)=√25=5',
            2,
            'hero_keypad'
        );
    }

    /** Q9: multiple identities */
    protected function qMultiCorrectIdentities(): array
    {
        return $this->dyn(
            'هويات صحيحة (متعدد)',
            [
                ['type' => 'text', 'text' => 'اختر كل الهويات الصحيحة:'],
                ['type' => 'math', 'latex' => '\\sin^{2}\\theta + \\cos^{2}\\theta = 1'],
            ],
            [
                'type' => 'multiple_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => '\\sin^{2}\\theta + \\cos^{2}\\theta = 1', 'icon' => 'star'],
                        ['id' => 'b', 'label' => '(a+b)^{2} = a^{2} + 2ab + b^{2}', 'icon' => 'book'],
                        ['id' => 'c', 'label' => '(a+b)^{2} = a^{2} + b^{2}', 'icon' => 'moon'],
                        ['id' => 'd', 'label' => '\\dfrac{d}{dx}x^{2} = 2x', 'icon' => 'rocket'],
                    ],
                    'correctIds' => ['a', 'b', 'd'],
                ],
            ],
            'الهوية المثلثية، مربع المجموع، ومشتقة x² صحيحة',
            3,
            'equation_grid'
        );
    }

    /** Q10: listen_choose with spoken number still, math options */
    protected function qListenEquation(): array
    {
        return $this->dyn(
            'استمع واختر المعادلة',
            [
                ['type' => 'text', 'text' => 'استمع ثم اختر المعادلة التي تعني «خمسة تساوي اثنان زائد ثلاثة»'],
                ['type' => 'audio', 'text' => 'خمسة تساوي اثنان زائد ثلاثة'],
            ],
            [
                'type' => 'listen_choose',
                'payload' => [
                    'prompt' => [
                        'label' => 'استمع',
                        'text' => 'خمسة تساوي اثنان زائد ثلاثة',
                        'icon' => '🎧',
                        'audioUrl' => null,
                    ],
                    'options' => [
                        ['id' => 'a', 'label' => '5 = 2 + 3', 'icon' => 'number-5'],
                        ['id' => 'b', 'label' => '5 = 2 \\times 3', 'icon' => 'number-2'],
                        ['id' => 'c', 'label' => '5 = \\frac{2}{3}', 'icon' => 'number-3'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            '5 = 2 + 3',
            2,
            'listen_stage'
        );
    }

    /**
     * Q11: big showcase — many symbols only in stem (pick “شاهدت الرموز”).
     * Stress-tests KaTeX coverage in one viewport.
     */
    protected function qShowcaseAllInStem(): array
    {
        return $this->dyn(
            'معرض الرموز الشامل',
            [
                ['type' => 'text', 'text' => 'معرض الرموز — تأكد أن كل سطر يظهر بشكل صحيح ثم اختر «نعم ظهرت»'],
                ['type' => 'math', 'latex' => '\\text{عمليات: } a+b-c \\times d \\div e \\cdot f \\pm g'],
                ['type' => 'math', 'latex' => '\\text{كسر وجذر: } \\frac{a}{b},\\; \\sqrt{x},\\; \\sqrt[n]{x},\\; x^{2},\\; a_{n}'],
                ['type' => 'math', 'latex' => '\\text{يوناني: } \\alpha\\beta\\gamma\\delta\\theta\\lambda\\mu\\pi\\sigma\\omega\\Delta\\Omega'],
                ['type' => 'math', 'latex' => '\\text{مثلثات: } \\sin x,\\; \\cos x,\\; \\tan x,\\; \\arcsin x'],
                ['type' => 'math', 'latex' => '\\text{تحليل: } \\sum_{i=1}^{n} i,\\; \\prod_{i=1}^{n} i,\\; \\int_{a}^{b} f(x)\\,dx,\\; \\lim_{x\\to\\infty}'],
                ['type' => 'math', 'latex' => '\\text{علاقات: } \\leq\\;\\geq\\;\\neq\\;\\approx\\;\\equiv\\;\\propto\\;\\in\\;\\subset\\;\\cup\\;\\cap'],
                ['type' => 'math', 'latex' => '\\text{أسهم: } \\rightarrow\\;\\leftarrow\\;\\Rightarrow\\;\\Leftrightarrow\\;\\mapsto'],
                ['type' => 'math', 'latex' => '\\text{فيزياء: } F=ma,\\; E=mc^{2},\\; v=\\frac{\\Delta x}{\\Delta t},\\; P=\\frac{F}{A}'],
                ['type' => 'math', 'latex' => '\\text{أقواس: } \\left( \\frac{1}{2} \\right),\\; \\left| x \\right|,\\; \\left\\{ a,b \\right\\}'],
                ['type' => 'math', 'latex' => '\\text{هندسة: } \\angle ABC,\\; \\triangle ABC,\\; \\perp,\\; \\parallel'],
            ],
            [
                'type' => 'single_choice',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => 'نعم، ظهرت الرموز بوضوح', 'icon' => 'star'],
                        ['id' => 'b', 'label' => 'لا، بعضها كود خام', 'icon' => 'moon'],
                        ['id' => 'c', 'label' => 'الصفحة توقفت', 'icon' => 'tree'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'هذا سؤال تحقق بصري لعرض KaTeX — الإجابة المتوقعة: ظهرت الرموز بوضوح',
            1,
            'poster_cards'
        );
    }
}
