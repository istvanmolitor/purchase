<?php

namespace Molitor\Purchase\Repositories;

use Molitor\Purchase\Enums\PurchaseState;
use Molitor\Purchase\Models\PurchaseStatus;

class PurchaseStatusRepository implements PurchaseStatusRepositoryInterface
{
    public function __construct(protected PurchaseStatus $purchaseStatus) {}

    public function getOptions(): array
    {
        return $this->purchaseStatus->pluck('name', 'id')->toArray();
    }

    public function create(string $name, PurchaseState $state, ?string $description): PurchaseStatus
    {
        return $this->purchaseStatus->create([
            'name' => $name,
            'state' => $state,
            'description' => $description,
        ]);
    }

    public function update(PurchaseStatus $purchaseStatus, string $name, PurchaseState $state, ?string $description): PurchaseStatus
    {
        $purchaseStatus->update([
            'name' => $name,
            'state' => $state,
            'description' => $description,
        ]);

        return $purchaseStatus->refresh();
    }
}
