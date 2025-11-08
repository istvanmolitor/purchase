<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Molitor\Currency\Models\Currency;
use Molitor\Customer\Models\Customer;
use Molitor\Stock\Models\Warehouse;

class Purchase extends Model
{
    protected $fillable = [
        'is_closed',
        'url',
        'customer_id',
        'warehouse_id',
        'purchase_status_id',
        'comment',
        'purchase_date',
        'expected_delivery_date',
        'delivery_date',
        'total_price',
        'currency_id',
        'user_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Purchase $model) {
            $model->user_id = auth()->id();
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function purchaseStatus(): BelongsTo
    {
        return $this->belongsTo(PurchaseStatus::class, 'purchase_status_id');
    }
}
