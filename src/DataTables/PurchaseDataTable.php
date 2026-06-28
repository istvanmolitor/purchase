<?php

declare(strict_types=1);

namespace Molitor\Purchase\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Admin\DataTables\DataTable;
use Molitor\Purchase\Http\Resources\PurchaseResource;
use Molitor\Purchase\Models\Purchase;

class PurchaseDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return Purchase::class;
    }

    protected function getResourceClass(): string
    {
        return PurchaseResource::class;
    }

    protected function initColumns(): void
    {
        $this->addColumn('id')->setOrderable()->setHidden();
        $this->addColumn('url')->setLabel('URL')->setSearchable();
        $this->addColumn('comment')->setLabel('Megjegyzés')->setSearchable();
    }

    public function query(Builder $query): Builder
    {
        return $query->with([
            'customer',
            'currency',
            'warehouse',
            'purchaseStatus',
            'purchaseItems.product.productUnit',
            'purchaseExtraItems.purchaseExtraItemType',
        ]);
    }
}
