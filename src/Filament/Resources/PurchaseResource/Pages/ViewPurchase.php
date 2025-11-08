<?php

namespace Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Molitor\Purchase\Filament\Resources\PurchaseResource;
use Molitor\Purchase\Services\PurchaseService;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_status')
                ->label(__('purchase::common.manage_status'))
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->url(fn () => PurchaseResource::getUrl('manage-status', ['record' => $this->record])),
            Action::make('close_purchase')
                ->label(__('purchase::common.close_purchase'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => !$this->record->delivery_date)
                ->requiresConfirmation()
                ->action(function () {
                    $purchaseService = app(PurchaseService::class);
                    $purchaseService->closePurchase($this->record, now());

                    $this->refreshFormData([
                        'is_closed',
                        'delivery_date',
                    ]);
                })
                ->successNotificationTitle(__('purchase::common.purchase_closed_successfully')),
        ];
    }
}
