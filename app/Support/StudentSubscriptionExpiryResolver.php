<?php

namespace App\Support;

use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Purchase;

class StudentSubscriptionExpiryResolver
{
    public static function resolveForUser(User $user, ?int $classId = null): array
    {
        $purchases = $user->relationLoaded('purchases')
            ? $user->purchases
            : Purchase::query()
                ->where('user_id', $user->id)
                ->where('purchasable_type', SchoolClass::class)
                ->completed()
                ->whereNull('access_revoked_at')
                ->with('purchasable')
                ->get();

        $activePurchases = $purchases
            ->filter(function (Purchase $purchase) use ($classId) {
                if ($purchase->purchasable_type !== SchoolClass::class
                    || $purchase->status !== 'completed'
                    || $purchase->access_revoked_at !== null) {
                    return false;
                }

                if ($classId) {
                    return (int) $purchase->purchasable_id === $classId;
                }

                return true;
            })
            ->values();

        $withExpiry = $activePurchases
            ->filter(fn (Purchase $purchase) => $purchase->expires_at !== null)
            ->sortByDesc(fn (Purchase $purchase) => $purchase->expires_at?->timestamp ?? 0)
            ->values();

        if ($withExpiry->isNotEmpty()) {
            /** @var Purchase $primary */
            $primary = $withExpiry->first();
            $class = $primary->purchasable;

            return [
                'purchase_id' => $primary->id,
                'class_id' => (int) $primary->purchasable_id,
                'expires_at' => $primary->expires_at,
                'expires_at_input' => $primary->expires_at->format('Y-m-d'),
                'class_name' => $class?->name,
                'editable' => true,
                'settable' => false,
                'multiple' => ! $classId && $withExpiry->count() > 1,
                'multiple_count' => $withExpiry->count(),
                'max_expires_at' => $class?->subscription_ends_at?->format('Y-m-d'),
                'source' => 'purchase',
            ];
        }

        $withoutExpiry = $activePurchases
            ->filter(fn (Purchase $purchase) => $purchase->expires_at === null)
            ->values();

        if ($withoutExpiry->isNotEmpty()) {
            /** @var Purchase $primary */
            $primary = $withoutExpiry->first();
            $class = $primary->purchasable;

            return [
                'purchase_id' => $primary->id,
                'class_id' => (int) $primary->purchasable_id,
                'expires_at' => null,
                'expires_at_input' => '',
                'class_name' => $class?->name,
                'editable' => true,
                'settable' => true,
                'multiple' => ! $classId && $withoutExpiry->count() > 1,
                'multiple_count' => $withoutExpiry->count(),
                'max_expires_at' => $class?->subscription_ends_at?->format('Y-m-d'),
                'source' => 'purchase',
            ];
        }

        $enrollments = $user->relationLoaded('classEnrollments')
            ? $user->classEnrollments
            : $user->classEnrollments()->approved()->with('schoolClass')->get();

        $enrollments = $enrollments
            ->where('status', 'approved')
            ->when($classId, fn ($collection) => $collection->where('class_id', $classId));

        $enrollment = $enrollments->first();
        $schoolClass = $enrollment?->schoolClass;

        if ($schoolClass?->subscription_ends_at) {
            return [
                'purchase_id' => null,
                'class_id' => $schoolClass->id,
                'expires_at' => $schoolClass->subscription_ends_at,
                'expires_at_input' => $schoolClass->subscription_ends_at->format('Y-m-d'),
                'class_name' => $schoolClass->name,
                'editable' => false,
                'settable' => false,
                'source' => 'class',
            ];
        }

        if ($classId && $enrollment) {
            return [
                'purchase_id' => null,
                'class_id' => $classId,
                'expires_at' => null,
                'expires_at_input' => '',
                'class_name' => $schoolClass?->name,
                'editable' => true,
                'settable' => true,
                'max_expires_at' => $schoolClass?->subscription_ends_at?->format('Y-m-d'),
                'source' => 'enrollment',
            ];
        }

        return [
            'purchase_id' => null,
            'class_id' => null,
            'expires_at' => null,
            'expires_at_input' => '',
            'editable' => false,
            'settable' => false,
            'source' => null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function resolveAllForUser(User $user): array
    {
        $enrollments = $user->relationLoaded('classEnrollments')
            ? $user->classEnrollments->where('status', 'approved')
            : $user->classEnrollments()->approved()->with('schoolClass')->get();

        return $enrollments
            ->sortBy(fn ($enrollment) => $enrollment->schoolClass?->name ?? '')
            ->map(function ($enrollment) use ($user) {
                $classId = (int) $enrollment->class_id;
                $resolved = self::resolveForUser($user, $classId);

                return array_merge($resolved, [
                    'enrollment_id' => $enrollment->id,
                    'class_name' => $enrollment->schoolClass?->name ?? '—',
                ]);
            })
            ->values()
            ->all();
    }
}
