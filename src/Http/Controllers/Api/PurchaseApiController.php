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
use Molitor\Purchase\Models\Purchase;
use Molitor\Purchase\Models\PurchaseLog;
use Molitor\Purchase\Models\PurchaseStatus;
use Molitor\Purchase\Services\PurchaseService;
use Molitor\Stock\Models\Warehouse;

class PurchaseApiController extends Controller
{
    use HasAdminFilters;

    public function index(Request $request): JsonResponse
    {
        $query = Purchase::query()->with(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

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

        $this->syncPurchaseItems($purchase, $validated['purchase_items']);

        PurchaseLog::query()->create([
            'purchase_id' => $purchase->id,
            'purchase_status_id' => $validated['purchase_status_id'],
            'user_id' => auth()->id(),
            'comment' => $validated['comment'] ?? null,
            'status_changed_at' => now(),
        ]);

        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'message' => 'Beszerzes sikeresen letrehozva.',
        ], 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'logs' => $this->getLogs($purchase),
        ]);
    }

    public function edit(Purchase $purchase): JsonResponse
    {
        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

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

        $this->syncPurchaseItems($purchase, $validated['purchase_items']);

        if ($previousStatusId !== (int) $validated['purchase_status_id']) {
            PurchaseLog::query()->create([
                'purchase_id' => $purchase->id,
                'purchase_status_id' => $validated['purchase_status_id'],
                'user_id' => auth()->id(),
                'comment' => $validated['comment'] ?? null,
                'status_changed_at' => now(),
            ]);
        }

        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

        return response()->json([
            'data' => new PurchaseResource($purchase),
            'message' => 'Beszerzes sikeresen frissitve.',
        ]);
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        $purchase->purchaseItems()->delete();
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

        $purchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

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
        $closedPurchase->load(['customer', 'currency', 'warehouse', 'purchaseStatus', 'purchaseItems.product']);

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
                ->orderBy('sku')
                ->get(['id', 'sku'])
                ->map(function (Product $product): array {
                    return [
                        'id' => $product->id,
                        'name' => $product->sku,
                    ];
                })
                ->values(),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
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
}





