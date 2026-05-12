<?php

namespace App\DataTransferObjects;

use App\Enums\PricingMode;
use App\Models\Currency;
use Illuminate\Support\Collection;

readonly class ClassAccessData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $image,
        public PricingMode $pricingMode,
        public bool $canAccess,
        public bool $canPurchase,
        public float $effectivePrice,
        public ?string $displayPrice,
        public string $accessType,
        public array $badge,
        public bool $showPrice,
        public ?Currency $currency,
        public float $oldPrice,
        public bool $isEffectivelyFree,
        public int $subjectsCount,
        public Collection $subjects,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'pricing_mode' => $this->pricingMode->value,
            'pricing_mode_label' => $this->pricingMode->label(),
            'can_access' => $this->canAccess,
            'can_purchase' => $this->canPurchase,
            'effective_price' => $this->effectivePrice,
            'display_price' => $this->displayPrice,
            'access_type' => $this->accessType,
            'badge' => $this->badge,
            'show_price' => $this->showPrice,
            'currency' => $this->currency ? [
                'code' => $this->currency->code,
                'symbol' => $this->currency->symbol,
                'name' => $this->currency->name,
            ] : null,
            'old_price' => $this->oldPrice,
            'is_effectively_free' => $this->isEffectivelyFree,
            'subjects_count' => $this->subjectsCount,
            'subjects' => $this->subjects->map(fn ($s) => $s instanceof SubjectAccessData ? $s->toArray() : $s)->values()->all(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
