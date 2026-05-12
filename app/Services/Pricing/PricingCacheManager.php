<?php

namespace App\Services\Pricing;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\DataTransferObjects\SubjectAccessData;
use App\DataTransferObjects\ClassAccessData;
use Illuminate\Support\Facades\Cache;

class PricingCacheManager
{
    const CLASS_ACCESS_TTL = 3600;
    const SUBJECT_ACCESS_TTL = 3600;
    const CLASS_LIST_TTL = 1800;
    const SUBJECT_LIST_TTL = 1800;

    public function getClassAccessData(
        SchoolClass $class,
        ?User $user,
        callable $resolver,
        $currencyId = null
    ): ClassAccessData {
        $cacheKey = $this->getClassAccessKey($class, $user, $currencyId);
        $tags = $this->getClassTags($class);

        return Cache::tags($tags)->remember(
            $cacheKey,
            self::CLASS_ACCESS_TTL,
            $resolver
        );
    }

    public function getSubjectAccessData(
        Subject $subject,
        ?User $user,
        callable $resolver,
        $currencyId = null
    ): SubjectAccessData {
        $cacheKey = $this->getSubjectAccessKey($subject, $user, $currencyId);
        $tags = $this->getSubjectTags($subject);

        return Cache::tags($tags)->remember(
            $cacheKey,
            self::SUBJECT_ACCESS_TTL,
            $resolver
        );
    }

    public function getSubjectsAccessData(
        SchoolClass $class,
        ?User $user,
        callable $resolver,
        $currencyId = null
    ): array {
        $cacheKey = $this->getSubjectsListKey($class, $user, $currencyId);
        $tags = array_merge($this->getClassTags($class), ['subjects']);

        return Cache::tags($tags)->remember(
            $cacheKey,
            self::SUBJECT_LIST_TTL,
            $resolver
        );
    }

    public function invalidateClass(SchoolClass $class): void
    {
        $tags = $this->getClassTags($class);
        Cache::tags($tags)->flush();
    }

    public function invalidateSubject(Subject $subject): void
    {
        $tags = $this->getSubjectTags($subject);
        Cache::tags($tags)->flush();
    }

    public function invalidateUserAccess(User $user): void
    {
        Cache::tags(['user_access_' . $user->id])->flush();
    }

    public function invalidateGlobalPricing(): void
    {
        Cache::tags(['pricing', 'classes', 'subjects'])->flush();
    }

    public function invalidateOnPurchase(User $user, $purchasable): void
    {
        $this->invalidateUserAccess($user);

        if ($purchasable instanceof SchoolClass) {
            $this->invalidateClass($purchasable);
        } elseif ($purchasable instanceof Subject) {
            $this->invalidateSubject($purchasable);
            if ($purchasable->schoolClass) {
                $this->invalidateClass($purchasable->schoolClass);
            }
        }
    }

    public function invalidateOnPriceChange($model): void
    {
        if ($model instanceof SchoolClass) {
            $this->invalidateClass($model);
        } elseif ($model instanceof Subject) {
            $this->invalidateSubject($model);
        }
    }

    public function invalidateOnPricingModeChange(Subject $subject): void
    {
        $this->invalidateSubject($subject);
        if ($subject->schoolClass) {
            $this->invalidateClass($subject->schoolClass);
        }
    }

    private function getClassAccessKey(SchoolClass $class, ?User $user, $currencyId = null): string
    {
        $userId = $user ? $user->id : 'guest';
        $currency = $currencyId ?? 'default';

        return "class_access:{$class->id}:user_{$userId}:currency_{$currency}";
    }

    private function getSubjectAccessKey(Subject $subject, ?User $user, $currencyId = null): string
    {
        $userId = $user ? $user->id : 'guest';
        $currency = $currencyId ?? 'default';

        return "subject_access:{$subject->id}:user_{$userId}:currency_{$currency}";
    }

    private function getSubjectsListKey(SchoolClass $class, ?User $user, $currencyId = null): string
    {
        $userId = $user ? $user->id : 'guest';
        $currency = $currencyId ?? 'default';

        return "subjects_list:{$class->id}:user_{$userId}:currency_{$currency}";
    }

    private function getClassTags(SchoolClass $class): array
    {
        return [
            'pricing',
            'classes',
            'class_' . $class->id,
        ];
    }

    private function getSubjectTags(Subject $subject): array
    {
        $tags = [
            'pricing',
            'subjects',
            'subject_' . $subject->id,
        ];

        if ($subject->class_id) {
            $tags[] = 'class_' . $subject->class_id;
        }

        return $tags;
    }
}
