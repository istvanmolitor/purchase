<?php

namespace Molitor\Purchase\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_status_id' => $this->purchase_status_id,
            'url' => $this->url,
            'customer_id' => $this->customer_id,
            'warehouse_id' => $this->warehouse_id,
            'comment' => $this->comment,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'expected_delivery_date' => $this->expected_delivery_date?->toDateString(),
            'delivery_date' => $this->delivery_date?->toDateString(),
            'total_price' => $this->total_price !== null ? (float) $this->total_price : null,
            'currency_id' => $this->currency_id,
            'user_id' => $this->user_id,
            'is_closed' => isset($this->is_closed) ? (bool) $this->is_closed : null,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer?->id,
                    'name' => $this->customer?->name,
                    'currency_id' => $this->customer?->currency_id,
                ];
            }),
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency?->id,
                    'code' => $this->currency?->code,
                    'name' => $this->currency?->name,
                ];
            }),
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse?->id,
                    'name' => $this->warehouse?->name,
                ];
            }),
            'purchase_status' => PurchaseStatusResource::make($this->whenLoaded('purchaseStatus')),
            'purchase_items' => PurchaseItemResource::collection($this->whenLoaded('purchaseItems')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}

