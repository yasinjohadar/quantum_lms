<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Services\GamificationNotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BackupNotificationService
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * إشعار اكتمال النسخ
     */
    public function notifyBackupCompleted(Backup $backup): void
    {
        $this->sendEmailNotification($backup, 'completed');
        $this->createSystemNotification($backup, 'completed');
        $this->sendWebhookNotification($backup, 'completed');
    }

    /**
     * إشعار فشل النسخ
     */
    public function notifyBackupFailed(Backup $backup, string $error): void
    {
        $this->sendEmailNotification($backup, 'failed');
        $this->createSystemNotification($backup, 'failed');
        $this->sendWebhookNotification($backup, 'failed');
    }

    /**
     * إرسال إشعار بريد إلكتروني
     */
    public function sendEmailNotification(Backup $backup, string $type): void
    {
        $admin = $backup->creator ?? \App\Models\User::where('role', 'admin')->first();
        
        if (!$admin || !$admin->email) {
            return;
        }

        $subject = $type === 'completed' 
            ? 'اكتملت عملية النسخ الاحتياطي بنجاح'
            : 'فشلت عملية النسخ الاحتياطي';

        $message = $type === 'completed'
            ? "تم إنشاء نسخة احتياطية بنجاح:\n\n"
              . "الاسم: {$backup->name}\n"
              . "النوع: {$backup->backup_type}\n"
              . "الحجم: {$backup->getFileSize()}\n"
              . "التاريخ: {$backup->completed_at->format('Y-m-d H:i:s')}"
            : "فشلت عملية النسخ الاحتياطي:\n\n"
              . "الاسم: {$backup->name}\n"
              . "الخطأ: {$backup->error_message}";

        try {
            Mail::raw($message, function ($mail) use ($admin, $subject) {
                $mail->to($admin->email)
                     ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Error sending backup email notification: ' . $e->getMessage());
        }
    }

    /**
     * إرسال Webhook
     */
    public function sendWebhookNotification(Backup $backup, string $type): void
    {
        $webhookUrl = config('backup.webhook_url');
        
        if (!$webhookUrl) {
            return;
        }

        try {
            Http::post($webhookUrl, [
                'type' => $type,
                'backup_id' => $backup->id,
                'name' => $backup->name,
                'status' => $backup->status,
                'backup_type' => $backup->backup_type,
                'file_size' => $backup->file_size,
                'error_message' => $backup->error_message,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending backup webhook notification: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء إشعار نظام
     */
    public function createSystemNotification(Backup $backup, string $type): void
    {
        $user = $backup->creator ?? \App\Models\User::where('role', 'admin')->first();
        
        if (!$user) {
            return;
        }

        $title = $type === 'completed'
            ? 'اكتملت عملية النسخ الاحتياطي'
            : 'فشلت عملية النسخ الاحتياطي';

        $message = $type === 'completed'
            ? "تم إنشاء نسخة احتياطية: {$backup->name}"
            : "فشلت عملية النسخ الاحتياطي: {$backup->name}";

        $this->notificationService->sendNotification(
            $user,
            'backup_' . $type,
            $title,
            $message,
            [
                'backup_id' => $backup->id,
                'url' => route('admin.backups.show', $backup->id),
            ]
        );
    }
}

