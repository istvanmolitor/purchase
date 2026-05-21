<?php

namespace Molitor\Purchase\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Molitor\Admin\Traits\HasAdminFilters;
use Molitor\Purchase\Http\Requests\StorePurchaseStatusRequest;
use Molitor\Purchase\Http\Requests\UpdatePurchaseStatusRequest;
use Molitor\Purchase\Http\Resources\PurchaseStatusResource;
use Molitor\Purchase\Models\PurchaseStatus;

class PurchaseStatusApiController extends Controller
{
    use HasAdminFilters;

    public function index(Request $request): JsonResponse
    {
        $query = PurchaseStatus::query();
        $statuses = $this->applyAdminFilters($query, $request, ['name', 'description'])
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'data' => PurchaseStatusResource::collection($statuses->items()),
            'meta' => [
                'current_page' => $statuses->currentPage(),
                'last_page' => $statuses->lastPage(),
                'per_page' => $statuses->perPage(),
                'total' => $statuses->total(),
            ],
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([]);
    }

    public function store(StorePurchaseStatusRequest $request): JsonResponse
    {
        $status = PurchaseStatus::query()->create($request->validated());

        return response()->json([
            'data' => new PurchaseStatusResource($status),
            'message' => 'Beszerzes statusz sikeresen letrehozva.',
        ], 201);
    }

    public function show(PurchaseStatus $purchaseStatus): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseStatusResource($purchaseStatus),
        ]);
    }

    public function edit(PurchaseStatus $purchaseStatus): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseStatusResource($purchaseStatus),
        ]);
    }

    public function update(UpdatePurchaseStatusRequest $request, PurchaseStatus $purchaseStatus): JsonResponse
    {
        $purchaseStatus->update($request->validated());

        return response()->json([
            'data' => new PurchaseStatusResource($purchaseStatus),
            'message' => 'Beszerzes statusz sikeresen frissitve.',
        ]);
    }

    public function destroy(PurchaseStatus $purchaseStatus): JsonResponse
    {
        $purchaseStatus->delete();

        return response()->json([
            'message' => 'Beszerzes statusz sikeresen torolve.',
        ]);
    }
}

