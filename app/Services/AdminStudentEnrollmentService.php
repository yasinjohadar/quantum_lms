<?php

namespace App\Services;

use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Services\Pricing\SubjectPricingResolver;

class AdminStudentEnrollmentService
{
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
     * @return array{created: int, skipped: int}
     */
    public function provisionSubjectEnrollmentsForApprovedClass(int $userId, SchoolClass $class, string $enrollmentNotes, int $enrolledBy): array
    {
        $class->loadMissing(['subjects' => function ($query) {
            $query->where('is_active', true);
        }]);

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($class->subjects as $subject) {
            if (! $this->subjectPricingResolver->isIncludedInClassBundle($subject)) {
                continue;
            }
            $existingEnrollment = Enrollment::withTrashed()
                ->where('user_id', $userId)
                ->where('subject_id', $subject->id)
                ->first();

            if ($existingEnrollment) {
                if ($existingEnrollment->status === 'active') {
                    $skippedCount++;
                    continue;
                }
                if (in_array($existingEnrollment->status, ['pending', 'suspended', 'completed'], true)) {
                    $existingEnrollment->forceDelete();
                }
            }

            Enrollment::create([
                'user_id' => $userId,
                'subject_id' => $subject->id,
                'enrolled_by' => $enrolledBy,
                'enrolled_at' => now(),
                'status' => 'active',
                'notes' => $enrollmentNotes,
            ]);
            $createdCount++;
        }

        return ['created' => $createdCount, 'skipped' => $skippedCount];
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
}
