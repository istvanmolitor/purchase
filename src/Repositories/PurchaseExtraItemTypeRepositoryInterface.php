<?php

namespace Molitor\Purchase\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Purchase\Models\PurchaseExtraItemType;

interface PurchaseExtraItemTypeRepositoryInterface
{
    public function newQuery(): Builder;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PurchaseExtraItemType;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PurchaseExtraItemType $purchaseExtraItemType, array $data): PurchaseExtraItemType;

    public function delete(PurchaseExtraItemType $purchaseExtraItemType): void;
}
