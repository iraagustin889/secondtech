<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFeeConfiguration extends Model
{
    protected $fillable = ['type', 'value', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'value' => 'decimal:2'];
    }

    public static function active(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }

    // Dipanggil checkout: hasil dalam rupiah (bulat)
    public static function calculate(int $subtotal): int
    {
        $config = self::active();

        if (! $config) {
            return 0;
        }

        return $config->type === 'percentage'
            ? (int) round($subtotal * $config->value / 100)
            : (int) round($config->value);
    }
}