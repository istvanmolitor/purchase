<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseExtraItemType extends Model
{
    protected $table = 'purchase_extra_item_types';

    protected $fillable = [
        'name',
        'description',
    ];

    public function __toString(): string
    {
        return $this->name;
    }
}


