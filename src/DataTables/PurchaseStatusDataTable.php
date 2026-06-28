<?php

declare(strict_types=1);

namespace Molitor\Purchase\DataTables;

use Molitor\Admin\DataTables\DataTable;
use Molitor\Purchase\Http\Resources\PurchaseStatusResource;
use Molitor\Purchase\Models\PurchaseStatus;

class PurchaseStatusDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return PurchaseStatus::class;
    }

    protected function getResourceClass(): string
    {
        return PurchaseStatusResource::class;
    }

    protected function initColumns(): void
    {
        $this->addColumn('name')->setSearchable()->setOrderable();
        $this->addColumn('description')->setSearchable();
    }
}
