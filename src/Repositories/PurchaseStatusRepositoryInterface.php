<?php

namespace Molitor\Purchase\Repositories;

use Molitor\Purchase\Enums\PurchaseState;
use Molitor\Purchase\Models\PurchaseStatus;

interface PurchaseStatusRepositoryInterface
{
    /**
     * @return array<int, string>
     */
    public function getOptions(): array;

    public function create(string $name, PurchaseState $state, ?string $description): PurchaseStatus;

    public function update(PurchaseStatus $purchaseStatus, string $name, PurchaseState $state, ?string $description): PurchaseStatus;
}
