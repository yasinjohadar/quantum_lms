<?php

namespace App\Services;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Purchase;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;

class SubjectPricingService
{
    /**
     * الحصول على السعر الفعلي للمادة مع مراعاة الـ override
     *
     * الأولوية:
     * 1. إذا المادة لديها is_free_override = true → مجانية دائماً
     * 2. إذا الصف مجاني والمادة ليس لديها price_override → مجانية
     * 3. إذا الصف مدفوع → السعر الافتراضي للمادة
     * 4. إذا المادة لديها price_override > 0 → السعر المخصص
     */
    public function getEffectivePrice(Subject $subject, $currencyId = null): float
    {
        if ($subject->is_free_override) {
            return 0;
        }

        $class = $subject->schoolClass;

        if ($class && $class->is_free) {
            return 0;
        }

        return (float) $subject->getPrice($currencyId);
    }

    /**
     * تحديد ما إذا كانت المادة مجانية فعلياً
     */
    public function isEffectivelyFree(Subject $subject): bool
    {
        if ($subject->is_free_override) {
            return true;
        }

        $class = $subject->schoolClass;

        if ($class && $class->is_free) {
            return true;
        }

        $price = $this->getEffectivePrice($subject);

        return $price == 0;
    }

    /**
     * تحديد ما إذا كان يمكن شراء المادة بشكل منفصل
     */
    public function canPurchaseSeparately(Subject $subject): bool
    {
        if (!$subject->can_purchase_separately) {
            return false;
        }

        $class = $subject->schoolClass;

        if ($class && $class->is_free && !$subject->is_free_override) {
            return false;
        }

        if ($this->isEffectivelyFree($subject)) {
            return false;
        }

        return true;
    }

    /**
     * التحقق من وصول المستخدم للمادة
     *
     * المادة تكون متاحة إذا تحقق أحد الشروط:
     * 1. المادة مجانية (is_free_override أو ترث مجانية الصف)
     * 2. المستخدم اشترى الصف بالكامل
     * 3. المستخدم اشترى المادة منفردة
     * 4. المستخدم مسجل في الصف (ClassEnrollment approved)
     * 5. المستخدم مسجل في المادة (Enrollment active)
     */
    public function hasAccess(User $user, Subject $subject): bool
    {
        if ($this->isEffectivelyFree($subject)) {
            return true;
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

    /**
     * التحقق من وصول المستخدم للصف
     */
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

    /**
     * الحصول على نوع الوصول للمادة (للعرض في الواجهة)
     *
     * القيم الممكنة:
     * - free: مجانية
     * - included_in_class: ضمن اشتراك الصف
     * - purchasable: يمكن شراؤها منفردة
     * - requires_class_purchase: تتطلب شراء الصف
     * - no_access: لا يوجد وصول
     */
    public function getAccessType(User $user, Subject $subject): string
    {
        if ($this->isEffectivelyFree($subject)) {
            return 'free';
        }

        if ($this->hasPurchasedSubject($user, $subject)) {
            return 'purchased';
        }

        if ($this->hasPurchasedClass($user, $subject)) {
            return 'included_in_class';
        }

        if ($this->isEnrolledInClass($user, $subject)) {
            return 'included_in_class';
        }

        if ($this->isEnrolledInSubject($user, $subject)) {
            return 'purchased';
        }

        if ($this->canPurchaseSeparately($subject)) {
            return 'purchasable';
        }

        $class = $subject->schoolClass;

        if ($class && !$class->is_free) {
            return 'requires_class_purchase';
        }

        return 'no_access';
    }

    /**
     * الحصول على Badge النص المناسب للمادة
     */
    public function getAccessBadge(Subject $subject, ?User $user = null): array
    {
        if (!$user) {
            if ($this->isEffectivelyFree($subject)) {
                return ['text' => 'مجاني', 'class' => 'bg-success', 'icon' => 'fa-check-circle'];
            }

            return ['text' => 'مدفوع', 'class' => 'bg-warning', 'icon' => 'fa-lock'];
        }

        $accessType = $this->getAccessType($user, $subject);

        return match ($accessType) {
            'free' => ['text' => 'مجاني', 'class' => 'bg-success', 'icon' => 'fa-check-circle'],
            'purchased' => ['text' => 'مشتريات', 'class' => 'bg-primary', 'icon' => 'fa-check'],
            'included_in_class' => ['text' => 'ضمن الاشتراك', 'class' => 'bg-info', 'icon' => 'fa-graduation-cap'],
            'purchasable' => ['text' => 'شراء منفصل', 'class' => 'bg-warning', 'icon' => 'fa-shopping-cart'],
            'requires_class_purchase' => ['text' => 'يتطلب شراء الصف', 'class' => 'bg-secondary', 'icon' => 'fa-lock'],
            default => ['text' => 'مدفوع', 'class' => 'bg-warning', 'icon' => 'fa-lock'],
        };
    }

    /**
     * هل المستخدم اشترى المادة منفردة؟
     */
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

    /**
     * هل المستخدم اشترى الصف الذي يحتوي المادة؟
     */
    private function hasPurchasedClass(User $user, Subject $subject): bool
    {
        $class = $subject->schoolClass;

        if (!$class) {
            return false;
        }

        return $this->hasPurchasedClassDirectly($user, $class);
    }

    /**
     * هل المستخدم اشترى الصف مباشرة؟
     */
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

    /**
     * هل المستخدم مسجل في الصف الذي يحتوي المادة؟
     */
    private function isEnrolledInClass(User $user, Subject $subject): bool
    {
        $class = $subject->schoolClass;

        if (!$class) {
            return false;
        }

        return $this->isEnrolledInClassDirectly($user, $class);
    }

    /**
     * هل المستخدم مسجل في الصف مباشرة؟
     */
    private function isEnrolledInClassDirectly(User $user, SchoolClass $class): bool
    {
        return ClassEnrollment::where('user_id', $user->id)
            ->where('class_id', $class->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * هل المستخدم مسجل في المادة مباشرة؟
     */
    private function isEnrolledInSubject(User $user, Subject $subject): bool
    {
        return Enrollment::where('user_id', $user->id)
            ->where('subject_id', $subject->id)
            ->where('status', 'active')
            ->exists();
    }
}
