<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على الوحدات المتاحة
        $units = Unit::all();
        
        if ($units->isEmpty()) {
            $this->command->warn('لا توجد وحدات متاحة. يرجى إنشاء وحدات أولاً.');
            return;
        }

        $this->command->info('بدء إنشاء 100 سؤال (10 أسئلة لكل نوع)...');

        // 1. اختيار واحد (10 أسئلة)
        $this->createSingleChoiceQuestions($units);

        // 2. اختيار متعدد (10 أسئلة)
        $this->createMultipleChoiceQuestions($units);

        // 3. صح/خطأ (10 أسئلة)
        $this->createTrueFalseQuestions($units);

        // 4. إجابة قصيرة (10 أسئلة)
        $this->createShortAnswerQuestions($units);

        // 5. مقالي (10 أسئلة)
        $this->createEssayQuestions($units);

        // 6. مطابقة (10 أسئلة)
        $this->createMatchingQuestions($units);

        // 7. ترتيب (10 أسئلة)
        $this->createOrderingQuestions($units);

        // 8. ملء الفراغات (10 أسئلة)
        $this->createFillBlanksQuestions($units);

        // 9. رقمي (10 أسئلة)
        $this->createNumericalQuestions($units);

        // 10. سحب وإفلات (10 أسئلة)
        $this->createDragDropQuestions($units);

        $this->command->info('تم إنشاء 100 سؤال بنجاح!');
    }

    /**
     * إنشاء أسئلة اختيار واحد
     */
    private function createSingleChoiceQuestions($units)
    {
        $questions = [
            [
                'title' => 'ما هي عاصمة المملكة العربية السعودية؟',
                'content' => '<p>اختر العاصمة الصحيحة للمملكة العربية السعودية من الخيارات التالية:</p>',
                'options' => [
                    ['content' => 'الرياض', 'is_correct' => true],
                    ['content' => 'جدة', 'is_correct' => false],
                    ['content' => 'الدمام', 'is_correct' => false],
                    ['content' => 'مكة المكرمة', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو أكبر كوكب في المجموعة الشمسية؟',
                'content' => '<p>اختر الكوكب الأكبر من حيث الحجم:</p>',
                'options' => [
                    ['content' => 'المشتري', 'is_correct' => true],
                    ['content' => 'زحل', 'is_correct' => false],
                    ['content' => 'الأرض', 'is_correct' => false],
                    ['content' => 'نبتون', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو العنصر الكيميائي الذي رمزه "O"؟',
                'content' => '<p>اختر العنصر الصحيح:</p>',
                'options' => [
                    ['content' => 'الأكسجين', 'is_correct' => true],
                    ['content' => 'الذهب', 'is_correct' => false],
                    ['content' => 'الحديد', 'is_correct' => false],
                    ['content' => 'الكربون', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'كم عدد القارات في العالم؟',
                'content' => '<p>اختر العدد الصحيح للقارات:</p>',
                'options' => [
                    ['content' => '7', 'is_correct' => true],
                    ['content' => '5', 'is_correct' => false],
                    ['content' => '6', 'is_correct' => false],
                    ['content' => '8', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو أطول نهر في العالم؟',
                'content' => '<p>اختر أطول نهر:</p>',
                'options' => [
                    ['content' => 'نهر النيل', 'is_correct' => true],
                    ['content' => 'نهر الأمازون', 'is_correct' => false],
                    ['content' => 'نهر المسيسيبي', 'is_correct' => false],
                    ['content' => 'نهر اليانغتسي', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي أصغر دولة في العالم من حيث المساحة؟',
                'content' => '<p>اختر الدولة الأصغر:</p>',
                'options' => [
                    ['content' => 'الفاتيكان', 'is_correct' => true],
                    ['content' => 'موناكو', 'is_correct' => false],
                    ['content' => 'سان مارينو', 'is_correct' => false],
                    ['content' => 'ليختنشتاين', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو أعمق محيط في العالم؟',
                'content' => '<p>اختر المحيط الأعمق:</p>',
                'options' => [
                    ['content' => 'المحيط الهادئ', 'is_correct' => true],
                    ['content' => 'المحيط الأطلسي', 'is_correct' => false],
                    ['content' => 'المحيط الهندي', 'is_correct' => false],
                    ['content' => 'المحيط المتجمد الشمالي', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو العدد الأولي الأصغر؟',
                'content' => '<p>اختر العدد الأولي الأصغر:</p>',
                'options' => [
                    ['content' => '2', 'is_correct' => true],
                    ['content' => '1', 'is_correct' => false],
                    ['content' => '3', 'is_correct' => false],
                    ['content' => '0', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو الغاز الذي يشكل النسبة الأكبر من الغلاف الجوي؟',
                'content' => '<p>اختر الغاز الصحيح:</p>',
                'options' => [
                    ['content' => 'النيتروجين', 'is_correct' => true],
                    ['content' => 'الأكسجين', 'is_correct' => false],
                    ['content' => 'ثاني أكسيد الكربون', 'is_correct' => false],
                    ['content' => 'الأرجون', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هو أطول جبل في العالم؟',
                'content' => '<p>اختر الجبل الأطول:</p>',
                'options' => [
                    ['content' => 'جبل إيفرست', 'is_correct' => true],
                    ['content' => 'جبل كي 2', 'is_correct' => false],
                    ['content' => 'جبل كانشينجونغا', 'is_correct' => false],
                    ['content' => 'جبل لوتسي', 'is_correct' => false],
                ]
            ],
        ];

        foreach ($questions as $index => $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'single_choice',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(5, 20),
                'is_active' => true,
                'created_by' => 1,
            ]);

            foreach ($questionData['options'] as $order => $optionData) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $optionData['content'],
                    'is_correct' => $optionData['is_correct'],
                    'order' => $order + 1,
                ]);
            }

            // ربط السؤال بوحدة عشوائية
            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة اختيار واحد');
    }

    /**
     * إنشاء أسئلة اختيار متعدد
     */
    private function createMultipleChoiceQuestions($units)
    {
        $questions = [
            [
                'title' => 'ما هي اللغات البرمجية التالية؟',
                'content' => '<p>اختر جميع اللغات البرمجية من القائمة:</p>',
                'options' => [
                    ['content' => 'Python', 'is_correct' => true],
                    ['content' => 'JavaScript', 'is_correct' => true],
                    ['content' => 'HTML', 'is_correct' => false],
                    ['content' => 'CSS', 'is_correct' => false],
                    ['content' => 'Java', 'is_correct' => true],
                ]
            ],
            [
                'title' => 'ما هي القارات التي تقع في نصف الكرة الشمالي؟',
                'content' => '<p>اختر جميع القارات الصحيحة:</p>',
                'options' => [
                    ['content' => 'أوروبا', 'is_correct' => true],
                    ['content' => 'آسيا', 'is_correct' => true],
                    ['content' => 'أمريكا الشمالية', 'is_correct' => true],
                    ['content' => 'أفريقيا', 'is_correct' => false],
                    ['content' => 'أستراليا', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي الألوان الأساسية في الضوء؟',
                'content' => '<p>اختر جميع الألوان الأساسية:</p>',
                'options' => [
                    ['content' => 'الأحمر', 'is_correct' => true],
                    ['content' => 'الأخضر', 'is_correct' => true],
                    ['content' => 'الأزرق', 'is_correct' => true],
                    ['content' => 'الأصفر', 'is_correct' => false],
                    ['content' => 'البرتقالي', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي أنواع البيانات في البرمجة؟',
                'content' => '<p>اختر جميع أنواع البيانات الصحيحة:</p>',
                'options' => [
                    ['content' => 'String', 'is_correct' => true],
                    ['content' => 'Integer', 'is_correct' => true],
                    ['content' => 'Boolean', 'is_correct' => true],
                    ['content' => 'Function', 'is_correct' => false],
                    ['content' => 'Array', 'is_correct' => true],
                ]
            ],
            [
                'title' => 'ما هي الغازات النبيلة؟',
                'content' => '<p>اختر جميع الغازات النبيلة:</p>',
                'options' => [
                    ['content' => 'الهيليوم', 'is_correct' => true],
                    ['content' => 'النيون', 'is_correct' => true],
                    ['content' => 'الأرجون', 'is_correct' => true],
                    ['content' => 'الأكسجين', 'is_correct' => false],
                    ['content' => 'النيتروجين', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي أجزاء النبات الأساسية؟',
                'content' => '<p>اختر جميع الأجزاء الأساسية:</p>',
                'options' => [
                    ['content' => 'الجذور', 'is_correct' => true],
                    ['content' => 'الساق', 'is_correct' => true],
                    ['content' => 'الأوراق', 'is_correct' => true],
                    ['content' => 'الثمار', 'is_correct' => false],
                    ['content' => 'البذور', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي أنظمة التشغيل؟',
                'content' => '<p>اختر جميع أنظمة التشغيل:</p>',
                'options' => [
                    ['content' => 'Windows', 'is_correct' => true],
                    ['content' => 'Linux', 'is_correct' => true],
                    ['content' => 'macOS', 'is_correct' => true],
                    ['content' => 'Microsoft Word', 'is_correct' => false],
                    ['content' => 'Google Chrome', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي الأعداد الأولية؟',
                'content' => '<p>اختر جميع الأعداد الأولية:</p>',
                'options' => [
                    ['content' => '2', 'is_correct' => true],
                    ['content' => '3', 'is_correct' => true],
                    ['content' => '5', 'is_correct' => true],
                    ['content' => '4', 'is_correct' => false],
                    ['content' => '6', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي المحيطات في العالم؟',
                'content' => '<p>اختر جميع المحيطات:</p>',
                'options' => [
                    ['content' => 'المحيط الهادئ', 'is_correct' => true],
                    ['content' => 'المحيط الأطلسي', 'is_correct' => true],
                    ['content' => 'المحيط الهندي', 'is_correct' => true],
                    ['content' => 'البحر المتوسط', 'is_correct' => false],
                    ['content' => 'البحر الأحمر', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'ما هي أجهزة الإدخال في الحاسوب؟',
                'content' => '<p>اختر جميع أجهزة الإدخال:</p>',
                'options' => [
                    ['content' => 'لوحة المفاتيح', 'is_correct' => true],
                    ['content' => 'الماوس', 'is_correct' => true],
                    ['content' => 'الماسح الضوئي', 'is_correct' => true],
                    ['content' => 'الشاشة', 'is_correct' => false],
                    ['content' => 'الطابعة', 'is_correct' => false],
                ]
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'multiple_choice',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(10, 25),
                'is_active' => true,
                'created_by' => 1,
            ]);

            foreach ($questionData['options'] as $order => $optionData) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $optionData['content'],
                    'is_correct' => $optionData['is_correct'],
                    'order' => $order + 1,
                ]);
            }

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة اختيار متعدد');
    }

    /**
     * إنشاء أسئلة صح/خطأ
     */
    private function createTrueFalseQuestions($units)
    {
        $questions = [
            ['title' => 'الأرض كروية الشكل', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
            ['title' => 'الماء يتجمد عند درجة حرارة 100 مئوية', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => false],
            ['title' => 'الشمس نجم', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
            ['title' => 'الضوء أسرع من الصوت', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
            ['title' => 'الأسماك تتنفس بالرئتين', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => false],
            ['title' => 'القمر يدور حول الأرض', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
            ['title' => 'الذهب معدن', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
            ['title' => 'الطماطم من الفواكه', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
            ['title' => 'القطب الشمالي أبرد من القطب الجنوبي', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => false],
            ['title' => 'الإنترنت اخترع في القرن العشرين', 'content' => '<p>هل هذه العبارة صحيحة أم خاطئة؟</p>', 'correct' => true],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'true_false',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(5, 15),
                'is_active' => true,
                'created_by' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => 'صحيح',
                'is_correct' => $questionData['correct'],
                'order' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => 'خاطئ',
                'is_correct' => !$questionData['correct'],
                'order' => 2,
            ]);

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة صح/خطأ');
    }

    /**
     * إنشاء أسئلة إجابة قصيرة
     */
    private function createShortAnswerQuestions($units)
    {
        $questions = [
            ['title' => 'ما هي عاصمة مصر؟', 'content' => '<p>اكتب اسم العاصمة:</p>'],
            ['title' => 'كم عدد أيام الأسبوع؟', 'content' => '<p>اكتب العدد:</p>'],
            ['title' => 'ما هو اسم أكبر محيط؟', 'content' => '<p>اكتب اسم المحيط:</p>'],
            ['title' => 'كم عدد الكواكب في المجموعة الشمسية؟', 'content' => '<p>اكتب العدد:</p>'],
            ['title' => 'ما هو اسم أطول نهر في أفريقيا؟', 'content' => '<p>اكتب اسم النهر:</p>'],
            ['title' => 'ما هي عاصمة فرنسا؟', 'content' => '<p>اكتب اسم العاصمة:</p>'],
            ['title' => 'كم عدد قارات العالم؟', 'content' => '<p>اكتب العدد:</p>'],
            ['title' => 'ما هو اسم أطول جبل في العالم؟', 'content' => '<p>اكتب اسم الجبل:</p>'],
            ['title' => 'ما هي عاصمة اليابان؟', 'content' => '<p>اكتب اسم العاصمة:</p>'],
            ['title' => 'كم عدد أيام السنة الكبيسة؟', 'content' => '<p>اكتب العدد:</p>'],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'short_answer',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(5, 15),
                'is_active' => true,
                'created_by' => 1,
            ]);

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة إجابة قصيرة');
    }

    /**
     * إنشاء أسئلة مقالية
     */
    private function createEssayQuestions($units)
    {
        $questions = [
            ['title' => 'اشرح كيف تعمل عملية البناء الضوئي', 'content' => '<p>اكتب مقالاً مفصلاً عن عملية البناء الضوئي في النباتات:</p>'],
            ['title' => 'ما هي أهمية الماء في الحياة؟', 'content' => '<p>اكتب مقالاً عن أهمية الماء:</p>'],
            ['title' => 'اشرح كيف يعمل الإنترنت', 'content' => '<p>اكتب مقالاً عن كيفية عمل الإنترنت:</p>'],
            ['title' => 'ما هي فوائد القراءة؟', 'content' => '<p>اكتب مقالاً عن فوائد القراءة:</p>'],
            ['title' => 'اشرح دورة الماء في الطبيعة', 'content' => '<p>اكتب مقالاً عن دورة الماء:</p>'],
            ['title' => 'ما هي أهمية التعليم في المجتمع؟', 'content' => '<p>اكتب مقالاً عن أهمية التعليم:</p>'],
            ['title' => 'اشرح كيف تعمل الخلايا الشمسية', 'content' => '<p>اكتب مقالاً عن الخلايا الشمسية:</p>'],
            ['title' => 'ما هي آثار التلوث البيئي؟', 'content' => '<p>اكتب مقالاً عن آثار التلوث:</p>'],
            ['title' => 'اشرح أهمية الحفاظ على البيئة', 'content' => '<p>اكتب مقالاً عن الحفاظ على البيئة:</p>'],
            ['title' => 'ما هي فوائد ممارسة الرياضة؟', 'content' => '<p>اكتب مقالاً عن فوائد الرياضة:</p>'],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'essay',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(20, 50),
                'is_active' => true,
                'created_by' => 1,
            ]);

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة مقالية');
    }

    /**
     * إنشاء أسئلة مطابقة
     */
    private function createMatchingQuestions($units)
    {
        $questions = [
            [
                'title' => 'طابق العواصم مع الدول',
                'content' => '<p>قم بمطابقة العواصم مع دولها:</p>',
                'pairs' => [
                    ['left' => 'الرياض', 'right' => 'السعودية'],
                    ['left' => 'القاهرة', 'right' => 'مصر'],
                    ['left' => 'بغداد', 'right' => 'العراق'],
                    ['left' => 'دمشق', 'right' => 'سوريا'],
                ]
            ],
            [
                'title' => 'طابق العناصر الكيميائية مع رموزها',
                'content' => '<p>قم بمطابقة العناصر مع رموزها:</p>',
                'pairs' => [
                    ['left' => 'الأكسجين', 'right' => 'O'],
                    ['left' => 'الهيدروجين', 'right' => 'H'],
                    ['left' => 'الكربون', 'right' => 'C'],
                    ['left' => 'النيتروجين', 'right' => 'N'],
                ]
            ],
            [
                'title' => 'طابق الكواكب مع ترتيبها',
                'content' => '<p>قم بمطابقة الكواكب مع ترتيبها من الشمس:</p>',
                'pairs' => [
                    ['left' => 'عطارد', 'right' => '1'],
                    ['left' => 'الزهرة', 'right' => '2'],
                    ['left' => 'الأرض', 'right' => '3'],
                    ['left' => 'المريخ', 'right' => '4'],
                ]
            ],
            [
                'title' => 'طابق الألوان الأساسية مع الألوان الثانوية',
                'content' => '<p>قم بمطابقة الألوان:</p>',
                'pairs' => [
                    ['left' => 'أحمر + أصفر', 'right' => 'برتقالي'],
                    ['left' => 'أزرق + أصفر', 'right' => 'أخضر'],
                    ['left' => 'أحمر + أزرق', 'right' => 'بنفسجي'],
                ]
            ],
            [
                'title' => 'طابق الحيوانات مع بيئاتها',
                'content' => '<p>قم بمطابقة الحيوانات مع بيئاتها:</p>',
                'pairs' => [
                    ['left' => 'السمكة', 'right' => 'الماء'],
                    ['left' => 'الطائر', 'right' => 'الهواء'],
                    ['left' => 'الأسد', 'right' => 'اليابسة'],
                ]
            ],
            [
                'title' => 'طابق الفصول مع الأشهر',
                'content' => '<p>قم بمطابقة الفصول:</p>',
                'pairs' => [
                    ['left' => 'الربيع', 'right' => 'مارس - مايو'],
                    ['left' => 'الصيف', 'right' => 'يونيو - أغسطس'],
                    ['left' => 'الخريف', 'right' => 'سبتمبر - نوفمبر'],
                    ['left' => 'الشتاء', 'right' => 'ديسمبر - فبراير'],
                ]
            ],
            [
                'title' => 'طابق أجزاء النبات مع وظائفها',
                'content' => '<p>قم بمطابقة الأجزاء مع وظائفها:</p>',
                'pairs' => [
                    ['left' => 'الجذور', 'right' => 'امتصاص الماء'],
                    ['left' => 'الأوراق', 'right' => 'البناء الضوئي'],
                    ['left' => 'الساق', 'right' => 'نقل الغذاء'],
                ]
            ],
            [
                'title' => 'طابق الأنظمة مع وظائفها',
                'content' => '<p>قم بمطابقة الأنظمة:</p>',
                'pairs' => [
                    ['left' => 'الجهاز التنفسي', 'right' => 'التنفس'],
                    ['left' => 'الجهاز الهضمي', 'right' => 'الهضم'],
                    ['left' => 'الجهاز الدوري', 'right' => 'الدورة الدموية'],
                ]
            ],
            [
                'title' => 'طابق القارات مع أكبر دولها',
                'content' => '<p>قم بمطابقة القارات:</p>',
                'pairs' => [
                    ['left' => 'آسيا', 'right' => 'روسيا'],
                    ['left' => 'أفريقيا', 'right' => 'الجزائر'],
                    ['left' => 'أمريكا الشمالية', 'right' => 'كندا'],
                ]
            ],
            [
                'title' => 'طابق الوحدات مع قياساتها',
                'content' => '<p>قم بمطابقة الوحدات:</p>',
                'pairs' => [
                    ['left' => 'المتر', 'right' => 'الطول'],
                    ['left' => 'الكيلوجرام', 'right' => 'الكتلة'],
                    ['left' => 'الثانية', 'right' => 'الوقت'],
                ]
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'matching',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(10, 25),
                'is_active' => true,
                'created_by' => 1,
            ]);

            foreach ($questionData['pairs'] as $order => $pair) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $pair['left'],
                    'match_target' => $pair['right'],
                    'is_correct' => true,
                    'order' => $order + 1,
                ]);
            }

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة مطابقة');
    }

    /**
     * إنشاء أسئلة ترتيب
     */
    private function createOrderingQuestions($units)
    {
        $questions = [
            [
                'title' => 'رتب مراحل دورة حياة الفراشة',
                'content' => '<p>رتب المراحل بالترتيب الصحيح:</p>',
                'items' => ['بيضة', 'يرقة', 'شرنقة', 'فراشة']
            ],
            [
                'title' => 'رتب خطوات عملية الطبخ',
                'content' => '<p>رتب الخطوات بالترتيب الصحيح:</p>',
                'items' => ['تحضير المكونات', 'التسخين', 'الطهي', 'التقديم']
            ],
            [
                'title' => 'رتب مراحل النمو عند الإنسان',
                'content' => '<p>رتب المراحل بالترتيب الصحيح:</p>',
                'items' => ['طفل', 'مراهق', 'شاب', 'كهل']
            ],
            [
                'title' => 'رتب خطوات حل المشكلة',
                'content' => '<p>رتب الخطوات بالترتيب الصحيح:</p>',
                'items' => ['تحديد المشكلة', 'جمع المعلومات', 'تحليل الحلول', 'تنفيذ الحل']
            ],
            [
                'title' => 'رتب مراحل اليوم',
                'content' => '<p>رتب المراحل بالترتيب الصحيح:</p>',
                'items' => ['الصباح', 'الظهر', 'المساء', 'الليل']
            ],
            [
                'title' => 'رتب خطوات الكتابة',
                'content' => '<p>رتب الخطوات بالترتيب الصحيح:</p>',
                'items' => ['التخطيط', 'الكتابة', 'المراجعة', 'التعديل']
            ],
            [
                'title' => 'رتب مراحل الفصول',
                'content' => '<p>رتب الفصول بالترتيب الصحيح:</p>',
                'items' => ['الربيع', 'الصيف', 'الخريف', 'الشتاء']
            ],
            [
                'title' => 'رتب خطوات البحث العلمي',
                'content' => '<p>رتب الخطوات بالترتيب الصحيح:</p>',
                'items' => ['الملاحظة', 'الفرضية', 'التجربة', 'النتيجة']
            ],
            [
                'title' => 'رتب مراحل التعليم',
                'content' => '<p>رتب المراحل بالترتيب الصحيح:</p>',
                'items' => ['الابتدائي', 'المتوسط', 'الثانوي', 'الجامعي']
            ],
            [
                'title' => 'رتب خطوات البرمجة',
                'content' => '<p>رتب الخطوات بالترتيب الصحيح:</p>',
                'items' => ['التخطيط', 'الكتابة', 'الاختبار', 'التشغيل']
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'ordering',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(10, 25),
                'is_active' => true,
                'created_by' => 1,
            ]);

            foreach ($questionData['items'] as $order => $item) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $item,
                    'is_correct' => true,
                    'order' => $order + 1,
                ]);
            }

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة ترتيب');
    }

    /**
     * إنشاء أسئلة ملء الفراغات
     */
    private function createFillBlanksQuestions($units)
    {
        $questions = [
            [
                'title' => 'املأ الفراغات في الجملة التالية',
                'content' => '<p>العاصمة {1} هي {2} وتقع في قارة {3}.</p>',
                'answers' => ['السعودية', 'الرياض', 'آسيا']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>الماء يتكون من ذرتين من {1} وذرة واحدة من {2}.</p>',
                'answers' => ['الهيدروجين', 'الأكسجين']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>أطول نهر في العالم هو نهر {1} وأطول جبل هو {2}.</p>',
                'answers' => ['النيل', 'إيفرست']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>عدد الكواكب في المجموعة الشمسية هو {1} وأكبر كوكب هو {2}.</p>',
                'answers' => ['8', 'المشتري']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>أصغر دولة في العالم هي {1} وأكبر دولة هي {2}.</p>',
                'answers' => ['الفاتيكان', 'روسيا']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>اللون الأساسي في الضوء هو {1} و {2} و {3}.</p>',
                'answers' => ['الأحمر', 'الأخضر', 'الأزرق']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>أجزاء النبات الأساسية هي {1} و {2} و {3}.</p>',
                'answers' => ['الجذور', 'الساق', 'الأوراق']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>عدد القارات في العالم هو {1} وأكبر قارة هي {2}.</p>',
                'answers' => ['7', 'آسيا']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>العنصر الكيميائي الذي رمزه O هو {1} ورمزه H هو {2}.</p>',
                'answers' => ['الأكسجين', 'الهيدروجين']
            ],
            [
                'title' => 'املأ الفراغات',
                'content' => '<p>أعمق محيط هو {1} وأطول نهر في أفريقيا هو {2}.</p>',
                'answers' => ['الهادئ', 'النيل']
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'fill_blanks',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(10, 25),
                'blank_answers' => $questionData['answers'],
                'is_active' => true,
                'created_by' => 1,
            ]);

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة ملء الفراغات');
    }

    /**
     * إنشاء أسئلة رقمية
     */
    private function createNumericalQuestions($units)
    {
        $questions = [
            ['title' => 'ما هو ناتج 5 × 7؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 35, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 100 ÷ 4؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 25, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 15 + 23؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 38, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 50 - 17؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 33, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 8²؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 64, 'tolerance' => 0],
            ['title' => 'ما هو ناتج √144؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 12, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 3 × 4 × 5؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 60, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 144 ÷ 12؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 12, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 7 × 8؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 56, 'tolerance' => 0],
            ['title' => 'ما هو ناتج 9²؟', 'content' => '<p>اكتب الناتج:</p>', 'answer' => 81, 'tolerance' => 0],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'],
                'type' => 'numerical',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(5, 15),
                'tolerance' => $questionData['tolerance'],
                'is_active' => true,
                'created_by' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => (string)$questionData['answer'],
                'is_correct' => true,
                'order' => 1,
            ]);

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة رقمية');
    }

    /**
     * إنشاء أسئلة سحب وإفلات
     */
    private function createDragDropQuestions($units)
    {
        $questions = [
            [
                'title' => 'اسحب العناصر إلى الفئات الصحيحة',
                'content' => '<p>قم بسحب العناصر إلى الفئات المناسبة:</p>',
                'items' => ['تفاح', 'موز', 'برتقال', 'جزر', 'طماطم', 'خيار'],
                'zones' => [
                    ['label' => 'فواكه'],
                    ['label' => 'خضروات'],
                ]
            ],
            [
                'title' => 'اسحب الحيوانات إلى بيئاتها',
                'content' => '<p>قم بسحب الحيوانات:</p>',
                'items' => ['سمكة', 'طائر', 'أسد', 'دولفين', 'نسر'],
                'zones' => [
                    ['label' => 'مائي'],
                    ['label' => 'جوي'],
                    ['label' => 'برّي'],
                ]
            ],
            [
                'title' => 'اسحب الألوان إلى مجموعاتها',
                'content' => '<p>قم بسحب الألوان:</p>',
                'items' => ['أحمر', 'أزرق', 'أصفر', 'أخضر', 'برتقالي', 'بنفسجي'],
                'zones' => [
                    ['label' => 'أساسية'],
                    ['label' => 'ثانوية'],
                ]
            ],
            [
                'title' => 'اسحب الكواكب إلى مجموعاتها',
                'content' => '<p>قم بسحب الكواكب:</p>',
                'items' => ['عطارد', 'الزهرة', 'الأرض', 'المريخ', 'المشتري', 'زحل'],
                'zones' => [
                    ['label' => 'صخرية'],
                    ['label' => 'غازية'],
                ]
            ],
            [
                'title' => 'اسحب العناصر إلى مجموعاتها',
                'content' => '<p>قم بسحب العناصر:</p>',
                'items' => ['أكسجين', 'هيدروجين', 'كربون', 'نيتروجين', 'حديد', 'ذهب'],
                'zones' => [
                    ['label' => 'غازات'],
                    ['label' => 'معادن'],
                ]
            ],
            [
                'title' => 'اسحب المواد إلى أنواعها',
                'content' => '<p>قم بسحب المواد:</p>',
                'items' => ['ماء', 'هواء', 'ذهب', 'فضة', 'خشب', 'بلاستيك'],
                'zones' => [
                    ['label' => 'سائلة'],
                    ['label' => 'صلبة'],
                    ['label' => 'غازية'],
                ]
            ],
            [
                'title' => 'اسحب الأجهزة إلى أنواعها',
                'content' => '<p>قم بسحب الأجهزة:</p>',
                'items' => ['لوحة مفاتيح', 'ماوس', 'شاشة', 'طابعة', 'ماسح ضوئي'],
                'zones' => [
                    ['label' => 'إدخال'],
                    ['label' => 'إخراج'],
                ]
            ],
            [
                'title' => 'اسحب القارات إلى نصف الكرة',
                'content' => '<p>قم بسحب القارات:</p>',
                'items' => ['أوروبا', 'آسيا', 'أفريقيا', 'أمريكا الشمالية', 'أمريكا الجنوبية'],
                'zones' => [
                    ['label' => 'شمالي'],
                    ['label' => 'جنوبي'],
                ]
            ],
            [
                'title' => 'اسحب الفصول إلى فتراتها',
                'content' => '<p>قم بسحب الفصول:</p>',
                'items' => ['ربيع', 'صيف', 'خريف', 'شتاء'],
                'zones' => [
                    ['label' => 'دافئة'],
                    ['label' => 'باردة'],
                ]
            ],
            [
                'title' => 'اسحب الأنظمة إلى وظائفها',
                'content' => '<p>قم بسحب الأنظمة:</p>',
                'items' => ['تنفسي', 'هضمي', 'دوري', 'عصبي'],
                'zones' => [
                    ['label' => 'تنفس'],
                    ['label' => 'هضم'],
                    ['label' => 'دورة دموية'],
                    ['label' => 'تحكم'],
                ]
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'title' => $questionData['title'],
                'content' => $questionData['content'] . '<div class="drop-zones" data-zones=\'' . json_encode($questionData['zones']) . '\'></div>',
                'type' => 'drag_drop',
                'difficulty' => $this->getRandomDifficulty(),
                'default_points' => rand(10, 25),
                'is_active' => true,
                'created_by' => 1,
            ]);

            foreach ($questionData['items'] as $order => $item) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $item,
                    'is_correct' => true,
                    'order' => $order + 1,
                ]);
            }

            $question->units()->attach($units->random()->id);
        }

        $this->command->info('تم إنشاء 10 أسئلة سحب وإفلات');
    }

    /**
     * الحصول على صعوبة عشوائية
     */
    private function getRandomDifficulty(): string
    {
        $difficulties = ['easy', 'medium', 'hard'];
        return $difficulties[array_rand($difficulties)];
    }
}
