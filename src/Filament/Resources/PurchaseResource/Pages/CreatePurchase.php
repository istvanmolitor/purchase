<?php
namespace Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Purchase\Filament\Resources\PurchaseResource;
use Molitor\Address\Repositories\AddressRepositoryInterface;
use Molitor\Address\Models\Address;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Sikeres mentés');
    }
}
