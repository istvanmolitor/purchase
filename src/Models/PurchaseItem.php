<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Molitor\Currency\Models\Currency;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductUnit;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'currency_id',
        'comment',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
