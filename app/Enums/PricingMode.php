<?php

namespace App\Enums;

enum PricingMode: string
{
    case INHERIT = 'inherit';
    case FREE = 'free';
    case PAID = 'paid';
    case SUBSCRIPTION = 'subscription';
    case BUNDLE_ONLY = 'bundle_only';
    case HIDDEN = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::INHERIT => 'يرث من الصف',
            self::FREE => 'مجاني',
            self::PAID => 'مدفوع',
            self::SUBSCRIPTION => 'يتطلب اشتراك',
            self::BUNDLE_ONLY => 'ضمن الباقة فقط',
            self::HIDDEN => 'مخفي',
        };
    }

    public function isFree(): bool
    {
        return in_array($this, [self::FREE, self::INHERIT], true);
    }

    public function isPurchasable(): bool
    {
        return in_array($this, [self::PAID, self::SUBSCRIPTION], true);
    }

    public function isVisible(): bool
    {
        return $this !== self::HIDDEN;
    }

    public function requiresPurchase(): bool
    {
        return in_array($this, [self::PAID, self::SUBSCRIPTION, self::BUNDLE_ONLY], true);
    }

    public static function default(): self
    {
        return self::INHERIT;
    }

    public static function fromLegacy(bool $isFree, bool $canPurchaseSeparately): self
    {
        if ($isFree && !$canPurchaseSeparately) {
            return self::FREE;
        }

        if (!$isFree && $canPurchaseSeparately) {
            return self::PAID;
        }

        if ($isFree && $canPurchaseSeparately) {
            return self::INHERIT;
        }

        return self::BUNDLE_ONLY;
    }
}
