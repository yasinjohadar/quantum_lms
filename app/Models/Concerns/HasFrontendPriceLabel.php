<?php

namespace App\Models\Concerns;

use App\Models\Currency;

trait HasFrontendPriceLabel
{
    public const DEFAULT_CUSTOM_PRICE_LABEL = 'مدفوع';

    /**
     * @return array{
     *     mode: 'free'|'hidden'|'label'|'amount',
     *     text: string,
     *     amount: float,
     *     currency_symbol: string,
     *     currency_code: string
     * }
     */
    public function resolveFrontendPricePresentation(
        bool $isEffectivelyFree,
        float $effectivePrice,
        ?Currency $currency = null
    ): array {
        $showPrice = (bool) ($this->show_price ?? true);
        $currencySymbol = $currency?->symbol ?? $currency?->code ?? '';
        $currencyCode = $currency?->code ?? '';

        if ($isEffectivelyFree || $effectivePrice <= 0) {
            return [
                'mode' => 'free',
                'text' => 'مجاني',
                'amount' => 0.0,
                'currency_symbol' => $currencySymbol,
                'currency_code' => $currencyCode,
            ];
        }

        if (! $showPrice) {
            return [
                'mode' => 'hidden',
                'text' => 'تواصل لمعرفة السعر',
                'amount' => $effectivePrice,
                'currency_symbol' => $currencySymbol,
                'currency_code' => $currencyCode,
            ];
        }

        if ($this->use_custom_price_label ?? false) {
            $label = trim((string) ($this->custom_price_label ?? ''));
            if ($label === '') {
                $label = self::DEFAULT_CUSTOM_PRICE_LABEL;
            }

            return [
                'mode' => 'label',
                'text' => $label,
                'amount' => $effectivePrice,
                'currency_symbol' => $currencySymbol,
                'currency_code' => $currencyCode,
            ];
        }

        return [
            'mode' => 'amount',
            'text' => number_format($effectivePrice, 2),
            'amount' => $effectivePrice,
            'currency_symbol' => $currencySymbol,
            'currency_code' => $currencyCode,
        ];
    }

    public function normalizedCustomPriceLabel(): ?string
    {
        if (! ($this->use_custom_price_label ?? false)) {
            return null;
        }

        $label = trim((string) ($this->custom_price_label ?? ''));

        return $label !== '' ? $label : self::DEFAULT_CUSTOM_PRICE_LABEL;
    }
}
