<?php

declare(strict_types=1);

namespace Molitor\Purchase\DataTables;

use Molitor\Admin\DataTables\DataTable;
use Molitor\Purchase\Http\Resources\PurchaseExtraItemTypeResource;
use Molitor\Purchase\Models\PurchaseExtraItemType;

class PurchaseExtraItemTypeDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return PurchaseExtraItemType::class;
    }

    protected function getResourceClass(): string
    {
        return PurchaseExtraItemTypeResource::class;
    }

    protected function initColumns(): void
    {
        $this->addColumn('name')->setSearchable()->setOrderable();
        $this->addColumn('description')->setSearchable();
    }
}
