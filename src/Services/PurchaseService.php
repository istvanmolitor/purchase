<?php

namespace Molitor\Purchase\Services;

use Carbon\Carbon;
use Molitor\Purchase\Models\Purchase;
use Molitor\Stock\Models\StockMovement;

class PurchaseService
{
    public function closePurchase(Purchase $purchase, Carbon $deliveryDate): Purchase
    {
        $stockMovement = StockMovement::create([
            'type' => 'in',
            'warehouse_id' => $purchase->warehouse_id,
            'description' => 'Purchase #' . $purchase->id,
        ]);

        foreach ($purchase->purchaseItems as $purchaseItem) {
            $stockMovement->stockMovementItems()->create([
                'product_id' => $purchaseItem->product_id,
                'quantity' => $purchaseItem->quantity,
                'description' => $purchaseItem->comment,
            ]);
        }

        $purchase->update([
            'is_closed' => true,
            'delivery_date' => $deliveryDate,
        ]);

        return $purchase->fresh();
    }
}
