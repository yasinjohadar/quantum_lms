<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Subject;
use App\Models\Quiz;
use App\Models\Lesson;
use Carbon\Carbon;

class AnalyticsEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('لا توجد طلاب في قاعدة البيانات. تخطي seeding.');
            return;
        }

        // استخدام فقط أحداث الكويزات لتجنب مشاكل العلاقات مع الدروس
        $eventTypes = [
            'start_quiz' => ['count' => 40, 'quiz' => true],
            'complete_quiz' => ['count' => 35, 'quiz' => true],
        ];

        $events = [];

        foreach ($eventTypes as $eventType => $config) {
            $createdCount = 0;
            $maxAttempts = $config['count'] * 5; // محاولات أكثر
            $attempts = 0;

            while ($createdCount < $config['count'] && $attempts < $maxAttempts) {
                $attempts++;
                $user = $students->random();
                
                $eventData = [
                    'event_type' => $eventType,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'updated_at' => Carbon::now(),
                ];

                $canCreate = false;

                if ($config['lesson'] ?? false) {
                    // الحصول على درس عشوائي موجود مع تحميل العلاقات
                    $lesson = Lesson::with('unit.section')->inRandomOrder()->first();
                    if ($lesson && $lesson->unit && $lesson->unit->section) {
                        $subjectId = $lesson->unit->section->subject_id;
                        // التحقق من أن subject_id موجود فعلاً
                        if ($subjectId && Subject::find($subjectId)) {
                            $eventData['lesson_id'] = $lesson->id;
                            $eventData['subject_id'] = $subjectId;
                            $canCreate = true;
                        }
                    }
                } elseif ($config['quiz'] ?? false) {
                    // الحصول على اختبار عشوائي موجود
                    $quiz = Quiz::inRandomOrder()->first();
                    if ($quiz && $quiz->subject_id) {
                        // التحقق من أن subject_id موجود فعلاً
                        if (Subject::find($quiz->subject_id)) {
                            $eventData['quiz_id'] = $quiz->id;
                            $eventData['subject_id'] = $quiz->subject_id;
                            $canCreate = true;
                        }
                    }
                }

                if ($canCreate) {
                    $events[] = $eventData;
                    $createdCount++;
                }
            }
        }

        // تقسيم إلى batches لتجنب مشاكل الذاكرة
        if (!empty($events)) {
            foreach (array_chunk($events, 100) as $batch) {
                AnalyticsEvent::insert($batch);
            }
            $this->command->info('تم إنشاء ' . count($events) . ' حدث تحليلي تجريبي.');
        } else {
            $this->command->warn('لم يتم إنشاء أي أحداث تحليلية. تأكد من وجود دروس أو اختبارات في قاعدة البيانات.');
        }
    }
}

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Subject;
use App\Models\Quiz;
use App\Models\Lesson;
use Carbon\Carbon;

class AnalyticsEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('لا توجد طلاب في قاعدة البيانات. تخطي seeding.');
            return;
        }

        // استخدام فقط أحداث الكويزات لتجنب مشاكل العلاقات مع الدروس
        $eventTypes = [
            'start_quiz' => ['count' => 40, 'quiz' => true],
            'complete_quiz' => ['count' => 35, 'quiz' => true],
        ];

        $events = [];

        foreach ($eventTypes as $eventType => $config) {
            $createdCount = 0;
            $maxAttempts = $config['count'] * 5; // محاولات أكثر
            $attempts = 0;

            while ($createdCount < $config['count'] && $attempts < $maxAttempts) {
                $attempts++;
                $user = $students->random();
                
                $eventData = [
                    'event_type' => $eventType,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'updated_at' => Carbon::now(),
                ];

                $canCreate = false;

                if ($config['lesson'] ?? false) {
                    // الحصول على درس عشوائي موجود مع تحميل العلاقات
                    $lesson = Lesson::with('unit.section')->inRandomOrder()->first();
                    if ($lesson && $lesson->unit && $lesson->unit->section) {
                        $subjectId = $lesson->unit->section->subject_id;
                        // التحقق من أن subject_id موجود فعلاً
                        if ($subjectId && Subject::find($subjectId)) {
                            $eventData['lesson_id'] = $lesson->id;
                            $eventData['subject_id'] = $subjectId;
                            $canCreate = true;
                        }
                    }
                } elseif ($config['quiz'] ?? false) {
                    // الحصول على اختبار عشوائي موجود
                    $quiz = Quiz::inRandomOrder()->first();
                    if ($quiz && $quiz->subject_id) {
                        // التحقق من أن subject_id موجود فعلاً
                        if (Subject::find($quiz->subject_id)) {
                            $eventData['quiz_id'] = $quiz->id;
                            $eventData['subject_id'] = $quiz->subject_id;
                            $canCreate = true;
                        }
                    }
                }

                if ($canCreate) {
                    $events[] = $eventData;
                    $createdCount++;
                }
            }
        }

        // تقسيم إلى batches لتجنب مشاكل الذاكرة
        if (!empty($events)) {
            foreach (array_chunk($events, 100) as $batch) {
                AnalyticsEvent::insert($batch);
            }
            $this->command->info('تم إنشاء ' . count($events) . ' حدث تحليلي تجريبي.');
        } else {
            $this->command->warn('لم يتم إنشاء أي أحداث تحليلية. تأكد من وجود دروس أو اختبارات في قاعدة البيانات.');
        }
    }
}
