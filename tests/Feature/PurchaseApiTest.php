<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_logs');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('purchase_statuses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('warehouse_regions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('currencies');
        Schema::enableForeignKeyConstraints();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_seller')->default(false);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('warehouse_regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('name');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku');
            $table->string('slug')->nullable();
            $table->decimal('price')->default(0);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('product_unit_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->smallInteger('state')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_status_id')->constrained('purchase_statuses');
            $table->string('url')->nullable();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->text('comment')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->decimal('total_price', 11)->nullable();
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity');
            $table->decimal('price', 11)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('purchase_status_id')->constrained('purchase_statuses');
            $table->text('comment')->nullable();
            $table->dateTime('status_changed_at');
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_closed')->default(false);
            $table->string('code')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity');
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_region_id')->constrained('warehouse_regions');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity', 11, 2)->default(0);
            $table->decimal('min_quantity', 11, 2)->default(0);
            $table->decimal('max_quantity', 11, 2)->nullable();
        });
    }

    public function test_can_manage_purchase_statuses(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $createResponse = $this->postJson('/api/admin/purchase/purchase-statuses', [
            'name' => 'Fuggoben',
            'state' => 1,
            'description' => 'Teszt statusz',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Fuggoben');

        $statusId = $createResponse->json('data.id');

        $this->putJson("/api/admin/purchase/purchase-statuses/{$statusId}", [
            'name' => 'Lezarva',
            'state' => 2,
            'description' => 'Frissitett',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Lezarva');

        $this->deleteJson("/api/admin/purchase/purchase-statuses/{$statusId}")
            ->assertOk();

        $this->assertDatabaseMissing('purchase_statuses', ['id' => $statusId]);
    }

    public function test_can_create_update_and_delete_purchase(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $currencyId = DB::table('currencies')->insertGetId([
            'code' => 'HUF',
            'name' => 'Forint',
            'is_enabled' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Beszallito Kft',
            'is_seller' => true,
            'currency_id' => $currencyId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Kozponti',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statusId = DB::table('purchase_statuses')->insertGetId([
            'name' => 'Uj',
            'state' => 0,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'sku' => 'PRD-001',
            'slug' => 'prd-001',
            'price' => 0,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $createResponse = $this->postJson('/api/admin/purchase/purchases', [
            'purchase_status_id' => $statusId,
            'customer_id' => $customerId,
            'warehouse_id' => $warehouseId,
            'currency_id' => $currencyId,
            'total_price' => 1000,
            'purchase_items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                    'price' => 500,
                ],
            ],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.customer_id', $customerId)
            ->assertJsonPath('data.purchase_items.0.product_id', $productId);

        $purchaseId = $createResponse->json('data.id');

        $updateResponse = $this->putJson("/api/admin/purchase/purchases/{$purchaseId}", [
            'purchase_status_id' => $statusId,
            'customer_id' => $customerId,
            'warehouse_id' => $warehouseId,
            'currency_id' => $currencyId,
            'total_price' => 2500,
            'purchase_items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 5,
                    'price' => 500,
                ],
            ],
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.total_price', 2500)
            ->assertJsonPath('data.purchase_items.0.quantity', 5);

        $this->deleteJson("/api/admin/purchase/purchases/{$purchaseId}")
            ->assertOk();

        $this->assertDatabaseMissing('purchases', ['id' => $purchaseId]);
    }

    public function test_returns_products_that_need_purchasing(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $currencyId = DB::table('currencies')->insertGetId([
            'code' => 'HUF',
            'name' => 'Forint',
            'is_enabled' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Beszallito',
            'is_seller' => true,
            'currency_id' => $currencyId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Fo raktar',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseRegionId = DB::table('warehouse_regions')->insertGetId([
            'warehouse_id' => $warehouseId,
            'name' => 'A regio',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $plannedStatusId = DB::table('purchase_statuses')->insertGetId([
            'name' => 'Tervezett',
            'state' => 0,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'sku' => 'PRD-REQ-001',
            'slug' => 'prd-req-001',
            'price' => 0,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('stocks')->insert([
            'warehouse_region_id' => $warehouseRegionId,
            'product_id' => $productId,
            'quantity' => 2,
            'min_quantity' => 4,
            'max_quantity' => null,
        ]);

        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_status_id' => $plannedStatusId,
            'customer_id' => $customerId,
            'warehouse_id' => $warehouseId,
            'currency_id' => $currencyId,
            'user_id' => User::query()->firstOrFail()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_items')->insert([
            'purchase_id' => $purchaseId,
            'product_id' => $productId,
            'quantity' => 1,
            'price' => null,
            'comment' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'is_closed' => false,
            'code' => 'ORD-001',
            'customer_id' => $customerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/admin/purchase/purchases/requirements/list');

        $response->assertOk()
            ->assertJsonPath('total_products', 1)
            ->assertJsonPath('data.0.sku', 'PRD-REQ-001')
            ->assertJsonPath('data.0.current_quantity', 2)
            ->assertJsonPath('data.0.incoming_purchases', 1)
            ->assertJsonPath('data.0.pending_orders', 2)
            ->assertJsonPath('data.0.available_quantity', 1)
            ->assertJsonPath('data.0.need_to_order', 3)
            ->assertJsonPath('warehouses.0.id', $warehouseId);
    }
}

