<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'order_number',
        'subtotal',
        'service_fee',
        'shipping_cost',
        'total',
        'status',
        'shipping_method',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'recipient_location',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_method' => ShippingMethod::class,
            'subtotal' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class);
    }
}
