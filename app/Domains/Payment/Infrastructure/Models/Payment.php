<?php

namespace App\Domains\Payment\Infrastructure\Models;

use App\Domains\Order\Infrastructure\Models\Order;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(PaymentFactory::class)]
class Payment extends Model
{
    use HasUlids,  HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'gateway',
        'transaction_reference',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
