<?php

namespace App\Support;

use App\Models\Purchase;
use App\Models\SchoolClass;
use App\Models\Subject;
use Carbon\Carbon;

class PurchaseApprovalExpiryDefaults
{
    public static function resolve(Purchase $purchase): array
    {
        $purchase->loadMissing('purchasable');
        $class = self::resolveClass($purchase);
        $classEndsAt = $class?->subscription_ends_at?->copy();

        $fallback = now()->addMonths(3)->endOfDay();

        if ($classEndsAt) {
            $default = $classEndsAt->isPast() ? now()->endOfDay() : $classEndsAt;
        } else {
            $default = $fallback;
        }

        return [
            'class' => $class,
            'class_subscription_ends_at' => $classEndsAt,
            'default_expires_at' => $default,
            'max_expires_at' => $classEndsAt && $classEndsAt->isFuture() ? $classEndsAt : null,
        ];
    }

    private static function resolveClass(Purchase $purchase): ?SchoolClass
    {
        $purchasable = $purchase->purchasable;

        if ($purchasable instanceof SchoolClass) {
            return $purchasable;
        }

        if ($purchasable instanceof Subject) {
            $purchasable->loadMissing('schoolClass');

            return $purchasable->schoolClass;
        }

        return null;
    }
}
