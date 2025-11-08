<?php

namespace Molitor\Purchase\Filament\Resources\PurchaseStatusResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Molitor\Purchase\Filament\Resources\PurchaseStatusResource;

class EditPurchaseStatus extends EditRecord
{
    protected static string $resource = PurchaseStatusResource::class;

    public function getTitle(): string
    {
        return __('purchase::purchase_status.edit');
    }

    public function getBreadcrumb(): string
    {
        return __('purchase::common.edit');
    }
}

