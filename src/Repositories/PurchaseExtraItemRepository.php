<?php

namespace Molitor\Purchase\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Purchase\Models\PurchaseExtraItem;

class PurchaseExtraItemRepository implements PurchaseExtraItemRepositoryInterface
{
    protected PurchaseExtraItem $purchaseExtraItem;

    public function __construct()
    {
        $this->purchaseExtraItem = new PurchaseExtraItem();
    }

    public function newQuery(): Builder
    {
        return $this->purchaseExtraItem->newQuery();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): PurchaseExtraItem
    {
        return $this->purchaseExtraItem->newQuery()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(PurchaseExtraItem $purchaseExtraItem, array $data): PurchaseExtraItem
    {
        $purchaseExtraItem->update($data);

        return $purchaseExtraItem;
    }

    public function delete(PurchaseExtraItem $purchaseExtraItem): void
    {
        $purchaseExtraItem->delete();
    }
}

