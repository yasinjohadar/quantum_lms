<?php

namespace App\Services\Pricing;

use App\Enums\PricingMode;
use App\Models\Subject;

class SubjectPricingResolver
{
    public function getEffectivePrice(Subject $subject, $currencyId = null): float
    {
        $pricingMode = $this->resolvePricingMode($subject);

        if ($pricingMode === PricingMode::FREE) {
            return 0.0;
        }

        if ($pricingMode === PricingMode::INHERIT) {
            $class = $subject->schoolClass;
            if ($class && $class->is_free) {
                return 0.0;
            }
        }

        if ($pricingMode === PricingMode::HIDDEN) {
            return 0.0;
        }

        return (float) $subject->getPrice($currencyId);
    }

    public function isEffectivelyFree(Subject $subject): bool
    {
        $pricingMode = $this->resolvePricingMode($subject);

        if ($pricingMode === PricingMode::FREE) {
            return true;
        }

        if ($pricingMode === PricingMode::INHERIT) {
            $class = $subject->schoolClass;
            if ($class && $class->is_free) {
                return true;
            }
        }

        $price = $this->getEffectivePrice($subject);

        return $price == 0;
    }

    public function canPurchaseSeparately(Subject $subject): bool
    {
        $pricingMode = $this->resolvePricingMode($subject);

        if (!$pricingMode->isPurchasable()) {
            return false;
        }

        if ($this->isEffectivelyFree($subject)) {
            return false;
        }

        if ($pricingMode === PricingMode::BUNDLE_ONLY) {
            return false;
        }

        return true;
    }

    public function resolvePricingMode(Subject $subject): PricingMode
    {
        if (isset($subject->pricing_mode) && !empty($subject->pricing_mode)) {
            return PricingMode::from($subject->pricing_mode);
        }

        return PricingMode::fromLegacy(
            $subject->is_free_override ?? false,
            $subject->can_purchase_separately ?? true
        );
    }
}
