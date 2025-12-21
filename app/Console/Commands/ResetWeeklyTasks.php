<?php

namespace App\Console\Commands;

use App\Services\TaskService;
use Illuminate\Console\Command;

class ResetWeeklyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:reset-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إعادة تعيين المهام الأسبوعية وإنشاء مهام جديدة';

    /**
     * Execute the console command.
     */
    public function handle(TaskService $taskService): int
    {
        $this->info('بدء إعادة تعيين المهام الأسبوعية...');

        // إعادة تعيين المهام القديمة
        $taskService->resetWeeklyTasks();
        $this->info('تم إعادة تعيين المهام القديمة');

        // إنشاء مهام جديدة
        $taskService->createUserTasksForWeek();
        $this->info('تم إنشاء المهام الجديدة');

        $this->info('اكتملت عملية إعادة التعيين بنجاح!');
        return Command::SUCCESS;
    }
}
