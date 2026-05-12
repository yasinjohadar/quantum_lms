<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectAccessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof \App\DataTransferObjects\SubjectAccessData) {
            return $this->resource->toArray();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'pricing_mode' => $this->pricing_mode ?? 'inherit',
            'can_access' => $this->whenLoaded('accessData', fn () => $this->accessData['can_access'] ?? false),
            'can_purchase' => $this->whenLoaded('accessData', fn () => $this->accessData['can_purchase'] ?? false),
            'effective_price' => $this->whenLoaded('accessData', fn () => $this->accessData['effective_price'] ?? 0),
            'display_price' => $this->whenLoaded('accessData', fn () => $this->accessData['display_price'] ?? null),
            'access_type' => $this->whenLoaded('accessData', fn () => $this->accessData['access_type'] ?? 'no_access'),
            'badge' => $this->whenLoaded('accessData', fn () => $this->accessData['badge'] ?? []),
            'show_price' => $this->show_price ?? true,
            'currency' => $this->whenLoaded('defaultCurrency', function () {
                if (!$this->defaultCurrency) {
                    return null;
                }
                return [
                    'code' => $this->defaultCurrency->code,
                    'symbol' => $this->defaultCurrency->symbol,
                    'name' => $this->defaultCurrency->name,
                ];
            }),
            'old_price' => $this->whenLoaded('accessData', fn () => $this->accessData['old_price'] ?? 0),
            'is_effectively_free' => $this->whenLoaded('accessData', fn () => $this->accessData['is_effectively_free'] ?? false),
        ];
    }
}
