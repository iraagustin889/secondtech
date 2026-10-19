<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    'stock',
    'status',
    'image',
])]
class Product extends Model
{
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
}
