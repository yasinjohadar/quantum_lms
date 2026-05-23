<?php

namespace App\Support;

use App\Models\Concerns\HasFrontendPriceLabel;
use Illuminate\Http\Request;

class AdminCustomPriceLabelInput
{
    public static function merge(array $data, Request $request, bool $effectivelyFree = false): array
    {
        $isFree = $effectivelyFree || (bool) ($data['is_free'] ?? false);
        $showPrice = (bool) ($data['show_price'] ?? true);

        if ($isFree || ! $showPrice) {
            $data['use_custom_price_label'] = false;
            $data['custom_price_label'] = null;

            return $data;
        }

        $useCustom = $request->boolean('use_custom_price_label');
        $data['use_custom_price_label'] = $useCustom;

        if ($useCustom) {
            $label = trim((string) $request->input('custom_price_label', ''));
            $data['custom_price_label'] = $label !== ''
                ? $label
                : HasFrontendPriceLabel::DEFAULT_CUSTOM_PRICE_LABEL;
        } else {
            $data['custom_price_label'] = null;
        }

        return $data;
    }
}
