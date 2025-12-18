<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $adminId = $admin ? $admin->id : 1;
        
        $units = Unit::pluck('id')->toArray();

        // أسئلة اختيار واحد (10 أسئلة)
        $this->createSingleChoiceQuestions($adminId, $units, 10);
        
        // أسئلة اختيار متعدد (8 أسئلة)
        $this->createMultipleChoiceQuestions($adminId, $units, 8);
        
        // أسئلة صح/خطأ (8 أسئلة)
        $this->createTrueFalseQuestions($adminId, $units, 8);
        
        // أسئلة إجابة قصيرة (5 أسئلة)
        $this->createShortAnswerQuestions($adminId, $units, 5);
        
        // أسئلة مقالية (5 أسئلة)
        $this->createEssayQuestions($adminId, $units, 5);
        
        // أسئلة مطابقة (4 أسئلة)
        $this->createMatchingQuestions($adminId, $units, 4);
        
        // أسئلة ترتيب (4 أسئلة)
        $this->createOrderingQuestions($adminId, $units, 4);
        
        // أسئلة ملء الفراغات (3 أسئلة)
        $this->createFillBlanksQuestions($adminId, $units, 3);
        
        // أسئلة رقمية (3 أسئلة)
        $this->createNumericalQuestions($adminId, $units, 3);

        $this->command->info('تم إنشاء 50 سؤال بنجاح!');
    }

    /**
     * أسئلة اختيار واحد
     */
    protected function createSingleChoiceQuestions($adminId, $units, $count): void
    {
        $questions = [
            [
                'title' => 'ما هي عاصمة المملكة العربية السعودية؟',
                'options' => [
                    ['content' => 'الرياض', 'is_correct' => true],
                    ['content' => 'جدة', 'is_correct' => false],
                    ['content' => 'مكة المكرمة', 'is_correct' => false],
                    ['content' => 'المدينة المنورة', 'is_correct' => false],
                ],
                'explanation' => 'الرياض هي عاصمة المملكة العربية السعودية وأكبر مدنها.',
            ],
            [
                'title' => 'ما هو الناتج من 5 × 8؟',
                'options' => [
                    ['content' => '35', 'is_correct' => false],
                    ['content' => '40', 'is_correct' => true],
                    ['content' => '45', 'is_correct' => false],
                    ['content' => '48', 'is_correct' => false],
                ],
                'explanation' => '5 × 8 = 40',
            ],
            [
                'title' => 'أي من التالي يعتبر من الثدييات؟',
                'options' => [
                    ['content' => 'السمكة', 'is_correct' => false],
                    ['content' => 'الحوت', 'is_correct' => true],
                    ['content' => 'التمساح', 'is_correct' => false],
                    ['content' => 'السلحفاة', 'is_correct' => false],
                ],
                'explanation' => 'الحوت من الثدييات لأنه يرضع صغاره ويتنفس الهواء.',
            ],
            [
                'title' => 'ما هي أكبر قارة في العالم من حيث المساحة؟',
                'options' => [
                    ['content' => 'أفريقيا', 'is_correct' => false],
                    ['content' => 'آسيا', 'is_correct' => true],
                    ['content' => 'أمريكا الشمالية', 'is_correct' => false],
                    ['content' => 'أوروبا', 'is_correct' => false],
                ],
                'explanation' => 'آسيا هي أكبر قارات العالم مساحة وسكاناً.',
            ],
            [
                'title' => 'ما هو العنصر الكيميائي الذي رمزه O؟',
                'options' => [
                    ['content' => 'الذهب', 'is_correct' => false],
                    ['content' => 'الأكسجين', 'is_correct' => true],
                    ['content' => 'الأوزون', 'is_correct' => false],
                    ['content' => 'الأوسميوم', 'is_correct' => false],
                ],
                'explanation' => 'O هو رمز عنصر الأكسجين في الجدول الدوري.',
            ],
            [
                'title' => 'كم عدد أركان الإسلام؟',
                'options' => [
                    ['content' => '4', 'is_correct' => false],
                    ['content' => '5', 'is_correct' => true],
                    ['content' => '6', 'is_correct' => false],
                    ['content' => '7', 'is_correct' => false],
                ],
                'explanation' => 'أركان الإسلام خمسة: الشهادتان، الصلاة، الزكاة، الصوم، والحج.',
            ],
            [
                'title' => 'ما هي اللغة الرسمية في البرازيل؟',
                'options' => [
                    ['content' => 'الإسبانية', 'is_correct' => false],
                    ['content' => 'البرتغالية', 'is_correct' => true],
                    ['content' => 'الإنجليزية', 'is_correct' => false],
                    ['content' => 'الفرنسية', 'is_correct' => false],
                ],
                'explanation' => 'البرتغالية هي اللغة الرسمية في البرازيل.',
            ],
            [
                'title' => 'أي كوكب يُعرف بالكوكب الأحمر؟',
                'options' => [
                    ['content' => 'الزهرة', 'is_correct' => false],
                    ['content' => 'المريخ', 'is_correct' => true],
                    ['content' => 'المشتري', 'is_correct' => false],
                    ['content' => 'زحل', 'is_correct' => false],
                ],
                'explanation' => 'المريخ يُعرف بالكوكب الأحمر بسبب لونه المائل للحمرة.',
            ],
            [
                'title' => 'ما هو أطول نهر في العالم؟',
                'options' => [
                    ['content' => 'نهر الأمازون', 'is_correct' => false],
                    ['content' => 'نهر النيل', 'is_correct' => true],
                    ['content' => 'نهر المسيسيبي', 'is_correct' => false],
                    ['content' => 'نهر اليانغتسي', 'is_correct' => false],
                ],
                'explanation' => 'نهر النيل هو أطول نهر في العالم بطول حوالي 6,650 كم.',
            ],
            [
                'title' => 'من هو مخترع المصباح الكهربائي؟',
                'options' => [
                    ['content' => 'نيكولا تسلا', 'is_correct' => false],
                    ['content' => 'توماس إديسون', 'is_correct' => true],
                    ['content' => 'ألبرت أينشتاين', 'is_correct' => false],
                    ['content' => 'غراهام بيل', 'is_correct' => false],
                ],
                'explanation' => 'توماس إديسون هو مخترع المصباح الكهربائي العملي.',
            ],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            $question = Question::create([
                'type' => 'single_choice',
                'title' => $q['title'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => rand(1, 3),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);

            foreach ($q['options'] as $order => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $opt['content'],
                    'is_correct' => $opt['is_correct'],
                    'order' => $order + 1,
                ]);
            }

            // ربط بوحدة عشوائية
            if (!empty($units) && rand(0, 1)) {
                $question->units()->attach($units[array_rand($units)]);
            }
        }
    }

    /**
     * أسئلة اختيار متعدد
     */
    protected function createMultipleChoiceQuestions($adminId, $units, $count): void
    {
        $questions = [
            [
                'title' => 'أي من التالي يعتبر من الألوان الأساسية؟',
                'options' => [
                    ['content' => 'الأحمر', 'is_correct' => true],
                    ['content' => 'الأزرق', 'is_correct' => true],
                    ['content' => 'الأخضر', 'is_correct' => false],
                    ['content' => 'الأصفر', 'is_correct' => true],
                    ['content' => 'البرتقالي', 'is_correct' => false],
                ],
                'explanation' => 'الألوان الأساسية هي الأحمر والأزرق والأصفر.',
            ],
            [
                'title' => 'اختر الأعداد الأولية من القائمة التالية:',
                'options' => [
                    ['content' => '2', 'is_correct' => true],
                    ['content' => '3', 'is_correct' => true],
                    ['content' => '4', 'is_correct' => false],
                    ['content' => '5', 'is_correct' => true],
                    ['content' => '6', 'is_correct' => false],
                ],
                'explanation' => 'الأعداد الأولية هي التي تقبل القسمة على نفسها وعلى 1 فقط.',
            ],
            [
                'title' => 'أي من الدول التالية تقع في قارة أفريقيا؟',
                'options' => [
                    ['content' => 'مصر', 'is_correct' => true],
                    ['content' => 'المغرب', 'is_correct' => true],
                    ['content' => 'تركيا', 'is_correct' => false],
                    ['content' => 'نيجيريا', 'is_correct' => true],
                    ['content' => 'الهند', 'is_correct' => false],
                ],
                'explanation' => 'مصر والمغرب ونيجيريا دول أفريقية.',
            ],
            [
                'title' => 'اختر الكواكب الصخرية من المجموعة الشمسية:',
                'options' => [
                    ['content' => 'الأرض', 'is_correct' => true],
                    ['content' => 'المريخ', 'is_correct' => true],
                    ['content' => 'المشتري', 'is_correct' => false],
                    ['content' => 'عطارد', 'is_correct' => true],
                    ['content' => 'زحل', 'is_correct' => false],
                ],
                'explanation' => 'الكواكب الصخرية هي: عطارد، الزهرة، الأرض، والمريخ.',
            ],
            [
                'title' => 'أي من العناصر التالية معادن؟',
                'options' => [
                    ['content' => 'الحديد', 'is_correct' => true],
                    ['content' => 'النحاس', 'is_correct' => true],
                    ['content' => 'الكربون', 'is_correct' => false],
                    ['content' => 'الذهب', 'is_correct' => true],
                    ['content' => 'الكبريت', 'is_correct' => false],
                ],
                'explanation' => 'الحديد والنحاس والذهب من المعادن.',
            ],
            [
                'title' => 'اختر الأفعال الخمسة من الجمل التالية:',
                'options' => [
                    ['content' => 'يكتبون', 'is_correct' => true],
                    ['content' => 'تكتبين', 'is_correct' => true],
                    ['content' => 'يكتب', 'is_correct' => false],
                    ['content' => 'يكتبان', 'is_correct' => true],
                    ['content' => 'اكتب', 'is_correct' => false],
                ],
                'explanation' => 'الأفعال الخمسة تتصل بواو الجماعة وألف الاثنين وياء المخاطبة.',
            ],
            [
                'title' => 'أي من التالي يعتبر من مصادر الطاقة المتجددة؟',
                'options' => [
                    ['content' => 'الطاقة الشمسية', 'is_correct' => true],
                    ['content' => 'طاقة الرياح', 'is_correct' => true],
                    ['content' => 'النفط', 'is_correct' => false],
                    ['content' => 'الطاقة المائية', 'is_correct' => true],
                    ['content' => 'الفحم', 'is_correct' => false],
                ],
                'explanation' => 'الطاقة المتجددة تشمل الشمسية والرياح والمائية.',
            ],
            [
                'title' => 'اختر الأشكال الهندسية رباعية الأضلاع:',
                'options' => [
                    ['content' => 'المربع', 'is_correct' => true],
                    ['content' => 'المستطيل', 'is_correct' => true],
                    ['content' => 'المثلث', 'is_correct' => false],
                    ['content' => 'المعين', 'is_correct' => true],
                    ['content' => 'الدائرة', 'is_correct' => false],
                ],
                'explanation' => 'المربع والمستطيل والمعين من الأشكال رباعية الأضلاع.',
            ],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            $question = Question::create([
                'type' => 'multiple_choice',
                'title' => $q['title'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => rand(2, 4),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);

            foreach ($q['options'] as $order => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $opt['content'],
                    'is_correct' => $opt['is_correct'],
                    'order' => $order + 1,
                ]);
            }

            if (!empty($units) && rand(0, 1)) {
                $question->units()->attach($units[array_rand($units)]);
            }
        }
    }

    /**
     * أسئلة صح/خطأ
     */
    protected function createTrueFalseQuestions($adminId, $units, $count): void
    {
        $questions = [
            ['title' => 'الشمس تدور حول الأرض.', 'is_true' => false, 'explanation' => 'الأرض هي التي تدور حول الشمس وليس العكس.'],
            ['title' => 'الماء يغلي عند درجة 100 مئوية عند مستوى سطح البحر.', 'is_true' => true, 'explanation' => 'نعم، درجة غليان الماء هي 100°C عند الضغط الجوي العادي.'],
            ['title' => 'القمر ينتج ضوءه الخاص.', 'is_true' => false, 'explanation' => 'القمر يعكس ضوء الشمس ولا ينتج ضوءه الخاص.'],
            ['title' => 'الحيتان من الأسماك.', 'is_true' => false, 'explanation' => 'الحيتان من الثدييات وليست من الأسماك.'],
            ['title' => 'المملكة العربية السعودية أكبر دولة عربية من حيث المساحة.', 'is_true' => false, 'explanation' => 'الجزائر هي أكبر دولة عربية من حيث المساحة.'],
            ['title' => 'الذهب معدن ثمين.', 'is_true' => true, 'explanation' => 'نعم، الذهب من المعادن الثمينة.'],
            ['title' => 'عدد أيام السنة الميلادية 365 يوماً.', 'is_true' => true, 'explanation' => 'نعم، والسنة الكبيسة 366 يوماً.'],
            ['title' => 'اللغة العربية من اللغات السامية.', 'is_true' => true, 'explanation' => 'نعم، اللغة العربية تنتمي لعائلة اللغات السامية.'],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            $question = Question::create([
                'type' => 'true_false',
                'title' => $q['title'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => 1,
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => 'صح',
                'is_correct' => $q['is_true'],
                'order' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => 'خطأ',
                'is_correct' => !$q['is_true'],
                'order' => 2,
            ]);

            if (!empty($units) && rand(0, 1)) {
                $question->units()->attach($units[array_rand($units)]);
            }
        }
    }

    /**
     * أسئلة إجابة قصيرة
     */
    protected function createShortAnswerQuestions($adminId, $units, $count): void
    {
        $questions = [
            ['title' => 'ما هي عاصمة فرنسا؟', 'explanation' => 'باريس هي عاصمة فرنسا.'],
            ['title' => 'كم عدد أضلاع المثلث؟', 'explanation' => 'المثلث له 3 أضلاع.'],
            ['title' => 'ما هو الرمز الكيميائي للماء؟', 'explanation' => 'الرمز الكيميائي للماء هو H2O.'],
            ['title' => 'من هو النبي الذي ابتلعه الحوت؟', 'explanation' => 'سيدنا يونس عليه السلام.'],
            ['title' => 'ما هي أصغر دولة في العالم؟', 'explanation' => 'الفاتيكان هي أصغر دولة في العالم.'],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            Question::create([
                'type' => 'short_answer',
                'title' => $q['title'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => rand(1, 2),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);
        }
    }

    /**
     * أسئلة مقالية
     */
    protected function createEssayQuestions($adminId, $units, $count): void
    {
        $questions = [
            ['title' => 'اكتب فقرة عن أهمية القراءة في حياة الإنسان.', 'explanation' => 'إجابة مفتوحة تُقيّم حسب المحتوى والأسلوب.'],
            ['title' => 'ناقش أسباب التلوث البيئي وطرق مكافحته.', 'explanation' => 'إجابة مفتوحة تُقيّم حسب الشمولية والتحليل.'],
            ['title' => 'اشرح أهمية التقنية في التعليم الحديث.', 'explanation' => 'إجابة مفتوحة تُقيّم حسب العمق والأمثلة.'],
            ['title' => 'تحدث عن رؤية المملكة 2030 وأهدافها.', 'explanation' => 'إجابة مفتوحة تُقيّم حسب المعلومات والتنظيم.'],
            ['title' => 'قارن بين الحياة في الماضي والحاضر.', 'explanation' => 'إجابة مفتوحة تُقيّم حسب المقارنة والتحليل.'],
        ];

        $difficulties = ['medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            Question::create([
                'type' => 'essay',
                'title' => $q['title'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 2],
                'default_points' => rand(5, 10),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);
        }
    }

    /**
     * أسئلة مطابقة
     */
    protected function createMatchingQuestions($adminId, $units, $count): void
    {
        $questions = [
            [
                'title' => 'طابق بين الدولة وعاصمتها:',
                'pairs' => [
                    ['content' => 'السعودية', 'match_target' => 'الرياض'],
                    ['content' => 'مصر', 'match_target' => 'القاهرة'],
                    ['content' => 'الإمارات', 'match_target' => 'أبوظبي'],
                    ['content' => 'الأردن', 'match_target' => 'عمان'],
                ],
            ],
            [
                'title' => 'طابق بين العالم واختراعه:',
                'pairs' => [
                    ['content' => 'إديسون', 'match_target' => 'المصباح الكهربائي'],
                    ['content' => 'بيل', 'match_target' => 'الهاتف'],
                    ['content' => 'نيوتن', 'match_target' => 'قوانين الحركة'],
                    ['content' => 'أينشتاين', 'match_target' => 'النظرية النسبية'],
                ],
            ],
            [
                'title' => 'طابق بين الحيوان وغذائه:',
                'pairs' => [
                    ['content' => 'الأسد', 'match_target' => 'اللحوم'],
                    ['content' => 'الأرنب', 'match_target' => 'الجزر'],
                    ['content' => 'الباندا', 'match_target' => 'الخيزران'],
                    ['content' => 'النحلة', 'match_target' => 'الرحيق'],
                ],
            ],
            [
                'title' => 'طابق بين الكلمة ومعناها:',
                'pairs' => [
                    ['content' => 'الجلد', 'match_target' => 'الصبر'],
                    ['content' => 'الكرم', 'match_target' => 'السخاء'],
                    ['content' => 'الشجاعة', 'match_target' => 'الإقدام'],
                    ['content' => 'التواضع', 'match_target' => 'عدم الكبر'],
                ],
            ],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            $question = Question::create([
                'type' => 'matching',
                'title' => $q['title'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => count($q['pairs']),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);

            foreach ($q['pairs'] as $order => $pair) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $pair['content'],
                    'match_target' => $pair['match_target'],
                    'is_correct' => true,
                    'order' => $order + 1,
                ]);
            }

            if (!empty($units) && rand(0, 1)) {
                $question->units()->attach($units[array_rand($units)]);
            }
        }
    }

    /**
     * أسئلة ترتيب
     */
    protected function createOrderingQuestions($adminId, $units, $count): void
    {
        $questions = [
            [
                'title' => 'رتب مراحل دورة حياة الفراشة:',
                'items' => ['البيضة', 'اليرقة', 'الشرنقة', 'الفراشة'],
            ],
            [
                'title' => 'رتب الكواكب حسب قربها من الشمس:',
                'items' => ['عطارد', 'الزهرة', 'الأرض', 'المريخ'],
            ],
            [
                'title' => 'رتب الأرقام من الأصغر إلى الأكبر:',
                'items' => ['15', '28', '42', '67', '89'],
            ],
            [
                'title' => 'رتب خطوات الوضوء:',
                'items' => ['النية', 'غسل الوجه', 'غسل اليدين', 'مسح الرأس', 'غسل الرجلين'],
            ],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            $question = Question::create([
                'type' => 'ordering',
                'title' => $q['title'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => count($q['items']),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);

            foreach ($q['items'] as $order => $item) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $item,
                    'is_correct' => true,
                    'order' => $order + 1,
                ]);
            }

            if (!empty($units) && rand(0, 1)) {
                $question->units()->attach($units[array_rand($units)]);
            }
        }
    }

    /**
     * أسئلة ملء الفراغات
     */
    protected function createFillBlanksQuestions($adminId, $units, $count): void
    {
        $questions = [
            [
                'title' => 'عاصمة المملكة العربية السعودية هي [blank].',
                'answers' => ['الرياض'],
                'explanation' => 'الرياض هي عاصمة المملكة العربية السعودية.',
            ],
            [
                'title' => 'الماء يتكون من [blank] و [blank].',
                'answers' => ['الهيدروجين', 'الأكسجين'],
                'explanation' => 'الماء H2O يتكون من ذرتي هيدروجين وذرة أكسجين.',
            ],
            [
                'title' => 'أكبر محيط في العالم هو المحيط [blank].',
                'answers' => ['الهادئ'],
                'explanation' => 'المحيط الهادئ هو أكبر محيطات العالم.',
            ],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            Question::create([
                'type' => 'fill_blanks',
                'title' => $q['title'],
                'blank_answers' => $q['answers'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => count($q['answers']),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'عام',
            ]);
        }
    }

    /**
     * أسئلة رقمية
     */
    protected function createNumericalQuestions($adminId, $units, $count): void
    {
        $questions = [
            [
                'title' => 'ما هو ناتج 25 + 37؟',
                'answer' => '62',
                'tolerance' => 0,
                'explanation' => '25 + 37 = 62',
            ],
            [
                'title' => 'ما هي قيمة π (باي) تقريباً؟',
                'answer' => '3.14',
                'tolerance' => 0.01,
                'explanation' => 'π ≈ 3.14159...',
            ],
            [
                'title' => 'كم عدد دقائق الساعة الواحدة؟',
                'answer' => '60',
                'tolerance' => 0,
                'explanation' => 'الساعة = 60 دقيقة',
            ],
        ];

        $difficulties = ['easy', 'medium', 'hard'];
        
        foreach (array_slice($questions, 0, $count) as $index => $q) {
            $question = Question::create([
                'type' => 'numerical',
                'title' => $q['title'],
                'tolerance' => $q['tolerance'],
                'explanation' => $q['explanation'],
                'difficulty' => $difficulties[$index % 3],
                'default_points' => rand(1, 2),
                'is_active' => true,
                'created_by' => $adminId,
                'category' => 'رياضيات',
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => $q['answer'],
                'is_correct' => true,
                'order' => 1,
            ]);
        }
    }
}

