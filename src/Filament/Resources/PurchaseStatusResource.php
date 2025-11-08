<?php

namespace Molitor\Purchase\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Gate;
use Molitor\Purchase\Filament\Resources\PurchaseStatusResource\Pages;
use Molitor\Purchase\Models\PurchaseStatus;

class PurchaseStatusResource extends Resource
{
    protected static ?string $model = PurchaseStatus::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-flag';

    public static function getNavigationGroup(): string
    {
        return __('purchase::common.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('purchase::purchase_status.title');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'purchase_status');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('purchase::common.name'))
                ->required()
                ->maxLength(255),
            Select::make('state')
                ->label(__('purchase::common.state'))
                ->options([
                    0 => __('purchase::purchase_status.state_initial'),
                    1 => __('purchase::purchase_status.state_in_progress'),
                    2 => __('purchase::purchase_status.state_completed'),
                    3 => __('purchase::purchase_status.state_cancelled'),
                ])
                ->default(0)
                ->required(),
            Textarea::make('description')
                ->label(__('purchase::common.description'))
                ->nullable()
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('purchase::common.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state')
                    ->label(__('purchase::common.state'))
                    ->getStateUsing(function ($record) {
                        return match ($record->state) {
                            0 => __('purchase::purchase_status.state_initial'),
                            1 => __('purchase::purchase_status.state_in_progress'),
                            2 => __('purchase::purchase_status.state_completed'),
                            3 => __('purchase::purchase_status.state_cancelled'),
                            default => '-',
                        };
                    })
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->state) {
                            0 => 'gray',
                            1 => 'warning',
                            2 => 'success',
                            3 => 'danger',
                            default => 'gray',
                        };
                    })
                    ->sortable(),
                TextColumn::make('purchases_count')
                    ->label(__('purchase::purchase_status.purchases_count'))
                    ->counts('purchases')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('purchase::common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([
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
            'index' => Pages\ListPurchaseStatuses::route('/'),
            'create' => Pages\CreatePurchaseStatus::route('/create'),
            'edit' => Pages\EditPurchaseStatus::route('/{record}/edit'),
        ];
    }
}

