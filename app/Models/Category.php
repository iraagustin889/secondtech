<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Kolom yang boleh diisi lewat create()/update()
#[Fillable(['name', 'slug', 'is_active'])]
class Category extends Model
{
    // Ubah is_active dari 0/1 di database menjadi true/false di PHP
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Satu kategori punya banyak produk
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
