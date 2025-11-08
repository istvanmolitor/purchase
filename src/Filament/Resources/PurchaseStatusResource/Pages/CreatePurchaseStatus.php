<?php

namespace Molitor\Purchase\Filament\Resources\PurchaseStatusResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Purchase\Filament\Resources\PurchaseStatusResource;

class CreatePurchaseStatus extends CreateRecord
{
    protected static string $resource = PurchaseStatusResource::class;

    public function getBreadcrumb(): string
    {
        return __('purchase::common.create');
    }
}
