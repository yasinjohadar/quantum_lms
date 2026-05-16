<?php

namespace App\Services\Pricing;

use App\Enums\PricingMode;
use App\Models\Subject;

class SubjectPricingResolver
{
    /**
     * Whether enrolling in / purchasing the class should auto-create subject enrollments for this subject.
     * Subjects with a positive effective price are sold separately and must not be bulk-enrolled with the class.
     */
    public function isIncludedInClassBundle(Subject $subject, $currencyId = null): bool
    {
        $mode = $this->resolvePricingMode($subject);

        if ($mode === PricingMode::HIDDEN) {
            return false;
        }

        if ($mode === PricingMode::FREE) {
            $subject->loadMissing('schoolClass');
            if ($subject->schoolClass && ! $subject->schoolClass->is_free) {
                return false;
            }
        }

        if ($this->getEffectivePrice($subject, $currencyId) > 0) {
            return false;
        }

        return true;
    }

    public function getEffectivePrice(Subject $subject, $currencyId = null): float
    {
        $pricingMode = $this->resolvePricingMode($subject);

        if ($pricingMode === PricingMode::FREE) {
            return 0.0;
        }

        if ($pricingMode === PricingMode::INHERIT) {
            $subject->loadMissing('schoolClass');
            $class = $subject->schoolClass;
            if ($class && $class->is_free) {
                $explicitPrice = (float) $subject->getPrice($currencyId);

                return $explicitPrice > 0 ? $explicitPrice : 0.0;
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

        if ($pricingMode === PricingMode::HIDDEN) {
            return true;
        }

        return $this->getEffectivePrice($subject) == 0;
    }

    public function canPurchaseSeparately(Subject $subject): bool
    {
        $pricingMode = $this->resolvePricingMode($subject);

        if ($pricingMode === PricingMode::HIDDEN || $pricingMode === PricingMode::BUNDLE_ONLY) {
            return false;
        }

        if ($pricingMode === PricingMode::FREE) {
            return false;
        }

        if ($this->isEffectivelyFree($subject)) {
            return false;
        }

        if (! ($subject->can_purchase_separately ?? true)) {
            return false;
        }

        if ($pricingMode->isPurchasable()) {
            return true;
        }

        if ($pricingMode === PricingMode::INHERIT) {
            return $this->getEffectivePrice($subject) > 0;
        }

        return false;
    }

    public function resolvePricingMode(Subject $subject): PricingMode
    {
        if ($subject->is_free_override ?? false) {
            return PricingMode::FREE;
        }

        if (isset($subject->pricing_mode) && ! empty($subject->pricing_mode)) {
            return PricingMode::from($subject->pricing_mode);
        }

        return PricingMode::fromLegacy(
            $subject->is_free_override ?? false,
            $subject->can_purchase_separately ?? true
        );
    }
}
