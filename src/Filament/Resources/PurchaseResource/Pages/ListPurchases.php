<?php
namespace Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Molitor\Purchase\Filament\Resources\PurchaseResource;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    public function getBreadcrumb(): string
    {
        return __('Purchase::common.list');
    }

    public function getTitle(): string
    {
        return __('purchase::purchase.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('purchase::purchase.create'))
                ->icon('heroicon-o-plus'),
        ];
    }
}
