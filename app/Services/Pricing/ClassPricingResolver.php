<?php

namespace App\Services\Pricing;

use App\Models\SchoolClass;

class ClassPricingResolver
{
    public function getEffectivePrice(SchoolClass $class, $currencyId = null): float
    {
        if ($class->is_free) {
            return 0.0;
        }

        return (float) $class->getPrice($currencyId);
    }

    public function isEffectivelyFree(SchoolClass $class): bool
    {
        if ($class->is_free) {
            return true;
        }

        $price = $this->getEffectivePrice($class);

        return $price == 0;
    }

    public function canPurchase(SchoolClass $class): bool
    {
        return !$class->is_free && $this->getEffectivePrice($class) > 0;
    }
}
