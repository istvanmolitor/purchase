<?php

namespace Molitor\Purchase\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Purchase\Models\Purchase;
use Molitor\Purchase\Models\PurchaseItem;

interface PurchaseItemRepositoryInterface
{
    public function delete(PurchaseItem $purchaseItem): void;

    public function getPurchaseItemsByPurchase(Purchase $purchase): Collection;

    public function saveItems(Purchase $purchase, array $rows): void;
}
