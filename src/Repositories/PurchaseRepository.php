<?php

namespace Molitor\Purchase\Repositories;

use Molitor\Purchase\Models\Purchase;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    protected Purchase $purchase;

    public function __construct()
    {
        $this->purchase = new Purchase;
    }

    public function delete(Purchase $purchase): void
    {
        $purchase->delete();
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function create(array $validated): Purchase
    {
        return $this->purchase->create([
            'purchase_status_id' => $validated['purchase_status_id'],
            'url' => $validated['url'] ?? null,
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'comment' => $validated['comment'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'total_price' => $validated['total_price'] ?? null,
            'currency_id' => $validated['currency_id'],
        ]);
    }
}
