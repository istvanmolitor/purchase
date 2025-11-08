<?php

namespace Molitor\Purchase\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Molitor\Purchase\Filament\Resources\PurchaseResource;
use Molitor\Purchase\Models\Purchase;
use Molitor\Purchase\Models\PurchaseLog;
use Molitor\Purchase\Repositories\PurchaseStatusRepositoryInterface;

class ManagePurchaseStatus extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PurchaseResource::class;

    protected string $view = 'purchase::filament.pages.manage-purchase-status';

    public ?array $data = [];

    public Purchase $purchase;

    public function mount(Purchase $record): void
    {
        $this->purchase = $record;

        $this->form->fill([
            'purchase_status_id' => $this->purchase->purchase_status_id,
            'comment' => '',
        ]);
    }

    protected function getFormSchema(): array
    {
        /** @var PurchaseStatusRepositoryInterface $purchaseStatusRepository */
        $purchaseStatusRepository = app(PurchaseStatusRepositoryInterface::class);

        return [
            Select::make('purchase_status_id')
                ->label(__('purchase::common.purchase_status'))
                ->options($purchaseStatusRepository->getOptions())
                ->required()
                ->helperText(__('purchase::common.select_new_status')),
            Textarea::make('comment')
                ->label(__('purchase::common.comment'))
                ->rows(4)
                ->helperText(__('purchase::common.status_change_comment_helper')),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('purchase::common.save_status'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $oldStatusId = $this->purchase->purchase_status_id;
        $newStatusId = $data['purchase_status_id'];

        if ($oldStatusId !== $newStatusId) {
            $this->purchase->update([
                'purchase_status_id' => $newStatusId,
            ]);

            PurchaseLog::create([
                'purchase_id' => $this->purchase->id,
                'purchase_status_id' => $newStatusId,
                'user_id' => auth()->id(),
                'comment' => $data['comment'] ?? null,
                'status_changed_at' => now(),
            ]);

            Notification::make()
                ->success()
                ->title(__('purchase::common.status_updated_successfully'))
                ->send();
        } else {
            Notification::make()
                ->warning()
                ->title(__('purchase::common.status_not_changed'))
                ->send();
        }

        $this->redirect(PurchaseResource::getUrl('view', ['record' => $this->purchase]));
    }

    public function getBreadcrumbs(): array
    {
        return [
            PurchaseResource::getUrl('index') => __('purchase::purchase.title'),
            PurchaseResource::getUrl('view', ['record' => $this->purchase]) => '#' . $this->purchase->id,
            '#' => __('purchase::common.manage_status'),
        ];
    }

    public function getLogs()
    {
        return PurchaseLog::where('purchase_id', $this->purchase->id)
            ->with(['purchaseStatus', 'user'])
            ->orderBy('status_changed_at', 'desc')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('purchase::common.back'))
                ->url(PurchaseResource::getUrl('view', ['record' => $this->purchase]))
                ->color('gray'),
        ];
    }
}
