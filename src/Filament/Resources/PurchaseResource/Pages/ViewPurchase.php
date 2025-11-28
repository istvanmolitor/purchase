<?php

namespace Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Molitor\Purchase\Filament\Resources\PurchaseResource;
use Molitor\Purchase\Services\PurchaseService;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    public function getTitle(): string
    {
        return __('purchase::common.view_purchase');
    }

    public function getBreadcrumb(): string
    {
        return __('purchase::common.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(__('purchase::common.edit')),
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
                    /** @var PurchaseService $purchaseService */
                    $purchaseService = app(PurchaseService::class);
                    $purchaseService->closePurchase($this->record, now());

                    $this->dispatch('$refresh');
                })
                ->successNotificationTitle(__('purchase::common.purchase_closed_successfully')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('purchase::common.basic_info'))
                    ->schema([
                        IconEntry::make('is_closed')
                            ->label(__('purchase::common.is_closed'))
                            ->boolean(),
                        TextEntry::make('customer.name')
                            ->label(__('purchase::common.customer')),
                        TextEntry::make('purchaseStatus.name')
                            ->label(__('purchase::common.purchase_status'))
                            ->badge()
                            ->color(fn ($record) => $record->purchaseStatus?->color ?? 'gray'),
                        TextEntry::make('warehouse.name')
                            ->label(__('purchase::common.destination_warehouse')),
                        TextEntry::make('url')
                            ->label(__('purchase::common.url'))
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab()
                            ->copyable()
                            ->visible(fn ($record) => !empty($record->url))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Fieldset::make(__('purchase::common.dates'))
                    ->schema([
                        TextEntry::make('purchase_date')
                            ->label(__('purchase::common.purchase_date'))
                            ->date(),
                        TextEntry::make('expected_delivery_date')
                            ->label(__('purchase::common.expected_delivery_date'))
                            ->date(),
                        TextEntry::make('delivery_date')
                            ->label(__('purchase::common.delivery_date'))
                            ->date()
                            ->placeholder(__('purchase::common.not_delivered')),
                    ])
                    ->columns(3),
                Fieldset::make(__('purchase::common.financial'))
                    ->schema([
                        TextEntry::make('total_price')
                            ->label(__('purchase::common.total_price'))
                            ->money(fn ($record) => $record->currency?->code ?? 'USD'),
                        TextEntry::make('currency.code')
                            ->label(__('purchase::common.currency')),
                    ])
                    ->columns(2),
                Fieldset::make(__('purchase::common.comment'))
                    ->schema([
                        TextEntry::make('comment')
                            ->label(__('purchase::common.comment'))
                            ->placeholder(__('purchase::common.no_comment'))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->comment)),
                Fieldset::make(__('purchase::common.purchase_items'))
                    ->schema([
                        RepeatableEntry::make('purchaseItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('product')
                                    ->label(__('purchase::common.product'))
                                    ->state(fn ($record) => (string) $record->product)
                                    ->columnSpan(2),
                                TextEntry::make('quantity')
                                    ->label(__('purchase::common.quantity'))
                                    ->state(fn ($record) => $record->quantity . ' ' . ($record->product?->productUnit ? (string) $record->product->productUnit : ''))
                                    ->columnSpan(1),
                                TextEntry::make('price')
                                    ->label(__('purchase::common.price'))
                                    ->money(fn () => $this->record->currency?->code ?? 'USD')
                                    ->columnSpan(1),
                                TextEntry::make('comment')
                                    ->label(__('purchase::common.comment'))
                                    ->placeholder(__('purchase::common.no_comment'))
                                    ->visible(fn ($record) => !empty($record->comment))
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(__('purchase::common.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('purchase::common.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('purchase::common.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
