<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * أسئلة اختبار بسيطة جداً (سؤالان لكل نوع) داخل اختبار جاهز للحل.
 * مستثناة: مقالي، إجابة قصيرة، ملء الفراغات
 */
class SimpleQuestionTypesTestSeeder extends Seeder
{
    public const QUIZ_TITLE = 'اختبار أنواع الأسئلة (تجريبي)';

    public const CATEGORY = 'اختبار الأنواع';

    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@admin.com')->value('id')
            ?? User::query()->value('id')
            ?? 1;

        [$subjectId, $unitId] = $this->resolveSubjectAndUnit();

        $questionIds = [];

        DB::transaction(function () use ($adminId, $subjectId, $unitId, &$questionIds) {
            $this->cleanupPrevious();

            foreach ($this->definitions() as $def) {
                $question = Question::create([
                    'type' => $def['type'],
                    'title' => $def['title'],
                    'content' => $def['content'] ?? null,
                    'explanation' => $def['explanation'] ?? null,
                    'difficulty' => 'easy',
                    'default_points' => 1,
                    'tolerance' => $def['tolerance'] ?? null,
                    'is_active' => true,
                    'created_by' => $adminId,
                    'subject_id' => $subjectId,
                    'category' => self::CATEGORY,
                ]);

                foreach ($def['options'] ?? [] as $index => $option) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => $option['is_correct'] ?? true,
                        'match_target' => $option['match_target'] ?? null,
                        'correct_order' => $option['correct_order'] ?? null,
                        'order' => $index + 1,
                    ]);
                }

                if ($question->type === 'drag_drop') {
                    $this->syncDragDropZones($question->fresh('options'));
                }

                if ($unitId) {
                    $question->units()->syncWithoutDetaching([$unitId]);
                }

                $questionIds[] = $question->id;
                $this->command?->info("✓ [{$def['type']}] {$def['title']}");
            }

            $quiz = $this->createQuiz($adminId, $subjectId, $unitId, $questionIds);

            $this->command?->newLine();
            $this->command?->info('تم إنشاء '.count($questionIds).' سؤالاً داخل اختبار جاهز للحل.');
            $this->command?->info('عنوان الاختبار: '.self::QUIZ_TITLE);
            $this->command?->info('معرّف الاختبار (quiz_id): '.$quiz->id);
            $this->command?->info('رابط الأدمن: /admin/quizzes/'.$quiz->id);
            $this->command?->info('رابط الطالب: /student/subjects/'.$subjectId.'/quizzes (ثم افتح الاختبار)');
        });
    }

    private function cleanupPrevious(): void
    {
        $oldQuizIds = Quiz::withTrashed()
            ->where('title', self::QUIZ_TITLE)
            ->pluck('id');

        if ($oldQuizIds->isNotEmpty()) {
            QuizQuestion::query()->whereIn('quiz_id', $oldQuizIds)->delete();
            Quiz::withTrashed()->whereIn('id', $oldQuizIds)->forceDelete();
        }

        $oldQuestions = Question::withTrashed()
            ->where('category', self::CATEGORY)
            ->where('title', 'like', 'اختبار:%')
            ->get();

        foreach ($oldQuestions as $question) {
            $question->options()->delete();
            $question->units()->detach();
            QuizQuestion::query()->where('question_id', $question->id)->delete();
            $question->forceDelete();
        }
    }

    private function createQuiz(int $adminId, ?int $subjectId, ?int $unitId, array $questionIds): Quiz
    {
        $quizData = [
            'subject_id' => $subjectId,
            'unit_id' => $unitId,
            'title' => self::QUIZ_TITLE,
            'description' => 'اختبار تجريبي بسيط للتحقق من أنواع الأسئلة بعد الإصلاحات.',
            'instructions' => 'أجب عن جميع الأسئلة. كل سؤال بدرجة واحدة. بعد الإرسال ستظهر النتيجة والإجابات الصحيحة.',
            'duration_minutes' => 30,
            'show_timer' => true,
            'auto_submit' => true,
            'max_attempts' => 10,
            'pass_percentage' => 50,
            'grading_method' => 'highest',
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'questions_per_page' => 1,
            'allow_back_navigation' => true,
            'show_result_immediately' => true,
            'show_correct_answers' => true,
            'show_explanation' => true,
            'show_points_per_question' => true,
            'review_options' => 'immediately',
            'available_from' => now()->subDay(),
            'available_to' => now()->addYear(),
            'is_active' => true,
            'is_published' => true,
            'review_status' => Quiz::REVIEW_STATUS_APPROVED,
            'created_by' => $adminId,
            'order' => 1,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('quizzes', 'scope')) {
            $quizData['scope'] = 'unit';
        }

        $quiz = Quiz::create($quizData);

        foreach ($questionIds as $index => $questionId) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_id' => $questionId,
                'order' => $index + 1,
                'points' => 1,
                'is_required' => true,
            ]);
        }

        if (method_exists($quiz, 'calculateTotalPoints')) {
            $quiz->calculateTotalPoints();
        } else {
            $quiz->update(['total_points' => count($questionIds)]);
        }

        return $quiz->fresh();
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveSubjectAndUnit(): array
    {
        $subject = Subject::query()
            ->where('name', 'like', '%علوم%')
            ->whereHas('schoolClass', fn ($q) => $q->where('name', 'like', '%أول%'))
            ->first();

        if (! $subject) {
            $subject = Subject::query()->where('name', 'like', '%علوم%')->first()
                ?? Subject::query()->first();
        }

        if (! $subject) {
            $this->command?->warn('لم يتم العثور على مادة — ستُنشأ الأسئلة بدون subject_id.');

            return [null, null];
        }

        $unit = Unit::query()
            ->whereHas('section', fn ($q) => $q->where('subject_id', $subject->id))
            ->first()
            ?? Unit::query()->first();

        $className = optional($subject->schoolClass)->name ?? '-';
        $this->command?->info("المادة: {$subject->name} ({$className}) | الوحدة: ".($unit->name ?? 'بدون'));

        return [$subject->id, $unit?->id];
    }

    private function syncDragDropZones(Question $question): void
    {
        $zones = $question->options
            ->pluck('match_target')
            ->map(fn ($t) => trim(html_entity_decode(strip_tags((string) $t), ENT_QUOTES, 'UTF-8')))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $label) => ['label' => $label])
            ->all();

        $zonesAttr = htmlspecialchars(json_encode($zones, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        $zonesHtml = '<div class="drop-zones" data-zones="'.$zonesAttr.'"></div>';

        $content = trim((string) ($question->content ?? '')).$zonesHtml;
        $question->update(['content' => $content]);
    }

    private function definitions(): array
    {
        return [
            [
                'type' => 'single_choice',
                'title' => 'اختبار: ما لون السماء عادةً في النهار؟',
                'explanation' => 'الإجابة الصحيحة: أزرق',
                'options' => [
                    ['content' => 'أزرق', 'is_correct' => true],
                    ['content' => 'أخضر', 'is_correct' => false],
                    ['content' => 'أحمر', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'single_choice',
                'title' => 'اختبار: كم يوماً في الأسبوع؟',
                'explanation' => 'الإجابة الصحيحة: 7',
                'options' => [
                    ['content' => '5', 'is_correct' => false],
                    ['content' => '6', 'is_correct' => false],
                    ['content' => '7', 'is_correct' => true],
                    ['content' => '8', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'title' => 'اختبار: أيٌّ مما يلي فاكهة؟ (اختر كل الإجابات الصحيحة)',
                'explanation' => 'الصحيح: تفاح وموز',
                'options' => [
                    ['content' => 'تفاح', 'is_correct' => true],
                    ['content' => 'موز', 'is_correct' => true],
                    ['content' => 'جزر', 'is_correct' => false],
                    ['content' => 'خيار', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'title' => 'اختبار: أيٌّ مما يلي لون أساسي؟ (اختر كل الإجابات الصحيحة)',
                'explanation' => 'الألوان الأساسية: أحمر وأزرق',
                'options' => [
                    ['content' => 'أحمر', 'is_correct' => true],
                    ['content' => 'أزرق', 'is_correct' => true],
                    ['content' => 'أخضر', 'is_correct' => false],
                    ['content' => 'برتقالي', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'true_false',
                'title' => 'اختبار: الشمس تشرق من الشرق.',
                'explanation' => 'صحيح',
                'options' => [
                    ['content' => 'صح', 'is_correct' => true],
                    ['content' => 'خطأ', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'true_false',
                'title' => 'اختبار: القطة تسبح مثل السمكة.',
                'explanation' => 'خطأ',
                'options' => [
                    ['content' => 'صح', 'is_correct' => false],
                    ['content' => 'خطأ', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'matching',
                'title' => 'اختبار: طابق الحيوان بصوته',
                'explanation' => 'قطة→مواء، كلب→نباح',
                'options' => [
                    ['content' => 'قطة', 'match_target' => 'مواء', 'is_correct' => true],
                    ['content' => 'كلب', 'match_target' => 'نباح', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'matching',
                'title' => 'اختبار: طابق الرقم باسمه',
                'explanation' => '1→واحد، 2→اثنان',
                'options' => [
                    ['content' => '1', 'match_target' => 'واحد', 'is_correct' => true],
                    ['content' => '2', 'match_target' => 'اثنان', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'ordering',
                'title' => 'اختبار: رتّب الأرقام تصاعدياً',
                'explanation' => '1 ثم 2 ثم 3',
                'options' => [
                    ['content' => '1', 'correct_order' => 1, 'is_correct' => true],
                    ['content' => '2', 'correct_order' => 2, 'is_correct' => true],
                    ['content' => '3', 'correct_order' => 3, 'is_correct' => true],
                ],
            ],
            [
                'type' => 'ordering',
                'title' => 'اختبار: رتّب أيام الأسبوع من البداية',
                'explanation' => 'الأحد ثم الإثنين ثم الثلاثاء',
                'options' => [
                    ['content' => 'الأحد', 'correct_order' => 1, 'is_correct' => true],
                    ['content' => 'الإثنين', 'correct_order' => 2, 'is_correct' => true],
                    ['content' => 'الثلاثاء', 'correct_order' => 3, 'is_correct' => true],
                ],
            ],
            [
                'type' => 'numerical',
                'title' => 'اختبار: كم يساوي 2 + 2؟',
                'explanation' => 'الإجابة: 4',
                'tolerance' => 0,
                'options' => [
                    ['content' => '4', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'numerical',
                'title' => 'اختبار: كم يساوي 10 − 3؟ (يُسمح بفرق ±1)',
                'explanation' => 'الإجابة: 7 (التسامح ±1)',
                'tolerance' => 1,
                'options' => [
                    ['content' => '7', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'drag_drop',
                'title' => 'اختبار: ضع كل عنصر في مجموعته',
                'content' => '<p>اسحب الفاكهة إلى «فواكه» والخضار إلى «خضار».</p>',
                'explanation' => 'تفاح→فواكه، جزر→خضار',
                'options' => [
                    ['content' => 'تفاح', 'match_target' => 'فواكه', 'is_correct' => true],
                    ['content' => 'جزر', 'match_target' => 'خضار', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'drag_drop',
                'title' => 'اختبار: صنّف الحيوانات',
                'content' => '<p>اسحب كل حيوان إلى مجموعته الصحيحة.</p>',
                'explanation' => 'قطة→حيوانات أليفة، أسد→حيوانات برية',
                'options' => [
                    ['content' => 'قطة', 'match_target' => 'حيوانات أليفة', 'is_correct' => true],
                    ['content' => 'أسد', 'match_target' => 'حيوانات برية', 'is_correct' => true],
                ],
            ],
        ];
    }
}
