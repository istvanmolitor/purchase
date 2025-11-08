<?php
namespace Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Molitor\Purchase\Filament\Resources\PurchaseResource;
use Molitor\Address\Repositories\AddressRepositoryInterface;
use Molitor\Address\Models\Address;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Sikeres mentés');
    }
}
