<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'gateway',
        'gateway_order_id',
        'gateway_token',
        'gateway_redirect_url',
        'gateway_transaction_id',
        'gateway_response',
        'status',
        'reference',
        'submitted_at',
        'paid_at',
        'released_at',
        'cancelled_at',
        'refunded_at',
        'verified_by',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'gateway_response' => 'array',
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'bank_transfer', 'transfer_bank' => 'Virtual Account',
            'qris' => 'QRIS',
            'e_wallet' => 'E-Wallet',
            'cod' => 'COD',
            default => str($this->method)->replace('_', ' ')->title()->toString(),
        };
    }
}
