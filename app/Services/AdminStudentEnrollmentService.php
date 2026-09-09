<?php

namespace App\Services;

use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\Pricing\SubjectPricingResolver;

class AdminStudentEnrollmentService
{
    /**
     * نصوص الملاحظات التي تكتبها آليات الإلغاء التلقائي الحقيقية (انتهاء اشتراك فعلي) —
     * وجود إحداها على تسجيل غير نشط يعني أنه أُلغي بحق ولا يجب إعادة تفعيله تلقائياً
     * ضمن إصلاح جماعي (على عكس حذف يدوي بالخطأ، الذي لا يترك أي ملاحظة كهذه).
     */
    private const LEGITIMATE_EXPIRATION_NOTES = [
        'انتهت صلاحية الاشتراك تلقائياً',
        'انتهت مدة اشتراك الصف تلقائياً',
    ];

    public function __construct(
        protected SubjectPricingResolver $subjectPricingResolver,
    ) {
    }
    /**
     * ربط طالب بصف (موافقة) ومزامنة مواد الصف النشطة.
     *
     * @return array{created: int, skipped: int}
     */
    public function assignApprovedClassWithProvisioning(int $userId, int $classId, ?string $notes, int $enrolledBy): array
    {
        $classEnrollmentPayload = [
            'status' => 'approved',
            'enrolled_by' => $enrolledBy,
            'enrolled_at' => now(),
            'notes' => $notes,
        ];

        $classEnrollment = ClassEnrollment::withTrashed()
            ->where('user_id', $userId)
            ->where('class_id', $classId)
            ->first();

        if ($classEnrollment) {
            if ($classEnrollment->trashed()) {
                $classEnrollment->restore();
            }
            $classEnrollment->update($classEnrollmentPayload);
        } else {
            ClassEnrollment::create(array_merge(
                [
                    'user_id' => $userId,
                    'class_id' => $classId,
                ],
                $classEnrollmentPayload
            ));
        }

        $class = SchoolClass::with(['subjects' => function ($query) {
            $query->where('is_active', true);
        }])->findOrFail($classId);

        return $this->provisionSubjectEnrollmentsForApprovedClass(
            $userId,
            $class,
            'تم ربط الطالب بالصف: '.$class->name,
            $enrolledBy
        );
    }

    /**
     * @param  bool  $skipLegitimateExpirations  إن كان true، يتخطّى (لا يعيد تفعيل) أي تسجيل يحمل
     *                                            ملاحظة إلغاء تلقائي حقيقي (انتهاء اشتراك فعلي) —
     *                                            يُستخدم في الإصلاح الجماعي لتفادي إعادة منح وصول
     *                                            مجاني لطالب انتهى اشتراكه فعلاً.
     * @param  bool  $dryRun  إن كان true، لا يُنشئ/يحذف أي شيء فعلياً، فقط يحسب ما كان سيحدث.
     * @return array{created: int, skipped: int, skipped_expired: int}
     */
    public function provisionSubjectEnrollmentsForApprovedClass(
        int $userId,
        SchoolClass $class,
        string $enrollmentNotes,
        int $enrolledBy,
        bool $skipLegitimateExpirations = false,
        bool $dryRun = false
    ): array {
        $class->loadMissing(['subjects' => function ($query) {
            $query->where('is_active', true);
        }]);

        $createdCount = 0;
        $skippedCount = 0;
        $skippedExpiredCount = 0;

        foreach ($class->subjects as $subject) {
            if (! $this->subjectPricingResolver->isIncludedInClassBundle($subject)) {
                continue;
            }
            $existingEnrollment = Enrollment::withTrashed()
                ->where('user_id', $userId)
                ->where('subject_id', $subject->id)
                ->first();

            if ($existingEnrollment) {
                // تسجيل "active" لكنه محذوف ناعماً (عبر إجراء فصل يدوي سابق) يجب اعتباره
                // غير فعّال وإعادة إنشائه، لا تخطّيه.
                if ($existingEnrollment->status === 'active' && ! $existingEnrollment->trashed()) {
                    $skippedCount++;
                    continue;
                }

                if ($skipLegitimateExpirations && $this->hasLegitimateExpirationNote($existingEnrollment->notes)) {
                    $skippedExpiredCount++;
                    continue;
                }

                if (! $dryRun) {
                    $existingEnrollment->forceDelete();
                }
            }

            if (! $dryRun) {
                Enrollment::create([
                    'user_id' => $userId,
                    'subject_id' => $subject->id,
                    'enrolled_by' => $enrolledBy,
                    'enrolled_at' => now(),
                    'status' => 'active',
                    'notes' => $enrollmentNotes,
                ]);
            }
            $createdCount++;
        }

        return ['created' => $createdCount, 'skipped' => $skippedCount, 'skipped_expired' => $skippedExpiredCount];
    }

    private function hasLegitimateExpirationNote(?string $notes): bool
    {
        if (! $notes) {
            return false;
        }

        foreach (self::LEGITIMATE_EXPIRATION_NOTES as $marker) {
            if (str_contains($notes, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * إعادة تفعيل انضمام طالب واحد لكل مواد كل صف هو منضم إليه فعلاً (ClassEnrollment
     * بحالة 'approved') — دون لمس حالة انضمامه للصف نفسها، ودون أي أثر على طلاب آخرين
     * أو صفوف/مواد أخرى غير صفوفه. يُستخدم لإصلاح اشتراكات مواد أُلغيت (يدوياً أو تلقائياً
     * عبر انتهاء اشتراك) بينما بقي الطالب منضماً للصف.
     *
     * @return array{classes_processed: int, created: int, skipped: int}
     */
    public function resyncAllSubjectEnrollmentsForUser(int $userId, int $enrolledBy): array
    {
        $classIds = ClassEnrollment::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->pluck('class_id');

        $classes = SchoolClass::with(['subjects' => function ($query) {
            $query->where('is_active', true);
        }])->whereIn('id', $classIds)->get();

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($classes as $class) {
            $result = $this->provisionSubjectEnrollmentsForApprovedClass(
                $userId,
                $class,
                'إعادة مزامنة اشتراكات مواد الصف: '.$class->name,
                $enrolledBy
            );
            $totalCreated += $result['created'];
            $totalSkipped += $result['skipped'];
        }

        return [
            'classes_processed' => $classes->count(),
            'created' => $totalCreated,
            'skipped' => $totalSkipped,
        ];
    }

    /**
     * إصلاح جماعي آمن لكل الطلاب دفعة واحدة: يعيد فقط الاشتراكات الناقصة/الملغاة بالخطأ
     * (بلا ملاحظة انتهاء تلقائي حقيقي)، ويتخطّى أي تسجيل يحمل ملاحظة انتهاء اشتراك فعلي —
     * حتى لا يُعاد منح وصول مجاني لطالب انتهى اشتراكه بحق. لا يلمس ClassEnrollment إطلاقاً.
     *
     * @return array{students_processed: int, created: int, skipped: int, skipped_expired: int}
     */
    public function bulkResyncMissingSubjectEnrollments(int $enrolledBy, bool $dryRun = false): array
    {
        $userIds = ClassEnrollment::query()
            ->where('status', 'approved')
            ->distinct()
            ->pluck('user_id');

        $totals = ['students_processed' => 0, 'created' => 0, 'skipped' => 0, 'skipped_expired' => 0];

        foreach ($userIds as $userId) {
            $classIds = ClassEnrollment::query()
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->pluck('class_id');

            $classes = SchoolClass::with(['subjects' => function ($query) {
                $query->where('is_active', true);
            }])->whereIn('id', $classIds)->get();

            foreach ($classes as $class) {
                $result = $this->provisionSubjectEnrollmentsForApprovedClass(
                    $userId,
                    $class,
                    'إصلاح جماعي لاشتراكات مواد الصف: '.$class->name,
                    $enrolledBy,
                    skipLegitimateExpirations: true,
                    dryRun: $dryRun
                );
                $totals['created'] += $result['created'];
                $totals['skipped'] += $result['skipped'];
                $totals['skipped_expired'] += $result['skipped_expired'];
            }

            $totals['students_processed']++;
        }

        return $totals;
    }

    /**
     * @return array{insert_count: int, reactivated: int, skipped: int}
     */
    public function bulkAttachSubjects(array $userIds, array $subjectIds, string $status, ?string $notes, int $enrolledBy): array
    {
        $enrollments = [];
        $skipped = 0;
        $reactivated = 0;

        foreach ($userIds as $userId) {
            foreach ($subjectIds as $subjectId) {
                $existing = Enrollment::withTrashed()
                    ->where('user_id', $userId)
                    ->where('subject_id', $subjectId)
                    ->first();

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                        $existing->update([
                            'enrolled_by' => $enrolledBy,
                            'enrolled_at' => now(),
                            'status' => $status,
                            'notes' => $notes,
                        ]);
                        $reactivated++;
                        continue;
                    }
                    $skipped++;
                    continue;
                }

                $enrollments[] = [
                    'user_id' => $userId,
                    'subject_id' => $subjectId,
                    'enrolled_by' => $enrolledBy,
                    'enrolled_at' => now(),
                    'status' => $status,
                    'notes' => $notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($enrollments)) {
            Enrollment::insert($enrollments);
        }

        return [
            'insert_count' => count($enrollments),
            'reactivated' => $reactivated,
            'skipped' => $skipped,
        ];
    }

    /**
     * تسجيل الطلاب المنضمين فعلياً (class_enrollments.status = approved) في صف المادة
     * الجديدة، بشكل إضافي بحت: لا يلمس أي Enrollment موجود مسبقاً (نشط أو محذوف ناعماً)
     * إطلاقاً — فقط يُنشئ سجلاً جديداً لمن لا يملك أي سجل على الإطلاق لهذه المادة.
     * يُستدعى فقط من SubjectController::store() لحظة إنشاء مادة جديدة.
     *
     * @return array{created: int, skipped: int}
     */
    public function provisionSubjectForAlreadyEnrolledClassStudents(Subject $subject, int $enrolledBy): array
    {
        if (! $subject->class_id || ! $subject->is_active) {
            return ['created' => 0, 'skipped' => 0];
        }

        if (! $this->subjectPricingResolver->isIncludedInClassBundle($subject)) {
            return ['created' => 0, 'skipped' => 0];
        }

        $userIds = ClassEnrollment::query()
            ->where('class_id', $subject->class_id)
            ->where('status', 'approved')
            ->pluck('user_id')
            ->unique();

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($userIds as $userId) {
            $alreadyHasEnrollment = Enrollment::withTrashed()
                ->where('user_id', $userId)
                ->where('subject_id', $subject->id)
                ->exists();

            if ($alreadyHasEnrollment) {
                $skippedCount++;
                continue;
            }

            Enrollment::create([
                'user_id' => $userId,
                'subject_id' => $subject->id,
                'enrolled_by' => $enrolledBy,
                'enrolled_at' => now(),
                'status' => 'active',
                'notes' => 'تسجيل تلقائي عند إضافة المادة للصف: '.$subject->name,
            ]);
            $createdCount++;
        }

        return ['created' => $createdCount, 'skipped' => $skippedCount];
    }
}
