<?php

namespace App\Services;

use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Models\Purchase;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\Pricing\SubjectPricingResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassSubscriptionExpirationService
{
    public function __construct(
        protected SubjectPricingResolver $subjectPricingResolver,
    ) {
    }

    public function processExpiredClasses(): int
    {
        $processed = 0;

        SchoolClass::query()
            ->subscriptionExpiredDue()
            ->orderBy('id')
            ->chunkById(50, function ($classes) use (&$processed) {
                foreach ($classes as $class) {
                    $this->revokeClassSubscription($class);
                    $processed++;
                }
            });

        return $processed;
    }

    public function revokeClassSubscription(SchoolClass $class): void
    {
        DB::transaction(function () use ($class) {
            $class->refresh();

            if ($class->subscription_revoked_at !== null) {
                return;
            }

            if (! $class->subscription_ends_at || $class->subscription_ends_at->isFuture()) {
                return;
            }

            $revocationNote = 'انتهت مدة اشتراك الصف تلقائياً في ' . now()->format('Y-m-d');

            $approvedClassEnrollments = ClassEnrollment::query()
                ->where('class_id', $class->id)
                ->where('status', 'approved')
                ->get();

            $userIds = $approvedClassEnrollments->pluck('user_id')->unique()->values()->all();

            foreach ($approvedClassEnrollments as $classEnrollment) {
                $classEnrollment->update([
                    'status' => 'rejected',
                    'notes' => trim(($classEnrollment->notes ? $classEnrollment->notes . ' | ' : '') . $revocationNote),
                ]);
            }

            $class->load(['subjects' => function ($query) {
                $query->where('is_active', true);
            }]);

            $bundleSubjectIds = $class->subjects
                ->filter(fn (Subject $subject) => $this->subjectPricingResolver->isIncludedInClassBundle($subject))
                ->pluck('id')
                ->all();

            if ($bundleSubjectIds !== [] && $userIds !== []) {
                $activeEnrollments = Enrollment::query()
                    ->whereIn('user_id', $userIds)
                    ->whereIn('subject_id', $bundleSubjectIds)
                    ->where('status', 'active')
                    ->get();

                foreach ($activeEnrollments as $enrollment) {
                    $enrollment->update([
                        'status' => 'suspended',
                        'notes' => trim(($enrollment->notes ? $enrollment->notes . ' | ' : '') . $revocationNote),
                    ]);
                }

                Purchase::query()
                    ->completed()
                    ->whereNull('access_revoked_at')
                    ->whereIn('user_id', $userIds)
                    ->where(function ($query) use ($class, $bundleSubjectIds) {
                        $query->where(function ($classPurchaseQuery) use ($class) {
                            $classPurchaseQuery
                                ->where('purchasable_type', SchoolClass::class)
                                ->where('purchasable_id', $class->id);
                        })->orWhere(function ($subjectPurchaseQuery) use ($bundleSubjectIds) {
                            $subjectPurchaseQuery
                                ->where('purchasable_type', Subject::class)
                                ->whereIn('purchasable_id', $bundleSubjectIds);
                        });
                    })
                    ->chunkById(100, function ($purchases) use ($revocationNote) {
                        foreach ($purchases as $purchase) {
                            $purchase->update([
                                'access_revoked_at' => now(),
                                'notes' => trim(($purchase->notes ? $purchase->notes . ' | ' : '') . $revocationNote),
                            ]);
                        }
                    });
            } elseif ($userIds !== []) {
                Purchase::query()
                    ->completed()
                    ->whereNull('access_revoked_at')
                    ->whereIn('user_id', $userIds)
                    ->where('purchasable_type', SchoolClass::class)
                    ->where('purchasable_id', $class->id)
                    ->chunkById(100, function ($purchases) use ($revocationNote) {
                        foreach ($purchases as $purchase) {
                            $purchase->update([
                                'access_revoked_at' => now(),
                                'notes' => trim(($purchase->notes ? $purchase->notes . ' | ' : '') . $revocationNote),
                            ]);
                        }
                    });
            }

            $class->update([
                'subscription_revoked_at' => now(),
            ]);

            try {
                app(\App\Services\Pricing\PricingCacheManager::class)->invalidateClass($class);
            } catch (\Throwable $e) {
                Log::warning('Failed to invalidate pricing cache after class subscription expiry: ' . $e->getMessage());
            }
        });
    }
}
