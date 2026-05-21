<?php

namespace Molitor\Purchase\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Purchase\Models\Purchase;
use Molitor\Purchase\Models\PurchaseItem;

class PurchaseItemRepository implements PurchaseItemRepositoryInterface
{
    private PurchaseItem $purchaseItem;

    public function __construct()
    {
        $this->purchaseItem = new PurchaseItem();
    }

    public function delete(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->delete();
    }

    public function getPurchaseItemsByPurchase(Purchase $purchase): Collection
    {
        return $this->purchaseItem->where('purchase_id', $purchase->id)->with(['product', 'currency'])->get();
    }

    public function saveItems(Purchase $purchase, array $rows): void
    {
        $savedIds = [];

        foreach ($rows as $index => $purchaseItemData) {
            if ($purchase->purchaseItems->has($index)) {
                $purchaseItem = $purchase->purchaseItems->get($index);
            } else {
                $purchaseItem = new PurchaseItem();
                $purchaseItem->purchase_id = $purchase->id;
            }

            $purchaseItem->product_id = $purchaseItemData['product_id'];
            $purchaseItem->quantity = $purchaseItemData['quantity'];
            $purchaseItem->price = $purchaseItemData['price'];
            $purchaseItem->save();

            $savedIds[] = $purchaseItem->id;
        }

        $purchase->purchaseItems()
            ->whereNotIn('id', $savedIds)
            ->delete();
    }
}
