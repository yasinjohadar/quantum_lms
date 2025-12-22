<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إرسال التذكيرات المستحقة للأحداث';

    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): int
    {
        $this->info('بدء التحقق من التذكيرات المستحقة...');

        try {
            $reminderService->sendReminders();
            $this->info('تم إرسال التذكيرات بنجاح!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء إرسال التذكيرات: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
