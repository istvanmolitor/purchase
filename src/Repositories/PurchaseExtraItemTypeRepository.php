<?php

namespace Molitor\Purchase\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Purchase\Models\PurchaseExtraItemType;

class PurchaseExtraItemTypeRepository implements PurchaseExtraItemTypeRepositoryInterface
{
    protected PurchaseExtraItemType $purchaseExtraItemType;

    public function __construct()
    {
        $this->purchaseExtraItemType = new PurchaseExtraItemType;
    }

    public function newQuery(): Builder
    {
        return $this->purchaseExtraItemType->newQuery();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PurchaseExtraItemType
    {
        return $this->purchaseExtraItemType->newQuery()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PurchaseExtraItemType $purchaseExtraItemType, array $data): PurchaseExtraItemType
    {
        $purchaseExtraItemType->update($data);

        return $purchaseExtraItemType;
    }

    public function delete(PurchaseExtraItemType $purchaseExtraItemType): void
    {
        $purchaseExtraItemType->delete();
    }
}
