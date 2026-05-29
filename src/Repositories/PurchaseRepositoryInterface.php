<?php

namespace Molitor\Purchase\Repositories;

use Molitor\Purchase\Models\Purchase;

interface PurchaseRepositoryInterface
{
    public function delete(Purchase $purchase): void;

    /**
     * @param array<string, mixed> $validated
     */
    public function create(array $validated): Purchase;
}
