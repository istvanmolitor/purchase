<?php

namespace Molitor\Purchase\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Molitor\Purchase\DataTables\PurchaseStatusDataTable;
use Molitor\Purchase\Enums\PurchaseState;
use Molitor\Purchase\Http\Requests\StorePurchaseStatusRequest;
use Molitor\Purchase\Http\Requests\UpdatePurchaseStatusRequest;
use Molitor\Purchase\Http\Resources\PurchaseStatusResource;
use Molitor\Purchase\Models\PurchaseStatus;
use Molitor\Purchase\Repositories\PurchaseStatusRepositoryInterface;

class PurchaseStatusApiController extends Controller
{
    public function index(PurchaseStatusDataTable $dataTable): AnonymousResourceCollection
    {
        return $dataTable->getResponse();
    }

    public function create(): JsonResponse
    {
        return response()->json([]);
    }

    public function store(StorePurchaseStatusRequest $request, PurchaseStatusRepositoryInterface $purchaseStatusRepository): JsonResponse
    {
        $status = $purchaseStatusRepository->create(
            $request->string('name')->toString(),
            PurchaseState::from($request->integer('state')),
            $request->input('description')
        );

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

    public function update(UpdatePurchaseStatusRequest $request, PurchaseStatus $purchaseStatus, PurchaseStatusRepositoryInterface $purchaseStatusRepository): JsonResponse
    {
        $purchaseStatus = $purchaseStatusRepository->update(
            $purchaseStatus,
            $request->string('name')->toString(),
            PurchaseState::from($request->integer('state')),
            $request->input('description')
        );

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
