<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * بيانات تجريبية لقائمة المراجعة (دروس + اختبارات قيد المراجعة).
 *
 * التشغيل فقط عند الحاجة:
 *   php artisan db:seed --class=ContentReviewQueueSeeder
 */
class ContentReviewQueueSeeder extends Seeder
{
    private const TITLE_PREFIX = '[تجربة مراجعة]';

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $teacher = $this->ensureTeacherUser();
        ['subject' => $subject, 'section' => $section, 'unit' => $unit] = $this->resolveCurriculum();

        $teacher->assignedSubjects()->syncWithoutDetaching([
            $subject->id => [
                'assigned_by' => $teacher->id,
                'assigned_at' => now(),
            ],
        ]);

        $pendingLessons = $this->seedPendingLessons($unit, $section, 5);
        $rejectedLesson = $this->seedRejectedLesson($unit, $section, $teacher);
        $pendingQuizzes = $this->seedPendingQuizzes($subject, $unit, $section, $teacher, 5);
        $rejectedQuiz = $this->seedRejectedQuiz($subject, $unit, $section, $teacher);

        $this->command?->info('تم إنشاء بيانات تجربة قائمة المراجعة:');
        $this->command?->line('  - دروس قيد المراجعة: '.$pendingLessons);
        $this->command?->line('  - دروس مرفوضة: '.$rejectedLesson);
        $this->command?->line('  - اختبارات قيد المراجعة: '.$pendingQuizzes);
        $this->command?->line('  - اختبارات مرفوضة: '.$rejectedQuiz);
        $this->command?->line('  - المادة: '.$subject->name.' (ID '.$subject->id.')');
        $this->command?->line('  - المعلم التجريبي: '.$teacher->email.' / 123456789');
        $this->command?->warn('العناصر تبدأ بـ «'.self::TITLE_PREFIX.'» لسهولة التعرف عليها.');
    }

    private function ensureTeacherUser(): User
    {
        foreach ([
            'quiz-create',
            'quiz-edit',
            'quiz-show',
            'lesson-create',
            'lesson-edit',
            'lesson-show',
            'quiz-submit-for-review',
            'lesson-submit-for-review',
        ] as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['description' => $permissionName]
            );
        }

        $roleAttrs = ['dashboard_type' => 'admin'];
        if (Schema::hasColumn((new Role)->getTable(), 'staff_profile')) {
            $roleAttrs['staff_profile'] = 'teacher';
        }

        $role = Role::updateOrCreate(
            ['name' => 'teacher-content-uploader', 'guard_name' => 'web'],
            $roleAttrs
        );
        $role->givePermissionTo([
            'quiz-create',
            'quiz-edit',
            'quiz-show',
            'lesson-create',
            'lesson-edit',
            'lesson-show',
            'quiz-submit-for-review',
            'lesson-submit-for-review',
        ]);

        $teacher = User::firstOrCreate(
            ['email' => 'teacher.review.seed@example.com'],
            [
                'name' => 'معلم تجربة المراجعة',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (! $teacher->hasRole($role)) {
            $teacher->assignRole($role);
        }

        return $teacher;
    }

    /**
     * @return array{subject: Subject, section: SubjectSection, unit: Unit}
     */
    private function resolveCurriculum(): array
    {
        $unit = Unit::query()
            ->whereHas('section.subject')
            ->with(['section.subject.schoolClass'])
            ->orderBy('id')
            ->first();

        if ($unit?->section?->subject) {
            return [
                'subject' => $unit->section->subject,
                'section' => $unit->section,
                'unit' => $unit,
            ];
        }

        $suffix = 'review-seed-'.Str::lower(Str::random(6));

        $stage = Stage::firstOrCreate(
            ['slug' => 'stage-'.$suffix],
            [
                'name' => 'مرحلة تجربة المراجعة',
                'order' => 99,
                'is_active' => true,
            ]
        );

        $class = SchoolClass::firstOrCreate(
            ['slug' => 'class-'.$suffix],
            [
                'name' => 'صف تجربة المراجعة',
                'stage_id' => $stage->id,
                'order' => 99,
                'is_active' => true,
            ]
        );

        $subject = Subject::firstOrCreate(
            ['slug' => 'subject-'.$suffix],
            [
                'name' => 'مادة تجربة المراجعة',
                'class_id' => $class->id,
                'order' => 99,
                'is_active' => true,
                'display_in_class' => true,
            ]
        );

        $section = SubjectSection::firstOrCreate(
            [
                'subject_id' => $subject->id,
                'title' => 'قسم تجربة المراجعة',
            ],
            [
                'order' => 1,
                'is_active' => true,
            ]
        );

        $unit = Unit::firstOrCreate(
            [
                'section_id' => $section->id,
                'title' => 'وحدة تجربة المراجعة',
            ],
            [
                'order' => 1,
                'is_active' => true,
            ]
        );

        return compact('subject', 'section', 'unit');
    }

    private function seedPendingLessons(Unit $unit, SubjectSection $section, int $count): int
    {
        $created = 0;
        $baseOrder = (int) (Lesson::query()->where('unit_id', $unit->id)->max('order') ?? 0);

        for ($i = 1; $i <= $count; $i++) {
            $title = self::TITLE_PREFIX.' درس قيد المراجعة '.$i;

            $lesson = Lesson::updateOrCreate(
                [
                    'title' => $title,
                    'unit_id' => $unit->id,
                ],
                [
                    'section_id' => $section->id,
                    'description' => 'درس تجريبي لاختبار مسار الموافقة من قائمة المراجعة.',
                    'video_type' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'video_id' => 'dQw4w9WgXcQ',
                    'duration' => 600,
                    'order' => $baseOrder + $i,
                    'is_active' => false,
                    'is_free' => false,
                    'is_preview' => false,
                    'review_status' => Lesson::REVIEW_STATUS_PENDING,
                    'review_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'submitted_for_review_at' => now()->subMinutes($count - $i + 1),
                ]
            );

            if ($lesson->wasRecentlyCreated || $lesson->wasChanged()) {
                $created++;
            }
        }

        return $created;
    }

    private function seedRejectedLesson(Unit $unit, SubjectSection $section, User $teacher): int
    {
        $title = self::TITLE_PREFIX.' درس مرفوض';

        Lesson::updateOrCreate(
            [
                'title' => $title,
                'unit_id' => $unit->id,
            ],
            [
                'section_id' => $section->id,
                'description' => 'درس مرفوض تجريبي لعرض حالة الرفض.',
                'video_type' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'video_id' => 'dQw4w9WgXcQ',
                'duration' => 300,
                'order' => 9990,
                'is_active' => false,
                'review_status' => Lesson::REVIEW_STATUS_REJECTED,
                'review_notes' => 'ملاحظات تجريبية: يرجى تحسين جودة الفيديو وإعادة الإرسال.',
                'reviewed_by' => null,
                'reviewed_at' => now()->subHour(),
                'submitted_for_review_at' => now()->subHours(2),
            ]
        );

        return 1;
    }

    private function seedPendingQuizzes(
        Subject $subject,
        Unit $unit,
        SubjectSection $section,
        User $teacher,
        int $count
    ): int {
        $created = 0;
        $baseOrder = (int) (Quiz::query()->where('subject_id', $subject->id)->max('order') ?? 0);

        for ($i = 1; $i <= $count; $i++) {
            $title = self::TITLE_PREFIX.' اختبار قيد المراجعة '.$i;

            $quiz = Quiz::updateOrCreate(
                [
                    'title' => $title,
                    'subject_id' => $subject->id,
                ],
                [
                    'unit_id' => $unit->id,
                    'section_id' => $section->id,
                    'lesson_id' => null,
                    'scope' => 'unit',
                    'description' => 'اختبار تجريبي لاختبار مسار الموافقة من قائمة المراجعة.',
                    'pass_percentage' => 50,
                    'grading_method' => 'highest',
                    'review_options' => 'immediately',
                    'total_points' => 10,
                    'order' => $baseOrder + $i,
                    'is_active' => false,
                    'is_published' => false,
                    'created_by' => $teacher->id,
                    'review_status' => Quiz::REVIEW_STATUS_PENDING,
                    'review_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'submitted_for_review_at' => now()->subMinutes($count - $i + 1),
                ]
            );

            $this->attachSampleQuestion($quiz, $subject, $teacher, $i);
            $created++;
        }

        return $created;
    }

    private function seedRejectedQuiz(
        Subject $subject,
        Unit $unit,
        SubjectSection $section,
        User $teacher
    ): int {
        $title = self::TITLE_PREFIX.' اختبار مرفوض';

        $quiz = Quiz::updateOrCreate(
            [
                'title' => $title,
                'subject_id' => $subject->id,
            ],
            [
                'unit_id' => $unit->id,
                'section_id' => $section->id,
                'scope' => 'unit',
                'description' => 'اختبار مرفوض تجريبي لعرض حالة الرفض.',
                'pass_percentage' => 50,
                'grading_method' => 'highest',
                'review_options' => 'immediately',
                'total_points' => 10,
                'order' => 9990,
                'is_active' => false,
                'is_published' => false,
                'created_by' => $teacher->id,
                'review_status' => Quiz::REVIEW_STATUS_REJECTED,
                'review_notes' => 'ملاحظات تجريبية: أضف أسئلة أوضح ثم أعد الإرسال.',
                'reviewed_by' => null,
                'reviewed_at' => now()->subHour(),
                'submitted_for_review_at' => now()->subHours(2),
            ]
        );

        $this->attachSampleQuestion($quiz, $subject, $teacher, 99);

        return 1;
    }

    private function attachSampleQuestion(Quiz $quiz, Subject $subject, User $teacher, int $index): void
    {
        $questionTitle = self::TITLE_PREFIX.' سؤال للاختبار '.$quiz->id.'-'.$index;

        $question = Question::firstOrCreate(
            [
                'title' => $questionTitle,
                'subject_id' => $subject->id,
            ],
            [
                'type' => 'single_choice',
                'content' => 'سؤال تجريبي لمسار مراجعة الاختبارات.',
                'explanation' => 'الإجابة الصحيحة هي الخيار الأول.',
                'difficulty' => 'easy',
                'default_points' => 10,
                'is_active' => true,
                'created_by' => $teacher->id,
            ]
        );

        if ($question->options()->count() === 0) {
            QuestionOption::create([
                'question_id' => $question->id,
                'content' => 'الإجابة الصحيحة',
                'is_correct' => true,
                'order' => 1,
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'content' => 'إجابة خاطئة',
                'is_correct' => false,
                'order' => 2,
            ]);
        }

        if (! $quiz->questions()->where('questions.id', $question->id)->exists()) {
            $quiz->questions()->attach($question->id, [
                'order' => 1,
                'points' => 10,
                'is_required' => true,
            ]);
        }

        $quiz->update([
            'total_points' => $quiz->questions()->sum('quiz_questions.points') ?: 10,
        ]);
    }
}
