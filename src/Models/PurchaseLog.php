<?php

namespace Molitor\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PurchaseLog extends Model
{
    protected $fillable = [
        'user_id',
        'purchase_id',
        'purchase_status_id',
        'comment',
        'status_changed_at'
    ];

    protected $casts = [
        'status_changed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function purchaseStatus(): BelongsTo
    {
        return $this->belongsTo(PurchaseStatus::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
