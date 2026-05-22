<?php

namespace Molitor\Purchase\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Purchase\Models\PurchaseExtraItem;

interface PurchaseExtraItemRepositoryInterface
{
    public function newQuery(): Builder;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): PurchaseExtraItem;

    /**
     * @param array<string, mixed> $data
     */
    public function update(PurchaseExtraItem $purchaseExtraItem, array $data): PurchaseExtraItem;

    public function delete(PurchaseExtraItem $purchaseExtraItem): void;
}

