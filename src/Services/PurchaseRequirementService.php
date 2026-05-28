<?php

namespace Molitor\Purchase\Services;

use Illuminate\Support\Collection as SupportCollection;
use Molitor\Order\Models\OrderItem;
use Molitor\Product\Models\Product;
use Molitor\Purchase\Enums\PurchaseState;
use Molitor\Purchase\Models\PurchaseItem;
use Molitor\Stock\Models\Stock;

class PurchaseRequirementService
{
    /**
     * @return SupportCollection<int, array<string, float|int|string|null>>
     */
    public function getProductsThatNeedPurchasing(?int $warehouseId = null): SupportCollection
    {
        $products = Product::query()
            ->with(['productUnit', 'mainImage'])
            ->where('active', true)
            ->orderBy('sku')
            ->get();

        return $products->map(function (Product $product) use ($warehouseId) {
            return $this->calculateProductRequirement($product, $warehouseId);
        })->filter(function ($item) {
            return $item !== null;
        })->values();
    }

    /**
     * @return array<string, float|int|string|null>|null
     */
    private function calculateProductRequirement(Product $product, ?int $warehouseId = null): ?array
    {
        $stockQuery = Stock::query()
            ->where('product_id', $product->id);

        if ($warehouseId !== null) {
            $stockQuery->whereHas('warehouseRegion', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        $stocks = $stockQuery->get();

        if ($stocks->isEmpty()) {
            return null;
        }

        $currentQuantity = $stocks->sum(function ($stock) {
            return (float) $stock->quantity;
        });

        $minQuantity = $stocks->sum(function ($stock) {
            return (float) ($stock->min_quantity ?? 0);
        });

        $incomingPurchases = $this->getIncomingPurchasesForProduct($product->id, $warehouseId);
        $pendingOrders = $this->getPendingOrdersForProduct($product->id, $warehouseId);
        $availableQuantity = $currentQuantity + $incomingPurchases - $pendingOrders;

        if ($availableQuantity < $minQuantity) {
            $needToOrder = $minQuantity - $availableQuantity;

            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->sku,
                'image_url' => $product->mainImage?->image_url,
                'product_unit' => $product->productUnit?->name,
                'current_quantity' => round($currentQuantity, 2),
                'min_quantity' => round($minQuantity, 2),
                'incoming_purchases' => round($incomingPurchases, 2),
                'pending_orders' => round($pendingOrders, 2),
                'available_quantity' => round($availableQuantity, 2),
                'need_to_order' => round($needToOrder, 2),
            ];
        }

        return null;
    }

    private function getIncomingPurchasesForProduct(int $productId, ?int $warehouseId = null): float
    {
        $query = PurchaseItem::query()
            ->where('product_id', $productId)
            ->whereHas('purchase', function ($q) {
                $q->whereHas('purchaseStatus', function ($sq) {
                    $sq->whereNotIn('state', [
                        PurchaseState::Completed->value,
                        PurchaseState::Cancelled->value,
                    ]);
                });
            });

        if ($warehouseId !== null) {
            $query->whereHas('purchase', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        return (float) $query->sum('quantity');
    }

    private function getPendingOrdersForProduct(int $productId, ?int $warehouseId = null): float
    {
        $query = OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('is_closed', false);
            });

        return (float) $query->sum('quantity');
    }
}



