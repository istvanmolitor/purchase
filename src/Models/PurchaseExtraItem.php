<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseExtraItem extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function __toString(): string
    {
        return $this->name;
    }
}

