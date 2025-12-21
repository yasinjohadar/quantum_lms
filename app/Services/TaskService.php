<?php

namespace App\Services;

use App\Models\DailyTask;
use App\Models\WeeklyTask;
use App\Models\UserTask;
use App\Models\User;
use App\Models\SystemSetting;
use App\Events\TaskCompleted;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class TaskService
{
    public function __construct(
        private PointService $pointService,
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * الحصول على المهام اليومية النشطة
     */
    public function getDailyTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return DailyTask::active()->ordered()->get();
    }

    /**
     * الحصول على المهام الأسبوعية النشطة
     */
    public function getWeeklyTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return WeeklyTask::active()->ordered()->get();
    }

    /**
     * الحصول على مهام المستخدم
     */
    public function getUserTasks(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return UserTask::where('user_id', $user->id)
            ->with(['taskable'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * فحص إكمال المهمة
     */
    public function checkTaskCompletion(User $user, string $eventType, array $metadata = []): void
    {
        // فحص المهام اليومية
        $dailyTasks = $this->getDailyTasks();
        foreach ($dailyTasks as $task) {
            if ($this->matchesTaskType($task, $eventType)) {
                $this->updateTaskProgress($user, $task, $metadata);
            }
        }

        // فحص المهام الأسبوعية
        $weeklyTasks = $this->getWeeklyTasks();
        foreach ($weeklyTasks as $task) {
            if ($this->matchesTaskType($task, $eventType)) {
                $this->updateTaskProgress($user, $task, $metadata);
            }
        }
    }

    /**
     * التحقق من تطابق نوع المهمة مع الحدث
     */
    private function matchesTaskType($task, string $eventType): bool
    {
        $typeMap = [
            'lesson_attended' => 'attendance',
            'lesson_completed' => 'lesson_completion',
            'quiz_completed' => 'quiz',
            'question_answered' => 'question',
        ];

        return isset($typeMap[$eventType]) && $task->type === $typeMap[$eventType];
    }

    /**
     * تحديث تقدم المهمة
     */
    private function updateTaskProgress(User $user, $task, array $metadata = []): void
    {
        $userTask = UserTask::firstOrCreate(
            [
                'user_id' => $user->id,
                'taskable_type' => get_class($task),
                'taskable_id' => $task->id,
            ],
            [
                'status' => 'pending',
                'progress' => 0,
            ]
        );

        if ($userTask->status === 'completed' || $userTask->status === 'expired') {
            return;
        }

        // زيادة التقدم
        $userTask->progress += 1;
        
        // التحقق من إكمال المهمة
        $criteria = $task->criteria ?? [];
        $requiredCount = $criteria['count'] ?? 1;

        if ($userTask->progress >= $requiredCount) {
            $userTask->status = 'completed';
            $userTask->completed_at = now();
            $userTask->save();

            // منح المكافأة
            $this->awardTaskReward($user, $task, $userTask);
        } else {
            $userTask->save();
        }
    }

    /**
     * منح مكافأة المهمة
     */
    public function awardTaskReward(User $user, $task, UserTask $userTask): void
    {
        if ($userTask->claimed_at) {
            return; // تم المطالبة بالفعل
        }

        DB::beginTransaction();
        try {
            // منح النقاط
            if ($task->points_reward > 0) {
                $this->pointService->awardPoints(
                    $user,
                    'task_completion',
                    $task->points_reward,
                    null,
                    [
                        'task_type' => get_class($task),
                        'task_id' => $task->id,
                    ]
                );
            }

            // تحديث حالة المطالبة
            $userTask->claimed_at = now();
            $userTask->save();

            // إرسال Event
            Event::dispatch(new TaskCompleted($user, get_class($task), $task->name, [
                'task_id' => $task->id,
                'points' => $task->points_reward,
            ]));

            // إرسال إشعار (سيتم التعامل معه عبر Listener)
            $this->notificationService->sendNotification(
                $user,
                'task_completed',
                'مهمة مكتملة!',
                "لقد أكملت المهمة: {$task->name}",
                [
                    'task_type' => get_class($task),
                    'task_id' => $task->id,
                    'points' => $task->points_reward,
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error awarding task reward: ' . $e->getMessage());
        }
    }

    /**
     * إعادة تعيين المهام اليومية
     */
    public function resetDailyTasks(): void
    {
        UserTask::where('taskable_type', DailyTask::class)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'expired']);
    }

    /**
     * إعادة تعيين المهام الأسبوعية
     */
    public function resetWeeklyTasks(): void
    {
        UserTask::where('taskable_type', WeeklyTask::class)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'expired']);
    }

    /**
     * إنشاء مهام جديدة للمستخدمين
     */
    public function createUserTasksForToday(): void
    {
        $dailyTasks = $this->getDailyTasks();
        $users = User::students()->get();

        foreach ($users as $user) {
            foreach ($dailyTasks as $task) {
                UserTask::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'taskable_type' => DailyTask::class,
                        'taskable_id' => $task->id,
                    ],
                    [
                        'status' => 'pending',
                        'progress' => 0,
                    ]
                );
            }
        }
    }

    /**
     * إنشاء مهام أسبوعية جديدة للمستخدمين
     */
    public function createUserTasksForWeek(): void
    {
        $weeklyTasks = $this->getWeeklyTasks();
        $users = User::students()->get();

        foreach ($users as $user) {
            foreach ($weeklyTasks as $task) {
                UserTask::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'taskable_type' => WeeklyTask::class,
                        'taskable_id' => $task->id,
                    ],
                    [
                        'status' => 'pending',
                        'progress' => 0,
                    ]
                );
            }
        }
    }
}

