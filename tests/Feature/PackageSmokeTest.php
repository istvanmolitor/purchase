<?php

namespace Molitor\Purchase\Tests\Feature;

use Molitor\Purchase\Providers\PurchaseServiceProvider;
use Tests\TestCase;

class PackageSmokeTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $this->assertTrue(class_exists(PurchaseServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(PurchaseServiceProvider::class));
    }
}

