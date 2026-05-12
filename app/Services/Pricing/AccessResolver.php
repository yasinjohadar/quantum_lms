<?php

namespace App\Services\Pricing;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Purchase;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\DataTransferObjects\SubjectAccessData;
use App\DataTransferObjects\ClassAccessData;
use App\Enums\PricingMode;
use App\Models\Currency;
use Illuminate\Support\Collection;

class AccessResolver
{
    public function hasSubjectAccess(User $user, Subject $subject): bool
    {
        $pricingMode = $this->resolveSubjectPricingMode($subject);

        if ($pricingMode === PricingMode::FREE) {
            return true;
        }

        if ($pricingMode === PricingMode::INHERIT) {
            $class = $subject->schoolClass;
            if ($class && $class->is_free) {
                return true;
            }
        }

        if ($pricingMode === PricingMode::HIDDEN) {
            return false;
        }

        if ($this->hasPurchasedSubject($user, $subject)) {
            return true;
        }

        if ($this->hasPurchasedClass($user, $subject)) {
            return true;
        }

        if ($this->isEnrolledInClass($user, $subject)) {
            return true;
        }

        if ($this->isEnrolledInSubject($user, $subject)) {
            return true;
        }

        return false;
    }

    public function hasClassAccess(User $user, SchoolClass $class): bool
    {
        if ($class->is_free) {
            return true;
        }

        if ($this->hasPurchasedClassDirectly($user, $class)) {
            return true;
        }

        if ($this->isEnrolledInClassDirectly($user, $class)) {
            return true;
        }

        return false;
    }

    public function canPurchaseSubject(User $user, Subject $subject): bool
    {
        $pricingMode = $this->resolveSubjectPricingMode($subject);

        if (!$pricingMode->isPurchasable()) {
            return false;
        }

        if ($this->hasSubjectAccess($user, $subject)) {
            return false;
        }

        $class = $subject->schoolClass;
        if ($class && $class->is_free && $pricingMode === PricingMode::INHERIT) {
            return false;
        }

        return true;
    }

    public function getSubjectAccessType(User $user, Subject $subject): string
    {
        $pricingMode = $this->resolveSubjectPricingMode($subject);

        if ($pricingMode === PricingMode::HIDDEN) {
            return 'hidden';
        }

        if ($this->hasSubjectAccess($user, $subject)) {
            if ($pricingMode === PricingMode::FREE || $this->isSubjectInherentlyFree($subject)) {
                return 'free';
            }

            if ($this->hasPurchasedSubject($user, $subject)) {
                return 'purchased';
            }

            if ($this->hasPurchasedClass($user, $subject) || $this->isEnrolledInClass($user, $subject)) {
                return 'included_in_class';
            }

            return 'enrolled';
        }

        if ($pricingMode === PricingMode::PAID) {
            return 'purchasable';
        }

        if ($pricingMode === PricingMode::SUBSCRIPTION) {
            return 'requires_subscription';
        }

        if ($pricingMode === PricingMode::BUNDLE_ONLY) {
            return 'bundle_only';
        }

        $class = $subject->schoolClass;
        if ($class && !$class->is_free) {
            return 'requires_class_purchase';
        }

        return 'no_access';
    }

    public function getClassAccessType(User $user, SchoolClass $class): string
    {
        if ($class->is_free) {
            return 'free';
        }

        if ($this->hasPurchasedClassDirectly($user, $class)) {
            return 'purchased';
        }

        if ($this->isEnrolledInClassDirectly($user, $class)) {
            return 'enrolled';
        }

        return 'purchasable';
    }

    public function getSubjectBadge(Subject $subject, ?User $user = null): array
    {
        $pricingMode = $this->resolveSubjectPricingMode($subject);

        if ($pricingMode === PricingMode::HIDDEN) {
            return ['text' => 'مخفي', 'class' => 'bg-secondary', 'icon' => 'fa-eye-slash'];
        }

        if (!$user) {
            if ($this->isSubjectInherentlyFree($subject)) {
                return ['text' => 'مجاني', 'class' => 'bg-success', 'icon' => 'fa-check-circle'];
            }

            return ['text' => 'مدفوع', 'class' => 'bg-warning', 'icon' => 'fa-lock'];
        }

        $accessType = $this->getSubjectAccessType($user, $subject);

        return match ($accessType) {
            'free' => ['text' => 'مجاني', 'class' => 'bg-success', 'icon' => 'fa-check-circle'],
            'purchased' => ['text' => 'مشتريات', 'class' => 'bg-primary', 'icon' => 'fa-check'],
            'included_in_class' => ['text' => 'ضمن الاشتراك', 'class' => 'bg-info', 'icon' => 'fa-graduation-cap'],
            'enrolled' => ['text' => 'مسجل', 'class' => 'bg-success', 'icon' => 'fa-check-circle'],
            'purchasable' => ['text' => 'شراء منفصل', 'class' => 'bg-warning', 'icon' => 'fa-shopping-cart'],
            'requires_subscription' => ['text' => 'يتطلب اشتراك', 'class' => 'bg-danger', 'icon' => 'fa-key'],
            'bundle_only' => ['text' => 'ضمن الباقة', 'class' => 'bg-secondary', 'icon' => 'fa-box'],
            'requires_class_purchase' => ['text' => 'يتطلب شراء الصف', 'class' => 'bg-secondary', 'icon' => 'fa-lock'],
            default => ['text' => 'مدفوع', 'class' => 'bg-warning', 'icon' => 'fa-lock'],
        };
    }

    public function getClassBadge(SchoolClass $class, ?User $user = null): array
    {
        if ($class->is_free) {
            return ['text' => 'مجاني', 'class' => 'bg-success', 'icon' => 'fa-check-circle'];
        }

        if (!$user) {
            return ['text' => 'مدفوع', 'class' => 'bg-warning', 'icon' => 'fa-lock'];
        }

        $accessType = $this->getClassAccessType($user, $class);

        return match ($accessType) {
            'free' => ['text' => 'مجاني', 'class' => 'bg-success', 'icon' => 'fa-check-circle'],
            'purchased' => ['text' => 'مشتريات', 'class' => 'bg-primary', 'icon' => 'fa-check'],
            'enrolled' => ['text' => 'مسجل', 'class' => 'bg-success', 'icon' => 'fa-check-circle'],
            default => ['text' => 'مدفوع', 'class' => 'bg-warning', 'icon' => 'fa-lock'],
        };
    }

    private function resolveSubjectPricingMode(Subject $subject): PricingMode
    {
        if (isset($subject->pricing_mode) && !empty($subject->pricing_mode)) {
            return PricingMode::from($subject->pricing_mode);
        }

        return PricingMode::fromLegacy(
            $subject->is_free_override ?? false,
            $subject->can_purchase_separately ?? true
        );
    }

    private function isSubjectInherentlyFree(Subject $subject): bool
    {
        $pricingMode = $this->resolveSubjectPricingMode($subject);

        if ($pricingMode === PricingMode::FREE) {
            return true;
        }

        if ($pricingMode === PricingMode::INHERIT) {
            $class = $subject->schoolClass;
            return $class && $class->is_free;
        }

        return false;
    }

    private function hasPurchasedSubject(User $user, Subject $subject): bool
    {
        $purchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', Subject::class)
            ->where('purchasable_id', $subject->id)
            ->where('status', 'completed')
            ->first();

        if (!$purchase) {
            return false;
        }

        if ($purchase->expires_at && $purchase->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    private function hasPurchasedClass(User $user, Subject $subject): bool
    {
        $class = $subject->schoolClass;
        return $class && $this->hasPurchasedClassDirectly($user, $class);
    }

    private function hasPurchasedClassDirectly(User $user, SchoolClass $class): bool
    {
        $purchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', SchoolClass::class)
            ->where('purchasable_id', $class->id)
            ->where('status', 'completed')
            ->first();

        if (!$purchase) {
            return false;
        }

        if ($purchase->expires_at && $purchase->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    private function isEnrolledInClass(User $user, Subject $subject): bool
    {
        $class = $subject->schoolClass;
        return $class && $this->isEnrolledInClassDirectly($user, $class);
    }

    private function isEnrolledInClassDirectly(User $user, SchoolClass $class): bool
    {
        return ClassEnrollment::where('user_id', $user->id)
            ->where('class_id', $class->id)
            ->where('status', 'approved')
            ->exists();
    }

    private function isEnrolledInSubject(User $user, Subject $subject): bool
    {
        return Enrollment::where('user_id', $user->id)
            ->where('subject_id', $subject->id)
            ->where('status', 'active')
            ->exists();
    }
}
