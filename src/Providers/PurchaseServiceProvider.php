<?php

namespace Molitor\Purchase\Providers;

use Illuminate\Support\ServiceProvider;
use Molitor\Purchase\Repositories\PurchaseRepositoryInterface;
use Molitor\Purchase\Repositories\PurchaseRepository;
use Molitor\Purchase\Repositories\PurchaseItemRepositoryInterface;
use Molitor\Purchase\Repositories\PurchaseItemRepository;
use Molitor\Purchase\Repositories\PurchaseStatusRepository;
use Molitor\Purchase\Repositories\PurchaseStatusRepositoryInterface;

class PurchaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'purchase');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'purchase');
    }

    public function register()
    {
        $this->app->bind(PurchaseRepositoryInterface::class, PurchaseRepository::class);
        $this->app->bind(PurchaseItemRepositoryInterface::class, PurchaseItemRepository::class);
        $this->app->bind(PurchaseStatusRepositoryInterface::class, PurchaseStatusRepository::class);
    }
}
