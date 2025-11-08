<?php

namespace Molitor\Purchase\Filament\Resources;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;
use Molitor\Purchase\Models\Purchase;
use Molitor\Currency\Models\Currency;
use Molitor\Product\Models\Product;
use Molitor\Purchase\Repositories\PurchaseStatusRepositoryInterface;
use Molitor\Stock\Repositories\WarehouseRepositoryInterface;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-receipt-refund';

    public static function getNavigationGroup(): string
    {
        return __('purchase::common.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('purchase::purchase.title');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'purchase');
    }

    public static function form(Schema $schema): Schema
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = app(CustomerRepositoryInterface::class);

        /** @var CurrencyRepositoryInterface $currencyRepository */
        $currencyRepository = app(CurrencyRepositoryInterface::class);

        /** @var WarehouseRepositoryInterface $warehouseRepository */
        $warehouseRepository = app(WarehouseRepositoryInterface::class);

        /** @var PurchaseStatusRepositoryInterface $purchaseStatusRepository */
        $purchaseStatusRepository = app(PurchaseStatusRepositoryInterface::class);

        return $schema->components([
            Select::make('customer_id')
                ->label(__('purchase::common.customer'))
                ->relationship('customer', 'name', function (Builder $query) {
                    $query->where('is_seller', true);
                })
                ->required()
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) use ($customerRepository) {
                    if (empty($state)) {
                        return;
                    }
                    $customer = $customerRepository->getById($state);
                    if ($customer && $customer->currency_id) {
                        $set('currency_id', $customer->currency_id);
                    }
                }),
            Select::make('purchase_status_id')
                ->label(__('purchase::common.purchase_status'))
                ->options($purchaseStatusRepository->getOptions())
                ->searchable()
                ->required(),
            Select::make('warehouse_id')
                ->label(__('purchase::common.destination_warehouse'))
                ->options($warehouseRepository->getOptions())
                ->searchable()
                ->required(),
            Grid::make(3)->schema([
                DatePicker::make('purchase_date')
                    ->label(__('purchase::common.purchase_date'))
                    ->displayFormat('Y-m-d')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),
                DatePicker::make('expected_delivery_date')
                    ->label(__('purchase::common.expected_delivery_date'))
                    ->displayFormat('Y-m-d')
                    ->required()
                    ->columnSpan(1),
                DatePicker::make('delivery_date')
                    ->label(__('purchase::common.delivery_date'))
                    ->displayFormat('Y-m-d')
                    ->nullable()
                    ->columnSpan(1),
            ]),
            Grid::make(3)->schema([
                Select::make('currency_id')
                    ->label(__('purchase::common.currency'))
                    ->options(Currency::query()->pluck('code', 'id'))
                    ->default($currencyRepository->getDefaultId())
                    ->required()
                    ->reactive(),
                TextInput::make('total_price')
                    ->label(__('purchase::common.total_price'))
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->columnSpan(1)
                    ->suffix(function ($state, callable $get) {
                        $currencyId = $get('currency_id');
                        if (empty($currencyId)) {
                            return null;
                        }
                        try {
                            $currency = Currency::find($currencyId);
                        } catch (\Throwable $e) {
                            return null;
                        }
                        return $currency?->code;
                    }),
            ]),
            TextInput::make('url')
                ->label(__('purchase::common.url'))
                ->maxLength(255),
            Textarea::make('comment')
                ->label(__('purchase::common.comment'))
                ->columnSpanFull(),
            Repeater::make('purchaseItems')
                ->label(__('purchase::common.purchase_items'))
                ->relationship()
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('product_id')
                            ->label(__('purchase::common.product'))
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn($record) => (string) $record)
                            ->relationship('product', 'id')
                            ->required()
                            ->reactive()
                            ->columnSpan(1),
                        TextInput::make('quantity')
                            ->label(__('purchase::common.quantity'))
                            ->numeric()
                            ->required()
                            ->columnSpan(1)
                            ->suffix(function ($state, callable $get) {
                                $productId = $get('product_id');
                                if (empty($productId)) {
                                    return null;
                                }
                                try {
                                    $product = Product::with('productUnit')->find($productId);
                                } catch (\Throwable $e) {
                                    return null;
                                }
                                if (! $product || ! $product->productUnit) {
                                    return null;
                                }
                                return (string) $product->productUnit;
                            }),
                        TextInput::make('price')
                            ->label(__('purchase::common.price'))
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->columnSpan(1)
                            ->suffix(function ($state, callable $get) {
                                $currencyId = $get('../../currency_id');
                                if (empty($currencyId)) {
                                    return null;
                                }
                                try {
                                    $currency = Currency::find($currencyId);
                                } catch (\Throwable $e) {
                                    return null;
                                }
                                return $currency?->code;
                            }),
                    ]),
                    Textarea::make('comment')->label(__('purchase::common.comment'))->columnSpanFull(),
                ])
                ->columns(1)
                ->defaultItems(1)
                ->minItems(1)
                ->addActionLabel(__('purchase::common.add_item')),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->label(__('purchase::common.customer'))->sortable(),
                TextColumn::make('purchase_items_count')
                    ->label(__('purchase::common.items_count'))
                    ->counts('purchaseItems')
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label(__('purchase::common.total_quantity'))
                    ->getStateUsing(function ($record) {
                        return $record->purchaseItems->sum('quantity');
                    })
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label(__('purchase::common.total_price'))
                    ->getStateUsing(function ($record) {
                        return $record->total_price . ' ' . $record->currency->code;
                    }),
                TextColumn::make('warehouse.name')
                    ->label(__('purchase::common.destination_warehouse')),
                TextColumn::make('expected_delivery_date')
                    ->label(__('purchase::common.expected_delivery_date'))
                    ->getStateUsing(function ($record) {
                        if (!$record->expected_delivery_date) {
                            return null;
                        }

                        $expectedDate = Carbon::parse($record->expected_delivery_date);
                        $today = Carbon::today();

                        if ($record->delivery_date) {
                            return $expectedDate->format('Y-m-d');
                        }

                        if ($expectedDate->isPast()) {
                            $daysLate = $expectedDate->diffInDays($today);
                            return $expectedDate->format('Y-m-d') . ' (' . $daysLate . ' ' . __('purchase::common.days_late') . ')';
                        }

                        return $expectedDate->format('Y-m-d');
                    })
                    ->color(function ($record) {
                        if (!$record->expected_delivery_date || $record->delivery_date) {
                            return null;
                        }

                        $expectedDate = Carbon::parse($record->expected_delivery_date);
                        return $expectedDate->isPast() ? 'danger' : null;
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('delivery_status')
                    ->label(__('purchase::common.delivery_status'))
                    ->options([
                        'delivered' => __('purchase::common.delivered'),
                        'not_delivered' => __('purchase::common.not_delivered'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'delivered' => $query->whereNotNull('delivery_date'),
                            'not_delivered' => $query->whereNull('delivery_date'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
            'manage-status' => Pages\ManagePurchaseStatus::route('/{record}/manage-status'),
        ];
    }
}
