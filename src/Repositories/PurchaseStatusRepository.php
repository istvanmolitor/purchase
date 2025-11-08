<?php

namespace Molitor\Purchase\Repositories;

use Molitor\Purchase\Models\PurchaseStatus;

class PurchaseStatusRepository implements PurchaseStatusRepositoryInterface
{
    protected PurchaseStatus $purchaseStatus;

    public function __construct()
    {
        $this->purchaseStatus = new PurchaseStatus();
    }

    public function getOptions(): array
    {
        return $this->purchaseStatus->pluck('name', 'id')->toArray();
    }
}
