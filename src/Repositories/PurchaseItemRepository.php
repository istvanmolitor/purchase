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
        }

        /**
         * @var int $index
         * @var PurchaseItem $purchaseItem
         */
        foreach ($purchase->purchaseItems as $index => $purchaseItem) {
            if(!isset($request->purchaseItems[$index])) {
                $purchaseItem->delete();
            }
        }
    }
}
