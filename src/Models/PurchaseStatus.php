<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Molitor\Purchase\Enums\PurchaseState;

class PurchaseStatus extends Model
{
    protected $fillable = [
        'name',
        'state',
        'description',
    ];

    protected $casts = [
        'state' => PurchaseState::class,
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'purchase_status_id');
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
