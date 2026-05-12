<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Enums\PricingMode;

class PricingModeTest extends TestCase
{
    public function test_pricing_mode_labels(): void
    {
        $this->assertEquals('يرث من الصف', PricingMode::INHERIT->label());
        $this->assertEquals('مجاني', PricingMode::FREE->label());
        $this->assertEquals('مدفوع', PricingMode::PAID->label());
        $this->assertEquals('يتطلب اشتراك', PricingMode::SUBSCRIPTION->label());
        $this->assertEquals('ضمن الباقة فقط', PricingMode::BUNDLE_ONLY->label());
        $this->assertEquals('مخفي', PricingMode::HIDDEN->label());
    }

    public function test_is_free(): void
    {
        $this->assertTrue(PricingMode::FREE->isFree());
        $this->assertTrue(PricingMode::INHERIT->isFree());
        $this->assertFalse(PricingMode::PAID->isFree());
        $this->assertFalse(PricingMode::SUBSCRIPTION->isFree());
        $this->assertFalse(PricingMode::BUNDLE_ONLY->isFree());
        $this->assertFalse(PricingMode::HIDDEN->isFree());
    }

    public function test_is_purchasable(): void
    {
        $this->assertTrue(PricingMode::PAID->isPurchasable());
        $this->assertTrue(PricingMode::SUBSCRIPTION->isPurchasable());
        $this->assertFalse(PricingMode::FREE->isPurchasable());
        $this->assertFalse(PricingMode::INHERIT->isPurchasable());
        $this->assertFalse(PricingMode::BUNDLE_ONLY->isPurchasable());
        $this->assertFalse(PricingMode::HIDDEN->isPurchasable());
    }

    public function test_is_visible(): void
    {
        $this->assertFalse(PricingMode::HIDDEN->isVisible());
        $this->assertTrue(PricingMode::FREE->isVisible());
        $this->assertTrue(PricingMode::PAID->isVisible());
        $this->assertTrue(PricingMode::INHERIT->isVisible());
    }

    public function test_requires_purchase(): void
    {
        $this->assertTrue(PricingMode::PAID->requiresPurchase());
        $this->assertTrue(PricingMode::SUBSCRIPTION->requiresPurchase());
        $this->assertTrue(PricingMode::BUNDLE_ONLY->requiresPurchase());
        $this->assertFalse(PricingMode::FREE->requiresPurchase());
        $this->assertFalse(PricingMode::INHERIT->requiresPurchase());
        $this->assertFalse(PricingMode::HIDDEN->requiresPurchase());
    }

    public function test_default(): void
    {
        $this->assertEquals(PricingMode::INHERIT, PricingMode::default());
    }

    public function test_from_legacy_free_and_no_separate_purchase(): void
    {
        $mode = PricingMode::fromLegacy(true, false);
        $this->assertEquals(PricingMode::FREE, $mode);
    }

    public function test_from_legacy_not_free_and_separate_purchase(): void
    {
        $mode = PricingMode::fromLegacy(false, true);
        $this->assertEquals(PricingMode::PAID, $mode);
    }

    public function test_from_legacy_free_and_separate_purchase(): void
    {
        $mode = PricingMode::fromLegacy(true, true);
        $this->assertEquals(PricingMode::INHERIT, $mode);
    }

    public function test_from_legacy_not_free_and_no_separate_purchase(): void
    {
        $mode = PricingMode::fromLegacy(false, false);
        $this->assertEquals(PricingMode::BUNDLE_ONLY, $mode);
    }
}
