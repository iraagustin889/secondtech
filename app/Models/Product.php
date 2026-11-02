<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Kolom yang boleh diisi lewat create()/update()
#[Fillable([
    'user_id',
    'category_id',
    'name',
    'description',
    'price',
    'condition',
    'weight',
    'location',
    'shipping_origin_id',
    'stock',
    'status',
    'image',
])]
class Product extends Model
{
    use HasFactory;

    // Produk dimiliki oleh satu penjual (User lewat kolom user_id)
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Produk masuk ke satu kategori
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Produk bisa muncul di banyak item pesanan
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where('stock', '>', 0);
    }

    public function isPurchasable(): bool
    {
        return $this->status === 'active' && $this->stock > 0;
    }
}
