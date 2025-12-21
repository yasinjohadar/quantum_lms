<?php

namespace App\Console\Commands;

use App\Services\TaskService;
use Illuminate\Console\Command;

class ResetDailyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إعادة تعيين المهام اليومية وإنشاء مهام جديدة';

    /**
     * Execute the console command.
     */
    public function handle(TaskService $taskService): int
    {
        $this->info('بدء إعادة تعيين المهام اليومية...');

        // إعادة تعيين المهام القديمة
        $taskService->resetDailyTasks();
        $this->info('تم إعادة تعيين المهام القديمة');

        // إنشاء مهام جديدة
        $taskService->createUserTasksForToday();
        $this->info('تم إنشاء المهام الجديدة');

        $this->info('اكتملت عملية إعادة التعيين بنجاح!');
        return Command::SUCCESS;
    }
}
