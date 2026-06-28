<?php

namespace Molitor\Purchase\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Molitor\Purchase\DataTables\PurchaseExtraItemTypeDataTable;
use Molitor\Purchase\Http\Requests\StorePurchaseExtraItemTypeRequest;
use Molitor\Purchase\Http\Requests\UpdatePurchaseExtraItemTypeRequest;
use Molitor\Purchase\Http\Resources\PurchaseExtraItemTypeResource;
use Molitor\Purchase\Models\PurchaseExtraItemType;
use Molitor\Purchase\Repositories\PurchaseExtraItemTypeRepositoryInterface;

class PurchaseExtraItemTypeApiController extends Controller
{
    public function __construct(protected PurchaseExtraItemTypeRepositoryInterface $purchaseExtraItemTypeRepository) {}

    public function index(PurchaseExtraItemTypeDataTable $dataTable): AnonymousResourceCollection
    {
        return $dataTable->getResponse();
    }

    public function create(): JsonResponse
    {
        return response()->json([]);
    }

    public function store(StorePurchaseExtraItemTypeRequest $request): JsonResponse
    {
        $purchaseExtraItemType = $this->purchaseExtraItemTypeRepository->create($request->validated());

        return response()->json([
            'data' => new PurchaseExtraItemTypeResource($purchaseExtraItemType),
            'message' => 'Beszerzesi extra tetel tipus sikeresen letrehozva.',
        ], 201);
    }

    public function show(PurchaseExtraItemType $purchaseExtraItemType): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseExtraItemTypeResource($purchaseExtraItemType),
        ]);
    }

    public function edit(PurchaseExtraItemType $purchaseExtraItemType): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseExtraItemTypeResource($purchaseExtraItemType),
        ]);
    }

    public function update(UpdatePurchaseExtraItemTypeRequest $request, PurchaseExtraItemType $purchaseExtraItemType): JsonResponse
    {
        $this->purchaseExtraItemTypeRepository->update($purchaseExtraItemType, $request->validated());

        return response()->json([
            'data' => new PurchaseExtraItemTypeResource($purchaseExtraItemType),
            'message' => 'Beszerzesi extra tetel tipus sikeresen frissitve.',
        ]);
    }

    public function destroy(PurchaseExtraItemType $purchaseExtraItemType): JsonResponse
    {
        $this->purchaseExtraItemTypeRepository->delete($purchaseExtraItemType);

        return response()->json([
            'message' => 'Beszerzesi extra tetel tipus sikeresen torolve.',
        ]);
    }
}
