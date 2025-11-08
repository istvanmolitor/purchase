<?php

namespace Molitor\Purchase\Filament\Resources\PurchaseStatusResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Molitor\Purchase\Filament\Resources\PurchaseStatusResource;

class ListPurchaseStatuses extends ListRecords
{
    protected static string $resource = PurchaseStatusResource::class;

    public function getTitle(): string
    {
        return __('purchase::purchase_status.title');
    }

    public function getBreadcrumb(): string
    {
        return __('purchase::common.list');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('purchase::common.create')),
        ];
    }
}

