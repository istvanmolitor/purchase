<?php

namespace Molitor\Purchase\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseExtraItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_id' => $this->purchase_id,
            'purchase_extra_item_type_id' => $this->purchase_extra_item_type_id,
            'price' => $this->price !== null ? (float) $this->price : null,
            'comment' => $this->comment,
            'purchase_extra_item_type' => $this->whenLoaded('purchaseExtraItemType', function () {
                return [
                    'id' => $this->purchaseExtraItemType?->id,
                    'name' => $this->purchaseExtraItemType?->name,
                    'description' => $this->purchaseExtraItemType?->description,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
