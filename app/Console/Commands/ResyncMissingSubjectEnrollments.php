<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AdminStudentEnrollmentService;
use Illuminate\Console\Command;

/**
 * إصلاح جماعي لكل الطلاب دفعة واحدة: يعيد تفعيل انضمام أي طالب منضم فعلاً لصف (ClassEnrollment
 * approved) إلى أي مادة نشطة ضمن ذلك الصف فقدها بالخطأ (حذف يدوي أو أي سبب آخر) — بينما
 * يتخطّى عمداً أي اشتراك أُلغي بحق بسبب انتهاء اشتراك فعلي (يحمل ملاحظة تلقائية مميزة)، حتى لا
 * يُعاد منح وصول مجاني لطالب لا يستحقه. لا يلمس ClassEnrollment ولا أي بيانات أخرى إطلاقاً.
 */
class ResyncMissingSubjectEnrollments extends Command
{
    protected $signature = 'enrollments:resync-missing-subjects {--dry-run : عرض ما سيتم إصلاحه فقط دون تنفيذ أي تغيير فعلي}';

    protected $description = 'إعادة تفعيل اشتراكات المواد الناقصة لكل الطلاب المنضمين لصفوفهم فعلاً، مع استثناء الاشتراكات المنتهية بحق';

    public function handle(AdminStudentEnrollmentService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $admin = User::role('admin')->first();

        if (! $admin) {
            $this->error('لا يوجد مستخدم بدور admin لاستخدامه كمنفّذ لعملية الإصلاح.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('وضع المعاينة (dry-run) — لن يتم تنفيذ أي تغيير فعلي.');
        }

        $result = $service->bulkResyncMissingSubjectEnrollments($admin->id, $dryRun);

        $this->newLine();
        $this->info("الطلاب الذين تمت مراجعتهم: {$result['students_processed']}");
        $this->info(($dryRun ? 'سيُعاد تفعيل: ' : 'تمت إعادة تفعيل: ')."{$result['created']} اشتراك مادة.");
        $this->info("تم تخطّي {$result['skipped']} مادة مفعّلة أصلاً بشكل سليم.");
        $this->info("تم استثناء {$result['skipped_expired']} مادة انتهى اشتراكها فعلاً (لن تُعاد تفعيلها).");

        return self::SUCCESS;
    }
}
