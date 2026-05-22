<?php

namespace Molitor\Purchase\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Molitor\Admin\Traits\HasAdminFilters;
use Molitor\Purchase\Http\Requests\StorePurchaseExtraItemRequest;
use Molitor\Purchase\Http\Requests\UpdatePurchaseExtraItemRequest;
use Molitor\Purchase\Http\Resources\PurchaseExtraItemResource;
use Molitor\Purchase\Models\PurchaseExtraItem;
use Molitor\Purchase\Repositories\PurchaseExtraItemRepositoryInterface;

class PurchaseExtraItemApiController extends Controller
{
    use HasAdminFilters;

    public function __construct(protected PurchaseExtraItemRepositoryInterface $purchaseExtraItemRepository)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->purchaseExtraItemRepository->newQuery();

        $purchaseExtraItems = $this->applyAdminFilters($query, $request, ['name', 'description'])
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'data' => PurchaseExtraItemResource::collection($purchaseExtraItems->items()),
            'meta' => [
                'current_page' => $purchaseExtraItems->currentPage(),
                'last_page' => $purchaseExtraItems->lastPage(),
                'per_page' => $purchaseExtraItems->perPage(),
                'total' => $purchaseExtraItems->total(),
            ],
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([]);
    }

    public function store(StorePurchaseExtraItemRequest $request): JsonResponse
    {
        $purchaseExtraItem = $this->purchaseExtraItemRepository->create($request->validated());

        return response()->json([
            'data' => new PurchaseExtraItemResource($purchaseExtraItem),
            'message' => 'Beszerzesi extra tetel sikeresen letrehozva.',
        ], 201);
    }

    public function show(PurchaseExtraItem $purchaseExtraItem): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseExtraItemResource($purchaseExtraItem),
        ]);
    }

    public function edit(PurchaseExtraItem $purchaseExtraItem): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseExtraItemResource($purchaseExtraItem),
        ]);
    }

    public function update(UpdatePurchaseExtraItemRequest $request, PurchaseExtraItem $purchaseExtraItem): JsonResponse
    {
        $this->purchaseExtraItemRepository->update($purchaseExtraItem, $request->validated());

        return response()->json([
            'data' => new PurchaseExtraItemResource($purchaseExtraItem),
            'message' => 'Beszerzesi extra tetel sikeresen frissitve.',
        ]);
    }

    public function destroy(PurchaseExtraItem $purchaseExtraItem): JsonResponse
    {
        $this->purchaseExtraItemRepository->delete($purchaseExtraItem);

        return response()->json([
            'message' => 'Beszerzesi extra tetel sikeresen torolve.',
        ]);
    }
}

