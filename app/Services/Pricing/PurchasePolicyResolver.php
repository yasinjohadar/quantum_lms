<?php

namespace App\Services\Pricing;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Purchase;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Enums\PricingMode;

class PurchasePolicyResolver
{
    public function __construct(
        protected AccessResolver $accessResolver,
        protected SubjectPricingResolver $subjectPricingResolver,
        protected ClassPricingResolver $classPricingResolver,
    ) {
    }

    public function canPurchaseClass(User $user, SchoolClass $class): bool
    {
        if (!$class->is_active) {
            return false;
        }

        if ($class->is_free) {
            return false;
        }

        if ($this->accessResolver->hasClassAccess($user, $class)) {
            return false;
        }

        return true;
    }

    public function canPurchaseSubject(User $user, Subject $subject): bool
    {
        if (!$subject->is_active) {
            return false;
        }

        if (! $this->subjectPricingResolver->canPurchaseSeparately($subject)) {
            return false;
        }

        if ($this->accessResolver->hasSubjectAccess($user, $subject)) {
            return false;
        }

        return true;
    }

    public function canAccessSubject(User $user, Subject $subject): bool
    {
        if (!$subject->is_active) {
            return false;
        }

        return $this->accessResolver->hasSubjectAccess($user, $subject);
    }

    public function canAccessClass(User $user, SchoolClass $class): bool
    {
        if (!$class->is_active) {
            return false;
        }

        return $this->accessResolver->hasClassAccess($user, $class);
    }

    public function getPurchaseRestrictions(User $user, Subject $subject): array
    {
        $restrictions = [];

        if (!$subject->is_active) {
            $restrictions[] = 'المادة غير نشطة حالياً';
        }

        $pricingMode = $this->subjectPricingResolver->resolvePricingMode($subject);

        if ($pricingMode === PricingMode::HIDDEN) {
            $restrictions[] = 'المادة مخفية ولا يمكن الوصول إليها';
        }

        if ($pricingMode === PricingMode::SUBSCRIPTION) {
            $restrictions[] = 'المادة تتطلب اشتراكاً فعالاً';
        }

        if ($pricingMode === PricingMode::BUNDLE_ONLY) {
            $restrictions[] = 'المادة متاحة فقط ضمن باقة كاملة';
        }

        if ($this->accessResolver->hasSubjectAccess($user, $subject)) {
            $restrictions[] = 'لديك وصول بالفعل لهذه المادة';
        }

        return $restrictions;
    }

    public function getPurchaseRestrictionsForClass(User $user, SchoolClass $class): array
    {
        $restrictions = [];

        if (!$class->is_active) {
            $restrictions[] = 'الصف غير نشط حالياً';
        }

        if ($class->is_free) {
            $restrictions[] = 'الصف مجاني ولا يحتاج شراء';
        }

        if ($this->accessResolver->hasClassAccess($user, $class)) {
            $restrictions[] = 'لديك وصول بالفعل لهذا الصف';
        }

        return $restrictions;
    }

    public function hasActiveSubscription(User $user, SchoolClass $class): bool
    {
        $subscription = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', SchoolClass::class)
            ->where('purchasable_id', $class->id)
            ->where('status', 'completed')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->first();

        return $subscription !== null;
    }

    public function hasTimeLimitedAccess(User $user, Subject $subject): bool
    {
        $purchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', Subject::class)
            ->where('purchasable_id', $subject->id)
            ->where('status', 'completed')
            ->whereNotNull('expires_at')
            ->first();

        if (!$purchase) {
            return false;
        }

        return !$purchase->expires_at->isPast();
    }

    public function hasTimeLimitedClassAccess(User $user, SchoolClass $class): bool
    {
        $purchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', SchoolClass::class)
            ->where('purchasable_id', $class->id)
            ->where('status', 'completed')
            ->whereNotNull('expires_at')
            ->first();

        if (!$purchase) {
            return false;
        }

        return !$purchase->expires_at->isPast();
    }
}
