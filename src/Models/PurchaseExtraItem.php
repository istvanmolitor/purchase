<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseExtraItem extends Model
{
    protected $table = 'purchase_extra_items';

    protected $fillable = [
        'purchase_id',
        'purchase_extra_item_type_id',
        'price',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function purchaseExtraItemType(): BelongsTo
    {
        return $this->belongsTo(PurchaseExtraItemType::class, 'purchase_extra_item_type_id');
    }
}
