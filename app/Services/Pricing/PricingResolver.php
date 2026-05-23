<?php

namespace App\Services\Pricing;

use App\DataTransferObjects\ClassAccessData;
use App\DataTransferObjects\SubjectAccessData;
use App\Enums\PricingMode;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Currency;

class PricingResolver
{
    public function __construct(
        protected SubjectPricingResolver $subjectPricingResolver,
        protected ClassPricingResolver $classPricingResolver,
        protected AccessResolver $accessResolver,
    ) {
    }

    public function resolveSubject(Subject $subject, $currencyId = null): array
    {
        $price = $this->subjectPricingResolver->getEffectivePrice($subject, $currencyId);
        $currency = $this->getCurrency($subject, $currencyId);
        $pricingMode = $this->resolveSubjectPricingMode($subject);
        $isFree = $this->subjectPricingResolver->isEffectivelyFree($subject);

        return [
            'price' => $price,
            'currency' => $currency,
            'pricing_mode' => $pricingMode,
            'is_free' => $isFree,
            'old_price' => $price > 0 ? $price * 1.2 : 0,
        ];
    }

    public function resolveClass(SchoolClass $class, $currencyId = null): array
    {
        $price = $this->classPricingResolver->getEffectivePrice($class, $currencyId);
        $currency = $this->getClassCurrency($class, $currencyId);
        $pricingMode = $class->is_free ? PricingMode::FREE : PricingMode::PAID;
        $isFree = $class->is_free || $price == 0;

        return [
            'price' => $price,
            'currency' => $currency,
            'pricing_mode' => $pricingMode,
            'is_free' => $isFree,
            'old_price' => $price > 0 ? $price * 1.2 : 0,
        ];
    }

    public function resolveSubjectAccessData(
        Subject $subject,
        ?\App\Models\User $user = null,
        $currencyId = null
    ): SubjectAccessData {
        $pricingMode = $this->resolveSubjectPricingMode($subject);
        $price = $this->subjectPricingResolver->getEffectivePrice($subject, $currencyId);
        $currency = $this->getCurrency($subject, $currencyId);
        $isFree = $this->subjectPricingResolver->isEffectivelyFree($subject);
        $showPrice = $subject->show_price ?? true;

        $canAccess = $user ? $this->accessResolver->hasSubjectAccess($user, $subject) : $isFree;
        $canPurchase = $user ? $this->accessResolver->canPurchaseSubject($user, $subject) : false;
        $canPurchaseSeparately = ($subject->can_purchase_separately ?? true)
            && $this->subjectPricingResolver->canPurchaseSeparately($subject);
        $accessType = $user ? $this->accessResolver->getSubjectAccessType($user, $subject) : ($isFree ? 'free' : 'requires_purchase');
        $badge = $this->accessResolver->getSubjectBadge($subject, $user);

        $pricePresentation = $subject->resolveFrontendPricePresentation($isFree, $price, $currency);
        $displayPrice = $this->displayPriceFromPresentation($pricePresentation);

        return new SubjectAccessData(
            id: $subject->id,
            name: $subject->name,
            slug: $subject->slug,
            image: $subject->image,
            pricingMode: $pricingMode,
            canAccess: $canAccess,
            canPurchase: $canPurchase,
            canPurchaseSeparately: $canPurchaseSeparately,
            effectivePrice: $price,
            displayPrice: $displayPrice,
            accessType: $accessType,
            badge: $badge,
            showPrice: $showPrice,
            currency: $currency,
            oldPrice: $price > 0 ? $price * 1.2 : 0,
            isEffectivelyFree: $isFree,
            priceDisplayMode: $pricePresentation['mode'],
        );
    }

    public function resolveClassAccessData(
        SchoolClass $class,
        ?\App\Models\User $user = null,
        $currencyId = null
    ): ClassAccessData {
        $pricingMode = $class->is_free ? PricingMode::FREE : PricingMode::PAID;
        $price = $this->classPricingResolver->getEffectivePrice($class, $currencyId);
        $currency = $this->getClassCurrency($class, $currencyId);
        $isFree = $class->is_free || $price == 0;
        $showPrice = $class->show_price ?? true;

        $canAccess = $user ? $this->accessResolver->hasClassAccess($user, $class) : $isFree;
        $canPurchase = $user ? !$canAccess && !$class->is_free : !$class->is_free;
        $accessType = $user ? $this->accessResolver->getClassAccessType($user, $class) : ($isFree ? 'free' : 'purchasable');
        $badge = $this->accessResolver->getClassBadge($class, $user);

        $pricePresentation = $class->resolveFrontendPricePresentation($isFree, $price, $currency);
        $displayPrice = $this->displayPriceFromPresentation($pricePresentation);

        $subjects = $class->subjects()
            ->active()
            ->ordered()
            ->get()
            ->map(fn ($s) => $this->resolveSubjectAccessData($s, $user, $currencyId));

        return new ClassAccessData(
            id: $class->id,
            name: $class->name,
            slug: $class->slug,
            image: $class->image,
            pricingMode: $pricingMode,
            canAccess: $canAccess,
            canPurchase: $canPurchase,
            effectivePrice: $price,
            displayPrice: $displayPrice,
            accessType: $accessType,
            badge: $badge,
            showPrice: $showPrice,
            currency: $currency,
            oldPrice: $price > 0 ? $price * 1.2 : 0,
            isEffectivelyFree: $isFree,
            subjectsCount: $subjects->count(),
            subjects: $subjects,
        );
    }

    public function resolveBulkSubjectAccessData(
        \Illuminate\Support\Collection $subjects,
        ?\App\Models\User $user = null,
        $currencyId = null
    ): \Illuminate\Support\Collection {
        return $subjects->map(fn ($s) => $this->resolveSubjectAccessData($s, $user, $currencyId));
    }

    private function displayPriceFromPresentation(array $presentation): ?string
    {
        return match ($presentation['mode']) {
            'label' => $presentation['text'],
            'amount' => trim($presentation['text'].' '.($presentation['currency_symbol'] ?: $presentation['currency_code'])),
            default => null,
        };
    }

    private function resolveSubjectPricingMode(Subject $subject): PricingMode
    {
        return $this->subjectPricingResolver->resolvePricingMode($subject);
    }

    private function getCurrency(Subject $subject, $currencyId = null): ?Currency
    {
        if ($currencyId) {
            return Currency::find($currencyId);
        }

        return $subject->defaultCurrency ?? Currency::getDefault();
    }

    private function getClassCurrency(SchoolClass $class, $currencyId = null): ?Currency
    {
        if ($currencyId) {
            return Currency::find($currencyId);
        }

        return $class->defaultCurrency ?? Currency::getDefault();
    }
}
