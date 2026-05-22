<?php

namespace Molitor\Purchase\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Molitor\Purchase\Models\Purchase;
use Molitor\Stock\Enums\StockMovementType;
use Molitor\Stock\Models\StockMovement;
use Molitor\Stock\Models\WarehouseRegion;

class PurchaseService
{
    public function closePurchase(Purchase $purchase, Carbon $deliveryDate): Purchase
    {
        $warehouseRegion = WarehouseRegion::query()
            ->where('warehouse_id', $purchase->warehouse_id)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if ($warehouseRegion === null) {
            throw new \RuntimeException('No warehouse region found for purchase warehouse.');
        }

        $stockMovement = StockMovement::create([
            'type' => StockMovementType::In,
            'warehouse_id' => $purchase->warehouse_id,
            'description' => 'Purchase #'.$purchase->id,
        ]);

        foreach ($purchase->purchaseItems as $purchaseItem) {
            $stockMovement->stockMovementItems()->create([
                'product_id' => $purchaseItem->product_id,
                'quantity' => $purchaseItem->quantity,
                'warehouse_region_id' => $warehouseRegion->id,
            ]);
        }

        $attributes = [
            'delivery_date' => $deliveryDate,
        ];

        if (Schema::hasColumn('purchases', 'is_closed')) {
            $attributes['is_closed'] = true;
        }

        $purchase->update($attributes);

        return $purchase->fresh();
    }
}
