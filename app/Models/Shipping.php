<?php

namespace App\Models;

use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use Database\Factories\ShippingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    /** @use HasFactory<ShippingFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'status',
        'courier',
        'service',
        'origin_id',
        'destination_id',
        'destination_name',
        'weight',
        'cost',
        'estimated_delivery',
        'resi',
        'tracking_status',
        'tracking_checked_at',
        'cod_location',
        'cod_meeting_at',
        'seller_cod_confirmed_at',
        'buyer_cod_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => ShippingMethod::class,
            'status' => ShippingStatus::class,
            'cost' => 'decimal:2',
            'tracking_checked_at' => 'datetime',
            'cod_meeting_at' => 'datetime',
            'seller_cod_confirmed_at' => 'datetime',
            'buyer_cod_confirmed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
