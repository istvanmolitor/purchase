<?php

namespace Molitor\Purchase\Repositories;

use Molitor\Purchase\Models\Purchase;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    protected Purchase $purchase;

    public function __construct()
    {
        $this->purchase = new Purchase();
    }

    public function delete(Purchase $purchase): void
    {
        $purchase->delete();
    }
}
