<?php

namespace Molitor\Purchase\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Molitor\Admin\Traits\HasAdminFilters;
use Molitor\Currency\Models\Currency;
use Molitor\Customer\Models\Customer;
use Molitor\Product\Models\Product;
use Molitor\Purchase\Http\Requests\ChangePurchaseStatusRequest;
use Molitor\Purchase\Http\Requests\ClosePurchaseRequest;
use Molitor\Purchase\Http\Requests\StorePurchaseRequest;
use Molitor\Purchase\Http\Requests\UpdatePurchaseRequest;
use Molitor\Purchase\Http\Resources\PurchaseResource;
use Molitor\Purchase\Models\PurchaseExtraItem;
use Molitor\Purchase\Models\PurchaseItem;
use Molitor\Purchase\Models\Purchase;
use Molitor\Purchase\Models\PurchaseExtraItemType;
use Molitor\Purchase\Models\PurchaseLog;
use Molitor\Purchase\Models\PurchaseStatus;
use Molitor\Purchase\Services\PurchaseService;
use Molitor\Stock\Models\Warehouse;

class PurchaseApiController extends Controller
{
    use HasAdminFilters;

    public function index(Request $request): JsonResponse
    {
        $query = Purchase::query()->with(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        $purchases = $this->applyAdminFilters($query, $request, ['url', 'comment'], 'id')
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'data' => PurchaseResource::collection($purchases->items()),
            'meta' => [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'per_page' => $purchases->perPage(),
                'total' => $purchases->total(),
            ],
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json($this->getFormData());
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $purchase = Purchase::query()->create([
            'purchase_status_id' => $validated['purchase_status_id'],
            'url' => $validated['url'] ?? null,
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'comment' => $validated['comment'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'total_price' => $validated['total_price'] ?? null,
            'currency_id' => $validated['currency_id'],
        ]);

        if (! empty($validated['purchase_items'])) {
            $this->syncPurchaseItems($purchase, $validated['purchase_items']);
        }

        if (! empty($validated['purchase_extra_items'])) {
            $this->syncPurchaseExtraItems($purchase, $validated['purchase_extra_items']);
        }

        PurchaseLog::query()->create([
            'purchase_id' => $purchase->id,
            'purchase_status_id' => $validated['purchase_status_id'],
            'user_id' => auth()->id(),
            'comment' => $validated['comment'] ?? null,
            'status_changed_at' => now(),
        ]);

        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'message' => 'Beszerzes sikeresen letrehozva.',
        ], 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'logs' => $this->getLogs($purchase),
            'products' => $this->getSelectedProducts($purchase),
            'purchase_extra_item_types' => $this->getSelectedExtraItemTypes($purchase),
        ]);
    }

    public function edit(Purchase $purchase): JsonResponse
    {
        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        return response()->json(array_merge([
            'data' => new PurchaseResource($purchase),
            'logs' => $this->getLogs($purchase),
        ], $this->getFormData()));
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): JsonResponse
    {
        $validated = $request->validated();
        $previousStatusId = (int) $purchase->purchase_status_id;

        $purchase->update([
            'purchase_status_id' => $validated['purchase_status_id'],
            'url' => $validated['url'] ?? null,
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'comment' => $validated['comment'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'total_price' => $validated['total_price'] ?? null,
            'currency_id' => $validated['currency_id'],
        ]);

        if (! empty($validated['purchase_items'])) {
            $this->syncPurchaseItems($purchase, $validated['purchase_items']);
        } else {
            $purchase->purchaseItems()->delete();
        }

        if (! empty($validated['purchase_extra_items'])) {
            $this->syncPurchaseExtraItems($purchase, $validated['purchase_extra_items']);
        } else {
            $purchase->purchaseExtraItems()->delete();
        }

        if ($previousStatusId !== (int) $validated['purchase_status_id']) {
            PurchaseLog::query()->create([
                'purchase_id' => $purchase->id,
                'purchase_status_id' => $validated['purchase_status_id'],
                'user_id' => auth()->id(),
                'comment' => $validated['comment'] ?? null,
                'status_changed_at' => now(),
            ]);
        }

        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'message' => 'Beszerzes sikeresen frissitve.',
        ]);
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        $purchase->purchaseLogs()->delete();
        $purchase->purchaseItems()->delete();
        $purchase->purchaseExtraItems()->delete();
        $purchase->delete();

        return response()->json([
            'message' => 'Beszerzes sikeresen torolve.',
        ]);
    }

    public function updateStatus(ChangePurchaseStatusRequest $request, Purchase $purchase): JsonResponse
    {
        $validated = $request->validated();

        $purchase->update([
            'purchase_status_id' => $validated['purchase_status_id'],
        ]);

        PurchaseLog::query()->create([
            'purchase_id' => $purchase->id,
            'purchase_status_id' => $validated['purchase_status_id'],
            'user_id' => auth()->id(),
            'comment' => $validated['comment'] ?? null,
            'status_changed_at' => now(),
        ]);

        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'message' => 'Beszerzes statusz sikeresen frissitve.',
        ]);
    }

    public function close(ClosePurchaseRequest $request, Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        $deliveryDate = $request->validated()['delivery_date'] ?? now()->toDateString();

        $purchase = $purchase->load('purchaseItems');
        $closedPurchase = $purchaseService->closePurchase($purchase, Carbon::parse($deliveryDate));
        $closedPurchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product', 'purchaseExtraItems.purchaseExtraItemType']);

        return response()->json([
            'data' => new PurchaseResource($closedPurchase),
            'message' => 'Beszerzes sikeresen lezarva.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getFormData(): array
    {
        return [
            'customers' => Customer::query()
                ->where('is_seller', true)
                ->orderBy('name')
                ->get(['id', 'name', 'currency_id']),
            'currencies' => Currency::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'warehouses' => Warehouse::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'purchase_statuses' => PurchaseStatus::query()
                ->orderBy('name')
                ->get(['id', 'name', 'state', 'description']),
            'products' => Product::query()
                ->with('mainImage:id,product_id,image_url')
                ->orderBy('sku')
                ->get(['id', 'sku'])
                ->map(function (Product $product): array {
                    return [
                        'id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name ?: $product->sku,
                        'image_url' => $product->mainImage?->image_url,
                    ];
                })
                ->values(),
            'purchase_extra_item_types' => PurchaseExtraItemType::query()
                ->orderBy('name')
                ->get(['id', 'name', 'description']),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncPurchaseItems(Purchase $purchase, array $items): void
    {
        $keptIds = [];

        foreach ($items as $item) {
            $purchaseItem = $purchase->purchaseItems()->updateOrCreate(
                [
                    'id' => $item['id'] ?? null,
                ],
                [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? null,
                    'comment' => $item['comment'] ?? null,
                ]
            );

            $keptIds[] = $purchaseItem->id;
        }

        $purchase->purchaseItems()
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncPurchaseExtraItems(Purchase $purchase, array $items): void
    {
        $keptIds = [];

        foreach ($items as $item) {
            $purchaseExtraItem = $purchase->purchaseExtraItems()->updateOrCreate(
                [
                    'id' => $item['id'] ?? null,
                ],
                [
                    'purchase_extra_item_type_id' => $item['purchase_extra_item_type_id'],
                    'price' => $item['price'] ?? null,
                    'comment' => $item['comment'] ?? null,
                ]
            );

            $keptIds[] = $purchaseExtraItem->id;
        }

        $purchase->purchaseExtraItems()
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getLogs(Purchase $purchase): array
    {
        return PurchaseLog::query()
            ->where('purchase_id', $purchase->id)
            ->with(['purchaseStatus', 'user'])
            ->orderByDesc('status_changed_at')
            ->get()
            ->map(function (PurchaseLog $log): array {
                return [
                    'id' => $log->id,
                    'purchase_status' => $log->purchaseStatus?->name,
                    'user' => $log->user?->name,
                    'comment' => $log->comment,
                    'status_changed_at' => $log->status_changed_at?->toDateTimeString(),
                ];
            })
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSelectedProducts(Purchase $purchase): array
    {
        return $purchase->purchaseItems
            ->map(function (PurchaseItem $purchaseItem): ?array {
                if ($purchaseItem->product === null) {
                    return null;
                }

                return [
                    'id' => $purchaseItem->product->id,
                    'sku' => $purchaseItem->product->sku,
                    'name' => $purchaseItem->product->sku,
                ];
            })
            ->filter()
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSelectedExtraItemTypes(Purchase $purchase): array
    {
        return $purchase->purchaseExtraItems
            ->map(function (PurchaseExtraItem $purchaseExtraItem): ?array {
                if ($purchaseExtraItem->purchaseExtraItemType === null) {
                    return null;
                }

                return [
                    'id' => $purchaseExtraItem->purchaseExtraItemType->id,
                    'name' => $purchaseExtraItem->purchaseExtraItemType->name,
                    'description' => $purchaseExtraItem->purchaseExtraItemType->description,
                ];
            })
            ->filter()
            ->unique('id')
            ->values()
            ->toArray();
    }
}
